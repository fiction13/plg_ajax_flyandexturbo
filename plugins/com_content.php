<?php
/*
 * @package   plg_ajax_flyandexturbo
 * @version   3.3.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Model\ArticlesModel;

class FLYandexTurboCoreContent extends FLYandexTurboCore
{

	/**
	 * @param $params
	 *
	 * @throws Exception
	 */
	function __construct($params)
	{
		parent::__construct($params);

		JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');
	}

	/**
	 * @param   int  $page
	 *
	 * @return array
	 */
	public function getContent($page = 1)
	{
		$result      = array();
		$options     = $this->params->get('content_options');
		$enableImage = $options->content_add_image;
		$items       = $this->getItems($page);

		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$date  = new Date($item->created);
				$image = $enableImage ? $this->getImage($item) : '';

				$result[] = array(
					'title'      => $item->title,
					'image'      => $image,
					'link'       => $this->getItemLink($item),
					'date'       => $date->toRFC822(true),
					'author'     => $this->getAuthor($item->created_by),
					'content'    => $this->prepareContent($item->introtext . $item->fulltext),
					'price'      => '',
					'properties' => '',
					'related'    => array()
				);
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getContentCategories()
	{
		$result         = array();
		$options        = $this->params->get('content_options');
		$image          = $options->content_add_image;
		$catImageEnable = $options->content_categories_add_image;
		$itemsEnable    = $options->content_categories_add_items;
		$categories     = $this->getCategories();

		if (!empty($categories))
		{

			foreach ($categories as $category)
			{
				$date    = new Date($category->created_time);
				$catLink = Route::_(ContentHelperRoute::getCategoryRoute($category->id) . $this->getUtmTags(), false, $this->ssl);
				$image   = $catImageEnable ? $this->getCatImage($category) : '';

				// Add Items To Category Content
				if ($itemsEnable)
				{
					$itemsHtml = array();
					$items     = $this->getItems(1, $category->id);

					// Get Path For Category Items
					$path = PluginHelper::getLayoutPath('ajax', 'flyandexturbo', 'default_category_items');

					// Render Template
					ob_start();
					include $path;
					$html = ob_get_clean();

					$category->description .= $html;
				}

				$result[] = array(
					'title'   => $category->title,
					'image'   => $image,
					'link'    => $catLink,
					'date'    => $date->toRFC822(true),
					'author'  => $this->getAuthor($category->created_user_id),
					'content' => $this->prepareContent($category->description),
					'related' => array()
				);
			}
		}

		return $result;
	}

	/**
	 * @param   int  $page
	 * @param   int  $category
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getItems($page = 1, $category = 0)
	{
		$items   = array();
		$options = $this->params->get('content_options');
		$catId   = (isset($options->content_catid) && isset($options->content_catid[0]) && !empty($options->content_catid[0])) ? $options->content_catid : array();
		$limit   = $options->content_count ?? 500;
		$start   = ($page - 1) * $limit;

		if ($category)
		{
			$catId = (array) $category;
			$limit = $options->content_categories_items_count ?? 30;
			$start = 0;
		}

		// Get an instance of the generic articles model
		$model = $this->app->bootComponent('com_content')->getMVCFactory()->createModel('Articles', 'Site', ['ignore_request' => true]);

		// Set application parameters in model
		$appParams = $this->app->getParams();
		$model->setState('params', $appParams);

		// Set the filters based on the module params
		$model->setState('list.start', $start);
		$model->setState('list.limit', $limit);
		$model->setState('filter.published', 1);

		// Access filter
		$access     = !ComponentHelper::getParams('com_content')->get('show_noauth');
		$model->setState('filter.access', $access);

		// Category filter
		$model->setState('filter.category_id', $catId);

		// Filter by language
		$model->setState('filter.language', $this->app->getLanguageFilter());

		//  Featured switch
		switch ($options->content_show_featured)
		{
			case '1' :
				$model->setState('filter.featured', 'only');
				break;
			case '0' :
				$model->setState('filter.featured', 'hide');
				break;
			default :
				$model->setState('filter.featured', 'show');
				break;
		}

		// Set ordering
		$ordering = 'a.created';
		$dir      = 'DESC';

		if ($this->params->get('enable_random'))
		{
			$model->setState('list.ordering', Factory::getDbo()->getQuery(true)->Rand());
		}
		else
		{
			$model->setState('list.ordering', $ordering);
			$model->setState('list.direction', $dir);
		}

		$items = $model->getItems();

		return $items;
	}

	/**
	 * @return array|mixed
	 */
	public function getCategories()
	{
		$items   = array();
		$options = $this->params->get('content_options');
		$catId   = isset($options->content_categories_catid) ? $options->content_categories_catid : array();

		// Remove All Categories Value

		if (!empty($catId) && empty($catId[0]))
		{
			unset($catId[0]);
		}

		$query = $this->db->getQuery(true);

		$query->select('*')
			->from($this->db->quoteName('#__categories'))
			->where($this->db->quoteName('parent_id') . ' != 0')
			->where($this->db->quoteName('published') . ' = 1')
			->where($this->db->quoteName('extension') . ' = ' . $this->db->quote('com_content'));

		if (!empty($catId))
		{
			$query->where('id IN (' . implode(',', $catId) . ')');
		}

		$this->db->setQuery($query);

		$result = $this->db->loadObjectList();

		return $result;
	}

	/**
	 * @param $item
	 *
	 * @return string|void
	 */
	public function getImage($item)
	{
		// Get Images From Content
		$image = '';
		$json  = json_decode($item->images);

		if (!empty($json->image_fulltext))
		{
			$image = '<figure><img src="' . Uri::root() . $json->image_fulltext . '" alt="' . $this->clearText($json->image_fulltext_alt) . '" title="' . $this->clearText($json->image_fulltext_caption) . '" /></figure>';

			return $image;
		}

		if (!empty($json->image_intro))
		{
			$image = '<figure><img src="' . Uri::root() . $json->image_intro . '" /><figcaption>' . $this->clearText($json->image_intro_caption) . '</figcaption></figure>';

			return $image;
		}

		return;
	}

	/**
	 * @param $category
	 *
	 * @return string|void
	 */
	public function getCatImage($category)
	{
		// Get Images From Content
		$image = '';
		$json  = json_decode($category->params);

		if (!empty($json->image))
		{
			$image = '<figure><img src="' . Uri::root() . $json->image . '" alt="' . $this->clearText($category->title) . '" title="' . $this->clearText($category->title) . '" /></figure>';

			return $image;
		}

		return;
	}

	/**
	 * @param $item
	 *
	 * @return string|void|null
	 */
	public function getItemLink($item)
	{
		$slug = $item->id . ':' . $item->alias;
		$link = Route::_(ContentHelperRoute::getArticleRoute($slug, $item->catid, $item->language) . $this->getUtmTags(), false, $this->ssl);

		return $link;
	}
}
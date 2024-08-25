<?php
/*
 * @package   plg_ajax_flyandexturbo
 * @version   3.3.4
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Application\CMSApplication;

class plgAjaxFLYandexTurbo extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    CMSApplication
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    DatabaseDriver
	 * @since  4.0.0
	 */
	protected $db;


	/**
	 * @var bool
	 */
	protected $autoloadLanguage = true;


	/**
	 * @var
	 */
	public $params;
	private \Joomla\Input\Input $input;

	private \Joomla\CMS\Document\Document $document;

	/**
	 * @param $subject
	 * @param $config
	 */
	public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);

		$this->app      = Factory::getApplication();
		$this->input    = $this->app->getInput();
		$this->document = $this->app->getDocument();
	}

	/**
	 * @return false|string|void
	 */
	public function onAjaxFlyandexturbo()
	{
		// Check Code
		if ($this->input->getString('code') != $this->params->get('channel_code'))
		{
			return;
		}

		// Set Encoding
		$this->document->setMimeEncoding('application/xml');
		$this->document->setType('xml');
		$this->app->setHeader('X-Robots-Tag', 'all', true);

		// Check Cache
		if ($this->params->get('enable_cache', 0))
		{
			// Get Request
			$component = $this->input->getString('component', 'all');
			$page      = $this->input->getInt('page', 1);
			$mode      = $this->input->getString('mode', 'all');

			$cacheId = md5(serialize($component . '_' . $page . '_' . $mode));

			$cache = new Cache(
				array(
					'caching'  => true,
					'lifetime' => $this->params->get('cache_time', $this->app->get('cachetime', 1140))
				)
			);

			$html = $cache->get($cacheId, 'plg_flyandexturbo');

			if (!$html)
			{
				$html = $this->getRss();

				$cache->store($html, $cacheId, 'plg_flyandexturbo');
			}
		}
		else
		{
			$html = $this->getRss();
		}

		return $html;
	}

	/**
	 * @return false|string
	 */
	public function getRss()
	{
		// Get Request
		$component = $this->input->getString('component', 'all');
		$page      = $this->input->getInt('page', 1);
		$mode      = $this->input->getString('mode', 'all');

		// Get Path
		$path    = PluginHelper::getLayoutPath('ajax', 'flyandexturbo');
		$plgPath = dirname(__FILE__) . '/plugins';

		$items   = array();
		$content = array();

		// Check All Components
		if ($component == 'all')
		{
			$plgPath = dirname(__FILE__) . '/plugins';

			if (is_dir($plgPath))
			{
				foreach (Folder::files($plgPath, '^([-_A-Za-z0-9]+)\.php$') as $file)
				{
					$plugin     = basename($file, '.php');
					$filePath   = $plgPath . '/' . $file;
					$plgName    = str_replace('com_', '', $plugin);
					$class      = 'FLYandexTurboCore' . ucfirst($plgName);
					$plgOptions = $this->params->get($plgName . '_options');
					$content    = array();

					if (is_file($filePath) && !class_exists($class) && isset($plgOptions->enable) && $plgOptions->enable)
					{
						include $filePath;

						$obj = new $class($this->params);

						switch ($mode)
						{
							case 'all':
								$content = $obj->getContent($page);

								if ($plgOptions->enable_categories)
								{
									$content = array_merge($content, $obj->getContentCategories());
								}

								break;

							case 'items':
								$content = $obj->getContent($page);
								break;

							case 'categories':
								if ($plgOptions->enable_categories)
								{
									$content = $obj->getContentCategories();
								}

								break;
						}

						$items = array_merge($items, $content);
					}
				}
			}
		}
		else
		{
			$component  = strtolower($component);
			$filePath   = $plgPath . '/com_' . $component . '.php';
			$class      = 'FLYandexTurboCore' . ucfirst($component);
			$plgOptions = $this->params->get($component . '_options');

			if (is_file($filePath) && !class_exists($class) && $plgOptions->enable)
			{
				include $filePath;

				$obj = new $class($this->params);

				switch ($mode)
				{
					case 'all':
						$content = $obj->getContent($page);

						if ($plgOptions->enable_categories)
						{
							$content = array_merge($content, $obj->getContentCategories());
						}

						break;

					case 'items':
						$content = $obj->getContent($page);
						break;

					case 'categories':
						if ($plgOptions->enable_categories)
						{
							$content = $obj->getContentCategories($page);
						}

						break;
				}

				$items = array_merge($items, $content);
			}
		}

		// Render Template
		ob_start();
		include $path;
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * @param $position
	 *
	 * @return mixed|string|void
	 */
	public function renderModule($position)
	{
		$moduleHtml       = array();
		$modules          = ModuleHelper::getModules($position);
		$attribs          = array();
		$attribs['style'] = 'xhtml';

		if (!empty($modules))
		{ // Check Empty Modules
			foreach ($modules as $module)
			{
				$moduleHtml[] = ModuleHelper::renderModule($module, $attribs);
			}

			// Strip Tags
			$core       = new FLYandexTurboCore($this->params);
			$tags       = $core->getAllowTags($this->params->get('items_tags', array()));
			$moduleHtml = implode("\n", $moduleHtml);
			$moduleHtml = strip_tags($moduleHtml, $tags);

			if ($this->params->get('enable_plugins', 0))
			{
				$moduleHtml = HTMLHelper::_('content.prepare', $moduleHtml);
			}

			return $moduleHtml;
		}

		return;
	}

	/**
	 * @param   array   $properties
	 * @param   string  $delimiter
	 * @param   string  $labelTag
	 * @param   string  $parentTag
	 * @param   string  $childTag
	 *
	 * @return string|void
	 */
	public function getProperties($properties = array(), $delimiter = ':', $labelTag = 'b', $parentTag = 'ul', $childTag = 'li')
	{
		if (!empty($properties))
		{
			$html = array();

			$html[] = '<' . $parentTag . '>';

			foreach ($properties as $prop)
			{

				if ($parentTag == 'table')
				{
					$html[] = '<tr>';
					$html[] = '<td><' . $labelTag . '>' . $prop['name'] . $delimiter . '</' . $labelTag . '></td>';
					$html[] = '<td>' . $prop['value'] . '</td>';
					$html[] = '</tr>';
				}
				else
				{
					$html[] = '<' . $childTag . '><' . $labelTag . '>' . $prop['name'] . $delimiter . '</' . $labelTag . '> ' . $prop['value'] . '</' . $childTag . '>';
				}

			}

			$html[] = '</' . $parentTag . '>';

			return implode("\n", $html);
		}

		return;
	}
}


/**
 * FL Yandex Turbo Core Class
 */
class FLYandexTurboCore
{
	protected mixed $params;

	protected ?\Joomla\CMS\Application\CMSApplicationInterface $app;

	private mixed $db;

	private \Joomla\Input\Input $input;

	private string $tags;

	protected int $ssl;

	/**
	 * @param $params
	 *
	 * @throws Exception
	 */
	function __construct($params)
	{
		$this->params = $params;
		$this->app    = Factory::getApplication();
		$this->db     = Factory::getContainer()->get('DatabaseDriver');
		$this->input  = $this->app->getInput();
		$this->tags   = $this->getAllowTags($this->params->get('items_tags', array()));
		$this->ssl    = ($this->app->get('force_ssl', 0) == 2 || $this->params->get('enable_force_ssl', 0)) ? 1 : -1;
	}

	/**
	 * @param   int  $page
	 *
	 * @return array
	 */
	public function getContent($page = 1)
	{
		return array();
	}

	/**
	 * @return array
	 */
	public function getContentCategories()
	{
		return array();
	}

	/**
	 * @return array
	 */
	public function getContentCustom()
	{
		$pages  = $this->params->get('custom_options');
		$result = array();

		if (!empty($pages))
		{
			foreach ($pages as $page)
			{
				if ($page->title && $page->content && $page->link)
				{ // Check Required Link, Content And Title
					$date  = new Date($page->date);
					$image = $page->image ? '<figure><img src="' . Uri::root() . $page->image . '" alt="' . $this->clearText($page->title) . '" title="' . $this->clearText($page->title) . '" /></figure>' : '';

					$result[] = array(
						'title'      => $page->title,
						'image'      => $image,
						'link'       => $page->link,
						'date'       => $date->toRFC822(true),
						'author'     => $page->author,
						'content'    => $this->prepareContent($page->content),
						'price'      => '',
						'properties' => '',
						'related'    => array()
					);
				}

			}
		}


		return $result;
	}

	/**
	 * @param $item
	 */
	public function getItemLink($item)
	{
		return;
	}

	/**
	 * @param $item
	 *
	 * @return mixed
	 */
	public function getItemTitle($item)
	{
		return $item->title;
	}

	/**
	 * @param $id
	 *
	 * @return string|null
	 */
	public function getAuthor($id)
	{
		$userName = '';
		$author   = $this->params->get('items_author');

		if ($author == 1)
		{
			$user     = Factory::getUser($id);
			$userName = $user->name;
		}
		else if ($author == 0)
		{
			$userName = $this->params->get('items_author_name', 'Администратор');
		}

		return $userName;
	}

	/**
	 * @param   array  $tags
	 *
	 * @return string
	 */
	public function getAllowTags($tags = array())
	{
		if (empty($tags))
		{
			return '';
		}

		$tags = array_map(
			function ($el) {
				return "<{$el}>";
			},
			$tags
		);

		return implode('', $tags);
	}

	/**
	 * @param $content
	 *
	 * @return array|mixed|string|string[]
	 */
	public function replaceTags($content)
	{
		$tags = $this->params->get('replace_tags');

		if ($tags)
		{
			$allTags = $replaceTags = array();

			foreach ($tags as $tag)
			{
				$allTags[]     = '<' . $tag;
				$allTags[]     = '</' . $tag . '>';
				$replaceTags[] = '<div';
				$replaceTags[] = '</div>';
			}

			$content = str_replace($allTags, $replaceTags, $content);
		}

		return $content;
	}

	/**
	 * @param $html
	 *
	 * @return array|string|string[]|null
	 */
	public function prepareContent($html)
	{
		if ($this->params->get('enable_plugins', 0))
		{
			$html = HTMLHelper::_('content.prepare', $html);
		}

		$allow_attr = array('href', 'src', 'http-equiv', 'content', 'charset');
		$allow_tags = array('br', 'img', 'script', 'meta', 'iframe', 'hr', 'source');

		// Remove scripts
		$html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);

		$dom                     = new DOMDocument('1.0', 'UTF-8');
		$dom->encoding           = "UTF-8";
		$dom->preserveWhiteSpace = false;

		libxml_use_internal_errors(true);

		// Convert
		$html = mb_encode_numericentity(
			htmlspecialchars_decode(
				htmlentities($html, ENT_NOQUOTES, 'UTF-8', false)
				, ENT_NOQUOTES
			), [0x80, 0x10FFFF, 0, ~0],
			'UTF-8'
		);

		$dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $html);

		// Remove Image Links And Wrap Images With Figure Tag

		if ($this->params->get('enable_figcaption'))
		{
			$images = $dom->getElementsByTagName('img');

			if ($images)
			{
				foreach ($images as $image)
				{
					if (empty($image->getAttribute('src')))
					{
						$image->setAttribute('src', $image->getAttribute('data-src'));
					}

					if ($image->parentNode->tagName == 'a')
					{
						$image->parentNode->parentNode->replaceChild($image, $image->parentNode);
					}

					if ($image->parentNode->tagName != 'figure')
					{
						$figure = $dom->createElement('figure');
						$title  = $image->getAttribute('alt') ? $image->getAttribute('alt') : $image->getAttribute('title');
						$image->parentNode->insertBefore($figure, $image);
						$figure->appendChild($image);

						if ($title)
						{
							$figcaption = $dom->createElement('figcaption', $title);
							$figure->appendChild($figcaption);
						}
					}
				}
			}
		}

		$xpath = new DOMXPath($dom);

		// Remove Tags With Noflyandexturbo Class And Custom Classes

		$removeElementsCondition = "contains(@class, 'noflyandexturbo')";

		if ($this->params->get('enable_remove'))
		{
			$classes = $this->params->get('remove_classes', '');
			$ids     = $this->params->get('remove_ids', '');

			if (!empty($classes))
			{
				$classes                 = array_map('trim', explode(',', $classes));
				$removeElementsCondition .= " or contains(@class, '" . implode("') or contains(@class, '", $classes) . "')";
			}

			if (!empty($ids))
			{
				$ids                     = array_map('trim', explode(',', $ids));
				$removeElementsCondition .= " or contains(@id, '" . implode("') or contains(@id, '", $ids) . "')";
			}
		}

		$removeElements = $xpath->query("//*[" . $removeElementsCondition . "]");

		if ($removeElements)
		{
			foreach ($removeElements as $node)
			{
				$node->parentNode->removeChild($node);
			}
		}

		// Clean All Tags

		if ($this->params->get('enable_clean'))
		{
			foreach ($xpath->query('//@*') as $node)
			{
				if (!in_array($node->nodeName, $allow_attr))
				{
					$node->parentNode->removeAttribute($node->nodeName);
				}
			}
		}

		// Remove Empty Tags

		foreach ($xpath->query('//*[not(node())]') as $node)
		{
			if (!in_array($node->nodeName, $allow_tags) && !strlen(trim($node->textContent)))
			{
				$node->parentNode->removeChild($node);
			}
		}

		// Add Ads Inside Content

		if ($this->params->get('items_advertisement_content'))
		{
			$paragraphCount = $this->params->get('items_advertisement_content_count');
			$paragraph      = $dom->getElementsByTagName('p')->item($paragraphCount);

			if ($paragraph)
			{
				$ads = $dom->createElement('figure');

				$ads->setAttribute('data-turbo-ad-id', 'flyandexturbo_ads_content');

				$paragraph->parentNode->insertBefore($ads, $paragraph);
			}
		}

		// Save Original Html

		$result = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace(array('<html>', '</html>', '<body>', '</body>', '<head>', '</head>', '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'), array('', '', '', '', '', '', ''), $dom->saveHTML()));

		// Replace Special Tags

		if ($this->params->get('enable_replace'))
		{
			$result = $this->replaceTags($result);
		}

		// Strip tags

		$result = strip_tags($result, $this->tags);

		// Remove Spaces

		$result = trim(preg_replace('/\s+/', ' ', $result));

		// Remove Text With Braces

		if ($this->params->get('enable_clean_braces'))
		{
			$result = preg_replace('/\{[^}]+\}/', '', $result);
		}

		// Remove Custom H1

		if ($this->params->get('enable_remove_h1'))
		{
			$result = preg_replace('/<h1[^>]*?>.*?<\/h1>/', '', $result);
		}

		// Remove Anchor Links

		if ($this->params->get('enable_remove_anchor'))
		{
			$result = preg_replace('/<a\s+[^>]*href="[^\/"]+"[^>]*>([^>]*)<\/a>/', '', $result);
		}

		return $result;
	}

	/**
	 * @param $text
	 *
	 * @return string
	 */
	public function clearText($text)
	{
		return trim(addslashes(htmlspecialchars(strip_tags($text))));
	}

	/**
	 * @return string|void
	 */
	public function getUtmTags()
	{
		if ($this->params->get('enable_utm'))
		{
			$utm = $this->params->get('utm_tags');
			$utm = '&' . trim(trim($utm), '\?');

			return $utm;
		}

		return;
	}
}
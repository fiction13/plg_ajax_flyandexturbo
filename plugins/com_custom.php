<?php
/*
 * @package   plg_ajax_flyandexturbo
 * @version   3.3.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

class FLYandexTurboCoreCustom extends FLYandexTurboCore
{

	/**
	 * @param $params
	 *
	 * @throws Exception
	 */
	function __construct($params)
	{
		parent::__construct($params);
	}

	/**
	 * @param   int  $page
	 *
	 * @return array
	 */
	public function getContent($page = 1)
	{
		$result  = array();
		$options = $this->params->get('custom_options');
		$items   = $options->items;

		if (!empty($items))
		{
			foreach ($items as $item)
			{
				if ($item->title && $item->content && $item->link)
				{ // Check Required Link, Content And Title
					$date  = new JDate($item->date);
					$image = $item->image ? '<figure><img src="' . Uri::root() . $item->image . '" alt="' . $this->clearText($item->title) . '" title="' . $this->clearText($item->title) . '" /></figure>' : '';

					$result[] = array(
						'title'      => $item->title,
						'image'      => $image,
						'link'       => $item->link,
						'date'       => $date->toRFC822(true),
						'author'     => $item->author,
						'content'    => $this->prepareContent($item->content),
						'price'      => '',
						'properties' => '',
						'related'    => array()
					);
				}

			}
		}

		return $result;
	}
}
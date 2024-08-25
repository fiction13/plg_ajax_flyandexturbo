<?php
/*
 * @package   plg_ajax_flyandexturbo
 * @version   3.3.4
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

class JFormFieldFLInit extends JFormField
{

	/**
	 * @var string
	 */
	protected $type = 'flinit';

	/**
	 * @return string|void
	 */
	protected function getLabel()
	{
		$lang = Factory::getLanguage();

		// Init Plugins Languages

		$plgPath = dirname(dirname(__FILE__)) . '/subform/plugins';

		if (is_dir($plgPath))
		{
			$plugins = Folder::files($plgPath, '^([-_A-Za-z0-9]+)\.xml$');

			foreach ($plugins as $file)
			{
				$extension = basename($file, '.xml');
				$lang->load($extension, JPATH_SITE . '/plugins/ajax/flyandexturbo', null, true); // Load Language For Plugin

				if ($extension != 'com_custom')
				{
					$component = str_replace('com_', '', $extension);

					if (!$this->checkComponent($component))
					{
						$id = 'attrib-' . $component;

						Factory::getDocument()->addScriptDeclaration("
					        document.addEventListener('DOMContentLoaded', (event) => {
					        	document.querySelector('button[aria-controls=\"" . $id . "\"]').remove();
								document.querySelector('#" . $id . "').remove();
					        });");
					}
				}
			}
		}

		return;
	}

	/**
	 * @return string|void
	 */
	protected function getInput()
	{
		return;
	}

	/**
	 * @param $component
	 *
	 * @return bool|void
	 */
	protected function checkComponent($component)
	{
		$component = ComponentHelper::getComponent('com_' . $component, true);

		if ($component && $component->enabled)
		{
			return true;
		}

		return;
	}
}
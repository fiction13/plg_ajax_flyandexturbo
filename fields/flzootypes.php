<?php
/*
 * @package   plg_ajax_flyandexturbo
 * @version   3.3.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\File;

defined('_JEXEC') or die('Restricted access');

class JFormFieldFLZooTypes extends JFormField
{

	/**
	 * @var string
	 */
	protected $type = 'flzootypes';

	/**
	 * @return string|void
	 */
	public function getInput()
	{

		if (!File::exists(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php')
			|| !ComponentHelper::getComponent('com_zoo', true)->enabled)
		{
			return;
		}

		// load config
		require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

		// get app
		$zoo     = App::getInstance('zoo');
		$attribs = '';
		$options = array();

		if ($v = $this->element->attributes()->class)
		{
			$attribs .= ' class="' . $v . '"';
		}
		else
		{
			$attribs .= ' class="inputbox"';
		}

		if ($this->element->attributes()->multiple)
		{
			$attribs .= ' multiple="multiple"';
		}

		foreach ($zoo->application->getApplications() as $application)
		{
			foreach ($application->getTypes() as $type)
			{
				$options[] = $zoo->html->_('select.option', $type->id, $type->name);
			}
		}

		return $zoo->html->_('select.genericlist', $options, $this->getName($this->fieldname), trim($attribs), 'value', 'text', $this->value, $this->id);
	}
}
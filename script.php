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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

defined( '_JEXEC' ) or die( 'Restricted access' );

class plgAjaxFLYandexTurboInstallerScript
{
    /**
	 * Minimum PHP version required to install the extension
	 *
	 * @var  string
	 *
	 * @since  1.4.0
	 */
	protected $minimumPhp = '7.4';

	/**
	 * Minimum Joomla! version required to install the extension
	 *
	 * @var  string
	 *
	 * @since  1.4.0
	 */
	protected $minimumJoomla = '4.0.0';

    /**
	 * Runs right after any installation action.
	 *
	 * @param   string            $type    Type of PostFlight action. Possible values are:
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @throws  Exception
	 *
	 * @return  boolean True on success, False on failure.
	 *
	 * @since   1.0.0
	 */
	function postflight($type, $parent)
	{
		$app      = Factory::getApplication();
		$jversion = new Version();

		// Check PHP
		if (!(version_compare(PHP_VERSION, $this->minimumPhp) >= 0))
		{
			$app->enqueueMessage(Text::sprintf('PLG_FLYANDEXTURBO_ERROR_PHP',
				$this->minimumPhp), 'error');

			return false;
		}

		// Check Joomla version
		if (!$jversion->isCompatible($this->minimumJoomla))
		{
			$app->enqueueMessage(Text::sprintf('PLG_FLYANDEXTURBO_ERROR_JOOMLA',
				$this->minimumJoomla), 'error');

			return false;
		}

        // Enable plugin
		if ($type == 'install')
        {
            $this->enablePlugin($parent);
        }

		return true;
	}

	/**
	 * Enable plugin after installation.
	 *
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @since   1.0.0
	 */
	protected function enablePlugin($parent)
	{
		// Prepare plugin object
		$plugin          = new stdClass();
		$plugin->type    = 'plugin';
		$plugin->element = $parent->getElement();
		$plugin->folder  = (string) $parent->getParent()->manifest->attributes()['group'];
		$plugin->enabled = 1;

		// Update record
		Factory::getDbo()->updateObject('#__extensions', $plugin, array('type', 'element', 'folder'));
	}
}

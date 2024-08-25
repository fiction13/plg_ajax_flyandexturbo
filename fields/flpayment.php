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

use Joomla\CMS\Factory;

class JFormFieldFLPayment extends JFormField
{

	/**
	 * @var string
	 */
	protected $type = 'flpayment';

	/**
	 * @return string
	 */
	protected function getLabel()
	{
		$component = $this->element->attributes()->component;

		Factory::getDocument()->addScriptDeclaration("
	        jQuery(function($) {
	        	$(document).ready(function() {
					$('#flyandexturbo-payment-" . $component . "').closest('.control-label').removeClass('control-label');
	        	});
	        });");

		$html   = array();
		$html[] = '<div id="flyandexturbo-payment-' . $component . '" class="alert alert-info">';
		$html[] = '<h3>' . $component . '</h3>';
		$html[] = '<p>Расширение плагина <b>FL Yandex Turbo</b> для работы с компонентом <b>' . $component . '</b> производится на коммерческой основе.</p>';
		$html[] = '<p>Приобрести расширение плагина можно со страницы плагина по ссылке ниже.</p>';
		$html[] = '<p><a class="btn btn-success btn-large" href="https://fictionlabs.ru/razrabotka/fl-yandex-turbo" target="_blank"><i class="icon-thumbs-up"></i> Приобрести</a></p>';
		$html[] = '</div>';

		return implode('', $html);
	}

	/**
	 * @return string|void
	 */
	protected function getInput()
	{
		return;
	}
}
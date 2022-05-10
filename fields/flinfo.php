<?php
/*
 * @package   plg_ajax_flyandexturbo
 * @version   3.3.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

class JFormFieldFLInfo extends JFormField
{

	/**
	 * @var string
	 */
	protected $type = 'flinfo';

	/**
	 * @return string
	 * @throws Exception
	 */
	protected function getLabel()
	{
		Factory::getDocument()->addScriptDeclaration("
	        jQuery(function($) {
	        	$(document).ready(function() {
					$('joomla-tab-element[id^=\"attrib-\"]:not(joomla-tab-element[id=\"attrib-pro\"], joomla-tab-element[id=\"attrib-main\"]) > .options-form > .form-grid > .control-group > .control-label').remove();
					$('joomla-tab-element[id^=\"attrib-\"]:not(joomla-tab-element[id=\"attrib-pro\"], joomla-tab-element[id=\"attrib-main\"]) > .options-form > .form-grid > .control-group > .controls').css('margin-left', '0');
					$('ul#flyandexturbo-tabs').closest('.control-label').removeClass('control-label');

					// Fix For Subform layout
					$('.subform-table-sublayout-section-byfieldsets').addClass('form-vertical');
					$('#myTabTabs').parent().addClass('w-100');
	        	});
	        });");

		$id   = Factory::getApplication()->input->getString('extension_id');
		$link = Uri::root() . '?option=com_ajax&plugin=flyandexturbo&format=raw&code=' . $this->getCode($id);

		$html[] = HTMLHelper::_('bootstrap.startTabSet', 'myTab', ['active' => 'flyandexturbo-main']);

		// Tab #1 Start
		$html[] = HTMLHelper::_('bootstrap.addTab', 'myTab', 'flyandexturbo-main', '<i class="icon-list-2"></i> Лента');
		$html[] = '<div class="alert alert-info">';
		$html[] = '<h4>Лента Яндекс.Турбо</h4>';
		$html[] = '<p><a href="' . $link . '" target="_blank">' . $link . '</a></p>';
		$html[] = '<h4>Лента материалов Яндекс.Турбо</h4>';
		$html[] = '<p><a href="' . $link . '&mode=items" target="_blank">' . $link . '&mode=items</a></p>';
		$html[] = '<h4>Лента категорий Яндекс.Турбо</h4>';
		$html[] = '<p><a href="' . $link . '&mode=categories" target="_blank">' . $link . '&mode=categories</a></p>';
		$html[] = '</div>';
		$html[] = HTMLHelper::_('bootstrap.endTab');
		// Tab #1 End

		// Tab #2 Start
		$html[] = HTMLHelper::_('bootstrap.addTab', 'myTab', 'flyandexturbo-pro', '<i class="icon-plus-circle"></i> Специальные возможности');
		$html[] = '<div class="alert alert-info">';
		$html[] = '<h4>Отображение модулей</h4>';
		$html[] = '<p>До материала - позиция <b>fl-yandex-turbo-top</b></p>';
		$html[] = '<p>После материала - позиция <b>fl-yandex-turbo-bottom</b></p>';
		$html[] = '<h4>Служебные тэги и метки</h4>';
		$html[] = '<p>Отключение формы в определенном материале - добавление в тело материала метки <b>{flyandexturbo_no_form}</b></p>';
		$html[] = '<p>Удаление опеределенного тэга из материала - добавление к тэгу класса <b>noflyandexturbo</b></p>';
		$html[] = '<h4>Пагинация ленты</h4>';
		$html[] = '<p>Для разбиения ленты на страницы к адресу ленты нужно добавить параметр <b>&page=2</b> c номером нужной страницы.</p>';
		$html[] = '<h4>Отображение только материалов</h4>';
		$html[] = '<p>Для того, чтобы в ленту попадали только материалы, без категорий, к адресу ленты нужно добавить параметр <b>&mode=items</b>.</p>';
		$html[] = '<h4>Отображение только категорий</h4>';
		$html[] = '<p>Для того, чтобы в ленту попадали только категории, без материалов, к адресу ленты нужно добавить параметр <b>&mode=categories</b>.</p>';
		$html[] = '<h4>Отображение ленты компонента</h4>';
		$html[] = '<p>Для того, чтобы в ленту попадали только страницы определенного компонента, к адресу ленты нужно добавить параметр <b>&component=content</b>, <b>&component=zoo</b> или <b>&component=k2</b> в зависимости от нужного компонента.</p>';
		$html[] = '<h4>Отображение ленты вручную добавленных страниц</h4>';
		$html[] = '<p>Для того, чтобы в ленту попадали только вручную добавленные страницы, к адресу ленты нужно добавить параметр <b>&component=custom</b>.</p>';
		$html[] = '</div>';
		$html[] = HTMLHelper::_('bootstrap.endTab');
		// Tab #2 End

		// Tab #3 Start
		$html[] = HTMLHelper::_('bootstrap.addTab', 'myTab', 'flyandexturbo-thanks', '<i class="icon-heart"></i> Сказать Спасибо');
		$html[] = '<iframe class="mt-3" src="https://yoomoney.ru/quickpay/shop-widget?writer=seller&targets=%D0%9F%D0%BE%D0%B4%D0%B4%D0%B5%D1%80%D0%B6%D0%BA%D0%B0%20%D0%BF%D1%80%D0%BE%D0%B5%D0%BA%D1%82%D0%B0%20FL%20Yandex%20Turbo&default-sum=500&button-text=11&payment-type-choice=on&successURL=&quickpay=shop&account=41001392723045&" width="100%" height="222" frameborder="0" allowtransparency="true" scrolling="no"></iframe>';
		$html[] = HTMLHelper::_('bootstrap.endTab');
		// Tab #3 End

		// Tab #4 Start
		$html[] = HTMLHelper::_('bootstrap.addTab', 'myTab', 'flyandexturbo-help', '<i class="icon-help"></i> Информация для поддержки');
		$html[] = '<div class="alert alert-info">';
		$html[] = '<ul>';
		$html[] = '<li><b>Версия FL Yandex Turbo:</b> 3.3.0</li>';
		$html[] = '<li><b>Версия Joomla:</b> ' . JVERSION . '</li>';
		$html[] = '<li><b>Версия PHP:</b> ' . phpversion() . '</li>';
		$html[] = '<li><b>Адрес ленты:</b> ' . $link . '</li>';
		$html[] = '</ul>';
		$html[] = '<p>Вышеуказанная информация сильно поможет и увеличит Ваши шансы получить бесплатную поддержку, если вдруг у Вас что-то не работает.</p>';
		$html[] = '<p>Бесплатная техническая поддержка по возможности оказывается в комментариях <a href="https://fictionlabs.ru/razrabotka/fl-yandex-turbo" target="_blank">на странице плагина</a>.</p>';
		$html[] = '</div>';
		$html[] = HTMLHelper::_('bootstrap.endTab');
		// Tab #4 End

		$html[] = HTMLHelper::_('bootstrap.endTabSet');

		return implode('', $html);
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	protected function getInput()
	{
		$id   = Factory::getApplication()->input->getString('extension_id');
		$html = '<input type="hidden" name="' . $this->name . '" value="' . $this->getCode($id) . '"/>';

		return $html;
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	protected function getCode($value)
	{
		$code = substr(strrev(md5($value)), 0, 16);

		return $code;
	}
}
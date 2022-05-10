<?php
/*
 * @package   plg_ajax_flyandexturbo
 * @version   3.3.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

error_reporting(0);

// Social

$socialHtml   = $callbackHtml = $formHtml = $advert_place_1 = $advert_place_2 = '';
$moduleTop    = $this->renderModule('fl-yandex-turbo-top');
$moduleBottom = $this->renderModule('fl-yandex-turbo-bottom');

if ((int) $this->params->get('items_social'))
{
	$socialOptions = $this->params->get('items_social_options');

	if (!empty($socialOptions))
	{
		$socialHtml = '<div data-block="share" data-network="' . implode(', ', $socialOptions) . '"></div> ';
	}
}

// Callback

if ((int) $this->params->get('items_callback'))
{
	$callbackTitle   = $this->params->get('items_callback_title');
	$callbackStick   = $this->params->get('items_callback_stick');
	$callbackOptions = $this->params->get('items_callback_options');

	if (!empty($callbackOptions))
	{
		$callbackHtml .= '<div data-block="widget-feedback" data-title="' . $callbackTitle . '" data-stick="' . $callbackStick . '">';

		foreach ($callbackOptions as $key => $callback)
		{
			$callbackHtml .= '<div data-type="' . $callback->items_callback_type . '"' .
				($callback->items_callback_url ? ' data-url="' . $callback->items_callback_url . '"' : '') .
				($callback->items_callback_email ? ' data-send-to="' . $callback->items_callback_email . '"' : '') .
				($callback->items_callback_company ? ' data-agreement-company="' . $callback->items_callback_company . '"' : '') .
				($callback->items_callback_link ? ' data-agreement-link="' . $callback->items_callback_link . '"' : '') .
				'></div>';
		}

		$callbackHtml .= '</div>';
	}
}

// Form

if ((int) $this->params->get('items_form'))
{
	$formType    = $this->params->get('items_form_type');
	$formEmail   = $this->params->get('items_form_email');
	$formCompany = $this->params->get('items_form_company');
	$formLink    = $this->params->get('items_form_link');
	$formBgcolor = $this->params->get('items_form_bgcolor');
	$formColor   = $this->params->get('items_form_color');
	$formBold    = $this->params->get('items_form_bold');
	$formText    = $this->params->get('items_form_text');

	if ($formType == 'item')
	{
		$formHtml .= '<form data-type="callback" data-send-to="' . $formEmail . '">'
			. ($formLink ? ' data-agreement-link="' . $formLink . '"' : '')
			. ($formCompany ? 'data-agreement-company="' . $formCompany . '"' : '')
			. '></form>';
	}
	else
	{
		$formHtml .= '<div><button formaction="mailto:' . $formEmail . '" data-send-to="' . $formEmail . '"' .
			($formLink ? ' data-agreement-link="' . $formLink . '"' : '') .
			($formCompany ? ' data-agreement-company="' . $formCompany . '"' : '') .
			($formBgcolor ? ' data-background-color="' . $formBgcolor . '"' : '') .
			($formColor ? ' data-color="' . $formColor . '"' : '') .
			($formBold ? ' data-primary="' . $formBold . '"' : '') .
			'>' . $formText . '</button></div>';
	}
}

?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL; ?>
<rss
        xmlns:yandex="http://news.yandex.ru"
        xmlns:media="http://search.yahoo.com/mrss/"
        xmlns:turbo="http://turbo.yandex.ru"
        version="2.0">

	<?php if (!empty($items)) : ?>

        <channel>
            <turbo:cms_plugin>8D723BD54DAF289363945EFC48B90F4C</turbo:cms_plugin>
            <title><?php echo $this->params->get('channel_title'); ?></title>
            <link><?php echo Uri::root(); ?></link>
            <description><?php echo $this->params->get('channel_description'); ?></description>
            <language><?php echo $this->params->get('channel_language'); ?></language>

			<?php if ((int) $this->params->get('items_analitics'))
			{
				$analiticsOptions = $this->params->get('items_analitics_options');

				if (!empty($analiticsOptions))
				{
					foreach ($analiticsOptions as $analitic)
					{ ?>
                        <yandex:analytics id="<?php echo $analitic->items_analitics_id; ?>"
                                          type="<?php echo $analitic->items_analitics_type; ?>" <?php echo $analitic->items_analitics_liveinternet_params ? 'params="' . $analitic->items_analitics_liveinternet_params . '"' : ''; ?>></yandex:analytics>
					<?php }
				}
			} ?>

			<?php if ((int) $this->params->get('items_advertisement'))
			{
				$advertisementOptions = $this->params->get('items_advertisement_options');

				if (!empty($advertisementOptions))
				{
					$i = 1;

					foreach ($advertisementOptions as $advertisement)
					{
						${'advert_place_' . $i} = '<figure data-turbo-ad-id="flyandexturbo_ads_' . $i . '"></figure>';
						?>
                        <turbo:adNetwork
                                type="<?php echo $advertisement->items_advertisement_type; ?>" <?php echo $advertisement->items_advertisement_id ? 'id="' . $advertisement->items_advertisement_id . '"' : ''; ?>
                                turbo-ad-id="flyandexturbo_ads_<?php echo $i; ?>"><?php echo $advertisement->items_advertisement_attr ? '<![CDATA[' . $advertisement->items_advertisement_attr . ']]>' : ''; ?></turbo:adNetwork>
						<?php $i++;
					}

					// Ads Inside Content

					if ($this->params->get('items_advertisement_content'))
					{
						$adsContent = $advertisementOptions->items_advertisement_options0;
						?>
                        <turbo:adNetwork
                                type="<?php echo $adsContent->items_advertisement_type; ?>" <?php echo $adsContent->items_advertisement_id ? 'id="' . $adsContent->items_advertisement_id . '"' : ''; ?>
                                turbo-ad-id="flyandexturbo_ads_content"><?php echo $adsContent->items_advertisement_attr ? '<![CDATA[' . $adsContent->items_advertisement_attr . ']]>' : ''; ?></turbo:adNetwork>
					<?php }
				}
			} ?>

			<?php foreach ($items as $key => $item) : ?>
				<?php
				if (strpos($item['content'], '{flyandexturbo_no_form}') !== false)
				{ // Remove Form For Current Item
					$item['content'] = str_replace('{flyandexturbo_no_form}', '', $item['content']);
					$pageFormHtml    = '';
				}
				else
				{
					$pageFormHtml = $formHtml;
				}
				?>
                <item turbo="<?php echo $this->params->get('disable_feed') ? 'false' : 'true' ?>">
                    <title><?php echo htmlspecialchars($item['title']); ?></title>
                    <turbo:topic><?php echo htmlspecialchars($item['title']); ?></turbo:topic>
                    <link><?php echo htmlspecialchars($item['link']); ?></link>
                    <guid isPermaLink="false"><?php echo htmlspecialchars($item['link']); ?></guid>

					<?php if ($item['date'] && $this->params->get('items_date', 1)) : ?>
                        <pubDate><?php echo $item['date']; ?></pubDate>
					<?php endif; ?>

					<?php if ($item['author'] && $this->params->get('items_author', 1) != 2) : ?>
                        <author><?php echo $item['author']; ?></author>
					<?php endif; ?>

					<?php
					// Get Path For Content
					$pathContent = PluginHelper::getLayoutPath('ajax', 'flyandexturbo', 'default_content');

					// Render Template
					ob_start();
					include $pathContent;
					$html = ob_get_clean();
					?>

                    <turbo:content><?php echo '<![CDATA[' . $html . ']]>'; ?></turbo:content>

					<?php if ($item['related']) : ?>
                        <yandex:related>
							<?php foreach ($item['related'] as $key => $related) : ?>
                                <link url="<?php echo $related['link']; ?>" <?php echo $related['image'] ? 'img="' . $related['image'] . '"' : ''; ?>><?php echo $related['text']; ?></link>
							<?php endforeach; ?>
                        </yandex:related>
					<?php endif; ?>

                </item>
			<?php endforeach; ?>

        </channel>

	<?php else : ?>
        <noitems><?php echo Text::_('PLG_FLYANDEXTURBO_NO_ITEMS'); ?></noitems>
	<?php endif; ?>
</rss>
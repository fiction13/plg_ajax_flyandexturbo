<?php
/*
 * @package   plg_ajax_flyandexturbo
 * @version   3.3.4
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

error_reporting(0);

// no direct access
defined('_JEXEC') or die('Restricted access');
?>

    <header>
        <h1><?php echo $item['title'] ?></h1>

		<?php echo $item['image'] ? $item['image'] : ''; ?>
    </header>

<?php echo $moduleTop; // Module Position Top ?>

<?php echo $advert_place_1; //A ds Place #1 ?>

<?php if ($item['price']) : // Price ?>
    <h2><?php echo JText::_('PLG_FLYANDEXTURBO_PRICE') ?>: <?php echo $item['price']; ?></h2>
<?php endif; ?>

<?php if ($item['properties']) : // Properties ?>
    <h2><?php echo JText::_('PLG_FLYANDEXTURBO_PROPS') ?></h2>
	<?php echo $this->getProperties($item['properties']); ?>
<?php endif; ?>

<?php echo $item['content']; // Content ?>

<?php echo $callbackHtml; // Callback Buttons ?>

<?php echo $pageFormHtml; // Callback Form ?>

<?php echo $socialHtml; // Social Buttons ?>

<?php echo $advert_place_2; // Ads Place #2 ?>

<?php echo $moduleBottom; // Module Position Bottom ?>
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

?>

<?php if (!empty($items)) : ?>
    <div class="turbo-items">
		<?php foreach ($items as $item) : ?>
			<?php
			$itemLink  = $this->getItemLink($item);
			$itemTitle = '<h3 class="turbo-items__item-title">' . $this->getItemTitle($item) . '</h3>';
			$itemImage = $this->getImage($item);
			?>

            <div class="turbo-items__item">
				<?php if ($itemLink) : ?>
                    <a class="turbo-items__item-link" href="<?php echo $itemLink; ?>">
						<?php echo $itemImage; ?>
						<?php echo $itemTitle; ?>
                    </a>
				<?php else : ?>
					<?php echo $itemImage ? $itemImage : ''; ?>
					<?php echo $itemTitle; ?>
				<?php endif; ?>
            </div>

		<?php endforeach; ?>
    </div>
<?php endif; ?>
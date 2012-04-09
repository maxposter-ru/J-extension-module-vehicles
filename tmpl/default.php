<?php
/**
 * ?
 *
 * @param  string  $moduleclass_sfx
 * @param  string  $menuId          Menu identifier (first) for component
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$size = $params->get('photo_size', '120x90');
list($width, $height) = explode('x', $size);
?>

<div class="maxposter-<?php echo $moduleclass_sfx ?> maxposter-module-vehicles">
    <?php $vehicles = simplexml_import_dom($xpath->query('/response/vehicles')->item(0)) ?>
    <?php foreach ($vehicles as $vehicle) : ?>
        <?php
            $priceStatus = (string) $vehicle->price['status'];
            $prevPrice = (string) $vehicle->price->previous;
            // Menu ID?
            $vehicleRoute = JRoute::_(sprintf('index.php?option=com_maxposter&view=car%s&vehicle_id=%d', $menuId, $vehicle['vehicle_id']));
        ?>
        <div class="maxposter-item">
            <div class="maxposter-item-wrapper">
                <div class="maxposter-photo">
                    <a href="<?php echo $vehicleRoute ?>">
                        <?php if ((string) $vehicle->photo) : ?>
                            <img src="<?php echo JURI::root(true), 'media/maxposter/photos/cache/', (strpos($params->get('dealer_id'), '_') ? $vehicle['dealer_id'] . '/' : ''), $vehicle['vehicle_id'], '/', $size, '/', $vehicle->photo['file_name'] ?>" alt="<?php printf('%s %s', $vehicle->mark, $vehicle->model) ?>" width="<?php echo $width ?>" height="<?php echo $height ?>" />
                        <?php else: ?>
                            <?php echo JHtml::image('maxposter/no_photo_120x90.gif', sprintf('%s %s', $vehicle->mark, $vehicle->model), array('width' => $width, 'height' => $height), true) ?>
                        <?php endif ?>
                    </a>
                </div>
                <div class="maxposter-text">
                    <h4><a href="<?php echo $vehicleRoute ?>"><?php printf('%s %s', $vehicle->mark, $vehicle->model) ?></a><span></span></h4>
                    <?php if ($params->get('view_show_price', 1)) : ?>
                        <p class="maxposter-price<?php $priceStatus ? ' maxposter-price-' . $priceStatus : ''; ?>">
                            <?php switch ($vehicle->price->value['unit']) :
                                case 'eur': ?>€ <?php break; case 'usd': ?>$ <?php break; ?>
                            <?php endswitch ?>
                            <?php echo str_replace(' ', '&nbsp;', number_format((string) $vehicle->price->value, 0, '.', ' ')) ?>
                            <?php if ('rub' == $vehicle->price->value['unit']) : ?>
                                руб.
                            <?php endif ?>
                        </p>
                    <?php endif ?>
                    <?php if ($params->get('view_show_priceold', 0)) : ?>
                        <?php if ($priceStatus && $prevPrice) : ?>
                            <p class="maxposter-previous-price">
                                <?php switch ($vehicle->price->value['unit']) :
                                    case 'eur': ?>€ <?php break; case 'usd': ?>$ <?php break; ?>
                                <?php endswitch ?>
                                <?php echo str_replace(' ', '&nbsp;', number_format($prevPrice, 0, '.', ' ')) ?>
                                <?php if ('rub' == $vehicle->price->value['unit']) : ?>
                                    руб.
                                <?php endif ?>
                            </p>
                        <?php else: ?>
                            <p class="maxposter-previous-price-placeholder">&nbsp;</p>
                        <?php endif ?>
                    <?php endif ?>
                    <?php if ($params->get('view_show_year', 1)) : ?>
                    <p class="maxposter-year"><?php echo $vehicle->year ?> г.в.</p>
                    <?php endif ?>
                </div>
            </div>
        </div>
    <?php endforeach ?>
</div>

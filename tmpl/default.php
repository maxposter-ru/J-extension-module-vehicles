<?php
/**
 * ?
 *
 * @param  string  $moduleclass_sfx
 * @param  string  $maxRoute        url to com_maxposter search results
 * @param  array   $search          current search data
 * @param  object  $helper
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
            // Menu ID?
            $vehicleRoute = JRoute::_(sprintf('index.php?option=com_maxposter&view=car&vehicle_id=%d', $vehicle['vehicle_id']));
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
                    <h4><a href="<?php echo JRoute::_(sprintf('index.php?option=com_maxposter&view=car&vehicle_id=%d', $vehicle['vehicle_id'])) ?>"><?php printf('%s %s', $vehicle->mark, $vehicle->model) ?></a><span></span></h4>
                    <?php if ($params->get('show_price', 1)) : ?>
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
                    <?php if ($params->get('show_priceold', 0)) : ?>
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
                    <?php if ($params->get('show_year', 1)) : ?>
                    <p class="maxposter-year"><?php echo $vehicle->year ?> г.в.</p>
                    <?php endif ?>
                </div>
            </div>
        </div>
    <?php endforeach ?>
</div>

<?php /*
     <vehicle vehicle_id="76684" dealer_id="337" type="car">
  <mark mark_id="105">Toyota</mark>
  <model model_id="2656">Land Cruiser 100</model>
  <year>2001</year>
  <steering_wheel>
    <place steering_wheel_place_id="left">левый</place>
      </steering_wheel>
  <engine>
    <type engine_type_id="petrol_injector">бензин инжектор</type>
    <volume unit="cm3">4663</volume>
          <power unit="hp">235</power>
          </engine>
  <drive>
    <type drive_type_id="optional_4wd">подключаемый полный</type>
  </drive>
  <gearbox>
    <type gearbox_type_id="automatic">автоматическая</type>
  </gearbox>
  <body>
    <type body_type_id="suv5">внедорожник (5-ти дв.)</type>
    <color body_color_id="black" metallic="1">черный металлик</color>
  </body>
  <mileage>
    <value unit="km">129000</value>
      </mileage>
  <condition condition_id="excellent">отличное</condition>
      <price>
    <value unit="rub" rub_price="820000">820000</value>
          </price>
  <pts_owner_count>один</pts_owner_count>
  <availability availability_id="available">в наличии</availability>

                <photo photo_id="726915" file_name="8466fabfe8ff9b81a64eed68ac42a66b.jpg">
        <thumbnail>http://www.maxposter.ru/photo/337/76684/thumbnail/8466fabfe8ff9b81a64eed68ac42a66b.jpg</thumbnail>
              </photo>
            <dates>
    <created_at>2012-04-07</created_at>
    <updated_at>2012-04-07</updated_at>
    <expires_at>2012-05-07</expires_at>
  </dates>
</vehicle>
*/

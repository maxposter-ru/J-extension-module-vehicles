<?php
/**
 * Модуль выводит несколько автомобилей по заданным условиям
 *
 * @param  JRegistry  $params  module params
 * @param  JSite      $app
 * @param  stdClass   $module  @see JModuleHelper::renderModule
 * @param  string     $option  com_content for example
 * @param  unknown    $scope
 * @param  string     $path    absolute path to current module file, like __FILE__
 * @param  JLanguage  $lang
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// проверка включенности компонента
if (!JComponentHelper::isEnabled('com_maxposter', $strict = true)) {
    if (defined('JDEBUG') && constant('JDEBUG')) {
        // Load error template
        require (JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default') . '_error'));
    }
    // just return silently
    return;
}
// JInput
$JInput = $app->input;

// Component settings
$maxParams = JComponentHelper::getParams('com_maxposter', $strict = true); # $app->getParams('com_maxposter')
$params->merge($maxParams);

$maxComponent = JComponentHelper::getComponent('com_maxposter');
$menu = $app->getMenu();
$items = $menu->getItems('component_id', $maxComponent->id);
$menuId = (!empty($items['0']) ? sprintf('&Itemid=%d', $items['0']->id) : '');
# $maxLayout = ($maxLayout = $params->getValue('max_layout', '')) ? sprintf('&layout=%s', $maxLayout) : '';

// MaxPoster libraries
jimport('maxposter.maxCacheHtmlClient');
require_once (JPATH_SITE.'/components/com_maxposter/lib/client/maxClient.php');
require_once (JPATH_SITE.'/components/com_maxposter/helpers/helper.php');

# Styles
# base
JHtml::stylesheet('maxposter/mod_vehicles/style.css', array(), true, false, false);
# client overrides
JHtml::stylesheet('mod_maxposter_vehicles.css', array(), true);

// Определение, какие данные необходимы от Интернет-сервиса
$client = new maxClient(MaxPosterHelper::getConfig());
$client->setOption('rows_by_request', $params->get('api_limit', '3'));
$client->setOption('rows_by_page', $params->get('api_limit', '3'));
$client->setPage(1);
$client->setRequestThemeName($params->get('api_request', 'vehicles'));
$client->setPage($page);

$search = array();
$searchFields = array(
    'filter_mark'           => 'mark_id',
    'filter_model'          => 'model_id',
    'filter_year_from'      => array('year', 'from'),
    'filter_year_to'        => array('year', 'to'),
    'filter_steering_wheel' => 'steering_wheel',
    'filter_engine_type'    => 'engine_type',
    'filter_drive_type'     => 'drive_type',
    'filter_gearbox_type'   => 'gearbox_type',
    'filter_body_type'      => 'body_type',
    'filter_availability'   => 'availability',
    'filter_price_from'     => array('price', 'from'),
    'filter_price_to'       => array('price', 'to'),
    'filter_distance_from'  => array('distance', 'from'),
    'filter_distance_to'    => array('distance', 'to'),
    'filter_special'        => 'special_offer',
    'filter_order_by'       => 'order_by',
    'filter_order_direction'=> 'order_direction',
);
foreach ($searchFields as $searchField => $searchBinding) {
    $tmpParam = $params->get($searchField, '');
    if (!empty($tmpParam) && (array('0' => '0') != $tmpParam)) {
        if (is_array($searchBinding)) {
            // keep $tmp var at deeper key available
            $tmp = &$search;
            while (!empty($searchBinding)) {
                $key = array_shift($searchBinding);
                $tmp[$key] = array();
                $tmp = & $tmp[$key];
                unset($key);
            }
            $tmp = $params->get($searchField);
            // unset $tmp var binding
            unset($tmp);
        } else {
            $search[$searchBinding] = $params->get($searchField);
        }
    }
}
$client->setRequestParams(array('search' => $search));

// TODO: дополнительно опция: заполнять до лимита другими авто
//       будет проблемка с кешированием

// TODO: copypaste перенести кеширование xml в одно место
// кеширование XML
$cache = JFactory::getCache('com_maxposter', '');
$cache->setCaching(true);
$cache->setLifeTime(60);
$cache->_getStorage()->_lifetime = 60;
$cacheId = $client->getRequestCacheId();

if (!$rawXml = $cache->get($cacheId)) {
    $xml = $client->getXml();
    $responceId = $xml->getElementsByTagName('response')->item(0)->getAttribute('id');
    if ('error' != $responceId) {
        list($cacheActualAt, $cacheExpiresAt) = $client->getCacheTimes();
        $cacheLife = (int) $cacheExpiresAt - time(); # кешируем в секундах
        $cache->setLifeTime($cacheLife);
        $cache->_getStorage()->_lifetime = $cacheLife;
        if ($cacheLife > 1) {
            $cache->store($xml->saveXml(), $cacheId);
        }
    }
} else {
    $xml = new DOMDocument();
    $xml->loadXML($rawXml);
    $responceId = $xml->getElementsByTagName('response')->item(0)->getAttribute('id');
}

switch ($responceId) {
    # ошибка запроса, сервис недоступен и т.п.
    case 'error':
        $params->set('error', 404);
        $cache->setCaching(false);
        $cache->setLifeTime(0);
        $cache->_getStorage()->_lifetime = 0;
        # FIXME: сейчас в случае ошибки модуль не будет выведен
        return '';
        break;
    case 'vehicles':
    case 'full_vehicles':
        // нечего выводить - ничего и не выводим
        if ($xml->getElementsByTagName('vehicle')->length == 0) {
            return '';
        }
        break;
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

$xpath = new DomXPath($xml);

// Template
// TODO: кешировать
require (JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default')));

<?php
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

require_once __DIR__ . '/helper.php';

$cacheparams               = new stdClass();
$cacheparams->cachemode    = 'safeuri';
$cacheparams->class        = 'ModAuthorInfoHelper';
$cacheparams->method       = 'getAuthorContact';
$cacheparams->methodparams = [$params];
$cacheparams->modeparams   = [
    'option' => 'CMD',
    'view'   => 'CMD',
    'id'     => 'INT',
    'lang'   => 'CMD',
];

$authorData = ModuleHelper::moduleCache($module, $params, $cacheparams);

if (!$authorData) {
    return;
}

require ModuleHelper::getLayoutPath('mod_authorinfo', $params->get('layout', 'default'));

<?php
defined('_JEXEC') or die;
require_once dirname(__FILE__) . '/helper.php';
$authorData = ModAuthorInfoHelper::getAuthorContact();
require JModuleHelper::getLayoutPath('mod_authorinfo', $params->get('layout', 'default'));

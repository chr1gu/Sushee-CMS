<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 30/09/14
 * Time: 20:51
 */
require_once dirname(__FILE__) . '/../../src/admin.login.php';
require_once dirname(__FILE__) . '/../../src/admin.modules.php';
require_once dirname(__FILE__) . '/../../src/admin.overview.php';

$login = new AdminLogin();
$user = $login->getSessionUser();
if ($user) {
    $modules = new AdminModules();
    $overview = new AdminOverview($user, $modules->getModules());
    $template = $overview->getTemplate();
    print ($template);
} else {
    include 'login.html';
}

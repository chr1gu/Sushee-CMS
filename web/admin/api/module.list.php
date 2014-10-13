<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 02/10/14
 * Time: 18:56
 */

require_once dirname(__FILE__) . '/../../../src/admin.modules.php';
require_once dirname(__FILE__) . '/../../../src/admin.login.php';
header('Content-Type: application/json');

$login = new AdminLogin();
$user = $login->getSessionUser();
if (!$user) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Keine Berechtigung.'
    )));
}

$id = filter_input(INPUT_GET, "id");
//sleep(2);

$adminModule = new AdminModules();
$module = $adminModule->getModuleById($id);

if (!$module) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Module nicht gefunden!'
    )));
}

$fields = $adminModule->getListFieldsForModule($module);
$data = $adminModule->getListDataForModule($module);

return print (json_encode(array(
    'success' => true,
    'name' => $module['name'],
    'id' => $module['id'],
    'single' => $module['single'],
    'fields' => $fields,
    'data' => $data
)));

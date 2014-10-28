<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 05/10/14
 * Time: 15:32
 */

require_once dirname(__FILE__) . '/../../../src/admin.modules.php';
require_once dirname(__FILE__) . '/../../../src/admin.login.php';
header('Content-Type: application/json');

$login = new AdminLogin();
$user = $login->getSessionUser();
$adminModule = new AdminModules();
$id = filter_input(INPUT_GET, "id");
if (!empty($_POST)) {
    $id = filter_input(INPUT_POST, "id");
}
$module = $adminModule->getModuleById($id);

if (!$user) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Keine Berechtigung.'
    )));
}

if (!$module) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Module nicht gefunden!'
    )));
}

// write data
if (!empty($_POST)) {
    // save data
    $dataId = filter_input(INPUT_POST, 'data-id');
    if (!$dataId) {
        return print (json_encode(array(
            'success' => false,
            'error' => 'Daten-ID nicht vorhanden'
        )));
    }
    $data = $adminModule->setData($module, $_POST);
}

$data = null;
if ($module['single']) {
    $data = $adminModule->getData($module);
}

$dataId = filter_input(INPUT_GET, "data-id");
if ($dataId) {
    $dataArray = $adminModule->getData($module, null, $dataId);
    $data = count($dataArray) ? $dataArray[0] : null;
}

if (!$data) {
    $data = array(
        'id' => time()
    );
}

return print (json_encode(array(
    'success' => true,
    'name' => $module['name'],
    'id' => $module['id'],
    'single' => $module['single'],
    'fields' => $module['fields'],
    'data' => $data
)));

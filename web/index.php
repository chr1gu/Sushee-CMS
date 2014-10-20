<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 14/10/14
 * Time: 19:25
 */
$path = filter_input(INPUT_GET, "path");
if (!$path) {
    return header("location:./admin");
}

header('Content-Type: application/json');
$dataDir = dirname(__FILE__) . '/../data';
$viewsDir = $dataDir . '/views';
$viewPath = $viewsDir . '/' . $path . '.json';

if (!is_file($viewPath)) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'View nicht gefunden'
    )));
}

$viewData = json_decode(file_get_contents($viewPath), true);
if (!$viewData) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'View konnte nicht geladen werden'
    )));
}

$module = isset($viewData['module']) ? $viewData['module'] : null;
if (!$module) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Modul nicht definiert'
    )));
}

$fields = isset($viewData['fields']) && is_array($viewData['fields']) && !empty($viewData['fields']) ? $viewData['fields'] : null;
if (!$fields) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Keine Felder definiert'
    )));
}

// TODO: where...


require_once dirname(__FILE__) . '/../src/admin.modules.php';

$adminModule = new AdminModules();
$module = $adminModule->getModuleById($viewData['module']);
$data = $adminModule->getData($viewData['module'], $viewData['fields']);
$response = array(
    'success' => true,
    //'module' => $module,
    'data' => $data
);

return print (json_encode($response));

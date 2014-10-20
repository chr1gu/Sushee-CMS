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

// "special" file url
/*if (strpos($path, 'file/') === 0) {
    $args = explode('/', $path);
    $id = $args[1];
    $file = $args[2];
    include_once dirname(__FILE__) . '/admin/api/file.php';
    return;
}*/

$path = str_replace('/', '.', $path);
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

// TODO: where...

require_once dirname(__FILE__) . '/../src/admin.modules.php';

$adminModule = new AdminModules();
$module = $adminModule->getModuleById($viewData['module']);
$data = $adminModule->getData($module, $fields);

// legacy project tweaks for api inconsistency..
if (isset($viewData['version']) && $viewData['version'] === 0.9) {
    $imageId = null;
    // provide 'url' param for image types
    foreach ($module['fields'] as $field) {
        if ($field['type'] === 'image')
            $imageId = $field['id'];
    }
    if ($imageId) {
        foreach ($data as $key => $value) {
            //$data[$key]['id'] = (int)$data[$key]['id'];
            $data[$key][$imageId]['width'] = 320;
            $data[$key][$imageId]['height'] = 240;
            $data[$key][$imageId]['url'] .= '&width=320&height=240';
            $data[$key]['url'] = $value[$imageId]['url'];
        }
    }
    // directly return data without success flag..
    return print (json_encode($data));
}
// ..............................................

$response = array(
    'success' => true,
    //'module' => $module,
    'data' => $data
);

return print (json_encode($response));

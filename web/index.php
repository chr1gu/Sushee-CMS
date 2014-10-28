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

// remove trailing slash
$path = $path = rtrim($path, '/');
// replace slashes because all views are stored in the same folder
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
    $youtubeId = null;
    // provide 'url' param for image types
    foreach ($module['fields'] as $field) {
        if ($field['type'] === 'image')
            $imageId = $field['id'];
        if ($field['type'] === 'youtube')
            $youtubeId = $field['id'];
    }
    if ($imageId) {
        foreach ($data as $key => $value) {
            $data[$key][$imageId]['width'] = 320;
            $data[$key][$imageId]['height'] = 240;
            $data[$key][$imageId]['url'] .= '&width=320&height=240';
            $data[$key]['url'] = $value[$imageId]['url'];
        }
    }
    if ($youtubeId) {
        foreach ($data as $key => $value) {
            $url = $value[$youtubeId]['url'];
            $image = $data[$key][$youtubeId]['image'];
            $v = preg_replace('/http(s?)\:\/\/www\.youtube\.com\/watch\?v\=/', '', $url);
            $data[$key][$youtubeId]['url'] = $image . '&width=320&height=240';
            $data[$key][$youtubeId]['image'] = '';
            $data[$key]['url'] = $v;
        }
    }
    // directly return data without success flag..
    return print (json_encode($data));
}
// ..............................................

// display data as array
if (isset($viewData['data-output']) && $viewData['data-output'] === 'array') {
    if ($module['single']) {
        $data = array_values($data);
    } else {
        $newData = array();
        for ($i=0;$i<count($data);$i++) {
            $newData = array_merge($newData, array_values($data[$i]));
        }
        $data = $newData;
    }
}

$response = array(
    'success' => true,
    //'module' => $module,
    'data' => $data
);

return print (json_encode($response));

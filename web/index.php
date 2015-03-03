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
$limit = (int)filter_input(INPUT_GET, "limit");
$page = filter_input(INPUT_GET, "page");
if (!$page)
    $page = 0;
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


// handle form submission here:
if (!empty($_POST)) {
    // pre-check
    if (!isset($module['form']) || !isset($module['form']['receiver_subject']) || !isset($module['form']['receiver_message'])) {
        $response = array('success' => false);
        $response['message_title'] = "Konfigurationsfehler";
        $response['message'] = "Die Nachricht konnte nicht gesendet werden. Versuch es doch einfach nochmal oder schick uns eine Mail an support@sushee.ch";
        return print (json_encode($response));
    }
    $message = $module['form']['receiver_message'];
    $headers = '';
    if (isset($module['form']['sender'])) {
        $headers = 'From: ' . $module['form']['sender'] . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
    }
    foreach ($_POST as $key => $value) {
        if ($message) {
            $message .= "\n\n";
        }
        $message .= $key . ":\n" . $value;
    };
    $message .= "\n" . $module['form']['receiver_message_footer'];
    $subject = $module['form']['receiver_subject'];
    $subject = str_replace('{DATETIME}', date("d.m.y H:i"), $subject);
    $message = str_replace('{DATETIME}', date("d.m.y H:i"), $message);

    mail($module['form']['receiver'], $subject, $message, $headers);

    $response = array(
        'success' => true,
        'message_title' => $module['form']['success']['message_title'],
        'message' => $message
    );
    return print (json_encode($response));
}
//


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
/*if (isset($viewData['data-output']) && $viewData['data-output'] === 'array') {
    if ($module['single']) {
        unset($data['created_at']);
        unset($data['updated_at']);
        unset($data['id']);
        $data = array_values($data);
    } else {
        $newData = array();
        for ($i=0;$i<count($data);$i++) {
            $newData = array_merge($newData, array_values($data[$i]));
        }
        $data = $newData;
    }
}*/

if ($limit > 0) {
    $totalCount = count($data);
    $data = array_slice($data, $limit * $page, $limit);
}
$response = array(
    'success' => true,
    //'module' => $module,
    'data' => $data
);
if ($limit > 0) {
    $response['hasMore'] = $totalCount > ($limit*($page + 1));
    $response['limit'] = $limit;
    $response['page'] = $page;
}
return print (json_encode($response));

<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 11/10/14
 * Time: 20:47
 */
require_once dirname(__FILE__) . '/../../../src/admin.login.php';

$login = new AdminLogin();
$user = $login->getSessionUser();
if (!$user) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Keine Berechtigung.'
    )));
}

$url = filter_input(INPUT_GET, 'url');
if (!$url) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'URL nicht vorhanden.'
    )));
}

$dataDir = dirname(__FILE__) . '/../../../data/modules/';
$id = filter_input(INPUT_GET, 'data-id');
if (!$id) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Daten-ID nicht vorhanden'
    )));
}

$fieldId = filter_input(INPUT_GET, 'field-id');
if (!$fieldId) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Field-ID nicht vorhanden'
    )));
}

$module = filter_input(INPUT_GET, 'id');
if (!$module) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Module-ID nicht vorhanden'
    )));
}

if (!is_dir($dataDir)) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Datenverzeichnis konnte nicht gelesen werden'
    )));
}
$moduleDir = $dataDir . $module . '/';
if (!is_dir($moduleDir)) {
    mkdir($moduleDir);
}
if (!is_dir($dataDir)) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Uploadverzeichnis konnte nicht gelesen werden'
    )));
}



$matches = array();
$youtubeContent = file_get_contents($url);
preg_match_all('/<meta property="og:image" content="(.*)">/i', $youtubeContent, $matches);
if (count($matches) < 2 || !count($matches[1])) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'og:image nicht gefunden.'
    )));
}

$youtubeImageUrl = $matches[1][0];
$youtubeImage = file_get_contents($youtubeImageUrl);

// TODO: save image check uplaod..
$temp = explode(".", basename($youtubeImageUrl));
$extension = end($temp);
$filePath = $dataDir . $module . '/' . $id . '_' . $fieldId . '.' . $extension;
$thumbnails = $dataDir . $module . '/' . $id . '_' . $fieldId . '.resized-*';
// remove already existing file
if (file_exists($filePath)) {
    unlink($filePath);
}
// remove thumbnails
foreach (glob($thumbnails) as $thumbnail) {
    unlink($thumbnail);
}

$basename = basename($filePath);
file_put_contents($filePath, $youtubeImage);

return print (json_encode(array(
    'success' => true,
    'url' => './api/file.php?id=' . $module . '&file=' . $basename,
    'name' => $basename
)));

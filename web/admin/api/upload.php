<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 05/10/14
 * Time: 11:48
 */

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

$maxFilesize = 8000000;
if ($_FILES["file"]["size"] >= $maxFilesize) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Datei ist zu gross: ' . formatBytes($_FILES["file"]["size"]) . '. Max. ' . formatBytes($maxFilesize) . ' erlaubt'
    )));
}

if ((($_FILES["file"]["type"] !== "image/gif")
    && ($_FILES["file"]["type"] !== "image/jpeg")
    && ($_FILES["file"]["type"] !== "image/jpg")
    && ($_FILES["file"]["type"] !== "image/pjpeg")
    && ($_FILES["file"]["type"] !== "image/x-png")
    && ($_FILES["file"]["type"] !== "image/png"))
) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Mime-Typ ' . $_FILES["file"]["type"] . ' nicht erlaubt'
    )));
}

$allowedExts = array("gif", "jpeg", "jpg", "png");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);

if (!in_array($extension, $allowedExts)) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Dateiendung .' . $extension . ' nicht erlaubt'
    )));
}

if ($_FILES["file"]["error"] > 0) {
    return print (json_encode(array(
        'success' => false,
        'error' => $_FILES["file"]["error"]
    )));
}

//$dataFile = $dataDir . $id . '.json';
// double-check if file exists before saving the file
//if (!is_file($dataFile)) {
//    return print (json_encode(array(
//        'success' => false,
//        'error' => $id . ' nicht gefunden'
//    )));
//}

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

move_uploaded_file($_FILES["file"]["tmp_name"], $filePath);
$basename = basename($filePath);

return print (json_encode(array(
    'success' => true,
    'url' => './api/file.php?id=' . $module . '&file=' . $basename,
    'name' => $basename
)));

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

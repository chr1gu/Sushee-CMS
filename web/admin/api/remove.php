<?php

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

$module = filter_input(INPUT_GET, 'id');
if (!$module) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Module-ID nicht vorhanden'
    )));
}

$moduleDir = $dataDir . $module . '/';

if (!is_dir($moduleDir)) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Modul Verzeichnis konnte nicht gelesen werden'
    )));
}

$fieldId = filter_input(INPUT_GET, 'field-id');
if (!$fieldId) {
    // delete module data
    $filePatern = $moduleDir. $id . '{.,_}*';
} else {
    // delete field data
    $filePatern = $moduleDir. $id . '_' . $fieldId . '*';
}

foreach (glob($filePatern, GLOB_BRACE) as $file) {
    unlink($file);
}

return print (json_encode(array(
    'success' => true
)));

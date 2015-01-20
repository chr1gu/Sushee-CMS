<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 13/01/15
 * Time: 15:23
 */

$controller = dirname(__FILE__) . '/../../../' . filter_input(INPUT_GET, "controller");
$response = null;

header('Content-Type: application/json');

try {
    if (is_file($controller))
        $response = include($controller);
} catch (Exception $e) {
    //
}

if (!$response) {
    return print (json_encode(array(
        'success' => true,
        'html' => '<div class="row"><h2>Fehler</h2><h4>Das Modul liefert keinen Rückgabewert</h4></div>'
    )));
}
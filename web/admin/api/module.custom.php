<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 13/01/15
 * Time: 15:23
 */

$controller = dirname(__FILE__) . '/../../../' . filter_input(INPUT_GET, "controller");
$javascript = str_replace('.php', '.js', $controller);

$response = null;
header('Content-Type: application/json');

function injectJS($response, $javascript) {
    if (isset($response['html']) && is_file($javascript))
        $response['html'] .= '<script type="text/javascript">' . file_get_contents($javascript) . '</script>';
    return $response;
}

try {
    if (is_file($controller))
        $response = include($controller);

    $response = injectJS($response, $javascript);
    print json_encode($response);

} catch (Exception $e) {
    //
}

if (!$response) {
    return print (json_encode(array(
        'success' => true,
        'html' => '<div class="row"><h2>Fehler</h2><h4>Das Modul liefert keinen Rückgabewert</h4></div>'
    )));
}
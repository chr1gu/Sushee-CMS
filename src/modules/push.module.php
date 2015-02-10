<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 05/10/14
 * Time: 15:46
 */

function return_me($me) { return $me; }
// todo: check security shizzle.. we shouldn't send around the id via get params
$filters = array
(
    'parse-application-id' => FILTER_SANITIZE_ENCODED,
    'parse-rest-api-key' => FILTER_SANITIZE_ENCODED,
    'push-pages' => array(
        'filter' => FILTER_CALLBACK,
        'options' => 'return_me',
    ),
);

$input = filter_input_array(empty($_POST) ? INPUT_GET : INPUT_POST, $filters);
if (empty($input['push-pages']) || empty($input['parse-application-id']) || empty($input['parse-rest-api-key']))
{
    return array(
        'success' => false,
        'html' => 'Das Modul wurde noch nicht korrekt konfiguriert!'
    );
}


// form submission
if (!empty($_POST)) {
    $message = filter_input(INPUT_POST, 'message');
    $pageId = filter_input(INPUT_POST, 'pageId');
    return sendPush($message, $pageId, $input['parse-application-id'], $input['parse-rest-api-key']);
}

function sendPush($message, $pageId, $parseApplicationId, $parseRestApiKey) {
    if (empty($message)) {
        return array(
            'success' => false,
            'error' => 'Die Mitteilung ist leer'
        );
    }
    $data = array(
        'data' => array(
            'alert' => $message,
            'channels' => new stdClass(),
            'pageId' => $pageId
        ),
        'where' => new stdClass()
        //'where' => array(
            //'deviceType' => 'ios'
        //)
    );
    $data_string = json_encode($data);
    $ch = curl_init('https://api.parse.com/1/push');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    //curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string),
            'X-Parse-Application-Id: ' . $parseApplicationId,
            'X-Parse-REST-API-Key: ' . $parseRestApiKey
    ));

    $result = curl_exec($ch);
    if (empty($result)) {
        return array(
            'success' => false,
            'error' => 'Der Push-Service ist momentan nicht erreichbar'
        );
    }
    $response = json_decode($result, true);
    if (empty($response)) {
        return array(
            'success' => false,
            'error' => 'Der Push-Service ist momentan nicht erreichbar'
        );
    }
    if (isset($response['error'])) {
        return array(
            'success' => false,
            'error' => $response['error']
        );
    }
    return array(
        'success' => true,
        'message' => 'Nachricht "'.$message.'" erfolgreich abgeschickt!'
    );
}

$push_pages = '';
$selectField = '';
if (!empty($input['push-pages'])) {
    $selectField .= '<div class="picker" style="margin-left:0;"><select class="select" name="pageId">';
    foreach ($input['push-pages'] as $index => $value) {
        $push_pages .= '<input type="hidden" name="push-pages[' . $index . '][label]" value="'.$value['label'].'" />';
        $push_pages .= '<input type="hidden" name="push-pages[' . $index . '][id]" value="'.$value['id'].'" />';
        $selectField .= '<option value="' . $value['id'] . '">' . $value['label'] . '</option>';
    }
    $selectField .= '</select></div>';
}

$formAction = "api/module.custom.php?controller=src/modules/push.module.php";
$html = '<form id="push-form" action="' . $formAction . '" method="post" class="row">
<h2 style="padding-bottom: 5px;">Push Nachrichten</h2>
<ul>
<li><span class="alert-container">&nbsp;</span></li>
<li class="field"><h4>Ziel</h4>' . $selectField . '</li>
<li class="field"><h4>Nachricht</h4><textarea class="input textarea" name="message" placeholder=""></textarea></li>
</ul>
<div style="padding-top: 20px;">
<div class="medium primary rounded btn pull_right">
<input type="hidden" name="parse-rest-api-key" value="' . $input['parse-rest-api-key'] . '"/>
<input type="hidden" name="parse-application-id" value="' . $input['parse-application-id'] . '"/>
' . $push_pages . '
<input type="submit" value="Abschicken"/>
</div>
</div>
</form>';



if (empty($input['parse-rest-api-key']) || empty($input['parse-application-id']) || empty($input['']))

return array(
    'success' => true,
    'html' => $html
);


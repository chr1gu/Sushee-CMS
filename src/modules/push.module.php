<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 05/10/14
 * Time: 15:46
 */

// form submission
if (!empty($_POST)) {
    $message = filter_input(INPUT_POST, 'message');
    sendPush($message);
    return print (json_encode(array(
        'success' => true,
        'html' => $message
    )));
}

function sendPush($message) {
    $data = array(
        'data' => array(
            'alert' => $message,
            'channels' => array()
        ),
        'where' => array(
            //'deviceType' => 'ios'
        )
    );
    $data_string = json_encode($data);
    $ch = curl_init('https://api.parse.com/1/push');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string),
            'X-Parse-Application-Id: ' . '2HfPAu3pBM9M0hn531XCtBEiQIxHkLyplLrDlHl1',
            'X-Parse-REST-API-Key: ' . 'u6DZhOZYyNdcwxdlVoHIA5qCxblWHiTnINhoEsFL'
        )
    );

    $result = curl_exec($ch);
    //var_dump($result);
    //die;

}

$selectField = '';
$selectField .= '<div class="picker" style="margin-left:0;"><select class="select" name="">';
$selectField .= '<option value="">' . '-- Bitte auswählen --' . '</option>';
$selectField .= '<option value="">' . 'Kontakt' . '</option>';
$selectField .= '</select></div>';


$formAction = "api/module.custom.php?controller=src/modules/push.module.php";
$html = '<form action="' . $formAction . '" method="post" class="row">
<h2 style="padding-bottom: 5px;">Push Notification</h2>
<ul>
<li><span></span><input type="hidden" name="id" value="info"><input type="hidden" name="data-id" value="1418658684"></li>
<li class="field"><h4>Page</h4>' . $selectField . '</li>
<li class="field"><h4>Text</h4><textarea class="input textarea" name="message" placeholder=""></textarea></li>
</ul>
<div style="padding-top: 20px;">
<div class="medium primary rounded btn pull_right">
<input type="submit" value="Abschicken">
</div>
</div>
</form>';

return print (json_encode(array(
    'success' => true,
    'html' => $html
)));


<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 30/09/14
 * Time: 21:08
 */
require_once dirname(__FILE__) . '/../../../src/admin.login.php';

$username = '';
$password = '';
if (!empty ($_GET)) {
    if (isset($_GET['username'])) $username = $_GET['username'];
    if (isset($_GET['password'])) $password = $_GET['password'];
}
if (!empty ($_POST)) {
    if (isset($_POST['username'])) $username = $_POST['username'];
    if (isset($_POST['password'])) $password = $_POST['password'];
}
//sleep(2);

$admin = new AdminLogin();
$user = $admin->getUser($username);
$isValid = $admin->isValidUser($username, $password);

header('Content-Type: application/json');

if (!$user) {
    return print (json_encode(array(
        'success' => false,
        'warning' => array(
            '.username'
        )
    )));
}

if (!$isValid) {
    $admin->handleLoginFailure($user);
}

if ($admin->isLocked($username)) {
    return print (json_encode(array(
        'success' => false,
        'error' => 'Benutzer vorübergehend gesperrt!',
        'warning' => array(
            '.username'
        )
    )));
}

if (!$isValid) {
    return print (json_encode(array(
        'success' => false,
        'warning' => array(
            '.password'
        )
    )));
}

$admin->handleLoginSuccess($username);
$admin->addSession($user);

return print (json_encode(array(
    'success' => true,
    'user' => array (
        'display_name' => $user['display_name'],
        'name' => $user['name'],
        'role' => $user['role'],
    )
)));

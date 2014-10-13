<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 30/09/14
 * Time: 20:52
 */
class AdminLogin
{
    protected $dataDir;
    protected $sessionDir;
    protected $users = array();
    // login attempts until the account gets locked
    public $loginAttempts = 3;
    // how long is the account locked (Seconds)
    public $lockTimeout = 600;

    function __construct ()
    {
        session_start();
        $this->dataDir = dirname(__FILE__) . '/../data/';
        $this->sessionDir = $this->dataDir . 'sessions/';
        $usersPath = $this->dataDir . 'config/users.json';
        if (file_exists($usersPath)) {
            $usersDataRaw = file_get_contents($usersPath);
            $usersData = json_decode($usersDataRaw, true);
            $this->loginAttempts = $usersData['login-attempts'];
            $this->lockTimeout = $usersData['lock-timeout'];
            $this->users = $usersData['users'];
        }
    }

    function getUsers () {
        return $this->users;
    }

    function getUser ($username)
    {
        foreach ($this->users as $user) {
            if ($user['name'] === $username) {
                return $user;
            }
        }
    }

    function getSessionUser ()
    {
        return !empty ($_SESSION) && isset ($_SESSION['user']) ? $_SESSION['user'] : false;
    }

    function isLocked ($username)
    {
        $lockFile = $this->sessionDir . $username . '.lock';
        if (is_file($lockFile)) {
            $session = json_decode(file_get_contents($lockFile), true);
            $lastLogin = strtotime($session['last-login']);
            $now = strtotime(date('r'));
            if (((int)$session['attempt']) > $this->loginAttempts && $lastLogin > ($now - $this->lockTimeout)) {
                return true;
            }
        }
        return false;
    }

    function handleLoginFailure ($user)
    {
        $lockFile = $this->sessionDir . $user['name'] . '.lock';
        $data = array(
            'user' => $user['name'],
            'attempt' => 1,
            'last-login' => date('r')
        );
        if (is_file($lockFile)) {
            $session = json_decode(file_get_contents($lockFile), true);
            $data['attempt'] = ((int)$session['attempt'])+1;
        }
        file_put_contents($lockFile, json_encode($data));
    }

    function handleLoginSuccess ($username)
    {
        $lockFile = $this->sessionDir . $username . '.lock';
        if (file_exists($lockFile)) {
            unlink ($lockFile);
        }
    }

    function isValidUser ($username, $password)
    {
        $user = self::getUser($username);
        if ($user) {
            $salt = $user['salt'];
            $hash = hash ('sha256', $salt . $password);
            return $hash === $user['hash'];
        }
    }

    function addSession($user)
    {
        $_SESSION['user'] = $user;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 30/09/14
 * Time: 20:52
 */
class ApplicationTestBase
{
    // directories
    protected $srcDir;
    protected $dataDir;
    protected $configDir;
    protected $sessionDir;

    // urls
    protected $adminUrl;

    // controllers
    protected $adminLogin;
    protected $adminModules;
    protected $adminOverview;

    public function __construct()
    {
        $this->setupPaths ();
        $this->setupControllers();
    }
    
    public function setupPaths ()
    {
        $this->srcDir = dirname(__FILE__) . '/../';
        $this->dataDir = $this->srcDir . '../data/';
        $this->configDir = $this->dataDir . '/config/';
        $this->sessionDir = $this->dataDir . '/sessions/';

        $protocol = 'http';
        $host = filter_input(INPUT_SERVER, 'HTTP_HOST');
        $request_uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $complete_uri = $protocol. '://' . $host . $request_uri;
        $this->adminUrl = pathinfo($complete_uri, PATHINFO_DIRNAME) . '/';
    }
    
    public function setupControllers ()
    {
        require_once $this->srcDir . 'admin.login.php';
        require_once $this->srcDir . 'admin.overview.php';
        require_once $this->srcDir . 'admin.modules.php';

        $this->adminLogin = new AdminLogin ();
        $this->adminModules = new AdminModules();
        $this->adminOverview = new AdminOverview(
            $this->getTestUser(),
            $this->adminModules->getModules()
        );
    }
    
    public function getTestUser ()
    {
        $users = $this->adminLogin->getUsers();
        return $users[0];
    }
    
    public function test ($name)
    {
        $action = 'test' . ucfirst($name);
        if(is_callable(array($this, $action))) {
            try {
                $result = $this->$action();
                $success = $result ? $result : false;
                return is_bool($success) ? array (
                    'success' => $success
                ) : $success;
            } catch (Exception $e) {
                return array (
                    'success' => false,
                    'message' => $e->getMessage()
                );
            }
        }
        return array (
            'success' => false,
            'message' => $action . ' nicht vorhanden'
        );
    }
    
    public function getTests ()
    {
        return $this->availableTests;
    }
    
    protected function getDocumentFromHtml ($html)
    {
        if (empty($html)) {
            throw new Exception("Html konnte nicht geladen werden.");
        }
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        try {
            $doc->loadHTML($html);
        } catch (Exception $e) {
            throw new Exception("Html Lesefehler: " . trim($e->getMessage()));
        }
        // internal error handling
        foreach (libxml_get_errors() as $error) {
            // ignore html exceptions for the moment and try to parse the doc..
            // throw new Exception("Html Lesefehler: " . trim($error->message));
        }
        libxml_clear_errors();
        return $doc;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 30/09/14
 * Time: 20:52
 */
require_once 'application.test.base.php';

class ApplicationTest extends ApplicationTestBase
{
    protected $availableTests;
    
    public function __construct ()
    {
        parent::__construct();

        $this->availableTests = array (
            array (
                'name' => 'Benutzer vorhanden?',
                'description' => 'Mindestens ein Benutzer muss angelegt werden, um das Backend zu nutzen.',
                'test' => 'userAvailability'
            ),
            array (
                'name' => 'User-Verzeichnis schreibbar?',
                'description' => 'Das Benutzerverzeichnis muss schreibbar sein, damit Benutzer-Logins gesperrt werden können.',
                'test' => 'userDirectory'
            ),
            array (
                'name' => 'Testbenutzer laden',
                'description' => 'Testbenutzer können geladen werden',
                'test' => 'dummyUser'
            ),
            array (
                'name' => 'Testmodule laden',
                'description' => 'Testmodule können geladen werden',
                'test' => 'dummyModules'
            ),
            array (
                'name' => 'Module vorhanden',
                'description' => 'Mindestens ein Modul sollte konfiguriert werden.',
                'test' => 'moduleAvailability'
            ),
            array (
                'name' => 'Login mit Testbenutzer',
                'description' => 'Anmeldung mit einem Testbenutzer funktioniert.',
                'test' => 'loginUser'
            ),
            array (
                'name' => 'Benutzer sperren',
                'description' => 'Beim mehreren, fehlerhaften Anmeldeversuchen wird der Benutzer gesperrt.',
                'test' => 'lockUser'
            ),
            array (
                'name' => 'Admin-Panel: Auflistung der Module',
                'description' => 'Die Module werden in der Seitennavigation aufgelistet.',
                'test' => 'modulesNavigation'
            ),
            array (
                'name' => 'Admin-Panel: Modul Listenansicht ohne Daten',
                'description' => 'Die Listenansicht eines Moduls wird korrekt angezeigt.',
                'test' => 'moduleListviewWithoutData'
            ),
            array (
                'name' => 'Admin-Panel: Modul Listenansicht mit Daten',
                'description' => 'Die Listenansicht eines Moduls wird korrekt angezeigt.',
                'test' => 'moduleListviewWithData'
            ),
            array (
                'name' => 'Admin-Panel: Modul Detailansicht',
                'description' => 'Die Detailansicht eines Moduls wird korrekt angezeigt.',
                'test' => 'moduleDetailview'
            ),
            array (
                'name' => 'Admin-Panel: Neues Element erstellen',
                'description' => 'Ein neues Element kann erstellt werden.',
                'test' => 'addElement'
            ),
            array (
                'name' => 'JSON-Api: Element Auflistung',
                'description' => 'Die bestehenden Elemente sind via Api abrufbar.',
                'test' => 'apiModuleList'
            ),
            array (
                'name' => 'JSON-Api: Element Details',
                'description' => 'Die Details von einem Element sind via Api abrufbar.',
                'test' => 'apiModuleDetail'
            ),
            array (
                'name' => 'Admin-Panel: Element bearbeiten',
                'description' => 'Ein existierendes Element kann bearbeitet -und gespeichert werden.',
                'test' => 'editElement'
            ),
            array (
                'name' => 'Admin-Panel: Pflichtfelder',
                'description' => 'Wenn ein Feld als Pflichtfeld markiert wurde, dann muss dies ein Wert enthalten andernfalls wird eine Fehlermeldung angezeigt.',
                'test' => 'mandatoryFields'
            ),
            array (
                'name' => 'Admin-Panel: Gesperrtes Element anzeigen',
                'description' => 'Beim Öffnen eines gesperrten Elements erscheint eine Warnung.',
                'test' => 'showLockedElement'
            ),
            array (
                'name' => 'Admin-Panel: Gesperrtes Element speichern',
                'description' => 'Beim Speichern eines gesperrten Elements erscheint eine Warnung.',
                'test' => 'saveLockedElement'
            ),
            array (
                'name' => 'Admin-Panel: Element löschen',
                'description' => 'Ein Element kann gelöscht werden.',
                'test' => 'deleteElement'
            ),
            array (
                'name' => 'Admin-Panel: Gelöschtes Element speichern',
                'description' => 'Beim Versuch, ein bereits gelöschtes Element zu speichern, muss eine Warnung angezeigt werden und das Dokument als neues Dokument gespeichert werden.',
                'test' => 'saveDeletedElement'
            ),
            array (
                'name' => 'Benutzer Logout',
                'description' => 'Der Benutzer kann sich abmelden.',
                'test' => 'logout'
            ),
            array (
                'name' => 'W3C / DOM Validierung',
                'description' => 'Das generierte HTML ist korrekt validiert und weist keine Fehler auf.',
                'test' => 'w3c'
            ),
            array (
                'name' => 'Backup erstellen',
                'description' => 'Backup kann erstellt werden',
                'test' => 'backup'
            ),
            array (
                'name' => 'Verlinkte Module Testen',
                'description' => 'Jedes vorhandene Modul nochmal in vollem Umfang testen',
                'test' => 'moduleTODO'
            ),
        );
    }
    
    protected function testUserAvailability ()
    {
        $users = $this->adminLogin->getUsers();
        if (count($users) > 0) {
            return array (
                'success' => true,
                'message' => count($users) . ' Benutzer'
            );
        }
        return array (
            'success' => false,
            'message' => 'Kein Benutzer'
        );
    }

    protected function testUserDirectory ()
    {
        $testFile = $this->sessionDir . 'test';
        touch($testFile);
        if (!is_file($testFile))
            return false;
        unlink($testFile);
        if (is_file($testFile))
            return false;
        return true;
    }

    protected function testModuleAvailability ()
    {
        $num = $this->adminModules->getNumModules();
        if ($num > 0) {
            return array (
                'success' => true,
                'message' => $num . ' Module'
            );
        }
        return array (
            'success' => false,
            'message' => 'Kein Modul'
        );
    }
    
    protected function testLoginUser ()
    {
        
    }

    protected function testLockUser ()
    {
        $testuser = $this->getTestUser();
        $username = $testuser['name'];
        if ($this->adminLogin->isLocked($username)) {
            return array(
                'success' => false,
                'message' => 'Benutzer (' . $username . ') bereits gesperrt'
            );
        }
        $authUrl = $this->adminUrl . 'api/authenticate.php?username=' . $username;
        for ($i = 0; $i < $this->adminLogin->loginAttempts + 1; $i++) {
            $response = file_get_contents($authUrl);
            $responseData = json_decode($response, true);
            if ($responseData['success'] === false && isset($responseData['error'])) {
                // user locked.. unlock again:
                $this->adminLogin->handleLoginSuccess($username);
                if ($this->adminLogin->isLocked($username)) {
                    return array(
                        'success' => false,
                        'message' => 'Benutzer (' . $username . ') immernoch gesperrt'
                    );
                }
                return true;
            }
        }
        return false;
    }

    protected function testModulesNavigation ()
    {
        $template = $this->adminOverview->getTemplate();
        $doc = $this->getDocumentFromHtml($template);
        $xpath = new DOMXpath($doc);
        $elements = $xpath->query('//ul[@class="side-navigation"]/li');
        $modules = $this->adminModules->getModules();
        if ($elements->length != count($modules)) {
            return array(
                'success' => false,
                'message' => $elements->length .' von '.count($modules) . ' Module gefunden'
            );
        }
        for ($i=0; $i<$elements->length; $i++) {
            $label = trim($elements->item($i)->nodeValue);
            if ($modules[$i]['name'] !== $label) {
                return array(
                    'success' => false,
                    'message' => 'Modulname stimmt nicht überein'
                );
            }
        }
        return true;
    }
}

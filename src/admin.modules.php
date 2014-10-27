<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 01/10/14
 * Time: 21:59
 */
class AdminModules
{
    protected $modules = array();
    protected $modulesDir;

    function __construct ()
    {
        $dataDir = dirname(__FILE__) . '/../data/';
        $this->modulesDir = $dataDir . 'modules/';
        $modulePath = $dataDir . 'config/modules.json';
        if (file_exists($modulePath)) {
            $moduleDataRaw = file_get_contents($modulePath);
            $moduleData = json_decode($moduleDataRaw, true);
            $this->modules = $this->getVerifiedModules($moduleData);
        }
    }

    function getModules ()
    {
        return $this->modules;
    }

    function getModuleById ($id)
    {
        if (!$id) {
            return $this->modules[0];
        }
        foreach ($this->modules as $module) {
            if ($module['id'] === $id) {
                return $module;
            }
        }
    }

    public function getData ($module, $fields = null, $dataId = "*")
    {
        $data = array ();
        $fields = is_array($fields) ? $fields : array();
        $dataPattern = $this->modulesDir . $module['id'] . '/' . $dataId . '.json';
        foreach (glob($dataPattern) as $filename) {
            $contentRaw = file_get_contents($filename);
            $content = json_decode($contentRaw, true);
            $id = basename($filename, '.' . pathinfo($filename, PATHINFO_EXTENSION));
            if (!empty($fields)) {
                $filteredContent = array ();
                foreach ($content as $index => $value) {
                    if (in_array($index, $fields)) {
                        $filteredContent[$index] = $value;
                    }
                }
                $filteredContent['id'] = $id;
                $filteredContent['created_at'] = isset($content['created_at']) ? $content['created_at'] : time();
                $filteredContent['updated_at'] = isset($content['updated_at']) ? $content['updated_at'] : time();
                $content = $filteredContent;
            } else {
                $content['id'] = $id;
            }

            // loop through fields and add extra info which is not stored in the file
            // e.g. a file path
            foreach ($module['fields'] as $field) {
                if (isset($content[$field['id']])) {
                    if ($field['type'] === 'image') {
                        $val = $content[$field['id']];
                        $content[$field['id']] = array(
                            'name' => $val,
                            'url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/admin/api/file.php?id=' . $module['id'] . '&file=' . $val
                        );
                    } else if ($field['type'] === 'youtube') {
                        $val = $content[$field['id']];
                        $val['image'] = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/admin/api/file.php?id=' . $module['id'] . '&file=' . $val['name'];
                        $content[$field['id']] = $val;
                    }
                } else if(empty($fields)) {
                    // add new or empty fields
                    $content[$field['id']] = '';
                }
            }

            $data[] = $content;

        }
        return $data;
    }
    
    public function setData ($module, $data)
    {
        $dataDirectory = $this->modulesDir . $module['id'];
        $dataPattern = $dataDirectory . '/' . $data['data-id'] . '.json';
        if (!is_dir($this->modulesDir)) {
            mkdir($this->modulesDir);
        }
        if (!is_dir($dataDirectory)) {
            mkdir($dataDirectory);
        }
        if (is_file($dataPattern)) {
            // file exists
            $existingData = json_decode(file_get_contents($dataPattern), true);
            $existingData['updated_at'] = time();
            foreach ($module['fields'] as $field) {
                $id = $field['id'];
                // empty value based on type of field
                // this could be an empty string, a false boolean, an empty array or a zero
                $empty = "";
                $existingData[$id] = (isset($data[$id]) ? $data[$id] : $empty);
            }
            file_put_contents($dataPattern, json_encode($existingData));
            return $existingData;
        } else {
            // new file
            $filteredData = array();
            foreach ($module['fields'] as $field) {
                $id = $field['id'];
                // empty value based on type of field
                // this could be an empty string, a false boolean, an empty array or a zero
                $empty = "";
                $filteredData[$id] = (isset($data[$id]) ? $data[$id] : $empty);
            }
            $filteredData['created_at'] = time();
            $filteredData['updated_at'] = time();
            file_put_contents($dataPattern, json_encode($filteredData));
            return $filteredData;
        }
    }

    function getListDataForModule ($module)
    {
        $data = array ();
        if (!$module || !isset($module['id']) || !isset($module['list-fields']) || !is_array($module['list-fields']))
            return $data;

        return $this->getData($module, $module['list-fields']);
    }

    function getListFieldsForModule ($module)
    {
        if (!$module || empty($module['list-fields'])) {
            return array();
        }
        $fields = $module['fields'];
        $listFields = $module['list-fields'];
        $validFields = array();
        foreach ($listFields as $listField) {
            foreach ($fields as $field) {
                if ($listField === $field['id']) {
                    $validFields[] = $field;
                }
            }
        }
        return $validFields;
    }

    function getVerifiedModules ($modules)
    {
        $verifiedModules = array();
        foreach ($modules as $module)
        {
            if (!isset($module['name']) || empty($module['name']))
                continue;

            if (!isset($module['custom-module'])) {
                if (!isset($module['id']) || empty($module['id']))
                    continue;
                if (!isset($module['fields']) || empty($module['fields']))
                    continue;

                // cleanup fields
                $module['id'] = $this->normalize($module['id']);
                $module['fields'] = $this->getVerifiedFields($module['fields']);
                if (empty($module['id']))
                    continue;
                if (empty($module['fields']))
                    continue;
            } else {
                //$module['single'] = true;
                continue;
            }

            // all tests passed, add..
            $verifiedModules[] = $module;
        }
        return $verifiedModules;
    }

    function normalize ($string)
    {
        $string = strtolower($string);
        return preg_replace("([^\w\s\d\-_~,;:\[\]\(\].]|[\. ])", '', $string);
    }

    function getVerifiedFields ($fields)
    {
        $verifiedFields = array();
        foreach ($fields as $field) {
            if (!isset($field['id']) || empty($field['id']))
                continue;
            if (!isset($field['name']) || empty($field['name']))
                continue;
            if (!isset($field['type']) || empty($field['type']))
                continue;
            // all tests passed, add..
            $verifiedFields[] = $field;
        }
        return $verifiedFields;
    }

    function getNumModules () {
        return count($this->modules);
    }
}

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
        // loop through data
        $data = array ();
        $dataPattern = $this->modulesDir . $module['id'] . '/' . $dataId . '.json';
        $files = glob($dataPattern);
        foreach ($files as $filename)
        {
            $contentRaw = file_get_contents($filename);
            $content = json_decode($contentRaw, true);
            $data[] = $this->getDataRecord($module, $content, $this->getId($filename));
        }
        // for single modules, always return one item
        if ($module['single']) {
            if (count($data) > 0)
                return array($data[0]);
            else
                $data[] = $this->getDataRecord($module, array(), $this->getId());
        }
        return $data;
    }

    protected function getId($filename = null)
    {
        if ($filename != null) {
            $base = basename($filename);
            $withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $base);
            return $withoutExt;
        }
        return time();
    }

    protected function getDataRecord($module, $data, $id = null)
    {
        $apiHost = 'http://' . $_SERVER['HTTP_HOST'] . '/admin/api';
        $fields = $module['fields'];
        $record = array();
        $fieldData = array();
        $data = !empty($data) ? $data : array();
        foreach ($fields as $field) {
            if (isset($data[$field['id']])) {
                $value = $data[$field['id']];
                // inject youtube previews
                if ($field['type'] === 'youtube' && isset($data[$field['id'] . '_preview'])) {
                    $field['preview'] = $data[$field['id'] . '_preview'];
                }
                if ($field['type'] === 'image') {
                    $field['preview'] =  $apiHost . '/file.php?id=' . $module['id'] . '&file=' . $value;
                }
                //if (is_array($value)) {
                //    return array_merge($field, $value);
                //} else {
                    $field['value'] = $value;
                //}
            } else {
                $field['value'] = "";
            }
            $fieldData[] = $field;
        }
        $record['id'] = $id !== null ? $id : $this->getId();
        $record['created_at'] = isset($data['created_at']) ? $data['created_at'] : time();
        $record['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : time();
        $record['fields'] = $fieldData;
        return $record;
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
            for ($i = 0; $i<count($module['fields']); $i++) {
                $field = $module['fields'][$i];
                $id = $field['id'];
                if (isset($data[$id]))
                    $existingData[$id] = $data[$id];
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

    function getVerifiedModules ($modules)
    {
        $verifiedModules = array();
        foreach ($modules as $module)
        {
            // custom modules
            if (isset($module['name']) && !empty($module['name']) && isset($module['controller']) && is_file(dirname(__FILE__) . '/../' . $module['controller'])) {
                $verifiedModules[] = $module;
                continue;
            }

            // regular modules
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

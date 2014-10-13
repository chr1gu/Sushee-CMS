<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 30/09/14
 * Time: 20:52
 */
class AdminOverview
{
    protected $webDir;
    protected $user;
    protected $modules;

    function __construct ($user, $modules)
    {
        $this->user = $user;
        $this->modules = $modules;
        $this->webDir = dirname(__FILE__) . '/../web/';
    }

    function getTemplate()
    {
        $template = file_get_contents($this->webDir . 'admin/overview.html');
        $template = str_replace('{DisplayName}', $this->user['display_name'], $template);
        //$template = preg_replace('/<ul class="side-navigation">(.*)<\/ul>/s', $this->getSideNavigationHtml (), $template);
        $template = str_replace('{navigation-items}', $this->getSideNavigationItems (), $template);
        return $template;
    }

    function getSideNavigationItems ()
    {
        $html = '';
        foreach ($this->modules as $module) {
            $single = $module['single'] ? "true" : "false";
            $icon = isset($module['icon']) ? $module['icon'] : 'icon-plus';
            $html .= '<li><a href="#" module-single="' . $single . '" module-id="' . $module['id'] . '"><i class="' . $icon . '"></i><span>' . $module['name'] . '</span></a></li>';
        }
        return $html;
    }
}

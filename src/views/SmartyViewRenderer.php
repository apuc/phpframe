<?php

namespace framework\views;

use Smarty;
use framework\App;

class SmartyViewRenderer extends AbstractViewRenderer
{
    public function render($filename, $attributes)
    {
        $smarty = new Smarty();
        $smarty->setTemplateDir($this->viewsPath);
        $smarty->setCompileDir($this->cachePath);

        if (array_key_exists('escape_html', $this->options)) {
            $smarty->setEscapeHtml((bool)$this->options['escape_html']);
        }

        if (array_key_exists('minify_html', $this->options) && (bool)$this->options['minify_html']) {
            $smarty->registerFilter('output', ['\framework\View\SmartyViewRenderer', 'minifyHtml']);
        }

        $pluginsPaths = App::getConfigValue('pluginsPaths', [], $this->options);
        array_walk($pluginsPaths, function ($path) use ($smarty) {
            $smarty->addPluginsDir($path);
        });

        $smarty->assign($attributes);
        $smarty->display($filename);
    }

    public static function minifyHtml($tpl_output, \Smarty_Internal_Template $template)
    {
        $tpl_output = preg_replace('![\t ]*[\r\n]+[\t ]*!', '', $tpl_output);
        return $tpl_output;
    }
}

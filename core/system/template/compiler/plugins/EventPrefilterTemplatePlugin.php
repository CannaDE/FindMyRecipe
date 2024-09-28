<?php
namespace fmr\system\template\compiler\plugins;

use fmr\system\template\compiler\TemplateScriptingCompiler;
use fmr\TimeMonitoring;

class EventPrefilterTemplatePlugin implements PrefilterTemplatePluginInterface {

    /**
     * @inheritDoc
     */
    public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler) {
        $ldq = preg_quote($compiler->getLeftDelimiter(), '~');
        $rdq = preg_quote($compiler->getRightDelimiter(), '~');

        return preg_replace_callback(
            "~{$ldq}event\\ name\\=\\'([\\w]+)\\'{$rdq}~",
            static function ($match) use ($templateName) {
                return TimeMonitoring::getTPL()->getTemplateListenerCode($templateName, $match[1]);
            },
            $sourceContent
        );
    }
}
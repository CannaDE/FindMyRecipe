<?php

namespace fmr\system\template\compiler\plugins;

use fmr\system\template\compiler\TemplateScriptingCompiler;

/**
 * Prefilters are used to process the source of the template immediately before compilation.
 */
interface PrefilterTemplatePluginInterface {
    /**
     * Executes this prefilter.
     *
     * @param string $templateName
     * @param string $sourceContent
     * @param TemplateScriptingCompiler $compiler
     * @return  string
     */
    public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler);
}
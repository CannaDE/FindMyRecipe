<?php

namespace fmr\system\template\compiler\plugins;

use fmr\system\template\compiler\TemplateScriptingCompiler;

/**
 * Compiler functions are called during the compilation of a template.
 */
interface CompilerTemplatePluginInterface {
    /**
     * Executes the start tag of this compiler function.
     *
     * @param array $tagArgs
     * @param TemplateScriptingCompiler $compiler
     * @return  string
     */
    public function executeStart($tagArgs, TemplateScriptingCompiler $compiler): string;

    /**
     * Executes the end tag of this compiler function.
     *
     * @param TemplateScriptingCompiler $compiler
     * @return  string
     */
    public function executeEnd(TemplateScriptingCompiler $compiler): string;
}
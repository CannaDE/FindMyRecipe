<?php

namespace fmr\system\template\compiler\plugins;



use fmr\system\template\TemplateEngine;

/**
 * Template functions are identical to template blocks, but they have no closing tag.
 */
interface FunctionTemplatePluginInterface {
    /**
     * Executes this template function.
     *
     * @param array $tagArgs
     * @param TemplateEngine $tplObj
     * @return  string
     */
    public function execute($tagArgs, TemplateEngine $tplObj);
}
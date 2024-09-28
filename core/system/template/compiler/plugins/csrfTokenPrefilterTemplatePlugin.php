<?php

namespace fmr\system\template\compiler\plugins;


use fmr\system\template\compiler\TemplateScriptingCompiler;

/**
 * See CsrfTokenFunctionTemplatePlugin.
 *
 */
class CsrfTokenPrefilterTemplatePlugin implements PrefilterTemplatePluginInterface {
    /**
     * @inheritDoc
     */
    public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler)
    {
        $getToken = '$__core->session->getSecurityToken()';

        return strtr($sourceContent, [
            '{csrfToken type=raw}' => \sprintf('{@%s}', $getToken),
            '{csrfToken type=url}' => \sprintf('{@%s|rawurlencode}', $getToken),
            '{csrfToken}' => sprintf('<input type="hidden" name="x" class="xsrfTokenInput" value="{@%s}">', $getToken),
        ]);
    }
}
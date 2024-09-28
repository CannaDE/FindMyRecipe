<?php
namespace fmr\system\template\compiler\plugins;

use fmr\system\template\compiler\TemplateScriptingCompiler;
use fmr\system\exception\template\TemplateCompilerException;

/**
 * Template compiler plugin which assigns a certain value to a template variable.
 *
 * Usage:
 *  {assign var=name value="foo"}
 */
class AssignCompilerTemplatePlugin implements CompilerTemplatePluginInterface {

    /**
     * @inheritDoc
     */
    public function executeStart($tagArgs, TemplateScriptingCompiler $compiler): string {
        if(!isset($tagArgs['var'])) {
            throw new TemplateCompilerException(
                $compiler::formatSyntaxError(
                    'missing \'var\' argument in {assign} tag',
                    $compiler->getCurrentIdentifier(),
                    $compiler->getCurrentLineNo()
                )
            );
        }
        if(!isset($tagArgs['value'])) {
            throw new TemplateCompilerException(
                $compiler::formatSyntaxError(
                    'missing \'value\' argument in {assign} tag',
                    $compiler->getCurrentIdentifier(),
                    $compiler->getCurrentLineNo()
                )
            );
        }
        return "<?php \$this->assign(" . $tagArgs['var'] . ", " . $tagArgs['value'] . "); ?>";
    }

    /**
     * @inheritDoc
     */
    public function executeEnd(TemplateScriptingCompiler $compiler): string {
        throw new TemplateCompilerException(
            $compiler::formatSyntaxError(
                'unknown close {/assign}',
                $compiler->getCurrentIdentifier(),
                $compiler->getCurrentLineNo()
            )
        );
    }
}
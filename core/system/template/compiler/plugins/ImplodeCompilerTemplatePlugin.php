<?php

namespace fmr\system\template\compiler\plugins;

use fmr\system\exception\SystemException;
use fmr\system\template\compiler\TemplateScriptingCompiler;
use fmr\util\StringUtil;

/**
 * Template compiler plugin which joins array elements to a string.
 *
 * Usage:
 *  {implode from=$array key=bar item=foo glue=";"}{$foo}{/implode}
 */
class ImplodeCompilerTemplatePlugin implements CompilerTemplatePluginInterface
{
    /**
     * local tag stack
     * @var string[]
     */
    protected array $tagStack = [];

    /**
     * @inheritDoc
     */
    public function executeStart($tagArgs, TemplateScriptingCompiler $compiler): string
    {
        $compiler->pushTag('implode');

        if (!isset($tagArgs['from'])) {
            throw new SystemException(
                $compiler::formatSyntaxError(
                    "missing 'from' argument in implode tag",
                    $compiler->getCurrentIdentifier(),
                    $compiler->getCurrentLineNo()
                )
            );
        }
        if (!isset($tagArgs['item'])) {
            throw new SystemException(
                $compiler::formatSyntaxError(
                    "missing 'item' argument in implode tag",
                    $compiler->getCurrentIdentifier(),
                    $compiler->getCurrentLineNo()
                )
            );
        }

        $glue = $tagArgs['glue'] ?? "', '";
        $itemVar = \mb_substr($tagArgs['item'], 0, 1) != '$' ? "\$this->tplVars[" . $tagArgs['item'] . "]" : $tagArgs['item'];
        $keyVar = null;
        if (isset($tagArgs['key'])) {
            $keyVar = \mb_substr($tagArgs['key'], 0, 1) != '$' ? "\$this->tplVars[" . $tagArgs['key'] . "]" : $tagArgs['key'];
        }
        $hash = StringUtil::getRandomID();
        $this->tagStack[] = ['hash' => $hash, 'glue' => $glue, 'itemVar' => $itemVar, 'keyVar' => $keyVar];

        $phpCode = "<?php\n";
        $phpCode .= "\$_length" . $hash . " = count(" . $tagArgs['from'] . ");\n";
        $phpCode .= "\$_i" . $hash . " = 0;\n";
        $phpCode .= "\$_item" . $hash . " = {$itemVar} ?? null;\n";
        $phpCode .= "\$_key" . $hash . " = " . ($keyVar ? "{$keyVar} ?? " : "") . " null;\n";
        $phpCode .= "foreach (" . $tagArgs['from'] . " as " . ($keyVar ? $keyVar . " => " : '') . $itemVar . ") { ?>";

        return $phpCode;
    }

    /**
     * @inheritDoc
     */
    public function executeEnd(TemplateScriptingCompiler $compiler): string {
        $compiler->popTag('implode');
        $tagArgs = \array_pop($this->tagStack);

        // Close the foreach loop
        $phpCode = "<?php\n";
        $phpCode .= "if (++\$_i" . $tagArgs['hash'] . " < \$_length" . $tagArgs['hash'] . ") { echo " . $tagArgs['glue'] . "; }\n";
        $phpCode .= "}\n";

        // Unset item and key and restore previous values
        $phpCode .= "unset({$tagArgs['itemVar']});";
        $phpCode .= "{$tagArgs['itemVar']} = \$_item" . $tagArgs['hash'] . ";\n";
        if ($tagArgs['keyVar']) {
            $phpCode .= "unset({$tagArgs['keyVar']});";
            $phpCode .= "{$tagArgs['keyVar']} = \$_key" . $tagArgs['hash'] . ";\n";
        }

        // Unset temporary variables
        $phpCode .= "unset(\$_length" . $tagArgs['hash'] . ", \$_i" . $tagArgs['hash'] . ");\n";
        $phpCode .= "unset(\$_item" . $tagArgs['hash'] . ", \$_key" . $tagArgs['hash'] . ");\n";
        $phpCode .= "?>";

        return $phpCode;
    }
}
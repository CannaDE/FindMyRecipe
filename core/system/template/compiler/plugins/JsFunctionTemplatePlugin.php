<?php
namespace fmr\system\template\compiler\plugins;

use fmr\system\exception\template\TemplateCompilerException;
use fmr\system\template\TemplateEngine;
use fmr\util\StringUtil;
use fmr\FindMyRecipe;

class JsFunctionTemplatePlugin implements FunctionTemplatePluginInterface {

    /**
     * list of included JavaScript files
     * @var array
     */
    protected array $includedFiles = [];
    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj) {
        //cfwDebug(debug_backtrace());
        if(empty($tagArgs['file']) && empty($tagArgs['lib']))
            throw new TemplateCompilerException(
                'missing argument in {js} template tag',
                'missing \'<strong>file</strong>\' or \'<strong>lib</strong>\' argument in <strong>{js}</strong> template tag',
                str_replace('\\', '/', $tplObj->getSourceFilename($tplObj->tplVars['templateName']))
            );

        $isJQuery = false;
        if(isset($tagArgs['lib']) && ($tagArgs['lib'] === 'jquery' || $tagArgs['lib'] === 'jquery-ui' || $tagArgs['lib'] === 'bootstrap') && empty($tagArgs['file'])) {
            $tagArgs['bundle'] = '';
            $isJQuery = true;
        }

        $source = 'js/';
        if(!empty($tagArgs['bundle']) && !ENABLE_DEV_MODE) {
            $source .= $tagArgs['bundle'];
        } elseif(!empty($tagArgs['lib'])) {
            if($isJQuery) {
                $source .= '3rdParty/' . $tagArgs['lib'];
            } else {
                $source .= $tagArgs['lib'];
                if(!empty($tagArgs['file'])) {
                    $source .= '/' . $tagArgs['file'];
                }
            }
        } else {
            $source .= $tagArgs['file'];
        }

        if(isset($this->includedFiles[$source]))
            return '';

        $this->includedFiles[$source] = true;


        if(!empty($tagArgs['hasTiny']) && $tagArgs['hasTiny'] === true)
            $source .= '.tiny';

        $source .= '.min';
        $source .= '.js?t=' . LAST_UPDATE_TIME;

        $relocate = (!isset($tagArgs['core']) || $tagArgs['core'] !== 'true');
        $html = '<script' . ($relocate ? ' data-relocate="true"' : '') . ' src="' . $source . '"></script>' . "\n";

        if(isset($tagArgs['encodeJs']) && $tagArgs['encodeJs'] === true) {
            $html = StringUtil::encodeJS($html);
        }
        return $html;
    }
}
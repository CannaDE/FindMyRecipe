<?php
namespace fmr\system\template;

use cfw\system\event\EventHandler;
use fmr\Singleton;
use fmr\system\template\compiler\TemplateCompiler;
use fmr\system\exception\template\TemplateEngineException;
use fmr\util\HeaderUtil;
use fmr\util\StringUtil;

class TemplateEngine extends Singleton {

    /**
     * directory used to cache compiled templates
     * @var string
     */
    public string $cacheDir = '';

    /**
     * language id used to specific language versions of compiled templates
     * @var int
     */
    public int $languageID = 0;

    /**
     * directoy used as template source
     * @var string
     */
    public string $templateDirs = '';

    /**
     * namespace template modifiers and plugins
     * @var string
     */
    public string $pluginNamespace = '';

    /**
     * active template compiler object
     * @var string
     */
    protected $tplCompiler;

    /**
     * all template variables assigned during runtime
     * @var array
     */
    public array $tplVars = [];

    /**
     * contains all templates with assigned template listeners.
     * @var string[][][]
     */
    protected array $templateEvents = [];

    /**
     * true, if template listener code was already loaded
     * @var bool
     */
    protected bool $templateEventsLoaded = false;

    /**
     * forces the template engine to recompile all included templates
     * @var bool
     */
    protected bool $forceCompile = false;

    private array $prefilters = [];

    public array $pluginObjects = [];

    public array $foreachVars = [];

    public array $tagStack = [];


    protected function init()
    {
        $this->templateDirs = BASE_DIR.'templates/';
        $this->pluginNamespace = 'fmr\system\template\compiler\plugins';
        $this->cacheDir = BASE_DIR.'templates/cached/';

        $this->assignDefaultVariables();
    }

    protected function assignDefaultVariables() {
        $this->tplVars['tpl'] = [];

        $this->tplVars['tpl']['template'] = '';
        $this->tplVars['tpl']['incTemplates'] = '';

        $this->tplVars['tpl']['section'] = $this->tplVars['tpl']['foreach'] = $this->tplVars['tpl']['capture'] = [];
    }

    public function assignVar($variable, $value = '') {
        if(is_array($variable)) {
            foreach($variable as $key => $value) {
                if(empty($key))
                    continue;

                $this->assignVar($key, $value);
            }
        } else {
            $this->tplVars[$variable] = $value;
        }
    }

    public function assign($variable, $value = '') {
        return $this->assignVar($variable, $value);
    }

    public function displayTemplate($templateName, $sendHeaders = true) {
        if($sendHeaders) {
            HeaderUtil::sendHeaders();

            //EventHandler::getInstance()->fireAction($this, "beforeDisplayTemplate");
        }

        $sourceFilename = $this->getSourceFilename($templateName);
        $compiledFilename = $this->getCompiledFilename($templateName);
        $metaDataFilename = $this->getMetaDataFilename($templateName);
        $metaData = $this->getMetaData($metaDataFilename);

        //if template compiled
        if($metaData === null || !$this->isCompiled($templateName, $sourceFilename, $compiledFilename, $metaData)) {

            //compile template
            $this->compileTemplate($templateName, $sourceFilename, $compiledFilename, [
                'data' => $metaData,
                'filename' => $metaDataFilename
            ]);
        }

        include($compiledFilename);

        if($sendHeaders) {
            //EventHandler::getInstance()->fireAction($this, "afterDisplayTemplate");
        }

    }

    public function fetchTemplate(string $templateName, array $variables = []): bool|string
    {

        if(!empty($variables)) {
            $this->tplVars = array_merge($this->tplVars, $variables);
        }

        try {
            ob_start();
            $this->displayTemplate($templateName);
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        return $output;
    }

    public function fetchString(string $compiledSource, array $variables = []): string {

        if(!empty($variables))
            $this->tplVars = array_merge($this->tplVars, $variables);

        ob_start();
        eval("?>" . $compiledSource);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    public function getSourceFilename($templateName): string
    {
        $sourceFilename = $this->getPath($templateName);
        if(!empty($sourceFilename)) {
            return $sourceFilename;
        }
        else throw new TemplateEngineException("Template '" . $templateName . "' not found in " . str_replace('\\', '/', $this->templateDirs) . $templateName . ".tpl", 404);
    }

    public function getPath($templateName): ?string {
        if(!empty($this->templateDirs)) {
            $path = $this->templateDirs.$templateName.".tpl";

            if(file_exists($path)) {
                return $path;
            }
        } return null;
    }

    /**
     * Returns the absolute filename of a compiled template.
     *
     * @param string $templateName
     * @return  string
     */
    public function getCompiledFilename($templateName): string {
        return $this->cacheDir . "fmr_core_" . $this->languageID . "_" . $templateName .".php";
    }

    /**
     * Returns the absolute filename for template's meta data.
     *
     * @param string $templateName
     * @return  string
     */
    public function getMetaDataFilename(string $templateName): string {
        return $this->cacheDir . "fmr_core_" . $this->languageID . "_" . $templateName .".metaData.php";
    }

    /**
     * Returns the class name of a plugin.
     *
     * @param string $type
     * @param string $tag
     * @return  string
     */
    public function getPluginClassName(string $type, string $tag): string {
        return $this->pluginNamespace . "\\" . StringUtil::firstCharToUpperCase($tag) . StringUtil::firstCharToUpperCase(\mb_strtolower($type)) . 'TemplatePlugin';
    }

    /**
     * Returns true if the template with the given data is already compiled.
     *
     * @param string $templateName
     * @param string $sourceFilename
     * @param string $compiledFilename
     * @param array $metaData
     * @return  bool
     */
    public function isCompiled(string $templateName, string $sourceFilename, string $compiledFilename, array $metaData): bool {

        if($this->forceCompile ||!file_exists($compiledFilename)) {
            return false;
        } else {

            $sourceTime = filemtime($sourceFilename);
            $compiledTime = filemtime($compiledFilename);

            if($sourceTime >= $compiledTime) {
                return false;
            } else {

                if(!empty($metaData['include'])) {
                    foreach($metaData['include'] as $includedTemplates) {
                        foreach($includedTemplates as $includedTemplate) {
                            $incTemplateFileName = $this->getSourceFilename($includedTemplate);
                            $incTime = filemtime($incTemplateFileName);

                            if($incTime >= $compiledTime) {
                                return false;
                            }
                        }
                    }
                }
                return true;
            }
        }
    }

    /**
     * Compiles a template.
     *
     * @param string $templateName
     * @param string $sourceFileName
     * @param string $compiledFileName
     * @param array $metaData
     */
    protected function compileTemplate(string $templateName, string $sourceFileName, string $compiledFileName, array $metaData) {
        $content = $this->getSourceContent($sourceFileName);

        //compile template
        $this->getCompiler()->compile($templateName, $content, $compiledFileName, $metaData);
    }

    /**
     * Returns the template compiler.
     *
     * @return TemplateCompiler
     */
    public function getCompiler(): TemplateCompiler {
        if($this->tplCompiler === null) {
            $this->tplCompiler = new TemplateCompiler($this);
        }

        return $this->tplCompiler;
    }

    /**
     * Returns an array with all prefilters.
     *
     * @return  array[]
     */
    public function getPrefilters(): array {
        return $this->prefilters;
    }

    /**
     * Registers prefilters.
     *
     * @param string[] $prefilters
     */
    public function registerPrefilter(array $prefilters) {
        foreach ($prefilters as $name) {
            $this->prefilters[$name] = $name;
        }
    }

    /**
     * Removes a prefilter by its internal name.
     *
     * @param string $name internal prefilter identifier
     */
    public function removePrefilter(string $name) : void {
        unset($this->prefilters[$name]);
    }

    /**
     * Reads the content of a template file.
     *
     * @param string $sourceFileName
     * @return  string
     * @throws  TemplateEngineException
     */
    public function getSourceContent(string $sourceFileName): string {
        $content = '';
        if(!file_exists($sourceFileName) || (($content = @file_get_contents($sourceFileName)) === false )) {
            throw new TemplateEngineException(sprintf("Could not open template '%s' for reading", $sourceFileName));
        } else {
            return $content;
        }
    }
    /**
     * Returns the absolute filename for template's meta data.
     *
     * @param string $fileName
     * @return  array|null
     */
    protected function getMetaData(string $fileName): ?array {
        if(!file_exists($fileName) || !is_readable($fileName)) {
            return null;
        }

        $content = file_get_contents($fileName);

        $pos = strpos($content, "\n");
        if($pos === false) {
            return null;
        }

        $content = substr($content, $pos + 1);

        try {
            $data = unserialize($content);

            if(!is_array($data)) {
                return null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return $data;
    }

    /**
     * Returns template events.
     *
     * @param string $templateName
     * @param string $eventName
     * @return  string
     */
    public function getTemplateListenerCode(string $templateName, string $eventName): string {
        $this->loadTemplateListenerCode();

        if(isset($this->templateEvents[$templateName][$eventName])) {
            return implode("\n", $this->templateEvents[$templateName][$eventName]);
        }
        return '';
    }

    protected function loadTemplateListenerCode() {
        if(!$this->templateEventsLoaded) {
            //$this->templateEvents = TemplateEventsCacheBuilder::getInstance()->getData();
            //$this->templateEventsLoaded = true;
        }
    }
}
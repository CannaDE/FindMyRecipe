<?php
namespace {

    use fmr\FindMyRecipe;

    error_reporting(E_ALL);
    // set php error handler
    set_error_handler([FindMyRecipe::class, "handle"]);
    // set exception handler
    set_exception_handler([FindMyRecipe::class, 'handleExceptions']);
    // set shutdown function
    //register_shutdown_function([TimeMonitoring::class, "destruct"]);

    if (PHP_VERSION_ID >= 82000) {
        @ini_set('zend.exception_ignore_args', 0);
        @ini_set('zend.exception_string_param_max_len', 25);
    }
    @ini_set('assert.exception', 1);


    #[NoReturn] function cfwDebug(): void
    {
        // Basic CSS styling for simple readability (white background, black text)
        echo <<<HTML
        <style>
            .debug-container { font-family: Arial, sans-serif; font-size: 14px; background-color: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; color: #000; }
            .debug-header { font-size: 16px; font-weight: bold; color: #000; margin-bottom: 5px; }
            .debug-arg { margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9; border-radius: 5px; }
            .debug-type { font-size: 12px; color: #555; margin-left: 5px; }
            .debug-object, .debug-array { display: block; margin-top: 5px; }
            .toggle-btn { cursor: pointer; color: #007BFF; font-size: 12px; margin-top: 5px; display: none; }
            .file-info { margin-top: 10px; font-style: italic; font-size: 12px; color: #333; }
            hr { border: 0; height: 1px; background-color: #eee; margin: 10px 0; }
        </style>
    
        <script>
            function toggleVisibility(id) {
                var element = document.getElementById(id);
                if (element.style.display === "none") {
                    element.style.display = "block";
                } else {
                    element.style.display = "none";
                }
            }
        </script>
    HTML;
    
        echo "<div class='debug-container'>";
    
        $args = func_get_args();
        $length = count($args);
    
        if ($length === 0) {
            echo "<div class='debug-header'>ERROR: No arguments provided.</div><hr>";
        } else {
            for ($i = 0; $i < $length; $i++) {
                $arg = $args[$i];
                $type = gettype($arg);
                echo "<div class='debug-arg'><span class='debug-header'>Argument {$i} (<span class='debug-type'>{$type}</span>)</span><hr>";
    
                if (is_array($arg) || is_object($arg)) {
                    $id = "debug_" . uniqid();
    
                    // Automatically open if it's an array or object
                    echo "<div class='debug-object' id='{$id}'>";
                    echo "<pre>" . print_r($arg, true) . "</pre>";
                    echo "</div>";
                } else {
                    echo "<pre>" . var_export($arg, true) . "</pre>";
                }
    
                echo "</div>";
            }
        }
    
        $backtrace = debug_backtrace();
        echo "<div class='file-info'>cfwDebug() called in {$backtrace[0]['file']} on line {$backtrace[0]['line']}</div>";
        echo "</div>";
    
        exit;
    }
}
namespace fmr\system\exception {

    use fmr\util\FileUtil;
    
    function formatFilePath(string $path, int $lineNumber): string {
        $path = FileUtil::unifyDirSeparator($path);
        [
                'dirname' => $dirname,
            'basename' => $basename
        ] = pathinfo($path);

        preg_match('/(?:framework)([^"]*)/', $dirname, $matches);

        if(!empty($matches))
            return sprintf(
                '../%s/<strong>%s</strong>:<strong>%s</strong>',
                StringUtil::encodeHTML($matches[0]),
                StringUtil::encodeHTML($basename),
                $lineNumber
            );

        return $path . ':' . $lineNumber;

    }

    function throwableShow($e) {

        if($e instanceof UserException) {
            $e->show();
            exit();   
        }

        $exceptionClassName = mb_substr(get_class($e), mb_strrpos(get_class($e), '\\'));
        $exceptionClassName = (str_contains($exceptionClassName, "\\")) ? substr($exceptionClassName, 1, strlen($exceptionClassName)) : $exceptionClassName;
        include('_exception.php');
        exit();
    }

    
}
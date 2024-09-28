<!DOCTYPE HTML>
<html lang="de">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <title>Fatal Error</title>



    <style>
        /* desktop */
        @media (min-width: 768px) {
            .exceptionBoundary, .exceptionContent {
                margin: 0 auto;
                max-width: 1400px;
                min-width: 1200px;
                padding: 0 10px;
            }

            .exceptionSystemInformation .exceptionFieldValue {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }

        .exceptionInlineCode {
            border-radius: 3px;
            font-family: monospace;
            padding: 3px 10px;
        }

        .bg-exception {
            background-color: #DA4453 !important;
        }

        .bg-exceptionInlineCode {
            background-color: rgb(198 30 46 / 70%) !important;
            color: #fff;
        }

        .exceptionStacktraceCounter {
            color: darkgoldenrod !important;
        }




        /* desktop */
        @media (min-width: 768px) {
            .page-title, .content, .accordion {
                margin: 0 auto;
                max-width: 1400px;
                min-width: 1200px;
            }

            .exceptionContent {
                margin-top: 10px;
            }
        }


        .exceptionStacktrace {
            display: block;
            overflow: auto;
        }

        .exceptionStacktraceCall {

        }

        .exceptionSystemInformations,
        .exceptionErrorDetails,
        .exceptionStacktrace,
        .exceptionStacktraceCall,
        .exceptionStacktraceFile {
            list-style-type: none;
        }

        pre.exceptionFieldValue {
            font-size: 14px;
            white-space: pre-wrap;
        }

        .exceptionStacktraceFile,
        .exceptionStacktraceFile span,
        .exceptionStacktraceCall,
        .exceptionStacktraceCall span {
            font-family: Consolas, "Lucida Console", monospace;
            font-size: 14px;
            white-space: nowrap;
            font-weight: 500;
        }

        .exceptionStacktraceFile,
        .exceptionStacktraceFile span {
            font-size: 11px;
            margin-top: -4px;
        }


        .exceptionStacktraceMiddleware {
            padding: 20px 0;
        }

        .exceptionStacktraceMiddleware summary {
            cursor: pointer;
            -webkit-user-select: none;
            user-select: none;
        }

        .exceptionStacktraceMiddleware ul {
            border-left: 5px solid #ccc;
            list-style: none;
            margin-top: 20px;
            padding-left: 15px;
        }

        .exceptionStacktraceSensitiveParameterValue {
            border: 1px dashed #d81b60;
            padding: 2px 5px;
            font-size: 12px !important;
        }

        .exceptionStacktraceCounter,
        .exceptionStacktraceType {
            color: rgb(91, 72, 0);
        }

        .exceptionStacktraceCounter {
            padding: 10px 10px;
            background: rgb(251,251,251);
            background: -moz-linear-gradient(90deg, rgba(251,251,251,1) 0%, rgba(241,241,241,1) 100%);
            background: -webkit-linear-gradient(90deg, rgba(251,251,251,1) 0%, rgba(241,241,241,1) 100%);
            background: linear-gradient(90deg, rgba(251,251,251,1) 0%, rgba(241,241,241,1) 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#fbfbfb",endColorstr="#f1f1f1",GradientType=1);
        }


        .exceptionSystemInformation > li:not(:first-child),
        .exceptionErrorDetails > li:not(:first-child) {
            margin-top: 10px;
        }

        .exceptionErrorDetails li, .exceptionSystemInformation li {
            list-style: none;
        }

        .exceptionFieldTitle .exceptionColon {
            /* hide colon in browser, but will be visible after copy & paste */
            opacity: 0;
        }

        .exceptionFieldValue {
            font-size: 13px;
            min-height: 1em;
        }

        pre.exceptionFieldValue {
            font-size: 13px;
            white-space: pre-wrap;
        }

        ul { padding-left: 0; }
        .tabs-rounded a:first-child { border-top-left-radius: 0; border-bottom-left-radius: 0; }
        .tabs-rounded a:last-child { border-top-right-radius: 0; border-bottom-right-radius: 0; }
        .tab-controls a { background-color: #e3e3e3; }
        .content { background-color: white; }

        .fileExcerpt li.selected {
            background-color: rgba(218, 68, 83, 0.35);
        }

        .bg-exception-btn {
            background: rgb(251,251,251);
            background: linear-gradient(180deg, rgba(251,251,251,1) 0%, rgba(241,241,241,1) 100%);
            border: 1px solid #cecece;
        }
        .badge {
            min-width: 20px;
        }

        .exceptionStacktraceEntry { border-bottom: 1px solid #cecece; }
        .exceptionStacktraceCall > strong {
            color: #00769f;
        }
        .exceptionStacktraceFile { color: #060; }
        .exceptionStacktraceParameter { color: #9f0038; }
        .exception-color-red { color: #a62e2e; }
        .exception-color-purple { color: #9f0038; }
        .exception-color-green { color: #060; }

        .exceptionMenu .active {
            background-image: linear-gradient(to bottom, #283048 22%, #859398  100%) !important;
            color: white !important;
        }

        .exceptionStacktrace, .exceptionErrorDetails, .exceptionRequest { display: none; }
        .show {display: block; }

        .nice_r {
            font: 12px Consolas, "Lucida Console", monospace;
            cursor: default;
        }

        .nice_r .nice_r_c {
            display: block;
            text-decoration: none;
            color: #222;
            padding: 2px;
            white-space: nowrap;
            overflow: hidden;
            -ms-text-overflow: ellipsis;
            -o-text-overflow: ellipsis;
            text-overflow: ellipsis;
        }

        .nice_r a.nice_r_c:hover .nice_r_k,
        .nice_r a.nice_r_c:hover .nice_r_d,
        .nice_r a.nice_r_c:hover .nice_r_d span,
        .nice_r a.nice_r_c:hover .nice_r_p,
        .nice_r a.nice_r_c:hover .nice_r_a,
        .nice_r a.nice_r_c:hover {
            background-color: ##cecece3d;
            color: black;
        }

        .nice_r .nice_r_a {
            color: #000;
        }

        .nice_r .nice_r_ad .nice_r_a {
            opacity: 0.4;
        }

        .nice_r .nice_r_k {
            color: #060;
            font-weight: bold;
        }

        .nice_r .nice_r_d {
            font-size: 11px;
            color: #777;
        }

        .nice_r .nice_r_d span {
            color: #333;
        }

        .nice_r .nice_r_p {
            color: #000;
            font-weight: bold;
        }

        .nice_r .nice_r_v {
            margin-left: 6px;
            padding-left: 6px;
            border-left: 1px dotted #CCC;
            display: none;
        }

        .nice_r .nice_r_ir {
            font-style: italic;
        }

        .nice_r .nice_r_p.nice_r_t_integer,
        .nice_r .nice_r_p.nice_r_t_double {
            color: #F0E;
        }

        .nice_r .nice_r_p.nice_r_t_string {
            color: #E00;
        }

        .nice_r .nice_r_p.nice_r_t_boolean {
            color: #00E;
        }

        .nice_r .nice_r_t_comment {
            color: #080;
        }

        .nice_r .nice_r_m .nice_r_k {
            color: #909;
        }

        .nice_r .nice_r_m .nice_r_ma {
            font-weight: normal;
            color: #777;
        }

        .nice_r .nice_r_m .nice_r_mv {
            color: #00E;
        }

        .nice_r .nice_r_m_public .nice_r_mo,
        .nice_r .nice_r_m_protected .nice_r_mo,
        .nice_r .nice_r_m_private .nice_r_mo,
        .nice_r .nice_r_m_abstract .nice_r_mo,
        .nice_r .nice_r_m_final .nice_r_mo {
            font-weight: normal;
            color: #008;
        }

        .nice_r a.nice_r_c.nice_r_m_constructor .nice_r_k,
        .nice_r a.nice_r_c.nice_r_m_destructor .nice_r_k {
            color: #C02;
        }

        .nice_r .nice_r_m_magic {
            font-style: italic;
        }

        .nice_r .nice_r_m_deprecated {
            text-decoration: line-through;
        }

    </style>

    <script>
        function toggleContent(e) {
            var className = $(e).data("show-class");


            $('.show').each(function() {
                $(this).removeClass("show");
            });
            $('.active').each(function() {
                $(this).removeClass("active");
            });
            $(e).addClass("active");

            $("."+className).addClass("show");


        }
        function nice_r_toggle(pfx, id) {
            var elp = document.getElementById(pfx + '_v' + id);
            var elc = document.getElementById(pfx + '_a' + id);
            if (elp) {
                if (elp.style.display === 'block') {
                    elp.style.display = 'none';
                    if (elc) elc.innerHTML = '&#9658;';
                } else {
                    elp.style.display = 'block';
                    if (elc) elc.innerHTML = '&#9660;';
                }
            }
        }
        document.addEventListener('DOMContentLoaded', (e) => {

            function toggleContent(e) {

            }
            $('.toggleContent').each(function() {
                console.log($(this).data("show-class"));
               $(this).on("click", function() {
                   console.log('text');
               });
            });


        });

    </script>
</head>

<body class="theme-light">


<div id="page">

    <div class="page-content">

        <div class="page-title page-title-small ">
            <i class="fa fa-exclamation-triangle color-white float-start fa-4x m-2" style="padding-right: 20px;" aria-hidden="true"></i>
            <h2><?php use cfw\system\http\request\RequestHandler;
                use cfw\system\util\FileUtil;
                use cfw\system\util\StringUtil;
                use cfw\TimeMonitoring;
                use function cfw\system\exception\fileExcerpt;
                use function cfw\system\exception\sanitizeStacktrace;

                if(EXCEPTION_PRIVACY === 'private') {
                echo "Ein Fehler ist aufgetreten";
            } else {
                echo $e->getMessage();
            } ?></h2>
            <span class="" style="color: #1f1f1f;">Interner Fehlercode: <span class="exceptionInlineCodeWrapper"><span class="exceptionInlineCode bg-exceptionInlineCode"><?php echo $exceptionID ?></span></span></span>

        </div>
        <div class="card header-card" data-card-height="105">
            <div class="card-overlay bg-exception opacity-95"></div>
            <div class="card-overlay dark-mode-tint"></div>
        </div>

        <?php if (EXCEPTION_PRIVACY == 'private'){ ?>
        <div class="exceptionContent">
            <div class="content">
                <h3>Was ist passiert?</h3>
                <div class="divider divider-small bg-exception"></div>
                <div class="mt-2">
                    <p>Leider ist es bei der Verarbeitung zu einem Fehler gekommen und die Ausführung wurde abgebrochen. Falls möglich, leiten Sie bitte den oben stehenden Fehlercode an den Administrator weiter.</p>
                    <p>
                        Administratoren können die vollständige Fehlermeldung mit Hilfe dieses Codes in der Administrationsoberfläche unter „Protokoll » Fehler“ einsehen.
                        Zusätzlich wurden die Informationen in die Protokolldatei <span class="exceptionInlineCodeWrapper hours "><span class="exceptionInlineCod color-red-dark shadow-xs shadow-m badge"><?php echo $logFile ?></span></span> geschrieben und können beispielsweise mit Hilfe eines FTP-Programms abgerufen werden.
                    </p>
                    <p class="color-red-light">Hinweis: Der Fehlercode wird zufällig generiert, erlaubt keinen Rückschluss auf die Ursache und ist daher für Dritte nutzlos.</p>
                </div>
            </div>
        </div>
      <?php }
      else {
          ?>
        <div class="exceptionMenu mt-5">
            <div class="exceptionContent">
                <a href="#" onClick="toggleContent(this)" class="btn btn-m mb-3 rounded-xs text-uppercase font-600 shadow-s bg-exception-btn color-black toggleContent active" data-show-class="exceptionErrorDetails">Exception</a>
                <a href="#" onClick="toggleContent(this)" class="btn btn-m mb-3 rounded-xs text-uppercase font-600 shadow-s bg-exception-btn color-black toggleContent" data-show-class="exceptionStacktrace">Stacktrace <span class="badge bg-exception p-1"><?php echo count($e->getTrace()); ?></span></a>
                <a href="#" onClick="toggleContent(this)" class="btn btn-m mb-3 rounded-xs text-uppercase font-600 shadow-s bg-exception-btn color-black toogleContent" data-show-class="exceptionRequest">Requests</a>
            </div>
        </div>
        <div class="exceptionContent exceptionErrorDetails show">
            <div class="content">
                    <div class="exceptionErrorException p-2">

                        <?php
                        if(method_exists($e, 'getDescription') && $e->getDescription() !== null) {
                            ?>
                            <div class="mt-2 font-15 mb-3"">
                                <p class="exceptionFieldTitle"><span class=" color-red-dark">Error</span><span class="exceptionColon">:</span><br />
                                    <span  class="exceptionInlineCode" style="color: #727272; padding: 3px 3px; background-color: #f0f0f0;"> <?php echo $e->getDescription(); ?></span></p>
                            </div>
                            <?php
                        }
                        ?>

                        <div class="divider divider-small bg-exception mb-3 mt-3"></div>

                        <div class="row">



                            <div class="col-md-2 mt-2">
                                <p class="exceptionFieldTitle"><span class=" color-red-dark">Error Code</span><span class="exceptionColon">:</span><br />
                                    <?php echo ($e->getCode() !== 0) ? $e->getCode() : 'unknown'; ?></p>
                            </div>

                            <div class="col-md-2 mt-2">
                                <p class="exceptionFieldTitle"><span class=" color-red-dark">Error Type</span><span class="exceptionColon">:</span><br />
                                    <span class="exceptionFieldValue"><?php echo $exceptionClassName ?></span></p>
                            </div>

                            <div class="clearfix"></div>

                            <div class="col-auto mt-2">
                                <p class="exceptionFieldTitle"><span class=" color-red-dark">Error File</span><span class="exceptionColon">:</span><br />
                                    <a href="#" class="exceptionInlineCode" style="color: #727272; padding: 3px 3px; background-color: #f0f0f0;" data-menu="menu-maps"><?php echo \fmr\system\exception\formatFilePath($e->getFile(), $e->getLine()); ?></a></p>
                            </div>

                            <div class="clearfix"></div>

                        </div>
                        <?php
                            if($e instanceof \cfw\system\exception\SystemException) {
                            $i = 1;
                            if(!empty($e->getExtraInformation())) { ?>

                                <h5 class="exceptionTitle color-red-light">Extra Informations</h5>
                                <div class="row">
                                    <?php
                                        foreach($e->getExtraInformation() as $item => $key) {
                                    ?>
                                            <div class="col-md-2">
                                                <p class="exceptionFieldTitle"><span class="color-red-dark"><?php echo $item; ?></span><span class="exceptionColon">:</span><br />
                                                    <?php echo $key; ?></p>
                                            </div>
                                <?php
                                $i++;
                            }
                            ?>
                                    <div class="clearfix"></div>
                                </div>
                        <?php
                        }
                        }

                        ?>


                        <div class="divider divider-small bg-exception mb-3 mt-3"></div>

                        <div class="row ">

                            <?php
                            if(method_exists($e, 'getDescription') && $e->getDescription() !== null) {
                                ?>
                                <div class="col-md-2 mt-2">
                                    <p class="exceptionFieldTitle"><span class=" color-red-dark">Date</span><span class="exceptionColon">:</span><br />
                                        <?php echo date('Y-m-d H:i:s'); ?> Uhr</p>
                                </div>
                                <?php
                            }
                            ?>

                            <div class="col-md-2 mt-2">
                                <p class="exceptionFieldTitle"><span class=" color-red-dark">PHP Version</span><span class="exceptionColon">:</span><br />
                                    <?php echo phpversion(); ?></p>
                            </div>

                            <div class="col-md-2 mt-2">
                                <p class="exceptionFieldTitle"><span class=" color-red-dark">FindMyRecipe Version</span><span class="exceptionColon">:</span><br />
                                    <?php echo FMR_VERSION ?>
                            </div>

                            <div class="col-md-2 mt-2">
                                <p class="exceptionFieldTitle"><span class=" color-red-dark">Fehlercode</span><span class="exceptionColon">:</span><br />
                                    <span href="#" class="exceptionInlineCode" style="color: #727272; padding: 3px 3px; background-color: #f0f0f0;"><?php echo $exceptionID; ?></span>
                            </div>

                            <div class="clearfix"></div>

                            <div class="col-md-2 mt-2">
                                <p class="exceptionFieldTitle"><span class=" color-red-dark">HTTP Referer</span><span class="exceptionColon">:</span><br />
                                    <?php
                                        if(isset($_SERVER['HTTP_REFERER'])) {
                                            echo ($_SERVER['HTTP_REFERER'] === URL) ? "/" : $_SERVER['HTTP_REFERER'];
                                        }
                                     ?></p>
                            </div>

                            <div class="col-md-7 mt-2">
                                <p class="exceptionFieldTitle"><span class=" color-red-dark">Useragent</span><span class="exceptionColon">:</span><br />
                                    <?php echo $_SERVER['HTTP_USER_AGENT']; ?></p>
                            </div>

                        </div>

                        <div class="divider divider-small bg-exception mb-3 mt-3"></div>

                        <ul class="exceptionErrorDetails">
                            <?php
                            $templateContextLines = \cfw\system\exception\getTemplateContextLines($e);
                            if (!empty($templateContextLines)) {
                            ?>
                            <li>
                                <p class="exceptionFieldTitle"><span class=" color-red-dark">Template Context</span><span class="exceptionColon">:</span><br />
                                <pre class="exceptionFieldValue"><?php echo StringUtil::encodeHTML(implode("", $templateContextLines));?></pre></p>
                            </li>
                            <?php
                            }
                            ?>
                        </ul>


                    <div data-bs-parent="#tab-group-1" class="collapse" id="tab-2">

                    </div>


                    <div data-bs-parent="#tab-group-1" class="collapse" id="tab-3">
                        <p class="bottom-0">
                            <pre>
                                <?php print_r(\cfw\system\http\request\RequestHandler::getInstance()->getActiveRequest()); ?>
                        </pre>
                        </p>
                    </div>
                </div>







            </div>



        </div>
    <div class="exceptionContent exceptionStacktrace">
        <div class="content">
            <div class="exceptionBoundary">
                <p class="exceptionSubtitle">Stack Trace</p>
                <div class="mb-0">
                    <ul class="exceptionStacktrace">
                        <?php
                        $trace = sanitizeStacktrace($e);
                        $foundMiddlewareEnd = false;
                        for ($i = 0, $max = count($trace); $i < $max; $i++) {
                        echo "<div class='exceptionStacktraceEntry'>";
                        // The stacktrace is in reverse order, therefore we need to check for
                        // the end of the middleware first.
                        if (\cfw\system\exception\isMiddlewareEnd($trace[$i])) {
                            $foundMiddlewareEnd = true;
                            ?>
                            <li class="exceptionStacktraceMiddleware">
                            <details>
                            <summary>Middleware</summary>
                            <ul>
                            <?php
                        } elseif (\cfw\system\exception\isMiddlewareStart($trace[$i]) && !$foundMiddlewareEnd) {
                            ?>
                            </ul>
                            </details>
                            </li>
                            <?php
                        }
                        ?>
                        <span class="exceptionStacktraceCounter float-start">#<?php echo $i; ?></span>
                        <li class="exceptionStacktraceCall">

                            <?php
                            echo \sprintf(
                                '<strong>%s</strong><span class="exceptionStacktraceType">%s</span>%s(',
                                $trace[$i]['class'],
                                $trace[$i]['type'],
                                $trace[$i]['function'],
                            );
                            echo implode(', ', array_map(function ($item) {
                                switch (gettype($item)) {
                                    case 'integer':
                                    case 'double':
                                        return '<span class="exceptionStacktraceParameter">' . $item . '</span>';
                                    case 'NULL':
                                        return 'null';
                                    case 'string':
                                        return '<span class="exceptionStacktraceParameter">\'' . $item . '</span>' . "'";
                                    case 'boolean':
                                        return $item ? '<span class="exceptionStacktraceParameter">true</span>' : '<span class="exceptionStacktraceParameter">false</false>';
                                    case 'array':
                                        $keys = array_keys($item);
                                        if (count($keys) > 5) return "[ " . count($keys) . " items ]";
                                        return '[ ' . implode(', ', array_map(function ($item) {
                                                return $item . ' => ';
                                            }, $keys)) . ']';
                                    case 'object':
                                        if ($item instanceof \UnitEnum) {
                                            return '<span class="exceptionStacktraceParameter">' . $item::class . '::' . $item->name . '</span>';
                                        }
                                        if ($item instanceof \SensitiveParameterValue) {
                                            return '<span class="exceptionStacktraceSensitiveParameterValue">' . $item::class . '</span>';
                                        }

                                        return '<span class="exceptionStacktraceParameter">' . $item::class . '</span>';
                                    case 'resource':
                                        return 'resource(' . get_resource_type($item) . ')';
                                    case 'resource (closed)':
                                        return 'resource (closed)';
                                }

                                throw new \LogicException('Unreachable');
                            }, $trace[$i]['args']));



                            echo ')';

                            echo '</li>';


                            ?>
                        <li class="exceptionStacktraceFile"><?php echo sprintf("%s:<strong>%s</strong>", StringUtil::encodeHTML($trace[$i]['file']), $trace[$i]['line']); ?></li>
                    </div>



                    <?php
                    }
                    }
                    ?>
                    </ul>
                </div>


            </div>
        </div>
    </div>
</div>





    <!-- footer and footer card-->
    <div class="footer"></div>
</div>
<!-- end of page content-->

<div id="menu-maps" class="menu menu-box-modal rounded-m" style="height: auto;"">
<div class="fileExcerpt m-3" style="border: 1px solid #cecece;">
    <?php echo fileExcerpt($e->getFile(), $e->getLine(), 7); ?>
</div>
    <a href="#" class="mb-3 close-menu btn btn-center-m btn-sm shadow-l rounded-s text-uppercase font-900 bg-green-dark">Awesome</a>
</div>

<div id="menu-stacktrace" class="menu menu-box-modal rounded-m" style="height: 80%;"">
<div class="exceptionStacktraceContainer font-10">
    <div class="exceptionBoundary">
        <p class="exceptionSubtitle">Stack Trace</p>

    </div>
</div>
<a href="#" class="mb-3 close-menu btn btn-center-m btn-sm shadow-l rounded-s text-uppercase font-900 bg-green-dark">Awesome</a>
</div>




</div>
</body>

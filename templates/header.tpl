<!DOCTYPE html>
<html dir="ltr" lang="de">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    {if !$pageTitle|isset}
        {assign var='pageTitle' value=''}
        {if (!$__core->isLandingPage()) && $__core->getActivePage() != null && $__core->getActivePage()->getTitle()}
            {capture assign='pageTitle'}{$__core->getActivePage()->getTitle()}{/capture}
        {/if}
    {/if}

    <title>{if $pageTitle}{@$pageTitle} - {/if}{PAGE_TITLE}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
        <meta name="format-detection" content="telephone=no">


    

    {if !$canonicalURL|empty}
        <link rel="canonical" href="{$canonicalURL}">
    {/if}

    {if !$headContent|empty}
        {@$headContent}
    {/if}

    <script>
        setTimeout(function(){
            let preloader = document.getElementById('preloader')
            if(preloader){literal}{preloader.classList.add('preloader-hide');}{/literal}
                },300);

        //Don't jump on Empty Links
        const emptyHref = document.querySelectorAll('a[href="#"]')
        emptyHref.forEach(el => el.addEventListener('click', e => {
            e.preventDefault();
            return false;
        }));
    </script>

    {js lib='require' hasTiny=false}
    {js file='require.config' hasTiny=false}
    {js file='require.linearExecution' hasTiny=false}

    <script>
    requirejs.config({
        baseUrl: 'js/',
        urlArgs: 't={@LAST_UPDATE_TIME}'
    });

    </script>

    <script data-relocate="true">
        __require_define_amd = define.amd;
        define.amd = undefined;
    </script>

    {js lib='jquery' hasTiny=true}
    {js lib='jquery-ui' hasTiny=false}
    {js lib='bootstrap' hasTiny=false}

    {literal}
    <script data-relocate="true">
        define.amd = __require_define_amd;
        $.holdReady(true);

        //Fix Scroll for AJAX pages.
        if ('scrollRestoration' in window.history) window.history.scrollRestoration = 'manual';

        require(['Bootstrap'], function(Bootstrap) {

            Bootstrap.setup();

        });
    </script>
    {/literal}

    {if $templateName == '__userException'}
        <link rel="stylesheet" href="style/maintenance.css">
        {elseif $templateName == 'botinfo'}
        <link rel="stylesheet" href="style/simple.css">
    {else}
        <link rel="stylesheet" href="style/main_style.css">
    {/if}
    <link rel="stylesheet" href="style/dark.css">
</head>
<body id="page"
      itemscope itemtype="https://schema.org/WebPage"{if !$canonicalURL|empty} itemid="{$canonicalURL}"{/if} data-template="{$templateName}"
      class="page-highlight page">
<div id="preloader"><div class="spinner-border color-highlight" role="status"></div></div>
<span id="pageTop"></span>

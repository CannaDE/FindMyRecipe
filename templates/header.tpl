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


    {if !$allowSpidersToIndexThisPage|isset}
        {if ($allowSpidersToIndexThisPage|empty && ($__core->getActivePage() == null || ($allowSpidersToIndexThisPage|isset && $allowSpidersToIndexThisPage == false)))}
            <meta name="robots" content="noindex">{/if}

    {/if}

    <script>
    setTimeout(function(){
        let preloader = document.getElementById('preloader')
        if(preloader){literal}{preloader.classList.add('preloader-hide');}{/literal}
            },150);

    //Don't jump on Empty Links
    const emptyHref = document.querySelectorAll('a[href="#"]')
    emptyHref.forEach(el => el.addEventListener('click', e => {
        e.preventDefault();
        return false;
    }));
    </script>

    

    {if !$canonicalURL|empty}
        <link rel="canonical" href="{$canonicalURL}">
    {/if}

    {if !$headContent|empty}
        {@$headContent}
    {/if}




    <link rel="stylesheet" type="text/css" href="../style/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="../style/style.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900|Roboto:300,300i,400,400i,500,500i,700,700i,900,900i&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../fonts/css/fontawesome-all.min.css">
    <link rel="manifest" href="../_manifest.json" data-pwa-version="set_in_manifest_and_pwa_js">
    <link rel="apple-touch-icon" sizes="180x180" href="app/icons/icon-192x192.png">

</head>
<body id="tpl_{$templateName}"
      itemscope itemtype="https://schema.org/WebPage"{if !$canonicalURL|empty} itemid="{$canonicalURL}"{/if} data-template="{$templateName}"
      class="theme-dark page-highlight {if $__core->getActivePage() != null && $__core->getActivePage()->cssClassName}{$__core->getActivePage()->cssClassName}{/if}{if !$__pageCssClassName|empty} {$__pageCssClassName}{/if}">

<div id="preloader"><div class="spinner-border color-highlight" role="status"></div></div>
<span id="pageTop"></span>
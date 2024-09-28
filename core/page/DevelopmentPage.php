<?php
namespace fmr\page;

class DevelopmentPage extends AbstractPage implements IAbstractPage {

    public string $templateName = 'home';

    const AVAILABLE_DURING_MAINTENANCE_MODE = true;
}

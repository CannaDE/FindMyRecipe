<?php
namespace fmr\page;

class ImprintPage extends AbstractPage implements IAbstractPage {

    public string $pageTitle = "Impressum";

    const AVAILABLE_DURING_MAINTENANCE_MODE = true;
}

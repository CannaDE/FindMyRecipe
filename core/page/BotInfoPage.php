<?php
namespace fmr\page;

class BotInfoPage extends AbstractPage implements IAbstractPage {

    public string $pageTitle = "BotInfo";

    const AVAILABLE_DURING_MAINTENANCE_MODE = true;
}

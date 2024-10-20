<?php

namespace fmr\action;

use fmr\FindMyRecipe;
use fmr\system\exception\IllegalLinkException;

abstract class SecureAction extends AbstractAction
{
    public function readParameters()
    {
        parent::readParameters();
        $this->checkSecurityToken();
    }

    protected function checkSecurityToken()
    {

    }
}
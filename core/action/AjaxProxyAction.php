<?php

namespace fmr\action;

use fmr\system\exception\AjaxException;

class AjaxProxyAction extends AjaxInvokeAction
{

    protected string $interfaceName = '';

    protected array $objectIds = [];

    protected array $parameters = [];

    public function readParameters()
    {
        if (isset($_POST['objectIds']) && is_array($_POST['objectIds']))
            $this->objectIds = $_POST['objectIds'];

        if (isset($_POST['parameters']) && is_array($_POST['parameters']))
            $this->parameters = $_POST['parameters'];
        return parent::readParameters();

    }

    public function execute()
    {

        try {
            $objectAction = $this->className::getInstance();
            $this->response['returnValues'] = $objectAction->validateAction($this->actionName, $this->objectIds);
        } catch (\Throwable $e) {
            throw new AjaxException($e->getMessage());
        }


        return self::sendResponse();

    }
}
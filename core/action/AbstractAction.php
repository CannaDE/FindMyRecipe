<?php

namespace fmr\action;

use Psr\Http\Message\ResponseInterface;

class AbstractAction implements ActionInterface
{

    public bool $allowSpidersToIndex = false;

    public string $cssClassName = "";

    public string $title = "";

    final public function __construct()
    {
        //do nothing
    }

    public function __run()
    {
        $result = $this->readParameters();

        if ($result instanceof ResponseInterface)
            return $result;

        $result = $this->execute();

        if ($result instanceof ResponseInterface)
            return $result;
    }

    public function readParameters()
    {
        // do nothing
    }

    public function execute()
    {
        //do nothing
    }

    public function executed()
    {
        //do nothing
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
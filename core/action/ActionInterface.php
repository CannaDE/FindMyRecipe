<?php

namespace fmr\action;

interface ActionInterface
{

    public function __run();

    public function readParameters();

    public function execute();

    public function executed();
}
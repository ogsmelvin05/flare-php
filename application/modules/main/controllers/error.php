<?php

namespace Main\Controllers;

use Flare\Application\ErrorController;

class Error extends ErrorController
{
    public function index()
    {
        debug($this->getErrorCode());
        debug($this->getErrorMessage());
    }
}
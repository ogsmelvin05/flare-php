<?php

namespace Main\Controllers;

use Main\Controller;

class Index extends Controller
{
    public function index_action()
    {
        return $this->view('index');
    }
}
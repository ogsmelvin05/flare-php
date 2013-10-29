<?php

namespace Main\Controllers;

use Main\Controller;

class Index_Controller extends Controller
{
    public function index_action()
    {
        return $this->view('index');
    }
}
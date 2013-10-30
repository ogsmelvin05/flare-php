<?php

namespace Main\Controllers;

use Main\Controller;

class Index extends Controller
{
    public function index()
    {
        return $this->view('index');
    }
}
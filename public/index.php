<?php

$appDirectory = dirname(dirname(__FILE__));

require $appDirectory.'/system/flare.php';
use Flare\Flare as F;

F::createApp()->setAppDirectory($appDirectory.'/app')->start();
<?php

//define('ENV', 'dev');
//define('ENV', 'qa');
define('ENV', 'live');

define("APP_PATH", __DIR__);

// core
require("./vendors/thcframe/core/core.php");
THCFrame\Core\Core::initialize();

// plugins

$path = APP_PATH . "/application/plugins";
$iterator = new \DirectoryIterator($path);

foreach ($iterator as $item) {
    if (!$item->isDot() && $item->isDir()) {
        include($path . "/" . $item->getFilename() . "/initialize.php");
    }
}

//module loading

$modules = array('App', 'Admin');
THCFrame\Core\Core::registerModules($modules);

// load services and run dispatcher
THCFrame\Core\Core::run();

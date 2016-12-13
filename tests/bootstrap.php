<?php

if (!ini_get('date.timezone')) {
  date_default_timezone_set('America/New_York');
}

require __DIR__.'/../vendor/autoload.php';

$capsule = new Illuminate\Database\Capsule\Manager();
$capsule->addConnection(require(__DIR__.'/config/database.php'));
$capsule->bootEloquent();
$capsule->setAsGlobal();


$__autoload_paths = array('migrators');
foreach ($__autoload_paths as $path) {
    foreach (glob(__DIR__."/$path/*.php") as $dep) {
        require_once $dep;
    }
}

require __DIR__.'/suite/SlackBotTestCase.php';

<?php
    /**
     * RemotePlayer Project
     * 
     * Server Part
     * 
     * @author  Tianle Xu <xtl@xtlsoft.top>
     * @license GPL-V3
     * @package RemotePlayer
     * 
     */

    namespace RemotePlayer\Temp\Instance;

    require_once "./vendor/autoload.php";
    $conf = require "./config.php";

    $db = new \NonDB\NonDB(\NonDB\NonDB::driver("LocalDriver:./data/"));

    $pushServer = new \RemotePlayer\PushServer($conf['listen']['PushServer'], $conf, $db);
    $registerServer = new \RemotePlayer\RegisterServer($conf['listen']['RegisterServer'], $conf, $db, $pushServer);

    $webInterface = new \RemotePlayer\WebInterface($conf['listen']['WebInterface'], $conf, $db);

    \Workerman\Worker::runAll();
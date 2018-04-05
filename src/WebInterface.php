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

    namespace RemotePlayer;

    class WebInterface {

        protected $worker = null;
        public static $config = [];
        protected $db = null;

        public function __construct(string $uri, array $config, \NonDB\NonDB $db){

            self::$config = $config;
            $this->db = $db;

            $this->worker = new \Workerman\WebServer($uri);
            $this->worker->count = self::$config['server']['count'];
            $this->worker->name = self::$config['server']['namePre'] . "WebInterface";

            $this->worker->addRoot("*", __DIR__ . "/web/");

        }

    }
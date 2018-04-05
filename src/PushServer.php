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

    class PushServer {

        protected $worker = null;
        protected $config = [];
        protected $db = null;
        protected $conns = [];
        protected $seccode = "dh93w8hdn98pwe9&G&(DGpas9bdp9asbD(HDSA(PDH(SAHD{APH*D(ASGD^*ASGXYP*Vcts98aGVX8o9asvX*Vas8xfg98AGX8aGV)&xfas8xgha7*GX08^GS)X*ga08xg60&AX87aGSX*Oagpxg";

        public function __construct(string $uri, array $config, \NonDB\NonDB $db){

            $this->config = $config;
            $this->db = $db;

            $this->worker = new \Workerman\Worker($uri);
            $this->worker->count = $this->config['server']['count'];
            $this->worker->name = $this->config['server']['namePre'] . "PushServer";
            $this->worker->onConnect = [$this, "onConnect"];
            $this->worker->onMessage = [$this, "onMessage"];
            $this->worker->onClose = [$this, "onClose"];

        }

        public function onConnect($conn){

            $conn->send("{\"type\": \"comment\", \"message\": \"RemotePlayer Server v0.1.0\"}");

        }

        public function onMessage($conn, $msg){

            $msg = json_decode($msg, 1);
            switch($msg['type']){

                case "register":
                    $dev = $msg['device'];
                    $sec = $msg['secret'];
                    $id = $msg['id'];

                    $secret = $this->db->table("device")->{$dev}->secret;
                    
                    if($sec === $secret){
                        $conn->device = $dev;
                        $this->conns[$dev] = $conn;
                        $conn->send("{\"type\": \"result\", \"result\": \"success\", \"id\": $id}");
                        return;
                    }else{
                        $conn->send("{\"type\": \"result\", \"result\": \"error\", \"id\": $id}");
                        $conn->close();
                        return;
                    }

                case "sendVoice":
                
                    if($this->seccode != $msg['seccode']){
                        return;
                    }
                    $this->conns[$msg['device']]->send(json_encode([
                        "type" => "sendVoice",
                        "voice" => $msg['voice'],
                        "autoplay" => $msg['autoplay']
                    ]));
                    return;

            }

        }

        public function onClose($conn){

            if(isset($conn->device)){
                unset($this->conns[$conn->device]);
            }
            return;

        }

        public function sendVoice($device, $voice, $autoplay = true){

            $conn = new \Workerman\Connection\AsyncTcpConnection(
                str_replace("websocket", "ws", $this->config['listen']['PushServer'])
            );

            $conn->connect();

            $conn->send(json_encode([
                "type" => "sendVoice",
                "device" => $device,
                "autoplay" => $autoplay,
                "voice" => $voice,
                "seccode" => $this->seccode
            ]));


            return true;

        }

        public function getVoice($path){

            return base64_encode(file_get_contents($path));

        }

    }
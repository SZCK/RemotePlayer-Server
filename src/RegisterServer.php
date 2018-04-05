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

    class RegisterServer {

        protected $worker = null;
        protected $config = [];
        protected $db = null;
        protected $pushServer = null;

        public function __construct(string $uri, array $config, \NonDB\NonDB $db, \RemotePlayer\PushServer $pushServer){

            $this->config = $config;
            $this->db = $db;
            $this->pushServer = $pushServer;

            $this->worker = new \Workerman\Worker($uri);
            $this->worker->count = $this->config['server']['count'];
            $this->worker->name = $this->config['server']['namePre'] . "RegisterServer";

            $this->worker->onMessage = [$this, "onMessage"];

        }

        public function onMessage($conn, $msg){

            $response = function($msg) use ($conn){
                $conn->send(json_encode($msg));
                $conn->close();
            };

            if(!@$_GET['action']){
                return $response([
                    "status" => "error",
                    "result" => [
                        "reason" => "Invalid Request"
                    ]
                ]);
            }

            switch($_GET['action']){
                case "register":
                    if(!$_GET['device'] || !$_GET['password']){
                        return $response([
                            "status" => "error",
                            "result" => [
                                "reason" => "Invalid Parameters"
                            ]
                        ]);
                    }
                    $tbl = $this->db->table("device");

                    if(isset($tbl[$_GET['device']])){
                        return $response([
                            "status" => "error",
                            "result" => [
                                "reason" => "Already Registered"
                            ]
                        ]);
                    }

                    $device = $tbl->create($_GET['device']);
                    $device->id = $_GET['device'];
                    $device->password = (new \Mixcm\PassportLib\Password())->encrypt($_GET['password'], (new \Mixcm\PassportLib\Generate())->salt(256));
                    $device->secret = (new \Mixcm\PassportLib\Generate())->salt(256);
                    $device->save();

                    return $response([
                        "status" => "success",
                        "result" => [
                            "secret" => $device->secret
                        ]
                    ]);

                case "auth":
                    if(!$_GET['device'] || !$_GET['password']){
                        return $response([
                            "status" => "error",
                            "result" => [
                                "reason" => "Invalid Parameters"
                            ]
                        ]);
                    }

                    $input = $_GET['password'];
                    $password = $this->db->table("device")->{$_GET['device']}->password;

                    $rslt = (new \Mixcm\PassportLib\Password())->check($input, $password);

                    if($rslt){
                        return $response([
                            "status" => "success",
                            "result" => [
                                "authenticated" => true
                            ]
                        ]);
                    }else{
                        return $response([
                            "status" => "error",
                            "result" => [
                                "reason" => "Wrong Password"
                            ]
                        ]);
                    }
                    
                case "sendVoice":
                    
                    if(!$_GET['device'] || !$_GET['password'] || !$_GET['voice']){
                        return $response([
                            "status" => "error",
                            "result" => [
                                "reason" => "Invalid Parameters"
                            ]
                        ]);
                    }

                    $autoplay = (isset($_GET['autoplay'])) ? $_GET['autoplay'] : true;
                    
                    $input = $_GET['password'];
                    $password = $this->db->table("device")->{$_GET['device']}->password;

                    $rslt = (new \Mixcm\PassportLib\Password())->check($input, $password);

                    if($rslt){
                        $this->pushServer->sendVoice($_GET['device'], $this->pushServer->getVoice($_GET['voice']), $autoplay);
                        return $response([
                            "status" => "success",
                            "result" => [
                                "authenticated" => true
                            ]
                        ]);
                    }else{
                        return $response([
                            "status" => "error",
                            "result" => [
                                "reason" => "Wrong Password"
                            ]
                        ]);
                    }

            }

            return $response([
                    "status" => "error",
                    "result" => [
                        "reason" => "Invalid Request"
                    ]
                ]);

        }

    }
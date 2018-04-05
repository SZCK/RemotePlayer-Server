<?php

    return [

        "listen" => [
            "PushServer" => "websocket://0.0.0.0:30991",
            "RegisterServer" => "http://0.0.0.0:30992",
            "CoolQServer" => "http://0.0.0.0:30993",
            "WebInterface" => "http://0.0.0.0:30994"
        ],

        "server" => [
            "count" => 1,
            "namePre" => "remotePlayer_"
        ]

    ];

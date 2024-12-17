<?php
return array (

    'loggers' => [
        'value' => [
            'LoggerAgents' => [
                'constructor' => function () {
                    return new \Logger('agents', 5 * 1024 * 1024);
                },
                'level' => \Psr\Log\LogLevel::DEBUG, // log level
            ],
        ],
        'readonly' => true,
    ],

);

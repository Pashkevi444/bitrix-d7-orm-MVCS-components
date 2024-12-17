<?php
return array (
    'routing' =>
        array (
            'value' =>
                array (
                    'config' =>
                        array (
                            0 => 'api.php',
                        ),
                ),
        ),
    'utf_mode' =>
        array (
            'value' => true,
            'readonly' => true,
        ),
    'cache_flags' =>
        array (
            'value' =>
                array (
                    'config_options' => 3600,
                    'site_domain' => 3600,
                ),
            'readonly' => false,
        ),
    'cookies' =>
        array (
            'value' =>
                array (
                    'secure' => false,
                    'http_only' => true,
                ),
            'readonly' => false,
        ),
    'exception_handling' =>
        array (
            'value' =>
                array (
                    'debug' => true,
                    'handled_errors_types' => 4437,
                    'exception_errors_types' => 4437,
                    'ignore_silence' => false,
                    'assertion_throws_exception' => true,
                    'assertion_error_type' => 256,
                    'log' =>
                        array (
                            'settings' =>
                                array (
                                    'file' => 'bitrix/err.log',
                                    'log_size' => 1000000,
                                ),
                        ),
                ),

            'readonly' => false,
        ),

    'connections' =>
        array (
            'value' =>
                array (
                    'default' =>
                        array (
                            'className' => '\\Bitrix\\Main\\DB\\MysqliConnection',
                            'host' => 'localhost',
                            'database' => 'test',
                            'login' => 'test',
                            'password' => 'test',
                            'options' => 2,
                        ),
                ),
            'readonly' => true,
        ),
    'crypto' =>
        array (
            'value' =>
                array (
                    'crypto_key' => 'b42fbb42d0a838e39bef7a20fbba9841',
                ),
            'readonly' => true,
        ),
);

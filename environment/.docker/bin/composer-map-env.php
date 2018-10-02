#!/usr/bin/env php

<?php

class Mapper
{
    protected $jsonFile;
    protected $stdIn;
    protected $stdOut;
    protected $stdErr;

    /**
     * @param array $argv
     */
    public function __construct(array $argv)
    {
        $this->jsonFile = $argv[1];
        $this->stdIn   = STDIN;
        $this->stdOut  = STDOUT;
        $this->stdErr  = STDERR;

        $data = json_decode(file_get_contents($this->jsonFile), true);
        $data['extra']['incenteev-parameters']['env-map'] = [
            'database_driver'         => 'APP_DB_DRIVER',
            'database_host'           => 'APP_DB_HOST',
            'database_port'           => 'APP_DB_PORT',
            'database_name'           => 'APP_DB_NAME',
            'database_user'           => 'APP_DB_USER',
            'database_password'       => 'APP_DB_PASSWORD',
            'mailer_transport'        => 'APP_MAILER_TRANSPORT',
            'mailer_host'             => 'APP_MAILER_HOST',
            'mailer_port'             => 'APP_MAILER_PORT',
            'mailer_encryption'       => 'APP_MAILER_ENCRYPTION',
            'mailer_user'             => 'APP_MAILER_USER',
            'mailer_password'         => 'APP_MAILER_PASSWORD',
            'websocket_bind_port'     => 'APP_WEBSOCKET_BIND_PORT',
            'websocket_backend_port'  => 'APP_WEBSOCKET_BACKEND_PORT',
            'websocket_frontend_port' => 'APP_WEBSOCKET_FRONTEND_PORT',
            'installed'               => 'APP_IS_INSTALLED',
            'secret'                  => 'APP_SECRET'
        ];

        file_put_contents($this->jsonFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * @param string $msg
     */
    protected function out($msg)
    {
        fwrite($this->stdOut, $msg);
    }

    /**
     * @param string $msg
     */
    protected function err($msg)
    {
        fwrite($this->stdErr, $msg);
    }
}

new Mapper($argv);
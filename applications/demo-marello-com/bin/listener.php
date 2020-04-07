#!/usr/bin/env php
<?php
class Listener
{
    protected $user;
    protected $logFile;
    protected $stdIn;
    protected $stdOut;
    protected $stdErr;
    protected $stdLog;

    public function __construct($argv)
    {
        $script = array_shift($argv);
        $this->logFile = array_shift($argv);
        $this->user    = array_shift($argv);
        $this->stdIn   = STDIN;
        $this->stdOut  = STDOUT;
        $this->stdErr  = STDERR;
        $executable    = array_shift($argv);

        if (!$this->logFile || !$executable) {
            $this->err("Usage: $script <log_file> <cmd> [args]");
            exit(1);
        }

        $this->stdLog  = fopen($this->logFile, 'w+');
        chmod($this->logFile, 0777);

        if (!count($argv)) {
            $cmd = $executable;
        } else {
            $argv = array_map(function ($v){
                return sprintf("'%s'", $v);
            }, $argv);
            $cmd = sprintf('%s %s', $executable, implode(' ', $argv));
        }
        $this->cmd = sprintf(
            '/sbin/runuser -s /bin/sh -c "%s > %s" %s',
            addcslashes($cmd, '"'),
            $this->logFile,
            $this->user
        );

        $this->out("READY\n");
        while (true) {
            sleep(1);
            while (false !== ($contents = fgets($this->stdIn))) {
                ftruncate($this->stdLog, 0); // Clear log
                $this->onData($this->parse($contents));
                $this->out("RESULT 2\nOK");
                $this->out("READY\n");
            }
        }
    }

    protected function log($msg)
    {
        fwrite($this->stdLog, $msg);
    }

    protected function out($msg)
    {
        fwrite($this->stdOut, $msg);
    }

    protected function err($msg)
    {
        fwrite($this->stdErr, $msg);
    }

    protected function onData($params)
    {
        passthru($this->cmd);
    }

    /**
     * @param $str
     * @return array
     */
    protected function parse($str)
    {
        $matches = [];
        $eventParams = [];
        preg_match_all('/([\w-]+)?:([\w-]+)/', $str, $matches);
        if (isset($matches[0])) {
            foreach (array_keys($matches[0]) as $index) {
                $eventParams[$matches[1][$index]] = $matches[2][$index];
            }
        }

        return $eventParams;
    }
}

new Listener($argv);
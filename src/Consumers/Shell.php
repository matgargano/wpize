<?php

namespace WPize\Consumers;
class Shell extends Consumer_Base
{
    public function __construct($data, $base = null)
    {
        parent::__construct($data, $base);
    }

    public function grab()
    {
        $commands = $this->data['retrieve']['commands'];
        chdir($this->dir);
        foreach ($commands as $command) {
            shell_exec($command);
        }
    }
}




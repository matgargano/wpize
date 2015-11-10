<?php

namespace WPize\Consumer_Base;

use WPize\Consumers\Consumer_Base;

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

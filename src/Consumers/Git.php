<?php

namespace WPize\Consumers;
class Git extends Consumer_Base
{
    public function __construct($data, $base = null)
    {
        parent::__construct($data, $base);
    }

    public function grab()
    {
        $repo = $this->data['retrieve']['repo'];
        shell_exec('git clone ' . $repo . ' .');
        chdir($this->dir);
        if (isset($this->data['postCmd']) && is_array($this->data['postCmd'])) {
            foreach ($this->data['postCmd'] as $command) {
                shell_exec($command);
            }
        }



    }
}




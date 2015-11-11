<?php

namespace WPize\Consumers;

use WPize\Utils\Utils;


abstract class Consumer_Base
{
    protected $data;
    protected $base;
    protected $hash;
    protected $dir;

    public function __construct($data, $base = null)
    {
        $this->data = $data;
        $this->base = $base;
        $this->hash = md5(microtime(true));

        if (!$this->base) {
            $this->base = $_SERVER['HOME'];
        }
        $this->dir = $this->base . '/' . $this->hash;
        mkdir($this->dir, 0777, true);
        chdir($this->dir);

    }

    public function __destruct(){

        Utils::recursivelyRemoveDirectory($this->dir);

    }

    abstract public function grab();

    public function handle()
    {

        $this->grab();
        $this->process();

    }

    public function process()
    {

	    if ( isset($this->data['kill']) && is_array($this->data['kill'])) {
		    foreach($this->data['kill'] as $kill) {


			    Utils::recursivelyRemoveDirectory($this->dir . '/' . $kill);
		    }
	    }

        if (is_array($this->data['keep'])) {
            foreach ($this->data['keep'] as $keepArray) {

                $source = key($keepArray['pathMap']);
                $destination = $keepArray['pathMap'][$source];
                $recursive = true;
                if (isset($keepArray['recursive']) && !$keepArray['recursive']) {
                    $recursive = false;
                }
                $actualSourcePath = $this->dir . '/' . $source;
                $actualDestinationPath = $this->base . '/build/' . $destination;
	            if (!is_dir($actualDestinationPath)) {
                    mkdir($actualDestinationPath, 0777, true);
                }

                if ($recursive) {

                    Utils::recursivelyCopy($actualSourcePath, $actualDestinationPath);
                } else {

                    $scan = scandir($actualSourcePath . '/');

                    foreach ($scan as $object) {
                        if (!is_dir($actualSourcePath . '/' . $object)) {
                            copy($actualSourcePath . '/' . $object, $actualDestinationPath . '/' . $object);
                        }
                    }

                }


            }
        }

    }




}



<?php

namespace WPize\Consumers;

use WPize\WPize;


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

        WPIze::recursivelyRemoveDirectory($this->dir);

    }

    abstract public function grab();

    public function handle()
    {

        $this->grab();
        $this->process();

    }

    public function process()
    {
        if (is_array($this->data['keep'])) {
            foreach ($this->data['keep'] as $keepArray) {

                $source = key($keepArray['pathMap']);
                $destination = $keepArray['pathMap'][$source];
                $recursive = true;
                if (isset($keepArray['recursive']) && !$keepArray['recursive']) {
                    $recursive = false;
                }
                $actualSourcePath = $this->dir . '/' . $source;
                $actualDestinationPath = $this->base . '/final/' . $destination;
                if (!is_dir($actualDestinationPath)) {
                    mkdir($actualDestinationPath, 0777, true);
                }

                if ($recursive) {

                    self::recursivelyCopy($actualSourcePath, $actualDestinationPath);
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

    public static function recursivelyCopy($src, $dst)
    {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            mkdir($dst);
        }
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::recursivelyCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }


}



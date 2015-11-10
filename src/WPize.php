<?php

namespace WPize;

use \WPize\Consumers;

class WPize
{

    private $config;
    private $realBase;
    private $piece;

    public function __construct(Array $config = array())
    {
        if (!$config) {
            throw new \Exception('Please pass in a configuration array');
        }
        $this->config = $config;
    }

    public function process()
    {


        if (!$this->config['tempBase']) {
            $this->realBase = self::createTempDir();
        } else {
            $this->realBase = $this->config['tempBase'];
        }
        if (!$this->realBase) {
            throw new \Exception('Issue creating temporary directory');
        }
        if (is_dir($this->realBase)) {
            $this->recursivelyRemoveBase();
        }


        foreach ($this->config['pieces'] as $piece) {

            $this->piece = $piece;
            $this->processPiece();


        }


    }

    public function processPiece()
    {

        $type = 'Shell';
        if (isset($this->piece['retrieve']['type'])) {
            $type = $this->piece['retrieve']['type'];
        }

        $class = 'WPize\\Consumers\\' . $type;

        if (class_exists($class)) {

            /**
             * @var \WPize\Consumers\Consumer_Base $handle
             */

            $handle = new $class($this->piece, $this->realBase);
            $handle->handle();
        }

    }

    public function recursivelyRemoveBase()
    {

        self::recursivelyRemoveDirectory($this->realBase);
    }

    public static function recursivelyRemoveDirectory($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") self::recursivelyRemoveDirectory($dir . "/" . $object); else unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public static function createTempDir()
    {

        $tempfile = tempnam(sys_get_temp_dir(), '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
        }
        mkdir($tempfile);
        if (is_dir($tempfile)) {
            return $tempfile;
        }
        return false;
    }

}




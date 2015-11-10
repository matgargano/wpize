<?php

namespace WPize;

use WPize\Consumer_Base\Git;
use \WPize\Consumers;

class WPize
{

    private $config;
    private $realBase;

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
            $this->config['tempBase'] = self::createTempDir();
        }
        if (!$this->config['tempBase']) {
            throw new \Exception('Issue creating temporary directory');
        }
        if (is_dir($this->realBase)) {
            $this->recursivelyRemoveBase();
        }


        foreach ($this->config['pieces'] as $piece) {

            $type = 'Shell';
            if (isset($piece['retrieve']['type'])) {
                $type = $piece['retrieve']['type'];
            }

            $class = 'WPize\\Consumers\\' . $type;

            if (class_exists($class)) {




                /**
                 * @var \WPize\Consumers\Consumer_Base $handle
                 */

                $handle = new $class($piece, $this->realBase);
                $handle->handle();
            }


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




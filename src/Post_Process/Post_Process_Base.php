<?php

namespace WPize\Post_Process;

abstract class Post_Process_Base {

    protected $data;
    protected $base;

    public function __construct($data, $base = null) {

        $this->data = $data;
        $this->base = $base . '/final';

    }

    abstract public function handle();

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
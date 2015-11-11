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



}
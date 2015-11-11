<?php

namespace WPize;

use \WPize\Consumers;
use WPize\Utils\Utils;

class WPize
{

	static $counter = 0;
    private $config;
    private $realBase;
    private $piece;
    private $currentPostProcess;

    public function __construct(Array $config = array())
    {
        if (!$config) {
            throw new \Exception('Please pass in a configuration array');
        }
        $this->config = $config;
    }

    public function process()
    {


        if (!isset($this->config['tempBase']) || !$this->config['tempBase']) {
            $this->realBase = Utils::createTempDir();
            chdir($this->realBase);
            mkdir('wpize');
            $this->realBase .= '/wpize';
        } else {
            $this->realBase = $this->config['tempBase'];
        }
	    Utils::recursivelyRemoveDirectory($this->realBase);
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
        $this->postProcess();


    }

    public function postProcess()
    {

        if (!isset($this->config['post']) || !is_array($this->config['post'])) {
            return;
        }

        foreach($this->config['post'] as $post) {

            $this->currentPostProcess = $post;
            $this->processPostStep();


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
            $handle = null; //destruct the object to clear the directory
	        if ( 'Git' == $type ) {
		        static::$counter++;
		        if ( static::$counter > 2 ) {
			        die;
		        }
	        }
        }

    }

	public function processPostStep(){


		$class = 'WPize\\Post_Process\\' . $this->currentPostProcess['type'];

		if (class_exists($class)) {

			/**
			 * @var \WPize\Post_Process\Post_Process_Base $handle
			 */

			$handle = new $class($this->currentPostProcess, $this->realBase);
			$handle->handle();
			$handle = null; //destruct the object to clear the directory
		}

	}

    public function recursivelyRemoveBase()
    {

        Utils::recursivelyRemoveDirectory($this->realBase);
    }



}




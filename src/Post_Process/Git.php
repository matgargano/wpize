<?php

namespace WPize\Post_Process;

use WPize\Utils\Utils;

class Git extends Post_Process_Base
{

	public function __construct($data, $base = null)
	{
		parent::__construct($data, $base);
	}

    public function handle()
    {

        chdir($this->base);
        if (!isset($this->data['branch'])) {
            $this->data['branch'] = 'master';
        }

        if (isset($this->data['method'])) {
            $method = $this->data['method'];
            if (method_exists($this, $method)) {
                $this->$method();
            } else {
                throw new \Exception('Merge methoeds not supported ' . $method);
            }

        }

    }

    public function revcursiveMerge()
    {


        exec('git init');
        exec('git clone ' . $this->data['repo'] . ' ' . $this->base . '/interim');
        $scan = scandir($this->base);
        foreach ($scan as $object) {
            if (in_array($object, array('.', '..', 'interim', '.git'))) {
                continue;
            }
            if (is_dir($this->base . '/' . $object)) {
                Utils::recursivelyCopy($this->base . '/' . $object, $this->base . '/interim/' . $object);
            } else {
                copy($this->base . '/' . $object, $this->base . '/interim/' . $object);
            }
        }
	    $this->handleOverwrites('interim', '');
        chdir($this->base . '/interim');
        exec('git add .');
        exec('git commit -am "Pushing up ' . @date('YmdHis') . ' deploy"');
        exec('git tag -a ' . @date('YmdHis'). ' -m " Tagging the ' . @date('YmdHis') . ' deploy"');
        exec('git push origin ' . $this->data['branch']);
        exec('git push origin --tags');

    }

    public function force(){

        chdir($this->base);
        exec('git clone ' . $this->data['repoSource'] . ' ' . $this->base . '/source/');
        exec('git clone ' . $this->data['repoDestination'] . ' ' . $this->base . '/destination/');


        $scan = scandir($this->base . '/source/');
        foreach ($scan as $object) {
            if (in_array($object, array('.', '..', '.git'))) {
                continue;
            }
            if (is_dir($this->base . '/source/' . $object)) {
	            Utils::recursivelyCopy( $this->base . '/source/' . $object, $this->base . '/destination/' . $object);
            } else {
                copy($this->base . '/source/' . $object, $this->base . '/destination/' . $object);
            }
        }

	    $this->handleOverwrites('source', 'destination');
        chdir($this->base . '/destination/');
        exec('git add .');
        exec('git commit -am "Pushing up ' . @date('YmdHis') . ' deploy"');
        exec('git tag -a ' . @date('YmdHis'). ' -m " Tagging the ' . @date('YmdHis') . ' deploy"');
        exec('git push origin ' . $this->data['branch']);
        exec('git push origin --tags');





    }

	function handleOverwrites($source, $destination){
		if ( $source ) {
			$source = $source . '/';
		}
		if ( $destination ) {
			$destination = $destination . '/';
		}
		if ( is_array($this->data['overwrites']) ) {
			foreach($this->data['overwrites'] as $overwrite) {
				$key = key($overwrite['pathMap']);
				$value = $overwrite['pathMap'][$key];
				Utils::recursivelyRemoveDirectory($this->base . '/' . $destination  .$value);

				Utils::recursivelyCopy( $this->base . '/' . $source . $key, $this->base . '/' . $destination  . $value );
			}
		}
	}

}
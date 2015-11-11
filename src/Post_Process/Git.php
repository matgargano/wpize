<?php

namespace WPize\Post_Process;

use WPize\Utils\Utils;

class Git extends Post_Process_Base
{

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
                throw new \Exception('Merge methoes not supported ' . $method);
            }

        }

    }

    public function revcursiveMerge()
    {


        exec('git init');
        exec('git clone ' . $this->data['repo'] . ' ' . $this->base . '/interim');
        $scan = scandir($this->base);
        foreach ($scan as $object) {
            if (in_array($object, array('.', '..', '/interim'))) {
                continue;
            }
            if (is_dir($this->base . '/' . $object)) {
                Utils::recursivelyCopy($this->base . '/' . $object, $this->base . '/interim' . $object, array( 'directories' => '.git' ));
            } else {
                copy($this->base . '/' . $object, $this->base . '/interim' . $object);
            }
        }
        chdir($this->base . '/interim');
        exec('git add .');
        exec('git commit -am "Pushing up ' . date('YmdHis') . ' deploy"');
        exec('git tag -a ' . date('YmdHis'). ' -m " Tagging the ' . date('YmdHis') . ' deploy"');
        exec('git push origin ' . $this->data['branch']);
        exec('git push origin --tags');

    }

    public function force(){

        chdir($this->base);
        exec('git clone ' . $this->data['repoSource'] . ' ' . $this->base . '/source/');
        exec('git clone ' . $this->data['repoDestination'] . ' ' . $this->base . '/destination/');


        $scan = scandir($this->base . '/source/');
        foreach ($scan as $object) {
            if (in_array($object, array('.', '..'))) {
                continue;
            }
            if (is_dir($this->base . '/source/' . $object)) {
	            Utils::recursivelyCopy( $this->base . '/source/' . $object, $this->base . '/destination/' . $object, array( 'directories' => '.git' ) );
            } else {
                copy($this->base . '/source/' . $object, $this->base . '/destination/' . $object);
            }
        }
        chdir($this->base . '/destination/');
        exec('git add .');
        exec('git commit -am "Pushing up ' . date('YmdHis') . ' deploy"');
        exec('git tag -a ' . date('YmdHis'). ' -m " Tagging the ' . date('YmdHis') . ' deploy"');
        exec('git push origin ' . $this->data['branch']);
        exec('git push origin --tags');





    }

}
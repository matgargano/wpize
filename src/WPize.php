<?php

namespace WPize;

use \WPize\Consumers;
use WPize\Utils\Utils;

class WPize
{


    private $config;
    private $realBase;
    private $step;
    private $buildDir;
    private $currentBuildStepName;
    private $currentDirectory;
    private $previousBuildDirectory;

    public function __construct(Array $config = array())
    {
        if (!$config) {
            throw new \Exception('Please pass in a configuration array');
        }
        $this->config = $config;
    }

    public function process()
    {


        $this->setupDirectories();
        $this->processSteps();


    }

    public function setupDirectories(){
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



    }

    public function processSteps(){

        foreach ($this->config['build'] as $stepName => $buildSteps) {
            $this->currentBuildStepName = $stepName;
            $this->currentDirectory = $this->realBase . '/' . $this->currentBuildStepName;
            $counter = 1;
            $numberSteps = count($buildSteps);
            foreach ($buildSteps as $step) {
                $counter++;
                if ($counter === $numberSteps) {
                    $this->previousBuildDirectory = $this->currentDirectory;
                }
                $this->step = $step;
                $this->buildDir = $this->processStep();


            }
        }
    }


    public function processStep()
    {

        $type = 'Shell';
        if (isset($this->step['retrieve']['type'])) {
            $type = $this->step['retrieve']['type'];
        }

        $class = 'WPize\\Consumers\\' . $type;

        if (class_exists($class)) {

            /**
             * @var \WPize\Consumers\Consumer_Base $handle
             */


            $handle = new $class($this->step, $this->currentDirectory);
            $handle->handle();
            $handle = null; //destruct the object to clear the directory

        } else {
            throw new \Exception('Cannot find a class to handle $type in namespace Wpize\\Consumers\\');
        }
        return $this->currentDirectory;


    }


    public function recursivelyRemoveBase()
    {

        Utils::recursivelyRemoveDirectory($this->realBase);
    }


}




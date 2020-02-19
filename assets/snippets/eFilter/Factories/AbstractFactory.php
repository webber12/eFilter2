<?php namespace eFilter\Factories;

include_once realpath(__DIR__ . '/../Interfaces/FactoryInterface.php');

use \eFilter\Interfaces\FactoryInterface;

class AbstractFactory implements FactoryInterface
{
    protected $filename;
    protected $classname;
    protected $alias;
    
    public function __construct($_EF = null, $params = [])
    {
        $this->EF = $_EF;
        $this->modx = EvolutionCMS();
        $this->params = $params;
    }
    
    public function load($params = [])
    {
        $this->check();
        include_once($this->filename);
        return new $this->classname($this->EF, $this->params);
    }
    
    protected function check()
    {
        if (!empty($this->alias)) {
            $className = trim($this->EF->config->getCFGDef($this->alias . 'ClassName', ''));
            $classFile = trim($this->EF->config->getCFGDef($this->alias . 'ClassFile', ''));
            if (!empty($className) && 
                !empty($classFile) && 
                file_exists(realpath(__DIR__ . '/../' . $classFile))
            ) {
                $this->filename = realpath(__DIR__ . '/../' . $classFile);
                $this->classname = $className;
            }
        }
    }

}

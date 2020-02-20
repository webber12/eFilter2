<?php namespace eFilter\Factories;

use eFilter\Interfaces\FactoryInterface;

class AbstractFactory implements FactoryInterface
{
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
        return new $this->classname($this->EF, $this->params);
    }
    
    protected function check()
    {
        if (!empty($this->alias)) {
            $className = trim($this->EF->config->getCFGDef($this->alias . 'ClassName', ''));
            $classFile = trim($this->EF->config->getCFGDef($this->alias . 'ClassFile', ''));
            if (!empty($className)) {
                $this->classname = $className;
            }
            if (!empty($classFile) && is_readable(MODX_BASE_PATH . $classFile)) {
                include_once(MODX_BASE_PATH . $classFile);
            }
        }
    }

}

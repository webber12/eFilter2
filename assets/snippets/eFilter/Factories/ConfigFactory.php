<?php namespace eFilter\Factories;

use eFilter\Factories\AbstractFactory;

class ConfigFactory extends AbstractFactory
{
    protected $classname = '\Helpers\Config';
    protected $alias = 'config';

    protected function check()
    {
        //объекта $this->EF->config еще не существует, берем значения из параметров
        $className = !empty(trim($this->params[$this->alias . 'ClassName'])) ? trim($this->params[$this->alias . 'ClassName']) : '';
        $classFile = !empty(trim($this->params[$this->alias . 'ClassFile'])) ? trim($this->params[$this->alias . 'ClassFile']) : '';
        if (!empty($className)) {
            $this->classname = $className;
        }
        if (!empty($classFile) && is_readable(MODX_BASE_PATH . $classFile)) {
            include_once(MODX_BASE_PATH . $classFile);
        }
    }
}

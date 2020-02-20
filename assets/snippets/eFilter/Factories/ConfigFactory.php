<?php namespace eFilter\Factories;

use eFilter\Factories\AbstractFactory;

class ConfigFactory extends AbstractFactory
{
    protected $filename = MODX_BASE_PATH . 'assets/lib/Helpers/Config.php';
    protected $classname = '\Helpers\Config';
	protected $alias = 'config'; //configClassName // configClassFile 
	
	protected function check()
    {
		if (!empty($this->alias)) {
			//объекта $this->EF->config еще не существует, берем значения из параметров
			$className = !empty(trim($this->params[$this->alias . 'ClassName'])) ? trim($this->params[$this->alias . 'ClassName']) : '';
			$classFile = !empty(trim($this->params[$this->alias . 'ClassFile'])) ? trim($this->params[$this->alias . 'ClassFile']) : '';
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

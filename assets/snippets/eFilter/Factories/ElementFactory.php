<?php namespace eFilter\Factories;

include_once realpath(__DIR__ . '/AbstractFactory.php');

use \eFilter\Factories\AbstractFactory;

class ElementFactory extends AbstractFactory
{
    protected $filename;
    protected $classname;
    protected $_EF; //eFilter object
    protected $elements = []; //array of elements objects
    
    public function __construct($_EF)
    {
        $this->EF = $_EF;
    }
    
    public function load($params = [])
    {
        $name = $params['name'];
        $obj = null;
        if (!empty($this->elements[$name])) {
            $obj = $this->elements[$name];
        } else {
            $obj = $this->loadElement($name);
        }
        if (empty($obj)) {
            if (!empty($this->elements['checkbox'])) {
                $obj = $this->elements['checkbox'];
            } else {
                $obj = $this->loadElement('checkbox');
            }
        }
        return $obj;
    }
    
    protected function check()
    {
        return;
    }
    
    protected function loadElement($originalname)
    {
        $name = ucfirst($originalname);
        $theme = ucfirst($this->EF->config->getCFGDef('cfg', 'default'));
        if (!class_exists('\\eFilter\\Elements\\' . $name)) {
            if (file_exists(MODX_BASE_PATH . 'assets/snippets/eFilter/Elements/' . $theme . '/' . $name . '.php')) {
                include_once(MODX_BASE_PATH . 'assets/snippets/eFilter/Elements/' . $theme . '/' . $name . '.php');
            } else if (file_exists(MODX_BASE_PATH . 'assets/snippets/eFilter/Elements/Default/' . $name . '.php')) {
                include_once(MODX_BASE_PATH . 'assets/snippets/eFilter/Elements/Default/' . $name . '.php');
            }
        }
        if (class_exists('\\eFilter\\Elements\\' . $name)) {
            $className = '\\eFilter\\Elements\\' . $name;
            $obj = new $className($this->EF);
            $this->elements[$originalname] = $obj;
        } else {
            $obj = null;
        }
        return $obj;
    }
}

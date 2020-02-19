<?php namespace eFilter\Factories;

include_once realpath(__DIR__ . '/AbstractFactory.php');

use \eFilter\Factories\AbstractFactory;

class DBModelFactory extends AbstractFactory
{

    protected $filename = MODX_BASE_PATH . 'assets/snippets/eFilter/Models/DBModel.php';
    protected $classname = '\eFilter\Models\DBModel';
    protected $alias = 'DBModel';
    
}

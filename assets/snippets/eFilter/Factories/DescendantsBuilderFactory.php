<?php namespace eFilter\Factories;

include_once realpath(__DIR__ . '/AbstractFactory.php');

use \eFilter\Factories\AbstractFactory;

class DescendantsBuilderFactory extends AbstractFactory
{
    protected $filename = MODX_BASE_PATH . 'assets/snippets/eFilter/Controllers/DescendantsBuilder.php';
    protected $classname = '\eFilter\Controllers\DescendantsBuilder';
    protected $alias = 'DescendantsBuilder';
}

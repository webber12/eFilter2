<?php namespace eFilter\Elements;

include_once realpath(__DIR__ . '/../AbstractElement.php');

class Pattern extends AbstractElement
{

    protected $type = 'pattern';
    
    protected function makeHrefForValue($title, $id)
    {
        return $title;
    }

}
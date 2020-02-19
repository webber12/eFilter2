<?php namespace eFilter\Elements;

include_once realpath(__DIR__ . '/../AbstractElement.php');

class Colors extends AbstractElement
{

    protected $type = 'colors';
    
    protected function makeHrefForValue($title, $id)
    {
        return $title;
    }

}
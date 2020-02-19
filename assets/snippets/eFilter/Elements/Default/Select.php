<?php namespace eFilter\Elements;

include_once realpath(__DIR__ . '/../AbstractElement.php');

class Select extends AbstractElement
{

    protected $type = 'select';

    protected function getSelected()
    {
        return ' selected="selected" ';
    }
    
    protected function makeHrefForValue($title, $id)
    {
        return $title;
    }

}
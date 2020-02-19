<?php namespace eFilter\Elements;

include_once realpath(__DIR__ . '/../AbstractElement.php');

class MultySelect extends AbstractElement
{

    protected $type = 'multySelect';
    
    protected function getSelected()
    {
        return ' selected="selected" ';
    }
    
    protected function makeHrefForValue($title, $id)
    {
        return $title;
    }

}
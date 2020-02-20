<?php namespace eFilter\Elements;

use eFilter\Elements\AbstractElement;

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
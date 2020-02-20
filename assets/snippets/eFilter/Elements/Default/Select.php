<?php namespace eFilter\Elements;

use eFilter\Elements\AbstractElement;

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
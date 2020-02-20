<?php namespace eFilter\Elements;

use eFilter\Elements\AbstractElement;

class Colors extends AbstractElement
{

    protected $type = 'colors';
    
    protected function makeHrefForValue($title, $id)
    {
        return $title;
    }

}
<?php namespace eFilter\Elements;

use eFilter\Elements\AbstractElement;

class Pattern extends AbstractElement
{

    protected $type = 'pattern';
    
    protected function makeHrefForValue($title, $id)
    {
        return $title;
    }

}
<?php namespace eFilter\Models;

use eFilter\Interfaces\DBModelInterface;

class DBModelAbstract implements DBModelInterface
{
    public function __construct($_EF = null, $params = [])
    {
        $this->EF = $_EF;
        $this->modx = EvolutionCMS();
        $this->params = $params;
    }

}

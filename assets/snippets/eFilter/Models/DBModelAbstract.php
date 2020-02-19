<?php namespace eFilter\Models;

include_once realpath(__DIR__ . '/../Interfaces/DBModelInterface.php');

use \eFilter\Interfaces\DBModelInterface;

class DBModelAbstract implements DBModelInterface
{
    public function __construct($_EF = null, $params = [])
    {
        $this->EF = $_EF;
        $this->modx = EvolutionCMS();
        $this->params = $params;
    }

}

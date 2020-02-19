<?php namespace eFilter\Interfaces;

interface ElementInterface
{

    public function render(/*$tv_id, $filters, $filter_values_full, $tv_elements, $filter_values*/);
    
    public function setParam($name, $value);

    public function getTpl($name);
    
    //для обратной совместимости со старыми настройками шаблонов элементов формы
    public function getOldTPL($name);

}

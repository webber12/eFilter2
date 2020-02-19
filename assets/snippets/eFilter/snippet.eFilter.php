<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
$output = "";

include_once('eFilter.class.php');
$eFltr = new eFilter\eFilter($params);
$process = $eFltr->process();
if ($process) {
    //фильтр отработал до конца, можно делать рендер формы, грузить скрипты и плейсхолдеры
    $eFltr->postProcess();
} else {
    //при попытке отработки обнаружено, что нет документов для фильтрации
    //больше ничего делать не нужно
}

return;

<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
$output = "";

include_once 'autoload.php';
$Indexer = new eFilter\Controllers\ElasticSearchIndexer($params);

switch ($modx->event->name) {
    case 'OnDocFormSave':
        $Indexer->OnDocFormSave($id);
        break;
    case 'OnPluginFormSave':
        $Indexer->OnPluginFormSave();
        break;
    default:
        break;
}

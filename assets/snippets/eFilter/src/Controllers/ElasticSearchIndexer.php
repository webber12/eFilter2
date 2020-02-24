<?php namespace eFilter\Controllers;

include_once MODX_BASE_PATH . 'assets/snippets/eFilter/eFilter.class.php';
use \modResource;

//elastic search php client
include_once MODX_BASE_PATH . 'vendor/autoload.php';
use \Elasticsearch\ClientBuilder;

class ElasticSearchIndexer
{
    public function __construct($params = [])
    {
        $this->modx = EvolutionCMS();
        $this->params = $params;
        $this->EF = new \eFilter\eFilter();
    }
    
    public function OnDocFormSave($docid)
    {
        $this->api = new \modResource($this->modx);
        $doc = $this->api->edit($docid);
        $template = $doc->get('template');
        if ($this->isProduct($template)) {
            $this->EF->docid = $this->getParentCategoryId($doc);
            $allowedTmp = $this->EF->getFilterParam($this->params['param_tv_name']);
            $TvArrForIndex = $this->EF->getFilterParamInfo();
            if (!empty($TvArrForIndex)) {
                $client = \Elasticsearch\ClientBuilder::create()->build();
                $p["index"]  = $this->params['indexKey'];
                $p["id"] = $docid;
                $query["id"] = $docid;
                $query["template"] = $template;
                $p["body"] = $query;
                $mapping = $client->indices()->getMapping( [ 'index' => $p["index"] ] );
                foreach ($TvArrForIndex as $tv) {
                    $tvid = $tv['id'];
                    $tvname = $tv['name'];
                    $tvvalue = $doc->get($tvname);
                    $tvtype = !empty($mapping[ $p["index"] ]['mappings']['properties']['tv' . $tvid]['type']) ? $mapping[ $p["index"] ]['mappings']['properties']['tv' . $tvid]['type'] : 'text';
                    switch ($tvtype) {
                        case 'scaled_float':
                            //все числа будем хранить в индексе в формате scaled_float и уровнем масштабирования 1000 (3 знака после запятой)
                            // 45.25 = 45250 / 1000
                            $tvvalue = str_replace([' ', ','], ['', '.'], $tvvalue);
                            break;
                        default:
                            //строки храним в UTF-8 в нижнем регистре
                            $tvvalue = strpos($tvvalue, "||") !== false ? array_map(function($a) {return trim(mb_strtolower($a, "UTF-8"));}, explode('||', $tvvalue)) : trim(mb_strtolower($tvvalue, "UTF-8"));
                            break;
                    }
                    $query["tv" . $tvid] = $tvvalue;
                }
                $p["body"] = $query;
                $result = $client->index($p);
            }
        }
    }

    public function OnPluginFormSave()
    {
        if ($this->checkPluginName($this->params['id'])) {
            //мы находимся в нужном плагине
            if (!empty(trim($this->params['tv_number_format']))) {
                $tv_number_format = array_map('trim', explode(',', $this->params['tv_number_format']));
                //print_r($tv_number_format);
                $p["index"]  = $this->params['indexKey'];
                $client = \Elasticsearch\ClientBuilder::create()->build();
                $mapping = $client->indices()->getMapping( [ 'index' => $p["index"] ] );
                $properties = !empty($mapping[ $p["index"] ]['mappings']['properties']) ? $mapping[ $p["index"] ]['mappings']['properties'] : [];
                $change = false;
                foreach ($tv_number_format as $tvid) {
                    if (empty($properties['tv' . $tvid])) {
                        $properties['tv' . $tvid] = [ "type" => "scaled_float", "scaling_factor" => 1000 ];
                        $change = true;
                    }
                }
                if (!empty($properties) && $change) {
                    //произошли изменения, будем пересохранять маппинг
                    $p["body"]["properties"] = $properties;
                    $result = $client->indices()->putMapping($p);
                }
            }
        }
    }
    
    protected function getParentCategoryId($doc)
    {
        $parent = $doc->get('parent');
        if (!empty($this->params['tv_tagcategory_name'])) {
            $tmp = $doc->get($this->params['tv_tagcategory_name']);
            if (!empty($tmp));
            $parent = explode(',', $tmp)[0];
        }
        return $parent;
    }
    
    protected function isProduct($template)
    {
        return in_array($template, array_map('trim', explode(',', $this->params['product_templates'])));
    }
    
    protected function checkPluginName($id)
    {
        return $this->modx->db->getValue("SELECT id FROM " . $this->modx->getFullTableName("site_plugins") . " WHERE id={$id} AND `name`='ElasticSearchIndexer'");
    }
}


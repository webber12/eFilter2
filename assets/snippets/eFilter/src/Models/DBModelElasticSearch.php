<?php namespace eFilter\Models;

use eFilter\Models\DBModelEloquent;

//php-client for elasticsearch
//install via composer
//composer require elasticsearch/elasticsearch

include_once MODX_BASE_PATH . 'vendor/autoload.php';
use Elasticsearch\ClientBuilder;

class DBModelElasticSearch extends DBModelEloquent
{
    public function __construct($_EF, $params)
    {
        parent::__construct($_EF, $params);
        $this->client = \Elasticsearch\ClientBuilder::create()->build();
        $this->indexKey = $this->EF->config->getCFGDef('indexKey', 'efilter');
        $this->mapping = $this->client->indices()->getMapping( ['index' => $this->indexKey] );
        $this->numFormats = array_map('trim', explode(',', 'long, integer, short, byte, double, float, half_float, scaled_float'));
    }

    public function getList($DLparams, $filters = [], $tvsInfo = [])
    {
        if (empty($filters)) {
            return $this->modx->runSnippet("DocLister", $DLparams);
        } else {
            $p['index'] = $this->indexKey;
            list($elasticFilters, $addDLFilters) = $this->makeFilterFromDLFilter($DLparams, $filters, $tvsInfo);
            $elasticFilters['bool']['filter'][]['terms']['id'] = explode(',', $DLparams['documents']);
            $p['body']['query'] = $elasticFilters;
            $result = $this->client->search($p);
            $result = $this->convertElasticResult($result);
            //дофильтровываем диапазон, если не сложилось с маппингом :)
            if ($result['total'] > 0 && !empty($addDLFilters)) {
                $DLparams['documents'] = implode(',', array_column($result, 'id'));
                $DLparams['filters'] = "AND(" . implode(";", $addDLFilters). ")";
                $result = $this->getList($DLparams);
            } else {
                $result = json_encode($result);
            }
            return $result;
        }
    }
    
    protected function makeFilterFromDLFilter($DLparams, $filters, $tvsInfo)
    {
        $elasticFilters = [];
        $tvNames = $this->getTvNames($tvsInfo);
        $addDLFilters = [];
        foreach ($filters as $filter) {
            $parts = explode(":", $filter, 4);
            $tvId = $tvNames[ $parts[1] ];
            switch ($parts[2]) {
                case 'eq':
                    $elasticFilters['bool']['filter'][]['term']['tv' . $tvId . '.keyword'] = mb_strtolower($parts[3], "UTF-8");
                    break;
                case 'in':
                    $elasticFilters['bool']['filter'][]['terms']['tv' . $tvId . '.keyword'] = array_map(function($a) {return mb_strtolower($a, "UTF-8");}, explode(',', $parts[3]));
                    break;
                case 'egt':
                    if ($this->checkNumber('tv' . $tvId)) {
                        $elasticFilters['bool']['filter'][]['range']['tv' . $tvId]['gte'] = $parts[3];
                    } else {
                        $addFilter[] = $filter;
                    }
                    break;
                case 'elt':
                    if ($this->checkNumber('tv' . $tvId)) {
                        $elasticFilters['bool']['filter'][]['range']['tv' . $tvId]['lte'] = $parts[3];
                    } else {
                        $addFilter[] = $filter;
                    }
                    break;
                case 'containsOne':
                    $elasticFilters['bool']['filter'][]['terms']['tv' . $tvId . '.keyword'] = array_map(function($a) {return mb_strtolower($a, "UTF-8");}, explode(',', $parts[3]));;
                    break;
                default:
                    break;
            }
        }
        $product_templates = array_map('trim', explode(',', $this->EF->config->getCFGDef('product_templates_id', 0)));
        $elasticFilters['bool']['filter'][]['terms']['template'] = $product_templates;
        return [ $elasticFilters, $addDLFilters ];
    }
    
    protected function convertElasticResult($result = [])
    {
        $rows = [];
        if (!empty($result['hits']['hits'])) {
            foreach ($result['hits']['hits'] as $row) {
                $rows[] = [ "id" => $row['_id'] ];
            }
        }
        return ["rows" => $rows, "total" => count($rows)];
    }
    
    protected function getTvNames($tvsInfo)
    {
        $tvNames = [];
        foreach ($tvsInfo as $id => $row) {
            $tvNames[ $row['name'] ] = $id;
        }
        return $tvNames;
    }
    
    protected function getMapping()
    {
        $properties = [];
        $tmp = $this->client->indices()->getMapping( ['index' => $this->indexKey] );
        if (!empty($tmp[$this->indexKey]['mappings']['properties'])) {
            $properties = $tmp[$this->indexKey]['mappings']['properties'];
        }
        return $mapping;
    }
    
    protected function checkNumber($propertyName)
    {
        $properties = !empty($this->mapping[$this->indexKey]['mappings']['properties']) ? $this->mapping[$this->indexKey]['mappings']['properties'] : [];
        return !empty($properties[$propertyName]['type']) && in_array($properties[$propertyName]['type'], $this->numFormats);
    }

}

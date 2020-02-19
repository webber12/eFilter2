<?php namespace eFilter;
include_once(MODX_BASE_PATH . 'assets/lib/Helpers/Config.php');
include_once(MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLTemplate.class.php');
include_once(MODX_BASE_PATH . 'assets/snippets/eFilter/Factories/ConfigFactory.php');
include_once(MODX_BASE_PATH . 'assets/snippets/eFilter/Factories/DBModelFactory.php');
include_once(MODX_BASE_PATH . 'assets/snippets/eFilter/Factories/ElementFactory.php');
include_once(MODX_BASE_PATH . 'assets/snippets/eFilter/Factories/DescendantsBuilderFactory.php');

if (!defined('MODX_BASE_PATH')) { die('What are you doing? Get out of here!'); }
$output = "";
//use Config;
//use DBModel;

class eFilter {

//id TV в котором хранятся настройки для категории товара
public $param_tv_id = '';

//имя TV в котором хранятся настройки для категории товара
public $param_tv_name = '';

//исходные параметры фильтра из json-строки multiTV
public $filter_param = array();

//все данные из таблицы site_tmplvars для тв, найденных в $filter_param
public $filter_param_info = array();

//массив заданных фильтров по категориям filter_cat -> array (tv_id)
public $filter_cats = array();

//массив заданных фильтров tv_id -> array (fltr_type,fltr_name,fltr_many)
public $filters = array();

//массив id tv входящих в заданный фильтр 
public $filter_tvs = array();

//массив id TV, входящих в список вывода для DocLister
public $list_tv_ids = array();

//массив имен TV, входящих в список вывода для DocLister
public $list_tv_names = array();

//массив имен (caption) TV, входящих в список вывода для DocLister
public $list_tv_captions = array();

//массив имен (описаний caption) tv входящих в заданный фильтр
public $filter_names = array();

//массив имен (name) tv входящих в заданный фильтр id1=>name1;id2=>name2
public $filter_tv_names = array();

//строка id tv заданных фильтров
public $filter_tv_ids = '';

//все возможные значения до фильтрации tv_id =>array()
//Array ( 
//          [14] => Array ( [синий] => Array ( [0] => 1 [1] => 1 ) [желтый] => Array ( [0] => 1 ) [красный] => Array ( [0] => 1 ) ) 
//          [16] => Array ( [Коллекция 1] => Array ( [0] => 1 ) [Коллекция 2] => Array ( [0] => 1 ) )
//          [17] => Array ( [S] => Array ( [0] => 1 ) [M] => Array ( [0] => 1 ) ) 
//      )
//можно посчитать количество по каждому из фильтров
public $filter_values_full = array();

//оставшиеся возможные значения после фильтрации tv_id =>array()
//Array ( 
//          [14] => Array ( [синий] => Array ( [0] => 1 [1] => 1 ) [желтый] => Array ( [0] => 1 ) [красный] => Array ( [0] => 1 ) ) 
//          [16] => Array ( [Коллекция 1] => Array ( [0] => 1 ) [Коллекция 2] => Array ( [0] => 1 ) ) 
//          [17] => Array ( [S] => Array ( [0] => 1 ) [M] => Array ( [0] => 1 ) ) 
//      )
//можно посчитать количество по каждому из фильтров
public $filter_values = array();

//текущие значения фильтра для поиска tv_id =>array()
public $curr_filter_values = array();

//текущие значения фильтра для поиска из $_GET['f']
public $fp = array();

//показывать 0 или ничего не показывать
public $zero = '';

//список id, значения которых не нужно сортировать
public $nosort_tv_id = array();

//тип фильтра для DocLister. По умолчанию - tvd
public $dl_filter_type;

//id tv, с помощью которого товары привязываются к категориям с помощью плагина tagSaver
public $tv_category_tag = 0;

//все продукты категории с учетом тегованных
public $categoryAllProducts = false;

//имя тв seocategory
public $seocategorytv = 'seocategory';

//собираем плейсхолдеры для установки
public $plh = [];

//парсер чанков
protected $tpl = null;

public function __construct($params = [])
{
    $this->modx = EvolutionCMS();
    $this->params = $params;
    $this->init($params);
}

protected function init($params = [])
{
    $this->config = (new \eFilter\Factories\ConfigFactory($params))->load();
    $this->addCustomConfig();
    $this->DBModel = (new \eFilter\Factories\DBModelFactory($this, $params))->load();
    $this->tpl = \DLTemplate::getInstance($this->modx);
    
    $this->docid = $this->config->getCFGDef('docid', $this->modx->documentIdentifier);
    $this->zero = !empty($this->config->getCFGDef('hideZero', 0)) ? '' : 0;
    $this->endings = !empty($this->config->getCFGDef('endings', '')) ? explode(',', $this->config->getCFGDef('endings', '')) : array('товар', 'товара', 'товаров');
    $this->nosort_tv_id = !empty($this->config->getCFGDef('nosortTvId', '')) ? explode(',', $this->config->getCFGDef('nosortTvId', '')) : array();
    $this->seocategorytv = $this->config->getCFGDef('seocategorytv', 'seocategory');
    $this->param_tv_name = $this->getParamTvName();

    $this->getFP();
    $this->prepareGetParams($this->fp);
    $this->setCommaAsSeparator();
}


public function process()
{
    //получаем значение параметров для категории товара в виде массива
    //если у ресурса не задано - смотрим родителя, если у родителя нет- смотрим дедушку
    $this->filter_param = $this->getFilterParam($this->param_tv_name);

    //формируем основные массивы для описания наших фильтров
    //на основе заданного в multiTV конфига
    //$this->filter_tvs;
    //$this->filter_names;
    //$this->filter_cats;
    //$this->filters;
    //$this->list_tv_ids;

    if (!empty($this->filter_param['fieldValue'])) {//если не пусто, то формируем фильтры
        $this->makeFilterArrays();
    }

    //получаем список id tv через запятую
    //которые участвуют в фильтрации
    //для последующих запросов
    if (!empty($this->filter_tvs)) {
        $this->filter_tv_ids = implode(',', $this->filter_tvs);
    }

    //получаем из конфигурации фильтра имена ТВ, которые используются в фильтрации
    $this->filter_tv_names = $this->getTVNames($this->filter_tv_ids);

    //получаем из конфигурации фильтра имена ТВ, которые используются в списке (для tvList DocLister)
    if (!empty($this->list_tv_ids)) {
        $this->list_tv_names = $this->getTVNames(implode(',', $this->list_tv_ids));
        $this->list_tv_captions = $this->getTVNames(implode(',', $this->list_tv_ids), 'caption');
        $this->list_tv_elements = $this->getTVNames(implode(',', $this->list_tv_ids), 'elements');
    }

    //параметры DocLister по умолчанию
    //он используется для поиска подходящих id ресурсов как без фильтров (категория, вложенность, опубликованность, удаленность и т.п.)
    //так и с использованием фильтра
    //на выходе получаем список id подходящих ресурсов через запятую
    $addWhereList = $this->config->getCFGDef('addWhereList', '');
    $DLparams = array(
                    'parents' => $this->docid, 
                    'depth' => $this->config->getCFGDef('depth', 3), 
                    'addWhereList' => (!empty($addWhereList) ? $addWhereList . ' AND ' : '') . 'c.template IN(' . $this->config->getCFGDef('product_templates_id', 0) . ')', 
                    'makeUrl' => '0'
                );
    $filter_ids = $this->modx->getPlaceholder("eFilter_filter_ids");
    if ($filter_ids && $filter_ids != '') {
        $DLparams['addWhereList'] .= ' AND c.id IN (' . $filter_ids . ') ';
    }
    $DLparamsAPI = array('JSONformat' => 'new', 'api' => 'id', 'selectFields' => 'c.id');
    $allProducts = $this->getCategoryAllProducts($this->docid, (int)$this->config->getCFGDef('tv_category_tag', 0));
    if (empty($allProducts)) return false;//если документов нет, то и делать ничего дальше не нужно

    unset($DLparams['parents']);
    unset($DLparams['depth']);
    $DLparams['documents'] = $allProducts;
    $DLparamsAll = array_merge($DLparams, $DLparamsAPI);

    //это список всех id товаров данной категории, дальше будем вычленять ненужные
    $_ = $this->DBModel->getList($DLparamsAll);
    $this->content_ids_full = $this->getListFromJson($_);

    //получаем $eFltr->content_ids
    //это пойдет в плейсхолдер (список documents через запятую
    //как все подходящие к данному фильтру товары
    //для подстановки в вызов DocLister и вывода списка отфильтрованных товаров на сайте
    $this
        ->makeAllContentIDs($DLparamsAll)

    //начинаем формировать фильтр
    //проходимся по каждому фильтру и берем список всех товаров с учетом всех фильтров кроме текущего
    //формируем по итогам массив $eFltr->curr_filter_values
    //в котором каждому id тв фильтра соответствует список документов, которые подходят для всего фильтра за 
    //исключением текущего
        ->makeCurrFilterValuesContentIDs($DLparamsAll)

    //берем все доступные значения для параметров до фильтрации и устанавливаем в $this->filter_values_full
        ->getFilterValues($this->content_ids_full, $this->filter_tv_ids)

    //берем доступные  значения для параметров после фильтрации
    //и формируем вывод фильтра с учетом количества для каждого из значений фильтра
    //количество считаем исходя из сформированного списка подходящих документов из массива и ставим в $this->curr_filter_values
        ->getFilterFutureValues($this->curr_filter_values, $this->filter_tv_ids);
    return true;
}

public function postProcess()
{
    $output = $this->renderFilterBlock($this->filter_cats, $this->filter_values_full, $this->filter_values, $this->filters, $this->config->getCFGDef('cfg', 'default'));

    //устанавливаем плейсхолдеры
    $this->setPlaceholders(
        array(
            //список документов для вывода (подставляем в DocLister, это происходит автоматом в сниппете getFilteredItems)
            "eFilter_ids" => $this->content_ids,
        
            //количество документов найденных при фильтрации
            //если искали и ничего не нашли (isset($_GET['f'])) - 0, если не искали - пусто ''
            "eFilter_ids_cnt" => $this->content_ids_cnt,
        
            //товар-товара-товаров в зависимости от количества и пусто, если ничего не искали
            "eFilter_ids_cnt_ending" => $this->content_ids_cnt_ending,
        
            //форма вывода фильтра - вставить плейсхолдер в нужное место шаблона
            "eFilter_form" => $output,
        
            //перечень tv для вывода в список товаров
            //нужно для обозначения в списке tvList вызова DocLister  в сниппете getFilteredItems
            "eFilter_tv_list" => $this->list_tv_names,
        
            //перечень tv для вывода в список товаров
            //нужно для вывода названий параметра рядом с его значением
            "eFilter_tv_names" => $this->list_tv_captions,
        
            //перечень elements tv для вывода в список товаров
            //нужно для определения откуда взято значение - из дерева или нет
            "eFilter_tv_elements" => $this->list_tv_elements
        )
    );
    $this->regClientScript();
}

protected function addCustomConfig()
{
    $theme = $this->config->getCFGDef('cfg', 'default');
    $name = $theme . ':' . __DIR__ . '/config/';
    $this->config->loadConfig($name);
    return true;
}

public function getParamTvName($tv_id = '')
{
    $tv_id = !empty($tv_id) ? $tv_id : $this->config->getCFGDef('param_tv_id', 0);
    return $this->DBModel->getTvName($tv_id);
}

public function getFilterParam($param_tv_name, $docid = 0)
{
    if (!$docid) {
        $docid = $this->docid;
    }
    $filter_param = array();
    $tv_config = $this->config->getCFGDef('tvConfig', '');
    if ($tv_config != '') {
        $filter_param = json_decode($tv_config, true);
    } else {
        $tv = $this->modx->getTemplateVar($param_tv_name, '*', $docid);
        $param_tv_val = $tv['value'] != '' ? $tv['value'] : $tv['defaultText'];
        if ($param_tv_val != '' && $param_tv_val != '[]' && $param_tv_val != '{"fieldValue":[{"param_id":""}],"fieldSettings":{"autoincrement":1}}') {//если задано для категории, ее и берем
            $filter_param = json_decode($param_tv_val, true);
        } else {//если не задано, идем к родителю
            $filter_param = $this->_getParentParam($docid, $param_tv_name);
        }
    }
    if (!empty($filter_param) && !empty($filter_param['fieldValue'])) {
        $tv_ids = array_column($filter_param['fieldValue'], 'param_id');
        $this->filter_param_info = $this->DBModel->getTVsInfo($tv_ids);
    }
    return $filter_param;
}

public function _getParentParam($docid, $param_tv_name) {
    $filter_param = array();
    $parent = $this->DBModel->getParent($docid);
    if ($parent || $parent == 0) {
        $tv = $this->modx->getTemplateVar($param_tv_name, '*', $docid);
        $param_tv_val = $tv['value'] != '' ? $tv['value'] : $tv['defaultText'];
        if ($param_tv_val != '' && $param_tv_val != '{"fieldValue":[{"param_id":""}],"fieldSettings":{"autoincrement":1}}' && $param_tv_val != '[]') {
            $filter_param = json_decode($param_tv_val, true);
        }  else {
            if ($parent) {
                $filter_param = $this->_getParentParam($parent, $param_tv_name);
            }
        }
    }
    return $filter_param;
}

public function makeFilterArrays()
{
    $this->common_filter_tvs = $this->common_filter_names = $this->common_filter_cats = $this->common_filters = array();
    foreach ($this->filter_param['fieldValue'] as $k => $v) {
        if ($v['fltr_yes'] == '1'){
            $this->filter_tvs[] = $v['param_id'];
            $this->filter_names[$v['fltr_name']] = $v['param_id'];
            $this->filter_cats[$v['cat_name']][$v['param_id']] = '1';
            $this->filters[$v['param_id']]['type'] = $v['fltr_type'];
            $this->filters[$v['param_id']]['name'] = $v['fltr_name'];
            $this->filters[$v['param_id']]['many'] = $v['fltr_many'];
            $this->filters[$v['param_id']]['href'] = $v['fltr_href'];
        }
        if ($v['list_yes'] == '1'){
            $this->list_tv_ids[] = $v['param_id'];
        }
        $this->common_filter_tvs[] = $v['param_id'];
        $this->common_filter_names[$v['fltr_name']] = $v['param_id'];
        $this->common_filter_cats[$v['cat_name']][$v['param_id']] = '1';
        $this->common_filters[$v['param_id']]['type'] = $v['fltr_type'];
        $this->common_filters[$v['param_id']]['name'] = $v['fltr_name'];
        $this->common_filters[$v['param_id']]['many'] = $v['fltr_many'];
        $this->common_filters[$v['param_id']]['href'] = $v['fltr_href'];
    }
}

protected function sortFilterValues($tv_id, $tv_elements, $filter_values_full)
{
    if (in_array($tv_id, $this->nosort_tv_id) || (isset($this->nosort_tv_id[0]) && $this->nosort_tv_id[0] == 'all')) {
        $sort_tmp = [];
        foreach($tv_elements[$tv_id] as $k => $v) {
            if ($filter_values_full[$tv_id][$k]) {
                $sort_tmp[$k] = $filter_values_full[$tv_id][$k];
            }
        }
        $filter_values_full[$tv_id] = $sort_tmp;
        unset($sort_tmp);
    } else {
        uksort($filter_values_full[$tv_id], create_function('$a,$b', 'return is_numeric($a) && is_numeric($b) ? ($a-$b) : strcasecmp(strtolower($a), strtolower($b));'));
    }
    return $filter_values_full;
}

protected function prepareFilterValues($tv_id, $tv_types, $tv_elements, $filter_values_full)
{
    //заменяем значения-id на заголовки страниц для тв типа selector
    if ($tv_types[$tv_id] == 'custom_tv:selector') {
        $selector_elements = $this->DBModel->getSelectorElements(array_keys($filter_values_full[$tv_id]));
        foreach ($selector_elements as $id => $pagetitle) {
            $tv_elements[$tv_id][$id] = $pagetitle;
        }
    }
    return $tv_elements;
}

public function renderFilterBlock($filter_cats, $filter_values_full, $filter_values, $filters, $config = '')
{
    $output = '';
    $fc = 0;
    $categoryWrapper = '';
    $isEmpty = true;
    $ElementFactory = new \eFilter\Factories\ElementFactory($this);
    $filterElements = [1 => 'checkbox', 2 => 'select', 3 => 'interval', 4 => 'radio', 5 => 'multySelect', 6 => 'slider', 7 => 'colors', 8 => 'pattern'];

    foreach ($filter_cats as $cat_name => $tmp) {
        $output = '';
        $tv_elements = $this->getDefaultTVValues($tmp);
        $tv_types = $this->getTVNames(implode(',', array_keys($tmp)), 'type');
        foreach ($tmp as $tv_id => $tmp2) {
            if (isset($filter_values_full[$tv_id])) {
                $wrapper = '';
                $count = '';
                $tv_elements = $this->prepareFilterValues($tv_id, $tv_types, $tv_elements, $filter_values_full);
                $filter_values_full = $this->sortFilterValues($tv_id, $tv_elements, $filter_values_full);

                $type = empty(trim($filters[$tv_id]['type'])) ? 1 : trim($filters[$tv_id]['type']);
                $element = !empty($filterElements[$type]) ? $filterElements[$type] : $type;
                $output .= $ElementFactory
                            ->load( [ 'name' => $element ] )
                            ->setParam('filters', $filters)
                            ->setParam('filter_values_full', $filter_values_full)
                            ->setParam('filter_values', $filter_values)
                            ->setParam('tv_id', $tv_id)
                            ->setParam('tv_elements', $tv_elements)
                            ->setParam('fp', $this->fp)
                            ->setParam('activeRowClass', $this->config->getCFGDef('activeRowClass', ' active '))
                            ->setParam('activeBlockClass', $this->config->getCFGDef('activeBlockClass', ' active '))
                            ->setParam('removeDisabled', $this->config->getCFGDef('removeDisabled', 0))
                            ->setParam('allowZero', $this->config->getCFGDef('allowZero', 0))
                            ->setParam('zero', $this->zero)
                            ->setParam('hideEmptyBlock', $this->config->getCFGDef('hideEmptyBlock', 0))
                            ->setParam('content_ids', $this->content_ids)
                            ->setParam('content_ids_full', $this->content_ids_full)
                            ->setParam('curr_filter_values', $this->curr_filter_values)
                            ->render();
            }
        }
        if ($output != '') {//есть, как минимум, одна непустая категория, т.е. фильтр надо выводить
            $isEmpty = false;
        }
        $cat_name = $this->parseTpl([ '[+cat_name+]' ], [$cat_name], $ElementFactory->load( [ 'name' => 'checkbox' ] )->getTpl('categoryNameRow'));
        $categoryWrapper .= $this->parseTpl(
            array('[+cat_name+]', '[+iteration+]', '[+wrapper+]'),
            array($cat_name, $fc, $output),
            $ElementFactory->load( [ 'name' => 'checkbox' ] )->getTpl('categoryOuter')
        );
        $fc++;
    }
    $output = $categoryWrapper; 
    $tpl = $ElementFactory->load( [ 'name' => 'checkbox' ] )->getTpl('filterForm');
    $resetTpl = $ElementFactory->load( [ 'name' => 'checkbox' ] )->getTpl('filterReset');

    $form_url = $this->getFormUrl();

    $form_result_cnt = !empty($this->content_ids_cnt) 
                ? $this->parseTpl(
                    [ '[+cnt+]', '[+ending+]' ], 
                    [ $this->content_ids_cnt, $this->content_ids_cnt_ending ], 
                    $this->config->getCFGDef('cntTpl', '@CODE:Найдено: [+cnt+] [+ending+]')) 
                : '';

    $output = !$isEmpty 
                ? $this->parseTpl( 
                    [ '[+url+]', '[+wrapper+]', '[+btn_text+]', '[+form_result_cnt+]', '[+form_method+]' ], 
                    [ $form_url, $output, $this->config->getCFGDef('btnText', 'Найти'), $form_result_cnt, $this->config->getCFGDef('formMethod', 'get') ], 
                    $tpl)
                : '';

    $output .= !$isEmpty 
                ? $this->parseTpl( 
                    [ '[+reset_url+]' ], 
                    [ $form_url ], 
                    $resetTpl) 
                : '';
    return $output;
}

protected function collectFilterValues($rows = [], $filter_values = [])
{
    foreach ($rows as $row) {
        if ($this->commaAsSeparator) {
            if ($this->commaAsSeparator === true || (is_array($this->commaAsSeparator) && in_array($row['tmplvarid'], $this->commaAsSeparator))) {
                $row['value'] = str_replace(',', '||', $row['value']);
            }
        }
        if (strpos($row['value'], '||') === false) {
            $v = $row['value'];
            if (isset($filter_values[$row['tmplvarid']][$v]['count'])) {
                $filter_values[$row['tmplvarid']][$v]['count'] += 1;
            } else {
                $filter_values[$row['tmplvarid']][$v]['count'] = 1;
            }
        } else {
            $tmp = array_map('trim', explode("||", $row['value']));
            foreach ($tmp as $v) {
                if (isset($filter_values[$row['tmplvarid']][$v]['count'])) {
                        $filter_values[$row['tmplvarid']][$v]['count'] += 1;
                } else {
                        $filter_values[$row['tmplvarid']][$v]['count'] = 1;
                }
            }
        }
    }
    return $filter_values;
}

protected function getFilterValues($content_ids, $filter_tv_ids = '')
{
    $filter_values = [];
    if ($content_ids != '') {//берем только если есть какие-то документы
        $rows = $this->DBModel->getFilterValues($content_ids, $filter_tv_ids);
        $filter_values = $this->collectFilterValues($rows, $filter_values);
    }
    $this->filter_values_full = $filter_values;
    return $this;
}

protected function getFilterFutureValues($curr_filter_values, $filter_tv_ids = '')
{
    $filter_values = array();
    if (!empty($curr_filter_values)) {//берем только если есть какие-то документы
        foreach ($curr_filter_values as $tv_id => $ids) {
            if (isset($ids['content_ids']) && $ids['content_ids'] != '') {
                $content_ids = $ids['content_ids'] == 'all' ? $this->content_ids : $ids['content_ids'];
                $rows = $this->DBModel->getFilterValues($content_ids, $filter_tv_ids, $tv_id);
                $filter_values = $this->collectFilterValues($rows, $filter_values);
            }
        }
    }
    $this->filter_values = $filter_values;
    return $this;
}

protected function collectDLFilter($f, $fid = false)
{
    $fltr = '';
    foreach ($f as $tvid => $v) {
        if (!$fid || $tvid != $fid) {
            $tvid = (int)$tvid;
            $oper = 'eq';
        
            if (isset($v['min']) || isset($v['max'])) { //если параметр - диапазон
                if (isset($v['min']) && (int)$v['min'] != 0 ) {
                    $fltr .= $this->config->getCFGDef('DLFilterType', 'tvd') . ':' . $this->filter_tv_names[$tvid] . ':egt:' . (int)$v['min'] . ';';
                }
                if (isset($v['max']) && (int)$v['max'] != 0 ) {
                    $fltr .= $this->config->getCFGDef('DLFilterType', 'tvd') . ':' . $this->filter_tv_names[$tvid] . ':elt:' . (int)$v['max'] . ';';
                }
            } else {//если значение/значения, но не диапазон
                if (is_array($v)) {
                    if (empty($this->config->getCFGDef('allowZero', 0))) {
                        foreach($v as $k1 => $v1) {
                            if ($v1 == '0') {
                                unset($v[$k1]);
                            }
                        }
                    }
                    $val = implode(',', $v);
                    if (count($v) > 1) {
                        $oper = 'in';
                    }
                } else {
                    $val = ($v == 0 || $v == '') ? '' : $v; 
                }
                if ($tvid != 0 && isset($this->filter_tv_names[$tvid]) && $val != '') {
                    if ($this->filters[$tvid]['many'] == '1') {
                        if (!empty($this->config->getCFGDef('useRegexp', 0))) {
                            $oper = 'regexp';
                            $val = '[[:<:]]' . str_replace(array(',', '||'), '[[:>:]]|[[:<:]]', $val) . '[[:>:]]';
                        } else {
                            $oper = 'containsOne';
                        }
                    }
                    $val = str_replace(array('(', ')'), array('\(', '\)'), $val);
                    $fltr .= $this->config->getCFGDef('DLFilterType', 'tvd') . ':' . $this->filter_tv_names[$tvid] . ':' . $oper . ':' . $val.';';
                }
            }
        }
    }
    return substr($fltr, 0 , -1);
}

public function makeAllContentIDs($DLparams)
{
    $this->content_ids = '';
    if (!empty($this->fp)) {//разбираем фильтры из строки GET и добавляем их в фильтр DocLister
        $f = $this->fp;
        $this->content_ids = '';
        if (is_array($f)) {

            $fltr = $this->collectDLFilter($f);

            if ($fltr != '') {
                $fltr = 'AND(' . $fltr . ')';
                $DLparams['filters'] = $fltr;
                $_ = $this->DBModel->getList($DLparams);
                $this->content_ids = $this->getListFromJson($_);
            } else {
                if ($this->categoryAllProducts) {
                    $this->content_ids = $this->categoryAllProducts;
                }
            }
        }
    } else {//если ничего не искали и у нас есть список всех продуктов категории, их и ставим
        if ($this->categoryAllProducts) {
            $this->content_ids = $this->categoryAllProducts;
        }
    }
    $this->decorateFilterResult($this->content_ids, $this->fp);
    return $this;
}

public function makeCurrFilterValuesContentIDs ($DLparams)
{
    $content_ids_list = false;
    if (!empty($this->fp)) {//разбираем фильтры из строки GET и считаем возможные значения и количество для этих фильтров без учета одного из них (выбранного)
        $f = $this->fp;
        if (is_array($f)) {
            foreach ($this->filter_tv_names as $fid =>$name) {
                $fltr = '';
                if (isset($f[$fid])) {

                    $fltr = $this->collectDLFilter($f, $fid);

                }
                if ($fltr != '') {
                    $fltr = 'AND(' . $fltr . ')';
                    $DLparams['filters'] = $fltr;
                    $_ = $this->DBModel->getList($DLparams);
                    $this->curr_filter_values[$fid]['content_ids'] = $this->getListFromJson($_);
                } else {
                    unset($DLparams['filters']);
                    if (isset($f[$fid])) {
                        if (!$content_ids_list) {
                            $_ = $this->DBModel->getList($DLparams);
                            $content_ids_list = $this->getListFromJson($_);
                        }
                        $this->curr_filter_values[$fid]['content_ids'] = $content_ids_list;
                    } else {
                        if (isset($this->content_ids) && $this->content_ids != '') {
                            $this->curr_filter_values[$fid]['content_ids'] = 'all';
                        } else {
                            if (!$content_ids_list) {
                                $_ = $this->DBModel->getList($DLparams);
                                $content_ids_list = $this->getListFromJson($_);
                            }
                            $this->curr_filter_values[$fid]['content_ids'] = $content_ids_list;
                        }
                    }
                }
            }
        }
    }
    return $this;
}

public function prepareGetParams($fp)
{
    $tmp = array();
    if (isset($fp['f']) && is_array($fp['f'])) {
        $tmp = $fp['f'];
    } else {
        //расшифровываем GET-строку формата f16=значение1,значение2&f17=значение3,значение4&f18=minmax~100,300 и преобразуем ее в обычный стандартный массив для обработки eFilter, 
        // array(
        //    "16" => array("значение1", "значение2"),
        //    "17" => array("значение3", "значение4"),
        //    "18" => array ("min" => "100", "max" => "300")
        //);
        //значения изначально должны быть url-кодированными, например через метод js encodeURIComponent
        foreach ($fp as $k => $v) {
            if (preg_match("/^f(\d+)/i", $k, $matches)) {
                $key = $matches[1];
                if (isset($matches[1]) && is_scalar($matches[1])) {
                    $minmax = strpos($v, 'minmax~');
                    if ($minmax !== false) {
                        $v = str_replace('minmax~', '', $v);
                    }
                    $tmp2 = explode(',', $v);
                    foreach ($tmp2 as $k2 => $v2) {
                        $tmp2[$k2] = urldecode($v2);
                    }
                    if ($minmax !== false) {
                        $tmp[$matches[1]]['min'] = isset($tmp2[0]) ? $tmp2[0] : '';
                        $tmp[$matches[1]]['max'] = isset($tmp2[1]) ? $tmp2[1] : '';
                    } else {
                        $tmp[$matches[1]] = $tmp2;
                    }
                }
            }
        }
    }
    $this->fp = $tmp;
}

public function getFP () {
    //готовим почву для передачи нужных параметров фильтрации прямо при вызове фильтра
    //вида &fp=`f16=значение1,значение2&f17=значение3,значение4&f18=minmax~100,300`
    //todo seo url
    $fp = $this->config->getCFGDef('fp', false);
    $this->fp = (!empty($fp)) ? $fp : (!empty($_GET) ? $_GET : array());
    return $this;
}
public function prepareGetParamsOld ($fp)
{
    $out = array();
    if (is_scalar($fp) && $fp != '') {
        //расшифровываем GET-строку формата f=1~значение1,значение2||2~значение3,значение4||3~100,300~minmax и преобразуем ее в обычный массив $f, 
        //где 1,2,3 - id соответствующих тв для фильтрации, значение1,значение2 - из значения через запятую
        //значения изначально должны быть url-кодированными, например через метод js encodeURIComponent
        //на выходе получим нужный нам массив 
        //$f = array(
        //    "1" => array("значение1", "значение2"),
        //    "2" => array("значение3", "значение4"),
        //    "3" => array ("min" => "100", "max" => "300")
        //);
        $fp = urldecode($fp);
        $tmp = explode("||", $fp);
        foreach ($tmp as $v) {
            $tmp2 = explode("~", $v);
            $tmp3 = isset($tmp2[1]) && $tmp2[1] != '' ? explode(",", $tmp2[1]) : array();
            $tv_id = (int)$tmp2[0];
            if (isset($tmp2[2]) && $tmp2[2] == 'minmax') {
                $out['f'][$tv_id]['min'] = $tmp3[0];
                $out['f'][$tv_id]['max'] = ($tmp3[1] != '' ? $tmp3[1] : '');
            } else {
                $out['f'][$tv_id] = $tmp3;
            }
        }
        if (!empty($out['f'])) {
            $this->fp = $out['f'];
        } else {
            $this->fp = array();
        }
    } else {
        $this->fp = $fp;
    }
}

protected function getDefaultTVValues($array = array())
{
    $out = array();
    $tvs = implode(",", array_keys($array));
    if ($tvs != '') {
        $elements = $this->getTVNames($tvs, 'elements');
        foreach ($elements as $tv_id => $element) {
            if (stristr($element, "@EVAL")) {
                $element = trim(substr($element, 6));
                $element = str_replace("\$modx->", "\$this->modx->", $element);
                $element = eval($element);
            }
            if ($element != '') {
                $tmp = explode("||", $element);
                foreach ($tmp as $v) {
                    $tmp2 = explode("==", $v);
                    $key = isset($tmp2[1]) && $tmp2[1] != '' ? $tmp2[1] : $tmp2[0];
                    $value = $tmp2[0];
                    if ($key != '') {
                        $out[$tv_id][$key] = $value;
                    }
                }
            }
        }
    }
    $this->modx->ef_elements_name = $out;
    return $out;
}

public function getListFromJson($json = '', $field = 'id', $separator = ',')
{
    $out = '';
    $_ = array();
    if (!empty($json)) {
        $tmp = json_decode($json, true);
        if (!empty($tmp) && isset($tmp['rows'])) {
            foreach ($tmp['rows'] as $row) {
                $_[] = $row[$field];
            }
        }
        $out = implode($separator, $_);
    }
    return $out;
}

//возвращает список всех дочерних товаров категории плюс товаров, прикрепленных к категории тегом tagSaver через tv с id=$tv_id
public function getCategoryAllProducts($id, $tv_id)
{
    //если хотим искать только по заданным документам, то до вызова [!eFilter!] устанавливаем их спискок в плейсхолдер eFilter_search_ids
    $search_ids = $this->modx->getPlaceholder("eFilter_search_ids");
    if ($search_ids && $search_ids != '') {
        $filter_ids = $this->modx->getPlaceholder("eFilter_filter_ids");
        if ($filter_ids && $filter_ids != '') {//если еще и установили ограничитель списка id в плейсхолдер eFilter_filter_ids 
            $search_ids = implode(',', array_intersect(explode(',', $search_ids), explode(',', $filter_ids)));
        }
        $this->categoryAllProducts = $search_ids;
        return $search_ids;
    }

    $seocat = false;
    if (!empty($this->modx->documentObject[$this->seocategorytv][1])) {
        //берем товары, которые принадлежат категории, указанной в tv с именем $this->seocategorytv
        $id = $this->modx->documentObject[$this->seocategorytv][1];
        $seocat = true;
    }

    $DescendantsBuilder = (new \eFilter\Factories\DescendantsBuilderFactory($this))->load();
    
    $DescendantsBuilder
        ->setParam('id', $id)
        ->setParam('DBModel', $this->DBModel)
        ->setParam('tagSaverTvId', $tv_id)
        ->setParam('seocat', $seocat)
        ->setParam('useMultiCategories', $this->config->getCFGDef('useMultiCategories', 0))
        ->setParam('product_templates_id', $this->config->getCFGDef('product_templates_id', 0))
        ->setParam('DLFilterType', $this->config->getCFGDef('DLFilterType', 'tvd'))
        ->setParam('common_filters', $this->common_filters)
        ->setParam('useRegexp', $this->config->getCFGDef('useRegexp', 0));

    $children = $DescendantsBuilder
        ->buildChildren()
        ->getChildren();

    $this->categoryAllProducts = implode(',', array_keys($children));
    return $this->categoryAllProducts;
}

public function setCommaAsSeparator()
{
    $this->commaAsSeparator = false;
    $commaAsSeparator = $this->config->getCFGDef('commaAsSeparator', 0);
    if (!empty($commaAsSeparator)) {
        $commaAsSeparator = trim($commaAsSeparator);
        if ($commaAsSeparator == "all") {
            $this->commaAsSeparator = true;
        } else {
            $this->commaAsSeparator = array_map('trim', explode(',', $commaAsSeparator));
        }
    }
    return $this;
}

protected function regClientScript()
{
    $this->modx->regClientCSS('assets/snippets/eFilter/html/css/eFilter.css');
    $this->modx->regClientCSS('assets/snippets/eFilter/html/css/slider.css');
    $this->modx->regClientScript('assets/snippets/eFilter/html/js/jquery-ui.min.js');
    $this->modx->regClientScript('assets/snippets/eFilter/html/js/jquery.ui.touch-punch.min.js');
    //вкл ajax
    if ($this->config->getCFGDef('ajax', 0) == 1) {
        $this->modx->regClientScript('<script>var eFiltrAjax = "1";</script>', array('plaintext' => true));
    }
    //автосабмит формы
    $this->modx->regClientScript('<script>var eFiltrAutoSubmit = "' . $this->config->getCFGDef('autoSubmit', 1) . '";</script>', array('plaintext' => true));
    //режим аякс: 1 - полный, 2 - перегружается только форма, а список по кнопке submit без ajax
    if ((int)$this->config->getCFGDef('ajaxMode', 0)) {
        $this->modx->regClientScript('<script>var eFiltrAjaxMode = "' . (int)$this->config->getCFGDef('ajaxMode', 0) . '";</script>', array('plaintext' => true));
    }
    //изменять адрес url после запросов
    if (!empty($this->config->getCFGDef('changeState', 0))) {
        $this->modx->regClientScript('<script>var eFiltrChangeState = "' . $this->config->getCFGDef('changeState', 0) . '";</script>', array('plaintext' => true));
    }
    $this->modx->regClientScript('assets/snippets/eFilter/html/js/eFilter.js');
}


/***** Helpers ***********/
public function isFilter()
{
    return !empty($this->fp);
}

protected function decorateFilterResult($content_ids, $fp)
{
    $this->content_ids_cnt = $content_ids != '' ? count(explode(',', $content_ids)) : (!empty($fp) ? '0' : '-1');
    if ($this->content_ids_cnt != '-1' && $this->content_ids_cnt != '0') {
        $this->content_ids_cnt_ending = $this->getNumEnding($this->content_ids_cnt, $this->endings);
    } else if ($this->content_ids_cnt == 0) {
        $this->content_ids_cnt_ending = isset($this->endings[2]) ? $this->endings : 'товаров';
    } else {
        $this->content_ids_cnt_ending = '';
    }
    return $this;
}


protected function setPlaceholders($array = array())
{
    if (!empty($array)) {
        foreach ($array as $k => $v) {
            $this->modx->setPlaceholder($k, $v);
        }
    }
}

public function getNumEnding($number, $endingArray)
{
    $number = $number % 100;
    if ($number >= 11 && $number <= 19) {
        $ending=$endingArray[2];
    } else {
        $i = $number % 10;
        switch ($i) {
            case (1): $ending = $endingArray[0]; break;
            case (2):
            case (3):
            case (4): $ending = $endingArray[1]; break;
            default: $ending=$endingArray[2];break;
        }
    }
    return $ending;
}

public function getTVNames($tv_ids = '', $field = 'name')
{
    $tv_names = [];
    if (!empty($tv_ids) && !empty($this->filter_param_info)) {
        $tmp = explode(',', $tv_ids);
        foreach ($tmp as $tv_id) {
            if (!empty($this->filter_param_info[$tv_id]) && !empty($this->filter_param_info[$tv_id][$field])) {
                $tv_names[$tv_id] = $this->filter_param_info[$tv_id][$field];
            }
        }
    }
    return $tv_names;
}

public function parseTpl($array1, $array2, $tpl)
{
    $str = '';
    if (is_null($this->tpl)) {
        $str = str_replace($array1, $array2, $tpl);
    } else {
        $array1 = array_map(function($a) {return str_replace(['[+', '+]'], '', $a);}, $array1);
        $data = array_combine($array1, $array2);
        $str = $this->tpl->parseChunk($tpl, $data);
    }
    return $str;
}

protected function getFormUrl()
{
    $tmp = explode('?', $_SERVER['REQUEST_URI']);
    $submitPage = trim($this->config->getCFGDef('submitPage', ''));
    $submitDocPage = $this->config->getCFGDef('submitDocPage', 0);
    if (is_numeric($submitPage)) {
        $form_url = $this->modx->makeUrl($submitPage);
    } else {
        $form_url = (!empty($tmp[0]) && !empty($submitDocPage)) ? $tmp[0] : $this->modx->makeUrl($this->docid);
    }
    return $form_url;
}

}

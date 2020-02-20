<?php namespace eFilter\Controllers;

use eFilter\Controllers\DescendantsBuilderAbstract;

class DescendantsBuilder extends DescendantsBuilderAbstract
{
    protected function getCategoryProductsChildren()
    {
        $p = array(
            'parents' => $this->id,
            'depth' => 6,
            'JSONformat' => 'new',
            'api' => 'id',
            'selectFields' => 'c.id',
            'makeUrl' => '0',
            'debug' => '0',
            'addWhereList' => 'template IN (' . $this->product_templates_id . ')'
        );
        $filter_ids = $this->modx->getPlaceholder("eFilter_filter_ids");
        if (!empty($filter_ids)) {
            $p['addWhereList'] .= ' AND c.id IN (' . $filter_ids . ') ';
        }
        $json = $this->DBModel->getList($p);
        if ($json && !empty($json)) {
            $arr = json_decode($json, true);
            if (!empty($arr) && isset($arr['rows'])) {
                $tmp2 = array();
                foreach ($arr['rows'] as $v) {
                $this->children[$v['id']] = 1;
                }
            }
        }
        return $this;
    }
    
    protected function getCategoryProductsMultiCategories()
    {
        if (!empty($this->useMultiCategories)) {
            $categories = array();
            //добавляем дочерние категории
            $childs = $this->modx->getChildIds($id, 5);
            if (!empty($childs)) {
                $categories = array_values($childs);
            }
            //и саму категорию
            $categories[] = $this->id;
            //берем все товары данных мультикатегорий
            $multiChildren = $this->DBModel->getMultiCategoryChildren($categories);
            foreach ($multiChildren as $k => $v) {
                $this->children[$k] = $v;
            }
        }
        return $this;
    }
    
    protected function getCategoryProductsTagSaver()
    {
        if (!empty($this->tagSaverTvId)) {
            //доп.условие, если задан сторонний ограничитель в плейсхолдере ранее
            $add_where = '';
            $filter_ids = $this->modx->getPlaceholder("eFilter_filter_ids");
            if (!empty($filter_ids)) {
                $add_where = ' AND b.doc_id IN (' . $filter_ids . ') ';
            }

            $tmp_parents = [];

            //берем id всех товаров, прикрепленные ко всем дочерним "категориям" относительно текущей категории (через tv "категория")
            //нужны только папки, потому берем только из кэша
            $aliaslistingfolder = $this->modx->config['aliaslistingfolder'];
            $this->modx->config['aliaslistingfolder'] = '0';
            $childs = $this->modx->getChildIds($this->id, 5);
            $this->modx->config['aliaslistingfolder'] = $aliaslistingfolder;
            if (!empty($childs)) {
                //исключаем случайные "товары-папки" и кэшированные "непапки"
                $tmp_parents = $this->DBModel->getChildrenFolders(array_values($childs), $this->product_templates_id);
            }
            //берем id всех товаров, привязанных к этой категории через tv category id=$tv_id
            $tmp_parents[] = $this->id;
    
            //собираем все прикрепленные к данным папкам товары
            $tagsChildren = $this->DBModel->getTagsChildren($this->tagSaverTvId, $tmp_parents, $add_where);
            foreach ($tagsChildren as $k => $v) {
                $this->children[$k] = $v;
            }
        }
        return $this;
    }
    
    protected function getSeoChildren()
    {
        if (!empty($this->seocat) && !empty($this->children)) {
            $out = array();
            $common_tv_names = $this->EF->getTVNames(implode(',', array_keys($this->common_filters)));
            $tvs = $this->modx->getTemplateVarOutput(array_keys($this->common_filters), $this->modx->documentIdentifier);
            $seoFilters = array();
            foreach ($this->common_filters as $k => $v) {
                $seo_tv_name = !empty($common_tv_names[$k]) ? $common_tv_names[$k] : '';
                if (empty($seo_tv_name) || empty($tvs[$seo_tv_name])) continue;
                switch ($v['type']) {
                    case '3':case '6':
                        //диапазон-слайдер
                        $minmax = array_map('trim', explode('-', $tvs[$seo_tv_name]));
                        if (!empty($minmax[0])) {
                            $seoFilters[] = $this->DLFilterType . ':' . $seo_tv_name . ':>=:' . $minmax[0];
                        }
                        if (!empty($minmax[1])) {
                            $seoFilters[] = $this->DLFilterType . ':' . $seo_tv_name . ':<=:' . $minmax[1];
                        }
                        break;
                    default:
                        if (empty($v['many'])) {
                            $seoFilters[] = $this->DLFilterType . ':' . $seo_tv_name . ':=:' . $tvs[$seo_tv_name];
                        } else {
                            if (!empty($this->useRegexp)) {
                                $seoFilters[] = $this->DLFilterType . ':' . $seo_tv_name . ':regexp:' . '[[:<:]]' . str_replace(array(',', '||'), '[[:>:]]|[[:<:]]', $tvs[$seo_tv_name]) . '[[:>:]]';
                            } else {
                                $seoFilters[] = $this->DLFilterType . ':' . $seo_tv_name . ':containsOne:' . str_replace('||', ',', $tvs[$seo_tv_name]);
                            }
                        }
                        break;
                }
            }
            if (!empty($seoFilters)) {
                $DLparams = array('api' => 'id', 'JSONformat' => 'new', 'documents' => implode(',', array_keys($this->children)), 'sortType' => 'doclist', 'filters' => 'AND(' . implode(';', $seoFilters) . ')');
                $seo_dl = $this->DBModel->getList($DLparams);
                $ids = $this->EF->getListFromJson($seo_dl);
                if (!empty($ids)) {
                    $this->children = array_flip(explode(',', $ids));
                } else {
                    $this->children = [];
                }
            }
        }
        return $this;
    }
    
}

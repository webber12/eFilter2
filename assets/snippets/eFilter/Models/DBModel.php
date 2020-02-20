<?php namespace eFilter\Models;

use eFilter\Models\DBModelAbstract;

class DBModel extends DBModelAbstract
{
    public function getTvName($tvid)
    {
        return $this->modx->db->getValue("SELECT `name` FROM " . $this->modx->getFullTableName('site_tmplvars') . " WHERE id = {$tvid} LIMIT 0,1");
    }
    
    public function getParent($docid)
    {
        return $this->modx->db->getValue("SELECT parent FROM " . $this->modx->getFullTableName('site_content') . " WHERE id = {$docid} LIMIT 0,1");;
    }
    /*
    public function getTVNames($obj, $tv_ids, $field = 'name')
    {
        $tv_names = [];
        $q = $this->modx->db->query("SELECT `a`.`id`, `a`.`" . $field . "` FROM " . $this->modx->getFullTableName('site_tmplvars') . " as `a`, " . $this->modx->getFullTableName('site_tmplvar_templates') . " as `b` WHERE `a`.`id` IN (" . $tv_ids . ") AND `a`.`id` = `b`.`tmplvarid` AND `b`.`templateid` IN(" . $obj->config->getCFGDef('product_templates_id', 0) . ") ORDER BY `b`.`rank` ASC, `a`.`" . $field . "` ASC");
        while ($row = $this->modx->db->getRow($q)){
            if (!isset($tv_names[$row['id']])) {
                $tv_names[$row['id']] = $row[$field];
            }
        }
        return $tv_names;
    }
    */
    public function getTVsInfo($tv_ids)
    {
        $arr = [];
        if (!empty($tv_ids)) {
            $q = $this->modx->db->query("SELECT * FROM " . $this->modx->getFullTableName('site_tmplvars') . " WHERE id IN (" . implode(',', $tv_ids) . ")");
            while ($row = $this->modx->db->getRow($q)) {
                $arr[ $row['id'] ] = $row;
            }
        }
        return $arr;
    }
    
    public function getSelectorElements($ids = [])
    {
        $elements = [];
        $q = $this->modx->db->query("SELECT id,pagetitle FROM " . $this->modx->getFullTableName("site_content") . " WHERE id IN (" . implode(",", $ids) . ") AND published=1 AND deleted=0 ORDER BY menuindex ASC");
        while ($row = $this->modx->db->getRow($q)) {
            $elements[$tv_id][$row['id']] = $row['pagetitle'];
        }
        return $elements;
    }
    
    public function getFilterValues($content_ids, $filter_tv_ids = '', $tv_id = false)
    {
        $rows = [];
        if (!$tv_id) {
            $sql = "SELECT * FROM " . $this->modx->getFullTableName('site_tmplvar_contentvalues') . " WHERE contentid IN (" . $content_ids . ") " . ($filter_tv_ids != '' ? " AND tmplvarid IN (" . $filter_tv_ids . ")" : "");
        } else {
            $sql = "SELECT * FROM " . $this->modx->getFullTableName('site_tmplvar_contentvalues') . " WHERE contentid IN (" . $content_ids . ") " . ($filter_tv_ids != '' ? " AND tmplvarid ={$tv_id}" : "");
        }
        $q = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($q)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function getList($DLparams)
    {
        return $this->modx->runSnippet("DocLister", $DLparams);
    }
    
    public function getChildrenFolders($childs = [], $template = 0)
    {
        $parents = [];
        $q = $this->modx->db->query("SELECT id FROM " . $this->modx->getFullTableName("site_content") . " WHERE id IN (" . implode(',', $childs) . ") AND deleted=0 AND published=1 AND isfolder=1 AND template NOT IN (" . $template . ")");
        while($row = $this->modx->db->getRow($q)) {
            $parents[] = $row['id'];
        }
        return $parents;
    }
    
    public function getTagsChildren($tv_id, $parents = [], $add_where = '')
    {
        $children = [];
        $sql = "SELECT a.*, b.* FROM " . $this->modx->getFullTableName("tags") . " a, " . $this->modx->getFullTableName("site_content_tags") . " b WHERE b.tv_id = " . $tv_id . " AND a.id = b.tag_id AND a.name IN (" . implode(",", $parents) . ")" . $add_where;
        $q = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($q)) {
            $children[$row['doc_id']] = 1;
        }
        return $children;
    }
    
    public function getMultiCategoryChildren($categories = [])
    {
        $children = [];
        $q = $this->modx->db->query("SELECT * FROM " . $this->modx->getFullTableName("site_content_categories") . " WHERE category IN (" . implode(',', $categories) . ")");
        while ($row = $this->modx->db->getRow($q)) {
            $children[$row['doc']] = 1;
        }
        return $children;
    }

    public function getMinMaxTV($tv_id, $content_ids = false)
    {
        $sql = "SELECT MIN( CAST( `value` AS UNSIGNED) ) as min, MAX( CAST( `value` AS UNSIGNED) ) as max FROM " . $this->modx->getFullTableName('site_tmplvar_contentvalues') . " WHERE tmplvarid = {$tv_id}";
        if (!empty($content_ids)) {
            $sql .= " AND contentid IN(" . $content_ids . ") ";
        }
        $q = $this->modx->db->query($sql);
        $minmax = $this->modx->db->getRow($q);
        return $minmax;
    }
    
}

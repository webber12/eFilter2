<?php namespace eFilter\Models;

include_once realpath(__DIR__ . '/DBModel.php');

use EvolutionCMS\Models\SiteTmplvar;
use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\Models\SiteTmplvarContentvalue;
use Illuminate\Support\Facades\DB;

class DBModelEloquent extends DBModel
{
    public function getTvName($tvid)
    {
        return SiteTmplvar::where('id', $tvid)->first()->name;
    }
    
    public function getParent($docid)
    {
        return SiteContent::where('id', $docid)->first()->parent;
    }
    
    public function getTVsInfo($tv_ids)
    {
        $arr = [];
        if (!empty($tv_ids)) {
            $res = SiteTmplvar::whereIn('id', $tv_ids)->get()->toArray();
            foreach ($res as $row) {
                $arr[ $row['id'] ] = $row;
            }
        }
        return $arr;
    }
    
    public function getSelectorElements($ids = [])
    {
        $elements = [];
        $res = SiteContent::select('id', 'pagetitle')
                            ->whereIn('id', $ids)
                            ->where('published', 1)
                            ->where('deleted', 0)
                            ->orderBy('menuindex', 'asc')
                            ->get()
                            ->toArray();
        foreach ($res as $row) {
            $elements[$tv_id][ $row['id'] ] = $row['pagetitle'];
        }
        return $elements;
    }
    
    public function getFilterValues($content_ids, $filter_tv_ids = '', $tv_id = false)
    {
        $rows = [];
        $res = SiteTmplvarContentvalue::whereIn('contentid', explode(',', $content_ids));
        if (!$tv_id) {
            if (!empty($filter_tv_ids)) {
                $res->whereIn('tmplvarid', explode(',', $filter_tv_ids));
            }
        } else {
            if (!empty($filter_tv_ids)) {
                $res->where('tmplvarid', $tv_id);
            }
        }
        foreach ($res->get()->toArray() as $row) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function getChildrenFolders($childs = [], $template = 0)
    {
        $parents = [];
        $res = SiteContent::select('id')
                            ->whereIn('id', $childs)
                            ->where('published', 1)
                            ->where('deleted', 0)
                            ->where('isfolder', 1)
                            ->whereNotIn('template', explode(',', $template))
                            ->get()->toArray();
        foreach ($res as $row) {
            $parents[] = $row['id'];
        }
        return $parents;
    }
    
    public function getTagsChildren($tv_id, $parents = [], $add_where = '')
    {
        $children = [];
        $res = DB::table('tags')->select('*')
                    ->leftJoin('site_content_tags', 'tags.id', '=', 'site_content_tags.tag_id')
                    ->where('site_content_tags.tv_id', $tv_id)
                    ->whereIn('tags.name', $parents);
        if (!empty($add_where)) {
            $res->whereRaw(str_replace('b.doc_id', 'site_content_tags.tv_id', $add_where), []);
        }
        foreach ($res->get() as $row) {
            $children[ $row->doc_id ] = 1;
        }
        return $children;
    }
    
    public function getMultiCategoryChildren($categories = [])
    {
        $children = [];
        $res = DB::table('site_content_categories')
                            ->select('*')
                            ->whereIn('category', $categories)
                            ->get()->toArray();
        foreach ($res as $row) {
            $children[$row['doc']] = 1;
        }
        return $children;
    }
    
    public function getMinMaxTV($tv_id, $content_ids = false)
    {
        $res = SiteTmplvarContentvalue::select(DB::raw('MIN( CAST( `value` AS UNSIGNED) ) as min, MAX( CAST( `value` AS UNSIGNED) ) as max'))->where('tmplvarid', $tv_id);
        if (!empty($content_ids)) {
            $res->whereIn('contentid', explode(',', $content_ids));
        }
        return $res->first()->toArray();
    }
}

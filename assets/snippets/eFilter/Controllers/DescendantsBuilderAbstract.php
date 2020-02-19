<?php namespace eFilter\Controllers;

include_once realpath(__DIR__ . '/../Interfaces/DescendantsBuilderInterface.php');

use \eFilter\Interfaces\DescendantsBuilderInterface;

class DescendantsBuilderAbstract implements DescendantsBuilderInterface
{
    protected $children = [];
    
    protected $id;
    protected $DBModel;
    protected $seocat;
    protected $tagSaverTvId;
    protected $useMultiCategories;
    protected $product_templates_id;
    protected $DLFilterType;
    protected $common_filters;
    protected $useRegexp;
    
    public function __construct($_EF)
    {
        $this->modx = EvolutionCMS();
        $this->EF = $_EF;
    }
    
    public function setParam($name, $value)
    {
        $this->{$name} = $value;
        return $this;
    }
    
    public function buildChildren()
    {
        $this
            ->getCategoryProductsChildren()
            ->getCategoryProductsMultiCategories()
            ->getCategoryProductsTagSaver()
            ->getSeoChildren();
        return $this;
    }
    
    protected function getCategoryProductsChildren(){}
    
    protected function getCategoryProductsMultiCategories(){}
    
    protected function getCategoryProductsTagSaver(){}
    
    protected function getSeoChildren(){}
    
    public function getChildren()
    {
        return $this->children;
    }

}

<?php namespace eFilter\Elements;

include_once realpath(__DIR__ . '/../Interfaces/ElementInterface.php');

class AbstractElement implements \eFilter\Interfaces\ElementInterface
{
    protected $oldTPLs = [];
    protected $rowTpl = null;
    protected $outerTpl = null;
    protected $type = 'checkbox';
    protected $templatePath = MODX_BASE_PATH . 'assets/snippets/eFilter/Templates/';

    protected $tv_id;
    protected $filters;
    protected $filter_values_full;
    protected $filter_values;
    protected $tv_elements;

    protected $fp;
    protected $activeRowClass;
    protected $activeBlockClass;
    protected $removeDisabled;
    protected $allowZero;
    protected $zero;
    protected $hideEmptyBlock;

    protected $content_ids;
    protected $content_ids_full;
    protected $curr_filter_values;
    
    public function __construct($_EF)
    {
        $this->EF = $_EF;
        $this->modx = $_EF->modx;//alias
        $this->oldTPLs = $this->loadOldTPLs();
        $this->rowTpl = $this->getRowTpl();
        $this->outerTpl = $this->getOuterTpl();
    }
    
    
    public function render()
    {
        //default checkbox render
        $output = '';
        $wrapper = '';
        $i = 0;
        $active_block_class = '';
        
        foreach ($this->filter_values_full[$this->tv_id] as $k => $v) {
            $tv_val_name = isset($this->tv_elements[$this->tv_id][$k]) ? $this->tv_elements[$this->tv_id][$k] : $k;
            if ($this->filters[$this->tv_id]['href'] == '1' && is_int($k)) {
                $tv_val_name = $this->makeHrefForValue($tv_val_name, $k);
            }
            $selected = '  ';
            $label_selected = '';
            if (isset($this->fp[$this->tv_id])) {
                $flag = false;
                if (is_array($this->fp[$this->tv_id]) && in_array($k, $this->fp[$this->tv_id])) {
                    $flag = true;
                } else {
                    $flag =  ($this->fp[$this->tv_id] == $k) ? true : false;
                }
                if ($flag) {
                    $selected = $this->getSelected();
                    $label_selected = $this->activeRowClass;
                    $active_block_class = $this->activeBlockClass;
                }
            }
            $disabled = (!empty($this->filter_values) && !isset($this->filter_values[$this->tv_id][$k]) ? 'disabled' : '');
            if ($disabled == '') {
                $count =  (isset($this->filter_values[$this->tv_id][$k]['count']) ? $this->filter_values[$this->tv_id][$k]['count'] : $this->filter_values_full[$this->tv_id][$k]['count']);
            } else {
                $count = $this->zero;
            }
            if ($this->removeDisabled == 0 || $disabled == '') {
                $i++;
                $wrapper .= ($k != '' || ($k == 0 && !empty($this->allowZero))) ? $this->EF->parseTpl(
                    array('[+tv_id+]', '[+value+]', '[+name+]', '[+selected+]', '[+label_selected+]', '[+disabled+]', '[+count+]', '[+iteration+]'),
                    array($this->tv_id, $k, $tv_val_name, $selected, $label_selected, $disabled, $count, $i),
                    $this->tplRowAdapter()
                ) : '';
            }
        }
        if (!empty($this->hideEmptyBlock) && $wrapper == '') return;
        $output .= $this->EF->parseTpl(
            array('[+tv_id+]', '[+name+]', '[+wrapper+]', '[+active_block_class+]'),
            array($this->tv_id, $this->filters[$this->tv_id]['name'], $wrapper, $active_block_class),
            $this->tplOuterAdapter()
        );
        return $output;
    }
    
    protected function getTplFolder()
    {
        $folder = $this->templatePath . '/Default/';
        $theme = $this->EF->config->getCFGDef('cfg', 'default');
        $theme = ucfirst($theme);
        if (is_dir($this->templatePath . '/' . $theme . '/')) {
            $folder = $this->templatePath . '/' . $theme . '/';
        }
        return $folder;
    }
    
    protected function getRowTpl()
    {
        if (file_exists($this->getTplFolder() . $this->type . 'Row.tpl')) {
            $tpl = $this->getTplFolder() . $this->type . 'Row.tpl';
        } else if (file_exists($this->templatePath . '/Default/' . $this->type . 'Row.tpl')) {
            $tpl = $this->templatePath . '/Default/' . $this->type . 'Row.tpl';
        } else {
            $tpl = null;
        }
        return !empty($tpl) ? file_get_contents($tpl) : null;
    }
    
    protected function getOuterTpl()
    {
        if (file_exists($this->getTplFolder() . $this->type . 'Outer.tpl')) {
            $tpl = $this->getTplFolder() . $this->type . 'Outer.tpl';
        } else if (file_exists($this->templatePath . '/Default/' . $this->type . 'Outer.tpl')) {
            $tpl = $this->templatePath . '/Default/' . $this->type . 'Outer.tpl';
        } else {
            $tpl = null;
        }
        return !empty($tpl) ? file_get_contents($tpl) : null;
    }
    
    protected function getSelected()
    {
        return ' checked="checked" ';
    }
    
    protected function makeHrefForValue($title, $id)
    {
        return '<a href="' . $this->EF->modx->makeUrl($id) . '">' . $title . '</a>';
    }
    
    protected function loadOldTPLs()
    {
        $aoutput = [];
        $name = $this->EF->config->getCFGDef('cfg', 'default');
        if (is_file(realpath(__DIR__ . '/../config/config.' . $name . '.php'))) {
            include(realpath(__DIR__ . '/../config/config.' . $name . '.php'));
        } else {
            include(realpath(__DIR__ . '/../config/config.default.php'));
        }
        $output = compact('tplRowCheckbox', 
                          'tplOuterCheckbox', 
                          'tplRowSelect', 
                          'tplOuterSelect', 
                          'tplRowInterval', 
                          'tplOuterInterval', 
                          'tplRowRadio', 
                          'tplOuterRadio', 
                          'tplRowMultySelect', 
                          'tplOuterMultySelect', 
                          'tplRowSlider', 
                          'tplOuterSlider', 
                          'tplRowColors', 
                          'tplOuterColors', 
                          'tplRowPattern', 
                          'tplOuterPattern', 
                          'tplFilterForm', 
                          'tplFilterReset', 
                          'filterCatName', 
                          'tplOuterCategory',
                          'filterCatClass'
                        );
        return $output;
    }
    
    public function getOldTPL($name)
    {
        return !empty($this->oldTPLs[$name]) ? '@CODE:' . $this->oldTPLs[$name] : '@CODE:';
    }
    
    protected function tplRowAdapter()
    {
        return !empty($this->rowTpl) ? $this->rowTpl : $this->getOldTPL('tplRow' . ucfirst($this->type));
    }
    
    protected function tplOuterAdapter()
    {
        return !empty($this->outerTpl) ? $this->outerTpl : $this->getOldTPL('tplOuter' . ucfirst($this->type));
    }

    public function setParam($name, $value)
    {
        $this->{$name} = $value;
        return $this;
    }

    public function getTpl($name)
    {
        $oldname = null;
        switch ($name) {
            case 'filterForm':
                $oldname = 'tplFilterForm';
                break;
            case 'filterReset':
                $oldname = 'tplFilterReset';
                break;
            case 'categoryOuter':
                $oldname = 'tplOuterCategory';
                break;
            case 'categoryNameRow':
                $oldname = 'filterCatName';
                break;
            default:
                break;
        }
        $tpl = $this->loadTpl($name);
        return !empty($tpl) ? $tpl : $this->getOldTPL($oldname);
    }
    
    protected function loadTpl($name)
    {
        if (file_exists($this->getTplFolder() . $name . '.tpl')) {
            $tpl = $this->getTplFolder() . $name . '.tpl';
        } else if (file_exists($this->templatePath . '/Default/' . $name . '.tpl')) {
            $tpl = $this->templatePath . '/Default/' . $name . '.tpl';
        } else {
            $tpl = null;
        }
        return !empty($tpl) ? file_get_contents($tpl) : null;
    }
}
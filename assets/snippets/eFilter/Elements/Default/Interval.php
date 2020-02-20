<?php namespace eFilter\Elements;

use eFilter\Elements\AbstractElement;

class Interval extends AbstractElement
{

    protected $type = 'interval';
    
    public function render()
    {
        //исходя из запроса $_GET
        $minval = '';
        $maxval = '';
        //смотрим мин. и макс. значения исходя из списка доступных contentid и запроса $_GET
        //т.е. реальный доступный диапазон значений "от и до"
        $minvalcurr = '';
        $maxvalcurr = '';
        
        if (!empty($this->curr_filter_values[$this->tv_id]['content_ids'])) {
            $content_ids = $this->curr_filter_values[$this->tv_id]['content_ids'] == 'all' ? $this->content_ids : $this->curr_filter_values[$this->tv_id]['content_ids'];
            $minmax = $this->EF->DBModel->getMinMaxTV($this->tv_id, $content_ids);
            $minvalcurr = $minmax['min'];
            $maxvalcurr = $minmax['max'];
        }
        $minvalcurr = isset($this->fp[$this->tv_id]['min']) && (int)$this->fp[$this->tv_id]['min'] != 0 && (int)$this->fp[$this->tv_id]['min'] >= (int)$minvalcurr ? (int)$this->fp[$this->tv_id]['min'] : $minvalcurr;
        $maxvalcurr = isset($this->fp[$this->tv_id]['max']) && (int)$this->fp[$this->tv_id]['max'] != 0 && (int)$this->fp[$this->tv_id]['max'] <= (int)$maxvalcurr  ? (int)$this->fp[$this->tv_id]['max'] : $maxvalcurr;
        $minval = isset($this->fp[$this->tv_id]['min']) && (int)$this->fp[$this->tv_id]['min'] != 0 ? (int)$this->fp[$this->tv_id]['min'] : $minval;
        $maxval = isset($this->fp[$this->tv_id]['max']) && (int)$this->fp[$this->tv_id]['max'] != 0 ? (int)$this->fp[$this->tv_id]['max'] : $maxval;
        $wrapper .= $this->EF->parseTpl(
            array('[+tv_id+]', '[+minval+]', '[+maxval+]', '[+minvalcurr+]', '[+maxvalcurr+]'),
            array($this->tv_id, $minval, $maxval, $minvalcurr, $maxvalcurr),
            $this->tplRowAdapter()
        );
        $output .= $this->EF->parseTpl(
            array('[+tv_id+]', '[+name+]', '[+wrapper+]'),
            array($this->tv_id, $this->filters[$this->tv_id]['name'], $wrapper),
            $this->tplOuterAdapter()
        );
        return $output;
    }
    
    protected function makeHrefForValue($title, $id)
    {
        return $title;
    }

}
<?php namespace eFilter\Elements;

include_once realpath(__DIR__ . '/../AbstractElement.php');

class Slider extends AbstractElement
{

    protected $type = 'slider';
    
    public function render()
    {
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
        } else if (!empty($this->content_ids_full)) {
            $minmax = $this->EF->DBModel->getMinMaxTV($this->tv_id, $this->content_ids_full);
            $minvalcurr = $minmax['min'];
            $maxvalcurr = $minmax['max'];
        } else { //фикс если ничего не выбрано - берем просто мин и макс цену
            $minmax = $this->EF->DBModel->getMinMaxTV($this->tv_id);
            $minvalcurr = $minmax['min'];
            $maxvalcurr = $minmax['max'];
        }
        if ($minvalcurr == $maxvalcurr) { //фикс - если цена одинаковая то делаем мин.диапазон
            $minvalcurr = $minvalcurr - 1;
            $maxvalcurr = $maxvalcurr + 1;
        }
        $maxvalcurr = $maxvalcurr != '' ? ceil($maxvalcurr) : '';
                   
        $minval = isset($this->fp[$this->tv_id]['min']) && (int)$this->fp[$this->tv_id]['min'] != 0 ? (int)$this->fp[$this->tv_id]['min'] : $minval;
        $maxval = isset($this->fp[$this->tv_id]['max']) && (int)$this->fp[$this->tv_id]['max'] != 0 ? (int)$this->fp[$this->tv_id]['max'] : $maxval;
        $maxval = $maxval != '' ? ceil($maxval) : '';
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
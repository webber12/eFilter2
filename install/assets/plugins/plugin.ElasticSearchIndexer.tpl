/**
 * ElasticSearchIndexer
 *
 * plugin for index tv for eFilter by ElasticSearch
 *
 * @author      webber (web-ber12@yandex.ru)
 * @category    plugin
 * @version     0.1
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @events OnDocFormSave,OnPluginFormSave
 * @internal    @properties &indexKey=Ключ индекса;string;efilter; &param_tv_name=Имя TV для хранения настроек фильтра;string;tovarparams; &product_templates=ID шаблонов товара;string; &tv_tagcategory_name=Имя TV для хранения категории;string; &tv_number_format=ID TV фильтра в числовом формате;string;
 * @internal    @installset base, sample
 * @internal    @modx_category Filters
 */
 
/***
// использует общие параметры модуля eLists - не забудьте их подключить в модуле и плагине 
// 1. производит индексацию тв, участвующих в фильтрации для данного товара OnDocFormSave
// 2. производит корректировку маппинга фильтра (числовые значения) OnPluginFormSave
***/

require MODX_BASE_PATH.'assets/snippets/eFilter/plugin.ElasticSearchIndexer.php';

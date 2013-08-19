<?php
class Module_Core_Repository_Model_Abstract extends Core_Model_Repository_Model {

  public $id                  = false;
  public $strip               = true;
  public $paginator_page      = false;
  public $paginator_page_name = false;
  public $ajax_url            = false;
  public $filter_section      = false;

  protected $ajax_callback_method = null;
  protected $items_per_page  = 3;
  protected $datafilter      = false;
  protected $datasorter	     = false;
  protected $datafilter_render_style = false;

  protected $sort_f          = null;
  protected $sort_t          = null;
  public    $_db             = null;
  protected $_query          = null;

  private $jqGrid_special_condition = array('bw'=>"begins-with",'ew'=>"ends-with",'cn'=>"contains",'da'=>"date");
  private $jqGrid_filters    = array( 'eq'  => '='
                                      ,'ne' => '!='
                                      ,'lt' => '<'
                                      ,'le' => '<='
                                      ,'gt' => '>'
                                      ,'ge' => '>=');

  public $_namespace      = false;
  public $session         = false;

  public $grid_id_container = false;

  public function __construct($id) {
    $this->_db = App::module('Core')->getModel('Db')->get();
  }

  public function asArray() {
    $array=array();
    foreach ($this as $var=>$value) {
      if ($var[0]!="_" && $value!==false) $array[$var]=$value;
    }
    return $array;
  }

  public function reset() {
    foreach ($this as $var=>$value) {
      if ($var[0]!="_") { $this->{$var}=false; }
    }
    return $this;
  }

  public function __call($function, $args) {
    preg_match("/^set([a-zA-Z\_]+)$/",$function,$matches);
    if (isset($matches[1])) {
      $var=strtolower($matches[1]);
      if (isset($this->{$var}) || @$this->{$var}===false) {
          $this->{$var}=$args[0];
      }
      return $this;
    }

    preg_match("/^get([a-zA-Z\_]+)$/",$function,$matches);
    if (isset($matches[1])) {
        $var=strtolower($matches[1]);
        if (isset($this->{$var})) {
            return $this->{$var};
        }
        return false;
    }
  }

  public function query_for_listing($select=null){
    if( empty($select) || ! is_object($select) ){
      App::module('Core')->exception( App::xlat('EXC_db_instance_not_found') . '<br />Launched at method query, file Repository/Model/Abstract' );
    }

    if ( ! empty($this->datasorter) ){
      $select->order( $this->add_datasorter() );
    }

    if ( ! empty($this->datafilter) && $this->datafilter->isActive() ) {
      require_once('Xplora/Datafilter/Sql.php');
      foreach ($this->datafilter->getFields() as $id=>$field) {
        if ( true===$field->getActive() ) {
          $select->where( "{$field->getFieldName()} {$field->getCondition()} ?", $field->getValue() );
        }
      }
    }

    // $select = $this->setPaginator_query( $select->__toString() )->paginate_query();
    $select = $this->paginate_query( $select );
    return $this->setPaginator_page_name(App::xlat('route_paginator_page'))
                ->paginate_render( $select );
  }

  public function paginate_query(&$select = null, $ajax_paginate_style = null){
    if( empty($select) || ! is_object($select) ){
      App::module('Core')->exception( App::xlat('EXC_db_instance_not_found') . '<br />Launched at method query, file Repository/Model/Abstract' );
    }

    App::module('Core')->getModel('Db/Datafilter')
                       ->setFilter_section( $this->filter_section )
                       ->apply_filters_to_query($select);

    $sql = $select->__toString();

    if( empty($sql) ){
      App::module('Core')->exception( App::xlat('EXC_db_instance_not_found') . '<br />Launched at method query, file Repository/Model/Abstract' );
    }

    require_once('Xplora/Paginate/Sql.php');
    $paginator=new Xplora_Paginate_Sql();
    $select = $paginator->setItems_per_page((int)$this->items_per_page)
                        ->setPage_current((int)$this->paginator_page)
                        ->setDb_adapter( $this->_db )
                        ->setQuery( $sql )
                        ->paginate();


    return $this->setPaginator_page_name(App::xlat('route_paginator_page'))
                ->paginate_render( $select, $ajax_paginate_style );
  }

  public function ajax_paginate_query(&$select = null, $callback = null){
    if( empty($callback) || empty($this->ajax_url) ){
      App::module('Core')->exception( App::xlat('EXC_ajax_callback_method_is_missing') . '<br />Launched at method ajax_paginate_query, file Repository/Model/Abstract' );
    }
    $this->ajax_callback_method = $callback;
    return $this->paginate_query($select,true);
  }

  public function paginate_render($data = null, $ajax_paginate_style = null){
    if ( empty($data) ){
      return null;
    }

    $pagination = null;
    if ( ! empty($data['pagination']['page_total']) &&  $data['pagination']['page_total']>1 ){

      if( empty($ajax_paginate_style) ){
        require_once "Local/View/Helper/Paginate.php";
        $render = new Local_View_Helper_Paginate();
      }else{
        require_once "Local/View/Helper/Ajaxpaginate.php";
        $render = new Local_View_Helper_Ajaxpaginate();
        $render->setAjax_url($this->ajax_url)->setJscallback( $this->ajax_callback_method . "; return false;");
      }

      $render->paginate()->setUrl(
            App::url()->removeParams(
              array(
                $this->paginator_page_name => Core_Controller_Front::getInstance()->getRequest()->getParam( $this->paginator_page_name )
              )
            )
          )
          ->setPaginator_page_name($this->paginator_page_name)
          ->setPage_current($data['pagination']['page_current'])
          ->setPage_total($data['pagination']['page_total'])
          ->setItems_per_page($data['pagination']['items_per_page'])
          ->setItems_total($data['pagination']['items_total']);

      $pagination = $render->renderPages();
    }

    return array_merge($data, array('pagination_html' => $pagination));
  }

  public function grouped_where($field = null, $grouped_field_values = array(), $and_or = "OR" ){
    if( ! is_array($grouped_field_values) || empty($field) ){
      return null;
    }

    $grouped_where = array();
    foreach($grouped_field_values AS $field_value){
      $grouped_where[] = $this->_db->quoteInto( $field . ' = ?', $field_value );
    }

    return empty($grouped_where) ? null : implode( " " . $and_or . " ", $grouped_where);
  }


  // jqGrid

  /*
   * @todo: falta agregar filtros mas personalizados
  */
  public function jqGrid_query_for_listing($select=null, $params=null){
    if( empty($select) || ! is_object($select) ){
      App::module('Core')->exception( App::xlat('EXC_db_instance_not_found') . '<br />Launched at method query, file Repository/Model/Abstract' );
    }

    // where
    if( ! empty($params['_search']) && $params['_search']==='true' ){

      $filters = json_decode($params['filters']);
      foreach($filters->rules AS $filter){

        if( array_key_exists($filter->op,$this->jqGrid_special_condition) ){
          $select->where( $this->jqGrid_where_special_condition($filter->field, $filter->data, $filter->op) );
        }else{
          $select->where( "{$filter->field} {$this->jqGrid_where_get_condition($filter->op)} ?", $filter->data );
        }

      }
    }

    // order
    if( ! empty($params['sidx']) ){
      $select->order( $params['sidx'] ." ". @$params['sord'] );
    }

    return $this->jqGrid_paginate_query( $select );
  }

  public function jqGrid_where_special_condition($field=null,$data=null,$operator=null){
    if( App::module('Core')->getModel('Parser')->check_function_params( func_get_args(), false ) ){
      switch($this->jqGrid_special_condition[$operator]){
        case 'begins-with':
        case 'date':
          return "$field LIKE '$data%'";
          break;
        case 'ends-with':
          return "$field LIKE '%$data'";
          break;
        case 'contains':
          return "`$field` LIKE '%$data%'";
          break;
        default:
          App::module('Core')->exception( App::xlat('EXC_jqGrid_where_condition_missing') . '<br />Launched at method jqGrid_where_special_condition, file Repository/Model/Abstract' );
          break;
      }
      return null;
    }
    return null;
  }

  public function jqGrid_where_get_condition($condition=null){
    return array_key_exists($condition,$this->jqGrid_filters) ?
      $this->jqGrid_filters[$condition]
    :
      App::module('Core')->exception( App::xlat('EXC_jqGrid_where_condition_missing') . '<br />Launched at method jqGrid_where_get_condition, file Repository/Model/Abstract' );
  }

  public function jqGrid_paginate_query(&$select = null, $ajax_paginate_style = null){
    $sql = $select->__toString();
    if( empty($sql) ){
      App::module('Core')->exception( App::xlat('EXC_db_instance_not_found') . '<br />Launched at method query, file Repository/Model/Abstract' );
    }

    require_once('Xplora/Paginate/Sql.php');
    $paginator = new Xplora_Paginate_Sql();
    $results   = $paginator->setItems_per_page((int)$this->items_per_page)
                           ->setPage_current((int)$this->paginator_page)
                           ->setDb_adapter( $this->_db )
                           ->setQuery( $sql )
                           ->paginate();

    if( empty($results['items']) || empty($results['pagination']) ){
      return '{"page": "1","total": 0,"records": "0", "rows": []}';
    }

    $to_json = array( 'page' => $this->paginator_page,  'total' => $results['pagination']['page_total'],  'records' => $results['pagination']['items_total']);
    foreach($results['items'] as $data){
      $to_json['rows'][] = array( 'id' => $data[$this->grid_id_container], 'cell'=> array_values($data) );
    }

    return json_encode($to_json);
  }



  // Datasorter

  public function init_datasorter(){
    require_once("Xplora/Datasorter.php");
    $this->sort_f = (string)Core_Controller_Front::getInstance()->getRequest()->getParam( App::xlat('route_sort_field') );
    $this->sort_t = (string)Core_Controller_Front::getInstance()->getRequest()->getParam( App::xlat('route_sort_direction') );

    $this->datasorter = Xplora_Datasorter::factory()->setUrl(
                        App::url()->removeParams(
                          array(
                            App::xlat('route_sort_field')      =>  $this->sort_f,
                            App::xlat('route_sort_direction')  =>  $this->sort_t,
                            App::xlat('route_paginator_page')  =>  Core_Controller_Front::getInstance()->getRequest()->getParam( App::xlat('route_paginator_page') ),
                          )
                        )
                      );
  }

  public function add_datasorter(){
    $order = false;
    if (is_array($sort=$this->datasorter->getSort())) {
      foreach ($sort as $field) {
        $order[] = $field->getFieldname()." ". $field->getSort_type();
      }
    }
    return $order;
  }

  public function datasorter_to_render(){
    if ( empty($this->datasorter) ){
      return array();
    }

    require_once "Local/View/Helper/Datasorter.php";
    $sorter = new Local_View_Helper_Datasorter();
    return $sorter->datasorter()->setDatasorter($this->datasorter);
  }

  // Datafilter

  public function init_datafilter($additional_route = 'you_forgot_to_set_the_route_for_method_init_datafilter'){
    require_once("Xplora/Datafilter.php");

    if ( empty($this->datafilter_render_style) ){
      $this->datafilter = Xplora_Datafilter::factory()->setUrl( App::base( $additional_route ) );
    }else{
      $this->datafilter = Xplora_Datafilter::factory()->setUrl(
      App::url()->removeParams( array(
                                  App::xlat('route_paginator_page')  =>  Core_Controller_Front::getInstance()->getRequest()->getParam( App::xlat('route_paginator_page') )
                              )));
    }

    $this->datafilter->setTranslator( App::translate()->getFormTranslator() )->setLocale( App::locale()->zend() );
  }

  public function datafilter_to_render(){
    if ( empty( $this->datafilter ) ){
      return array();
    }
    $this->datafilter->populate( Core_Controller_Front::getInstance()->getRequest()->getParams() );
    return $this->datafilter;
  }

  public function get_db(){
    return $this->_db;
  }

}
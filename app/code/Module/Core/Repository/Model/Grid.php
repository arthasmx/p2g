<?php
class Module_Core_Repository_Model_Grid extends Core_Model_Repository_Model{
  // columns
  public $columns          = array();
  // options
  public $options          = array();
  public $grid_id          = '#datagrid';
  public $caption          = false;
  public $foot_bar         = '#footerBar';
  public $data_type        = 'json';
  public $sort_by          = 'id';
  public $filter           = true;
  public $resize           = false;
  public $navigate         = true;
  public $on_cell_select   = false;
  public $on_select_all    = false;
  public $sort_orientation = 'desc';
  // url

  // paginator
  public $rows     = 20;
  public $row_list = '20,40,60,80,100';
  // filter
  public $avoid_searching_fields = array();

  // resize
  public $width      = '1600';
  public $height     = '100%';
  public $max_width  = 1800;
  public $max_height = 1000;


  private $not_quote = array('rowNum','rowList','search','sortable','multiselect','multiboxonly','viewrecords','width','searchoptions');
  private $type      = array('date','remote','select','picture','spanOnClick','span');

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

  function init(){
    App::header()->addScript(App::url()->get('/jqgrid/i18n/grid.locale-'. App::locale()->getLang() .'.js','js'));
    App::header()->addScript(App::url()->get('/jqgrid/jquery.jqGrid.min.js','js'));
    App::header()->addLink( App::www('/js/jqgrid/ui.jqgrid.css'),array('rel'=>'stylesheet','type'=>'text/css'));

    $this->setOptions(
      array('datatype'  => $this->data_type,
            'height'    => $this->height,
            'rowNum'    => $this->rows,
            'rowList'   => "[$this->row_list]",
            //'pager'     => $this->foot_bar,
            'sortname'  => $this->sort_by,
            'sortorder' => $this->sort_orientation
          )
    );
  }

  function jqGrid(){
    App::header()->add_jquery_events("
      jQuery('$this->grid_id').jqGrid({
        ".$this->get_options()."
        ".$this->get_column_names()."
        ".$this->get_models()."
        ".$this->on_cell_select_behavior()."
        ".$this->on_select_all_behavior()."

      });

      ".$this->filter_bar()."
      ".$this->navigator_bar()."
      ".$this->resize_grid()."
    ");
  }

  function add_model($columns=array()){
    foreach($columns AS $column){
      $name = $column['name'];
      $this->columns[$name] = $column;
      $this->columns[$name]['index'] = $name;
    }
    return $this;
  }

  function avoid_searching($fields=array()){
    if( empty($fields) ){
      return null;
    }

    $remove ='';
    foreach($fields AS $field){
      $remove .= 'jQuery("div#fbox_datagrid select option[value=\''. $field .'\']").remove();'. PHP_EOL;
    }
    $this->avoid_searching_fields = $remove;
    return $this;
  }

  function get_column_names(){
    if( empty($this->columns) ){
      App::module('Core')->exception( App::xlat('EXC_columns_missing') );
    }

    $names = 'colNames : [';
    foreach($this->columns AS $column){
      $names .= "'".$column['title']."',";
    }
    return trim($names,",") . "],";
  }

  function get_models(){
    if( empty($this->columns) ){
      App::module('Core')->exception( App::xlat('EXC_columns_missing') );
    }

    $models = 'colModel : [';
    foreach($this->columns AS $column){
      unset($column['title']);
      $model = "{";
      foreach($column AS $key=>$value){

          if( in_array($value,$this->type) ){

            switch($value){
              case 'date':
                $model .= $this->get_date_model();
                break;
              case 'remote':
                $model .= $this->get_remote_model();
                break;
              case 'picture':
                $model .= $this->get_picture_model();
                break;
              case 'spanOnClick':
                $model .= $this->get_span_on_click_model();
                break;
              case 'span':
                $model .= $this->get_span_model();
                break;
             case 'select':
                $model .= $this->get_select_model(@$column['options']);
                unset($column['options']);
                break;
              default:
                break;
            }

          }else{
            $model .= "$key:". (in_array($key, $this->not_quote)? $value : "'$value'") .",";
          }

      }
      $models .= trim($model,",") ."},";
    }
    return trim($models,",") . "]";
  }

  function get_picture_model(){
    return "align:'center', resizable:false,  formatter: function (cellValue, options, rowObject) {
                                                           return '<img src=\"'+ wwwUrl + cellValue +'\" />';
                                                         },";
  }

  function get_span_model(){
    return "align:'center', resizable:false,  formatter: function (cellValue, options, rowObject) {
      return '<span class=\"record-' + cellValue + '\"></span>';
    },";
  }

  function get_span_on_click_model(){
    return "align:'center', resizable:false,  formatter: function (cellValue, options, rowObject) {
      return '<span class=\"span-button-' + cellValue + '\"></span>';
    },";
  }

  function get_date_model(){
    return "align:'center', resizable:false, sorttype:'date', formatter:'date', formatoptions: {newformat:'d/M/Y'}, searchoptions:{ sopt:['da'], dataInit:function(el){
      jQuery(el).datepicker({
        changeYear: true, changeMonth: true, showButtonPanel:true,
        dateFormat : 'yy-mm-dd',
        onSelect   : function(dateText, inst){
          if( jQuery(this).parent().prop('tagName') == 'DIV'){
            jQuery('$this->grid_id')[0].triggerToolbar();
          }
        }
      });}
    },";
  }

  function get_remote_model(){
    return "stype:'select',";
  }

  function get_select_model($options=null){
    if( empty($options) || ! is_array($options) ){
      App::module('Core')->exception( App::xlat('EXC_columns_missing') );
    }

    $select = "align:'center', resizable:false, stype:'select', searchoptions:{ sopt:['eq'], value: {";
    foreach($options AS $key=>$value){
      $select .= "'$key':'$value',";
    }
    return trim($select,",") . "} },";
  }

  function setAttribs($attribs=array()){
    return $this->setOptions($attribs,null);
  }

  function setOptions($options=array(),$quote=true){
    if( empty($options) ){
      App::module('Core')->exception( App::xlat('EXC_no_options_where_given') );
    }

    foreach($options AS $key=>$value){
      $this->options[$key] = (in_array($key, $this->not_quote)? $value : "'$value'");
    }
    return $this;
  }

  function get_options(){
    $options = '';
    foreach($this->options AS $key=>$value){
      $options .= "$key:$value,";
    }
    return $options;
  }

  function filter_bar(){
    return empty($this->filter) ?
      null
    :
      "jQuery('$this->grid_id').jqGrid('filterToolbar', { stringResult: true, searchOnEnter: true, defaultSearch: 'eq', ignoreCase: true});";
  }

  function navigator_bar(){
    return empty($this->navigate) ?
      "jQuery('$this->grid_id').jqGrid('navGrid','$this->foot_bar', {edit:false, add:false, del:false, search:false}, {},{},{},{});"
    :
      "jQuery('$this->grid_id').jqGrid('navGrid','$this->foot_bar', {edit:false, add:false, del:false, search:true}, {},{},{},{closeOnEscape:true, multipleSearch:true,
        afterRedraw: function() {
          $this->avoid_searching_fields
        }
  });";
  }

  function resize_grid(){
    return empty($this->resize) ?
      null
    :
      "jQuery('$this->grid_id').jqGrid('gridResize',{Width:$this->width,maxWidth:$this->max_width,height:'$this->height', maxHeight:$this->max_height});";
  }

  function on_cell_select_behavior(){
    if( empty($this->on_cell_select) ){
      return null;
    }
    return $this->on_cell_select;
  }

  function on_select_all_behavior(){
    if( empty($this->on_select_all) ){
      return null;
    }
    return $this->on_select_all;
  }

  function set_foot_bar($id){
    if( empty($id) ){
      App::module('Core')->exception( App::xlat('EXC_no_options_where_given') );
    }

    $this->options['pager'] = "'$id'";
    $this->foot_bar         = $id;
    return $this;
  }


}
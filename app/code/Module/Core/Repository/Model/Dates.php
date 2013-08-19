<?php
class Module_Core_Repository_Model_Dates extends Core_Model_Repository_Model {

  /**
   * Formatea una fecha
   * @param string $formato | Indica el tipo de formato a aplicar a la fecha, como los siguientes
   * 0	= 2009-01-25 15:23:11 | OJO, esta es la fecha para guardar a mySQL y recibe en formato: 18/Dic/2009
   * 1	= 15/02/2009
   * 2	= 15/Feb/2009
   * 3	= Feb 15, 2009
   * 4	= Viernes 15/02/2009
   * 5	= Viernes 15/Feb/2009
   * 6	= Viernes 15 Feb 2009
   * 7
   * 8  = 2012/09
   * 9  = 11:19 PM
   * 10 = 2012\09 | Utilizado para crear directorios con formato Año \ Mes
  */
  function toDate($formato,$date,$timezone=false){
    if(!$timezone){
      date_default_timezone_set('America/Mazatlan');
    }

    $full_day = null;
    $datetime = new DateTime($date);
    switch ($formato) {
      case 0:
        $fecha=$datetime->format('Y-m-j ').date('H:i:s');
        break;
      case 1:
        $fecha=$datetime->format('j/m/Y');
        break;
      case 2:
        $fecha=$datetime->format('j/M/Y');
        break;
      case 3:
        $fecha=$datetime->format('M j, Y');
        break;
      case 4:
        $fecha=$datetime->format('D j/m/Y');
        break;
      case 5:
        $fecha=$datetime->format('D j/M/Y');
        break;
      case 6:
        $fecha=$datetime->format('D j M Y');
        break;
      case 7:
        $full_day = true;
        $fecha=$datetime->format('l j F Y');
        break;
      case 8:
        $fecha=$datetime->format('Y/m');
        break;
     case 9:
        $fecha=$datetime->format('h:i A');
        break;
     case 10:
        $fecha  = $datetime->format('Y/m');
        //$fecha = str_replace('/', "\\", $fecha );
        break;
     case 11:
        $day   = $datetime->format('d');
        $month = $datetime->format('M');
        $year  = $datetime->format('Y');

        $fecha = array('day'=>$day, 'month'=>$month, 'year'=>$year);
        break;

      case 12: // Convers Year/Month using the operating system directory separator
        $fecha  = $datetime->format(DS.'Y'.DS.'m'.DS);
        break;
      case 13: // Convers Year/Month using the operating system directory separator
          $fecha=$datetime->format('j M');
          break;

      default:
        //$fecha=$datetime->format('M j, Y');
        $fecha=$datetime->format('Y-m-d');
      break;
    }

    // Si locale NO es INGLES, traducimos; Porque ? porque el mySQL que tengo x default esta en ingles
    if(App::locale()->getLang()!='en'){
      if($full_day==true){
        // Dias completos
        $englishDates=array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
        $spanishDates=array(App::xlat('dates_long_day_7'),App::xlat('dates_long_day_1'),App::xlat('dates_long_day_2'),App::xlat('dates_long_day_3'),App::xlat('dates_long_day_4'),App::xlat('dates_long_day_5'),App::xlat('dates_long_day_6'));
        $fecha = str_replace($englishDates,$spanishDates,$fecha);
        // Completos
        $englishDates=array('January','February','March','April','May','June','July','August','September','October','November','December');
        $spanishDates=array(App::xlat('dates_long_month_1'),App::xlat('dates_long_month_2'),App::xlat('dates_long_month_3'),App::xlat('dates_long_month_4'),App::xlat('dates_long_month_5'),App::xlat('dates_long_month_6'),App::xlat('dates_long_month_7'),App::xlat('dates_long_month_8'),App::xlat('dates_long_month_9'),App::xlat('dates_long_month_10'),App::xlat('dates_long_month_11'),App::xlat('dates_long_month_12'));
        $fecha = str_replace($englishDates,$spanishDates,$fecha);
        }else{
        // Dias
        $englishDates=array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
        $spanishDates=array(App::xlat('dates_short_day_7'),App::xlat('dates_short_day_1'),App::xlat('dates_short_day_2'),App::xlat('dates_short_day_3'),App::xlat('dates_short_day_4'),App::xlat('dates_short_day_5'),App::xlat('dates_short_day_6'));
        $fecha = str_replace($englishDates,$spanishDates,$fecha);
        // Meses
        $englishDates=array('Jan','Apr','Aug','Dec');
        $spanishDates=array(App::xlat('dates_short_month_1'),App::xlat('dates_short_month_4'),App::xlat('dates_short_month_8'),App::xlat('dates_short_month_12'));
        $fecha = str_replace($englishDates,$spanishDates,$fecha);
      }
    }
    return $fecha;
  }

  function translate_date($fecha=null,$format){
    if( empty($fecha) ){
      return null;
    }

    switch ($format){
      case 1:
        $englishDates = array('Jan','Apr','Aug','Dec');
        $spanishDates = array(App::xlat('dates_short_month_1'),App::xlat('dates_short_month_4'),App::xlat('dates_short_month_8'),App::xlat('dates_short_month_12'));
        $translation  = str_replace($englishDates,$spanishDates,$fecha);
        break;
      default:
        $translation = null;
    }

    return $translation;
  }

  function yesterday(){
    return date('Y-m-d', time()-86400);
  }

  function tomorrow(){
    return date('Y-m-d', time()+86400);
  }

  function days_in_between($date_start, $date_end){
    $d = array();
    $date_start = strtotime($date_start);
    $date_end = strtotime($date_end);
    for($n=$date_start; $n<=$date_end; $n=$n+86400){
      array_push($d, strftime('%Y-%m-%d', $n));
    }
    return $d;
  }

  function rest_hours_to_date($date = null){
    if( empty($date) ){
      $date = 'Y-m-d H:i:s';
    }
    $hour_to_rest = App::getConfig('allow_modification_after_this_hours');
    return date("$date",strtotime("-$hour_to_rest hour"));
  }

  function is_time_between_times($time_start=null, $time_end=null, $time_to_look_for=null){
    if ($time_to_look_for > strtotime( $time_start ) && $time_to_look_for < strtotime( $time_end )) {
      return true;
    }
    return false;
  }

  function is_date_between_dates($dt_start=null, $dt_end=null, $dt_check=null){
    return $this->is_time_between_times($dt_start, $dt_end, $dt_check);
  }

}
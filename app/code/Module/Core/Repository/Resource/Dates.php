<?php
class Module_Core_Repository_Resource_Dates extends Core_Model_Repository_Resource {

	function init() {}

	/**
	 * @desc Suma N dias a la fecha actual obtenida por NOW
	 *
	 * @param INT $dias | Numero de dias a sumar
	 * @return string | fecha en formato "dd/mm/yyyy"
	 */
	function incrementa_dias($dias=false){
		if($dias<0||!$dias) return false;

		return date( "d/m/Y", strtotime( "now +".$dias." days" ));
	}

	/**
	 * @desc Metodo para obtener el detallado de las semanas que contiene un año
	 * @param $year | Año del cual deseamos obtener la informacion
	 * @param $mes_inicial | Mes DESDE el cual se desean obtener los datos (comenzar desde el)
	 * @param $mes_final | Mes HASTA el cual se desean obtener los datos (terminar en el)
	 * @return Array
	 */
	function getWeeksInYear($year=false,$mes_inicial=1,$mes_final=12){
		if(!$year) $year=date("Y");

		for($i=(int)$mes_inicial;$i<=(int)$mes_final; $i++){
			// dias que tiene el mes
				$dias=date('t', mktime (0,0,0, $i,1,$year ));
			// El mes
				$mes=date('F', mktime (0,0,0, $i,1,$year )).'<br>';
			// Semana Inicial
				$inicia=App::module('Core')->getResource('Dates')->weekOfTheYear( mktime(0,0,0,$i,1,$year ) );
			// Semana Final
				$termina=App::module('Core')->getResource('Dates')->weekOfTheYear( mktime(0,0,0,$i,$dias,$year ) );
			// Inicio Proxima Semana | Util para saber si SEMANA FINAL esta ligada al inicio de la PROXIMA SEMANA, es decir, que compartan DIAS
				$proxima_semana=App::module('Core')->getResource('Dates')->weekOfTheYear( mktime(0,0,0,($i+1),1,$year ) );

			// Sacamos el # de semana en el que termina el mes anterior (No aplica para ENERO)
				if($i>1 || $i>7){
					$semana_termino_mes_anterior=@$fechas[$i-1]['termina'];
				}else{
					$semana_termino_mes_anterior=0;
				}

			// Ultima semana (para diciembre!)
				if($termina==1){
					// En caso de 1, necesitamos sacar que numero de semana dio, para ello, tenemos que sacar la semana anterior...como la semana tiene 7 dias
					// iniciamos un FOR 1::8, el cual RESTA 1 dia a la fecha, identificamos la semana, asi obtenemos la semana previa: RARO, pero funciona :S
					for($x=1;$x<=8; $x++){
						$valor=App::module('Core')->getResource('Dates')->weekOfTheYear( mktime(0,0,0,$i,($dias-$x),$year));
						if( $valor!=$termina){
							$termina=$valor+1;
							break;
						}
					}
				}
			// Re-iniciamos la primera semana de ENERO cuando de 52,53+ a 1
				if($inicia>=52) $inicia=1;
			// Sacamos el total de semanas que tiene el mes en curso, restando la semana TERMINA con la semana INICIAL
				$semanas_en_mes=$termina-$inicia;
			// Con esta suma, definimos correctamente la semana en la que TERMINA
				$termina=$semanas_en_mes+$inicia;

				$fechas[$i]= Array(	'mes' => $mes,
									'inicia'=>$inicia,
									'termina' => $termina,
									'termino_mes_anterior' => $semana_termino_mes_anterior,
									'proxima_semana' => $proxima_semana,
									'total_semanas'=>$semanas_en_mes,
									'total_dias'=>$dias	);

			// Asignamos el COLSPAN que necesitara para renderizar
				$fechas[$i]['colspan']=$this->colspan($fechas[$i]);
			}
		// Necesitamos el rango de INICIO y FINAL de la semanas para los meses ?
			$fechas[0]= array(
				'inicia'=>$fechas[$mes_inicial]['inicia'],
				'termina'=>$fechas[$mes_final]['termina'],
			);

		return $fechas;
	}

	/**
	 * Metodo para obtener el COLSPAN que necesita el metodo getWeeksInYear
	 *
	 * @param Array $fecha | Array de datos
	 * @return int
	 */
	function colspan($fecha){
		if( ($fecha['inicia']==$fecha['termino_mes_anterior']) && ($fecha['termino_mes_anterior']!=0 ) ){
			$sumar=1;
		}else{
			$sumar=2;
		}

		$i2=$fecha['inicia'];
		$t=$fecha['termina'];

		for($i=$i2;$i<=$t; $i++){
			@$colspan+=$sumar;
			$sumar=2;
		}

		// Si se comparte, restarle 1 al COLSPAN
		if($fecha['proxima_semana']==$fecha['termina']){
			$colspan--;
		}
		return $colspan;
	}

	/**
	 * @desc Metodo para sacar los dias que tiene 1 semana.
	 * Entiendase 1 semana, al # de semana correspondiente por año. Recordemos que el # de semanas varía entre 52 y 53
	 * @param $weekNumber | Numero de semana
	 * @param $year | Año de la semana
	 * @return array
	 */
	function getDaysInWeek ($weekNumber=1, $year=false) {
		if(!$year) $year=date("Y");
		  // Count from '0104' because January 4th is always in week 1
		  // (according to ISO 8601).
		  $time = strtotime($year . '0104 +' . ($weekNumber - 1)
		                    . ' weeks');
		  // Get the time of the first day of the week
		  $mondayTime = strtotime('-' . (date('w', $time) - 1) . ' days',
		                          $time);
		  // Get the times of days 0 -> 6
		  $dayTimes = array ();
		  for ($i = 0; $i < 7; ++$i) {
		  		$dia	= strftime("%A",strtotime('+' . $i . ' days', $mondayTime));
		  		$fecha	= App::locale()->toDate(strftime('%Y-%m-%d', strtotime('+' . $i . ' days', $mondayTime)),"medium");
		  		$dayTimes[] = $dia . ', '. $fecha ;
		  }
		  // Return timestamps for mon-sun.
		  return $dayTimes;
	}

	public function diasEnSemana($weekNumber=1, $year=false){
		if(!$year) $year=date("Y");
		require_once 'Zend/Date.php';

		$date = new Zend_Date();
		$date->setYear($year)
		     ->setWeek($weekNumber)
		     ->setWeekDay(1);

		$weekDates = array();

		for ($day = 1; $day <= 7; $day++) {
		    if ($day == 1) {
		        // we're already at day 1
		        $date->addDay(-1);
		    }
		    else {
		        // get the next day in the week
		        $date->addDay(1);
		    }
		    $weekDates[] = date('l j M, Y', $date->getTimestamp());
		}
		return $weekDates;
	}

	/**
	 * Detalle completo de una semana por su numero "Datos de la semana 23"
	 */
	public function weekInfo($weekNumber=1, $year=false){
		if(!$year) $year=date("Y");
		require_once 'Zend/Date.php';

		$date = new Zend_Date();
		$date->setYear($year)
		     ->setWeek($weekNumber)
		     ->setWeekDay(1);

		$weekDates = array();

		for ($day = 1; $day <= 8; $day++) {
		    if ($day == 1) {
		        // we're already at day 1
		        $mes=date('n', $date->getTimestamp());
		        $date->addDay(-1);
		    }
		    else {
		        // get the next day in the week
		        $date->addDay(1);
		    }
		    $weekDates[] = date('l j M, Y', $date->getTimestamp());
		}
		$mes>6? $semestre=2 : $semestre = 1;
		$respuesta = array_merge(array('dias'=>$weekDates), array('semestre'=>$semestre));
		
		return $respuesta;
	}

	/**
	 * @desc Sacar el numero de semana correspondiente a una fecha
	 * @param $fecha | Fecha en formato TIMESTAMP, la obtenemos con:  mktime(0,0,0,1,11,2009); donde 1=mes, 11=dia, 2009=año
	 * @return int
	 */
	function weekOfTheYear($fecha) {
		$d = getdate($fecha);
		$days = $this->iso_week_days($d["yday"], $d["wday"]);

		if ($days < 0) {
			$d[ "yday"] += 365 + $this->is_leap_year(--$d["year"]);
			$days = $this->iso_week_days($d["yday"], $d["wday"]);
		}else {
			$d["yday"] -= 365 + $this->is_leap_year($d["year"]);
			$d2 = $this->iso_week_days($d["yday"], $d["wday"]);
			if (0 <= $d2)
			$days = $d2;
		}
		return (int)($days / 7) + 1;
	}

	protected function iso_week_days($yday, $wday) {
		return $yday - (($yday - $wday + 382) % 7) + 3;
	}

	protected function is_leap_year($year){
		if ((($year % 4) == 0 and ($year % 100)!=0) or ($year % 400) == 0)
			return 1;
		else
			return 0;
	}

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
	 */
	function toDate($formato,$date,$timezone=false){
		if(!$timezone){
			/*
			 * Buscar usando EDITPLUS en todo el proyecto por archivos .XML que contengan America/
			 * Para ver las combinaciones o timezones disponibles para america
			 */
			date_default_timezone_set('America/Mazatlan');
		}

		if($formato==0){
			// Fechas en idiomas habilitados
			// Estas fechas, estaran dentro del archivo de traducciones.
				$months = array(	1=>App::xlat('dates_short_month_1')
									,2=>App::xlat('dates_short_month_2')
									,3=>App::xlat('dates_short_month_3')
									,4=>App::xlat('dates_short_month_4')
									,5=>App::xlat('dates_short_month_5')
									,6=>App::xlat('dates_short_month_6')
									,7=>App::xlat('dates_short_month_7')
									,8=>App::xlat('dates_short_month_8')
									,9=>App::xlat('dates_short_month_9')
									,10=>App::xlat('dates_short_month_10')
									,11=>App::xlat('dates_short_month_11')
									,12=>App::xlat('dates_short_month_12')
				);
			// Parseamos la fecha
				$tmp = explode("/",$date);
				$mes=false;
				foreach($months AS $key=>$month){
					// Debug
					//echo $month.' <> '.$key.' <> '.$tmp[1].'<br />';					
					if(strtolower($month)==strtolower($tmp[1])){
						$mes=$key;
						break;
					}
				}

				if($mes){
					$date = $tmp[2].'-'.$mes.'-'.$tmp[0];
				}else{
					return false;
				}
		}		

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
				$fecha=$datetime->format('l j F Y');
				break;

			default:
				$fecha=$datetime->format('M j, Y');
				break;
		}

		// Si locale NO es INGLES, traducimos
		// Porque ? porque el mySQL que tengo x default esta en ingles
		if(App::locale()->getLang()!='en'){
			if($formato>6){
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

	/**
	 * Traduce el idioma del calendario para los filtros de javascript
	 * Si el locale es "ES", traducirlos a ES
	 * @param unknown_type $fecha
	 * @param unknown_type $toLang
	 * @return unknown
	 */
	function translateMonths($fecha,$toLang="es"){

//		if($toLang=='es'){
			// Dias
				$englishDates=array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
				$spanishDates=array(App::xlat('dates_short_day_7'),App::xlat('dates_short_day_1'),App::xlat('dates_short_day_2'),App::xlat('dates_short_day_3'),App::xlat('dates_short_day_4'),App::xlat('dates_short_day_5'),App::xlat('dates_short_day_6'));
				$fecha = str_replace($englishDates,$spanishDates,$fecha);
			// Meses
				$englishDates=array('Jan','Apr','Aug','Dec');
				$spanishDates=array(App::xlat('dates_short_month_1'),App::xlat('dates_short_month_4'),App::xlat('dates_short_month_8'),App::xlat('dates_short_month_12'));
				$fecha = str_replace($englishDates,$spanishDates,$fecha);
//		}else{
			// Dias completos
				$englishDates=array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
				$spanishDates=array(App::xlat('dates_long_day_7'),App::xlat('dates_long_day_1'),App::xlat('dates_long_day_2'),App::xlat('dates_long_day_3'),App::xlat('dates_long_day_4'),App::xlat('dates_long_day_5'),App::xlat('dates_long_day_6'));
				$fecha = str_replace($spanishDates,$englishDates,$fecha);
			// Completos
				$englishDates=array('January','February','March','April','May','June','July','August','September','October','November','December');
				$spanishDates=array(App::xlat('dates_long_month_1'),App::xlat('dates_long_month_2'),App::xlat('dates_long_month_3'),App::xlat('dates_long_month_4'),App::xlat('dates_long_month_5'),App::xlat('dates_long_month_6'),App::xlat('dates_long_month_7'),App::xlat('dates_long_month_8'),App::xlat('dates_long_month_9'),App::xlat('dates_long_month_10'),App::xlat('dates_long_month_11'),App::xlat('dates_long_month_12'));
				$fecha = str_replace($spanishDates,$englishDates,$fecha);
//		}
		return $fecha;
	}
	
/**
 * METODOS VIEJITOS QUE CAMBIE EL DIA 20 de ENERO 2010, para tratar de hacerlo MULTILANGUAGE
 * La idea es que las fechas se utilicen por medio del archivo de LOCALE en turno
 */	

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
	 */
	function toDate_OLD($formato,$date,$timezone=false){
		if(!$timezone){
			/*
			 * Buscar usando EDITPLUS en todo el proyecto por archivos .XML que contengan America/
			 * Para ver las combinaciones o timezones disponibles para america
			 */
			date_default_timezone_set('America/Mazatlan');
		}

		if($formato==0){
			// Fechas en idiomas habilitados
			// Estas fechas, estaran dentro del archivo de traducciones.
				$months = array(	1=>App::xlat('dates_short_month_1')
									,2=>App::xlat('dates_short_month_2')
									,3=>App::xlat('dates_short_month_3')
									,4=>App::xlat('dates_short_month_4')
									,5=>App::xlat('dates_short_month_5')
									,6=>App::xlat('dates_short_month_6')
									,7=>App::xlat('dates_short_month_7')
									,8=>App::xlat('dates_short_month_8')
									,9=>App::xlat('dates_short_month_9')
									,10=>App::xlat('dates_short_month_10')
									,11=>App::xlat('dates_short_month_11')
									,12=>App::xlat('dates_short_month_12')
				);
			// Parseamos la fecha
				$tmp = explode("/",$date);
				$mes=false;
				foreach($months AS $key=>$month){
					if($month==strtolower($tmp[1])){
						$mes=$key;
						break;
					}
				}

				if($mes){
					$date = $tmp[2].'-'.$mes.'-'.$tmp[0];
				}else{
					return false;
				}
		}		

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
				$fecha=$datetime->format('l j F Y');
				break;

			default:
				$fecha=$datetime->format('M j, Y');
				break;
		}

		// Traducimos a español, si locale=ES
		if(App::locale()->getLang()==='es'){
			if($formato>6){
			// Dias completos
				$englishDates=array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
				$spanishDates=array('Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado');
				$fecha = str_replace($englishDates,$spanishDates,$fecha);
			// Completos
				$englishDates=array('January','February','March','April','May','June','July','August','September','October','November','December');
				$spanishDates=array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
				$fecha = str_replace($englishDates,$spanishDates,$fecha);
			}else{
			// Dias
				$englishDates=array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
				$spanishDates=array('Dom','Lun','Mar','Mier','Jue','Vie','Sab');
				$fecha = str_replace($englishDates,$spanishDates,$fecha);
			// Meses
				$englishDates=array('Jan','Apr','Aug','Dec');
				$spanishDates=array('Ene','Abr','Ago','Dic');
				$fecha = str_replace($englishDates,$spanishDates,$fecha);
			}
		}
		return $fecha;
	}
	
	/**
	 * Traduce el idioma del calendario para los filtros de javascript
	 * Si el locale es "ES", traducirlos a ES
	 * @param unknown_type $fecha
	 * @param unknown_type $toLang
	 * @return unknown
	 */
	function translateMonths_OLD($fecha,$toLang="es"){

		if($toLang=='es'){
			// Dias
				$englishDates=array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
				$spanishDates=array('Dom','Lun','Mar','Mier','Jue','Vie','Sab');
				$fecha = str_replace($englishDates,$spanishDates,$fecha);
			// Meses
				$englishDates=array('Jan','Apr','Aug','Dec');
				$spanishDates=array('Ene','Abr','Ago','Dic');
				$fecha = str_replace($englishDates,$spanishDates,$fecha);
		}else{
			// Dias completos
				$englishDates=array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
				$spanishDates=array('Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado');
				$fecha = str_replace($spanishDates,$englishDates,$fecha);
			// Completos
				$englishDates=array('January','February','March','April','May','June','July','August','September','October','November','December');
				$spanishDates=array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
				$fecha = str_replace($spanishDates,$englishDates,$fecha);
		}
		return $fecha;
	}
	
}
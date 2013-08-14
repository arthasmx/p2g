<?php

class Module_Core_Repository_Resource_Pdf extends Core_Model_Repository_Resource {

	protected $documento	= false;
	protected $resultado	= false;
	protected $escrito		= false;
	protected $envia		= false;
	protected $titulo		= false;
	protected $clientes		= false;


	protected $carta		= false;
	protected $opciones		= false;

	protected $page = 1;

// INICIALIZACION ******************************************************************************************

	function init(){
	}

	/**
	 * Intercepta la llama a funciones que no existen y la ejecutamos
	 *
	 * @param unknown_type $function
	 * @param unknown_type $args
	 * @return unknown
	 */
	public function __call($function, $args) {
		// Comprueba SET

			preg_match("/^set([a-zA-Z]+)$/",$function,$matches);

			if (isset($matches[1])) {
				$var=strtolower($matches[1]);
				if (isset($this->{$var}) || @$this->{$var}===false) {
					$this->{$var}=$args[0];
				}

				return $this;
			}

		// Comprueba GET
			preg_match("/^get([a-zA-Z]+)$/",$function,$matches);
			if (isset($matches[1])) {
				$var=strtolower($matches[1]);
				if (isset($this->{$var})) {
					return $this->{$var};
				}
				return false;
			}

	}

	/**
	 * Creamos PDF con ayuda de Fpdf
	 *
	 * @param unknown_type $documento
	 * @return unknown
	 */
	public function create() {
		if ( !$this->documento)	$this->_module->exception( App::xlat('Es necesario un nombre de archivo para generar su PDF'),501 );
		if ( !$this->resultado)	$this->_module->exception( App::xlat('No hay datos para generar su PDF'),501 );
		if ( !$this->titulo)	$this->_module->exception( App::xlat('Es importante definir el titulo de los documentos'),501 );

		ini_set("memory_limit","128M");

			require_once("External/Fpdf/WriteHTML.php");
			$pdf=new PDF_HTML();

		// Desactiva el salto de pagina automatico
			$pdf->SetAutoPageBreak(false);
			$pdf->AddPage();

			$page=0;
			foreach($this->resultado as $carta){

				// Ventanilla
					$pdf->SetFont('Arial','',10);
					$pdf->SetXY(102.5,41);
					$pdf->MultiCell(0, 5, utf8_decode($carta['name'].PHP_EOL.$carta['address'].PHP_EOL. 'CP '.$carta['zip'].PHP_EOL.$carta['locality'] . ' (' . $carta['province']. ')'),0,'J' );

				// Remitente
					$pdf->SetFont('Arial','',8);
					$pdf->SetXY(10,41);
					$pdf->MultiCell(0, 5, utf8_decode($this->carta['name'].PHP_EOL.$this->carta['address'].PHP_EOL.$this->carta['provlocal'].PHP_EOL.$this->carta['phone']),0,'J' );

				// Titulo
					//$pdf->SetTextColor(0,0,0);
					$pdf->SetFont('Arial','B',11);
					$pdf->Cell(0, 40, utf8_decode($this->titulo) );

				// Carta
					$originales	 = array("\n","\r", "<p>", "</p>", "<strong>", "</strong>");
					$modificados = array("","", '<P ALIGN="justify">', '</P><BR>', '<STRONG>', '</STRONG>');
					$this->escrito = str_replace($originales,$modificados,$this->escrito);
					$pdf->SetFont('Arial','',10);
					$pdf->SetY(95);
					//$pdf->WriteHTML(utf8_decode($this->escrito));
					$pdf->WriteHTML(utf8_decode($this->escrito).'<br><br><br><strong>'.$this->carta['saludo'].'</strong><br>'.utf8_decode($this->carta['firma']));

					if ($page < count($this->resultado)-1) {
						$pdf->AddPage();
						$page+=1;
					}
			}

		//Create file { F= guarda el archivo. I=Lo lanza al navegador}
			$pdf->Output( $this->documento ,"F");

		return true;
	}

	public function start(){
		if ( !$this->documento)	$this->_module->exception( App::xlat('Es necesario un nombre de archivo para generar su PDF'),501 );
		//if ( !$this->resultado)	$this->_module->exception( App::xlat('No hay datos para generar su PDF'),501 );
		//if ( !$this->envia)		$this->_module->exception( App::xlat('Es importante definir el nombre de la persona que envia este documento'),501 );
		if ( !$this->titulo)	$this->_module->exception( App::xlat('Es importante definir el titulo de los documentos'),501 );

		require_once("External/Fpdf/WriteHTML.php");
		$pdf=new PDF_HTML();

		// Desactiva el salto de pagina automatico
			$pdf->SetAutoPageBreak(false);
			$pdf->AddPage();

		return $this;
	}

	public function save(){
		if ( !$this->pdf)	$this->_module->exception( App::xlat('No se ha iniciado el objeto de libreria PDF'),501 );

		//Create file { F= guarda el archivo. I=Lo lanza al navegador}
			$pdf->Output( $this->documento ,"F");
	}

	public function csv2pdf() {
		ini_set("memory_limit","128M");
		$this->start();

		foreach($this->resultado as $carta){

			// Ventanilla
				$pdf->SetFont('Arial','',10);
				$pdf->SetXY(102.5,41);
				$pdf->MultiCell(0, 5, utf8_decode($carta['name'].PHP_EOL.$carta['address'].PHP_EOL. 'CP '.$carta['zip'].PHP_EOL.$carta['locality'] . ' (' . $carta['province']. ')'),0,'J' );

			// Remitente
				$pdf->SetXY(10,41);
				$pdf->MultiCell(0, 5, utf8_decode($this->opciones['name'].PHP_EOL.$this->opciones['address'].PHP_EOL.$this->opciones['provlocal'].PHP_EOL.$this->opciones['phone']),0,'J' );

			// Titulo
				$pdf->SetFont('Arial','B',11);
				$pdf->Cell(0, 40, utf8_decode($this->titulo) );

			// Carta
				$originales	 = array("\n","\r", "<p>", "</p>", "<strong>", "</strong>");
				$modificados = array("","", '<P ALIGN="justify">', '</P><BR>', '<STRONG>', '</STRONG>');
				$this->escrito = str_replace($originales,$modificados,$this->escrito);

				$pdf->SetFont('Arial','',10);
				$pdf->SetY(95);
				$pdf->WriteHTML(utf8_decode($this->escrito).'<br><br><br>Atentamente:<br>'.utf8_decode($this->envia));

				if ($this->page < $this->opciones['rango']) {
					$pdf->AddPage();
					$this->page+=1;
				}else {
					$this->save();
				}

		}

		return true;
	}

	/**
	 * Metodo para crear etiquetas de una lista de clientes
	 * @return unknown_type
	 */
	public function etiquetas(){
		if ( !$this->clientes)	return false;

		ini_set("memory_limit","128M");

		define('FPDF_FONTPATH',FP.DS."font".DS);

			require_once("External/Fpdf/WriteHTML.php");
			$pdf=new PDF_HTML('P', 'cm', array('21.59','27.94'));

			// Desactiva el salto de pagina automatico
			$pdf->SetAutoPageBreak(false);
			$pdf->AddPage();

			$page=0;
			$pdf->SetFont('Arial','',8);

			foreach($this->clientes as $cliente){

					$pdf->SetXY(0,1);
					$pdf->MultiCell(0, .3, utf8_decode($cliente['nombre'].PHP_EOL.$cliente['direccion'].PHP_EOL.$cliente['colonia'].PHP_EOL.$cliente['ciudad'].', '.$cliente['estado'].PHP_EOL.$cliente['pais']),250,'J' );

					$pdf->SetXY(4,1);
					$pdf->MultiCell(0, .3, utf8_decode($cliente['nombre'].PHP_EOL.$cliente['direccion'].PHP_EOL.$cliente['colonia'].PHP_EOL.$cliente['ciudad'].', '.$cliente['estado'].PHP_EOL.$cliente['pais']),250,'J' );

					$pdf->SetXY(8,1);
					$pdf->MultiCell(0, .3, utf8_decode($cliente['nombre'].PHP_EOL.$cliente['direccion'].PHP_EOL.$cliente['colonia'].PHP_EOL.$cliente['ciudad'].', '.$cliente['estado'].PHP_EOL.$cliente['pais']),250,'J' );

					$pdf->SetXY(12,1);
					$pdf->MultiCell(0, .3, utf8_decode($cliente['nombre'].PHP_EOL.$cliente['direccion'].PHP_EOL.$cliente['colonia'].PHP_EOL.$cliente['ciudad'].', '.$cliente['estado'].PHP_EOL.$cliente['pais']),250,'J' );

					$pdf->SetXY(16,1);
					$pdf->MultiCell(0, .3, utf8_decode($cliente['nombre'].PHP_EOL.$cliente['direccion'].PHP_EOL.$cliente['colonia'].PHP_EOL.$cliente['ciudad'].', '.$cliente['estado'].PHP_EOL.$cliente['pais']),250,'J' );


/*
					if ($page < count($this->clientes)-1) {
						$pdf->AddPage();
						$page+=1;
					}
*/
			}



		//Create file { F= guarda el archivo. I=Lo lanza al navegador}
			$pdf->Output( FP.DS."etiquetas".DS."current.pdf" ,"F");

		return true;
	}

}
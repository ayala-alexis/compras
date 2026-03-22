<?php

/**
 * @author wserpas
 *
 */
class ODBC {
	// TODO - Insert your code here
	private $server;
	private $user;
	private $pass;
	private $conn;
	private $_numero;
	private $_mensaje;
	private $_sql;
	private $stmt;
	public $gastit;
	public $gasdet;
	public $AMesPresupuesto;
	public $dispo2;
	public $dispo2D;
	public $NMES;

	private static $_instance;

	/**
	 * seteamos la conexion y establecemos la conexion
	 */
	function __construct() {
		// TODO - Insert your code here
		$this->setConexion();
		$this->conectar();
	}

	// Establece los parametros para la conexion
	private function setConexion(){
		$c = Configuracion::getInstance();
		$this->server = $c->getServer();
		$this->user = $c->getUser();
		$this->pass = $c->getPass();
	}

	// Evitamos el clonaje de objeto
	private function __clone(){

	}
	private function __wakeup(){

	}

	/* Funcion encargada de crear si es necesario, el objeto. Esta es la funcion
	que debemos
	llamar desde fuera de la clase para instanciar el objeto, y asi, poder
	reutilizar sus metodos */
	public static function getInstance(){
		if(!(self::$_instance instanceof self)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	// Realiza la conexion
	private function conectar(){
		$conn = odbc_connect($this->server,$this->user,$this->pass);
		if ($conn == false) {
			throw new Exception ($this->Error());
		} else {
			$this->conn = $conn;
		}
	}

	// Metodo para ejecutar una sentencia sql
	public function ejecutar($sql) {
		// Pongamos en una variable lo que queremos ejecutar
		$this->_sql = $sql;
		$this->stmt = odbc_exec($this->conn,  $this->_sql );
		if (!$this->stmt) {
			throw new Exception ( $this->Error() );
		}
		return $this->stmt;
	}

	/*
	 * SEPARA GASTO
	 * separa la cuenta de gasto
	 * Parametros:
	 * $gasto = TITG+DETG
	 */
	public function separaGasto($gasto){
		$this->gastit = substr($gasto,0,2);
		$this->gastit = str_pad($this->gastit,2,"0",STR_PAD_LEFT);
		$this->gasdet = substr($gasto,2,2);
		$this->gasdet = str_pad($this->gasdet,2,"0",STR_PAD_LEFT);
	}
	/*
	 * OBTIENE EL GASTO ACTUAL ACUMULADO DEL AÑO Y MES EN CURSO
	 * DE LAS ENTRADAS (REVERSAS) PARA RESTARSELO AL GASTO DE LAS ENTRADAS
	 * CON ESTO SE OBTIENE EL GASTO REAL
	 * Parametros:
	 * $y = Año
	 * $cia = Compañia
	 * $gasto = Cuenta de gasto unida TITG+DETG
	 * $cc = Centro de Costo
	 * $mes = Mes de la Orden
	 */
	public function GastoM($y,$cia,$gasto,$cc,$mes){
		$fecha1 = $y.$mes.'01';
		$fecha2 = $y.$mes.'31';
		$s2 = "Select SUM(PMVAL) From CTBLIBDB.CTPREMO Where PMCIA = '".$cia."' "
			." AND PMFECR BETWEEN '".$fecha1."' AND '".$fecha2."' AND PMCCO = '".$cc."' "
			." AND PMTABG = '".$gasto."' AND PMTIPM = 'E' AND SUBSTRING(PMSWIC,1,1) <> '*' AND SUBSTRING(PMSWIC,3,1) <> 'I'";
		$q2 = $this->ejecutar($s2);
		odbc_fetch_row($q2);
		return odbc_result($q2, 1);
	}
	/*
	 * OBTIENE EL GASTO ACTUAL ACUMULADO DEL AÑO Y MES EN CURSO
	 * DE LAS SALIDAS
	 * Parametros:
	 * $y = Año
	 * $cia = Compañia
	 * $gasto = Cuenta de gasto unida TITG+DETG
	 * $cc = Centro de Costo
	 * $mes = Mes de la Orden
	 */
	public function GastoMS($y,$cia,$gasto,$cc,$mes){
		$fecha1 = $y.$mes.'01';
		$fecha2 = $y.$mes.'31';
		$s2 = "Select SUM(PMVAL) From CTBLIBDB.CTPREMO Where PMCIA = '".$cia."' "
			." AND PMFECR BETWEEN '".$fecha1."' AND '".$fecha2."' AND PMCCO = '".$cc."' "
			." AND PMTABG = '".$gasto."' AND PMTIPM = 'S' AND SUBSTRING(PMSWIC,1,1) <> '*' AND SUBSTRING(PMSWIC,3,1) <> 'I'";
		$q2 = $this->ejecutar($s2);
		odbc_fetch_row($q2);
		return odbc_result($q2, 1);
	}
	/*
	 * VERIFICA QUE EXISTA EL CENTRO DE COSTO EN EL PRESUPUESTO
	 * Parametros:
	 * $y = Año
	 * $cia = Compañia
	 * $gasto = Cuenta de gasto unida TITG+DETG
	 * $cc = Centro de Costo
	 */
	public function Existe($y,$cia,$cc){
		$s3 = "Select COUNT(*) From CTBLIBDB.CTPRESU Where PRCIA = '".$cia."' "
				." AND PRAÑO = '".$y."' AND PRCCO = '".$cc."'";
		$q3 = $this->ejecutar($s3);
		odbc_fetch_row($q3);
		if (odbc_result($q3, 1) <= 0 ){
			return "CENTRO DE COSTO NO EXISTE EN PRESUPUESTO.";
		} else {
			return 0;
		}
	}
	/*
	 * VERIFICA QUE EXISTA PRESUPUESTO PARA LA CUENTA DE GASTO
	 * SELECCIONADA
	 * Parametros;
	 * $y = Año
	 * $cia = Compañia
	 * $cc = Centro de Costo
	 * $gasto = Cuenta de Gasto
	 */
	public function ExisteCuenta($y,$cia,$cc,$gasto){
		$s3 = "Select COUNT(*) From CTBLIBDB.CTPRESU Where PRCIA = '".$cia."' "
			." AND PRAÑO = '".$y."' AND PRCCO = '".$cc."' "
			." AND PRTABG = '".$gasto."'";
		$q3 = $this->ejecutar($s3);
		odbc_fetch_row($q3);
		if ( odbc_result($q3, 1) <= 0 ){
			return "CUENTA DE GASTO NO PRESUPUESTADA PARA ESTE CENTRO DE COSTO.";
		} else {
			return 0;
		}
	}
	/*
	 * VERIFICA SI TIENE MONTO DE PRESUPUESTO SEGUN EL MES
	 * Parametros:
	 * $y = Año
	 * $cia = Compañia
	 * $gasto = Cuenta de gasto unida TITG+DETG
	 * $cc = Centro de Costo
	 * $mes = Mes
	 */
	public function MontoPresupuesto($y,$cia,$gasto,$cc,$mes,$valor){
		$mes = str_pad($mes,2,"0",STR_PAD_LEFT);
		$campo = 'PREM'.$mes;
		$s4 = "Select ".$campo." From CTBLIBDB.CTPRESU Where PRCIA = '".$cia."' "
			." AND PRAÑO = '".$y."' AND PRCCO = '".$cc."' "
			." AND PRTABG = '".$gasto."'";
		$q4 = $this->ejecutar($s4);
		odbc_fetch_row($q4);
		if ($valor == '1'){
			if (odbc_result($q4, 1) <= 0 ){
				return "CENTRO DE COSTO NO TIENE PRESUPUESTO PARA ESTE GASTO.";
			} else {
				return 0;
			}
		} else {
			return odbc_result($q4, 1);
		}
	}
	/*
	 * VERIFICA SI EXISTE AUTORIZACION DE FONDOS PARA EL MES EN CURSO
	 * DEL CENTRO DE COSTO Y CUENTA DE GASTO SELECCIONADA
	 * Parametros:
	 * $y = Año
	 * $cia = Compañia
	 * $gasto = Cuenta de gasto unida TITG+DETG
	 * $cc = Centro de Costo
	 * $mes = Mes
	 */
	public function MontoAutorizado($y,$cia,$gasto,$cc,$mes,$valor){
		$s5 = "Select SUM(PAADIC) From CTBLIBDB.CTPREAU Where PACIA = '".$cia."' "
			." AND PAAÑO = '".$y."' AND PAMES = '".$mes."' "
			." AND PACCO = '".$cc."' "
			." AND PATABG = '".$gasto."'";
		$q5 = $this->ejecutar($s5);
		odbc_fetch_row($q5);
		if ($valor == '1'){
			if ( odbc_result($q5, 1) <= 0 ){
				return "CENTRO DE COSTO NO TIENE PRESUPUESTO, NI AUTORIZACIONES DE FONDO PARA ESTE GASTO.";
			} else {
				return 0;
			}
		} else {
			return odbc_result($q5, 1);
		}
	}
	/*
	 * RETORNA EL VALOR QUE SE RESTA POR AUTORIZACIONES
	 * DEL CENTRO DE COSTO Y CUENTA DE GASTO SELECCIONADA
	 * Parametros:
	 * $y = Año
	 * $cia = Compañia
	 * $gasto = Cuenta de gasto unida TITG+DETG
	 * $cc = Centro de Costo
	 * $mes = Mes
	 */
	public function MontoDecremento($y,$cia,$gasto,$cc,$mes){
		$s5 = "Select SUM(PAREST) From CTBLIBDB.CTPREAU Where PACIA = '".$cia."' "
			." AND PAAÑO = '".$y."' AND PAMES = '".$mes."' "
			." AND PACCO = '".$cc."' "
			." AND PATABG = '".$gasto."'";
		$q5 = $this->ejecutar($s5);
		odbc_fetch_row($q5);
		return odbc_result($q5, 1);
	}
	/*
	 * DEVUELVE INFORMACION DEL CHEQUE ASOCIADO A UNA ORDEN DE COMPRA
	 */
	 public function infoCheque($fecha,$cia,$gasto,$cc,$orden,$punt){
		$orden = str_pad($orden,10,' ',STR_PAD_LEFT);
		if ($punt == '1'){
			$s6 = "Select * From CTBLIBDB.CTPREMO Where PMCIA = '".$cia."'"
				." AND PMFECR = '".$fecha."' AND PMTIPD = 'OC'"
				." AND PMCCO = '".$cc."' "
				." AND PMNDOC = '".$orden."'";
		} else {
			$s6 = "Select * From CTBLIBDB.CTPREMO Where PMCIA = '".$cia."'"
				." AND PMFECR = '".$fecha."' AND PMTIPD = 'OC'"
				." AND PMNDOC = '".$orden."'";
		}
		$q6 = $this->ejecutar($s6);
		$html = '<table border="0" width="70%" class="adminlist">
		<tr>
			<td colspan="6" align="center"><b>INFORMACION DE CHEQUE ENCONTRADA</b></td>
		</tr>
		<tr>
			<td>Centro de Costo</td>
			<td>No. de Orden</td>
			<td>Cuenta de Gasto</td>
			<td>Fecha de Orden</td>
			<td>Fecha Contable</td>
			<td>No. de Cheque</td>
		</tr>';
		while (odbc_fetch_row($q6)){
			$html .= '<tr>
				<td>'.odbc_result($q6, 2).'</td>
				<td>'.odbc_result($q6, 6).'</td>
				<td>'.odbc_result($q6, 3).'</td>
				<td>'.odbc_result($q6, 4).'</td>
				<td>'.odbc_result($q6, 16).'</td>
				<td>'.odbc_result($q6, 20).'</td>
			</tr>
			';
		}
		$html .= '</table>';
		return $html;
	}
	/*
	 * DEVUELVE INFORMACION DE PROVISION DE UNA ORDEN DE COMPRA
	 */
	public function infoProvision($fecha,$cia,$gasto,$cc,$orden,$punt){
		$orden = str_pad($orden,10,' ',STR_PAD_LEFT);
		if ($punt == '1'){
			$s7 = "Select * From CTBLIBDB.CTPREMO Where PMCIA = '".$cia."'"
				." AND PMFECR = '".$fecha."' AND PMTIPD = 'OC'"
				." AND PMCCO = '".$cc."' "
				." AND PMNDOC = '".$orden."'";
		} else {
			$s7 = "Select * From CTBLIBDB.CTPREMO Where PMCIA = '".$cia."'"
				." AND PMFECR = '".$fecha."' AND PMTIPD = 'OC'"
				." AND PMNDOC = '".$orden."'";
		}
		$q7 = $this->ejecutar($s7);
		$html = '<table border="0" width="80%" class="adminlist">
		<tr>
			<td colspan="8" align="center"><b>INFORMACION DE PROVISION ENCONTRADA</b></td>
		</tr>
		<tr>
			<td>Fecha de Orden</td>
			<td>Centro de Costo</td>
			<td>No. de Orden</td>
			<td>Cuenta de Gasto</td>
			<td>Fecha Contable</td>
			<td>Comprobante Contable</td>
			<td>No. Secuencia</td>
			<td>Origen</td>
		</tr>';
		while (odbc_fetch_row($q7)){
			$html .= '<tr>
				<td>'.odbc_result($q7, 4).'</td>
				<td>'.odbc_result($q7, 2).'</td>
				<td>'.odbc_result($q7, 6).'</td>
				<td>'.odbc_result($q7, 3).'</td>
				<td>'.odbc_result($q7, 16).'</td>
				<td>'.odbc_result($q7, 17).'</td>
				<td>'.odbc_result($q7, 18).'</td>
				<td>'.odbc_result($q7, 19).'</td>
			</tr>
			';
		}
		$html .= '</table>';
		return $html;
	}
	/*
	 * 	VERIFICA SI ESTA ANTES O DESPUES DEL CIERRE
	 */
	public function verCierre($fecha,$cia){
		$yyyy = substr($fecha,0,4);
		$mm = substr($fecha,4,2);
		$yyyymm = $yyyy.$mm;
		$s8 = "Select PUCERA From CTBLIBDB.CTPREPU Where PUCIA = '".$cia."'"
			." And PUCOD = 'CI'";
		$q8 = $this->ejecutar($s8);
		odbc_fetch_row($q8);
		if (odbc_result($q8, 1) <= $yyyymm ){
			$res = 'ANTES';
		} else {
			$res = 'DESPUES';
		}
		return $res;
	}
	/*
	 * 	RETORNA PERIODO DE ORDENES
	 */
	public function FechaPresupuesto($cia){
		$s8 = "Select PUCERA From CTBLIBDB.CTPREPU Where PUCIA = '".$cia."' "
				." AND PUCOD = 'CI'";
		$q8 = $this->ejecutar($s8);
		odbc_fetch_row($q8);
		$Y = date("Y");
		$YPun = substr(odbc_result($q8, 1),0,4);
		if ($YPun<$Y){
			//$this->AMesPresupuesto = $Y."01";
			$this->AMesPresupuesto = odbc_result($q8, 1);
		} else {
			$this->AMesPresupuesto = odbc_result($q8, 1);
		}
		return $this->AMesPresupuesto;
	}

	/*
	 * Obtine Disponible de meses anteriores
	 * @ $cia: Compañia
	 * @ $cc: Centro de Costo
	 * @ $gasto: Tabla de Gasto
	 * @ $mes: Mes de Gasto
	 */
	public function Dispo2($ciad,$ccd,$gastod,$mesd,$dispod) {
		$d = date_parse_from_format('Y-m-d', $dispod);
		$mpre = $d["month"];
		$apre = $d["year"];
		$msgd2 = "\nResultados :\n";
		$this->FechaPresupuesto($ciad);
		$aprePU = substr($this->AMesPresupuesto,0,4);
		$mprePU = substr($this->AMesPresupuesto,4,2);
		$msgd2 .= "Puntero: anio ".$aprePU.' mes '.$mprePU."\n";
		//$apre = substr($dispod,6,4);
		//$mpre = substr($dispod,3,2);
		// Si el mes de la orden es mayor al del puntero ponemos el del puntero
		if($mpre > $mprePU) { 
			$mpre = $mprePU;
		}
		$i=1;
		$this->dispo2 = 0;
		$suma = 0;
		$resta = 0;
		$gNeto=0;
		do {
			$i2 = str_pad($i,2,'0',STR_PAD_LEFT);
			$fldpre = 'PREM'.$i2;
			$fldgas = 'PRGM'.$i2;
			$msgd2 .= "Mes : ".$i2." Anio : ".$apre."\n";
			$suma_aut=0;
			$rest_aut=0;
			//  PRESUPUESTO
			$sq_d = "Select ".$fldpre.",".$fldgas." From CTBLIBDB.CTPRESU "
				."Where PRCIA='".$ciad."' And PRAÑO='".$apre
				."' And PRCCO='".$ccd."' And PRTABG='".$gastod."'";
			$qr_d = $this->ejecutar($sq_d);
			odbc_fetch_row($qr_d);
			$msgd2 .= "Presupuesto ".$i2." : Pre=".odbc_result($qr_d, 1)." Gas=".odbc_result($qr_d, 2)."\t";
			$suma += odbc_result($qr_d, 1);
			$resta += odbc_result($qr_d, 2);
			odbc_free_result($qr_d);
			// AUTORIZACIONES
			$sq_a = "Select PAADIC,PAREST From CTBLIBDB.CTPREAU "
				."Where PACIA='".$ciad."' And PAAÑO='".$apre."' And PAMES='".$i2."'"
				." And PACCO='".$ccd."' And PATABG='".$gastod."'";
			$qr_a = $this->ejecutar($sq_a);
			while(odbc_fetch_row($qr_a)) {
				$suma += odbc_result($qr_a, 1);
				$resta += odbc_result($qr_a, 2);
				$suma_aut += odbc_result($qr_a, 1);
				$rest_aut += odbc_result($qr_a, 2);
			}
			odbc_free_result($qr_a);
			$msgd2 .= "Autorizaciones ".$i2." : Adic=".$suma_aut." Dec=".$rest_aut."\n";
			if ($i >= $mprePU) {
				$fecha1 = $apre.$i2.'01';
				$fecha2 = $apre.$i2.'31';
				// GASTOS AUN NO CERRADOS
				$s2 = "Select SUM(PMVAL) From CTBLIBDB.CTPREMO Where PMCIA = '".$ciad."' "
					." AND PMFECR BETWEEN '".$fecha1."' AND '".$fecha2."' AND PMCCO = '".$ccd."' "
					." AND PMTABG = '".$gastod."' AND PMTIPM = 'S' AND SUBSTRING(PMSWIC,1,1) <> '*' AND SUBSTRING(PMSWIC,3,1) <> 'I'";
				$q2 = $this->ejecutar($s2);
				odbc_fetch_row($q2);
				$gastoSalidas = odbc_result($q2, 1);
				odbc_free_result($q2);
				$s2 = "Select SUM(PMVAL) From CTBLIBDB.CTPREMO Where PMCIA = '".$ciad."' "
					." AND PMFECR BETWEEN '".$fecha1."' AND '".$fecha2."' AND PMCCO = '".$ccd."' "
					." AND PMTABG = '".$gastod."' AND PMTIPM = 'E' AND SUBSTRING(PMSWIC,1,1) <> '*' AND SUBSTRING(PMSWIC,3,1) <> 'I'";
				$q2 = $this->ejecutar($s2);
				odbc_fetch_row($q2);
				$gastoEntradas = odbc_result($q2, 1);
				odbc_free_result($q2);
				$gastoNeto = $gastoSalidas+$gastoEntradas; // Gasto Neto = Salidas - Entradas
				$gNeto += $gastoNeto;
				$msgd2 .= "Gastos ".$i2." : ".$gastoNeto."\n";
			}
			$i++;
		} while($i <= 12); //$mesd
		$restar = $resta+$gNeto;
		$this->dispo2 = $suma-$restar;
		$this->dispo2D = $msgd2;
	}
	/*
	 * Verifica si cuenta podra utilizar presupuesto disponible
	 * de todo el año
	 * @ $cia: Compañia
	 * @ $cc: Centro de Costo
	 * @ $gasto: Cuenta de Gasto
	 */
	public function ocuparaPresupuestoMesesAnteriores($cia,$cc,$gasto) {
		$bandera = 'N';
		$sq_ocupa = "Select CPSWIT From CTBLIBDB.CTPRECP Where CPCIA='".$cia
			."' And CPCCO='".$cc."' And CPTABG='".$gasto."'";
		$qr_ocupa = $this->ejecutar($sq_ocupa);
		odbc_fetch_row($qr_ocupa);
		$swit = substr(odbc_result($qr_ocupa, 1),0,1);
		if ($swit == '*') {
			$bandera = 'S';
		}
		return $bandera;
	}
	/*
	Cuenta de gasto verifica presupuesto
	*/
	public function valida_presupuesto_cuenta_gasto($tipo_gasto,$detalle_gasto) {
		$sq_ocupa = "
						SELECT 
						SUBSTRING(RSWIC,1,1) VERIFICA
						FROM CTBLIBDB.TABGAS
						WHERE RCOMP=5 AND RTITG='$tipo_gasto' AND RDETG='$detalle_gasto'
					";
		$qr_ocupa = $this->ejecutar($sq_ocupa);
		odbc_fetch_row($qr_ocupa);
		$swit = odbc_result($qr_ocupa, 1);
		if ($swit == 'L') {
			return FALSE;
		}
		return TRUE;
	}
	/*
	 * Verifica si cuenta verifica presupuesto
	 * @ $cia: Compañia
	 * @ $titgas: Titulo de Gasto
	 * @ $detgas: Detalle de Gasto
	 */
	public function verificaPresupuesto($cia,$titgas, $detgas) {
		$bandera = 'N';
		$sq_ocupa = "Select RSWIC From CTBLIBDB.TABGAS Where RCOMP='".$cia
		."' And RTITG=".$titgas." And RDETG=".$detgas;
		$qr_ocupa = $this->ejecutar($sq_ocupa);
		odbc_fetch_row($qr_ocupa);
		$swit = substr(odbc_result($qr_ocupa, 1),0,1);
		if ($swit == ' ') {
			$bandera = 'S';
		}
		return $bandera;
	}


	/*
	 * Manejo de Errores
	*/
	private function Error() {
		$this->_numero = odbc_error();
		$msg = odbc_errormsg();
		$msg = str_replace ( "'", " ", $msg );
		$this->_mensaje = '<b>HA OCURRIDO EL SIGUIENTE ERROR!</b>';
		$this->_mensaje .= '<br><b>Error No : </b> ' . $this->_numero;
		switch ($this->_numero) {
			case 'S0002' :
				$this->_mensaje .= '<br><b>Descripcion : </b>el archivo que busca no existe, verifique libreria.archivo.';
				break;
			case 'S0022' :
				$this->_mensaje .= '<br><b>Descripcion : </b>El nombre de columna no existe.';
				break;
			case 1265 :
				$this->_mensaje .= '<br><b>Descripcion : </b>Datos Truncados en Columna.';
				break;
			case 1054 :
				$this->_mensaje .= '<br><b>Descripcion : </b>Nombre de Columna Erroneo.';
				break;
			case 1364 :
				$this->_mensaje .= '<br><b>Descripcion : </b>No se ha Definido Valor para el Campo.';
				break;
			case 1064 :
				$this->_mensaje .= '<br><b>Descripcion : </b>Error de Sintaxis.';
				break;
			case 1406 :
				$this->_mensaje .= '<br><b>Descripcion : </b>Informacion Mayor al Tamaño del Campo.';
				break;
			case 1136 :
				$this->_mensaje .= '<br><b>Descripcion : </b>Tablas No Poseen el Mismo Numero de Columnas.';
				break;
			case 1044 :
				$this->_mensaje .= '<br><b>Descripcion : </b>Acceos Denegado.';
				break;
			case 1045 :
				$this->_mensaje .= '<br><b>Descripcion : </b>Credenciales no validas.';
				break;
			case 1046 :
				$this->_mensaje .= '<br><b>Descripcion : </b>No se ha seleccionado base de datos.';
				break;
			case 2002 :
				$this->_mensaje .= '<br><b>Descripcion : </b>El sevidor ha rechazado la conexion.';
				break;
			default :
				$this->_mensaje .= '<br><b>Descripcion : </b>No ha podido identificarse el error, informe a sistemas.';
				break;
		}
		$this->_mensaje .= '<hr>';
		$this->_mensaje .= '<br><h4>habilitado modo <sup class="fg-color-red text-warning">beta</sup></h4>';
		$this->_mensaje .= '<br><b>Informacion Tecnica : </b>'.$msg;
		$this->_mensaje .= '<br><b>Sentencia : </b>'.$this->_sql;
		return $this->_mensaje;
	}

	/*
	 * NOMBRE DEL MES
	* Parametros:
	* $mes: Numero de Mes
	*/
	function Qmes($mes){
		$meses = array(
				1=>"ENERO",
				2=>"FEBRERO",
				3=>"MARZO",
				4=>"ABRIL",
				5=>"MAYO",
				6=>"JUNIO",
				7=>"JULIO",
				8=>"AGOSTO",
				9=>"SEPTIEMBRE",
				10=>"OCTUBRE",
				11=>"NOVIEMBRE",
				12=>"DICIEMBRE"
		);
		$this->NMES = $meses[$mes];
		return $this->NMES;
	}

}

?>
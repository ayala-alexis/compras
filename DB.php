<?php
class DB {
	private $servidor;
	private $usuario;
	private $password;
	private $base_datos;
	private $link;
	private $stmt;
	private $array;
	private $tbl_tagasto;
	private $tbl_cecosto;
	private $tbl_categoria;
	private $tbl_sublinea;
	private $_numero;
	private $_mensaje;
	private $_sql;
	public $_last_insert_id;
	private static $_instance;
	private $_link;
	private $_link2;
	private $db_prov;
	private $db_prov2;
	private $tbl_restric;
	
	// La conexion es privada para evitar que el objeto pueda ser creado
	// mediante new
	private function __construct() {
		$this->setConexion ();
		$this->conectar ();
	}
	
	// Metodo para establecer los parametros de la conexion
	private function setConexion() {
		$conf = Configuracion::getInstance ();
		$this->servidor = $conf->getHostDB ();
		$this->base_datos = $conf->getBD ();
		$this->usuario = $conf->getUserDB ();
		$this->password = $conf->getPassDB ();
		$this->tbl_tagasto = $conf->getTbl_tagasto ();
		$this->tbl_cecosto = $conf->getTbl_cecosto ();
		$this->tbl_categoria = $conf->getTbl_categoria ();
		$this->tbl_sublinea = $conf->getTbl_sublinea ();
		$this->db_prov = $conf->getDbprov();
		$this->db_prov2 = $conf->getDbprov2();
		$this->tbl_restric = $conf->getTbl_restric();
	}
	
	// Evitamos el clonaje del objeto
	private function __clone() {
	}
	
	public function __wakeup() {
		// Para PHP 8.1 debe ser público
		$this->conectar();
	}
	
	// Funcion encargada de crear si es necesario, el objeto. Esta es la funcion
	// que debemos llamar desde fuera de la clase para instanciar el objeto
	public static function getInstance() {
		if (! (self::$_instance instanceof self)) {
			self::$_instance = new self ();
		}
		return self::$_instance;
	}
	
	// Realiza la conexion
	private function conectar() {
		$this->link = new mysqli($this->servidor, $this->usuario, $this->password, $this->base_datos);
		
		if ($this->link->connect_error) {
			throw new Exception("Error de conexión: " . $this->link->connect_error);
		}
		
		// Establecer charset UTF-8
		if (!$this->link->set_charset("utf8")) {
			throw new Exception("Error al establecer charset: " . $this->link->error);
		}
	}
	
	// Conecta usando la base de datos de O.C.
	public function conecta_OC(){
		$this->_link = new mysqli($this->servidor, $this->usuario, $this->password, $this->db_prov);
		
		if ($this->_link->connect_error) {
			throw new Exception("Error de conexión OC: " . $this->_link->connect_error);
		}
		
		// Establecer charset latin1 como en la versión original
		if (!$this->_link->set_charset("latin1")) {
			throw new Exception("Error al establecer charset latin1: " . $this->_link->error);
		}
	}
	
	// ejecutamos en base de O.C.
	public function ejecuta_OC($sqlp){
		$this->_sql = $sqlp;
		$stmtp = $this->_link->query($this->_sql);
		
		if (! $stmtp) {
			throw new Exception($this->Error());
		}
		return $stmtp;
	}
	
	// Desconecta base O.C.
	public function desconecta_OC() {
		if ($this->_link) {
			$this->_link->close();
		}
	}
	
	// Metodo para ejecutar una sentencia sql
	public function ejecutar($sql, $params = null) {
		$this->_sql = $sql;
		
		// Si hay parámetros, usar consulta preparada
		if ($params !== null && is_array($params)) {
			$stmt = $this->link->prepare($sql);
			if (!$stmt) {
				throw new Exception($this->Error());
			}
			
			// Determinar tipos de parámetros
			$types = '';
			$bind_params = [];
			
			foreach ($params as $param) {
				if (is_int($param)) {
					$types .= 'i';
				} elseif (is_float($param)) {
					$types .= 'd';
				} elseif (is_string($param)) {
					$types .= 's';
				} else {
					$types .= 'b'; // blob
				}
				$bind_params[] = $param;
			}
			
			// Vincular parámetros
			$stmt->bind_param($types, ...$bind_params);
			
			if (!$stmt->execute()) {
				throw new Exception($this->Error());
			}
			
			$this->stmt = $stmt->get_result();
			$this->_last_insert_id = $stmt->insert_id;
			$stmt->close();
		} else {
			// Consulta simple
			$this->stmt = $this->link->query($sql);
			
			if (!$this->stmt) {
				throw new Exception($this->Error());
			}
			
			$this->_last_insert_id = $this->link->insert_id;
		}
		
		return $this->stmt;
	}
	
	// Método auxiliar para compatibilidad con código antiguo
	public function ejecutarSimple($sql) {
		return $this->ejecutar($sql);
	}
	
	// Método para obtener una fila de resultados de la sentencia sql
	public function obtener($stmt, $fila = 0) {
		if ($stmt instanceof mysqli_result) {
			if ($fila > 0) {
				$stmt->data_seek($fila);
			}
			$this->array = $stmt->fetch_array(MYSQLI_BOTH);
		} else {
			// Si es un resultado de consulta preparada
			$this->array = $stmt->fetch_array();
		}
		
		if (!$this->array) {
			$this->array = array();
		}
		return $this->array;
	}
	
	// Método para obtener una fila como array asociativo
	public function obtenerAsoc($stmt, $fila = 0) {
		if ($stmt instanceof mysqli_result) {
			if ($fila > 0) {
				$stmt->data_seek($fila);
			}
			return $stmt->fetch_assoc();
		}
		return array();
	}
	
	// Método para contar filas
	public function numRows($stmt) {
		if ($stmt instanceof mysqli_result) {
			return $stmt->num_rows;
		}
		return 0;
	}
	
	// Método para escapar strings (reemplazo de mysqli_real_escape_string)
	public function escape($string) {
		return $this->link->real_escape_string($string);
	}
	
	// Retorna nombre de tabla de gasto
	public function getTabGas($emp, $tit, $det) {
		$sql = "Select * From " . $this->tbl_tagasto . " Where gas_tit_codigo = ? And gas_det_codigo = ?";
		$run = $this->ejecutar($sql, [$tit, $det]);
		$fila = $this->obtener($run, 0);
		return isset($fila[4]) ? $fila[4] : '';
	}
	
	// Retorna nombre Centro de Costo
	public function getCC($emp, $cc) {
		$sql = "Select * From " . $this->tbl_cecosto . " Where id_empresa = ? And cc_codigo = ?";
		$run = $this->ejecutar($sql, [$emp, $cc]);
		$fila = $this->obtener($run, 0);
		return isset($fila[3]) ? $fila[3] : '';
	}
	
	// Retorna nombre de Categoria
	public function getCat($idcat) {
		$sql = "Select * From " . $this->tbl_categoria . " Where id_categoria = ?";
		$run = $this->ejecutar($sql, [$idcat]);
		$fila = $this->obtener($run, 0);
		return isset($fila[1]) ? $fila[1] : '';
	}
	
	// Retorna nombre de Sublinea
	public function getSublinea($l, $sl) {
		$sql = "Select * From " . $this->tbl_sublinea . " Where sl_linea = ? And sl_sublinea = ?";
		$run = $this->ejecutar($sql, [$l, $sl]);
		$fila = $this->obtener($run, 0);
		return isset($fila[3]) ? $fila[3] : '';
	}
	
	// PRUEVAS - Conecta usando la base de datos de O.C. 2
	public function conecta_OC2(){
		$this->_link2 = new mysqli($this->servidor, $this->usuario, $this->password, $this->db_prov2);
		
		if ($this->_link2->connect_error) {
			throw new Exception("Error de conexión OC2: " . $this->_link2->connect_error);
		}
		
		if (!$this->_link2->set_charset("latin1")) {
			throw new Exception("Error al establecer charset latin1: " . $this->_link2->error);
		}
	}
	
	// ejecutamos en base de O.C. 2
	public function ejecuta_OC2($sqlp){
		$this->_sql = $sqlp;
		$stmtp = $this->_link2->query($this->_sql);
		
		if (! $stmtp) {
			throw new Exception($this->Error());
		}
		return $stmtp;
	}
	
	// Desconecta base O.C. 2
	public function desconecta_OC2() {
		if ($this->_link2) {
			$this->_link2->close();
		}
	}
	
	/*
	 * Manejo de Errores
	 */
	private function Error() {
		$this->_numero = $this->link->errno;
		$msg = $this->link->error;
		$msg = str_replace("'", " ", $msg);
		
		$this->_mensaje = '<b>HA OCURRIDO EL SIGUIENTE ERROR!</b>';
		$this->_mensaje .= '<br><b>Error No : </b> ' . $this->_numero;
		
		switch ($this->_numero) {
			case 1062 :
				$this->_mensaje .= '<br><b>Descripcion : </b>Ya existe ese registro.';
				break;
			case 1146 :
				$this->_mensaje .= '<br><b>Descripcion : </b>No Existe la Tabla.';
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
				$this->_mensaje .= '<br><b>Descripcion : </b>Acceso Denegado.';
				break;
			case 1045 :
				$this->_mensaje .= '<br><b>Descripcion : </b>Credenciales no validas.';
				break;
			case 1046 :
				$this->_mensaje .= '<br><b>Descripcion : </b>No se ha seleccionado base de datos.';
				break;
			case 2002 :
				$this->_mensaje .= '<br><b>Descripcion : </b>El servidor ha rechazado la conexion.';
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
	
	// Destructor para cerrar conexiones
	public function __destruct() {
		if ($this->link) {
			$this->link->close();
		}
		if ($this->_link) {
			$this->_link->close();
		}
		if ($this->_link2) {
			$this->_link2->close();
		}
	}
}
?>
<?php
class Configuracion {
	private $_servidor;
	private $_usuario;
	private $_password;
	private $_bd;
	private $_tbl_acc_modulo;
	private $_tbl_acceso;
	private $_tbl_bodega;
	private $_tbl_categoria;
	private $_tbl_cecosto;
	private $_tbl_empresa;
	private $_tbl_inventario;
	private $_tbl_lista;
	private $_tbl_presupuesto;
	private $_tbl_producto;
	private $_tbl_proveedor;
	private $_tbl_rol;
	private $_tbl_sublinea;
	private $_tbl_tagasto;
	private $_tbl_usuario;
	private $_tbl_modulo;
	private $_tbl_js_scripts;
	private $_tbl_kardex;
	private $_tbl_rol_user;
	private $_tbl_acc_emp_cc;
	private $_tbl_permiso_modulo;
	private $_tbl_acc_modulo_lista;
	private $_tbl_acc_und_vls;
	private $_tbl_prehsol;
	private $_tbl_predsol;
	private $_tbl_prehsol_stat;
	private $_tbl_estadistica;
	private $_tbl_prehreq;
	private $_tbl_predreq;
	private $_tbl_prehreq_stat;
	private $_tbl_und_vls;
	private $_tbl_autorizacion;
	private $_tbl_gestores;
	private $_tbl_gestion_categorias;
	private $_tbl_tipo_categoria;
	private $_server;
	private $_user;
	private $_pass;
	private $_cia;
	private $_tbl_restric;
	private $_dbprov;
	private $_dbprov2;
	public $_email_proveeduria;
	public $_email_proveeduria2;
	public $_email_proveeduria3;
	public $_email_proveeduriaJefe;

	/* O.C. */
	private $_tcatego; // Tabla Categorias
	private $_tccosto; // Tabla Centros de Costo
	private $_tmovs; // Tabla Movimientos
	private $_torden; // Tabla Ordenes
	private $_tprovee; // Tabla Proveedores
	private $_tobser; // Tabla Observaciones
	private $_estados; // Arreglo de estados
	private $_estadosSC; // Arreglo de estados solicitud de compra
	private $_estadosCI;
	private static $_instance;
	private function __construct() {
		require 'Parametros.php';
		$this->_servidor = $servidor;
		$this->_usuario = $usuario;
		$this->_password = $password;
		$this->_bd = $bd;
		$this->_tbl_acc_modulo = $tbl_acc_modulo;
		$this->_tbl_acceso = $tbl_acceso;
		$this->_tbl_bodega = $tbl_bodega;
		$this->_tbl_categoria = $tbl_categoria;
		$this->_tbl_cecosto = $tbl_cecosto;
		$this->_tbl_empresa = $tbl_empresa;
		$this->_tbl_inventario = $tbl_inventario;
		$this->_tbl_lista = $tbl_lista;
		$this->_tbl_presupuesto = $tbl_presupuesto;
		$this->_tbl_producto = $tbl_producto;
		$this->_tbl_proveedor = $tbl_proveedor;
		$this->_tbl_rol = $tbl_rol;
		$this->_tbl_sublinea = $tbl_sublinea;
		$this->_tbl_tagasto = $tbl_tagasto;
		$this->_tbl_usuario = $tbl_usuario;
		$this->_tbl_modulo = $tbl_modulo;
		$this->_tbl_js_scripts = $tbl_js_scripts;
		$this->_tbl_kardex = $tbl_kardex;
		$this->_tcatego = $tcatego;
		$this->_tccosto = $tccosto;
		$this->_tmovs = $tmovs;
		$this->_torden = $torden;
		$this->_tprovee = $tprovee;
		$this->_tobser = $tobser;
		$this->_tbl_rol_user = $tbl_rol_user;
		$this->_tbl_acc_emp_cc = $tbl_acc_emp_cc;
		$this->_tbl_permiso_modulo = $tbl_permiso_modulo;
		$this->_tbl_acc_modulo_lista = $tbl_acc_modulo_lista;
		$this->_tbl_acc_und_vls = $tbl_acc_und_vls;
		$this->_tbl_prehsol = $tbl_prehsol;
		$this->_tbl_predsol = $tbl_predsol;
		$this->_tbl_prehsol_stat = $tbl_prehsol_stat;
		$this->_tbl_estadistica = $tbl_estadistica;
		$this->_tbl_und_vls = $tbl_und_vls;
		$this->_tbl_autorizacion = $tbl_autorizacion;
		$this->_tbl_gestores = $tbl_gestores;
		$this->_tbl_gestion_categorias = $tbl_gestion_categorias;
		$this->_tbl_tipo_categoria = $tbl_tipo_categoria;
		$this->_estados = Array(
								'0' => 'CREADO',
								'1' => 'ENVIO AUTORIZACION',
								'2' => 'AUTORIZADO',
								'3' => 'RECOLECTADO PROVEEDURIA',
								'4' => 'EN ORDEN DE COMPRA',
								'5' => 'RECIBIDO DE PROVEEDOR',
								'6' => 'ENVIADO AL SOLICITANTE',
								'7' => 'RECIBIDO SOLICITANTE',
								'8' => 'NEGADO',
								'9' => 'PENDIENTE APROBACION D.E.',
								'10' => 'SIN PRESUPUESTO'
								);
		$this->_estadosSC = Array(
				'0' => 'CREADO',
				'1' => 'SOLICITADO',
				'2' => 'ENVIADO REVISION',
				'3' => 'ENVIADO GESTION',
				'4' => 'RECIBIDA EN PROVEEDURIA',//AUTORIZADO COMPRA
				'5' => 'EN ESPERA DE COTIZACION', // EN ESPERA DE COTIZACION + ANALISIS DE COTIZACION
				'6' => 'EN ORDEN DE COMPRA',
				'7' => 'RECIBIDO DE PROVEEDOR',
				'8' => 'ENVIADO AL SOLICITANTE',
				'9' => 'RECIBIDO SOLICITANTE',
				'10' => 'DESISTIDA',//NEGADO
				'11' => 'PENDIENTE APROBACION D.E.',
				'12' => 'PENDIENTE POR AJUSTE DE PRESUPUESTO', //SIN PRESUPUESTO
				'20' =>	'DEVUELTO POR CATEGORIA'
		);
		$this->_estadosCI = Array(
				'0' => 'CREADO',
				'1' => 'AUTORIZADO',
				'2' => 'EN PROCESO',
				'3' => 'IMPRESO',
				'4' => 'EN REVISION',
				'10' => 'DESISTIDA'
		);
		$this->_tbl_prehreq = $tbl_prehreq;
		$this->_tbl_predreq = $tbl_predreq;
		$this->_tbl_prehreq_stat = $tbl_prehreq_stat;
		$this->_server = $server;
		$this->_user = $user;
		$this->_pass = $pass;
		$this->_cia = $cia;
		$this->_tbl_restric = $trestric;
		$this->_dbprov = $db_prov;
		$this->_dbprov2 = $db_prov2;
		$this->_email_proveeduria = $email_proveeduria;
		$this->_email_proveeduria2 = $email_proveeduria2;
		$this->_email_proveeduria3 = $email_proveeduria3;
		$this->_email_proveeduriaJefe = $email_proveeduriaJefe;
	}
	private function __clone() {
	}
	public static function depurar($obj){
		echo "<pre>";
		print_r($obj);
		echo "</pre>";
	}
	public static function return_menu(){
		$return_menu = '';
		if(isset($_SESSION['menu_return'])){
			$return_menu = $_SESSION['menu_return'];
		}
		return $return_menu;
	}
	public static function getInstance() {
		if (! (self::$_instance instanceof self)) {
			self::$_instance = new self ();
		}
		return self::$_instance;
	}
	public function getUserDB() {
		$var = $this->_usuario;
		return $var;
	}
	public function getHostDB() {
		$var = $this->_servidor;
		return $var;
	}
	public function getPassDB() {
		$var = $this->_password;
		return $var;
	}
	public function getBD() {
		$var = $this->_bd;
		return $var;
	}
	public function getTbl_acc_modulo() {
		$var = $this->_tbl_acc_modulo;
		return $var;
	}
	public function getTbl_acceso() {
		$var = $this->_tbl_acceso;
		return $var;
	}
	public function getTbl_bodega() {
		$var = $this->_tbl_bodega;
		return $var;
	}
	public function getTbl_categoria() {
		$var = $this->_tbl_categoria;
		return $var;
	}
	public function getTbl_cecosto() {
		$var = $this->_tbl_cecosto;
		return $var;
	}
	public function getTbl_empresa() {
		$var = $this->_tbl_empresa;
		return $var;
	}
	public function getTbl_inventario() {
		$var = $this->_tbl_inventario;
		return $var;
	}
	public function getTbl_lista() {
		$var = $this->_tbl_lista;
		return $var;
	}
	public function getTbl_presupuesto() {
		$var = $this->_tbl_presupuesto;
		return $var;
	}
	public function getTbl_producto() {
		$var = $this->_tbl_producto;
		return $var;
	}
	public function getTbl_proveedor() {
		$var = $this->_tbl_proveedor;
		return $var;
	}
	public function getTbl_rol() {
		$var = $this->_tbl_rol;
		return $var;
	}
	public function getTbl_sublinea() {
		$var = $this->_tbl_sublinea;
		return $var;
	}
	public function getTbl_tagasto() {
		$var = $this->_tbl_tagasto;
		return $var;
	}
	public function getTbl_usuario() {
		$var = $this->_tbl_usuario;
		return $var;
	}
	public function getTbl_modulo() {
		$var = $this->_tbl_modulo;
		return $var;
	}
	public function getTbl_js_scripts() {
		$var = $this->_tbl_js_scripts;
		return $var;
	}

	/**
	 *
	 * @return the $_tbl_kardex
	 */
	public function getTbl_kardex() {
		return $this->_tbl_kardex;
	}

	/**
	 *
	 * @return the $_tcatego
	 */
	public function getTcatego() {
		return $this->_tcatego;
	}

	/**
	 *
	 * @return the $_tccosto
	 */
	public function getTccosto() {
		return $this->_tccosto;
	}

	/**
	 *
	 * @return the $_tmovs
	 */
	public function getTmovs() {
		return $this->_tmovs;
	}

	/**
	 *
	 * @return the $_torden
	 */
	public function getTorden() {
		return $this->_torden;
	}

	/**
	 *
	 * @return the $_tprovee
	 */
	public function getTprovee() {
		return $this->_tprovee;
	}

	/**
	 *
	 * @return the $_tobser
	 */
	public function getTobser() {
		return $this->_tobser;
	}
	/**
	 * @return the $_tbl_rol_user
	 */
	public function getTbl_rol_user() {
		return $this->_tbl_rol_user;
	}
	/**
	 * @return the $_tbl_acc_emp_cc
	 */
	public function getTbl_acc_emp_cc() {
		return $this->_tbl_acc_emp_cc;
	}

	/**
	 * @return the $_tbl_permiso_modulo
	 */
	public function getTbl_permiso_modulo() {
		return $this->_tbl_permiso_modulo;
	}
	/**
	 * @return the $_tbl_acc_modulo_lista
	 */
	public function getTbl_acc_modulo_lista() {
		return $this->_tbl_acc_modulo_lista;
	}
	/**
	 * @return the $_tbl_acc_und_vls
	 */
	public function getTbl_acc_und_vls() {
		return $this->_tbl_acc_und_vls;
	}
	/**
	 * @return the $_tbl_prehsol
	 */
	public function getTbl_prehsol() {
		return $this->_tbl_prehsol;
	}
	/**
	 * @return the $_tbl_predsol
	 */
	public function getTbl_predsol() {
		return $this->_tbl_predsol;
	}
	/*
	 * Retorna descripcion del estado de la solicitud
	 */
	public function getEstado($estado){
		return $this->_estados[$estado];
	}
	/**
	 * @return the $_tbl_prehsol_stat
	 */
	public function getTbl_prehsol_stat() {
		return $this->_tbl_prehsol_stat;
	}

	/**
	 * @return the $_tbl_estadistica
	 */
	public function getTbl_estadistica() {
		return $this->_tbl_estadistica;
	}
	/**
	 * @return the $_tbl_prehreq
	 */
	public function getTbl_prehreq() {
		return $this->_tbl_prehreq;
	}

	/**
	 * @return the $_tbl_predreq
	 */
	public function getTbl_predreq() {
		return $this->_tbl_predreq;
	}

	/**
	 * @return the $_tbl_prehreq_stat
	 */
	public function getTbl_prehreq_stat() {
		return $this->_tbl_prehreq_stat;
	}

	/*
	 * retorna un listado de todos los estados
	 */
	public function getEstados(){
		return $this->_estados;
	}

	/**
	 * @return the $_tbl_und_vls
	 */
	public function getTbl_und_vls() {
		return $this->_tbl_und_vls;
	}

	/**
	 * @return the $_server
	 */
	public function getServer() {
		return $this->_server;
	}

	/**
	 * @return the $_user
	 */
	public function getUser() {
		return $this->_user;
	}

	/**
	 * @return the $_pass
	 */
	public function getPass() {
		return $this->_pass;
	}
	/**
	 * @return the $_cia
	 */
	public function getCia() {
		return $this->_cia;
	}
	/**
	 * @return the $_tbl_restric
	 */
	public function getTbl_restric() {
		return $this->_tbl_restric;
	}
	/**
	 * @return the $_tbl_gestores
	 */
	public function getTbl_gestores() {
		return $this->_tbl_gestores;
	}

	/**
	 * @return the $_dbprov
	 */
	public function getDbprov() {
		return $this->_dbprov;
	}
	/**
	 * @return the $_dbprov2
	 */
	public function getDbprov2() {
		return $this->_dbprov2;
	}
	/**
	 * @return the $_tbl_autorizacion
	 */
	public function getTbl_autorizacion() {
		return $this->_tbl_autorizacion;
	}
	
	/*
	 * retorna un listado de todos los estados
	*/
	public function getEstadosSC(){
		return $this->_estadosSC;
	}
	/*
	 * retorna un listado de todos los estados
	*/
	public function getEstadosCI(){
		return $this->_estadosCI;
	}
	/*
	 * Retorna descripcion del estado de la solicitud
	*/
	public function getEstadoSC($estado){
		return $this->_estadosSC[$estado];
	}
	/*
	 * Retorna descripcion del estado de la solicitud
	*/
	public function getEstadoCI($estado){
		return $this->_estadosCI[$estado];
	}
	/**
	 * @return the $_tbl_gestion_categorias
	 */
	public function getTbl_gestion_categorias() {
		return $this->_tbl_gestion_categorias;
	}
	/**
	 * @return the $_tbl_tipo_categoria
	 */
	public function getTbl_tipo_categoria() {
		return $this->_tbl_tipo_categoria;
	}
	function enviarEmailStream($jsonData) {
		// Convertir datos a JSON
		$jsonString = json_encode($jsonData);
		
		// Enviar la solicitud
		return file_get_contents(
				"http://192.168.40.6/Email/Email/Enviar", 
				false, 
				stream_context_create(array(
					'http' => array(
						'method' => 'POST',
						'header' => "Content-Type: application/json\r\n" .
								   "Content-Length: " . strlen($jsonString) . "\r\n",
						'content' => $jsonString,
						'timeout' => 30
					)
				))
			);
		
	}

}
?>
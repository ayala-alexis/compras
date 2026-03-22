<?php
/**
 *
 * @author wserpas
 * @copyright IMPRESSA, S.A. de C.V.
 * @version 1.0 
 *        
 */
class Usuario {
	private $db;
	private $conf;
	private static $_instance;
	/**
	 * Constructor de Usuario
	 */
	function __construct() {
		// TODO - Insert your code here
		$this->db = DB::getInstance ();
		$this->conf = Configuracion::getInstance ();
	}
	private function __clone() {
	}
	public static function getInstance() {
		if (! (self::$_instance instanceof self)) {
			self::$_instance = new self ();
		}
		return self::$_instance;
	}
	/*
	 * Retorna la informacion del usuario
	 */
	function infoUsuario($usuario) {
		$sql = 'Select * From ' . $this->conf->getTbl_usuario () . " Where usr_usuario = '" . $usuario . "'";
		$resultado = $this->db->ejecutar ( $sql );
		return $this->db->obtener ( $resultado, 0 );
	}
	/*
	 * Modulos a los que tiene acceso el usuario
	 */
	function modulosUsuario($usuario) {
		$sql = 'Select * From ' . $this->conf->getTbl_acc_modulo () . " Where id_usuario = '" . $usuario . "' Order By mod_categoria, mod_categoria2";
		$resultado = $this->db->ejecutar ( $sql );
		return $resultado;
	}
	/*
	 * Retorna los accesos del usuario
	 */
	function accesosUsuario($usuario) {
		$sql = 'Select * From ' . $this->conf->getTbl_acceso () . " Where usr_usuario = '" . $usuario . "'";
		$resultado = $this->db->ejecutar ( $sql );
		return $this->db->obtener ( $resultado, 0 );
	}
	/*
	 * Retorna si tiene acceso para autorizar categorias
	*/
	function accesosCategorias($id) {
		$sql = 'Select * From ' . $this->conf->getTbl_gestion_categorias() . " Where id_usuario = " . $id;
		$resultado = $this->db->ejecutar ( $sql );
		return $this->db->obtener ( $resultado, 0 );
	}
	/*
	 * Retorna si tiene acceso para autorizar gestiones
	*/
	function accesosGestor($id) {
		$sql = 'Select * From ' . $this->conf->getTbl_gestores() . " Where id_usuario = " . $id;
		$resultado = $this->db->ejecutar ( $sql );
		return $this->db->obtener ( $resultado, 0 );
	}
}

?>
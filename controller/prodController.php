<?php
session_start();
// Mantenimiento de Proveedores
function inicio() {
	include_once 'view/mttoProducto.php';
}
// Mantenimiento de Categorias
function slprod() {
	include_once 'view/mttoSLProd.php';
}
// Obtenemos los proveedore segun categoria seleccionada
function loadProvs() {
	$idcat = $_REQUEST ['idcat'];
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select id_proveedor, prov_nombre From ".$conf->getTbl_proveedor()." Where id_categoria = '".$idcat."'";
		try {
			$query = $db->ejecutar ( $sql );
			while ( $row = mysqli_fetch_assoc ( $query ) ) {
				$array [] = $row;
			}
			echo json_encode ( $array );
		} catch ( Exception $e1 ) {
			echo json_encode ( $e1->getMessage () );
		}
	} catch ( Exception $e ) {
		echo json_encode ( $e->getMessage () );
	}
}
// Analisis de inventario
function anal(){
	$slinea = $_REQUEST['id'];
	include_once 'model/SQLgenerales.php';
	include_once 'model/invModel.php';
	$filas = listarAnal($slinea);
	include_once 'view/prod/anal.php';
}
?>
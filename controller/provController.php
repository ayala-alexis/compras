<?php
$_SESSION ['ie'] = $_GET ['ie'];
// Mantenimiento de Proveedores
function inicio() {
	include_once 'view/prov/mttoProveedor.php';
}
// Mantenimiento de Categorias
function catprov() {
	include_once 'view/prov/mttoCatProv.php';
}
// Lista lista de precios por proveedor
function lista() {
	include_once 'model/SQLgenerales.php';
	include_once 'model/provModel.php';
	$filas = listar($_GET['id']);
	$sls = listaSublineas();
	include_once 'view/prov/lista/lista.php';
}
// Modificar Lista de precios
function modificar() {
	include 'model/provModel.php';
	$row2 = nomProv();
	include 'view/modificarListProv.php';
}
// Adicionar Proveedor
function adicionar() {
	include 'model/provModel.php';
	if ($_POST) {
		addProv();
		header('location: ?c=prov&a=lista&id='.$_POST['id_proveedor']);
		die();
	} else {
		$row2 = nomProv();
		include 'view/adicionarListProv.php';
	}

}
function eliminar() {
	include 'model/provModel.php';
	rmProv ();
	$filas = listar ();
	include 'view/listadoProveedor.php';
}

function buscar() {
	include 'model/provModel.php';
	$filas = busqueda ();
	include 'view/listadoProveedor.php';
}
/*
 * listarCcUsuario
*/
function listarProductoC(){
	require_once 'model/provModel.php';
	echo json_encode(listarProducto());
}
/*
 * Cambiar Codigo Tabla O.C.
*/
function cambio() {
	include 'model/provModel.php';
	$provs = Proveedores_Cambiar();
	include 'view/prov/cambio.php';
}
/*
 * Cambiar Codigo Tabla SICS
*/
function cambios() {
	include 'model/provModel.php';
	$provs = Proveedores_Cambiar();
	include 'view/prov/cambio_sics.php';
}
/*
 * Cambiar Codigo Tabla O.C.
*/
function cambioo() {
	$conf = Configuracion::getInstance ();
	/* Nos vamos a la O.C. */
	$host_oc = $conf->getHostDB();
	$user_oc = $conf->getUserDB();
	$pass_oc =$conf->getPassDB();
	$bd_oc = $conf->getDbprov();
	// Retornamos todos los proveedores
	$link = mysqli_connect ( $host_oc, $user_oc, $pass_oc, true );
	mysqli_select_db ( $bd_oc, $link );
	$sql = "Select * From provee06 Order by CODIGO";
	$result = mysqli_query ( $sql, $link );
	$provs = array();
	while($row = mysqli_fetch_array($result)){ 
		$provs[] = $row;
	}
	include 'view/prov/cambio_oc.php';
}
/*
 * Retorna las listas de precio vigentes por codigo
*/
function listasVigentes(){
	require_once 'model/provModel.php';
	echo json_encode(listaVigente());
}
/*
 * Retorna las listas de precio vigentes por codigo
*/
function listasVigentesMayoreo(){
	require_once 'model/provModel.php';
	echo json_encode(listaVigenteMayoreo());
}
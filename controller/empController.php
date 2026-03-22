<?php
$_SESSION ['ie'] = $_GET ['ie'];
// Mantenimiento de Empresa
function inicio() {
	/*
	include_once 'model/empModel.php';
	$emps = ListaEmpresas();
	if(isset($_GET['es']) && $_GET['es'] != '') {
		$infos = infoEmpresa($_GET['es']);
		$ccs = centroscosto($_GET['es']);
		$bodegas = bodegas($_GET['es']);
		$presupuestos = presupuestos($_GET['es']);
	}
	include_once 'view/emp/inicio.php';
	*/
	include_once 'view/mttoEmpresa.php';
}
// Mantenimiento de Centro de Costo
function cc() {
	include_once 'view/mttoCeCosto.php';
}
// Mantenimiento de Presupuesto
function pre() {
	include_once 'view/mttoPresupuesto.php';
}
// Mantenimiento de Tablas de Gasto
function tab() {
	include_once 'view/mttoTabGas.php';
}
// Mantenimiento de Tablas de Gasto
function bod() {
	include_once 'view/mttoBodegas.php';
}
/*
 * listarCcUsuario
*/
function listarAuth(){
	require_once 'model/empModel.php';
	echo json_encode(listaAuth());
}
?>
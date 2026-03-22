<?php
include_once '../Configuracion.php';
include_once '../DB.php';
$req = array_merge($_GET, $_POST);
$req["res"] = 0;
try {
	$db = DB::getInstance();
	$conf = Configuracion::getInstance();

	$db->conecta_OC();
	
	$tabla1 = 'movs06';
	$tabla2 = 'orden06';
	// Encabezados
	$sql_up = "Update ".$tabla1
		." Set CODPROVEE = ".$req["codig_n"]
		." Where CODPROVEE = '".$req["codig_a"]."'";
	$db->ejecuta_OC($sql_up);
	
	// Movimientos
	$sql_up = "Update ".$tabla2
		." Set CODPROVEE = ".$req["codig_n"]
		." Where CODPROVEE = '".$req["codig_a"]."'";
	$db->ejecuta_OC($sql_up);
	
	$req["res"] = 1;
		
	$db->desconecta_OC();
	// Retornamos
	header('Content-type: application/json');
	echo json_encode($req);
} catch (Exception $e) {
	$req["res"] = $e;
	header('Content-type: application/json');
	echo json_encode($req);
}
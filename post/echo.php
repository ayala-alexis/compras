<?php
include_once '../Configuracion.php';
include_once '../DB.php';
$req = array_merge($_GET, $_POST);
$req["res"] = 0;
try {
	$db = DB::getInstance();
	$conf = Configuracion::getInstance();

	$db->conecta_OC();
	$tabla = "provee".$req["id_emp"];
	$sql = "Select * From ".$tabla
		." Where CODIGO = '".$req["codig_a"]."'";
	$result = $db->ejecuta_OC($sql);
	// Lo encontro, nos quedamos hasta aqui
	if(mysqli_num_rows($result) > 0) {
		$sql_up = "Update ".$tabla
			." Set NVOCOD = ".$req["codig_n"]
			." Where CODIGO = '".$req["codig_a"]."'";
		$db->ejecuta_OC($sql_up);
		$req["res"] = 1;
	} else {
		// Busquemoslo con relleno de "0"
		$codigo = str_pad($req["codig_a"], 6, "0", STR_PAD_LEFT);
		$sql = "Select * From ".$tabla
			." Where CODIGO = '".$codigo."'";
		$result = $db->ejecuta_OC($sql);
		if(mysqli_num_rows($result) > 0) {
			$sql_up = "Update ".$tabla
				." Set NVOCOD = ".$req["codig_n"]
				." Where CODIGO = '".$codigo."'";
			$db->ejecuta_OC($sql_up);
			$req["res"] = 1;
		}
	}
	$db->desconecta_OC();
	// Retornamos
	header('Content-type: application/json');
	echo json_encode($req);
} catch (Exception $e) {
	$req["res"] = $e;
	header('Content-type: application/json');
	echo json_encode($req);
}
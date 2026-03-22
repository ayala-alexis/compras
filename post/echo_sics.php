<?php
include_once '../Configuracion.php';
include_once '../DB.php';
$req = array_merge($_GET, $_POST);
$req["res"] = 0;
try {
	$db = DB::getInstance();
	$conf = Configuracion::getInstance();
	$sql = "Select * From ".$conf->getTbl_proveedor()
		." Where prov_codigo = '".$req["codig_a"]."' and prov_origen = '".$req['id_emp']."'";
	$result = $db->ejecutar($sql);
	// Lo encontro, nos quedamos hasta aqui
	if(mysqli_num_rows($result) > 0) {
		$sql_up = "Update ".$conf->getTbl_proveedor()
			." Set prov_nvocod = ".$req["codig_n"]
			." Where prov_codigo = '".$req["codig_a"]."' and prov_origen = '".$req['id_emp']."'";
		$db->ejecutar($sql_up);
		$req["res"] = 1;
	} else {
		// Busquemoslo con relleno de "0"
		$codigo = str_pad($req["codig_a"], 6, "0", STR_PAD_LEFT);
		$sql = "Select * From ".$conf->getTbl_proveedor()
		." Where prov_codigo = '".$codigo."' and prov_origen = '".$req['id_emp']."'";
		$result = $db->ejecutar($sql);
		if(mysqli_num_rows($result) > 0) {
			$sql_up = "Update ".$conf->getTbl_proveedor()
			." Set prov_nvocod = ".$req["codig_n"]
			." Where prov_codigo = '".$codigo."' and prov_origen = '".$req['id_emp']."'";
			$db->ejecutar($sql_up);
			$req["res"] = 1;
		}
	}
	header('Content-type: application/json');
	echo json_encode($req);
} catch (Exception $e) {
	$req["res"] = $e;
	header('Content-type: application/json');
	echo json_encode($req);
}
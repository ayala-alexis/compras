<?php
$user = strtoupper ( $_POST ['User'] );
$pass = strtoupper ( $_POST ['Passwd'] );
require_once "Configuracion.php";
require_once "DB.php";
global $db;
$rtn = "error";
try {
	$db = DB::getInstance();
	$sql = "Update usuario Set "
		." usr_password = '" . $pass . "'"
		." Where usr_usuario = '" . $user . "'";
	try {
		$db->ejecutar($sql);
		$rtn = 'success';
	} catch (Exception $e) {
		$rtn = $e->getMessage();
	}
} catch (Exception $e) {
	$rtn = $e->getMessage();
}
echo json_encode($rtn);
?>
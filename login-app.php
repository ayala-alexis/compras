<?php
$user = strtoupper ( $_POST ['usr'] );
//$user = strtoupper("wserpas");
//$pass = strtoupper("wserpas");
require_once "Configuracion.php";
require_once "DB.php";
global $db;
$rtn = "error";
try {
	$db = DB::getInstance ();
	$sql = "Select * From usuario Where (usr_usuario = '" . $user . "' or usr_ad = '" . $user . "')";
	$consulta = $db->ejecutar ( $sql );
	if (mysqli_num_rows ( $consulta ) > 0) {
		try {
			$row = mysqli_fetch_array($consulta);
			if ($row ['usr_estado'] == 'A') {
				$rtn = 'success';
				session_start ();
				$_SESSION['u'] = $row['usr_usuario'];
				$_SESSION['n'] = $row['usr_nombre'];
				$_SESSION['i'] = $row['id_usuario'];
				$_SESSION['req'] = $row['usr_req'];
				$_SESSION['sol'] = $row['usr_sol'];
				$_SESSION['oc'] = $row['usr_oc'];
				$_SESSION['acc_app'] = 1;
				header('Location: index.php?c=login&a=ingreso');

			} else {
				echo 'Error de identificación.';
			}
		} catch ( Exception $e ) {
			$rtn = $e->getMessage ();
		}
	}
} catch ( Exception $e ) {
	$rtn = $e->getMessage ();
}
?>
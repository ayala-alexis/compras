<?php
session_start();
try {
	require '../DB.php';
	require '../Configuracion.php';
	$form = $_POST['datos'];
	$Conf = Configuracion::getInstance();
	$DB = DB::getInstance();
	try {
		switch ($form['t']) {
			// Tabla requisiciones
			case 'prehreq':
				$sql = "Select a.*, b.cc_descripcion From ".$Conf->getTbl_prehreq()." a"
					." join ".$Conf->getTbl_cecosto()." b"
					." On b.id_empresa = a.id_empresa And"
					." b.id_cc = a.id_cc"
					." Where "
					."a.id_empresa = ".$form['empresa'];
				$run = $DB->ejecutar($sql);
				if (mysqli_num_rows($run) > 0) {
					while ( $row = mysqli_fetch_array ( $run ) ) {
						$row['nestado'] = $Conf->getEstado($row['prehreq_estado']);
						$array[] = $row;
					}
				} else {
					$array[] = "";
				}
				$result = $array;
				break;
				// no hay tabla
			default:
				$result = 'errorazo';
				break;
		}
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
} catch (Exception $e1) {
	$result = $e1->getMessage();
}
echo json_encode($result);
?>
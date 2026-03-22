<?php
//print_r($_REQUEST);
include_once '../../Configuracion.php';
$conf = Configuracion::getInstance();
//echo $conf->_email_proveeduria;
$link = mysqli_connect("192.168.43.120", "root", "my47gmc") or die(mysqli_error());
mysqli_select_db("sicys", $link) or die(mysqli_error($link));
if(is_array($_REQUEST)) {
	foreach ($_REQUEST as $k => $v){
		$id =  $k;
	}
	
	$usuarios_distribuidos = array();
	// Obtenemos detalle del encabezado de la solicitud
	$sql = "Select * From prehsol Where id_prehsol = ".$id;
	$run = mysqli_query($sql, $link) or die(mysqli_error($link));
	$row = mysqli_fetch_array($run);
	
	// Verificamos si tiene aisgnada una categoria
	if($row['id_categoria'] > 0) {
		// Buscamos si tiene autorizacion por categoria
		$sql_estado_cat = "Select gi.id_auto_categoria, gi.id_usuario, u.usr_email, u.usr_nombre From"
				." gestion_categorias gi Join "
				." usuario u On"
				." u.id_usuario = gi.id_usuario"
				." Where id_categoria = ".$row['id_categoria'];
		$run_estado_cat = mysqli_query($sql_estado_cat, $link);		
		if(mysqli_num_rows($run_estado_cat) > 0) { // Tiene gestor de categoria
			while ($row_user = mysqli_fetch_array($run_estado_cat)){
				if(!empty($row_user[2])) {
					$usuarios_distribuidos[] = $row_user[3];
				}
			}
			print_r($usuarios_distribuidos);
			$cotis = 0;
			if(!empty($row['prehsol_coti1'])) {
				$cotis = 1;
			}
			if(!empty($row['prehsol_coti2'])) {
				$cotis = 1;
			}
			if(!empty($row['prehsol_coti3'])) {
				$cotis = 1;
			}
			$body = 'Se ha enviado la solicitud #'.$row['prehsol_numero_sol']. " para su revision segun categoria.<br>";
			$body .= 'A los siguientes destinatarios:<br>';
			foreach ($usuarios_distribuidos as $ua){
				$body .= $ua.'<br>';
			}
			if($cotis > 0){
				$body .= '<br>Favor revisar las cotizaciones adjuntas';
			}
			echo $body;
		}
	} else {
		// Verificamos si tiene un gestor definido para enviarse la autorizacion
		$sql_user = "Select g.id_usuario, u.usr_nombre, u.usr_email From gestion_usuarios g"
			." Join usuario u"
			." On u.id_usuario = g.id_usuario"
			." Where g.id_cc = ".$row['id_cc'];
		$run_user = mysqli_query($sql_user, $link) or die(mysqli_error($link));
		// Si tiene gestor asignado
		if(mysqli_num_rows($run_user) > 0) {
			// Enviamos a todos los gestores
			while ($row_user = mysqli_fetch_array($run_user)){
				if(!empty($row_user[2])) {
					$usuarios_distribuidos[] = $row_user[3];
				}
			}
			print_r($usuarios_distribuidos);
			$cotis = 0;
			if(!empty($row['prehsol_coti1'])) {
				$cotis = 1;
			}
			if(!empty($row['prehsol_coti2'])) {
				$cotis = 1;
			}
			if(!empty($row['prehsol_coti3'])) {
				$cotis = 1;
			}
			$body = 'Se ha enviodo la solicitud #'.$row['prehsol_numero_sol']. " para su gestion.";
			$body .= 'A los siguientes destinatarios:<br>';
			foreach ($usuarios_distribuidos as $ua){
				$body .= $ua.'<br>';
			}
			if($cotis > 0){
				$body .= '<br>Favor revisar las cotizaciones adjuntas';
			}
			echo $body;
		} else {
			echo "directo a proveeduria";
		}
	}
} else {
	echo 'No se ha enviado nada';
}
mysqli_close($link);
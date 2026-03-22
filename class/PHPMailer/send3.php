<?php
include_once '../../Configuracion.php';
$conf = Configuracion::getInstance();
//print_r($_REQUEST);
$link = mysqli_connect("192.168.43.120", "root", "my47gmc") or die(mysqli_error());
//$link = mysqli_connect("localhost", "root", "") or die(mysqli_error());
mysqli_select_db("sicys", $link) or die(mysqli_error($link));
if(is_array($_REQUEST)) {
	foreach ($_REQUEST as $k => $v){
		$id =  $k;
	}
	$sql = "Select * From prehsol Where id_prehsol = ".$id;
	$run = mysqli_query($sql, $link) or die(mysqli_error($link));
	$row = mysqli_fetch_array($run);
	
	$sql_cc = "Select c.cc_codigo, c.cc_descripcion, e.emp_nombre From cecosto c Join empresa e On c.id_empresa = e.id_empresa Where c.id_empresa = ".$row['id_empresa']. " and c.id_cc = ".$row['id_cc'];
	$run_cc = mysqli_query($sql_cc, $link) or die(mysqli_error());
	$row_cc = mysqli_fetch_array($run_cc);

	// Encabezado
	$_sql_up = "Update prehsol Set prehsol_estado = 12 where id_prehsol = ".$id;
	mysqli_query($_sql_up, $link);
	// Detalle
	$_sql_up2 = "Update predsol Set predsol_estado = 12 where id_prehsol = ".$id;
	mysqli_query($_sql_up2, $link);
	
	// Ponemos el estado de negado
	$sql_st = "Insert Into prehsol_stat Set "
		."id_prehsol = ".$id.", "
		."prehsol_stat = 12, "
		."prehsol_stat_desc = 'PENDIENTE POR AJUSTE DE PRESUPUESTO', "
		."prehsol_stat_fecha = '".date("Y-m-d")."', "
		."prehsol_stat_hora = '".date("H:i:s")."', "
		."prehsol_stat_usuario = '".$_SESSION['u']."'";
	mysqli_query($sql_st, $link);
	
	//print_r($row);
	
	require 'PHPMailerAutoload.php';
	$mail = new PHPMailer;
	
	$mail->SMTPDebug = 0;                               // Enable verbose debug output
	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = '192.168.43.130';
	$mail->setFrom('solicitud@impressa.com', 'Solicitud de Compra');
	$mail->isHTML(true);                                  // Set email format to HTML
	
	$sql_user = "Select a.id_usuario, u.usr_nombre, u.usr_email From acc_emp_cc a"
		." Join usuario u"
		." On u.id_usuario = a.id_usuario"
		." Where a.id_empresa = ".$row['id_empresa']
		." and a.id_cc = ".$row['id_cc']
		." and u.id_rol = 999999995";
	$run_user = mysqli_query($sql_user, $link) or die(mysqli_error($link));
	while ($row_user = mysqli_fetch_array($run_user)){
		if(!empty($row_user[2])) {
			$mail->addAddress($row_user[2], $row_user[1]);     // Add a recipient
		}
		//print ($row_user[0] .'=>'. $row_user[1].'=>'. $row_user[2].'<br>');
	}
	
	$mail->addAddress($conf->_email_proveeduria, "Gestor de Compra");     // Add a recipient
	$mail->addAddress($conf->_email_proveeduria2, "Gestor de Compra");     // Add a recipient
	$mail->addAddress($conf->_email_proveeduria3, "Analista de Compras");     // Add a recipient
	$mail->addAddress($conf->_email_proveeduriaJefe, "Gestor de Compra");     // Add a recipient
	
	$body = 'La solicitud #'.$row['prehsol_numero_sol']
		. "<br> de (". $row_cc["cc_codigo"] .")".$row_cc["cc_descripcion"]." de ".$row_cc["emp_nombre"]
		. "<br> presenta las siguientes inconsistencias de presupuesto.";
	$body .= '<br>Favor revisar y notificar al gestor de compra.';
	$body .= $row['prehsol_verificacion2'];
	/*$body .= '<br>Favor revisar y notificar al gestor de compra.';
	$body .= $row['prehsol_verificacion'];*/
	$mail->Subject = 'Pendiente por Ajuste de Presupuesto Solicitud #'.$row['prehsol_numero_sol'];
	$mail->Body    = $body;
	//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
	if(!$mail->send()) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;
	} else {
		echo '<br>Message has been sent';
		header('Location: http://192.168.40.6/sics/?c=solc&a=gestor&id=5');
		die();
	}
	
	
} else {
	echo 'No se ha enviado nada';
}
mysqli_close($link);
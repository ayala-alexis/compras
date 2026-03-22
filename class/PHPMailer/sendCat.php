<?php
//print_r($_REQUEST);
session_start();
include_once '../../Configuracion.php';
$conf = Configuracion::getInstance();
//echo $conf->_email_proveeduria;
$link = mysqli_connect($conf->getHostDB(), $conf->getUserDB(), $conf->getPassDB()) or die(mysqli_error());
//$link = mysqli_connect("localhost", "root", "") or die(mysqli_error());
mysqli_select_db($conf->getBD(), $link) or die(mysqli_error($link));
if(is_array($_REQUEST)) {
	foreach ($_REQUEST as $k => $v){
		$id =  $k;
	}
	
	$usuarios_distribuidos = array();
	// Obtenemos detalle del encabezado de la solicitud
	$sql = "Select * From prehsol Where id_prehsol = ".$id;
	$run = mysqli_query($sql, $link) or die(mysqli_error($link));
	$row = mysqli_fetch_array($run);

	echo "********1**********<br/>";
	$conf->depurar($row);
	echo "******************<br/><br/><br/>";
	//print_r($row);
	
	$sql_cc = "Select c.cc_codigo, c.cc_descripcion, e.emp_nombre From cecosto c Join empresa e On c.id_empresa = e.id_empresa Where c.id_empresa = ".$row['id_empresa']. " and c.id_cc = ".$row['id_cc'];
	$run_cc = mysqli_query($sql_cc, $link) or die(mysqli_error());
	$row_cc = mysqli_fetch_array($run_cc);

	echo "********2**********<br/>";
	$conf->depurar($row_cc);
	echo "******************<br/><br/><br/>";
	
	require 'PHPMailerAutoload.php';
	$mail = new PHPMailer;
	$mail->SMTPDebug = 0;                               // Enable verbose debug output
	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = '192.168.43.130';
	$mail->setFrom('solicitud@impressa.com', 'Solicitud de Compra');
	$mail->isHTML(true);                                  // Set email format to HTML
	
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
			$conf->depurar($row_user);
			if(!empty($row_user[2])) {
				$mail->addAddress($row_user[2], $row_user[1]);     // Add a recipient
				$usuarios_distribuidos[] = $row_user[1];
			}
		}
		$cotis = 0;
		if(!empty($row['prehsol_coti1'])) {
			$mail->addAttachment($row['prehsol_coti1']);         // Add attachments
			$cotis = 1;
		}
		if(!empty($row['prehsol_coti2'])) {
			$mail->addAttachment($row['prehsol_coti2']);    // Optional name
			$cotis = 1;
		}
		if(!empty($row['prehsol_coti3'])) {
			$mail->addAttachment($row['prehsol_coti3']);    // Optional name
			$cotis = 1;
		}
		$body = 'Se ha enviado la solicitud #'.$row['prehsol_numero_sol']. " para su gestion por convergencia.";
		$body .= "<br> desde (". $row_cc["cc_codigo"] .")".$row_cc["cc_descripcion"]." de ".$row_cc["emp_nombre"];
		$body .= '<br>A los siguientes destinatarios:<br>';
		foreach ($usuarios_distribuidos as $ua){
			$body .= $ua.'<br>';
		}
		if($cotis > 0){
			$body .= '<br>Favor revisar las cotizaciones adjuntas';
		}
		$mail->Subject = 'Solicitud para gestion #'.$row['prehsol_numero_sol'];
		$mail->Body    = $body;
		if(!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			echo '<br>Message has been sent';
		}
	} else {
		$mail->addAddress($conf->_email_proveeduria, "Gestor de Compra");     // Add a recipient
		$cotis = 0;
		if(!empty($row['prehsol_coti1'])) {
			$mail->addAttachment($row['prehsol_coti1']);         // Add attachments
			$cotis = 1;
		}
		if(!empty($row['prehsol_coti2'])) {
			$mail->addAttachment($row['prehsol_coti2']);    // Optional name
			$cotis = 1;
		}
		if(!empty($row['prehsol_coti3'])) {
			$mail->addAttachment($row['prehsol_coti3']);    // Optional name
			$cotis = 1;
		}
		$body = 'Se ha enviado la solicitud #'.$row['prehsol_numero_sol']. " para su gestion de compra.";
		$body .= "<br> desde (". $row_cc["cc_codigo"] .")".$row_cc["cc_descripcion"]." de ".$row_cc["emp_nombre"];
		if($cotis > 0){
			$body .= '<br>Favor revisar las cotizaciones adjuntas';
		}
		$mail->Subject = 'Solicitud para gestion de compra #'.$row['prehsol_numero_sol'];
		$mail->Body    = $body;
		if(!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			echo '<br>Message has been sent';
		}
	}
	// Todo se envio bien
	//header('Location: http://192.168.40.6/sics/?c=solc&a=gescat&es='.$row['id_categoria']);
	if($conf->return_menu()!=''){
		header('Location: '.$conf->return_menu());
		die();
	}else{
		header('Location: http://192.168.40.6/sics/?c=solc&a=gescat&es='.$row['id_categoria']);
		die();
	}
} else {
	echo 'No se ha enviado nada';
}
mysqli_close($link);
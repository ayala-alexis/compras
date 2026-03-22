<?php
//print_r($_REQUEST);
session_start();
require '../../Configuracion.php';
$conf = Configuracion::getInstance ();
$link = mysqli_connect("192.168.43.120", "root", "my47gmc") or die(mysqli_error());
//$link = mysqli_connect("localhost", "root", "") or die(mysqli_error());
mysqli_select_db("sicys", $link) or die(mysqli_error($link));
if(is_array($_REQUEST)) {
	foreach ($_REQUEST as $k => $v){
		$id =  $k;
	}
	$sql = "Select * From prehreq Where id_prehreq = ".$id;
	$run = mysqli_query($sql, $link) or die(mysqli_error($link));
	$row = mysqli_fetch_array($run);
	//print_r($row);
	
	$sql_cc = "Select c.cc_codigo, c.cc_descripcion, e.emp_nombre From cecosto c Join empresa e On c.id_empresa = e.id_empresa Where c.id_empresa = ".$row['id_empresa']. " and c.id_cc = ".$row['id_cc'];
	$run_cc = mysqli_query($sql_cc, $link) or die(mysqli_error());
	$row_cc = mysqli_fetch_array($run_cc);
	
	require 'PHPMailerAutoload.php';
	$mail = new PHPMailer;
	$ua = array();
	/* USUARIOS AUTORIZADORES PARA CENTRO DE COSTO */
	$sql_usea = "Select a.id_usuario, u.usr_nombre, u.usr_email, u.usr_ges From acc_emp_cc a"
		." Join usuario u"
		." On u.id_usuario = a.id_usuario"
		." Where a.id_empresa = ".$row['id_empresa']
		." and a.id_cc = ".$row['id_cc']
		." and u.id_rol = 999999995";
	$run_usea = mysqli_query($sql_usea, $link) or die(mysqli_error($link));
	while ($row_usea = mysqli_fetch_array($run_usea)){
		if($row_usea[3] <> '1') {
			$ua[] = $row_usea[1];
		}
	}
	
	/* USUARIOS ADMINISTRADORS */
	$sql_user = "Select a.id_usuario, u.usr_nombre, u.usr_email, u.usr_ges From acc_emp_cc a"
		." Join usuario u"
		." On u.id_usuario = a.id_usuario"
		." Where a.id_empresa = ".$row['id_empresa']
		." and a.id_cc = ".$row['id_cc']
		." and u.id_rol = 999999998";
	$run_user = mysqli_query($sql_user, $link) or die(mysqli_error($link));
	while ($row_user = mysqli_fetch_array($run_user)){
		if(!empty($row_user[2]) && $row_user[3] <> '1') {
			
			$mail->SMTPDebug = 0;                               // Enable verbose debug output
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = '192.168.43.130';
			$mail->setFrom('requisicion@impressa.com', 'Requisicion de Suministro');
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->addAddress($row_user[2], $row_user[1]);     // Add a recipient
			
			//$mail->addReplyTo('info@example.com', 'Information');
			$cotis = 0;
			
			$body = 'El usuario '.$row['prehreq_usuario'].' necesita enviar para autorizacion la requisicion #'.$row['prehreq_numero']. ",";
			$body .= "<br> desde (". $row_cc["cc_codigo"] .")".$row_cc["cc_descripcion"]." de ".$row_cc["emp_nombre"];
			$body .= '<br>pero el o los usuario(s) autorizador(es) para su centro de costo, que se listan a continuacion:<br>';
			foreach ($ua as $uk){
				$body .= $uk.'<br>';
			}
			$body .= ',no tiene(n) correo interno definido, favor asigne uno y notificar a usuario para que continue requisicion.';
			$mail->Subject = 'Notificacion para requisicion #'.$row['prehreq_numero'];
			$mail->Body    = $body;
			//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
			if(!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
			} else {
			echo '<br>Message has been sent';
			}
		}
		//print ($row_user[0] .'=>'. $row_user[1].'=>'. $row_user[2].'<br>');
	}
	
	header('Location: http://192.168.40.6/sics/?c=req&a=inicio&id=6&msg=ER001');
	
	die();
} else {
	echo 'No se ha enviado nada';
}
mysqli_close($link);
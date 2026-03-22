<?php
//print_r($_REQUEST);
$link = mysqli_connect("192.168.43.120", "root", "my47gmc") or die(mysqli_error());
//$link = mysqli_connect("localhost", "root", "") or die(mysqli_error());
mysqli_select_db("sicys", $link) or die(mysqli_error($link));
if(is_array($_REQUEST)) {
	$i = 1;
	foreach ($_REQUEST as $k => $v){
		if($i == 1) {
			$id =  $k;
		}
		if($i == 2) {
			$oc =  $k;
		}
		$i++;
	}
	$sql = "Select * From prehsol Where id_prehsol = ".$id;
	$run = mysqli_query($sql, $link) or die(mysqli_error($link));
	$row = mysqli_fetch_array($run);
	
	$sql_cc = "Select c.cc_codigo, c.cc_descripcion, e.emp_nombre From cecosto c Join empresa e On c.id_empresa = e.id_empresa Where c.id_empresa = ".$row['id_empresa']. " and c.id_cc = ".$row['id_cc'];
	$run_cc = mysqli_query($sql_cc, $link) or die(mysqli_error());
	$row_cc = mysqli_fetch_array($run_cc);
	
	require 'PHPMailerAutoload.php';
	$mail = new PHPMailer;
	
	$sql_user = "Select a.id_usuario, u.usr_nombre, u.usr_email From acc_emp_cc a"
		." Join usuario u"
		." On u.id_usuario = a.id_usuario"
		." Where a.id_empresa = ".$row['id_empresa']
		." and a.id_cc = ".$row['id_cc']
		." and u.id_rol = 999999995";
	$run_user = mysqli_query($sql_user, $link) or die(mysqli_error($link));
	while ($row_user = mysqli_fetch_array($run_user)){
		if(!empty($row_user[2])) {
			
			$mail->SMTPDebug = 0;                               // Enable verbose debug output
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = '192.168.43.130';
			$mail->setFrom('solicitud@impressa.com', 'Solicitud de Compra');
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->addAddress($row_user[2], $row_user[1]);     // Add a recipient
			
			$body = "Se ha generado la Orden de Compra para la solicitud #".$row['prehsol_numero_sol'];
			$body .= "<br> desde (". $row_cc["cc_codigo"] .")".$row_cc["cc_descripcion"]." de ".$row_cc["emp_nombre"];
			$mail->Subject = 'Orden de compra generada';
			$mail->Body    = $body;
			if(!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
			} else {
			echo '<br>Message has been sent';
			}
		}
	}
	header('Location: http://192.168.40.6/sics/?c=solc&a=gestor&id=5');
	die();
	
} else {
	echo 'No se ha enviado nada';
}
mysqli_close($link);
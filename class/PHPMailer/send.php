<?php
session_start();
require '../../Configuracion.php';
$conf = Configuracion::getInstance();
//print_r($_REQUEST);
$link = mysqli_connect($conf->getHostDB(), $conf->getUserDB(), $conf->getPassDB()) or die(mysqli_error());
//$link = mysqli_connect("localhost", "root", "") or die(mysqli_error());
mysqli_select_db($link, $conf->getBD()) or die(mysqli_error($link));
if (is_array($_REQUEST)) {
	foreach ($_REQUEST as $k => $v) {
		$id = $k;
	}
	$sql = "Select * From prehsol Where id_prehsol = " . $id;
	$run = mysqli_query($link, $sql) or die(mysqli_error($link));
	$row = mysqli_fetch_array($run);

	$sql_cc = "Select c.cc_codigo, c.cc_descripcion, e.emp_nombre From cecosto c Join empresa e On c.id_empresa = e.id_empresa Where c.id_empresa = " . $row['id_empresa'] . " and c.id_cc = " . $row['id_cc'];
	$run_cc = mysqli_query($link, $sql_cc) or die(mysqli_error());
	$row_cc = mysqli_fetch_array($run_cc);

	$conf->depurar($row_cc);
	//print_r($row);

	require 'PHPMailerAutoload.php';
	$mail = new PHPMailer;

	$sql_user = "Select a.id_usuario, u.usr_nombre, u.usr_email, u.usr_sol, am.id_modulo, am.acc_aut "
		. " From acc_emp_cc a"
		. " Join usuario u"
		. " On u.id_usuario = a.id_usuario"
		. " JOIN acc_modulo am"
		. " On am.id_usuario = a.id_usuario"
		. " Where a.id_empresa = " . $row['id_empresa']
		. " and a.id_cc = " . $row['id_cc']
		. " and u.id_rol = 999999995"
		. " and am.mod_url = '?c=solc&a=inicio&id=5'"
		. " and am.acc_aut = '1'";

	$conemail = 0;

	$run_user = mysqli_query($link, $sql_user) or die(mysqli_error($link));
	while ($row_user = mysqli_fetch_array($run_user)) {
		$conf->depurar($row_user);
		if (!empty($row_user[2]) && trim($row_user[2]) != '') {
			//print_r($row_user);
			$conemail++;
			$mail = new PHPMailer; //se agrego para resetear
			$mail->SMTPDebug = 0;                               // Enable verbose debug output
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = '192.168.43.130';
			$mail->setFrom('solicitud@impressa.com', 'Solicitud de Compra');
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->addAddress($row_user[2], $row_user[1]);     // Add a recipient
			//$mail->addAddress('wserpas@impressa.com', 'Desarrollo');     // Add a recipient

			//$mail->addReplyTo('info@example.com', 'Information');
			$cotis = 0;
			if (!empty($row['prehsol_coti1'])) {
				$mail->addAttachment($row['prehsol_coti1']);         // Add attachments
				$cotis = 1;
			}
			if (!empty($row['prehsol_coti2'])) {
				$mail->addAttachment($row['prehsol_coti2']);    // Optional name
				$cotis = 1;
			}
			if (!empty($row['prehsol_coti3'])) {
				$mail->addAttachment($row['prehsol_coti3']);    // Optional name
				$cotis = 1;
			}

			$body = $row['prehsol_usuario'] . ' le ha enviado la solicitud de compra #' . $row['prehsol_numero'] . " para su autorizacion.";
			$body .= "<br> de (" . $row_cc["cc_codigo"] . ")" . $row_cc["cc_descripcion"] . " de " . $row_cc["emp_nombre"];
			if ($cotis > 0) {
				$body .= '<br>Favor revisar las cotizaciones adjuntas';
			}
			$mail->Subject = 'Solicitud para autorizacion #' . $row['prehsol_numero'];
			$mail->Body = $body;
			//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
			// if (!$mail->send()) {
			// 	echo 'Message could not be sent.<br />';
			// 	echo 'Mailer Error: ' . $mail->ErrorInfo;
			// 	//die();
			// } else {
			// 	//echo '<br>Message has been sent';
			// }
		}
		//print ($row_user[0] .'=>'. $row_user[1].'=>'. $row_user[2].'<br>');
	}

	if ($conemail <= 0) {
		echo 'No se ha encontrado un usuario autorizador para este centro de costo, favor informe a proveeduria. <a href="http://192.168.40.6/sics/?c=solc&a=inicio&id=5"> Regresar</a>';
	} else {
		//echo 'Mensaje enviado con exito!';
		//header('Location: http://192.168.40.6/sics/?c=solc&a=inicio&id=5');
		if ($conf->return_menu() != '') {
			header('Location: ' . $conf->return_menu());
			die();
			//echo $conf->return_menu();
		} else {
			header('Location: http://192.168.40.6/sics/?c=solc&a=inicio&id=5');
			die();
		}
	}

} else {
	echo 'No se ha enviado nada';
}

mysqli_close($link);
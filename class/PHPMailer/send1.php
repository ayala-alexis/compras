<?php
//print_r($_REQUEST);
session_start();
include_once '../../Configuracion.php';
$conf = Configuracion::getInstance();
//echo $conf->_email_proveeduria;
$link = mysqli_connect($conf->getHostDB(), $conf->getUserDB(), $conf->getPassDB()) or die(mysqli_error());
//$link = mysqli_connect("localhost", "root", "") or die(mysqli_error());
mysqli_select_db($link, $conf->getBD()) or die(mysqli_error($link));

if (is_array($_REQUEST)) {
	foreach ($_REQUEST as $k => $v) {
		$id = $k;
	}

	$usuarios_distribuidos = array();
	// Obtenemos detalle del encabezado de la solicitud
	$sql = "Select * From prehsol Where id_prehsol = " . $id;
	$run = mysqli_query($link, $sql) or die(mysqli_error($link));
	$row = mysqli_fetch_array($run);
	//print_r($row);

	$sql_cc = "Select c.cc_codigo, c.cc_descripcion, e.emp_nombre From cecosto c Join empresa e On c.id_empresa = e.id_empresa Where c.id_empresa = " . $row['id_empresa'] . " and c.id_cc = " . $row['id_cc'];
	$run_cc = mysqli_query($link, $sql_cc) or die(mysqli_error());
	$row_cc = mysqli_fetch_array($run_cc);

	echo "****1******<br>";
	$conf->depurar($row_cc);
	echo "****1******<br><br>";

	require 'PHPMailerAutoload.php';
	$mail = new PHPMailer;
	$mail->SMTPDebug = 0;                               // Enable verbose debug output
	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = '192.168.43.130';
	$mail->setFrom('solicitud@impressa.com', 'Solicitud de Compra');
	$mail->isHTML(true);                                  // Set email format to HTML

	// Verificamos si tiene aisgnada una categoria
	if ($row['id_categoria'] > 0) {
		// Buscamos si tiene autorizacion por categoria

		// Primero buscamos las de segundo nivel
		$sql_estado_cat = "Select gi.id_auto_categoria, gi.id_usuario, u.usr_email, u.usr_nombre From"
			. " gestion_categorias gi Join "
			. " usuario u On"
			. " u.id_usuario = gi.id_usuario"
			. " Where id_categoria = " . $row['id_categoria'];
		//echo "2do Nivel " .$sql_estado_cat;
		$run_estado_cat = mysqli_query($link, $sql_estado_cat);
		/* 
		 * TIENE GESTOR DE CATEGORIA 2do. NIVEL
		 */
		if (mysqli_num_rows($run_estado_cat) > 0) {

			$sql_up = "update prehsol set gestion_nivel = 2 where id_prehsol = " . $id;
			mysqli_query($link, $sql_up) or die(mysqli_error($link));

			// Tiene gestor de categoria
			while ($row_user = mysqli_fetch_array($run_estado_cat)) {
				echo "****2******<br>";
				$conf->depurar($row_user);
				echo "****2******<br><br>";
				if (!empty($row_user[2])) {
					$mail->addAddress($row_user[2], $row_user[3]);     // Add a recipient
					$usuarios_distribuidos[] = $row_user[3];
				}
			}
			$cotis = 0;
			if (!empty($row['prehsol_coti1'])) {
				$mail->addAttachment($row['prehsol_coti1']);
				$cotis = 1;
			}
			if (!empty($row['prehsol_coti2'])) {
				$mail->addAttachment($row['prehsol_coti2']);
				$cotis = 1;
			}
			if (!empty($row['prehsol_coti3'])) {
				$mail->addAttachment($row['prehsol_coti3']);
				$cotis = 1;
			}
			$body = 'Se ha enviado la solicitud #' . $row['prehsol_numero_sol'] . " para su revision segun categoria.<br>";
			$body .= "<br> de (" . $row_cc["cc_codigo"] . ")" . $row_cc["cc_descripcion"] . " de " . $row_cc["emp_nombre"];
			$body .= '<br>A los siguientes destinatarios:<br>';
			foreach ($usuarios_distribuidos as $ua) {
				$body .= $ua . '<br>';
			}
			if ($cotis > 0) {
				$body .= '<br>Favor revisar las cotizaciones adjuntas';
			}
			$mail->Subject = 'Solicitud para revision y autorizacion #' . $row['prehsol_numero_sol'];
			$mail->Body = $body;
			if (!$mail->send()) {
				echo 'Message could not be sent.';
				echo 'Mailer Error: ' . $mail->ErrorInfo;
			} else {
				echo '<br>Message has been sent';
			}
		} else {

			// Primero buscamos las de primer nivel
			$sql_estado_cat = "Select gi.id_auto_categoria, gi.id_usuario, u.usr_email, u.usr_nombre From"
				. " gestion_categorias gi Join "
				. " usuario u On"
				. " u.id_usuario = gi.id_usuario"
				. " Where id_categoria = " . $row['id_categoria']
				. " and gestion_nivel=1";
			$run_estado_cat = mysqli_query($link, $sql_estado_cat);
			//echo "1er Nivel " .$sql_estado_cat;
			/*
			 * TIENE GESTOR DE CATEGORIA 1er. NIVEL
			 */
			if (mysqli_num_rows($run_estado_cat) > 0) {

				$sql_up = "update prehsol set gestion_nivel = 1 where id_prehsol = " . $id;
				mysqli_query($link, $sql_up) or die(mysqli_error($link));

				// Tiene gestor de categoria
				while ($row_user = mysqli_fetch_array($run_estado_cat)) {
					echo "****3******<br>";
					$conf->depurar($row_user);
					echo "****3******<br><br>";
					if (!empty($row_user[2])) {
						$mail->addAddress($row_user[2], $row_user[3]);     // Add a recipient
						$usuarios_distribuidos[] = $row_user[3];
					}
				}
				$cotis = 0;
				if (!empty($row['prehsol_coti1'])) {
					$mail->addAttachment($row['prehsol_coti1']);
					$cotis = 1;
				}
				if (!empty($row['prehsol_coti2'])) {
					$mail->addAttachment($row['prehsol_coti2']);
					$cotis = 1;
				}
				if (!empty($row['prehsol_coti3'])) {
					$mail->addAttachment($row['prehsol_coti3']);
					$cotis = 1;
				}
				$body = 'Se ha enviado la solicitud #' . $row['prehsol_numero_sol'] . " para su revision segun categoria.<br>";
				$body .= "<br> desde (" . $row_cc["cc_codigo"] . ")" . $row_cc["cc_descripcion"] . " de " . $row_cc["emp_nombre"];
				$body .= '<br>A los siguientes destinatarios:<br>';
				foreach ($usuarios_distribuidos as $ua) {
					$body .= $ua . '<br>';
				}
				if ($cotis > 0) {
					$body .= '<br>Favor revisar las cotizaciones adjuntas';
				}
				$mail->Subject = 'Solicitud para revision y autorizacion #' . $row['prehsol_numero_sol'];
				$mail->Body = $body;
				// if(!$mail->send()) {
				// 	echo 'Message could not be sent.';
				// 	echo 'Mailer Error: ' . $mail->ErrorInfo;
				// } else {
				// 	echo '<br>Message has been sent';
				// }

			} else {
				// Verificamos si tiene un gestor definido para enviarse la autorizacion
				$sql_user = "Select g.id_usuario, u.usr_nombre, u.usr_email From gestion_usuarios g"
					. " Join usuario u"
					. " On u.id_usuario = g.id_usuario"
					. " Where g.id_cc = " . $row['id_cc'];
				$run_user = mysqli_query($link, $sql_user) or die(mysqli_error($link));
				// Si tiene gestor asignado
				if (mysqli_num_rows($run_user) > 0) {
					// Enviamos a todos los gestores
					while ($row_user = mysqli_fetch_array($run_user)) {
						echo "****4******<br>";
						$conf->depurar($row_user);
						echo "****4******<br><br>";
						if (!empty($row_user[2])) {
							$mail->addAddress($row_user[2], $row_user[1]);     // Add a recipient
							$usuarios_distribuidos[] = $row_user[1];
						}
					}
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
					$body = 'Se ha enviado la solicitud #' . $row['prehsol_numero_sol'] . " para su gestion.<br>";
					$body .= "<br> desde (" . $row_cc["cc_codigo"] . ")" . $row_cc["cc_descripcion"] . " de " . $row_cc["emp_nombre"];
					$body .= '<br>A los siguientes destinatarios:<br>';
					foreach ($usuarios_distribuidos as $ua) {
						$body .= $ua . '<br>';
					}
					if ($cotis > 0) {
						$body .= '<br>Favor revisar las cotizaciones adjuntas';
					}
					$mail->Subject = 'Solicitud para gestion #' . $row['prehsol_numero_sol'];
					$mail->Body = $body;
					// if(!$mail->send()) {
					// 	echo 'Message could not be sent.';
					// 	echo 'Mailer Error: ' . $mail->ErrorInfo;
					// } else {
					// 	echo '<br>Message has been sent';
					// }
				} else {
					$mail->addAddress($conf->_email_proveeduria, "Analista de Compras");     // Add a recipient
					$mail->addAddress($conf->_email_proveeduria2, "Analista de Compras");     // Add a recipient
					$mail->addAddress($conf->_email_proveeduria3, "Analista de Compras");     // Add a recipient
					$mail->addAddress($conf->_email_proveeduriaJefe, "Jefe de Compras");     // Add a recipient
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
					$body = 'Se ha enviado la solicitud #' . $row['prehsol_numero_sol'] . " para su gestion de compra.";
					$body .= "<br> desde (" . $row_cc["cc_codigo"] . ")" . $row_cc["cc_descripcion"] . " de " . $row_cc["emp_nombre"];
					if ($cotis > 0) {
						$body .= '<br>Favor revisar las cotizaciones adjuntas';
					}
					$mail->Subject = 'Solicitud para gestion de compra #' . $row['prehsol_numero_sol'];
					$mail->Body = $body;
					// if(!$mail->send()) {
					// 	echo 'Message could not be sent.';
					// 	echo 'Mailer Error: ' . $mail->ErrorInfo;
					// } else {
					// 	echo '<br>Message has been sent';
					// }
				}
			}
		}
	} else {
		// Verificamos si tiene un gestor definido para enviarse la autorizacion
		$sql_user = "Select g.id_usuario, u.usr_nombre, u.usr_email From gestion_usuarios g"
			. " Join usuario u"
			. " On u.id_usuario = g.id_usuario"
			. " Where g.id_cc = " . $row['id_cc'];
		$run_user = mysqli_query($link, $sql_user) or die(mysqli_error($link));
		// Si tiene gestor asignado
		if (mysqli_num_rows($run_user) > 0) {
			// Enviamos a todos los gestores
			while ($row_user = mysqli_fetch_array($run_user)) {
				echo "****5******<br>";
				$conf->depurar($row_user);
				echo "****5******<br><br>";
				if (!empty($row_user[2])) {
					$mail->addAddress($row_user[2], $row_user[1]);     // Add a recipient
					$usuarios_distribuidos[] = $row_user[1];
				}
			}
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
			$body = 'Se ha enviodo la solicitud #' . $row['prehsol_numero_sol'] . " para su gestion.";
			$body .= "<br> desde (" . $row_cc["cc_codigo"] . ")" . $row_cc["cc_descripcion"] . " de " . $row_cc["emp_nombre"];
			$body .= '<br>A los siguientes destinatarios:<br>';
			foreach ($usuarios_distribuidos as $ua) {
				$body .= $ua . '<br>';
			}
			if ($cotis > 0) {
				$body .= '<br>Favor revisar las cotizaciones adjuntas';
			}
			$mail->Subject = 'Solicitud para gestion #' . $row['prehsol_numero_sol'];
			$mail->Body = $body;
			// if(!$mail->send()) {
			// 	echo 'Message could not be sent.';
			// 	echo 'Mailer Error: ' . $mail->ErrorInfo;
			// } else {
			// 	echo '<br>Message has been sent';
			// }
		} else {
			$mail->addAddress($conf->_email_proveeduria, "Analista de Compras");     // Add a recipient
			$mail->addAddress($conf->_email_proveeduria2, "Analista de Compras");     // Add a recipient
			$mail->addAddress($conf->_email_proveeduria3, "Analista de Compras");     // Add a recipient
			$mail->addAddress($conf->_email_proveeduriaJefe, "Jefe de Compras");     // Add a recipient
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
			$body = 'Se ha enviado la solicitud #' . $row['prehsol_numero_sol'] . " para su gestion de compra.";
			$body .= "<br> desde (" . $row_cc["cc_codigo"] . ")" . $row_cc["cc_descripcion"] . " de " . $row_cc["emp_nombre"];
			if ($cotis > 0) {
				$body .= '<br>Favor revisar las cotizaciones adjuntas';
			}
			$mail->Subject = 'Solicitud para gestion de compra #' . $row['prehsol_numero_sol'];
			$mail->Body = $body;
			// if(!$mail->send()) {
			// 	echo 'Message could not be sent.';
			// 	echo 'Mailer Error: ' . $mail->ErrorInfo;
			// } else {
			// 	echo '<br>Message has been sent';
			// }
		}
	}
	// Todo se envio bien
	//header('Location: http://192.168.40.6/sics/?c=solc&a=autoriza&id=5&es='.$row['id_empresa']);
	//echo $conf->return_menu();
	if ($conf->return_menu() != '') {
		header('Location: ' . $conf->return_menu());
		die();
	} else {
		header('Location: http://192.168.40.6/sics/?c=solc&a=autoriza&id=5&es=' . $row['id_empresa']);
		die();
	}
} else {
	echo 'No se ha enviado nada';
}
mysqli_close($link);
<?php
//print_r($_REQUEST);
session_start();
include_once '../../Configuracion.php';
$conf = Configuracion::getInstance();


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


$mail->addAddress("alexis.ayala@impressarepuestos.com", "Analista de Compras");     // Add a recipient
$mail->addAddress("alexis.ayala1@impressarepuestos.com", "Analista de Compras");     // Add a recipient
$mail->addAddress("alexis.ayala2@impressarepuestos.com", "Analista de Compras");     // Add a recipient
$mail->addAddress("alexis.ayala3@impressarepuestos.com", "Jefe de Compras");     // Add a recipient

$mail->addAttachment("C:\inetpub\wwwroot\iis-85.png");         // Add attachments
$mail->addAttachment("C:\inetpub\wwwroot\iis-86.png");         // Add attachments
$mail->addAttachment("C:\inetpub\wwwroot\iis-87.png");         // Add attachments
$cotis = 1;

$body = "Se ha enviado la solicitud #0000001 para su gestion de compra.";
$body .= "<br> desde (14-Sistemas) de Impressa Repuestos";
if($cotis > 0){
	$body .= '<br>Favor revisar las cotizaciones adjuntas';
}
$mail->Subject = 'Solicitud para gestion de compra #1009';
$mail->Body    = $body;
if(!$mail->send()) {
	echo 'Message could not be sent.';
	echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
	echo '<br>Message has been sent';
}
	
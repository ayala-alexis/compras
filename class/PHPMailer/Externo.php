<?php

function enviarEmailStream($url, $jsonData) {
    // Convertir datos a JSON
    $jsonString = json_encode($jsonData);
    
    // Configurar el contexto para HTTP POST
    $opciones = array(
        'http' => array(
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n" .
                       "Content-Length: " . strlen($jsonString) . "\r\n",
            'content' => $jsonString,
            'timeout' => 30
        )
    );
    
    $contexto = stream_context_create($opciones);
    
    // Enviar la solicitud
    $resultado = file_get_contents($url, false, $contexto);
    
    if ($resultado === FALSE) {
        return "Error al enviar la solicitud";
    }
    
    return $resultado;
}

session_start();
require '../../Configuracion.php';
$conf = Configuracion::getInstance ();

	$conf->depurar($row_cc);
	
	// Datos a enviar (mismos que arriba)
$datos = array(
    "Destinatario" => "alexis.ayala@impressarepuestos.com",
    "NameDestinatario" => "Solicitud de Compra",
    "Body" => "<p>Prueba de HMTL<b>Hola</b></p>",
    "EsHtml" => true,
    "Subject" => "Solicitud Compras",
    "ArchivosAdjuntos" => array(
        "C:\\inetpub\\wwwroot\\iis-85.png"
    )
);

// URL del servicio (reemplaza con la URL real)
$urlServicio = "http://192.168.40.6/Email/Email/Enviar";

// Enviar la solicitud
$respuesta = enviarEmailStream($urlServicio, $datos);
echo $respuesta;
	
	require 'PHPMailerAutoload.php';
	
	$mail = new PHPMailer; //se agrego para resetear
	$mail->SMTPDebug = 2;                               // Enable verbose debug output
	$mail->isSMTP();                                      // Set mailer to use SMTP
	//$mail->Host = '192.168.43.130';
	
	$mail->Host = 'smtp.office365.com';  							// Servidor Office 365:cite[1]:cite[2]:cite[3]
	$mail->SMTPAuth = true;              							// Habilitar autenticación:cite[1]:cite[5]
	$mail->Username = 'xxxxx@impressarepuestos.com';  	// Tu email completo de Office 365:cite[3]:cite[6]
	$mail->Password = 'xxxxx';		   							// Contraseña de Office 365:cite[3]:cite[6]
	$mail->SMTPSecure = 'tls';           							// Cifrado TLS:cite[2]:cite[3]:cite[5]
	$mail->Port = 587;                   							// Puerto para TLS:cite[1]:cite[2]:cite[3]
	//$mail->SMTPAutoTLS = false;   // Deshabilitar TLS automático
	
	$mail->CharSet = 'UTF-8';

	// Configuraciones específicas para entornos antiguos
	$mail->SMTPAutoTLS = true;
	$mail->Timeout = 45;
	$mail->Debugoutput = 'html';

	// Configuraciones para OpenSSL 0.9.8
	$mail->SMTPOptions = array(
		'ssl' => array(
			'verify_peer' => false,
			'verify_peer_name' => false,
			'allow_self_signed' => true,
			'crypto_method' => STREAM_CRYPTO_METHOD_TLS_CLIENT // Forzar TLS 1.0
		)
	);

	
	$mail->setFrom('alexis.ayala@impressarepuestos.com', 'Alexis Ayala');
	$mail->isHTML(true);                                  // Set email format to HTML
	$mail->addAddress("alexis.ayala@impressarepuestos.com", "Alexis Ayala");     // Add a recipient
	
	$mail->addAttachment("C:\\inetpub\\wwwroot\\sicsD\\uploads\\32783imagen.png");         // Add attachments
	$cotis = 1;
	
	$body = "Solicitud de prueba para su autorizacion.";
	if($cotis > 0){
		$body .= '<br>Favor revisar las cotizaciones adjuntas';
	}
	$mail->Subject = 'Solicitud para autorizacion #99999';
	$mail->Body    = $body;
	if(!$mail->send()) {
		echo 'Message could not be sent.<br />';
		echo 'Mailer Error: ' . $mail->ErrorInfo;
		//die();
	} else {
		//echo '<br>Message has been sent';
	}
?>
<?php

require_once dirname(__FILE__).'/../../class/PHPMailer/PHPMailerAutoload.php';

class EmailCheque{
    private $email;
    private $error;

    public function __construct(){
        $this->email=new PHPMailer;
        $this->email->SMTPDebug = 0;
        $this->email->isSMTP();
        $this->email->Host = '192.168.43.130';
        $this->email->isHTML(true);
		$this->email->addBCC('dsistemas@impressa.com','Seguimiento SC');
    }

    public function notificar_solicitud_creada($cheque){
        
        $body = "<p>".$cheque->usuario." le ha enviado la solicitud de cheque #".$cheque->id." para su autorizacion.</p>";
        $this->email->Subject = "Solicitud de cheque para autorizacion #".$cheque->id.".";
        $this->email->Body    = $body;

        if(!$this->email->send()) {
            $this->error=$this->email->ErrorInfo;
            return false;
        } else {
            return true;
        }
    }
    
    public function notificar_solicitud($email,$state,$status,$id_solicitud,$perfil){
        $msj = array();
        $id_solicitud=str_pad($id_solicitud, 6,'0',STR_PAD_LEFT);
        $correo_from = "solicitud@impressa.com";
        $body="";
        if($state=='send'){
            if($status=='N1' || $status=='N2' || $status=='N3'){
                $body = "<p><b>".$perfil->nombre."</b> le ha enviado la solicitud de cheque <b>#".$id_solicitud."</b> para su autorizacion.</p>";
                $this->email->Subject = "Solicitud de cheque para autorizacion #".$id_solicitud.".";
            }else if($status=='N4'){
                $body = "<p><b>Se ha autorizado solicitud de cheque <b>#".$id_solicitud."</b>.</p>";
                $this->email->Subject = "Solicitud de cheque autorizada #".$id_solicitud.".";
            }
            $this->email->Body = $body;
            $this->email->setFrom($correo_from, 'Solicitud de Cheque'); //Utilizar correo para enviar solicitud de cheque
            if($status=='N1' || $status=='N2' || $status=='N4'){
                foreach ($email as $correo) {
                    $this->email->addAddress($correo->email, $correo->nombre);
                    echo "\n * email: ".$correo->email." de: ".$correo->nombre;
					
					$files = array();

					$datos = array(
						"Destinatario" => $correo->email,
						"NameDestinatario" => $correo->nombre,
						"Body" => $body,
						"EsHtml" => true,
						"Subject" => $this->email->Subject,
						"ArchivosAdjuntos" => $files
					);

					//$this->enviarEmailStream($datos);
                }
                echo "Result ".$this->email->send();
            }else if($status=='N3'){
                $this->email->addAddress($email->email, $email->nombre);
                echo "\n - email: ".$email->email." de: ".$email->nombre;
                echo "Result ".$this->email->send();
				
				$files = array();
				$datos = array(
					"Destinatario" => $email->email,
					"NameDestinatario" => $email->nombre,
					"Body" => $body,
					"EsHtml" => true,
					"Subject" => $this->email->Subject,
					"ArchivosAdjuntos" => $files
				);
				//$this->enviarEmailStream($datos);
            }
        }else if($state=='fail'){
            $body = "<p><b>".$perfil->nombre."</b> ha desistido la solicitud de cheque <b>#".$id_solicitud."</b>.</p>";
            $this->email->Subject = "Solicitud de cheque desistida #".$id_solicitud.".";
            $this->email->Body = $body;
            $this->email->setFrom($correo_from, 'Solicitud de Cheque');
            $this->email->addAddress($email->email, $email->nombre);
            echo "\n D email: ".$email->email." de: ".$email->nombre;
            echo "Result ".$this->email->send();
			
			$files = array();
			$datos = array(
				"Destinatario" => $email->email,
				"NameDestinatario" => $email->nombre,
				"Body" => $body,
				"EsHtml" => true,
				"Subject" => $this->email->Subject,
				"ArchivosAdjuntos" => $files
			);
			//$this->enviarEmailStream($datos);
        }
    }

    public function send_email($msg){
        $id_solicitud=str_pad($msg['id_solicitud'], 6,'0',STR_PAD_LEFT);
        $this->email->setFrom('solicitud@impressa.com', 'Solicitud de Cheque');
        if($msg['avance']!='N5' && ($msg['status']=='R' || $msg['status']=='Z')){
            $this->email->Subject = "Solicitud de cheque para autorizacion #".$id_solicitud.".";
        }else if($msg['avance']=='N5' && $msg['status']=='R'){
            $this->email->Subject = "Solicitud de cheque autorizada #".$id_solicitud.".";
        }else{
            $this->email->Subject = "Solicitud de cheque desistida #".$id_solicitud.".";
        }
        $this->email->Body = safe_utf_decode($msg['body']);

        $conta = 0;
        foreach ($msg['para_usuario'] as $persona) {
            $this->email->addAddress($persona['correo'], safe_utf_decode($persona['nombre']));
            echo "\n * email: ".$persona['correo']." de: ".$persona['nombre'];
            $conta++;
			
			$files = array();
			$datos = array(
				"Destinatario" => $persona['correo'],
				"NameDestinatario" => safe_utf_decode($persona['nombre']),
				"Body" => $this->email->Body,
				"EsHtml" => true,
				"Subject" => $this->email->Subject,
				"ArchivosAdjuntos" => $files
			);
			//$this->enviarEmailStream($datos);
        }
        if($conta>0){
            echo "\nResult ".$this->email->send();
        }else{
            echo "\nNo hay destinatarios para enviar correo";
        }
    }

    public function send_email_x($msg){
        $id_solicitud=str_pad($msg['id_solicitud'], 6,'0',STR_PAD_LEFT);
        $this->email->setFrom('solicitud@impressa.com', 'Solicitud de Cheque');
        if($msg['avance']!='N5' && ($msg['status']=='R' || $msg['status']=='Z')){
            $this->email->Subject = "Solicitud de cheque para autorizacion #".$id_solicitud.".";
        }else if($msg['avance']=='N5' && $msg['status']=='R'){
            $this->email->Subject = "Solicitud de cheque autorizada #".$id_solicitud.".";
        }else{
            $this->email->Subject = "Solicitud de cheque desistida #".$id_solicitud.".";
        }
        $this->email->Body = safe_utf_decode($msg['body']);

        $conta = 0;
        foreach ($msg['para_usuario'] as $persona) {
            $this->email->addAddress($persona['correo'], safe_utf_decode($persona['nombre']));
            echo "\n * email: ".$persona['correo']." de: ".$persona['nombre'];
            $conta++;
			
			$files = array();
			$datos = array(
				"Destinatario" => $persona['correo'],
				"NameDestinatario" => safe_utf_decode($persona['nombre']),
				"Body" => $this->email->Body,
				"EsHtml" => true,
				"Subject" => $this->email->Subject,
				"ArchivosAdjuntos" => $files
			);
			//$this->enviarEmailStream($datos);
        }
        if($conta>0){
            echo "\nResult ".$this->email->send_x();
        }else{
            echo "\nNo hay destinatarios para enviar correo";
        }
    }

    public function notificar_solicitud_5k($email,$state,$status,$id_solicitud,$perfil){
        $msj = array();
        $id_solicitud=str_pad($id_solicitud, 6,'0',STR_PAD_LEFT);
        $correo_from = "solicitud@impressa.com";
        $body="";
        if($state=='send'){

            $body = "<p><b>".$perfil->nombre."</b> le ha enviado la solicitud de cheque <b>#".$id_solicitud."</b> para su autorizacion.</p>";
            $this->email->Subject = "Solicitud de cheque para autorizacion #".$id_solicitud.".";

            $this->email->Body = $body;
            $this->email->setFrom($correo_from, 'Solicitud de Cheque'); //Utilizar correo para enviar solicitud de cheque

            foreach ($email as $correo) {
                $this->email->addAddress($correo->email, $correo->nombre);
                echo "\n * email: ".$correo->email." de: ".$correo->nombre;
				
				$files = array();
				$datos = array(
					"Destinatario" => $correo->email,
					"NameDestinatario" => $correo->nombre,
					"Body" => $body,
					"EsHtml" => true,
					"Subject" => $this->email->Subject,
					"ArchivosAdjuntos" => $files
				);
				//$this->enviarEmailStream($datos);
            }
            echo "\nResult ".$this->email->send();

        }else if($state=='fail'){
            $body = "<p><b>".$perfil->nombre."</b> ha desistido la solicitud de cheque <b>#".$id_solicitud."</b>.</p>";
            $this->email->Subject = "Solicitud de cheque desistida #".$id_solicitud.".";
            $this->email->Body = $body;
            $this->email->setFrom($correo_from, 'Solicitud de Cheque');
            $this->email->addAddress($email->email, $email->nombre);
            echo "\n D email: ".$email->email." de: ".$email->nombre;
            echo "\nResult ".$this->email->send();
			
			$files = array();
			$datos = array(
				"Destinatario" => $email->email,
				"NameDestinatario" => $email->nombre,
				"Body" => $body,
				"EsHtml" => true,
				"Subject" => $this->email->Subject,
				"ArchivosAdjuntos" => $files
			);
			//$this->enviarEmailStream($datos);
        }
    }

    public function getError(){
        return $this->error;
    }
	
	public function enviarEmailStream($jsonData) {
		// Convertir datos a JSON
		$jsonString = json_encode($jsonData);
		
		// Enviar la solicitud
		return file_get_contents(
				"http://192.168.40.6/Email/Email/Enviar", 
				false, 
				stream_context_create(array(
					'http' => array(
						'method' => 'POST',
						'header' => "Content-Type: application/json\r\n" .
								   "Content-Length: " . strlen($jsonString) . "\r\n",
						'content' => $jsonString,
						'timeout' => 30
					)
				))
			);
		
	}
}

?>
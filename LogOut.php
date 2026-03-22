<?php
session_start();
$url='Location:./';
if(isset($_SESSION['acc_app'])){
	if($_SESSION['acc_app']=1){
		$url='Location: http://192.168.40.6/acceso/app.aspx';
	}
}
$_SESSION = array();
// Destruye todas las variables de la sesion  
session_unset();  
// Finalmente, destruye la sesion  
session_destroy();  
header ($url);
?>
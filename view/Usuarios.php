<?php 
$is_ufinal_o_autorizador = false;

if(isset($_SESSION)){
	if(isset($_SESSION['cheque'])){
		if(!empty($_SESSION['rol_usuario_cheque'])){
			$optionctl=$_SESSION['rol_usuario_cheque'];
			$is_ufinal_o_autorizador = ((int)$_SESSION['rol_de_usuario']<=2);
			if($optionctl=="N5"){
				header('Location: ?c=solcheque&a=consultar_impresion');
			}elseif($optionctl=="N3"){
				header('Location: ?c=solcheque&a=consultar');
			}elseif(($optionctl=="N1" || $optionctl=="N2" || $optionctl=="N4") && $is_ufinal_o_autorizador){
				header('Location: ?c=menu&a=index');
			}
		}
	}else{
		if(!empty($_SESSION['rol_usuario_cheque'])){
			$is_ufinal_o_autorizador = ((int)$_SESSION['rol_de_usuario']<=2);
			$optionctl=$_SESSION['rol_usuario_cheque'];
			if(($optionctl=="N1" || $optionctl=="N2" || $optionctl=="N4")  && $is_ufinal_o_autorizador){
				header('Location: ?c=menu&a=index');
			}
		}
	}
}
?>
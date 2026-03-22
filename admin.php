<?php
ob_start ();
error_reporting(E_ALL & ~E_WARNING); // Oculta warnings pero muestra otros errores
?>
<!DOCTYPE html>
	<html lang="es-ES">
	<head>
	<meta charset="utf-8">
	<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame -->
  	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>SICYS Admon. Usuarios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- ESTILOS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="css/redmond/jquery-ui-1.9.2.custom.min.css" />
	<link rel="stylesheet" type="text/css" href="css/jquery.mCustomScrollbar.css"/>
	<link type="text/css" rel="stylesheet" href="css/jquery.pnotify.default.css" />
	<link href="css/ui.dynatree.css" rel="stylesheet" type="text/css" id="skinSheet" />
	<link href="css/chardinjs.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="css/msgGrowl.css" />
	
	<link rel="stylesheet" type="text/css" href="css/custom.css" />

	<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv-printshiv.js"></script>
    <![endif]-->

	<script type="text/javascript" src="js/jquery-1.8.3.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.9.2.custom.js"></script>
	<script type="text/javascript" src="js/jquery.cookie.js"></script>
	<script type="text/javascript" src="js/jquery.blockUI.js"></script>

	<script type="text/javascript" src="js/bootstrap.js"></script>
	<script type="text/javascript" src="js/jquery.metadata.js"></script>
	<script type="text/javascript" src="js/jquery.validate.js"></script>
	<script type="text/javascript" src="js/jquery.mousewheel.js"></script>
	<script type="text/javascript" src="js/jquery_notification_v.1.js"></script>
	<script type="text/javascript" src="js/jquery.placeholder.js"></script>
	<script type="text/javascript" src="js/jquery.mCustomScrollbar.min.js"></script>
	<script type="text/javascript" src="js/msgGrowl.js"></script>
	<script type="text/javascript" src="js/jquery.pnotify.min.js"></script>
	<script type="text/javascript" src="js/chardinjs.min.js"></script>

	<script src="js/jquery.dynatree.js" type="text/javascript"></script>

	<!-- jquery.contextmenu,  A Beautiful Site (http://abeautifulsite.net/) -->
  	<script src="js/jquery.contextMenu-custom.js" type="text/javascript"></script>
  	<link href="css/jquery.contextMenu.css" rel="stylesheet" type="text/css" >

	<link rel="stylesheet" type="text/css" href="css/admin_custom.css" />

	<script type="text/javascript" src="js/modernizr-2.5.3.min.js"></script>
	<script type="text/javascript" src="js/respond.min.js"></script>


	<?php
	// xajax
	//require_once '/xajax/xajax_core/xajax.inc.php';
	//$xj = new xajax ();
	//$xj->printJavascript ( 'xajax/' );
	?>
	<script type="text/javascript">

		/*
		* deshabilita el F5
		*/
		/*
		window.history.forward(1);
		document.attachEvent("onkeydown", my_onkeydown_handler);
		function my_onkeydown_handler() {
			switch (event.keyCode) {
			case 116 : // 'F5'
				event.returnValue = false;
				event.keyCode = 0;
				window.status = "F5 NO PERMITIDO.";
				break;
			}
		}
		*/
		/***********************************************
		* Disable Text Selection script- � Dynamic Drive DHTML code library (www.dynamicdrive.com)
		* This notice MUST stay intact for legal use
		* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
		***********************************************/
		function disableSelection(target){
			if (typeof target.onselectstart!="undefined") { //For IE
				target.onselectstart=function(){
					return false;
					};
			} else if (typeof target.style.MozUserSelect!="undefined") { //For Firefox
				target.style.MozUserSelect="none";
			} else { //All other route (For Opera)
				target.onmousedown=function(){
					return false;
					};
			}
			target.style.cursor = "default";
		}
		/*
		* EVITAMOS EL CLICK DERECHO
		*/
		$(function () {
			$(this).bind("contextmenu", function (e) {
				e.preventDefault();
				$.msgGrowl ({
					type: 'error',
					title: 'IMPRESSA, S.A. de C.V.',
					text: 'Accion NO autorizada.'
				});
			});
		});
		/*
		*VEAMOS EL CTRL+C
		*/
		var ctlPressed = false; //Flag to check if pressed the CTL key
		var ctl = 17; //Key code for Ctl Key
		var c = 67; //Key code for "c" key
		var timeSlide = 5000;
		$(document).keydown(function(e) {
			if (e.keyCode == ctl) ctlPressed = true;
		}).keyup(function(e) {
			if (e.keyCode == ctl) ctlPressed = true;
		});
		$(function(){
			$(".container").keydown(function(e)     {
				if (ctlPressed && e.keyCode == c)
					$('#alerta').show('slow');
					$('#alerta').slideUp(timeSlide);
					return false;
			});
		});
	</script>
</head>
<?php
global $idmod;
// Primero algunas variables de configuracion
require_once 'Configuracion.php';
// Manejo de Base de Datos
require_once 'DB.php';
// Clase de Usuarios
require_once 'class/Usuario.php';
// La carpeta donde buscaremos los controladores
$carpetaControladores = "controller/";
// Si no se indica un controlador, este es el controlador que se usar�
$controladorPredefinido = "index";
// Si no se indica una accion, esta accion es la que se usar�
$accionPredefinida = "inicio";
if (! empty ( $_GET ['c'] )) {
	$controlador = $_GET ['c'];
} else {
	$controlador = $controladorPredefinido;
}
if (! empty ( $_GET ['a'] )) {
	$accion = $_GET ['a'];
} else {
	$accion = $accionPredefinida;
}
// SESSION
session_start ();
?>
<body>

	<!-- CONTENIDO -->
	<div class="container" id="content">

		<div class="row">

				<?php
				// Ya tenemos el controlador y la accion
				// Formamos el nombre del fichero que contiene
				// nuestro controlador
				$controlador = $carpetaControladores . $controlador . 'Controller.php';
				/*
				 * CONTENIDO AQUI
				 */
				// Si la sesion ya esta activa
				if (! empty ( $_SESSION ['n'] ) && ! empty ( $_SESSION ['u'] )) {
					// Incluimos el controlador o
					// detenemos todo si no existe
					if (is_file ( $controlador )) {
						require $controlador;
						// Invocamos la accion
						if (is_callable ( $accion )) {
							$accion ();
						} else {
						?>
							<div class="alert alert-danger">
								<p>Oops, ha ocurrido un error!</p>
								<p>
									Parece que el link que buscas no ha sido encontrado, tendremos
									que llevarte <a href="?c=login&a=ingreso">al Inicio</a>
								</p>
							</div>
						<?php
						}
					} else {
					?>
						<div class="alert alert-danger">
							<p>Oops, ha ocurrido un error!</p>
							<p>
								Parece que el link que buscas no ha sido encontrado, tendremos
								que llevarte <a href="?c=login&a=ingreso">al Inicio</a>
							</p>
						</div>
					<?php
					}
				} else {
					if ($controlador === 'controller/indexController.php') {
						// Incluimos el controlador o detenemos
						// todo si no existe
						if (is_file ( $controlador )) {
							require $controlador;
						}
						// Llamamos la accion o detenemos
						// todo si no existe
						if (is_callable ( $accion )) {
							$accion ();
						} else {
						?>
							<!-- La accion no ha sido definida para este controlador -->
							<div class="alert alert-danger">
								<p>Oops, ha ocurrido un error!</p>
								<p>
									Parece que el link que buscas no ha sido encontrado, tendremos
									que llevarte <a href="?c=login&a=ingreso">al Inicio</a>
								</p>
							</div>
						<?php
						}
					} else {
					?>
						<div class="alert alert-danger">
							<p>Oops, ha ocurrido un error!</p>
							<p>
								Parece que tus credenciales de sesion han expirado por favor
								vuelve a <a href="./">Iniciar Sesion</a>
							</p>
						</div>
					<?php
					}
				}
				?>

		</div>
	</div>
	<!-- ALERTA -->
	<div id="alerta" style="margin-left: 65px; top: 35px; position: absolute; z-index: 9999999; display: none;" class="alert alert-danger">
		<p>Lo sentimos no esta autorizado, para esta accion.</p>
	</div>
	<!-- CARGANDO -->
	<div id="waiting" style="display: none;">
		<fieldset>
			<legend>procesando peticion, espere por favor...</legend>
			<img src="css/redmond/images/ajax-loader.gif" />
		</fieldset>
	</div>
</body>
<script>
$('a').mouseover(function(){
	window.status = "SISTEMA INTEGRADO DE COMPRAS Y SUMINISTROS.";
});
$('a').mouseenter(function(){
	window.status = "SISTEMA INTEGRADO DE COMPRAS Y SUMINISTROS.";
});
$('a').mouseleave(function(){
	window.status = "SISTEMA INTEGRADO DE COMPRAS Y SUMINISTROS.";
});
$('a').hover(function(){
	window.status = "SISTEMA INTEGRADO DE COMPRAS Y SUMINISTROS.";
});
disableSelection(document.body);
</script>
</html>
<?php
ob_end_flush();
?>
<?php
// Primero algunas variables de configuracion
require 'Configuracion.php';
// Manejo de Base de Datos
require 'DB.php';
// La carpeta donde buscaremos los controladores
$carpetaControladores = "controller/";
// Si no se indica un controlador, este es el controlador que se usará
$controladorPredefinido = "index";
// Si no se indica una accion, esta accion es la que se usará
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
// Ya tenemos el controlador y la accion
// Formamos el nombre del fichero que contiene nuestro controlador
$controlador = $carpetaControladores . $controlador . 'Controller.php';
// Incluimos el controlador o detenemos todo si no existe
if (is_file ( $controlador )) {
	require $controlador;
	// Invocamos la accion
	if (is_callable ( $accion )) {
		$accion ();
	}
}
?>
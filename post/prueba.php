<?php

	include_once '../Configuracion.php';
	include_once '../DB.php';
	include_once '../ODBC.php';
	include_once '../ODBCIT.php';
	/* Check submitted value */

	$cuenta_valida_presupuesto = true;
	$presupuesto_tipo_gasto = '14';
	$presupuesto_detalle_gasto = '02';

	if (0){
		$o = ODBC::getInstance();
	} else {
		$o = ODBCIT::getInstance();
	}

	//Validación de cuenta de gasto
	$cuenta_valida_presupuesto = $o->valida_presupuesto_cuenta_gasto($presupuesto_tipo_gasto,$presupuesto_detalle_gasto);

	if($cuenta_valida_presupuesto){
		echo "<h2>SI SE VALIDA PRESUPUESTO</h2>";
	}else{
		echo "<h2>NO SE VALIDA PRESUPUESTO</h2>";
	}
		
?>

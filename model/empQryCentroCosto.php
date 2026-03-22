<?php

function buscarCC (){
	$opcion= $_POST['combo'];//combobox nos daria el id_cc del item seleccionado
	$db=DB::getInstance();
	$conf= Configuracion::getInstance ();
	$sql= "select * from " . $conf->getTbl_cecosto() ." where id_cc = 2 AND cc_descripcion = ". $opcion ;
	$run = $db->ejecutar($sql);

	while ( $row = mysqli_fetch_array ( $run ) ) {
		$array [] = $row;
	}
	return $array;
}


?>
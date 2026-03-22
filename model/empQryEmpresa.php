<?php
//trae la opcion del combobox
function buscarEmp ($nombre){
	$db=DB::getInstance();
	$conf= Configuracion::getInstance ();
	$sql= "select * from " . $conf->getTbl_empresa() ." where emp_nombre=" . $nombre;
	$run = $db->ejecutar($sql);
	
	while ( $row = mysqli_fetch_array ( $run ) ) {
		$array [] = $row;
	}
	return $array;
}

?>
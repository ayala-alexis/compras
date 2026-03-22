<?php
/*
$name = $_POST['name'];
$pk = $_POST['pk'];
$value = $_POST['value'];
$a = $_POST['a'];
$fp = fopen("ejemplo.txt","a");

fwrite($fp, "Name: $name\tPK: $pk\tValue: $value\tAdicional: $a" . PHP_EOL);
fclose($fp);
*/
/* Script for update record from X-editable. */
/* You will get 'pk', 'name' and 'value' in $_POST array. */
try {
	$pk = $_POST['pk'];
	$name = $_POST['name'];
	$value = $_POST['value'];
	$prehreq_numero = $_POST['prehreq_numero'];
	$id_empresa = $_POST['id_empresa'];
	$id_cc = $_POST['id_cc'];
	try {
		include_once '../Configuracion.php';
		include_once '../DB.php';
		/* Check submitted value */
		if(!empty($value)) {
			if (!empty($pk)) {
				/*
				 If value is correct you process it (for example, save to db).
				In case of success your script should not return anything, standard HTTP response '200 OK' is enough.
				for example:
				$result = mysqli_query('update users set '.mysqli_escape_string($name).'="'.mysqli_escape_string($value).'" where user_id = "'.mysqli_escape_string($pk).'"');
				*/
				try {
					$DB = DB::getInstance();
					$conf = Configuracion::getInstance();
					$sql_ver = "Select prehreq_estado From ".$conf->getTbl_prehreq()
						." Where "
						."id_empresa = ".$id_empresa." And "
						."id_cc = ".$id_cc." And "
						."prehreq_numero = ".$prehreq_numero;
					$run_sql = $DB->ejecutar($sql_ver);
					if (mysqli_num_rows($run_sql) <= 0) {
						$rtn = 1;
						$msg = 'La pre-requisicion ha dejado de existir';
						header('HTTP 400 Bad Request', true, 400);
						echo json_encode($msg);
					} else {
						$row = mysqli_fetch_array($run_sql);
						if ($row[0] > 1) {
							$rtn = 1;
							$msg = 'su estado ha sido <b>'.$conf->getEstado($row[0]).'</b>, presione F5 para actualizar.';
							header('HTTP 400 Bad Request', true, 400);
							echo json_encode($msg);
						} else  {
							$sql = "Update ".$conf->getTbl_predreq()." Set "
								."predreq_cantidad = ".$value.", "
								."predreq_total = (predreq_prec_uni*".$value."), "
								."predreq_cantidad_aut = ".$value
								." Where id_predreq = ".$pk;
							$DB->ejecutar($sql);
							echo json_encode('ok');
						}
					}
				} catch (Exception $e1) {
					header('HTTP 400 Bad Request', true, 400);
					echo json_encode($e1->getMessage());
				}
			} else {
				header('HTTP 400 Bad Request', true, 400);
				echo json_encode("no se ha podido actualizar");
			}
		} else {
			/*
			 In case of incorrect value or error you should return HTTP status != 200.
			Response body will be shown as error message in editable form.
			*/
			header('HTTP 400 Bad Request', true, 400);
			echo json_encode("This field is required!");
		}
	} catch (Exception $e) {
		header('HTTP 400 Bad Request', true, 400);
		echo json_encode($e->getMessage());
	}
} catch (Exception $p) {
	header('HTTP 400 Bad Request', true, 400);
	echo json_encode($p->getMessage());
}
?>

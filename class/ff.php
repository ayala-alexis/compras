<?php
/**
 * @author wserpas
 *
 */
session_start();
require '../DB.php';
require '../Configuracion.php';
require '../ODBC.php';
require '../ODBCIT.php';
$db = DB::getInstance ();
$conf = Configuracion::getInstance ();

$sql_ver2 = "
select
prehsol_Monto,
prehsol_Proveedor,
prehsol_MetodoPago,
moneda 
from sicys.prehsol
where id_prehsol = 9347
";
$run_sql2 = $db->ejecutar ( $sql_ver2 );
$row_sql2 = mysqli_fetch_array ( $run_sql2 );

echo "<pre>";
print_r($row_sql2['monto']);
echo "</pre>";


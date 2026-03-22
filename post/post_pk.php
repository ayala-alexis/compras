<?php
/* Script for update record from X-editable. */
/* You will get 'pk', 'name' and 'value' in $_POST array. */
$cant = $_POST['value']['city'];
$obs  = $_POST['value']['street'];
$id_predsol = $_POST['pk'];
//echo json_encode(print_r($_POST));
header('HTTP 400 Bad Request', true, 400);
echo json_encode('Cantidad : '.$cant.', Observacion : '.$obs);
?>

<?php 
session_start();
require_once '../../tcpdf/tcpdf.php';
// Primero algunas variables de configuracion
require_once '../../Configuracion.php';
// Manejo de Base de Datos
require_once '../../DB.php';
$db = DB::getInstance ();
$conf = Configuracion::getInstance ();

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('IMPRESSA REPUESTOS, S.A. de C.V.');
$pdf->SetTitle('Nota de Remision');
$pdf->SetSubject('Impresion');
$pdf->SetKeywords('Remision, Requisicion, Suministros, IMPRESSA');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Contador de lineas
$lns = 0;
$cabeza = '0';

// ---------------------------------------------------------
// add a page
$pdf->AddPage();

$sql = "Select a.prehreq_numero_req, a.prehreq_fecha, "
		." a.prehreq_hora, b.emp_nombre,c.cc_codigo, c.cc_descripcion, a.id_empresa, a.prehreq_numero_remision, "
		." a.prehreq_fecha_remision, a.prehreq_hora_remision, a.prehreq_usuario"
		." From "
		.$conf->getTbl_prehreq()." a"
		." Join ".$conf->getTbl_empresa()." b On b.id_empresa = a.id_empresa"
		." Join ".$conf->getTbl_cecosto()." c On c.id_empresa = a.id_empresa And c.id_cc = a.id_cc"
		." Where a.id_prehreq = ".$_GET['id'];
$run = $db->ejecutar($sql);
$rowH = mysqli_fetch_array($run);
// Nos traemos el numero de la nota
/*
$sql_cc = "Select cc_remision From ".$conf->getTbl_cecosto()
	." Where cc_codigo = ".$rowH['cc_codigo']." And "
	." id_empresa = ".$rowH['id_empresa'];
$res_cc = $db->ejecutar($sql_cc);
$row_cc = mysqli_fetch_array($res_cc);
// La actualizamos en 1, para proxima remision
$sql_ccup = "Update ".$conf->getTbl_cecosto()." Set "
	." cc_remision = ".($row_cc[0]+1)
	." Where cc_codigo = ".$rowH['cc_codigo']." And "
	." id_empresa = ".$rowH['id_empresa'];
$db->ejecutar($sql_ccup);
*/
// Matamos el ciclo en el encabezado
/*$sql_up_enc = "Update ".$conf->getTbl_prehreq()." Set "
	." prehreq_estado = 7 "
	." Where id_prehreq=".$_GET['id'];*/
// Ponemos el estado de creado
/*$sql_st = "Insert Into ".$conf->getTbl_prehreq_stat()." Set "
	."id_prehreq = ".$_GET['id'].", "
	."prehreq_stat = 7, "
	."prehreq_stat_desc = '".$conf->getEstado('7')."', "
	."prehreq_stat_fecha = '".date("Y-m-d")."', "
	."prehreq_stat_hora = '".date("H:i:s")."', "
	."prehreq_stat_usuario = '".$_SESSION['u']."'";
$db->ejecutar($sql_st);
$db->ejecutar($sql_up_enc);*/
// Detalle
$sqlD = "Select * From ".$conf->getTbl_predreq()
	." Where id_prehreq = ".$_GET['id'];
	//." And predreq_cantidad_aut > 0";
$runD = $db->ejecutar($sqlD);
$cuantos = mysqli_num_rows($runD);
$paginas = ceil(Round(($cuantos/40), 1));
if($paginas <= 0) {
	$paginas = 1;
}
	while ($row = mysqli_fetch_array($runD)) {
		++$lns;
		/*
		 * Debemos dejar costeado el movimiento
		 */
		try {
		$sql_costo = "Select prod_prom_movil From ".$conf->getTbl_producto()
			." Where prod_codigo = '".$row['prod_codigo']."'";
		$res_costo = $db->ejecutar($sql_costo);
		$costo = 0.00;
		if(mysqli_num_rows($res_costo) > 0){
			$row_costo = mysqli_fetch_array($res_costo);
			$costo = $row_costo[0];
		}
		// Ponemos costo y cerramos el ciclo
		$sql = "Update ".$conf->getTbl_predreq()." Set "
			//." predreq_estado = 7, "
		 	." predreq_costo = ".$costo.", "
			." predreq_remision = ".$rowH['prehreq_numero_remision'].", "
			." predreq_remision_fecha = '".date('Y-m-d H:i:s')."' "
			." Where id_predreq = ".$row['id_predreq'];
		$db->ejecutar($sql);
		} catch(Exception $e) {
			$pdf->writeHTML($e->getMessage(), true, 0, true, 0);
		}
		if($cabeza == '0') {
			// set font
			$pdf->SetFont('courier', '', 16);
			$logo = '
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td><img src="logoimpressa.jpg" width="101" height="31" /></td>
						<td align="center" valign="center">NOTA DE REMISION No. '.$rowH['prehreq_numero_remision'].'</td>
					</tr>
				</table>
			';
			$pdf->writeHTML($logo, true, 0, true, 0);
			// set font
			$pdf->SetFont('courier', '', 10);
			$encabezado = '
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td width="50%">Departamento: <b>'.$rowH['cc_descripcion'].'</b></td>
					<td width="50%">Fecha y Hora: <b>'.$rowH['prehreq_fecha_remision']." ".$rowH['prehreq_hora_remision'].'</b></td>
				</tr>
					<td colspan="2">Solicitado por: <b>'.$rowH['prehreq_usuario'].'</b></td>
				<tr>
				</tr>
			</table>
			';
			$pdf->writeHTML($encabezado, true, 0, true, 0);
			$detalle = '
			<table border="1" width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="20%" align="center">CODIGO</td>
					<td width="15%" align="center">CANTIDAD</td>
					<td width="65%" align="center">DESCRIPCION</td>
				</tr>
			';
			$cabeza = '1';
		}
		$detalle .='
				<tr>
					<td width="20%">'.$row['prod_codigo'].'</td>
					<td width="15%">'.$row['predreq_cantidad_aut'].'</td>
					<td width="65%">'.$row['predreq_descripcion'].'</td>
				</tr>
				';
		if($lns == 40){
			$detalle .='</table>';
			$pdf->writeHTML($detalle, true, 0, true, 0);
			$patas = '
				<br />
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td align="center">______________________</td>
						<td align="center">                  </td>
						<td align="center">______________________</td>
					</tr>
					<tr>
						<td align="center">Prepara Fecha y Hora</td>
						<td align="center"></td>
						<td align="center">Recibe Fecha y Hora</td>
					</tr>
					<tr>
						<td><i>Original</i></td>
						<td colspan="2" align="right"><i>Pagina '.$pdf->getPage().' de '.$paginas.'</i></td>
					</tr>
				</table>
				';
			$pdf->writeHTML($patas, true, 0, true, 0);
			$lns = 0;
			$cabeza = '0';
			$pdf->AddPage();
		}
	}
	if($lns > 0){
		$detalle .='</table>';
		$pdf->writeHTML($detalle, true, 0, true, 0);
		$patas = '';
		for ($i=$lns; $i<=40; $i++) {
			$patas .= '<br />';
		}
		$patas .= '
				<br />
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td align="center">______________________</td>
						<td align="center">                  </td>
						<td align="center">______________________</td>
					</tr>
					<tr>
						<td align="center">Prepara Fecha y Hora</td>
						<td align="center"></td>
						<td align="center">Recibe Fecha y Hora</td>
					</tr>
					<tr>
						<td><i>Original</i></td>
						<td colspan="2" align="right"><i>Pagina '.$pdf->getPage().' de '.$paginas.'</i></td>
					</tr>
				</table>
				';
		$pdf->writeHTML($patas, true, 0, true, 0);
		// Copiamos
		//$pdf->writeHTML('<br><br><br><br>', true, 0, true, 0);
		// set font
		/*$pdf->SetFont('courier', '', 16);
		$pdf->writeHTML($logo, true, 0, true, 0);
		// set font
		$pdf->SetFont('courier', '', 10);
		$pdf->writeHTML($encabezado, true, 0, true, 0);
		$pdf->writeHTML($detalle, true, 0, true, 0);
		$patas = '';
		for ($i=$lns; $i<=30; $i++) {
			$patas .= '<br>';
		}
		$patas .= '
				<br>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td align="center">______________________</td>
						<td align="center">                  </td>
						<td align="center">______________________</td>
					</tr>
					<tr>
						<td align="center">Prepara Fecha y Hora</td>
						<td align="center"></td>
						<td align="center">Recibe Fecha y Hora</td>
					</tr>
					<tr>
						<td><i>Copia</i></td>
						<td colspan="2" align="right"><i>Pagina '.$pdf->getPage().' de '.$paginas.'</i></td>
					</tr>
				</table>
				';
		$pdf->writeHTML($patas, true, 0, true, 0);*/
	}
// print a block of text using Write()

// ---------------------------------------------------------
//Close and output PDF document
//ob_start();
$name='NR_'.$rowH['prehreq_numero_remision'].'.PDF';
$pdf->Output($name, 'D');
//ob_end_flush();
//============================================================+
// END OF FILE
//============================================================+
?>
<?php
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
$pdf->SetTitle('Solicitud de Compra');
$pdf->SetSubject('Impresion');
$pdf->SetKeywords('solicitud, compra, sics, sicys, IMPRESSA');

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
/* Encabezado */
$sql = "Select a.ci_numero, a.ci_enc_fecha, a.ci_enc_hora, b.emp_nombre,c.cc_codigo, c.cc_descripcion, a.ci_observacion, a.id_empresa, a.id_cc, a.id_ci, a.prod_usuario, a.ci_estado From ci_enc a"
		." Join ".$conf->getTbl_empresa()." b On b.id_empresa = a.id_empresa"
		." Join ".$conf->getTbl_cecosto()." c On c.id_empresa = a.id_empresa And c.id_cc = a.id_cc"
		." Where a.id_ci = ".$_GET['id'];
$run = $db->ejecutar($sql);
$rowH = mysqli_fetch_array($run);
/* Marcamos de Impresa */
if($rowH['ci_estado'] == '1') {
	$sql_up = "Update ci_enc "
			."Set ci_estado=2"
			." Where id_ci=".$rowH['id_ci'];
	$db->ejecutar($sql_up);
	$sql_upd = "Update ci_det "
			."Set ci_det_estado=2"
			." Where id_ci=".$rowH['id_ci'];
	$db->ejecutar($sql_upd);
}
/* Detalle */
$sqlD = "Select * From ci_det"
	." Where id_ci = ".$_GET['id'];
$runD = $db->ejecutar($sqlD);
$cuantos = mysqli_num_rows($runD);
$paginas = ceil(($cuantos/11));
	while ($row = mysqli_fetch_array($runD)) {
		++$lns;
		if($cabeza == '0') {
			// set font and size
			$pdf->SetFont('courier', '', 14);
			$logo = '
				<table border="0" width="100%" cellpadding="0" cellspaing="0">
					<tr>
						<td width="25%" ><img src="logoimpressa.jpg" width="101" height="31" /></td>
						<td width="55%" align="center" valign="center"><b>SOLICITUD DE CONSUMO INTERNO</b></td>
						<td width="20%" align="center"><b>No. '.$rowH['ci_numero'].'</b></td>
					</tr>
				</table>
			';
			$pdf->writeHTML($logo, true, 0, true, 0);
			// set font
			$pdf->SetFont('courier', '', 10);
			$encabezado = '
			<table border="0" width="100%" cellpadding="0" cellspaing="0">
				<tr>
					<td>Sucursal: <b>'.$rowH['cc_descripcion'].' ( CC: '.$rowH['cc_codigo'].' )'.'</b></td>
					<td align="right">Fecha :<b>'.$rowH['ci_enc_fecha'].' '.$rowH['ci_enc_hora'].'</b></td>
				</tr>
				<tr>
					<td colspan="2">Concepto del gasto: <b>'.$rowH['ci_observacion'].'</b></td>
				</tr>
			</table>
			';
			$pdf->writeHTML($encabezado, true, 0, true, 0);
			$detalle = '
			<table border="1" width="100%" cellpadding="0" cellspaing="0">
				<tr>
					<td width="10%" align="center">CANTIDAD</td>
					<td width="20%" align="center">CODIGO</td>
					<td width="50%" align="center">DESCRIPCION</td>
					<td width="10%" align="center" style="font-size: 18px;">PRECIO UNITARIO</td>
					<td width="10%" align="center">VALOR</td>
				</tr>
			';
			$cabeza = '1';
		}
		$detalle .='
				<tr>
					<td width="10%" align="center">'.$row['ci_det_cantidad'].'</td>
					<td width="20%" align="center">'.$row['prod_codigo'].'</td>
					<td width="50%" align="center">'.$row['prod_descripcion'].'</td>
					<td width="10%">&nbsp;</td>
					<td width="10%">&nbsp;</td>
				</tr>
				';
		if($lns == 11){
			$detalle .='</table>';
			$pdf->writeHTML($detalle, true, 0, true, 0);
			$patas = '
				<br />
				<table border="0" width="100%" cellpadding="0" cellspaing="0">
					<tr>
						<td width="50%" align="center">SOLICITANTE <u>&nbsp;&nbsp;&nbsp;'.$rowH['prod_usuario'].'&nbsp;&nbsp;&nbsp;</u></td>
						<td width="50%" align="center">AUTORIZADO __________________</td>
					</tr>
					<tr>
						<td width="50%" align="center">FIRMA Y NOMBRE</td>
						<td width="50%" align="center">GERENCIA ADMINISTRATIVA / GERENCIA GENERAL</td>
					</tr>
					<tr>
						<td><i>Original</i></td>
						<td align="right"><i>Pagina '.$pdf->getPage().' de '.$paginas.'</i></td>
					</tr>
				</table>
				<br /><br />
				';
			// set font and size
			$pdf->SetFont('courier', '', 8);
			$pdf->writeHTML($patas, true, 0, true, 0);
			// Copiamos
			
			// set font
			$pdf->SetFont('courier', '', 12);
			$pdf->writeHTML($logo, true, 0, true, 0);
			// set font
			$pdf->SetFont('courier', '', 10);
			$pdf->writeHTML($encabezado, true, 0, true, 0);
			$pdf->writeHTML($detalle, true, 0, true, 0);
			$patas = '
				<br />
				<table border="0" width="100%" cellpadding="0" cellspaing="0">
					<tr>
						<td width="50%" align="center">SOLICITANTE <u>&nbsp;&nbsp;&nbsp;'.$rowH['prod_usuario'].'&nbsp;&nbsp;&nbsp;</u></td>
						<td width="50%" align="center">AUTORIZADO __________________</td>
					</tr>
					<tr>
						<td width="50%" align="center">FIRMA Y NOMBRE</td>
						<td width="50%" align="center">GERENCIA ADMINISTRATIVA / GERENCIA GENERAL</td>
					</tr>
					<tr>
						<td><i>Copia</i></td>
						<td align="right"><i>Pagina '.$pdf->getPage().' de '.$paginas.'</i></td>
					</tr>
				</table>
				';
			$pdf->SetFont('courier', '', 8);
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
		for ($i=$lns; $i<=11; $i++) {
			$patas .= '<br />';
		}
		$patas .= '
				<br />
				<table border="0" width="100%" cellpadding="0" cellspaing="0">
					<tr>
						<td width="50%" align="center">SOLICITANTE <u>&nbsp;&nbsp;&nbsp;'.$rowH['prod_usuario'].'&nbsp;&nbsp;&nbsp;</u></td>
						<td width="50%" align="center">AUTORIZADO __________________</td>
					</tr>
					<tr>
						<td width="50%" align="center">FIRMA Y NOMBRE</td>
						<td width="50%" align="center">GERENCIA ADMINISTRATIVA / GERENCIA GENERAL</td>
					</tr>
					<tr>
						<td><i>Original</i></td>
						<td align="right"><i>Pagina '.$pdf->getPage().' de '.$paginas.'</i></td>
					</tr>
				</table>
				<br /><br />
				';
		// set font and size
		$pdf->SetFont('courier', '', 8);
		$pdf->writeHTML($patas, true, 0, true, 0);
		// Copiamos
		
		// set font
		$pdf->SetFont('courier', '', 12);
		$pdf->writeHTML($logo, true, 0, true, 0);
		// set font
		$pdf->SetFont('courier', '', 10);
		$pdf->writeHTML($encabezado, true, 0, true, 0);
		$pdf->writeHTML($detalle, true, 0, true, 0);
		$patas = '';
		for ($i=$lns; $i<=11; $i++) {
			$patas .= '<br />';
		}
		$patas .= '
				<br />
				<table border="0" width="100%" cellpadding="0" cellspaing="0">
					<tr>
						<td width="50%" align="center">SOLICITANTE <u>&nbsp;&nbsp;&nbsp;'.$rowH['prod_usuario'].'&nbsp;&nbsp;&nbsp;</u></td>
						<td width="50%" align="center">AUTORIZADO __________________</td>
					</tr>
					<tr>
						<td width="50%" align="center">FIRMA Y NOMBRE</td>
						<td width="50%" align="center">GERENCIA ADMINISTRATIVA / GERENCIA GENERAL</td>
					</tr>
					<tr>
						<td><i>Copia</i></td>
						<td align="right"><i>Pagina '.$pdf->getPage().' de '.$paginas.'</i></td>
					</tr>
				</table>
				';
		// set font and size
		$pdf->SetFont('courier', '', 8);
		$pdf->writeHTML($patas, true, 0, true, 0);
	}
// print a block of text using Write()

// ---------------------------------------------------------
//Close and output PDF document
$name='SOLCI-'.$rowH['ci_numero'].'.PDF';
$pdf->Output($name, 'D');
//ob_start();
//header('Location: http://192.168.40.4/sics/?c=ci&a=inicio&id=12');
//header('Location: /sics/class/PHPMailer/sendCI.php?'.$rowH['id_ci']);
//ob_end_flush();
//============================================================+
// END OF FILE
//============================================================+
?>
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
$pdf->SetTitle('Requisicion de Sumnistro');
$pdf->SetSubject('Impresion');
$pdf->SetKeywords('Requisicion, Suministros, IMPRESSA');

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

$sql = "Select a.prehreq_numero_req, a.prehreq_fecha, a.prehreq_hora, b.emp_nombre,c.cc_codigo, c.cc_descripcion From ".$conf->getTbl_prehreq()." a"
		." Join ".$conf->getTbl_empresa()." b On b.id_empresa = a.id_empresa"
		." Join ".$conf->getTbl_cecosto()." c On c.id_empresa = a.id_empresa And c.id_cc = a.id_cc"
		." Where a.id_prehreq = ".$_GET['id'];
$run = $db->ejecutar($sql);
$rowH = mysqli_fetch_array($run);

$sqlD = "Select * From ".$conf->getTbl_predreq()
	." Where id_prehreq = ".$_GET['id'];
$runD = $db->ejecutar($sqlD);
$cuantos = mysqli_num_rows($runD);
$paginas = ceil(($cuantos/11));
	while ($row = mysqli_fetch_array($runD)) {
		++$lns;
		if($cabeza == '0') {
			// set font
			$pdf->SetFont('courier', '', 16);
			$logo = '
				<table border="0" width="100%" cellpadding="0" cellspaing="0">
					<tr>
						<td><img src="logoimpressa.jpg" width="101" height="31" /></td>
						<td align="center" valign="center"><b>REQUISICION DE SUMINISTROS</b></td>
					</tr>
				</table>
			';
			$pdf->writeHTML($logo, true, 0, true, 0);
			// set font
			$pdf->SetFont('courier', '', 10);
			$encabezado = '
			<table border="0" width="100%" cellpadding="0" cellspaing="0">
				<tr>
					<td>Departamento: <b>'.$rowH['cc_descripcion'].'</b></td>
					<td align="right">Fecha :<b>'.$rowH['prehreq_fecha'].' '.$rowH['prehreq_hora'].'</b></td>
				</tr>
				<tr>
					<td>Numero #: <b>'.$rowH['prehreq_numero_req'].'</b></td>
					<td align="right">Centro de Costos :<b>'.$rowH['cc_codigo'].'</b></td>
				</tr>
			</table>
			';
			$pdf->writeHTML($encabezado, true, 0, true, 0);
			$detalle = '
			<table border="1" width="100%" cellpadding="0" cellspaing="0">
				<tr>
					<td width="20%" align="center">CODIGO</td>
					<td width="10%" align="center">CANTIDAD</td>
					<td width="10%" align="center">UNIDAD</td>
					<td width="60%" align="center">DESCRIPCION</td>
				</tr>
			';
			$cabeza = '1';
		}
		$detalle .='
				<tr>
					<td width="20%">'.$row['prod_codigo'].'</td>
					<td width="10%">'.$row['predreq_cantidad_aut'].'</td>
					<td width="10%">'.$row['predreq_unidad'].'</td>
					<td width="60%">'.$row['predreq_descripcion'].'</td>
				</tr>
				';
		if($lns == 11){
			$detalle .='</table>';
			$pdf->writeHTML($detalle, true, 0, true, 0);
			$patas = '
				<br />
				<table border="0" width="100%" cellpadding="0" cellspaing="0">
					<tr>
						<td align="center">__________________</td>
						<td align="center">__________________</td>
						<td align="center">__________________</td>
					</tr>
					<tr>
						<td align="center">SOLICITANTE</td>
						<td align="center">GERENTE DE AREA</td>
						<td align="center">AUTORIZADO</td>
					</tr>
					<tr>
						<td><i>Original</i></td>
						<td colspan="2" align="right"><i>Pagina '.$pdf->getPage().' de '.$paginas.'</i></td>
					</tr>
				</table>
				<br /><br />
				';
			$pdf->writeHTML($patas, true, 0, true, 0);
			// Copiamos
			// set font
			$pdf->SetFont('courier', '', 16);
			$pdf->writeHTML($logo, true, 0, true, 0);
			// set font
			$pdf->SetFont('courier', '', 10);
			$pdf->writeHTML($encabezado, true, 0, true, 0);
			$pdf->writeHTML($detalle, true, 0, true, 0);
			$patas = '
				<br />
				<table border="0" width="100%" cellpadding="0" cellspaing="0">
					<tr>
						<td align="center">__________________</td>
						<td align="center">__________________</td>
						<td align="center">__________________</td>
					</tr>
					<tr>
						<td align="center">SOLICITANTE</td>
						<td align="center">GERENTE DE AREA</td>
						<td align="center">AUTORIZADO</td>
					</tr>
					<tr>
						<td><i>Copia</i></td>
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
		for ($i=$lns; $i<=11; $i++) {
			$patas .= '<br />';
		}
		$patas .= '
				<br />
				<table border="0" width="100%" cellpadding="0" cellspaing="0">
					<tr>
						<td align="center">__________________</td>
						<td align="center">__________________</td>
						<td align="center">__________________</td>
					</tr>
					<tr>
						<td align="center">SOLICITANTE</td>
						<td align="center">GERENTE DE AREA</td>
						<td align="center">AUTORIZADO</td>
					</tr>
					<tr>
						<td><i>Original</i></td>
						<td colspan="2" align="right"><i>Pagina '.$pdf->getPage().' de '.$paginas.'</i></td>
					</tr>
				</table>
				<br /><br />
				';
		$pdf->writeHTML($patas, true, 0, true, 0);
		// Copiamos
		// set font
		$pdf->SetFont('courier', '', 16);
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
						<td align="center">__________________</td>
						<td align="center">__________________</td>
						<td align="center">__________________</td>
					</tr>
					<tr>
						<td align="center">SOLICITANTE</td>
						<td align="center">GERENTE DE AREA</td>
						<td align="center">AUTORIZADO</td>
					</tr>
					<tr>
						<td><i>Copia</i></td>
						<td colspan="2" align="right"><i>Pagina '.$pdf->getPage().' de '.$paginas.'</i></td>
					</tr>
				</table>
				';
		$pdf->writeHTML($patas, true, 0, true, 0);
	}
// print a block of text using Write()

// ---------------------------------------------------------
//Close and output PDF document
//ob_start();
$name='REQ-'.$rowH['prehreq_numero_req'].'.PDF';
$pdf->Output($name, 'I');
//ob_end_flush();
//============================================================+
// END OF FILE
//============================================================+
?>
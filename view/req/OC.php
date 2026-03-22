<?php
// Include the main TCPDF library (search for installation path).
require_once('../../tcpdf/tcpdf.php');
// Primero algunas variables de configuracion
require_once '../../Configuracion.php';
// Manejo de Base de Datos
require_once '../../DB.php';
$db = DB::getInstance ();
$conf = Configuracion::getInstance ();
// OC
$host_oc = $conf->getHostDB();
$user_oc = $conf->getUserDB();
$pass_oc =$conf->getPassDB();
$bd_oc = $conf->getDbprov();
$empresa_oc = $_GET['e_oc'];
// O.C.
$tbl_oc = "orden" . $empresa_oc;
$tbl_prov_oc = "provee".$empresa_oc;
$link = mysqli_connect ( $host_oc, $user_oc, $pass_oc, true );
mysqli_select_db ( $bd_oc, $link );
$sql_oc = "Select CODPROVEE From ".$tbl_oc." Where NORDEN = '" . $_GET['o'] . "'";
$run_oc = mysqli_query ( $sql_oc, $link );
$row_oc = mysqli_fetch_array( $run_oc );
// Proveedor
$sql_p = "Select PROVEEDOR From ".$tbl_prov_oc
	." Where CODIGO = '".$row_oc['CODPROVEE']."'";
$res_p = mysqli_query($sql_p, $link);
$row_p = mysqli_fetch_array($res_p);
mysqli_close ( $link );
// SICS
$sql = "Select a.*,b.cc_descripcion,c.prehreq_numero_req From ".$conf->getTbl_predreq()." a"
	." Join ".$conf->getTbl_cecosto()." b"
	." On b.id_cc = a.id_cc And b.id_empresa = a.id_empresa"
	." Join ".$conf->getTbl_prehreq()." c"
	." On c.id_prehreq = a.id_prehreq"
	." Where a.predreq_numero_oc = ".$_GET['o']." and a.id_empresa = ".$_GET['e']
	." Order by a.prod_codigo";
$run = $db->ejecutar($sql);

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        $image_file = 'logoimpressa.jpg';
        $this->Image($image_file, 10, 10, '', '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('courier', 'B', 20);
        // Title
        $this->Cell(0, 15, 'SOPORTE DE ORDEN DE COMPRA', 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-30);
        // Set font
        $this->SetFont('courier', 'I', 8);
        // Page number
        $this->Cell(0, 0, '___________________________', 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->Ln();
        $this->Cell(0, 0, 'Autorizado', 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->Ln(4);
        $this->Cell(0, 10, 'Pagina '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

// create new PDF document
$pdf = new MYPDF('L', PDF_UNIT, 'LETTER', true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistemas y Tecnologia');
$pdf->SetTitle('Soporte de OC');
$pdf->SetSubject('Orden de Compra');
$pdf->SetKeywords('OC, Soporte');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// ---------------------------------------------------------

// set font
$pdf->SetFont('courier', '', 9);

// add a page
$pdf->AddPage();

// set some text to print
$txt = '
		<br><br>
		<table width="100%" cellpadding="5" cellspacing="5">
			<tr>
				<td>No. O.C.:<u><b>'.$_GET['o'].'</b></u></td>
			</tr>
			<tr>
				<td>Proveedor: <b>('.$row_oc['CODPROVEE'].') <u>'.$row_p['PROVEEDOR'].'</u></b></td>
			</tr>
			<tr>
				<td>Fecha y Hora de Emision: <u><b>'.date('d-m-Y h:i:s A').'</b></u></td>
			</tr>
		<table>
		<table border="1" width="100%">
			<tr>
				<td width="14%" align="center">CODIGO</td>
				<td width="7%" align="center">CANT</td>
				<td width="27%" align="center">DESCRIPCION</td>
				<td width="7%" align="center"># REQ</td>
				<td width="25%" align="center">CENTRO DE COSTO</td>
				<td width="10%" align="center">PREC.UNIT</td>
				<td width="10%" align="center">TOTAL</td>
			</tr>';
		while ($row = mysqli_fetch_array($run)){
		$txt .= '
			<tr>
				<td width="14%">'.$row['prod_codigo'].'</td>
				<td width="7%">'.$row['predreq_cantidad_aut'].'</td>
				<td width="27%">'.$row['predreq_descripcion'].'</td>
				<td width="7%">'.$row['prehreq_numero_req'].'</td>
				<td width="25%">'.$row['cc_descripcion'].'</td>
				<td width="10%">'.$row['predreq_prec_uni'].'</td>
				<td width="10%">'.$row['predreq_total'].'</td>
			</tr>';
		}
		$txt .='
		</table>
		';

// print a block of text using Write()
$pdf->writeHTML($txt);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('oc.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
?>

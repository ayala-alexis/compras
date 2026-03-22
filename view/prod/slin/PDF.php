<?php
// Include the main TCPDF library (search for installation path).
require_once('../../../tcpdf/tcpdf.php');
// Primero algunas variables de configuracion
require_once '../../../Configuracion.php';
// Manejo de Base de Datos
require_once '../../../DB.php';
$db = DB::getInstance ();
$conf = Configuracion::getInstance ();
// PDF
$host_oc = $conf->getHostDB();
$user_oc = $conf->getUserDB();
$pass_oc =$conf->getPassDB();
$bd_oc = $conf->getDbprov();

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
    	
    	// Nombre de Sublinea
    	$link = mysqli_connect("192.168.43.120", "root", "my47gmc");
    	$sql = "Select sl_descripcion From sublinea"
    	." Where sl_linea=".$_REQUEST['e_l']." and sl_sublinea=".$_REQUEST['e_sl'];
    	$run = mysqli_query($sql, $link);
    	$row_sl = mysqli_fetch_array($run);
    	
        // Logo
        $image_file = 'logoimpressa.jpg';
        $this->Image($image_file, 10, 10, '', '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('courier', 'B', 20);
        // Title
        $this->Cell(0, 8, 'FORMATO DE COTIZACION', 0, false, 'R', 0, '', 0, false, 'T', 'M');
        // Set font
        $this->SetFont('courier', 'B', 10);
        $this->Ln();
        $this->Cell(0, 7, $row_sl['sl_descripcion'], 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        //$this->SetY(-30);
    	$this->SetY(-20);
        // Set font
        $this->SetFont('courier', 'I', 8);
        // Page number
        //$this->Cell(0, 10, '___________________________', 0, false, 'L', 0, '', 0, false, 'T', 'M');
        //$this->Ln();
        //$this->Cell(0, 0, 'Contacto', 0, false, 'L', 0, '', 0, false, 'T', 'M');
        //$this->Ln(4);
        $this->Cell(0, 10, 'Pagina '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

// SICS
$sql = "Select * From ".$conf->getTbl_producto()
." Where sl_linea=".$_REQUEST['e_l']." and sl_sublinea=".$_REQUEST['e_sl']." Order By prod_codigo";
$run = $db->ejecutar($sql);

// create new PDF document
$pdf = new MYPDF('P', PDF_UNIT, 'LETTER', true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistemas y Tecnologia');
$pdf->SetTitle('Soporte de PDF');
$pdf->SetSubject('Orden de Compra');
$pdf->SetKeywords('PDF, Soporte');

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
				<td>Fecha y Hora de Emision: <u><b>'.date('d-m-Y h:i:s A').'</b></u></td>
			</tr>
			<tr>
				<td>Vigencia Hasta: __________________________</td>
			</tr>		
		<table>
		<table border="1" width="100%" cellpadding="5" cellspacing="0">
			<thead>
			<tr>
				<th width="20%" align="center">ITEM</th>
				<th width="50%" align="center">DESCRIPCION</th>
				<th width="10%" align="center">DETALLE</th>
				<th width="10%" align="center">MAYOREO</th>
				<th width="10%" align="center">MINIMO</th>
			</tr>
			</thead>
			<tbody>';
		while ($row = mysqli_fetch_array($run)){
		$txt .= '
			<tr>
				<td width="20%">'.$row['sl_linea'].''.$row['sl_sublinea'].' '.$row['prod_codigo'].'</td>
				<td width="50%">'.$row['prod_descripcion'].'</td>
				<td width="10%">&nbsp;</td>
				<td width="10%">&nbsp;</td>
				<td width="10%">&nbsp;</td>
			</tr>';
		}
		$txt .='
		</tbody>
		</table>
		';

// print a block of text using Write()
$pdf->writeHTML($txt);

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('PDF.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
?>

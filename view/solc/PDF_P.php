<link rel="stylesheet" type="text/css" href="../../css/bootstrap.css" />
<?php
require_once '../../tcpdf/tcpdf.php';
// Primero algunas variables de configuracion
require_once '../../Configuracion.php';
// Manejo de Base de Datos
require_once '../../DB.php';
$db = DB::getInstance ();
$conf = Configuracion::getInstance ();
// CABEZA
$sql = "Select a.prehsol_numero_sol, a.prehsol_fecha, a.prehsol_hora, b.emp_nombre,c.cc_codigo, c.cc_descripcion From ".$conf->getTbl_prehsol()." a"
	." Join ".$conf->getTbl_empresa()." b On b.id_empresa = a.id_empresa"
	." Join ".$conf->getTbl_cecosto()." c On c.id_empresa = a.id_empresa And c.id_cc = a.id_cc"
	." Where a.id_prehsol = ".$_GET['id'];
$run = $db->ejecutar($sql);
$rowH = mysqli_fetch_array($run);



// CUERPO
$sqlD = "Select * From ".$conf->getTbl_predsol()
	." Where id_prehsol = ".$_GET['id'];
$runD = $db->ejecutar($sqlD);

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {
    //Page header
    public function Header() {
        // Logo
        $image_file = 'logoimpressa.jpg';
        $this->Image($image_file, 10, 10, '', '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        $db = DB::getInstance ();
        $conf = Configuracion::getInstance ();
        // CABEZA
        $sql = "Select a.prehsol_numero_sol, a.prehsol_fecha, a.prehsol_hora, b.emp_nombre,c.cc_codigo, c.cc_descripcion From ".$conf->getTbl_prehsol()." a"
        	." Join ".$conf->getTbl_empresa()." b On b.id_empresa = a.id_empresa"
        	." Join ".$conf->getTbl_cecosto()." c On c.id_empresa = a.id_empresa And c.id_cc = a.id_cc"
        	." Where a.id_prehsol = ".$_GET['id'];
        $run = $db->ejecutar($sql);
        $rowH = mysqli_fetch_array($run);
        // Set font
        $this->SetFont('courier', 'B', 20);
        // Title
        $this->Cell(0, 15, 'SOLICITUD DE COMPRAS No. '. $rowH['prehsol_numero_sol'], 0, false, 'C', 0, '', 0, false, 'T', 'M');

        $this->Ln(20);
        $this->SetFont('courier', '', 10);
        // MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
        // Multicell test
        $this->MultiCell(100, 0, 'Departamento :'.$rowH['cc_descripcion'], 0, 'L', 0, 0, '', '', true);
        $this->MultiCell(0, 0, 'Fecha :'.$rowH['prehsol_fecha'], 0, 'R', 0, 0, '', '', true);
        $this->Ln();
        $this->MultiCell(123, 0, 'Seccion :'.$rowH['cc_codigo'], 0, 'L', 0, 0, '', '', true);
        $this->MultiCell(0, 0, 'COTIZACIONES', 0, 'C', 0, 0, '', '', true, 0, false, true, 0, 'M');
        //
        $this->SetTopMargin($this->GetY()+5);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-25);
        // Set font
        $this->SetFont('courier', 'I', 10);

        $db = DB::getInstance ();
        $conf = Configuracion::getInstance ();
        // ESTADOS
        $sql_auth = "Select prehsol_stat, prehsol_stat_usuario From "
        		.$conf->getTbl_prehsol_stat()
        		." Where id_prehsol = ".$_GET['id'];
        $run_auth = $db->ejecutar($sql_auth);
        while ($row_auth = mysqli_fetch_array($run_auth)) {
        	if($row_auth[0] == 1){
        		$creado = $row_auth[1];
        	}
        	if($row_auth[0] == 2){
        		$autorizado = $row_auth[1];
        	}
        }
		$this->setCellMargins(2, 1, 2, 1);
        $this->MultiCell(55, 0, $creado, 'B', 'C', 0, 0, '', '', true);
        $this->MultiCell(55, 0, $autorizado, 'B', 'C', 0, 0, '', '', true);
        $this->MultiCell(55, 0, '_______________________', 0, 'C', 0, 0, '', '', true);
        //$this->MultiCell(55, 0, '_______________________', 0, 'C', 0, 0, '', '', true);
        //$this->MultiCell(55, 0, '_______________________', 0, 'C', 0, 0, '', '', true);
        //$this->MultiCell(55, 0, '_______________________', 0, 'C', 0, 0, '', '', true);
        $this->Ln();
        $this->MultiCell(55, 0, 'SOLICITANTE', 0, 'C', 0, 0, '', '', true);
        $this->MultiCell(55, 0, 'GERENTE DE AREA', 0, 'C', 0, 0, '', '', true);
        $this->MultiCell(55, 0, 'AUTORIZADO', 0, 'C', 0, 0, '', '', true);
        $this->Ln();
        // Page number
        $this->SetFont('courier', 'I', 7);
        $this->Cell(0, 10, 'Pagina '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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
$pdf->SetFont('courier', '', 8);

// add a page
$pdf->AddPage();

$detalle = '
		<table class="table table-condensed" border="1" width="100%">
			<tr>
				<td rowspan="2" width="10%" align="center">CANTIDAD</td>
				<td rowspan="2" width="10%" align="center">UNIDAD</td>
				<td rowspan="2" width="50%" align="center">DESCRIPCION</td>
				<td colspan="3" width="30%"></td>
			</tr>
			<tr>
				<td width="10%"></td>
				<td width="10%"></td>
				<td width="10%"></td>
			</tr>
			';
while ($row = mysqli_fetch_array($runD)) {
	$detalle .='
			<tr>
				<td align="center">'.$row['predsol_cantidad'].'</td>
				<td>'.$row['predsol_unidad'].'</td>
				<td>'.$row['predsol_descripcion'].'</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			';
}
$detalle .= '
			</table>
			';

// print a block of text using writeHTML()
$pdf->writeHTML($detalle, true, 0, true, 0);

// ---------------------------------------------------------
//Close and output PDF document
ob_start();
$name='SOLC-'.$rowH['prehsol_numero_sol'].'.PDF';
$pdf->Output($name, 'I');
ob_end_flush();
//============================================================+
// END OF FILE
//============================================================+
?>
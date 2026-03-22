<link rel="stylesheet" type="text/css" href="../../css/bootstrap.css" />
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
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 028');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(10, PDF_MARGIN_TOP, 10);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


// ---------------------------------------------------------

$pdf->SetDisplayMode('fullpage', 'SinglePage', 'UseNone');

// set font
$pdf->SetFont('times', 'B', 20);

$pdf->AddPage('P', 'ANNENV_A8');
$pdf->Cell(0, 0, 'ANNENV_A8 PORTRAIT', 1, 1, 'C');

$pdf->AddPage('L', 'ANNENV_A8');
$pdf->Cell(0, 0, 'ANNENV_A8 LANDSCAPE', 1, 1, 'C');

$pdf->AddPage('P', 'P3');
$pdf->Cell(0, 0, 'P3 PORTRAIT', 1, 1, 'C');

$pdf->AddPage('L', 'P3');
$pdf->Cell(0, 0, 'P3 LANDSCAPE', 1, 1, 'C');

$pdf->AddPage('P', 'P4');
$pdf->Cell(0, 0, 'P4 PORTRAIT', 1, 1, 'C');

$pdf->AddPage('L', 'P4');
$pdf->Cell(0, 0, 'P4 LANDSCAPE', 1, 1, 'C');

$pdf->AddPage('P', 'P5');
$pdf->Cell(0, 0, 'P5 PORTRAIT', 1, 1, 'C');

$pdf->AddPage('L', 'P5');
$pdf->Cell(0, 0, 'P5 LANDSCAPE', 1, 1, 'C');


// --- test backward editing ---


$pdf->setPage(1, true);
$pdf->SetY(50);
$pdf->Cell(0, 0, 'ANNENV_A8 test', 1, 1, 'C');

$pdf->setPage(2, true);
$pdf->SetY(50);
$pdf->Cell(0, 0, 'ANNENV_A8 test', 1, 1, 'C');

$pdf->setPage(3, true);
$pdf->SetY(50);
$pdf->Cell(0, 0, 'P3 test', 1, 1, 'C');

$pdf->setPage(4, true);
$pdf->SetY(50);
$pdf->Cell(0, 0, 'P3 test', 1, 1, 'C');

$pdf->setPage(5, true);
$pdf->SetY(50);
$pdf->Cell(0, 0, 'P4 test', 1, 1, 'C');

$pdf->setPage(6, true);
$pdf->SetY(50);
$pdf->Cell(0, 0, 'P4 test', 1, 1, 'C');

$pdf->setPage(7, true);
$pdf->SetY(40);
$pdf->Cell(0, 0, 'P5 test', 1, 1, 'C');

$pdf->setPage(8, true);
$pdf->SetY(40);
$pdf->Cell(0, 0, 'P5 test', 1, 1, 'C');

$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
ob_start();
$pdf->Output('example_028.pdf', 'I');
ob_end_flush();

//============================================================+
// END OF FILE
//============================================================+

?>
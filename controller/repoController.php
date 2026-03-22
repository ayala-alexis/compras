<?php
/*
 * Listado de solicitudes
 */
function solc(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$estados = $conf->getEstadosSC();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	$key_sess = $_SESSION['u'].$_GET['id'].$_GET['c'].$_GET['a'];
	if(!isset($_GET['page'])) {
		$page=1;
		unset($_SESSION[$key_sess]) ;
		$_SESSION[$key_sess] = '';
	} else {
		$page=$_GET['page'];
	}
	if($_POST) {
		$_SESSION[$key_sess] = $_POST;
	}
	$solcs = Consulta($page, $_SESSION[$key_sess]);
	include 'view/repo/solicitudes.php';
}
/*
 * Listado de requisiciones
 */
function req(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/reqModel.php';
	$conf = Configuracion::getInstance();
	$estados = $conf->getEstados();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	$key_sess = $_SESSION['u'].$_GET['id'].$_GET['c'].$_GET['a'];
	if(!isset($_GET['page'])) {
		$page=1;
		unset($_SESSION[$key_sess]) ;
		$_SESSION[$key_sess] = '';
	} else {
		$page=$_GET['page'];
	}
	if($_POST) {
		$_SESSION[$key_sess] = $_POST;
	}
	$reqs = Consulta($page, $_SESSION[$key_sess]);
	include 'view/repo/requisiciones.php';
}
/*
 * Listado de ordenes
 */
function orden(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$estados = $conf->getEstados();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	$key_sess = $_SESSION['u'].$_GET['id'].$_GET['c'].$_GET['a'];
	if(!isset($_GET['page'])) {
		$page=1;
		unset($_SESSION[$key_sess]) ;
		$_SESSION[$key_sess] = '';
	} else {
		$page=$_GET['page'];
	}
	if($_POST) {
		$_SESSION[$key_sess] = $_POST;
	}
	$solcs = ConsultaOrdenes($page, $_SESSION[$key_sess]);
	include 'view/repo/ordenes.php';
}
/*
 * Listado de ordenes desde solicitud de compra
*/
function ordensc(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$estados = $conf->getEstados();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	$key_sess = $_SESSION['u'].$_GET['id'].$_GET['c'].$_GET['a'];
	if(!isset($_GET['page'])) {
		$page=1;
		unset($_SESSION[$key_sess]) ;
		$_SESSION[$key_sess] = '';
	} else {
		$page=$_GET['page'];
	}
	if($_POST) {
		$_SESSION[$key_sess] = $_POST;
	}
	$solcs = ConsultaOrdenesSC($page, $_SESSION[$key_sess]);
	include 'view/repo/ordenessc.php';
}
/*
 * Reportes a excel 
*/
function xls(){
	/** Error reporting */
	/*error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);*/
	include 'class/PHPExcel.php';
	include 'class/PHPExcel/Writer/PDF.php';
	
	PHPExcel_Settings::CHART_RENDERER_JPGRAPH;
	PHPExcel_Shared_Font::setTrueTypeFontPath('C:/Windows/Fonts/');
	PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_APPROX);
	// A choix, librairie tcPDF, mPDF ou domPDF
	$rendererName = PHPExcel_Settings::PDF_RENDERER_TCPDF;
	$rendererLibrary = 'tcpdf';
	$rendererLibraryPath =  "class/" . $rendererLibrary;
	//  Here's the magic: you __tell__ PHPExcel what rendering engine to use
	//     and where the library is located in your filesystem
	if (!PHPExcel_Settings::setPdfRenderer(
			$rendererName,
			$rendererLibraryPath
	)) {
		die('NOTICE: Please set the $rendererName and $rendererLibraryPath values' .
				'<br />' .
				'at the top of this script as appropriate for your directory structure'
		);
	}
	$db = DB::getInstance();
	$conf = Configuracion::getInstance();
	if($_POST) {
		$phpExcel = new PHPExcel();
		switch ($_GET['via']) {
			case 1:
				$phpExcel->getProperties()
					->setCreator('Impressa Repuestos')
					->setTitle('Compras por Centro de Costo')
					->setLastModifiedBy('Sistemas de Tecnologia')
					->setDescription('Reporte de compras por centro de costo')
					->setSubject('Compras por Centro de Costo')
					->setKeywords('centro costo compras sics impressa proveeduria')
					->setCategory('reportes');
					
					//$phpExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1,1);
					
					$phpExcel->setActiveSheetIndex(0);
					$phpExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
					$phpExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
					$phpExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
					$phpExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
					$phpExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
					$phpExcel->getActiveSheet()->getSheetView()->setView(PHPExcel_Worksheet_SheetView::SHEETVIEW_PAGE_LAYOUT);
					
				$sql = "SELECT
					empresa.emp_nombre,
					cecosto.cc_descripcion,
					Sum(predreq.predreq_total) AS total
					FROM
					predreq
					INNER JOIN cecosto ON cecosto.id_cc = predreq.id_cc
					INNER JOIN empresa ON empresa.id_empresa = cecosto.id_empresa
					INNER JOIN prehreq ON prehreq.id_prehreq = predreq.id_prehreq
					WHERE
					predreq.predreq_estado IN (5, 6, 7) AND
					prehreq.prehreq_fecha >= '".$_REQUEST['feini']."' AND prehreq.prehreq_fecha <= '".$_REQUEST['fefin']."'
					GROUP BY
					empresa.emp_nombre,
					cecosto.cc_descripcion
					ORDER BY
					total DESC";
				$run = $db->ejecutar($sql);
				$phpExcelSheet = $phpExcel->getSheet(0);
				$phpExcelSheet->setTitle("datos");
				$phpExcelSheet->setCellValue("a1", "Compras por Centro de Costo del ".$_REQUEST['feini']." al ".$_REQUEST['fefin']);
				$phpExcelSheet->setCellValue("a2", "Empresa");
				$phpExcelSheet->setCellValue("b2", "Centro de Costo");
				$phpExcelSheet->setCellValue("c2", "Valor");
								
				$header = 'a1:c1';
				$phpExcelSheet->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00ffff00');
				$style = array(
						'font' => array('bold' => true,),
						'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
				);
				$phpExcelSheet->getStyle($header)->applyFromArray($style);
				
				$i = 3;
				$sumas = 0;
				while ($registro = mysqli_fetch_object ($run)) {
					$phpExcel->setActiveSheetIndex(0)
					->setCellValue('A'.$i, $registro->emp_nombre)
					->setCellValue('B'.$i, $registro->cc_descripcion)
					->setCellValue('C'.$i, $registro->total);
					$sumas = $sumas + $registro->total;
					$i++;
				}
				$phpExcel->setActiveSheetIndex(0)->setCellValue('A'.$i, "Total");
				$phpExcel->setActiveSheetIndex(0)->setCellValue('C'.$i, $sumas);
				
				$phpExcel->getActiveSheet()
				->getStyle("C1:C".$i)
				->getNumberFormat()
				->setFormatCode('#,##0.00');
								
				// Calculate the column widths
				foreach(range('A', 'C') as $columnID) {
					$phpExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
				}
				$phpExcel->getActiveSheet()->calculateColumnWidths();
				
				$phpExcelSheet->mergeCells("a1:c1");
				
				// Set setAutoSize(false) so that the widths are not recalculated
				foreach(range('A', 'C') as $columnID) {
					$phpExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(false);
				}
				
				$i--;
				$dsl=array(
						new PHPExcel_Chart_DataSeriesValues('String', 'datos!$B$2', NULL, 1),
						new PHPExcel_Chart_DataSeriesValues('String', 'datos!$C$2', NULL, 1),
				);
				$xal=array(
						new PHPExcel_Chart_DataSeriesValues('String', 'datos!$B$3:$B$'.$i, NULL, 90),
				);
				$dsv=array(
						new PHPExcel_Chart_DataSeriesValues('Number', 'datos!$C$3:$C$'.$i, NULL, 90),
				);
				$ds=new PHPExcel_Chart_DataSeries(
						PHPExcel_Chart_DataSeries::TYPE_BARCHART,
						PHPExcel_Chart_DataSeries::GROUPING_STANDARD,
						range(0, count($dsv)-1),
						$dsl,
						$xal,
						$dsv
				);
				$pa=new PHPExcel_Chart_PlotArea(NULL, array($ds));
				$legend=new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
				$title=new PHPExcel_Chart_Title($_REQUEST['feini'] . ' a ' . $_REQUEST['fefin']);
				$chart= new \PHPExcel_Chart(
						'chart1',
						$title,
						$legend,
						$pa,
						true,
						0,
						NULL,
						NULL
				);
				$chart->setTopLeftPosition('E1');
				$chart->setBottomRightPosition('P21');
				$phpExcelSheet->addChart($chart);				
				$writer = PHPExcel_IOFactory::createWriter($phpExcel, 'PDF');
				$writer->setIncludeCharts(true);
				$phpExcel->setActiveSheetIndex(0);
				$file = $_SESSION['u'].'_cc.pdf';
				$writer->save(getcwd().'\\tmp\\'.$file);
				echo '<div class="bs-calltoaction bs-calltoaction-info">
                    <div class="row">
						<div class="col-md-3 cta-button">
							<a target="_new" class="btn btn-lg btn-block btn-info" href="tmp/'.$file.'">Descargar archivo</a>
                        </div>
                        <div class="col-md-9 cta-contents">
                            <h1 class="cta-title">Archivo generado.</h1>
                            <div class="cta-desc">
                                <p></p>
                            </div>
                        </div>
                     </div>
                </div>';
				break;
			case 2:
					$phpExcel->getProperties()
					->setCreator('Impressa Repuestos')
					->setTitle('Compras por Proveedor')
					->setLastModifiedBy('Sistemas de Tecnologia')
					->setDescription('Reporte de compras por proveedor')
					->setSubject('Compras por Proveedor')
					->setKeywords('proveedor compras sics impressa proveeduria')
					->setCategory('reportes');
					$sql = "SELECT
						proveedor.prov_nombre,
						Sum(predreq.predreq_total) AS total
						FROM
						predreq
						INNER JOIN proveedor ON proveedor.id_proveedor = predreq.id_proveedor
						INNER JOIN prehreq ON prehreq.id_prehreq = predreq.id_prehreq
						WHERE
						predreq.predreq_estado IN (5, 6, 7) AND
						prehreq.prehreq_fecha BETWEEN '".$_REQUEST['feini']."' AND '".$_REQUEST['fefin']."'
						GROUP BY
						proveedor.prov_nombre
						ORDER BY
						total DESC";
					$run = $db->ejecutar($sql);
					$phpExcelSheet = $phpExcel->getSheet(0);
					$phpExcelSheet->setTitle("datos");
					$phpExcelSheet->setCellValue("a1", "Compras por Proveedor del ".$_REQUEST['feini']." al ".$_REQUEST['fefin']);
					$phpExcelSheet->setCellValue("a2", "Proveedor");
					$phpExcelSheet->setCellValue("b2", "Valor");
									
					$header = 'a1:b1';
					$phpExcelSheet->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00ffff00');
					$style = array(
							'font' => array('bold' => true,),
							'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
					);
					$phpExcelSheet->getStyle($header)->applyFromArray($style);
				
					$i = 3;
					$sumas = 0;
					while ($registro = mysqli_fetch_object ($run)) {
						$phpExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i, $registro->prov_nombre)
						->setCellValue('B'.$i, $registro->total);
						$sumas = $sumas + $registro->total; 
						$i++;
					}
					$phpExcel->setActiveSheetIndex(0)->setCellValue('A'.$i, "Total");
					$phpExcel->setActiveSheetIndex(0)->setCellValue('B'.$i, $sumas);
					
					$phpExcel->getActiveSheet()
					->getStyle("B1:B".$i)
					->getNumberFormat()
					->setFormatCode('#,##0.00');
					
					// Calculate the column widths
					foreach(range('A', 'B') as $columnID) {
						$phpExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
					}
					$phpExcel->getActiveSheet()->calculateColumnWidths();
						
					// Set setAutoSize(false) so that the widths are not recalculated
					foreach(range('A', 'B') as $columnID) {
						$phpExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(false);
					}
					
					$phpExcelSheet->mergeCells("a1:b1");
					
					$dsl=array(
							new PHPExcel_Chart_DataSeriesValues('String', 'datos!$A$2', NULL, 1),
							new PHPExcel_Chart_DataSeriesValues('String', 'datos!$B$2', NULL, 1),
					);
					$xal=array(
							new PHPExcel_Chart_DataSeriesValues('String', 'datos!$A$3:$A$'.$i, NULL, 90),
					);
					$dsv=array(
							new PHPExcel_Chart_DataSeriesValues('Number', 'datos!$B$3:$B$'.$i, NULL, 90),
					);
					$ds=new PHPExcel_Chart_DataSeries(
							PHPExcel_Chart_DataSeries::TYPE_BARCHART,
							PHPExcel_Chart_DataSeries::GROUPING_STANDARD,
							range(0, count($dsv)-1),
							$dsl,
							$xal,
							$dsv
					);
					$pa=new PHPExcel_Chart_PlotArea(NULL, array($ds));
					$legend=new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
					$title=new PHPExcel_Chart_Title($_REQUEST['feini'] . ' a ' . $_REQUEST['fefin']);
					$chart= new \PHPExcel_Chart(
							'chart1',
							$title,
							$legend,
							$pa,
							true,
							0,
							NULL,
							NULL
					);
					$chart->setTopLeftPosition('E1');
					$chart->setBottomRightPosition('P21');
					$phpExcelSheet->addChart($chart);
					$writer = PHPExcel_IOFactory::createWriter($phpExcel, 'PDF');
					$writer->setIncludeCharts(true);
					$phpExcel->setActiveSheetIndex(0);
					$file = $_SESSION['u'].'_prv.pdf';
					$writer->save(getcwd().'\\tmp\\'.$file);
					echo '<div class="bs-calltoaction bs-calltoaction-info">
                    <div class="row">
						<div class="col-md-3 cta-button">
							<a target="_new" class="btn btn-lg btn-block btn-info" href="tmp/'.$file.'">Descargar archivo</a>
                        </div>
                        <div class="col-md-9 cta-contents">
                            <h1 class="cta-title">Archivo generado!</h1>
                            <div class="cta-desc">
                                <p></p>
                            </div>
                        </div>
                     </div>
                </div>';
				break;
			case 3: // Articulo
					$phpExcel->getProperties()
						->setCreator('Impressa Repuestos')
						->setTitle('Compras por Articulo')
						->setLastModifiedBy('Sistemas de Tecnologia')
						->setDescription('Reporte de compras por articulo')
						->setSubject('Compras por Articulo')
						->setKeywords('articulo compras sics impressa proveeduria')
						->setCategory('reportes');
						
					$phpExcel->setActiveSheetIndex(0);
					$phpExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
					$phpExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
					$phpExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
					$phpExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
					$phpExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
					$phpExcel->getActiveSheet()->getSheetView()->setView(PHPExcel_Worksheet_SheetView::SHEETVIEW_PAGE_LAYOUT);
					
					$sql = "SELECT
						sublinea.sl_linea,
						sublinea.sl_sublinea,
						sublinea.sl_descripcion,
						predreq.prod_codigo,
						predreq.predreq_descripcion,
						Sum(predreq.predreq_total) AS total
						FROM
						prehreq
						INNER JOIN predreq ON predreq.id_prehreq = prehreq.id_prehreq
						INNER JOIN sublinea ON predreq.sl_linea = sublinea.sl_linea AND predreq.sl_sublinea = sublinea.sl_sublinea
						WHERE
						predreq.predreq_estado IN (5, 6, 7) AND
						prehreq.prehreq_fecha BETWEEN '".$_REQUEST['feini']."' AND '".$_REQUEST['fefin']."'
						GROUP BY
						sublinea.sl_linea,
						sublinea.sl_sublinea,
						sublinea.sl_descripcion,
						predreq.prod_codigo,
						predreq.predreq_descripcion";
					//echo $sql;
					$run = $db->ejecutar($sql);
					$phpExcelSheet = $phpExcel->getSheet(0);
					$phpExcelSheet->setTitle("datos");
					$phpExcelSheet->setCellValue("a1", "Compras por Articulo del ".$_REQUEST['feini']." al ".$_REQUEST['fefin']);
					$phpExcelSheet->setCellValue("a2", "Linea");
					$phpExcelSheet->setCellValue("b2", "Sublinea");
					$phpExcelSheet->setCellValue("c2", "Nombre");
					$phpExcelSheet->setCellValue("d2", "Producto");
					$phpExcelSheet->setCellValue("e2", "Descripcion");
					$phpExcelSheet->setCellValue("f2", "Total");
					
						$header = 'a1:f1';
						$phpExcelSheet->getStyle($header)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00ffff00');
						$style = array(
								'font' => array('bold' => true,),
								'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
						);
						$phpExcelSheet->getStyle($header)->applyFromArray($style);
					
						$i = 3;
						$sumas = 0;
						while ($registro = mysqli_fetch_object ($run)) {
							$phpExcel->setActiveSheetIndex(0)
							->setCellValue('A'.$i, $registro->sl_linea)
							->setCellValue('B'.$i, $registro->sl_sublinea)
							->setCellValue('C'.$i, $registro->sl_descripcion)
							->setCellValue('D'.$i, $registro->prod_codigo)
							->setCellValue('E'.$i, $registro->predreq_descripcion)
							->setCellValue('F'.$i, $registro->total);
							$sumas = $sumas + $registro->total;
							$i++;
						}
						$phpExcel->setActiveSheetIndex(0)->setCellValue('E'.$i, "Total");
						$phpExcel->setActiveSheetIndex(0)->setCellValue('F'.$i, $sumas);
						
						$phpExcel->getActiveSheet()->getStyle("A1:F".$i)->getFont()->setSize(9);
						
						$phpExcel->getActiveSheet()
						->getStyle("F1:F".$i)
						->getNumberFormat()
						->setFormatCode('#,##0.00');
						
						$phpExcelSheet->mergeCells("A1:F1");
						
						// Calculate the column widths
						foreach(range('A', 'F') as $columnID) {
							$phpExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
						}
						$phpExcel->getActiveSheet()->calculateColumnWidths();
							
						// Set setAutoSize(false) so that the widths are not recalculated
						foreach(range('A', 'F') as $columnID) {
							$phpExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(false);
						}
						
						
						$dsl=array(
								new PHPExcel_Chart_DataSeriesValues('String', 'datos!$D$2', NULL, 1),
								new PHPExcel_Chart_DataSeriesValues('String', 'datos!$F$2', NULL, 1),
						);
						$xal=array(
								new PHPExcel_Chart_DataSeriesValues('String', 'datos!$D$3:$D$'.$i, NULL, 90),
						);
						$dsv=array(
								new PHPExcel_Chart_DataSeriesValues('Number', 'datos!$F$3:$F$'.$i, NULL, 90),
						);
						$ds=new PHPExcel_Chart_DataSeries(
								PHPExcel_Chart_DataSeries::TYPE_BARCHART,
								PHPExcel_Chart_DataSeries::GROUPING_STANDARD,
								range(0, count($dsv)-1),
								$dsl,
								$xal,
								$dsv
						);
						$pa=new PHPExcel_Chart_PlotArea(NULL, array($ds));
						$legend=new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
						$title=new PHPExcel_Chart_Title($_REQUEST['feini'] . ' a ' . $_REQUEST['fefin']);
						$chart= new \PHPExcel_Chart(
								'chart1',
								$title,
								$legend,
								$pa,
								true,
								0,
								NULL,
								NULL
						);
						$chart->setTopLeftPosition('G1');
						$chart->setBottomRightPosition('S21');
						$phpExcelSheet->addChart($chart);
						
						$phpExcel->getActiveSheet()
						->getHeaderFooter()->setOddFooter('&R&F Page &P / &N');
						$phpExcel->getActiveSheet()
						->getHeaderFooter()->setEvenFooter('&R&F Page &P / &N');
						
						$writer = PHPExcel_IOFactory::createWriter($phpExcel, 'PDF');
						$writer->setIncludeCharts(true);
						$file = $_SESSION['u'].'_prod.pdf';
						$writer->save(getcwd().'\\tmp\\'.$file);
						echo '<div class="bs-calltoaction bs-calltoaction-info">
		                    <div class="row">
								<div class="col-md-3 cta-button">
									<a target="_new" class="btn btn-lg btn-block btn-info" href="tmp/'.$file.'">Descargar archivo</a>
		                        </div>
		                        <div class="col-md-9 cta-contents">
		                            <h1 class="cta-title">Archivo generado!</h1>
		                            <div class="cta-desc">
		                                <p></p>
		                            </div>
		                        </div>
		                     </div>
		                </div>';
				break;
				
			case 4:
					$phpExcel->getProperties()
					->setCreator('Impressa Repuestos')
					->setTitle('Compras por Empres')
					->setLastModifiedBy('Sistemas de Tecnologia')
					->setDescription('Reporte de compras por empresa')
					->setSubject('Compras por Empresa')
					->setKeywords('empresa compras sics impressa proveeduria')
					->setCategory('reportes');
					
					$phpExcel->setActiveSheetIndex(0);
					$phpExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
					$phpExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
					$phpExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
					$phpExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
					$phpExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
					$phpExcel->getActiveSheet()->getSheetView()->setView(PHPExcel_Worksheet_SheetView::SHEETVIEW_PAGE_LAYOUT);
					
					$sql = "SELECT
						empresa.emp_nombre,
						Sum(predreq.predreq_total) AS total
						FROM
						prehreq
						INNER JOIN predreq ON predreq.id_prehreq = prehreq.id_prehreq
						INNER JOIN empresa ON predreq.id_empresa = empresa.id_empresa
						WHERE
						predreq.predreq_estado IN (5, 6, 7) AND
						prehreq.prehreq_fecha BETWEEN '".$_REQUEST['feini']."' AND '".$_REQUEST['fefin']."'
						GROUP BY
						empresa.emp_nombre";
					$run = $db->ejecutar($sql);
					$phpExcelSheet = $phpExcel->getSheet(0);
					$phpExcelSheet->setTitle("datos");
					$phpExcelSheet->setCellValue("a1", "Compras por Empresa del ".$_REQUEST['feini']." al ".$_REQUEST['fefin']);
					$phpExcelSheet->setCellValue("a2", "Empresa");
					$phpExcelSheet->setCellValue("b2", "Valor");
				
					$header = 'a1:b1';
					$phpExcelSheet->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00ffff00');
					$style = array(
							'font' => array('bold' => true,),
							'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
					);
					$phpExcelSheet->getStyle($header)->applyFromArray($style);
				
					$i = 3;
					$sumas = 0;
					while ($registro = mysqli_fetch_object ($run)) {
						$phpExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i, $registro->emp_nombre)
						->setCellValue('B'.$i, $registro->total);
						$sumas = $sumas + $registro->total;
						$i++;
					}
					$phpExcel->setActiveSheetIndex(0)->setCellValue('A'.$i, "Total");
					$phpExcel->setActiveSheetIndex(0)->setCellValue('B'.$i, $sumas);
					
					$phpExcel->getActiveSheet()
					->getStyle("B1:B".$i)
					->getNumberFormat()
					->setFormatCode('#,##0.00');
						
					// Calculate the column widths
					foreach(range('A', 'B') as $columnID) {
					    $phpExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
					}
					$phpExcel->getActiveSheet()->calculateColumnWidths();
					
					// Set setAutoSize(false) so that the widths are not recalculated
					foreach(range('A', 'B') as $columnID) {
					    $phpExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(false);
					}
					
					$phpExcelSheet->mergeCells("A1:B1");
					
					$dsl=array(
							new PHPExcel_Chart_DataSeriesValues('String', 'datos!$A$2', NULL, 1),
							new PHPExcel_Chart_DataSeriesValues('String', 'datos!$B$2', NULL, 1),
					);
					$xal=array(
							new PHPExcel_Chart_DataSeriesValues('String', 'datos!$A$3:$A$'.$i, NULL, 90),
					);
					$dsv=array(
							new PHPExcel_Chart_DataSeriesValues('Number', 'datos!$B$3:$B$'.$i, NULL, 90),
					);
					$ds=new PHPExcel_Chart_DataSeries(
							PHPExcel_Chart_DataSeries::TYPE_BARCHART,
							PHPExcel_Chart_DataSeries::GROUPING_STANDARD,
							range(0, count($dsv)-1),
							$dsl,
							$xal,
							$dsv
					);
					$pa=new PHPExcel_Chart_PlotArea(NULL, array($ds));
					$legend=new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
					$title=new PHPExcel_Chart_Title($_REQUEST['feini'] . ' a ' . $_REQUEST['fefin']);
					$chart= new \PHPExcel_Chart(
							'chart1',
							$title,
							$legend,
							$pa,
							true,
							0,
							NULL,
							NULL
					);
					
					$chart->setTopLeftPosition('E1');
					$chart->setBottomRightPosition('P21');
					$phpExcelSheet->addChart($chart);
					
					$writer = PHPExcel_IOFactory::createWriter($phpExcel, 'PDF');
					$writer->setIncludeCharts(true);
					$phpExcel->setActiveSheetIndex(0);
					$file = $_SESSION['u'].'_emp.pdf';
					$writer->save(getcwd().'\\tmp\\'.$file);
					echo '<div class="bs-calltoaction bs-calltoaction-info">
	                    <div class="row">
							<div class="col-md-3 cta-button">
								<a target="_new" class="btn btn-lg btn-block btn-info" href="tmp/'.$file.'">Descargar archivo</a>
	                        </div>
	                        <div class="col-md-9 cta-contents">
	                            <h1 class="cta-title">Archivo generado!</h1>
	                            <div class="cta-desc">
	                                <p></p>
	                            </div>
	                        </div>
	                     </div>
	                </div>';
				break;
			/*
			 * Listado para autorizacion de consumos internos
			 */
			case 5:
					$phpExcel->getProperties()
					->setCreator('Impressa Repuestos')
					->setTitle('Consumos para Autoriacion')
					->setLastModifiedBy('Sistemas de Tecnologia')
					->setDescription('Reporte de consumo interno para autorizacion')
					->setSubject('Consumos Internos para Autorizacion')
					->setKeywords('cnosumo interno uso sics impressa proveeduria')
					->setCategory('reportes');
						
					//$phpExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1,1);
						
					$phpExcel->setActiveSheetIndex(0);
					$phpExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
					$phpExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
					$phpExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
					$phpExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
					$phpExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
					$phpExcel->getActiveSheet()->getSheetView()->setView(PHPExcel_Worksheet_SheetView::SHEETVIEW_PAGE_LAYOUT);
						
					$sql = "SELECT
					empresa.emp_nombre,
					cecosto.cc_descripcion,
					Sum(predreq.predreq_total) AS total
					FROM
					predreq
					INNER JOIN cecosto ON cecosto.id_cc = predreq.id_cc
					INNER JOIN empresa ON empresa.id_empresa = cecosto.id_empresa
					INNER JOIN prehreq ON prehreq.id_prehreq = predreq.id_prehreq
					WHERE
					predreq.predreq_estado IN (5, 6, 7) AND
					prehreq.prehreq_fecha >= '".$_REQUEST['feini']."' AND prehreq.prehreq_fecha <= '".$_REQUEST['fefin']."'
					GROUP BY
					empresa.emp_nombre,
					cecosto.cc_descripcion
					ORDER BY
					total DESC";
					$run = $db->ejecutar($sql);
					$phpExcelSheet = $phpExcel->getSheet(0);
					$phpExcelSheet->setTitle("datos");
					$phpExcelSheet->setCellValue("a1", "Compras por Centro de Costo del ".$_REQUEST['feini']." al ".$_REQUEST['fefin']);
					$phpExcelSheet->setCellValue("a2", "Empresa");
					$phpExcelSheet->setCellValue("b2", "Centro de Costo");
					$phpExcelSheet->setCellValue("c2", "Valor");
				
					$header = 'a1:c1';
					$phpExcelSheet->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00ffff00');
					$style = array(
							'font' => array('bold' => true,),
							'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
					);
					$phpExcelSheet->getStyle($header)->applyFromArray($style);
				
					$i = 3;
					$sumas = 0;
					while ($registro = mysqli_fetch_object ($run)) {
						$phpExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i, $registro->emp_nombre)
						->setCellValue('B'.$i, $registro->cc_descripcion)
						->setCellValue('C'.$i, $registro->total);
						$sumas = $sumas + $registro->total;
						$i++;
					}
					$phpExcel->setActiveSheetIndex(0)->setCellValue('A'.$i, "Total");
					$phpExcel->setActiveSheetIndex(0)->setCellValue('C'.$i, $sumas);
				
					$phpExcel->getActiveSheet()
					->getStyle("C1:C".$i)
					->getNumberFormat()
					->setFormatCode('#,##0.00');
				
					// Calculate the column widths
					foreach(range('A', 'C') as $columnID) {
						$phpExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
					}
					$phpExcel->getActiveSheet()->calculateColumnWidths();
				
					$phpExcelSheet->mergeCells("a1:c1");
				
					// Set setAutoSize(false) so that the widths are not recalculated
					foreach(range('A', 'C') as $columnID) {
						$phpExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(false);
					}
				
					$i--;
					$dsl=array(
							new PHPExcel_Chart_DataSeriesValues('String', 'datos!$B$2', NULL, 1),
							new PHPExcel_Chart_DataSeriesValues('String', 'datos!$C$2', NULL, 1),
					);
					$xal=array(
							new PHPExcel_Chart_DataSeriesValues('String', 'datos!$B$3:$B$'.$i, NULL, 90),
					);
					$dsv=array(
							new PHPExcel_Chart_DataSeriesValues('Number', 'datos!$C$3:$C$'.$i, NULL, 90),
					);
					$ds=new PHPExcel_Chart_DataSeries(
							PHPExcel_Chart_DataSeries::TYPE_BARCHART,
							PHPExcel_Chart_DataSeries::GROUPING_STANDARD,
							range(0, count($dsv)-1),
							$dsl,
							$xal,
							$dsv
					);
					$pa=new PHPExcel_Chart_PlotArea(NULL, array($ds));
					$legend=new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
					$title=new PHPExcel_Chart_Title($_REQUEST['feini'] . ' a ' . $_REQUEST['fefin']);
					$chart= new \PHPExcel_Chart(
							'chart1',
							$title,
							$legend,
							$pa,
							true,
							0,
							NULL,
							NULL
					);
					$chart->setTopLeftPosition('E1');
					$chart->setBottomRightPosition('P21');
					$phpExcelSheet->addChart($chart);
					$writer = PHPExcel_IOFactory::createWriter($phpExcel, 'PDF');
					$writer->setIncludeCharts(true);
					$phpExcel->setActiveSheetIndex(0);
					$file = $_SESSION['u'].'_cc.pdf';
					$writer->save(getcwd().'\\tmp\\'.$file);
					echo '<div class="bs-calltoaction bs-calltoaction-info">
                    <div class="row">
						<div class="col-md-3 cta-button">
							<a target="_new" class="btn btn-lg btn-block btn-info" href="tmp/'.$file.'">Descargar archivo</a>
                        </div>
                        <div class="col-md-9 cta-contents">
                            <h1 class="cta-title">Archivo generado.</h1>
                            <div class="cta-desc">
                                <p></p>
                            </div>
                        </div>
                     </div>
                </div>';
				break;
			default:
				echo $_GET['via'];
				break;
		}
	} else {
		include 'view/repo/export.php';
	}
}
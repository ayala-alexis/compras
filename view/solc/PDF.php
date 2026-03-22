<?php

$Salto=false;
header("Content-Type: text/html;charset=utf-8");
require_once '../../tcpdf/tcpdf.php';
// Primero algunas variables de configuracion
require_once '../../Configuracion.php';
// Manejo de Base de Datos
require_once '../../DB.php';
$db = DB::getInstance ();
$conf = Configuracion::getInstance ();
/*
 * 
 */

$id_solicitud = $_GET['id'];
$sql = "Select a.prehsol_numero_sol, a.prehsol_fecha, a.prehsol_hora, b.emp_nombre,c.cc_codigo, c.cc_descripcion, prehsol_obs1, prehsol_gestion_observacion, prehsol_aprobacion_categoria, prehsol_aprobacion_categoria_usuario, prehsol_gestion_nivel2_usuario From ".$conf->getTbl_prehsol()." a"
	." Join ".$conf->getTbl_empresa()." b On b.id_empresa = a.id_empresa"
	." Join ".$conf->getTbl_cecosto()." c On c.id_empresa = a.id_empresa And c.id_cc = a.id_cc"
	." Where a.id_prehsol = ".$_GET['id'];
$run = $db->ejecutar($sql);
$rowH = mysqli_fetch_array($run);

function get_status($estado,$descr){
	$estado = (int)$estado;
	if($estado==1){
		$descr = "Solicitante";
	}else if($estado==4){
		$descr = "Enviado a Proveeduria";
	}
	return strtoupper($descr);
}

function getTraza($idprehsol){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select 
				id_prehsol,
				prehsol_stat,
				prehsol_stat_desc,
				max(prehsol_stat_fecha) prehsol_stat_fecha,
				max(prehsol_stat_hora) prehsol_stat_hora,
				prehsol_stat_usuario,
				prehsol_devolver 
				From ".$conf->getTbl_prehsol_stat()." 
				Where id_prehsol =".$idprehsol." and prehsol_stat>0 
				group by id_prehsol,
				prehsol_stat,
				prehsol_stat_desc,
				prehsol_stat_usuario,
				prehsol_devolver
				order by prehsol_stat_fecha,prehsol_stat_hora ";
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array[] = "";
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();

	}
	return $result;
}

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

	//Page header
	public function Header() {
		require_once '../../model/SQLgenerales.php'; 
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select a.prehsol_numero_sol, a.prehsol_fecha, a.prehsol_hora, b.emp_nombre,c.cc_codigo, c.cc_descripcion, prehsol_obs1, a.PREHSOL_MONTO,a.PREHSOL_PROVEEDOR, a.PREHSOL_METODOPAGO, a.ID_CATEGORIA, a.moneda, a.id_empresa From ".$conf->getTbl_prehsol()." a"
			." Join ".$conf->getTbl_empresa()." b On b.id_empresa = a.id_empresa"
			." Join ".$conf->getTbl_cecosto()." c On c.id_empresa = a.id_empresa And c.id_cc = a.id_cc"
			." Where a.id_prehsol = ".$_GET['id'];	
		
		$run = $db->ejecutar($sql);
		$rowH = mysqli_fetch_array($run);

		//echo json_encode($rowH);
		//exit();

		if($rowH['PREHSOL_PROVEEDOR'] != ""){
			$Proveedor = ReturnProveedor($rowH['PREHSOL_PROVEEDOR']);
		}else{
			$Proveedor = "";
		}

		if($rowH['PREHSOL_METODOPAGO'] != ""){
			$metodo_pago = ReturnNombreMetodoPago($rowH['PREHSOL_METODOPAGO']);
		}else{
			$metodo_pago = "";
		}

		$logo = '
				<table border="0" width="100%" cellpadding="0" cellspaing="0">
					<tr>
						<td><img src="logoimpressa.jpg" width="101" height="31" /></td>
						<td align="center" valign="center"><b>SOLICITUD DE COMPRA</b></td>
					</tr>
				</table>
			';
			$this->writeHTML($logo, true, 0, true, 0);
			$this->SetFont('courier', '', 10);
			$encabezado = '
			<table border="0" width="100%" cellpadding="0" cellspaing="0">
				<tr>
					<td>Departamento: <b>'.$rowH['cc_descripcion'].'</b></td>
					<td align="right">Fecha :<b>'.$rowH['prehsol_fecha'].' '.$rowH['prehsol_hora'].'</b></td>
				</tr>
				<tr>
					<td>Numero #: <b>'.$rowH['prehsol_numero_sol'].'</b></td>
					<td align="right">Centro de Costos :<b>'.$rowH['cc_codigo'].'</b></td>
				</tr>
				<tr>
					<td colspan="2">Observacion : <b>'.$rowH['prehsol_obs1'].'</b></td>
				</tr>
				';

			//echo json_encode($rowH['moneda']);
			//exit();

			if($rowH['ID_CATEGORIA']  != "" && $rowH['ID_CATEGORIA'] > 0){
				if($rowH['ID_CATEGORIA']  != "" && $rowH['PREHSOL_PROVEEDOR']  != "" && $rowH['PREHSOL_METODOPAGO']  != ""){
					$encabezado .= '
						<tr>';
						if($rowH[12] == "6"){
							$encabezado .='<td>Monto: <strong>'.$rowH[11].''.$rowH['PREHSOL_MONTO'].'</strong></td>';	
						}else if($rowH[12] == "8" ){
							$encabezado .='<td>Monto: <strong>'.$rowH[11].''.$rowH['PREHSOL_MONTO'].'</strong></td>';	
						}else{
							$encabezado .='<td>Monto: <strong>$'.$rowH['PREHSOL_MONTO'].'</strong></td>';	
						}
											
					$encabezado .='</tr>
					<tr>
						<td>Proveedor: <strong>'.$Proveedor.'</strong></td>					
					</tr>
				';	
				}				
			}

			$encabezado .= '</table>';
			$encabezado .= '
			<table border="1" width="100%" cellpadding="0" cellspaing="0">
				<tr>
					<td width="20%" align="center">CODIGO</td>
					<td width="10%" align="center">CANTIDAD</td>
					<td width="10%" align="center">UNIDAD</td>
					<td width="60%" align="center">DESCRIPCION</td>
				</tr>
			</table>
			';

			if($rowH['ID_CATEGORIA']  != "" && $rowH['ID_CATEGORIA'] > 0){
				if($rowH['ID_CATEGORIA']  != "" && $rowH['PREHSOL_PROVEEDOR']  != "" && $rowH['PREHSOL_METODOPAGO']  != ""){
					$GLOBALS['Salto']=true;
				}				
			}

			$this->writeHTML($encabezado, true, 0, true, 0);
	}
	
	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('helvetica', 'I', 8);
		// Page number
		$this->Cell(0, 10, 'Pagina '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('IMPRESSA REPUESTOS, S.A. de C.V.');
$pdf->SetTitle('Solicitud de Compra');
$pdf->SetSubject('Impresion');
$pdf->SetKeywords('solicitud, compra, sics, sicys, IMPRESSA');

// remove default header/footer
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP+25, PDF_MARGIN_RIGHT);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
/**/
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Contador de lineas
$lns = 0;
$cabeza = '0';

// ---------------------------------------------------------
// add a page
$pdf->AddPage();
/*
 * 
 */
$sqlD = "Select * From ".$conf->getTbl_predsol()
	." Where id_prehsol = ".$_GET['id'];
$runD = $db->ejecutar($sqlD);
$cuantos = mysqli_num_rows($runD);

if($GLOBALS['Salto']==true){
	$detalle = '<br>';
}


$detalle .= '<table border="1" width="100%" style="border-bottom: solid 1px blue;" cellpadding="0" cellspaing="0">';
//$pdf->SetFont('courier', '', 10);
//$pdf->writeHTML($tablaHead, true, 0, true, 0);
$paginas = ceil(($cuantos/11));
while ($row = mysqli_fetch_array($runD)) {
	$detalle .='
			<tr style="border-bottom: solid 1px blue;">
				<td width="20%" style="border-bottom: solid 1px blue;">'.$row['prod_codigo'].'</td>
				<td width="10%" style="border-bottom: solid 1px blue;">'.$row['predsol_cantidad_aut'].'</td>
				<td width="10%" style="border-bottom: solid 1px blue;">'.$row['predsol_unidad'].'</td>
				<td width="60%" style="border-bottom: solid 1px blue;">';
	if(!empty($row['predsol_observacion'])){
		$detalle .= $row['predsol_observacion'];
	} else {
		$detalle .= $row['predsol_descripcion'];
	}
		$detalle .='</td>
			</tr>
			';
		//$pdf->writeHTML($detalle, true, 0, true, 0);
		
}
$detalle .='</table>';

$observa_x = trim($rowH['prehsol_gestion_observacion']);
$observa_y = trim($rowH['prehsol_aprobacion_categoria']);
if(strlen($observa_x)) {
	$detalle .= '<p><b>Observacion adicional:</b>'.$rowH['prehsol_gestion_nivel2_usuario'].' - '.$observa_x.'</p>';
}
if(strlen($observa_y)) {
	$detalle .= '<p><b>Observacion adicional:</b> '.$rowH['prehsol_aprobacion_categoria_usuario'].' - '.$observa_y.'</p>';
}

$detalle .='<br /><br /><br /><br /><br /><br />
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
</table>';

$traza = getTraza($id_solicitud);
$detalle .='<br /><br /><br /><br /><br /><br />
<h3 style="margin:0;">Trazabilidad: </h3>
<table border="0" width="100%" cellspacing="5" cellpadding="5">
<tr>';
$estado=0;
foreach ($traza as $t):
	$estado++;
	$sta = (((int)$t['prehsol_stat']==10) ? 'red' : '#E0F9A1');
	$sta = (((int)$t['prehsol_stat']==20) ? 'orange' : $sta);
	$detalle .='
	<td align="left" bgcolor="'.$sta.'" align="top">'.
		'<div style="font-size:19px">'.
			'<b><u>'.get_status($t['prehsol_stat'],$t['prehsol_stat_desc']).'</u></b><br/><br/>'.
	            '<label align="left">'.
	            	'<img src="img/usuario.png" width="12" height="12" border="0"/> '.
	            	'<span>'.$t['prehsol_stat_usuario'].'</span>'.
	            '</label><br/>'.
	            '<span>'.$t['prehsol_stat_fecha'].' '.$t['prehsol_stat_hora'].'</span><br/><br/>'.
				'<span class="info-status"><br>';

					if($estado == 1){
						$detalle .='<i class="glyphicon glyphicon-ok-circle"></i> Solicitado';
					} else if($estado == 3){
						$detalle .='<i class="glyphicon glyphicon-ok-circle"></i> Cotizado';
					} else{
						if((int)$t['prehsol_stat']==10):
							$detalle .='<i class="glyphicon glyphicon-remove"></i> rechazado';
						elseif((int)$t['prehsol_stat']==20):
							$detalle .='<i class="glyphicon glyphicon-arrow-left"></i> Devuelto';
						else:
							$detalle .='<i class="glyphicon glyphicon-ok-circle"></i> Aprobado';
						endif;
					}
				
				$detalle .='</br></span><br/>'.
	            '<span>'.$t['prehsol_devolver'].'</span>'.
		'</div>'.
	'</td>';
endforeach;
$detalle .='</tr>
</table>';

$detalle .='<br>
<table>
	<td>
		<table>
			<tr><td><b>Metodo de pago</b></td></tr>
			<tr><td>Transferencia</td></tr>
			<tr><td>Cheque</td></tr>
			<tr><td>Cheque de gerencia</td></tr>
		</table>
	</td>	
</table>
';

/*
<td>
	<table>
		<tr><b>Condicion de pago</b></tr>
		<tr>
			<td><b>Anticipo</b></td>
			<td><b>Transferencia</b></td>
		</tr>
		<tr>
			<td>Texto libre %</td>
			<td>0</td>
		</tr>
		<tr>
			<td></td>
			<td>7 días</td>
		</tr>
		<tr>
			<td></td>
			<td>15 días</td>
		</tr>
		<tr>
			<td></td>
			<td>30 días</td>
		</tr>
		<tr>
			<td></td>
			<td>60 días</td>
		</tr>					
	</table>
</td>
*/

$pdf->SetFont('courier', '', 10);
$pdf->writeHTML($detalle, true, 0, true, 0);

// ---------------------------------------------------------
//Close and output PDF document
//ob_start();
$name='SOL-'.$rowH['prehsol_numero_sol'].'.PDF';
$pdf->Output($name, 'I');
//ob_end_flush();
//============================================================+
// END OF FILE
//============================================================+
?>
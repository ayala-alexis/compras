<?php
$orden = $_REQUEST['poc'];
$prt = $_REQUEST['r'];
$id_cia = $_REQUEST['cia'];
$id_prov = $_REQUEST['p'];
include_once 'class/numeros.php';
require_once 'tcpdf/config/lang/eng.php';
require_once 'tcpdf/tcpdf.php';
// Primero algunas variables de configuracion
require_once 'Configuracion.php';
// Manejo de Base de Datos
require_once 'DB.php';
$db = DB::getInstance ();
$conf = Configuracion::getInstance ();
/// Pone valores de consulta en un array
function toArray($_Res) {
	while ($fila = mysqli_fetch_array($_Res)) {
		$registros[] = $fila[0];
    }
    return $registros;
}


// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator('IMPRESSA S.A. de C.V.');
$pdf->SetAuthor('Sistemas y Tecnologia');
$pdf->SetTitle('Pre Orden de Compra');
$pdf->SetSubject($orden);
$pdf->SetKeywords('Pre Orden, IMPRESSA, Compra, SICS, SICYS, Sistemas, Tecnologia');


// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(3,20,2);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// ---------------------------------------------------------

// set font
$pdf->SetFont('courier', '', 8);

// add a page
//$pdf->AddPage();
$pdf->AddPage('P','LETTER');


$overflow = 30;

$sql_prt = "Select * From oc_impresiones Where id_empresa = ".$id_cia." And numero_oc='".$orden."'";
$run_prt = $db->ejecutar($sql_prt);
if(mysqli_num_rows($run_prt) <= 0) {
	$impresiones = 1;
	$sql_nuevo_prt = "Insert Into oc_impresiones"
		." Set id_empresa = ".$id_cia.","
		." numero_oc = '".$orden."', "
		." contador = ".$impresiones;
	$db->ejecutar($sql_nuevo_prt);
} else {
	$row_prt = mysqli_fetch_array($run_prt);
	$impresiones = $row_prt[2]+1;
	$sql_actualiza_prt = "Update oc_impresiones"
		." Set contador = ".$impresiones
		." Where id_empresa = ".$id_cia
		." and numero_oc = '".$orden."'";
	$db->ejecutar($sql_actualiza_prt);
}

// Datos de empresa
$_Sql0 = "Select * From ".$conf->getTbl_empresa()." Where id_empresa = ".$id_cia;
$_Qry0 = $db->ejecutar($_Sql0);
$_Row0 = mysqli_fetch_array($_Qry0);
// Codigo de proveedor para buscar nombre de categoria
$_Sql1 = "Select * From ".$conf->getTbl_proveedor()." Where id_proveedor = ".$id_prov;
$_Qry1 = $db->ejecutar($_Sql1);
$_Row1 = mysqli_fetch_array($_Qry1);
// Saca Nombre de Categoria
$_Sql2 = "Select * From ".$conf->getTbl_categoria()." Where id_categoria = '".$_Row1[0]."'";
$_Qry2 = $db->ejecutar($_Sql2);
$_Row2 = mysqli_fetch_array($_Qry2);


$db->conecta_OC();
$sql_count_items = "Select COUNT(DISTINCT CODAS400) From ".$conf->getTmovs().$_Row0["id_empresa_oc"]
	." Where"
	." CODPROVEE = '".$_Row1["prov_codigo"]."'"
	." and NORDEN = '".$orden."'";
$run_count_items = $db->ejecuta_OC($sql_count_items);
$contar = mysqli_fetch_array($run_count_items);
$db->desconecta_OC();

$paginas = $contar[0]/$overflow;
$paginas = ceil($paginas);
if (($contar[0] % $overflow) == 0) {
	$paginas = $paginas+1;
}
?>
<?php
/*
 * Informacion de Empresa
 */
$cabecera .= '<table  width="100%" border="0" cellpadding="0" cellspacing="0">';
if ($_Row0['id_empresa_oc'] == '02') {
	$cabecera .= '<tr>
	<td rowspan="5" width="15%" aligin="center"><img src="Images/MAGICO_.jpg" width="55" height="84" /></td>
	<td width="45%"><b>'.$_Row0['emp_nombre'].'</b></td>';
} else {
	$cabecera .= '<tr>
	<td rowspan="5" width="15%" align="center"><img src="Images/_totem.jpg" width="55" height="84" /></td>
	<td width="45%"><b>'.$_Row0['emp_nombre'].'</b></td>';
}
$cabecera .='<td width="40%"><b>ORDEN DE COMPRA No. '.$orden."&nbsp;&nbsp;";
$cabecera .='</b></td></tr>
	<tr>
		<td width="45%" rowspan="3">'.$_Row0['emp_direccion'].'</td>
		<td width="40%">Registro No. : <b>'.$_Row0['emp_registro'].'</b></td>
	</tr>
	<tr>
		<td width="40%">Giro : <b>'.$_Row0['emp_giro'].'</b></td>
	</tr>
	<tr>
		<td width="40%">Nit : <b>'.$_Row0['emp_nit'].'</b></td>
	</tr>
	<tr>
		<td width="45%">Tel.: '.$_Row0['emp_telefono'].'</td>
		<td width="40%">';
		if ($impresiones <= 1){
			$cabecera .= 'IMPRIME';
		} else {
			$cabecera .= 'REIMPRIME';
		}
		$cabecera .= ': <b>['.$_SESSION['u'].']&nbsp;'.$_SESSION['n'].'</b></td>
	</tr>
	</table>';
/*
 * Informacion De Orden
 */
$cabecera .= '
	<table border="1" width="100%" border="1" cellpadding="2" cellspacing="0">
		<tr>
			<td colspan="4">PROVEEDOR : <b>'.$_Row1['prov_nombre'].'</b></td>
		</tr>
		<tr>
			<td colspan="2">FECHA DE ORDEN: <b>'.date('d/m/Y').'</b></td>
			<td rowspan="2" colspan="2">
				FAX : <b>'.$_Row1['prov_fax'].'</b>
				PBX : <b>'.$_Row1['prov_telefono1'].'</b>
				USUARIO ACTUAL : <b>'.$_SESSION['u'].'</b>
				ATENCION : <b>'.$_Row1['prov_contacto1'].'</b>
				E-MAIL : <b>'.$_Row1['prov_email'].'</b>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td align="center">GESTOR DE COMPRA</td>
			<td align="center">COTIZACION</td>
			<td align="center">CONDICIONES DE PAGO</td>
			<td align="center">SOLICITUD DE COMPRA</td>
		</tr>
		<tr>
			<td align="center"><b>SICS</b></td>
			<td align="center"><b>'.$impresiones.'</b></td>
			<td align="center"><b>'.$_Row1['prov_dias'].'</b></td>
			<td align="center"><b>&nbsp;</b></td>
		</tr>';
		if ($_Row['predreq_estado'] == '*') {
			$cabecera .= '
				<tr>
				<td colspan="4">Anulacion : <b>&nbsp;</b></td>
				</tr>';
}
$cabecera .= '</table>';

// output the HTML content
$pdf->writeHTML($cabecera, true, 0, true, 0);

$p = 1; // Pagina = 1;
$item = 1; // Contador de Items
$sub = 0; // Acumula SubTotal de Orden
$db->conecta_OC();
$_SqlI = "Select"
	." CODAS400,"
	." sum(d.predreq_cantidad_aut) as tunidades,"
	." d.predreq_unidad, d.predreq_descripcion,"
	." d.predreq_prec_uni,"
	." sum(d.predreq_total) as tvalor"
	." From ".$conf->getTmovs().$_Row0["id_empresa_oc"]
	." Where"
	." CODPROVEE = '".$_Row1["prov_codigo"]."'"
	." and NORDEN = '".$orden."'";
/*$_SqlI = "Select  From ".$conf->getTbl_predreq()." d"
	." Where d.predreq_estado = 3"
	." And d.id_empresa = ".$id_cia
	." And d.predreq_numero_oc = '".$orden."'"
	." and d.predreq_cantidad_aut > 0"
	." Group By d.prod_codigo"
	." Order By d.prod_codigo";
$_QryI = $db->ejecutar($_SqlI);*/
$_QryI = $db->ejecuta_OC($_SqlI);
$db->desconecta_OC();
/*
 * Encabezado de Items
 */
$itemD  = '<table width="100%" border="1" cellpadding="1" cellspacing="0">';
$itemD .= '<tr><td width="13%"><b>CODIGO</b></td>';
$itemD .= '<td width="6%"><b>CANT</b></td>';
$itemD .= '<td width="7%"><b>UNDS</b></td>';
$itemD .= '<td width="52%"><b>DESCRIPCION</b></td>';
$itemD .= '<td width="11%"><b>PREC.UNIT</b></td>';
$itemD .= '<td width="11%"><b>TOTAL</b></td></tr>';
/*
 * Solo una pagina
 */
if ($contar[0] <= 15){
	while ($rowI = mysqli_fetch_array($_QryI)) {
		$itemD .= '<tr>
		   	<td width="13%" style="font-size: 19px;">'.$rowI['prod_codigo'].'</td>
			<td width="6%">'.$rowI['tunidades'].'</td>
			<td width="7%">'.$rowI['predreq_unidad'].'</td>';
		$itemD .= '<td width="52%" style="font-size: 19px;">'.$rowI['predreq_descripcion'].'</td>
			<td align="right" width="11%">'.$rowI['predreq_prec_uni'].'</td>
			<td align="right" width="11%">'.$rowI['tvalor'].'</td>';
		$itemD .= '</tr>';
	}
	$itemD .= '</table>';
} else {
	while ($rowI = mysqli_fetch_array($_QryI)) {
		$itemD .= '<tr>
		   	<td width="13%" style="font-size: 19px;">'.$rowI['prod_codigo'].'</td>
			<td width="6%">'.$rowI['tunidades'].'</td>
			<td width="7%">'.$rowI['predreq_unidad'].'</td>';
		$itemD .= '<td width="52%" style="font-size: 19px;">'.$rowI['predreq_descripcion'].'</td>
			<td align="right" width="11%">'.$rowI['predreq_prec_uni'].'</td>
			<td align="right" width="11%">'.$rowI['tvalor'].'</td>';
		$itemD .= '</tr>';
		$faltan = $contar[0]-$item;
		$sub = $sub+$rowI['tvalor'];
		$resto = $item % $overflow;
		if ($resto == 0){
			$itemD .= '<tr>';
			$itemD .= '<td colspan="3">Subtotal : '.$sub.'</td>';
			$itemD .= '<td colspan="3">Pagina '.$p.' de '.$paginas.', pasan ...</td>';
			$itemD .= '</tr>';
			$itemD .= '</table>';
			$p = $p+1;
			//echo $itemD;
			$pdf->writeHTML($itemD, true, 0, true, 0);
			//echo '<br><br><br><br><br>';
			//echo $cabecera;
			// reset pointer to the last page
			$pdf->lastPage();
			// Agregar una pagina
			$pdf->AddPage('P','LETTER');
			$pdf->writeHTML($cabecera, true, 0, true, 0);
			$itemD  = '<table width="100%" border="1" cellpadding="1" cellspacing="0">';
			$itemD .= '<tr><td width="13%"><b>CODIGO</b></td>';
			$itemD .= '<td width="6%"><b>CANT</b></td>';
			$itemD .= '<td width="7%"><b>UNDS</b></td>';
			$itemD .= '<td width="52%"><b>DESCRIPCION</b></td>';
			$itemD .= '<td width="11%"><b>PREC.UNIT</b></td>';
			$itemD .= '<td width="11%"><b>TOTAL</b></td></tr>';
			$itemD .= '<tr>';
			$itemD .= '<td colspan="6">... vienen '.$p.' de '.$paginas.'</td>';
			$itemD .= '</tr>';
		}

		$item = $item+1;
	}
	$itemD .= '</table>';
}


$pdf->writeHTML($itemD, true, 0, true, 0);
//echo $itemD;


$sql = "Select sum(predreq_total) From ".$conf->getTbl_predreq()
	." Where predreq_estado = 3"
	." And id_empresa = ".$id_cia
	." And predreq_numero_oc = '".$orden."'";

//$sql = "Select SUM(TOTAL) From ".$tabla." Where NORDEN = '".$orden."'";
$result = $db->ejecutar($sql);
$row = mysqli_fetch_array($result);
$valortotalorden = $row[0];
$sumas = number_format($row[0],2);
$iva = round(0.13*$row[0],2);
$subtotal = round(($row[0]+$iva),2);
$pasaaletra = $row[0]+$iva;
$subtot = round(($row[0]+$iva),2);
$subtotal = number_format($subtotal,2);
$iva = number_format($iva,2);
$retencion = 0;
$ivaretenido = round(0.01*$row[0],2);

//////// PIES DE LA ORDEN ///////////////

$pies = '<table width="100%" border="1" cellpadding="2" cellspacing="0">
		<tr>';
/*
 * Vamos a cambiar el IVA para que respete los tama�os de empresas
 */
// GRAN CONTRIBUYENTE
if($_Row0['emp_grande'] == '1') {
	// GRAN CONTRIBUYENTE
	if($_Row1['prov_tamanio'] == 'GRANDE') {
		$pies .='<td rowspan="3" width="75%"  style="font-size: 18px;">Entregar en : Km. 17 1/2 Carret. A Quezaltepeque, Cant�n Joya Galana Hda. El �ngel, Lot. Las Ventanas Pol. 2 #9 Fte. A bodegas de WalMart Apopa.</td>';
		$pies .='<td align="right" width="15%">
				<b>SUMAS $:</b>
			</td>
			<td align="right" width="10%">'.$sumas.'</td>
		</tr>';
		$pies .= '<tr>
					<td align="right" width="15%">
					<b>13% IVA $:</b>
					</td>
					<td align="right" width="10%">'.$iva.'</td>
				</tr>';
	} else {
		// NO GRAN CONTRIBUYENTE
		if($valortotalorden >= 100){
			$pies .='<td rowspan="4" width="75%"  style="font-size: 18px;">Entregar en : Km. 17 1/2 Carret. A Quezaltepeque, Cant�n Joya Galana Hda. El �ngel, Lot. Las Ventanas Pol. 2 #9 Fte. A bodegas de WalMart Apopa.</td>';
			$pies .='<td align="right" width="15%">
							<b>SUMAS $:</b>
						</td>
						<td align="right" width="10%">'.$sumas.'</td>
					</tr>';
			$pies .= '<tr>
					<td align="right" width="15%">
					<b>13% IVA $:</b>
					</td>
					<td align="right" width="10%">'.$iva.'</td>
				</tr>';
			$pies .= '<tr>
					<td align="right" width="15%">
						<b>1% Retencion $:</b>
					</td>
					<td align="right" width="10%">'.$ivaretenido.'</td>
				</tr>';
			$subtot = round(($row[0]+$iva-$ivaretenido),2);
			$subtotal = number_format($subtot,2);
		} else {
			$pies .='<td rowspan="3" width="75%"  style="font-size: 18px;">Entregar en : Km. 17 1/2 Carret. A Quezaltepeque, Cant�n Joya Galana Hda. El �ngel, Lot. Las Ventanas Pol. 2 #9 Fte. A bodegas de WalMart Apopa.</td>';
			$pies .='<td align="right" width="15%">
							<b>SUMAS $:</b>
						</td>
						<td align="right" width="10%">'.$sumas.'</td>
					</tr>';
			$pies .= '<tr>
					<td align="right" width="15%">
					<b>13% IVA $:</b>
					</td>
					<td align="right" width="10%">'.$iva.'</td>
				</tr>';
		}
	}
// NO GRAN CONTRIBUYENTE
} else {
	if($_Row1['prov_tamanio'] == 'GRANDE') {
		if($valortotalorden >= 100){
			$pies .='<td rowspan="4" width="75%"  style="font-size: 18px;">Entregar en : Km. 17 1/2 Carret. A Quezaltepeque, Cant�n Joya Galana Hda. El �ngel, Lot. Las Ventanas Pol. 2 #9 Fte. A bodegas de WalMart Apopa.</td>';
			$pies .='<td align="right" width="15%">
							<b>SUMAS $:</b>
						</td>
						<td align="right" width="10%">'.$sumas.'</td>
					</tr>';
			$pies .= '<tr>
					<td align="right" width="15%">
					<b>13% IVA $:</b>
					</td>
					<td align="right" width="10%">'.$iva.'</td>
				</tr>';
			$pies .= '<tr>
						<td align="right" width="15%">
							<b>1% Percibido $:</b>
						</td>
						<td align="right" width="10%">'.$ivaretenido.'</td>
					</tr>';
			$subtot = round(($row[0]+$iva+$ivaretenido),2);
			$subtotal = number_format($subtot,2);
		} else {
			$pies .='<td rowspan="3" width="75%"  style="font-size: 18px;">Entregar en : Km. 17 1/2 Carret. A Quezaltepeque, Cant�n Joya Galana Hda. El �ngel, Lot. Las Ventanas Pol. 2 #9 Fte. A bodegas de WalMart Apopa.</td>';
			$pies .='<td align="right" width="15%">
							<b>SUMAS $:</b>
						</td>
						<td align="right" width="10%">'.$sumas.'</td>
					</tr>';
			$pies .= '<tr>
						<td align="right" width="15%">
							<b>13% IVA $:</b>
						</td>
						<td align="right" width="10%">'.$iva.'</td>
					</tr>';
		}
	} else {
		$pies .='<td rowspan="3" width="75%"  style="font-size: 18px;">Entregar en : Km. 17 1/2 Carret. A Quezaltepeque, Cant�n Joya Galana Hda. El �ngel, Lot. Las Ventanas Pol. 2 #9 Fte. A bodegas de WalMart Apopa.</td>';
		$pies .='<td align="right" width="15%">
						<b>SUMAS $:</b>
					</td>
					<td align="right" width="10%">'.$sumas.'</td>
				</tr>';
		$pies .= '<tr>
					<td align="right" width="15%">
					<b>13% IVA $:</b>
					</td>
					<td align="right" width="10%">'.$iva.'</td>
				</tr>';
	}
	
}

$pies .= '<tr>
			<td align="right" width="15%">
				<b>TOTAL $:</b>
			</td>
			<td align="right" width="10%">'.$subtotal.'</td>
		</tr>
		<tr>
			<td colspan="3" width="100%"><b>SON:'.numtoletras($subtot).'</b></td>
		</tr>
	</table>';

	$pies .='<table width="100%" border="0" cellpadding="2" cellspacing="0">
	<tr>
		<td colspan="2" style="font-size: 19px;"><b>1-Presentar comprobante contable, orden de compra y hoja de recepción del bien o servicio firmada por el usuario para trámite de quedan, de no presentar la documentación completa no se entregara quedan.</b></td>
	</tr>
	<tr>
		<td colspan="2" style="font-size: 19px;">2-Favor emitir Comprobante de Credito Fiscal a Nombre de : <b>'.$_Row0['emp_nombre'].'</b> Registro No. : <b>'.$_Row0['emp_registro'].'</b>  NIT:<b>'.$_Row0['emp_nit'].' </b></td>
	</tr>
	<tr>
		<td colspan="2" style="font-size: 19px;">3-<b>HORARIO DE RECEPCION DE MERCADERIA ES DE LUNES A VIERNES DE 8:00AM-11:30AM Y DE 2:00PM-4:30PM.</b></td>
	</tr>
	<tr>
		<td colspan="2" style="font-size: 19px;">4-Quedan se entregaran en Recepción de Oficinas Centrales de Lunes a Viernes de 8:00AM a 12:00M y de 2:00PM a 4:30PM</td>
	</tr>
	<tr>
		<td colspan="2" style="font-size: 19px;">5-<b>'.$_Row0['emp_nombre'].'</b> Pagara el monto estipulado en la orden si el producto, servicio, asesoria, etc., ha cumplido con todos los
		</td>
	</tr>
	<tr>
		<td colspan="2" style="font-size: 19px;">requerimientos establecidos y descritos en la orden de compra o los solicitados mediante documentos de cotizacion enviados por el proveedor.
		</td>
	</tr>
	</table><br><br>';

/*$pies .='<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr>
	<td colspan="2" style="font-size: 19px;">1-Favor enviar original, triplicado y fotocopia del Credito Fiscal.</td>
</tr>
<tr>
	<td colspan="2" style="font-size: 19px;">2-No se EMITIRA QUEDAN si no se adjunta esta ORDEN DE COMPRA a su COMPROBANTE DE CREDITO FISCAL.</td>
</tr>
<tr>
	<td colspan="2" style="font-size: 19px;">3-Favor emitir Comprobante de Credito Fiscal a Nombre de : <b>'.$_Row0['emp_nombre'].'</b> Registro No. : <b>'.$_Row0['emp_registro'].'</b>  NIT:<b>'.$_Row0['emp_nit'].' </b></td>
</tr>
<tr>
	<td colspan="2" style="font-size: 19px;">4-<b>HORARIO DE RECEPCION DE MERCADERIA ES DE LUNES A VIERNES DE 8:00AM-11:30PM Y DE 2:00PM-4:30PM.</b></td>
</tr>
<tr>
	<td colspan="2" style="font-size: 19px;">5-Quedan se entregaran en Porteria de Oficinas Centrales durante los primeros 15 dias habiles de cada mes de Lunes a Viernes de 8:00AM a 12:00M y de 2:00PM a 4:30PM</td>
</tr>
<tr>
	<td colspan="2" style="font-size: 19px;">6-<b>'.$_Row0['emp_nombre'].'</b> Pagara el monto estipulado en la orden si el producto, servicio, asesoria, etc., ha cumplido con todos los
	</td>
</tr>
<tr>
	<td colspan="2" style="font-size: 19px;">requerimientos establecidos y descritos en la orden de compra o los solicitados mediante documentos de cotizacion enviados por el proveedor.
	</td>
</tr>
</table><br><br>';*/
  /*
  * Firmas
  */

$pies .= '<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td align="center">___________________</td>
		<td align="center">___'.$_SESSION['u'].'___</td>
		<td align="center">_______________</td>
	</tr>
	<tr>
		<td align="center">JEFE DE COMPRAS</td>
					<td align="center">ANALISTA COMPRAS</td>
		<td align="center">AUTORIZADO</td>
	</tr>
</table>';
// Fin del Cuerpo
$pies .='</body></html>';

//echo $pies;
$pdf->writeHTML($pies, true, 0, true, 0);

/*
 * Informacion del Archivp PDF
 * Creacion de la clase pdf
 */
$file = $orden.".pdf";

//Close and output PDF document
$pdf->Output($file, 'D');

?>
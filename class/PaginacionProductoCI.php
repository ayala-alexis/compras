<script type="text/javascript">
$("#pagination-digg li a").click(function(){
	var refer = this.href;
	var largo = refer.length;
	var ultima_pos = refer.substr((largo-1),1);
	if(ultima_pos != '#'){
		var element = refer.split('/');
		var campos = element[4].split(',');
		var pagi = campos[0];
		var tabl = campos[1];
		try{
			pagina(pagi, tabl);
		}catch(e){
			alert(e.message);
			alert(e.name);
		}
	}
	return false;
});
$("#btnAdd").click(function(){
	$("#formDiv").css('display','block');
	$("#formDiv").dialog({
		title: 'Adicionar Registro',
		width: 600,
		show: 'fold',
		hide: 'blind',
		modal: true
	});
	return false;
});
$(".btnMtto").click(function(){
	idform = '#formDiv'+this.id;
	$(idform).css('display','block');
	$(idform).dialog({
		title: 'Modificar Registro',
		width: 600,
		show: 'fold',
		hide: 'blind',
		modal: true
	});
	return false;
});
$(".btnDelete").click(function(){
    idRec = this.id;
	$("#dialog-confirm").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>El Registro '+idRec+' sera eliminado. Esta seguro?</p>').dialog({
			resizable: false,
			height: 200,
			modal: true,
			show: 'fold',
			hide: 'blind',
			buttons: {
				"Borrar Registro": function() {
				    idform = 'frmDelete'+idRec;
				    var campos = xajax.getFormValues(idform);
				    campos["num_pagi"] = $("#num_pag").val();
					myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
					$.ajax({
						type : "POST",
						url : myUrl,
						data: {
							form: campos
						},
						beforeSend: function() {
							$.pnotify({
								title: "eliminando...",
								text: "intentando eliminar registro de tabla, por favor espere...",
								icon: "glyphicon glyphicon-search",
								hide: true
							});
						},
						success : function(data){
							if(!$.isNumeric(data)){
						    	$.pnotify({
									title: "error",
									text: data,
									icon: "glyphicon glyphicon-ban-circle",
									hide: true,
									type: "error"
								});
							} else {
								if(data == 1 ){
									$.pnotify({
										title: "error",
										text: data,
										icon: "glyphicon glyphicon-ban-circle",
										hide: true,
										type: "error"
									});
								} else {
									$.pnotify({
										title: "eliminado",
										text: "Registro eliminado con exito!.",
										icon: "glyphicon glyphicon-remove",
										hide: true,
										type: "success"
									});
									pagina(campos["num_pagi"],"producto");
									$("#dialog-confirm").dialog( "close" );
								}
							}
						},
						error : function(XMLHttpRequest, textStatus, errorThrown) {
							$.pnotify({
								title: "error",
								text: "Ocurrio un error durante la ejecucion.",
								icon: "glyphicon glyphicon-ban-circle",
								hide: true,
								type: "error"
							});
						    $("#dialog-confirm").dialog( "close" );
						}
					});
				},
				Cancel: function() {
					$("#dialog-confirm").dialog( "close" );
				}
			}
		});
	return false;
});
</script>
<script>
$(function(){
	$('a').tooltip();
});
</script>
<?
session_start ();
require '../Configuracion.php';
require '../DB.php';
include_once '../model/SQLgenerales.php';
// Tabla de Paginacion
$tabla = $_POST ['tab'];
// Iniciamos instancia a la clase DB
$db = DB::getInstance ();
// Parametros de Configuracion
$conf = Configuracion::getInstance ();
// Sublineas
$sql_Cat = "Select prod_linea,prod_slinea, prod_descli From ci_producto Group by prod_linea, prod_slinea Order by prod_descli";
try {
	$run_Cat = $db->ejecutar($sql_Cat);
} catch (Exception $e) {
	echo $e->getMessage();
	die();
}
// Permisos de usuario en el modulo
$sqlAuthMod = "Select * From " . $conf->getTbl_acc_modulo () . " Where id_usuario = '" . $_SESSION ['i'] . "' And id_modulo = '" . $_SESSION ['idmod'] . "'";
try {
	$sqlAuthExec = $db->ejecutar ( $sqlAuthMod );
	$rowsAuth = mysqli_fetch_array ( $sqlAuthExec );
} catch ( Exception $e ) {
	echo $e->getMessage ();
	die ();
}
$filtro1 = "";
$filtro2 = "";
$filtro3 = "";
$filtro4 = "";
// Definimos el filtro
if(!empty($_POST['filtronombre']) || !empty($_POST['filtrocategoria'])) {
	if($_POST['filtrocategoria'] <> 'todos') {
		$filtro1 = " Where ";
	}
	if(!empty($_POST['filtronombre'])) {
		$filtro1 = " Where ";
	}
}
if(!empty($_POST['filtronombre']) && $_POST['filtrocategoria'] <> 'todos') {
	$filtro2 = " And ";
}
if($_POST['filtrocategoria'] <> 'todos') {
	$filtro4 = " prod_linea = '".substr($_POST['filtrocategoria'],0,2)."' And prod_slinea = '".substr($_POST['filtrocategoria'],2,2)."' ";
}
if(!empty($_POST['filtronombre'])) {
	$filtro3 = " prod_descripcion Like '%".$_POST['filtronombre']."%' ";
}

$sql = "Select * from ci_producto "
	.$filtro1.$filtro3.$filtro2.$filtro4;

// Consulta que devuelve todos los registros
try {
	$lista0 = $db->ejecutar ( $sql );
	// Se cuentan los registros devueltos por la consulta SQL $lista0
	$totalSql = mysqli_num_rows ( $lista0 );
} catch ( Exception $e ) {
	echo $e->getMessage ();
	die ();
}
// Registros a mostrar en cada p�gina
$rowCount = 15;
$pagesCount = ( int ) ceil ( $totalSql / $rowCount );
$pageIndex = isset ( $_POST ['pag'] ) ? ( int ) $_POST ['pag'] : 0;
if ($pageIndex > $pagesCount) {
	$pageIndex = $pagesCount - 1;
}
$offset = $pageIndex * $rowCount;
$_SESSION ['p'] = 0;
$_SESSION ['p'] = $pageIndex;

if($pageIndex <= 0) {
	$pageactual = 1;
} else {
	$pageactual = $pageIndex+1;
}

/* Obtenemos los registros solicitados */
$sqlPag = "SELECT * FROM ci_producto "
	.$filtro1.$filtro3.$filtro2.$filtro4
	. " ORDER BY prod_linea, prod_slinea, prod_codigo "
	." LIMIT " . $offset . "," . $rowCount . "";
try {
	// Consulta SQL con la que se sacar� el listado de registros
	$lista1 = $db->ejecutar ( $sqlPag );
} catch ( Exception $e ) {
	echo $e->getMessage ();
	die ();
}
// Bucle para generar el listado de registros
$forms = Array ();
echo '<h4 class="text-blue">Mantenimiento de Producto para Consumo Interno</h4>';
echo '<form class="form-horizontal" onSubmit="return false;">';
echo '<table width="100%" border="0" cellpadding="2" cellspacing="1">
	<tr>
	  <td bgcolor="#f5f5f5"><b>B&uacute;squeda en Registros</b></td>
	</tr>
	<tr>
		<td>
			<div class="form-group">
			<label class="control-label col-md-2" for="filtrocategoria">Sublinea</label>
			<div class="col-md-5">
				<select class="form-control input-sm" name="filtrocategoria" id="filtrocategoria">
					<option value="todos">-- Todos las sublineas --</option>
					';
				while($rowCat = mysqli_fetch_array($run_Cat)) {
					echo '<option value="'.str_pad($rowCat[0], 2, "0",STR_PAD_LEFT).str_pad($rowCat[1], 2, "0",STR_PAD_LEFT).'" ';
					if(str_pad($rowCat[0], 2, "0",STR_PAD_LEFT).str_pad($rowCat[1], 2, "0",STR_PAD_LEFT) == $_POST['filtrocategoria']) {
						echo "selected";
					} else  {
						echo "";
					}
					echo '>'.$rowCat[2].'</option>';
				}
				echo '</select>
			<div>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<div class="form-group">
				<label class="control-label col-md-2" for="filtronombre">Descripcion</label>
				<div class="col-md-5">
					<input type="text" class="form-control input-sm" id="filtronombre" name="filtronombre" placeholder="buscar" value="'.$_POST['filtronombre'].'">
				</div>
				<div class="col-md-3">
					<button class="btn btn-sm btn-default" id="btnBuscar" name="btnBuscar"><i class="glyphicon glyphicon-search"></i></button>
					&nbsp; &nbsp;
					<a class="btn btn-success" href="http://192.168.40.4/report/solicitudci.ashx">Descargar Listado Excel</a>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<b>Se encontraron '.$totalSql.' registros, Actualmente mostrando p&aacute;gina '.($pageIndex + 1) .' de '.$pagesCount.'</b>
		</td>
	</tr>
  </table>';
echo '</form>';
echo '<table class="table table-condensed table-hover table-striped">';
echo '<thead>';
echo '<tr>';
echo '<th>Sublinea</th>';
echo '<th>Codigo</th>';
echo '<th>Descripcion</th>';
if ($rowsAuth ['acc_del'] == 1) {
	echo '<th>&nbsp;</th>';
}

/*
 * echo '<th>FECHA</th>'; echo '<th>HORA</th>'; echo '<th>USUARIO</th>';
 */
echo '</tr>';
echo '</thead>';
echo '<tbody>';
while ( $fila = mysqli_fetch_array ( $lista1 ) ) {
	echo '<tr>';
	echo '<td>' . '(' . str_pad($fila[7], 2, "0",STR_PAD_LEFT) . '-' . str_pad($fila[8], 2, "0",STR_PAD_LEFT). ') ' . $fila['prod_descli'] . '</td><td>' . $fila [1] . '</td><td>' . $fila [2] . '</td>';
	// echo
	// '<td>'.$fila[17].'</td><td>'.$fila[18].'</td><td>'.$fila[19].'</td>';
	// Pone link segun acceso en modulo
	if ($rowsAuth ['acc_del'] == 1) {
		echo '<td align="center">
				<a class="btnDelete" href="#" id="' . $fila [0] . '" rel="tooltip" title="eliminar">
				<span class="glyphicon glyphicon-remove"></span>
				</a>
   			</td>';
	}
	$frmIdDel = 'frmDelete' . $fila [0];
	$htmlForm = '
			<form id="' . $frmIdDel . '" name="' . $frmIdDel . '" method="POST">
			<input type="hidden" name="id_producto" id="id_producto" value="' . $fila [0] . '" />
			<input type="hidden" value="delete" id="accion" name="accion">
			<input type="hidden" value="ci_producto" id="tabla" name="tabla">
			<input type="hidden" value="' . $_SESSION ['u'] . '" id="usr_crea" name="usr_crea">
			<input type="hidden" value="' . $pageIndex . '" id="num_pagi" name="num_pagi">
		</form>';
	$htmlFormJS = $htmlForm;
	$forms [$fila [0]] = $htmlFormJS;
	echo '</tr>';
}
echo '</tbody>';
echo '</table>';
// Se inicia el listado de p�ginas
echo '<ul id="pagination-digg" class="pagination pagination-sm">';

// Anterior Deshabilitado
if ($pageIndex == 0) {
	echo '<li class="disabled"><a href="#">Anterior</a></li>';
}
// Si la p�gina actual no es la primera, se muestra el enlace a la p�gina
// anterior
if ($pageIndex > 0) {
	echo '<li><a href="' . ($pageIndex - 1) . ',' . $tabla . '">Anterior</a></li>';
}
// Se saca el listado de p�ginas mediante un bucle
$pagesToShow = 3; // P�ginas que aparecen antes y despu�s de la actual
$start = $pageIndex - $pagesToShow;
if ($start < 0) {
	$start = 0;
}
$end = $pageIndex + $pagesToShow;
if ($end >= $pagesCount) {
	$end = $pagesCount - 1;
}
// Primera y Segunda
if ($start > 0) {
	for($i = 0; $i < 2 && $i < $start; ++ $i) {
		echo '<li><a href="' . $i . ',' . $tabla . '">' . ($i + 1) . '</a></li>';
	}
}
if ($start > 2) {
	echo '<li><a href="#"><strong> . . . </strong></a></li>';
}
// Actual
for($i = $start; $i <= $end; ++ $i) {
	if ($pageIndex == $i) {
		echo '<li class="active"><a href="' . $i . ',' . $tabla . '">' . ($i + 1) . '</a></li>';
	} else {
		echo '<li><a href="' . $i . ',' . $tabla . '">' . ($i + 1) . '</a></li>';
	}
}
if ($end < ($pagesCount - 3)) {
	echo '<li><a href="#"><strong> . . . </strong></a></li>';
}
// Penultima y Ultima
if ($end < ($pagesCount - 1)) {
	for($i = max ( ($pagesCount - 2), ($end + 1) ); $i < $pagesCount; ++ $i) {
		echo '<li><a href="' . $i . ',' . $tabla . '">' . ($i + 1) . '</a></li>';
	}
}
// Siguiente
if ($pageIndex < ($pagesCount - 1)) {
	echo '<li><a href="' . ($pageIndex + 1) . ',' . $tabla . '">Siguiente</a></li>';
}
// Ultima pagina
if ($pageIndex >= ($pagesCount - 1)) {
	echo '<li class="disabled"><a href="#">Siguiente</a></li>';
}
// Se finaliza el listado de p�ginas
if ($rowsAuth ['acc_add'] == 1) {
	echo '<li><a id="btnAdd" href="#">Adicionar</a></li>';
}
echo '</ul>';
// Pone link segun acceso en modulo
echo '<input type="hidden" id="num_pag" name="num_pag" value="' . $_SESSION ['p'] . '">';
foreach ( $forms as $fr ) {
	echo $fr;
}
?>
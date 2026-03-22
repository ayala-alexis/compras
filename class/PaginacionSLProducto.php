<?php session_start(); ?>
<script type="text/javascript">
$("#pagination-digg li a").unbind('click').bind('click',function(){
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
	$("#frmAdd").hide().fadeIn();
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
			height:200,
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
								text: "intentando eliminar registro, por favor espere...",
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
										text: "Registro eliminado.",
										icon: "glyphicon glyphicon-ok",
										hide: true,
										type: "error"
									});
									pagina(campos["num_pagi"],"sublinea");
									$("#dialog-confirm").dialog( "close" );
								}
							}
						},
						error : function(XMLHttpRequest, textStatus, errorThrown) {
							$.pnotify({
								title: "error...",
								text: "Ocurrion un error en la ejecucion.",
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
require '../Configuracion.php';
require '../DB.php';
// Tabla de Paginacion
$tabla = $_POST ['tab'];
// Iniciamos instancia a la clase DB
$db = DB::getInstance ();
// Parametros de Configuracion
$conf = Configuracion::getInstance ();
// Permisos de usuario en el modulo
$sqlAuthMod = "Select * From " . $conf->getTbl_acc_modulo () . " Where id_usuario = '" . $_SESSION ['i'] . "' And id_modulo = '" . $_SESSION ['idmod'] . "'";
try {
	$sqlAuthExec = $db->ejecutar ( $sqlAuthMod );
	$rowsAuth = mysqli_fetch_array ( $sqlAuthExec );
} catch ( Exception $e ) {
	echo $e->getMessage ();
	die ();
}
$sql = "Select * from " . $tabla;
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
$sqlPag = "SELECT * FROM " . $tabla . " ORDER BY sl_linea, sl_sublinea LIMIT " . $offset . "," . $rowCount . "";
try {
	// Consulta SQL con la que se sacar� el listado de registros
	$lista1 = $db->ejecutar ( $sqlPag );
} catch ( Exception $e ) {
	echo $e->getMessage ();
	die ();
}

$sql = "Select id_tagasto, gas_tit_codigo, gas_det_codigo, gas_descripcion From " . $conf->getTbl_tagasto(). " Order By gas_tit_codigo, gas_det_codigo";
$listata = $db->ejecutar ( $sql );
$tablas = array();
while ( $filata = mysqli_fetch_array ( $listata ) ) {
	if ($filata[2] != '00') {
		$tablas[] = $filata;
	}
}

$sql_e = "Select id_empresa, emp_nombre From " . $conf->getTbl_empresa(). " Order By emp_nombre";
$lista_e = $db->ejecutar ( $sql_e );
$emps = array();
while ($row_e2 = mysqli_fetch_array($lista_e)){
	$emps[] = $row_e2;
}

/* try {
	$listata = $db->ejecutar ( $sql );
} catch (Exception $e) {
	echo $e->getMessage ();
	die ();
} */
// Bucle para generar el listado de registros
$forms = Array ();
echo '<h4 class="text-blue">Mantenimiento de Lineas y Sublineas</h4>';
echo '<table class="table table-condensed" id="tabla">';
echo '<thead>';
echo '<tr>';
echo '<th>Linea</th>';
echo '<th>Sublinea</th>';
echo '<th>Descripcion</th>';
//echo '<th>&nbsp;</th>';
if ($rowsAuth ['acc_edit'] == 1) {
	echo '<th>&nbsp;</th>';
}
if ($rowsAuth ['acc_edit'] == 1) {
	echo '<th>&nbsp;</th>';
}
if ($rowsAuth ['acc_edit'] == 1) {
	echo '<th>&nbsp;</th>';
}
if ($rowsAuth ['acc_del'] == 1) {
	echo '<th>&nbsp;</th>';
}

/*
 * echo '<th>FECHA</th>'; echo '<th>HORA</th>'; echo '<th>USUARIO</th>';
 */
echo '</tr>';
echo '</thead>';
echo '<tbody class="searchable">';
while ( $fila = mysqli_fetch_array ( $lista1 ) ) {
	echo '<tr>';
	echo '<td>' . $fila [1] . '</td><td>' . $fila [2] . '</td><td>' . $fila [3] . '</td>';
	// echo '<td>'.$fila[5].'</td><td>'.$fila[6].'</td><td>'.$fila[7].'</td>';
	// Pone link segun acceso en modulo
	if ($rowsAuth ['acc_edit'] == 1) {
	echo '<td align="center">
			<a class="btnMtto" href="#" id="' . $fila [0] . '" rel="tooltip" title="editar informacion">
				<span class="glyphicon glyphicon-pencil"></span>
			</a>
   			</td>';
	}
	if ($rowsAuth ['acc_edit'] == 1) {
		echo '<td align="center">
			<a class="btnLista" href="./?c=prod&a=anal&id='.$fila[1]. $fila [2].'" id="' . $fila [0] . '" rel="tooltip" title="analisis de precios">
				<span class="glyphicon glyphicon-usd"></span>
			</a>
   			</td>';
	}
	if ($rowsAuth ['acc_edit'] == 1) {
		echo '<td align="center">
			<a class="btnLista" href="./view/prod/slin/PDF.php?e_l='.$fila[1].'&e_sl='. $fila [2].'" id="' . $fila [0] . '" rel="tooltip" title="lista para cotizar">
				<span class="glyphicon glyphicon-file"></span>
			</a>
   			</td>';
	}
	if ($rowsAuth ['acc_del'] == 1) {
	echo '<td align="center">
				<a class="btnDelete" href="#" id="' . $fila [0] . '" rel="tooltip" title="eliminar">
				<span class="glyphicon glyphicon-remove"></span>
				</a>
   			</td>';
	}
	echo '</tr>';
	$frmId = 'frmEdit' . $fila [0];
	$frmIdDel = 'frmDelete' . $fila [0];
	$frmDivId = 'formDiv' . $fila [0];

	/*
	 * GUARDAMOS EL FORMULARIO GENERADO EN UN ARREGLO PARA LUEGO TRABAJARLO, SU
	 * VALIDACION JAVASCRIPT SE LLEVA A TABLA JS_SCRIPTS
	 */
	$js_script = '
		<script type="text/javascript">
			$("#' . $frmId . '").validate({
			    rules: {
					sl_linea: {
						required: true,
						number: true,
						maxlength: 2,
						minlength: 2
					},
					sl_sublinea:{
						required: true,
						number: true,
						maxlength: 2,
						minlength: 2
					},
			     	sl_descripcion: {
			       		required: true
			     	}
			   },
			   messages: {
					sl_linea: {
						required : "Digite linea"
					},
					sl_sublinea: {
						required: "Digite sublinea"
					},
					sl_descripcion: {
						required: "Digite nombre"
					}
			   },
			submitHandler: function(form) {
				timeSlide=300;
			    var campos=xajax.getFormValues("' . $frmId . '");
				campos["num_pagi"] =' . $pageIndex . ';
				myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
				$.ajax({
					type : "POST",
					url : myUrl,
					data: {
						form: campos
					},
					beforeSend: function(){
						$.pnotify({
							title: "modificando...",
							text: "intentando modificar registro, por favor espere...",
							icon: "glyphicon glyphicon-search",
							hide: true
						});
						$(":submit").addClass("disabled");
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
							    $("#' . $frmId . ' input[type=reset]").click();
								$("#' . $frmDivId . '").dialog("destroy").remove();
								pagina(campos["num_pagi"],"sublinea");
								$.pnotify({
									title: "modificado",
									text: "Registro modificado.",
									icon: "glyphicon glyphicon-ok",
									hide: true,
									type: "success"
								});
							}
						}
						$(":submit").removeClass("disabled");
					},
					error : function(XMLHttpRequest, textStatus, errorThrown) {
					    $.pnotify({
							title: "error...",
							text: "Error en la ejecucion.",
							icon: "glyphicon glyphicon-ban-circle",
							hide: true,
							type: "error"
						});
						$(":submit").removeClass("disabled");
					}
				});
		   }
		});
		</script>
	';
	/* FORMULARIO HTML */
	$htmlForm = '
		<div id="' . $frmDivId . '" style="display: none; overflow: hidden;">
		<form id="' . $frmId . '" name="' . $frmId . '" method="POST" class="form-horizontal" role="form">

			<div class="form-group">
				<label class="control-label col-md-2">Linea</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="sl_linea" id="sl_linea" value="' . $fila [1] . '" placeholder="digite linea" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2">Sub-Linea</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" id="sl_sublinea" name="sl_sublinea" value="' . $fila [2] . '" placeholder="digite sub-linea" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2">Nombre</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" id="sl_descripcion" name="sl_descripcion" value="' . $fila [3] . '" placeholder="digite descripcion" />
				</div>
			</div>
							
			<div class="form-group text-center">
				<label  style="text-align: center !important;" class="control-label col-md-12 text-center" for="sl_linea">TABLA DE GASTO</label>
			</div>';
	
			foreach ($emps as $row_e){
				$htmlForm .='			
				<div class="form-group">
					<label class="control-label col-md-6" for="'.'id_tabgas'.$row_e['id_empresa'].'">'.$row_e['emp_nombre'].'</label>
					<div class="col-md-6">
								<select class="form-control input-sm" name="'.'id_tabgas'.$row_e['id_empresa'].'" id="'.'id_tabgas'.$row_e['id_empresa'].'">
								<option value="0">Sin Gasto</option>';
						foreach ($tablas as $tabgas){
							$htmlForm .= '<option value="'.$tabgas[0].'" '.($tabgas[0] == $fila['id_tabgas'.$row_e['id_empresa']] ? 'selected' : '').'>'.$tabgas[1].' '.$tabgas[2].'</option>';
						}
				$htmlForm .='</select></div></div>';
			
			}
			
			$htmlForm .='<div class="form-group"><div class="col-md-offset-2 col-md-10">
					<input type="hidden" value="edit" id="accion" name="accion">
					<input type="hidden" value="sublinea" id="tabla" name="tabla">
					<input type="hidden" value="' . $fila [0] . '" id="id_sublinea" name="id_sublinea">
					<input type="hidden" value="' . $_SESSION ['u'] . '" id="usr_crea" name="usr_crea">
					<input type="hidden" value="' . $pageIndex . '" id="num_pagi" name="num_pagi">
					<button class="btn btn-sm btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-pencil"></span> Modificar</button>
					<input type="reset" value="" style="display:none;">
				</div>
			</div>
		</form>
		<form id="' . $frmIdDel . '" name="' . $frmIdDel . '" method="POST">
			<input type="hidden" name="id_sublinea" id="id_sublinea" value="' . $fila [0] . '" />
			<input type="hidden" value="delete" id="accion" name="accion">
			<input type="hidden" value="sublinea" id="tabla" name="tabla">
			<input type="hidden" value="' . $_SESSION ['u'] . '" id="usr_crea" name="usr_crea">
			<input type="hidden" value="' . $pageIndex . '" id="num_pagi" name="num_pagi">
		</form>
		</div>';
	$htmlFormJS = $js_script . $htmlForm;
	$forms [$fila [0]] = $htmlFormJS;
}
echo '</tbody>';
echo '</table>';

// Se inicia el listado de p�ginas
echo '<ul id="pagination-digg" class="pagination">';

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
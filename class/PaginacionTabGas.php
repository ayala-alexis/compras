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
						beforeSend: function(){
							$(":submit").attr("disabled","disabled");
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
										type : "success",
								        text: "Registro Eliminado.",
								        hide: true,
								        title: "eliminado"
									});
									pagina(campos["num_pagi"],"tagasto");
									$("#dialog-confirm").dialog( "close" );
								}
							}
						},
						error : function(XMLHttpRequest, textStatus, errorThrown) {
						    showNotification({
								type : "error",
						        message: "Ha ocurrido un error durante la ejecucion.",
						        autoClose: true,
						        duration: 2
							});
						    $(":submit").removeAttr("disabled");
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
// Tabla de Paginacion
$tabla = $_POST ['tab'];
// Iniciamos instancia a la clase DB
$db = DB::getInstance ();
// Parametros de Configuracion
$conf = Configuracion::getInstance ();
// Permisos de usuario en el modulo
$sqlAuthMod = "Select * From " . $conf->getTbl_acc_modulo () . " Where id_usuario = '" . $_SESSION ['i'] . "' And id_modulo = '" . $_SESSION ['idmod'] . "'";
$sqlAuthExec = $db->ejecutar ( $sqlAuthMod );
$rowsAuth = mysqli_fetch_array ( $sqlAuthExec );

$sql = "Select * from " . $tabla;
// Consulta que devuelve todos los registros
$lista0 = $db->ejecutar ( $sql );
// Se cuentan los registros devueltos por la consulta SQL $lista0
$filmsCount = mysqli_num_rows ( $lista0 );
// P�ginas que van a aparecer, redondeando los decimales siempre hacia arriba

// Registros a mostrar en cada p�gina
$rowCount = 15;
$pagesCount = ( int ) ceil ( $filmsCount / $rowCount );
$pageIndex = isset ( $_POST ['pag'] ) ? ( int ) $_POST ['pag'] : 0;
if ($pageIndex > $pagesCount) {
	$pageIndex = $pagesCount - 1;
}
$offset = $pageIndex * $rowCount;
$_SESSION ['p'] = 0;
$_SESSION ['p'] = $pageIndex;

/* Obtenemos los registros solicitados */
$sqlPag = "SELECT * FROM " . $tabla . " ORDER BY gas_tit_codigo, gas_det_codigo LIMIT " . $offset . "," . $rowCount . "";
// Consulta SQL con la que se sacar� el listado de registros
$lista1 = $db->ejecutar ( $sqlPag );
// Bucle para generar el listado de registros
$forms = Array ();
echo '<h4 class="text-blue">Mantenimiento Tabla de Gasto</h4>';
echo '<table class="table table-condensed">';
echo '<thead>';
echo '<tr>';
echo '<th>gasto</th>';
echo '<th>Descripcion</th>';
echo '<th>&nbsp</th>';
echo '<th>&nbsp</th>';
/*
 * echo '<th>FECHA</th>'; echo '<th>HORA</th>'; echo '<th>USUARIO</th>';
 */
echo '</tr>';
echo '</thead>';
echo '<tbody>';
while ( $fila = mysqli_fetch_array ( $lista1 ) ) {
	echo '<tr>';
	echo '<td>' . $fila [2] . $fila [3] . '</td><td>' . $fila [4] . '</td>';
	// echo '<td>'.$fila[5].'</td><td>'.$fila[6].'</td><td>'.$fila[7].'</td>';
	// Pone link segun acceso en modulo
	if ($rowsAuth ['acc_edit'] == 1) {
		echo '<td align="center">
			<a class="btnMtto" href="#" id="' . $fila [0] . '" rel="tooltip" title="editar informacion">
				<span class="glyphicon glyphicon-pencil"></span>
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
					gas_tit_codigo: {
						required: true
					},
					gas_det_codigo: {
						required: true
					},
					gas_descripcion:{
						required: true
					}
			   },
			   messages: {
			     	gas_tit_codigo: {
				     	required : "Digite Gasto"
			     	},
					gas_det_codigo: {
				     	required : "Digite Gasto"
			     	},
					gas_descripcion: {
						required: "Digite Descripcion"
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
						$(":submit").attr("disabled","disabled");
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
								pagina(campos["num_pagi"],"tagasto");
								$.pnotify({
									type : "success",
							        text: "Registro Actualizado.",
							        hide: true,
							        title: "modificado"
								});
							}
						}
					},
					error : function(XMLHttpRequest, textStatus, errorThrown) {
					    $.pnotify({
							type : "error",
					        text: "Ha ocurrido un error durante la Modificacion.",
					        hide: true
						});
						$(":submit").removeAttr("disabled");
					}
				});
		   }
		});
		</script>
	';
	/* FORMULARIO HTML */
	$htmlForm = '
		<div id="' . $frmDivId . '" style="display: none; overflow: hidden;">
		<div class="container-fluid">
		<div class="row-fluid">
		<fieldset>
		<form role="form" class="form form-horizontal" id="' . $frmId . '" name="' . $frmId . '" method="POST">

			<div class="form-group">
				<label class="control-label col-md-2" for="gas_tit_codigo">Titulo</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="gas_tit_codigo" id="gas_tit_codigo" value="' . $fila [2] . '" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2" for="gas_det_codigo">Detalle</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="gas_det_codigo" id="gas_det_codigo" value="' . $fila [3] . '" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2" for="gas_descripcion">Descripcion</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="gas_descripcion" id="gas_descripcion" value="' . $fila [4] . '" />
				</div>
			</div>
							
			<div class="form-group">
				<div class="col-md-offset-2 col-md-10">
					<button class="btn btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-pencil"></span> Modificar</button>
					<input type="reset" value="" style="display:none;">
				</div>
			</div>

			<input type="hidden" name="id_tagasto" id="id_tagasto" value="' . $fila [0] . '" />
			<input type="hidden" name="id_empresa" id="id_empresa" value="' . $fila [1] . '" />
			<input type="hidden" value="edit" id="accion" name="accion">
			<input type="hidden" value="tagasto" id="tabla" name="tabla">
			<input type="hidden" value="' . $_SESSION ['u'] . '" id="usr_crea" name="usr_crea">
			<input type="hidden" value="' . $pageIndex . '" id="num_pagi" name="num_pagi">
		</form>
		<form id="' . $frmIdDel . '" name="' . $frmIdDel . '" method="POST">
			<input type="hidden" name="id_tagasto" id="id_tagasto " value="' . $fila [0] . '" />
			<input type="hidden" value="delete" id="accion" name="accion">
			<input type="hidden" value="tagasto" id="tabla" name="tabla">
			<input type="hidden" value="' . $_SESSION ['u'] . '" id="usr_crea" name="usr_crea">
			<input type="hidden" value="' . $pageIndex . '" id="num_pagi" name="num_pagi">
		</form>
		</div>';
	$htmlFormJS = $js_script . $htmlForm;
	$forms [$fila [0]] = $htmlFormJS;
	echo '</tr>';
}
echo '</tbody>';
echo '</table>';
// Se inicia el listado de p�ginas
echo '<div class="pagination">';
echo '<ul class="pagination" id="pagination-digg">';

// Anterior Deshabilitado
if ($pageIndex == 0) {
	echo '<li class="disabled"><a href="#">Prev</a></li>';
}
// Si la p�gina actual no es la primera, se muestra el enlace a la p�gina
// anterior
if ($pageIndex > 0) {
	echo '<li><a href="' . ($pageIndex - 1) . ',' . $tabla . '">Prev</a></li>';
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
	echo '<li><a href="' . ($pageIndex + 1) . ',' . $tabla . '">Next</a></li>';
}
// Ultima pagina
if ($pageIndex >= ($pagesCount - 1)) {
	echo '<li class="disabled"><a href="#">Next</a></li>';
}
// Se finaliza el listado de p�ginas
if ($rowsAuth ['acc_add'] == 1) {
	echo '<li><a id="btnAdd" href="#">Adicionar</a></li>';
}
echo '</ul>';
// Pone link segun acceso en modulo
echo '</div>';
echo '<input type="hidden" id="num_pag" name="num_pag" value="' . $_SESSION ['p'] . '">';
foreach ( $forms as $fr ) {
	echo $fr;
}
?>


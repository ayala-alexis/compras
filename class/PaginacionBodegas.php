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
		autoResize: true,
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
		autoResize: true,
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
							$.pnotify({
								title: "realizando peticion...",
								text: "intentando eliminar el registro, por favor espere...",
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
									$.pnotify({
										title: "eliminado",
										text: "registro eliminado",
										icon: "glyphicon glyphicon-ok",
										hide: true,
										type: "success"
									});
									pagina(campos["num_pagi"],"bodega");
									$("#dialog-confirm").dialog( "close" );
								}
							}
							$(":submit").removeClass("disabled");
						},
						error : function(XMLHttpRequest, textStatus, errorThrown) {
							$.pnotify({
								title: "error",
								text: "Error en la ejecucion.",
								icon: "glyphicon glyphicon-ban-circle",
								hide: true,
								type: "error"
							});
						    $(":submit").removeClass("disabled");
						    $("#dialog-confirm").dialog( "close" );
						}
					});
				},
				Cancel: function() {
					$("#dialog-confirm").dialog( "close" );
					$(":submit").removeClass("disabled");
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
// NOMBRE DE LA EMPRESA
$sqlEmp = "Select * From " . $conf->getTbl_empresa () . " Where id_empresa = '" . $_SESSION ['ie'] . "'";
$sqlEmpExec = $db->ejecutar ( $sqlEmp );
$rowsEmp = mysqli_fetch_array ( $sqlEmpExec );

$sql = "Select * from " . $tabla . " Where id_empresa='" . $_SESSION ['ie'] . "'";
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
$sqlPag = "SELECT * FROM " . $tabla . " Where id_empresa='" . $_SESSION ['ie'] . "' ORDER BY bod_codigo LIMIT " . $offset . "," . $rowCount . "";
// Consulta SQL con la que se sacar� el listado de registros
$lista1 = $db->ejecutar ( $sqlPag );
// Bucle para generar el listado de registros
$forms = Array ();
echo '<h4 class="text-blue"><a href="?c=emp&a=inicio&id=3"><img alt="" src="images/back-black.png"></a>  Mantenimiento de Bodegas : <b>' . $rowsEmp ['emp_nombre'] . '</b></h4>';
echo '<table class="table table-condensed">';
echo '<thead>';
echo '<tr>';
echo '<th>Codigo</th>';
echo '<th>Descripcion</th>';
echo '<th>&nbsp;</th>';
echo '<th>&nbsp;</th>';
/*
 * echo '<th>FECHA</th>'; echo '<th>HORA</th>'; echo '<th>USUARIO</th>';
 */
echo '</tr>';
echo '</thead>';
echo '<tbody>';
while ( $fila = mysqli_fetch_array ( $lista1 ) ) {
	echo '<tr>';
	echo '<td>' . $fila [7] . '</td><td>' . $fila [1] . '</td>';
	// echo '<td>'.$fila[4].'</td><td>'.$fila[5].'</td><td>'.$fila[6].'</td>';
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
					bod_descripcion: {
						required: true
					}
			   },
			   messages: {
			     	bod_descripcion: {
				     	required : "Digite Descripcion"
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
							title: "realizando peticion...",
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
								pagina(campos["num_pagi"],"bodega");
								$.pnotify({
									title: "modificado",
									text: "Registro modificado",
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
							title: "error",
							text: "Error en la ejecucion",
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
 		<fieldset>
		<legend>Modificar Bodega</legend>
		<form class="form-horizontal" id="' . $frmId . '" name="' . $frmId . '" method="POST" role="form">
			<div class="form-group">
				<label class="control-label col-md-3" for="inputEmail">Codigo</label>
				<div class="col-md-9">
					<p class="form-control-static text-info">' . $fila [7] . '</p>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-3" for="bod_descripcion">Descripcion</label>
				<div class="col-md-9">
					<input class="form-control input-sm" type="text" name="bod_descripcion" id="bod_descripcion" value="' . $fila [1] . '" />
				</div>
			</div>

			<input type="hidden" name="id_bodega" id="id_bodega" value="' . $fila [0] . '" />
			<input type="hidden" name="id_empresa" id="id_empresa" value="' . $fila [2] . '" />
			<input type="hidden" value="edit" id="accion" name="accion">
			<input type="hidden" value="bodega" id="tabla" name="tabla">
			<input type="hidden" value="' . $_SESSION ['u'] . '" id="usr_crea" name="usr_crea">
			<input type="hidden" value="' . $pageIndex . '" id="num_pagi" name="num_pagi">
			<div class="form-group">
				<div class="col-md-offset-3 col-md-9">
					<button class="btn btn-sm btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-pencil"></span> Modificar</button>
					<input type="reset" value="" style="display:none;">
				</div>
			</div>
		</form>
		<form id="' . $frmIdDel . '" name="' . $frmIdDel . '" method="POST">
			<input type="hidden" name="id_bodega" id="id_bodega " value="' . $fila [0] . '" />
			<input type="hidden" value="delete" id="accion" name="accion">
			<input type="hidden" value="bodega" id="tabla" name="tabla">
			<input type="hidden" value="' . $_SESSION ['u'] . '" id="usr_crea" name="usr_crea">
			<input type="hidden" value="' . $pageIndex . '" id="num_pagi" name="num_pagi">
		</form>
		</fieldset>
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
	echo '<li class="disabled"><a href="#">prev</a></li>';
}
// Si la p�gina actual no es la primera, se muestra el enlace a la p�gina
// anterior
if ($pageIndex > 0) {
	echo '<li><a href="' . ($pageIndex - 1) . ',' . $tabla . '">prev</a></li>';
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
	echo '<li><a href="' . ($pageIndex + 1) . ',' . $tabla . '">next</a></li>';
}
// Ultima pagina
if ($pageIndex >= ($pagesCount - 1)) {
	echo '<li class="disabled"><a href="#">next</a></li>';
}
// Se finaliza el listado de p�ginas
if ($rowsAuth ['acc_add'] == 1) {
	echo '<li><a id="btnAdd" href="#">adicionar</a></li>';
}
echo '</ul>';
// Pone link segun acceso en modulo
echo '</div>';
echo '<input type="hidden" id="num_pag" name="num_pag" value="' . $_SESSION ['p'] . '">';
foreach ( $forms as $fr ) {
	echo $fr;
}
?>


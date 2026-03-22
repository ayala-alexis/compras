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
	$('.ui-dialog-titlebar').show();
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
	$('.ui-dialog-titlebar').show();
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
										text: "registro eliminado con exito",
										icon: "glyphicon glyphicon-ok",
										hide: true,
										type: "success"
									});
									pagina(campos["num_pagi"],"usuario");
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
/*$(".btnAccess").click(function(){
	alert("Accesos para ID : "+this.id);
	return false;
});*/
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
// Obtenemos listado de roles
$sqlScript = "Select * From " . $conf->getTbl_rol ();
$rolesE = $db->ejecutar ( $sqlScript );
$rolArray = Array ();
while ( $rowR = mysqli_fetch_array ( $rolesE ) ) {
	$rolArray [] = $rowR;
}
// Permisos de usuario en el modulo
$sqlAuthMod = "Select * From " . $conf->getTbl_acc_modulo () . " Where id_usuario = '" . $_SESSION ['i'] . "' And id_modulo = '" . $_SESSION ['idmod'] . "'";
$sqlAuthExec = $db->ejecutar ( $sqlAuthMod );
$rowsAuth = mysqli_fetch_array ( $sqlAuthExec );
$sql = "Select * from " . $tabla . " Where id_rol > 1";
// Consulta que devuelve todos los registros
$lista0 = $db->ejecutar ( $sql );
// Se cuentan los registros devueltos por la consulta SQL $lista0
$totalSql = mysqli_num_rows ( $lista0 );

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

/* Obtenemos los registros solicitados */
$sqlPag = "SELECT * FROM " . $tabla . " Where id_rol > 1 ORDER BY usr_usuario LIMIT " . $offset . "," . $rowCount . "";
// Consulta SQL con la que se sacar� el listado de registros
$lista1 = $db->ejecutar ( $sqlPag );
// Bucle para generar el listado de registros
$forms = Array ();
echo '<h4>MANTENIMIENTO DE USUARIOS</h4>';
echo '<table class="table table-condensed table-bordered">';
echo '<thead>';
echo '<tr>';
echo '<th>USUARIO</th>';
echo '<th>NOMBRE</th>';
echo '<th>ESTADO</th>';
/*
 * echo '<th>FECHA</th>'; echo '<th>HORA</th>'; echo '<th>USUARIO</th>';
 */
echo '<th>ROL</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
while ( $fila = mysqli_fetch_array ( $lista1 ) ) {
	echo '<tr>';
	echo '<td>' . $fila [1] . '</td><td>' . $fila [3] . '</td><td>' . $fila [4] . '</td>';
	// echo '<td>'.$fila[5].'</td><td>'.$fila[6].'</td><td>'.$fila[7].'</td>';
	$RolSql = "Select rol_descripcion From " . $conf->getTbl_rol () . " Where id_rol='" . $fila [8] . "'";
	$RolExec = $db->ejecutar ( $RolSql );
	$RolRow = $db->obtener ( $RolExec, 0 );
	echo '<td>' . $RolRow [0] . '</td>';
	// Pone link segun acceso en modulo
	if ($rowsAuth ['acc_edit'] == 1) {
		echo '<td align="center">
			<a class="btnMtto" href="#" id="' . $fila [0] . '">
				<img src="css/themes/redmond/images/pencil.png">
			</a>
   			</td>';
	}
	if ($rowsAuth ['acc_del'] == 1) {
		echo '<td align="center">
				<a class="btnDelete" href="#" id="' . $fila [0] . '">
					<img src="css/themes/redmond/images/cross.png">
				</a>
   			</td>';
	}
	if ($rowsAuth ['acc_del'] == 1) {
		echo '<td align="center">
		<a class="btnAccess" href="?c=usua&a=mostrar&id=' . $fila [0]. '&id2=' . $fila [0] . '">
			<img src="css/themes/redmond/images/bullet_key.png">
		</a>
		</td>';
	}
	$frmId = 'frmEdit' . $fila [0];
	$frmIdDel = 'frmDelete' . $fila [0];
	$frmDivId = 'formDiv' . $fila [0];

	/*
	 * GUARDAMOS EL FORMULARIO GENERADO EN UN ARREGLO PARA LUEGO TRABAJARLO CON
	 * SU VALIDACION
	 */
	$js_script = '
		<script type="text/javascript">
			$("#' . $frmId . '").validate({
		    rules: {
				usr_usuario: {
					required: true
				},
				usr_nombre:{
					required: true
				},
			    id_rol: {
				    required: true
			    }
		   },
		   messages: {
		     	usr_usuario: {
			     	required : "Digite usuario"
		     	},
				usr_nombre: {
					required: "Digite nombre"
				},
		     	id_rol: {
			     	required: "Seleccion Rol"
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
						$("input[type=submit]").attr("disabled","disabled");
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
									title: "adicionado",
									text: "registro adicionado",
									icon: "glyphicon glyphicon-ok",
									hide: true,
									type: "success"
								});
							    $("#' . $frmId . ' input[type=reset]").click();
								$("#' . $frmDivId . '").dialog("destroy").remove();
								pagina(campos["num_pagi"],"usuario");
							}
						}
					},
					error : function(XMLHttpRequest, textStatus, errorThrown) {
					    $.pnotify({
							text: "Error en la ejecucion",
							hide: true,
							type: "error"
						});
					}
				});
		   }
		});
		</script>
	';
	$htmlForm = '
		<div id="' . $frmDivId . '" style="display: none; overflow: hidden;">
		<div class="container-fluid">
		<div class="row-fluid">
		<fieldset>
		<form class="form-horizontal" id="' . $frmId . '" name="' . $frmId . '" method="POST">
			<div class="control-group">
				<label class="control-label" for="">Usuario</label>
				<div class="controls">
					<input type="text" value="' . $fila [1] . '" disabled />
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="usr_nombre">Nombre</label>
				<div class="controls">
					<input type="text" name="usr_nombre" id="usr_nombre" value="' . $fila [3] . '" />
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="id_rol">Rol de Usuario</label>
				<div class="controls">
					<select name="id_rol" id="id_rol">';
	for($i = 0; $i < count ( $rolArray ); $i ++) {
		if ($rolArray [$i] ['id_rol'] != $fila [8]) {
			$htmlForm .= '<option value="' . $rolArray [$i] ['id_rol'] . '">' . $rolArray [$i] ['rol_descripcion'] . '</option>';
		} else {
			$htmlForm .= '<option selected="selected" value="' . $rolArray [$i] ['id_rol'] . '">' . $rolArray [$i] ['rol_descripcion'] . '</option>';
		}
	}
	$htmlForm .= '
					</select>
				</div>
			</div>
			<input type="hidden" name="usr_usuario" id="usr_usuario" value="' . $fila [1] . '" />
			<input type="hidden" name="id_usuario" id="id_usuario" value="' . $fila [0] . '" />
			<input type="hidden" value="edit" id="accion" name="accion">
			<input type="hidden" value="usuario" id="tabla" name="tabla">
			<input type="hidden" value="' . $_SESSION ['u'] . '" id="usr_crea" name="usr_crea">
			<input type="hidden" value="' . $pageIndex . '" id="num_pagi" name="num_pagi">
			<div class="control-group">
				<div class="controls">
					<button class="m-btn blue-stripe" type="submit" id="btnEnviar" name="btnEnviar">Modificar</button>
					<input type="reset" value="" style="display:none;">
				</div>
			</diV>
		</form>
		</fieldset>
		</div>
		</div>
		<form id="' . $frmIdDel . '" name="' . $frmIdDel . '" method="POST">
			<input type="hidden" name="usr_usuario" id="usr_usuario" value="' . $fila [1] . '" />
			<input type="hidden" name="id_usuario" id="id_usuario" value="' . $fila [0] . '" />
			<input type="hidden" value="delete" id="accion" name="accion">
			<input type="hidden" value="usuario" id="tabla" name="tabla">
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
echo '<ul id="pagination-digg">';

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


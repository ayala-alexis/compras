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
		width: 800,
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
		width: 800,
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
			height:170,
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
										type: "success"
									});
									pagina(campos["num_pagi"],"empresa");
									$("#dialog-confirm").dialog( "close" );
								}
							}
						},
						error : function(XMLHttpRequest, textStatus, errorThrown) {
							$.pnotify({
								title: "error",
								text: "Error en la ejecucion.",
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
		})
		.dialog("widget")
		.find(".ui-dialog-buttonpane button")
		.eq(0).addClass("btn btn-sm btn-primary").end()
		.eq(1).addClass("btn btn-sm btn-default").end();
	return false;
});
$(".btnBod").click(function(){
	$("#DivBod").css('display','block');
	$("#DivBod").dialog({
		title: 'Bodegas de Empresa',
		width: 700,
		show: 'fold',
		hide: 'blind',
		modal: true
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
$totalSql = mysqli_num_rows ( $lista0 );

// Registros a mostrar en cada p�gina
$rowCount = 10;
$pagesCount = ( int ) ceil ( $totalSql / $rowCount );
$pageIndex = isset ( $_POST ['pag'] ) ? ( int ) $_POST ['pag'] : 0;
if ($pageIndex > $pagesCount) {
	$pageIndex = $pagesCount - 1;
}
$offset = $pageIndex * $rowCount;
$_SESSION ['p'] = 0;
$_SESSION ['p'] = $pageIndex;

/* Obtenemos los registros solicitados */
$sqlPag = "SELECT * FROM " . $tabla . " ORDER BY id_empresa LIMIT " . $offset . "," . $rowCount . "";
// Consulta SQL con la que se sacar� el listado de registros
$lista1 = $db->ejecutar ( $sqlPag );
// Bucle para generar el listado de registros
$forms = Array ();
echo '<h4 class="text-blue">Mantenimiento Maestro de Empresas</h4>';
echo '<table class="table table-condensed table-bordered">';
echo '<thead>';
echo '<tr>';
echo '<th>Nombre</th>';
echo '<th>Razon</th>';
echo '<th>Direccion</th>';
echo '<th>NIT</th>';
echo '<th>Registro</th>';
echo '<th width="15%">&nbsp</th>';
/*
 * echo '<th>FECHA</th>'; echo '<th>HORA</th>'; echo '<th>USUARIO</th>';
 */
echo '</tr>';
echo '</thead>';
echo '<tbody>';
while ( $fila = mysqli_fetch_array ( $lista1 ) ) {
	echo '<tr>';
	echo '<td>' . $fila [1] . '</td><td>' . $fila [2] . '</td><td>' . $fila [3] . '</td>';
	echo '<td>' . $fila [4] . '</td><td>' . $fila [5] . '</td>';
	// echo '<td>'.$fila[12].'</td><td>'.$fila[13].'</td>';
	// echo '<td>'.$fila[14].'</td>';
	// Pone link segun acceso en modulo
	if ($rowsAuth ['acc_edit'] == 1) {
		echo '<td align="center">
			<a class="btnMtto" href="#" id="' . $fila [0] . '" rel="tooltip" title="editar informacion">
				<span class="glyphicon glyphicon-pencil"></span>
			</a>';
		echo '<a href="?c=emp&a=bod&ie=' . $fila [0] . '&id=' . $_SESSION ['idmod'] . '" rel="tooltip" title="bodegas">
				<span class="glyphicon glyphicon-home"></span>
			</a>';
	}
	if ($rowsAuth ['acc_edit'] == 1 /*&& $fila [10] == 'LOCAL'*/) {
		echo '<a href="?c=emp&a=cc&ie=' . $fila [0] . '&id=' . $_SESSION ['idmod'] . '" rel="tooltip" title="centros de costo">
				<span class="glyphicon glyphicon-list-alt"></span>
			</a>';
	}
	if ($rowsAuth ['acc_edit'] == 1 && $fila [10] == 'LOCAL' && $fila [6] == '1') {
		echo '<a href="?c=emp&a=pre&ie=' . $fila [0] . '&id=' . $_SESSION ['idmod'] . '" rel="tooltip" title="presupuesto">
				<span class="glyphicon glyphicon-usd"></span>
			</a>';
	}
	if ($rowsAuth ['acc_del'] == 1) {
		echo '
				<a class="btnDelete" href="#" id="' . $fila [0] . '" rel="tooltip" title="borrar empresa">
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
					emp_nombre: {
						required: true,
						maxlength: 75
					},
					emp_razon:{
						required: true,
						maxlength: 75
					},
			     	emp_direccion: {
			       		required: true,
			       		maxlength: 250
			     	},
				    emp_nit: {
					    required: true,
					    maxlength: 20
				    },
				    emp_registro: {
					    required: true,
					    maxlength: 20
				    },
					emp_usa_presupuesto: {
						required: true
					},
					emp_observaciones: {
						required: true
					},
					emp_telefono: {
						required: true
					}
			   },
			   messages: {
			     	emp_nombre: {
				     	required : "Digite nombre"
			     	},
					emp_razon: {
						required: "Digite razon"
					},
			     	emp_direccion: {
			       		required: "Digite direccion"
			     	},
			     	emp_nit: {
				     	required: "Digite NIT"
			     	},
			     	emp_registro: {
				     	required: "Digite Registro"
			     	},
					emp_usa_presupuesto: {
						required: "� Presupuesto ?"
					},
					emp_observaciones: {
						required: "Digite Observaciones"
					},
					emp_telefono: {
						required: "Digite Telefono"
					}
			   },
			submitHandler: function(form) {
			    var campos=xajax.getFormValues("' . $frmId . '");
				campos["num_pagi"] =' . $pageIndex . ';
				if(campos["emp_usa_presupuesto"] == "1"){
					campos["emp_origen_cc"] = campos["emp_origen_presupuesto"];
				}
				if($("input[name=grande'.$frmId.']").is(":checked")){
					campos["emp_grande"] = "1";
		    	} else {
		    		campos["emp_grande"] = " ";
		    	}
				myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
				$.ajax({
					type : "POST",
					url : myUrl,
					data: {
						form: campos
					},
					beforeSend: function(){
						$.pnotify({
							title: "realizando peticion..",
							text: "intentando actualizar registro, por favor espere...",
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
									title: "actualizado",
									text: "registro actualizado.",
									icon: "glyphicon glyphicon-ok",
									hide: true,
									type: "success"
								});
							    $("#' . $frmId . ' input[type=reset]").click();
								$("#' . $frmDivId . '").dialog("destroy").remove();
								pagina(campos["num_pagi"],"empresa");
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
					}
				});
		   }
		});
		$("#' . $frmId . ' #emp_usa_presupuesto").change(function(){
			var selectedVal = $(this).val();
			switch (selectedVal) {
			case "0":
					$("#' . $frmId . ' #origenPresupuesto").empty();
					$("#' . $frmId . ' #origenCC").empty();
					$("<div class=\"form-group\"><label class=\"control-label col-md-2\" for=emp_origen_cc>Origen C.C.</label><div class=\"col-md-10\"><select class=\"form-control input-sm\" id=emp_origen_cc name=emp_origen_cc><option value=LOCAL>Local</option><option value=AS400>AS/400</option></select></div></div>").hide().appendTo("#' . $frmId . ' #origenCC").fadeIn();
					$("#' . $frmId . ' #origenPresupuesto").append("<input type=hidden value= id=emp_origen_presupuesto name=emp_origen_presupuesto/>");
					break;
			case "1":
					$("#' . $frmId . ' #origenPresupuesto").empty();
					$("#' . $frmId . ' #origenCC").empty();
					$("<div class=\"form-group\"><label class=\"control-label col-md-2\" for=emp_origen_presupuesto>Origen Presupuesto</label><div class=\"col-md-10\"><select class=\"form-control input-sm\" id=emp_origen_presupuesto name=emp_origen_presupuesto><option value=LOCAL>Local</option><option value=AS400>AS/400</option></select></div></div>").hide().appendTo("#' . $frmId . ' #origenPresupuesto").fadeIn();
					break;
			default:
			    $("#' . $frmId . ' #origenPresupuesto").empty();
				$("#' . $frmId . ' #origenCC").empty();
			    break;
			}
		});
		</script>
	';
	/* FORMULARIO HTML */
	$htmlForm = '
		<div id="' . $frmDivId . '" style="display: none; overflow: hidden;">
			<fieldset>
				<legend>Modificar Empresa</legend>
					<form class="form-horizontal" id="' . $frmId . '" name="' . $frmId . '" method="POST" role="form">

						<div class="form-group">
							<label class="control-label col-md-2">Codigo</label>
							<div class="col-md-10">
								<input class="form-control input-sm" type="text" value="' . $fila [0] . '" disabled />
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-md-2" for="emp_nombre">Nombre</label>
							<div class="col-md-10">
								<input class="form-control input-sm" type="text" name="emp_nombre" id="emp_nombre" value="' . $fila [1] . '" />
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-md-2" for="emp_razon">Razon</label>
							<div class="col-md-10">
								<input class="form-control input-sm" type="text" name="emp_razon" id="emp_razon" value="' . $fila [2] . '" />
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-md-2" for="emp_direccion">Direccion</label>
							<div class="col-md-10">
								<textarea class="form-control input-sm" rows="" cols="" id="emp_direccion" name="emp_direccion">' . $fila [3] . '</textarea>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-md-2" for="emp_nit">NIT</label>
							<div class="col-md-10">
								<input class="form-control input-sm" type="text" name="emp_nit" id="emp_nit" value="' . $fila [4] . '" />
							</div>
						</div>
							    		
						<div class="form-group">
		    				<div class="col-sm-offset-2 col-sm-10">
								<div class="checkbox">
						            <label>
						                <input name="grande' . $frmId . '" id="grande' . $frmId . '" type="checkbox" value="" ';
						if($fila['emp_grande'] == '1') {
							$htmlForm .= ' checked';
						} else {
							$htmlForm .= ' ';
						}
						                $htmlForm .='>
						                <span class="cr"><i class="cr-icon fa fa-check"></i></span>
						                Gran Contribuyente
						            </label>
						        </div>
						    </div>
						</div>
							    		
							    		

						<div class="form-group">
							<label class="control-label col-md-2" for="emp_registro">Registro</label>
							<div class="col-md-10">
								<input class="form-control input-sm" type="text" name="emp_registro" id="emp_registro" value="' . $fila [5] . '" />
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-md-2" for="emp_telefono">Telefono</label>
							<div class="col-md-10">
								<input class="form-control input-sm" type="text" name="emp_telefono" id="emp_telefono" value="' . $fila [11] . '" />
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-md-2" for="emp_usa_presupuesto">Presupuesto</label>
							<div class="col-md-10">
								<select class="form-control input-sm" id="emp_usa_presupuesto" name="emp_usa_presupuesto">';
								if ($fila [6] == 1) {
									$htmlForm .= '<option value="1" selected="selected">SI</option><option value="0">NO</option>';
								} else {
									$htmlForm .= '<option value="0" selected="selected">NO</option><option value="1">SI</option>';
								}
	$htmlForm .= '
								</select>
							</div>
						</div>

						<div id="origenPresupuesto">
							<div class="form-group">
								<label class="control-label col-md-2" for="emp_origen_presupuesto">Origen Presupuesto</label>
								<div class="col-md-10">
									<select class="form-control input-sm" id="emp_origen_presupuesto" name="emp_origen_presupuesto">';
									if ($fila [7] == "LOCAL") {
										$htmlForm .= '<option value="LOCAL" selected="selected">Local</option><option value="AS400">AS/400</option>';
									} else {
										$htmlForm .= '<option value="AS400" selected="selected">AS/400</option><option value="LOCAL">Local</option>';
									}
	$htmlForm .= '					</select>
								</div>
							</div>
						</div>';
	$htmlForm .= '
							<div id="origenCC">
								<div class="form-group">
									<label class="control-label col-md-2" for="emp_origen_cc">Origen C.C.</label>
									<div class="col-md-10">
										<select class="form-control input-sm" id="emp_origen_cc" name="emp_origen_cc">';
											if ($fila [10] == "LOCAL") {
												$htmlForm .= '<option value="LOCAL" selected="selected">Local</option><option value="AS400">AS/400</option>';
											} else {
												$htmlForm .= '<option value="AS400" selected="selected">AS/400</option><option value="LOCAL">Local</option>';
											}
	$htmlForm .= '
										</select>
									</div>
								</div>
							</div>';
	$htmlForm .= '
							<div class="form-group">
								<label class="control-label col-md-2" for="emp_observaciones">Observaciones</label>
								<div class="col-md-10">
									<textarea class="form-control input-sm" rows="" cols="" id="emp_observaciones" name="emp_observaciones">' . $fila [9] . '</textarea>
								</div>
							</div>

							<div class="form-group">
								<div class="col-md-offset-2 col-md-10">
									<button class="btn btn-sm btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-pencil"></span> Modificar</button>
									<input type="reset" value="" style="display:none;">
								</div>
							</div>
							<input type="hidden" name="id_empresa" id="id_empresa" value="' . $fila [0] . '" />
							<input type="hidden" value="edit" id="accion" name="accion">
							<input type="hidden" value="empresa" id="tabla" name="tabla">
							<input type="hidden" value="' . $_SESSION ['u'] . '" id="usr_crea" name="usr_crea">
							<input type="hidden" value="' . $pageIndex . '" id="num_pagi" name="num_pagi">
					</form>
			</fieldset>
			<form id="' . $frmIdDel . '" name="' . $frmIdDel . '" method="POST">
				<input type="hidden" name="emp_nombre" id="emp_nombre" value="' . $fila [1] . '" />
				<input type="hidden" name="id_empresa" id="id_empresa " value="' . $fila [0] . '" />
				<input type="hidden" value="delete" id="accion" name="accion">
				<input type="hidden" value="empresa" id="tabla" name="tabla">
				<input type="hidden" value="' . $_SESSION ['u'] . '" id="usr_crea" name="usr_crea">
				<input type="hidden" value="' . $pageIndex . '" id="num_pagi" name="num_pagi">
			</form>
		</div>';
	$htmlFormJS = $htmlForm . $js_script;
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
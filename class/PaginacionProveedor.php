<?php session_start(); ?>
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
							$.pnotify({
								title: "realizando peticion...",
								text: "intentado eliminar registro, por favor espere...",
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
									pagina(campos["num_pagi"],"proveedor");
									$("#dialog-confirm").dialog( "close" );
								}
							}
						},
						error : function(XMLHttpRequest, textStatus, errorThrown) {
							$.pnotify({
								title: "error...",
								text: "Error en la ejecucion",
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
// Tabla de Paginacion
$tabla = $_POST ['tab'];
// Iniciamos instancia a la clase DB
$db = DB::getInstance ();
// Parametros de Configuracion
$conf = Configuracion::getInstance ();
$sql_Cat = "Select id_categoria, cat_descripcon From ".$conf->getTbl_categoria()." Order by cat_descripcon";
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
	$filtro4 = " a.id_categoria = ".$_POST['filtrocategoria'];
}
if(!empty($_POST['filtronombre'])) {
	$filtro3 = " a.prov_nombre Like '%".$_POST['filtronombre']."%' ";
}

// Seleccionamos todos para contarlos
$sql = "Select a.*, b.* from " . $tabla." a"
	." Join ".$conf->getTbl_categoria()." b"
	." On a.id_categoria = b.id_categoria "
	.$filtro1.$filtro3.$filtro2.$filtro4;
//echo $sql.'<br>';
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

/* Obtenemos los registros solicitados */
$sqlPag = "SELECT a.*, b.* FROM " . $tabla. " a"
	." Join ".$conf->getTbl_categoria()." b"
	." On a.id_categoria = b.id_categoria"
	.$filtro1.$filtro3.$filtro2.$filtro4
	." ORDER BY a.id_categoria LIMIT " . $offset . "," . $rowCount . "";
try {
	// Consulta SQL con la que se sacar� el listado de registros
	$lista1 = $db->ejecutar ( $sqlPag );
} catch ( Exception $e ) {
	echo $e->getMessage ();
	die ();
}
//echo '<pre>'.$sqlPag.'</pre>';
// Bucle para generar el listado de registros
$forms = Array ();
echo '<h4 class="text-blue">Mantenimiento Maestro de Proveedores</h4>';
echo '<form class="form-horizontal" onSubmit="return false;">';
echo '<table width="100%" border="0" cellpadding="2" cellspacing="1">
	<tr>
	  <td bgcolor="#f5f5f5"><b>B&uacute;squeda en Registros</b></td>
	</tr>
	<tr>
		<td>
			<div class="form-group">
			<label class="control-label col-md-2" for="filtrocategoria">Categoria</label>
			<div class="col-md-5">
				<select class="form-control input-sm" name="filtrocategoria" id="filtrocategoria">
					<option value="todos">-- Todos los proveedores --</option>
					';
					while($rowCat = mysqli_fetch_array($run_Cat)) {
						echo '<option value="'.$rowCat[0].'" ';
						if($rowCat[0] == $_POST['filtrocategoria']) {
							echo "selected";
						} else  {
							echo "";
						}
						echo '>'.$rowCat[1].'</option>';
					}
			echo '</select>
			<div>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<div class="form-group">
				<label class="control-label col-md-2" for="filtronombre">Nombre</label>
				<div class="col-md-5">
					<input type="text" class="form-control input-sm" id="filtronombre" name="filtronombre" placeholder="buscar" value="'.$_POST['filtronombre'].'">
				</div>
				<div class="col-md-1">
					<button class="btn btn-sm btn-default" id="btnBuscar" name="btnBuscar"><i class="glyphicon glyphicon-search"></i></button>
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
/*
echo '
<div class="btn-group">';
	if ($rowsAuth ['acc_xls'] == 1) {
  		echo '<button type="button" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-print"></span> Imprimir</button>
  		<button type="button" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-download-alt"></span> Exportar</button>';
	}
	// Se finaliza el listado de p�ginas
	if ($rowsAuth ['acc_add'] == 1) {
		echo '<button type="button" class="btn btn-sm btn-default" id="btnAdd"><span class="glyphicon glyphicon-plus"></span> Adicionar</button>';
	}
echo '</div>';*/
echo '<table class="table table-condensed" id="tabla">';
echo '<thead>';
echo '<tr>';
echo '<th>Categoria</th>';
echo '<th>Nombre</th>';
echo '<th>Telefono</th>';
echo '<th>contacto</th>';
if ($rowsAuth ['acc_edit'] == 1) {
	echo '<th>&nbsp</th>';
}
if ($rowsAuth ['acc_edit'] == 1) {
	echo '<th>&nbsp</th>';
}
if ($rowsAuth ['acc_del'] == 1) {
	echo '<th>&nbsp</th>';
}

/*
 * echo '<th>FECHA</th>'; echo '<th>HORA</th>'; echo '<th>USUARIO</th>';
 */
echo '</tr>';
echo '</thead>';
echo '<tbody class="searchable">';
while ( $fila = mysqli_fetch_array ( $lista1 ) ) {
	echo '<tr>';
	echo '<td>' . $fila ['cat_descripcon'] . '</td><td>' . $fila [2] . '</td>';
	echo '<td>' . $fila [6] . '</td><td>' . $fila [11] . '</td>';
	// echo
	// '<td>'.$fila[12].'</td><td>'.$fila[13].'</td><td>'.$fila[14].'</td>';
	// Pone link segun acceso en modulo
	if ($rowsAuth ['acc_edit'] == 1) {
		echo '<td align="center">
			<a class="btnMtto" href="#" id="' . $fila [1] . '" rel="tooltip" title="editar informacion">
				<span class="glyphicon glyphicon-pencil"></span>
			</a>
   			</td>';
	}
	$sql_qry_count_listas = "Select count(id_lista) From ".$conf->getTbl_lista().
		" Where id_proveedor = ".$fila['id_proveedor']."";
	$run_qry_count_listas = $db->ejecutar($sql_qry_count_listas);
	$row_query_count_listas = mysqli_fetch_array($run_qry_count_listas);
	
	if ($rowsAuth ['acc_edit'] == 1) {
		echo '<td align="center">
		<a class="btnLista" href="?c=prov&a=lista&id=' . $fila [1] . '" rel="tooltip" title="lista de precios">
			<span class="';
			if($row_query_count_listas[0] > 0) {
				echo 'glyphicon glyphicon-list-alt text-succes';
			} else {
				echo 'glyphicon glyphicon-ban-circle text-danger';
			}
			echo '"></span>
		</a>
		</td>';
	}
	if ($rowsAuth ['acc_del'] == 1) {
		echo '<td align="center">
				<a class="btnDelete" href="#" id="' . $fila [1] . '" rel="tooltip" title="eliminar">
				<span class="glyphicon glyphicon-remove"></span>
				</a>
   			</td>';
	}
	$frmId = 'frmEdit' . $fila [1];
	$frmIdDel = 'frmDelete' . $fila [1];
	$frmDivId = 'formDiv' . $fila [1];

	/*
	 * GUARDAMOS EL FORMULARIO GENERADO EN UN ARREGLO PARA LUEGO TRABAJARLO, SU
	 * VALIDACION JAVASCRIPT SE LLEVA A TABLA JS_SCRIPTS
	 */
	$js_script = '
		<script type="text/javascript">
			$("#' . $frmId . '").validate({
			    rules: {
					id_categoria: {
						required: true
					},
					prov_nombre:{
						required: true
					},
					prov_nit: {
						required: true
					},
			     	prov_razon: {
			       		required: true
			     	},
				    prov_telefono1: {
					    required: true
				    },
				    prov_direccion1: {
					    required: true
				    },
					prov_contacto1: {
						required: true
					}
			   },
			   messages: {
					id_categoria: {
						required : "Seleccione categoria"
					},
					prov_nombre: {
						required: "Digite nombre"
					},
					prov_razon: {
						required: "Digite razon"
					},
					prov_nit: {
						required: "Digite NIT"
					},
					prov_telefono1: {
						required: "Digite telefono"
					},
					prov_direccion1: {
						required: "Digite direccion"
					},
					prov_contacto1: {
						required: "Digite contacto"
					}
			   },
			submitHandler: function(form) {
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
							text: "intentando modificar registro, espere...",
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
								pagina(campos["num_pagi"],"proveedor");
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
					}
				});
		   }
		});
		</script>
	';
	/* FORMULARIO HTML */
	$htmlForm = '
		<div id="' . $frmDivId . '" style="display: none; overflow: hidden;">
		<form role="form" class="form-horizontal" id="' . $frmId . '" name="' . $frmId . '" method="POST">

			<div class="form-group">
				<label class="control-label col-md-2">categoria</label>
				<div class="col-md-10">
					<select class="form-control input-sm" name="id_categoria" id="id_categoria">
						<option value="' . $fila [0] . '">' . $db->getCat ( $fila [0] ) . '</option>';
						$sqlCat = "Select * From " . $conf->getTbl_categoria () . " Order By cat_descripcon";
						$listaCat = $db->ejecutar ( $sqlCat );
						while ( $filaCat = mysqli_fetch_array ( $listaCat ) ) {
							if ($filaCat [0] != $fila [0]) {
								$htmlForm .= '<option value="' . $filaCat [0] . '">' . $filaCat [1] . '</option>';
							}
						}
	$htmlForm .= '
					</select>
				  </div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2">Nombre</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="prov_nombre" id="prov_nombre" value="'.$fila[2].'" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2">Razon</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" id="prov_razon" name="prov_razon" value="'.$fila[3].'" />
				</div>
			</div>
								
			<div class="form-group">
			<label class="control-label col-md-2" for="prov_tamanio">Tama&ntilde;o</label>
			<div class="col-md-10">
				<select class="form-control input-sm" name="prov_tamanio" id="prov_tamanio">
					<option value="PEQUE&Ntilde;A" '.($fila[22] == "PEQUE�A" ? "selected" : "").'>PEQUE&Ntilde;A</option>
					<option value="MEDIANA" '.($fila[22] == "MEDIANA" ? "selected" : "").'>MEDIANA</option>
					<option value="GRANDE" '.($fila[22] == "GRANDE" ? "selected" : "").'>GRANDE</option>
				</select>
			</div>
			</div>
		
			<div class="form-group">
				<label class="control-label col-md-2" for="prov_giro">Giro</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="prov_giro" id="prov_giro" value="'.$fila[21].'" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2" for="prov_email">E-mail</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="email" name="prov_email" id="prov_email" value="'.$fila[13].'" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2">NIT</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" id="prov_nit" name="prov_nit" value="'.$fila[4].'" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2" for="prov_registro">No. Registro</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="prov_registro" id="prov_registro" value="'.$fila[5].'" />
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-2" for="prov_dias">Dias Credito</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="prov_dias" id="prov_dias" value="'.$fila[14].'" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2">Telefono 1</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="prov_telefono1" id="prov_telefono1" value="'.$fila[6].'" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2" for="prov_telefono2">Telefono 2</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="prov_telefono2" id="prov_telefono2" value="'.$fila[7].'" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2" for="prov_fax">FAX</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="prov_fax" id="prov_fax" value="'.$fila[8].'" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2">contacto</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="prov_contacto1" id="prov_contacto1" value="'.$fila[11].'" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2">direccion</label>
				<div class="col-md-10">
					<textarea class="form-control input-sm" rows="" cols="" id="prov_direccion1" name="prov_direccion1">'.$fila[9].'</textarea>
				</div>
			</div>

			<input type="hidden" value="edit" id="accion" name="accion">
			<input type="hidden" value="proveedor" id="tabla" name="tabla">
			<input type="hidden" value="'.$fila[1].'" id="id_proveedor" name="id_proveedor">
			<input type="hidden" value="'.$fila[19].'" id="prov_nvocod" name="prov_nvocod">
			<input type="hidden" value="'.$_SESSION['u'].'" id="usr_crea" name="usr_crea">
			<input type="hidden" value="'.$pageIndex.'" id="num_pagi" name="num_pagi">
			<div class="form-group">
				<div class="col-md-offset-2 col-md-10">
					<button class="btn btn-sm btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-pencil"></span> Modificar</button>
					<input type="reset" value="" style="display:none;">
				</div>
			</div>
		</form>
		<form id="'.$frmIdDel.'" name="'.$frmIdDel.'" method="POST">
			<input type="hidden" name="id_proveedor" id="id_proveedor " value="'.$fila[1].'" />
			<input type="hidden" value="'.$fila[19].'" id="prov_nvocod" name="prov_nvocod">
			<input type="hidden" value="delete" id="accion" name="accion">
			<input type="hidden" value="proveedor" id="tabla" name="tabla">
			<input type="hidden" value="'.$_SESSION['u'].'" id="usr_crea" name="usr_crea">
			<input type="hidden" value="'.$pageIndex.'" id="num_pagi" name="num_pagi">
		</form>
		</div>';
	$htmlFormJS = $js_script . $htmlForm;
	$forms[$fila[1]] = $htmlFormJS;
	echo '</tr>';
}
echo '</tbody>';
echo '</table>';
// Se inicia el listado de p�ginas
echo '<div class="pagination">';
echo '<ul class="pagination" id="pagination-digg">';

// Anterior Deshabilitado
if ($pageIndex == 0) {
	echo '<li class="disabled previous"><a href="#"><span class="glyphicon glyphicon-chevron-left"></span> Anterior</a></li>';
}
// Si la p�gina actual no es la primera, se muestra el enlace a la p�gina
// anterior
if ($pageIndex > 0) {
	echo '<li class="previous"><a href="'.($pageIndex-1).','.$tabla.'"><span class="glyphicon glyphicon-chevron-left"></span> Anterior</a></li>';
}
// Se saca el listado de p�ginas mediante un bucle
$pagesToShow = 1; // P�ginas que aparecen antes y despu�s de la actual
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
		echo '<li><a href="'.$i.','.$tabla.'">'.($i+1) . '</a></li>';
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
	echo '<li class="next"><a href="' . ($pageIndex + 1) . ',' . $tabla . '">Siguiente <span class="glyphicon glyphicon-chevron-right"></span></a></li>';
}
// Ultima pagina
if ($pageIndex >= ($pagesCount - 1)) {
	echo '<li class="disabled next"><a href="#">Siguiente <span class="glyphicon glyphicon-chevron-right"></span></a></li>';
}
// Se finaliza el listado de p�ginas
if ($rowsAuth ['acc_add'] == 1) {
	echo '<li><a href="#" id="btnAdd">Adicionar <span class="glyphicon glyphicon-plus"></span></a></li>';
}
echo '</ul>';
// Pone link segun acceso en modulo
echo '</div>';
echo '<input type="hidden" id="num_pag" name="num_pag" value="' . $_SESSION ['p'] . '">';
foreach ( $forms as $fr ) {
	echo $fr;
}
?>
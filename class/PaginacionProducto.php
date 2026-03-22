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
$sql_Cat = "Select id_sublinea, sl_descripcion, sl_linea, sl_sublinea From ".$conf->getTbl_sublinea()." Order by sl_descripcion";
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
	$filtro4 = " sl_linea = '".substr($_POST['filtrocategoria'],0,2)."' And sl_sublinea = '".substr($_POST['filtrocategoria'],2,2)."' ";
}
if(!empty($_POST['filtronombre'])) {
	$filtro3 = " prod_descripcion Like '%".$_POST['filtronombre']."%' ";
}

$sql = "Select * from " . $tabla
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
$sqlPag = "SELECT * FROM " . $tabla
	.$filtro1.$filtro3.$filtro2.$filtro4
	. " ORDER BY sl_linea, sl_sublinea, prod_codigo "
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
echo '<h4 class="text-blue">Mantenimiento de Producto</h4>';
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
					echo '<option value="'.$rowCat[2].$rowCat[3].'" ';
					if($rowCat[2].$rowCat[3] == $_POST['filtrocategoria']) {
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
				<label class="control-label col-md-2" for="filtronombre">Descripcion</label>
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
echo '<table class="table table-condensed table-hover table-striped">';
echo '<thead>';
echo '<tr>';
echo '<th>Sublinea</th>';
echo '<th>Codigo</th>';
echo '<th>Descripcion</th>';
if ($rowsAuth ['acc_edit'] == 1) {
	echo '<th width="5%">&nbsp;</th>';
}
if ($rowsAuth ['acc_del'] == 1) {
	echo '<th width="5%">&nbsp;</th>';
}

/*
 * echo '<th>FECHA</th>'; echo '<th>HORA</th>'; echo '<th>USUARIO</th>';
 */
echo '</tr>';
echo '</thead>';
echo '<tbody>';
while ( $fila = mysqli_fetch_array ( $lista1 ) ) {
	echo '<tr>';
	echo '<td>' . $db->getSublinea ( $fila [3], $fila [4] ) . '</td><td>' . $fila [1] . '</td><td>' . $fila [2] . '</td>';
	// echo
	// '<td>'.$fila[17].'</td><td>'.$fila[18].'</td><td>'.$fila[19].'</td>';
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
					prod_slinea: {
						required: true
					},
					prod_codigo:{
						required: true
					},
			     	prod_descripcion: {
			       		required: true
			     	}
			   },
			   messages: {
					prod_slinea: {
						required : "Seleccione sublinea"
					},
					prod_codigo: {
						required: "Digite codigo"
					},
					prod_descripcion: {
						required: "Digite descripcion"
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
							text: "intentando modificar registro de tabla, por favor espere...",
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
								pagina(campos["num_pagi"],"producto");
								$.pnotify({
									title: "actualizado",
									text: "Registro actualizado con exito!.",
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
							text: "Ocurrio un error en la ejecucion.",
							icon: "glyphicon glyphicon-ban-cricle",
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
			<form class="form-horizontal" id="' . $frmId . '" name="' . $frmId . '" method="POST" role="form">

			<div class="form-group">
				<label class="control-label col-md-2">categoria</label>
				<div class="col-md-10">
				<select class="form-control input-sm" name="prod_slinea" id="prod_slinea">
				<option value="' . $fila [3] . "," . $fila [4] . '">' . $db->getSublinea ( $fila [3], $fila [4] ) . '</option>';
	$sqlCat = "Select * From " . $conf->getTbl_sublinea () . " Where sl_sublinea <> '00' Order By sl_linea, sl_sublinea";
	$listaCat = $db->ejecutar ( $sqlCat );
	while ( $filaCat = mysqli_fetch_array ( $listaCat ) ) {
		if ($filaCat [1] . $filaCat [2] != $fila [3] . $fila [4]) {
			$htmlForm .= '<option value="' . $filaCat [1] . "," . $filaCat [2] . '">' . $db->getSublinea ( $filaCat [1], $filaCat [2] ) . '</option>';
		}
	}
	$htmlForm .= '</select>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2">Codigo</label>
				<div class="col-md-10">
					<p class="form-control-static text-info">' . $fila [1] . '</p>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2">Descripcion</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" id="prod_descripcion" name="prod_descripcion" value="' . $fila [2] . '" />
				</div>
			</div>
						
		<div class="form-group">
		    <div class="col-sm-offset-2 col-sm-10">
		      <div class="checkbox">
		        <label>
		          <input type="checkbox" name="prod_solc" id="prod_solc" '.($fila['prod_solc'] == '1' ? "checked":"").'> Solicitud de Compra
		        </label>
		      </div>
		    </div>
		</div>
		  
		<div class="form-group">
		    <div class="col-sm-offset-2 col-sm-10">
		      <div class="checkbox">
		        <label>
		          <input type="checkbox" name="prod_req" id="prod_req" '.($fila['prod_req'] == '1' ? "checked":"").'> Requisicion de Suministro
		        </label>
		      </div>
		    </div>
		</div>
						

			<div class="form-group">
				<label class="control-label col-md-2" for="prod_observacion">Observaciones</label>
				<div class="col-md-10">
					<textarea class="form-control input-sm" rows="5" name="prod_observacion" id="prod_observacion">'.trim($fila [27]).'</textarea>
				</div>
			</div>
									
			<hr />

			<div class="form-group">
              <label for="amount" class="col-sm-6 control-label">PROVEEDOR</label>
              <div class="col-sm-2">
                <p class="form-control-static">PRECIO</p>
              </div>
			  <label class="col-sm-2 control-label">ORDEN</label>
			  <label class="col-sm-2 control-label">HASTA</label>
            </div>
            <div class="form-group">
              <label class="col-sm-6 control-label">('.$fila[22].') '.nombreProveedor($fila[22]).'</label>
              <div class="col-sm-2">
                <p class="form-control-static">'.'$ '.sprintf('%0.2f', $fila[28]	).'</p>
              </div>
			  <label class="col-sm-2 control-label">Primero</label>
			';
			$Row_1[0] = 0.00;
			if($fila[28] > 0) {
				$Sql_1 = "Select lis_fin_vigencia From ".$conf->getTbl_lista()." Where id_proveedor=".$fila[22]." and prod_codigo='".$fila[1]
			 		."' And prov_orden='01'";
				$Run_1 = $db->ejecutar($Sql_1);
				$Row_1 = mysqli_fetch_array($Run_1);
			}
			$htmlForm .= '<label class="col-sm-2 control-label">'.$Row_1[0].'</label>';
    $htmlForm .='</div>
			<div class="form-group">
              <label for="amount" class="col-sm-6 control-label">('.$fila[23].') '.nombreProveedor($fila[23]).'</label>
              <div class="col-sm-2">
                <p class="form-control-static">'.'$ '.sprintf('%0.2f', $fila[29]	).'</p>
              </div>
			  <label class="col-sm-2 control-label">Segundo</label>';
		    $Row_2[0] = 0.00;
		    if($fila[29] > 0) {
		    	$Sql_2 = "Select lis_fin_vigencia From ".$conf->getTbl_lista()." Where id_proveedor=".$fila[23]." and prod_codigo='".$fila[1]
		    	."' And prov_orden='02'";
		    	$Run_2 = $db->ejecutar($Sql_2);
		    	$Row_2 = mysqli_fetch_array($Run_2);
		    }
		    $htmlForm .= '<label class="col-sm-2 control-label">'.$Row_2[0].'</label>';
            $htmlForm .='</div>
			<div class="form-group">
              <label for="amount" class="col-sm-6 control-label">('.$fila[24].') '.nombreProveedor($fila[24]).'</label>
              <div class="col-sm-2">
                <p class="form-control-static">'.'$ '.sprintf('%0.2f', $fila[30]	).'</p>
              </div>
			  <label class="col-sm-2 control-label">Tercero</label>';
            $Row_3[0] = 0.00;
            if($fila[30] > 0) {
            	$Sql_3 = "Select lis_fin_vigencia From ".$conf->getTbl_lista()." Where id_proveedor=".$fila[24]." and prod_codigo='".$fila[1]
            	."' And prov_orden='03'";
            	$Run_3 = $db->ejecutar($Sql_3);
            	$Row_3 = mysqli_fetch_array($Run_3);
            }
            $htmlForm .= '<label class="col-sm-2 control-label">'.$Row_3[0].'</label>';
            $htmlForm .='
            </div>
			<div class="form-group">
              <label for="amount" class="col-sm-6 control-label">('.$fila[25].') '.nombreProveedor($fila[25]).'</label>
              <div class="col-sm-2">
                <p class="form-control-static">'.'$ '.sprintf('%0.2f', $fila[31]	).'</p>
              </div>
			  <label class="col-sm-2 control-label">Cuarto</label>';
            $Row_4[0] = 0.00;
            if($fila[31] > 0) {
            	$Sql_4 = "Select lis_fin_vigencia From ".$conf->getTbl_lista()." Where id_proveedor=".$fila[25]." and prod_codigo='".$fila[1]
            	."' And prov_orden='04'";
            	$Run_4 = $db->ejecutar($Sql_4);
            	$Row_4 = mysqli_fetch_array($Run_4);
            }
            $htmlForm .= '<label class="col-sm-2 control-label">'.$Row_4[0].'</label>';
            $htmlForm .='
            </div>
			<div class="form-group">
              <label for="amount" class="col-sm-6 control-label">('.$fila[26].') '.nombreProveedor($fila[26]).'</label>
              <div class="col-sm-2">
                <p class="form-control-static">'.'$ '.sprintf('%0.2f', $fila[32]	).'</p>
              </div>
			  <label class="col-sm-2 control-label">Quinto</label>';
            $Row_5[0] = 0.00;
            if($fila[32] > 0) {
            	$Sql_5 = "Select lis_fin_vigencia From ".$conf->getTbl_lista()." Where id_proveedor=".$fila[26]." and prod_codigo='".$fila[1]
            	."' And prov_orden='05'";
            	$Run_5 = $db->ejecutar($Sql_5);
            	$Row_5 = mysqli_fetch_array($Run_5);
            }
            $htmlForm .= '<label class="col-sm-2 control-label">'.$Row_5[0].'</label>';
            $htmlForm .='
            </div>


			<div class="form-group">
				<div class="col-md-offset-2 col-md-10">
					<input type="hidden" value="edit" id="accion" name="accion">
					<input type="hidden" value="producto" id="tabla" name="tabla">
					<input type="hidden" value="' . $fila [0] . '" id="id_producto" name="id_producto">
					<input type="hidden" value="' . $_SESSION ['u'] . '" id="usr_crea" name="usr_crea">
					<input type="hidden" value="' . $pageIndex . '" id="num_pagi" name="num_pagi">
					<button class="btn btn-sm btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-pencil"></span> Modificar</button>
					<input type="reset" value="" style="display:none;">
				</div>
			</div>
		</form>
		<form id="' . $frmIdDel . '" name="' . $frmIdDel . '" method="POST">
			<input type="hidden" name="id_producto" id="id_producto" value="' . $fila [0] . '" />
			<input type="hidden" value="delete" id="accion" name="accion">
			<input type="hidden" value="producto" id="tabla" name="tabla">
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
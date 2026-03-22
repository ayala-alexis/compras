<?php
session_start();
?>
<script>
$(document).ready(function(){
	$("#frmAdd").validate({
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
		    var campos=xajax.getFormValues("frmAdd");
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
						title: "adicionando...",
						text: "intentando adicionar registro, por favor espere...",
						icon: "glyphicon glyphicon-search",
						hide: true
					});
					$(":submit").addClass("disabled");
				},
				success : function(data){
					if(!$.isNumeric(data)){
				    	$.pnotify({
							title: 'error',
							text: data,
							icon: 'glyphicon glyphicon-ban-circle',
							hide: true,
							type: "error"
						});
					} else {
						if(data == 1 ){
							$.pnotify({
								title: 'error',
								text: data,
								icon: 'glyphicon glyphicon-ban-circle',
								hide: true,
								type: "error"
							});
						} else {
							$.pnotify({
								title: "adicionado",
								text: "Registro adicionado.",
								icon: "glyphicon glyphicon-ok",
								hide: true,
								type: "success"
							});
						    $("#formDiv input[type=reset]").click();
							$("#formDiv").dialog("close");
							pagina(campos["num_pagi"],"sublinea");
						}
					}
					$(":submit").removeClass("disabled");
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$.pnotify({
						title: "error...",
						text: "Ocurrio un error en la ejecucion.",
						icon: "glyphicon glyphicon-ban-circle",
						hide: true,
						type: "error"
					});
				    $(":submit").removeClass("disabled");
				}
			});
	   }
	});
});
</script>
<script>
/*
 * Paginacion
 */
function pagina(pagi, tabl){
	$.ajax({
		type : 'POST',
		url : location.protocol + "//" + location.host + '/sics/class/PaginacionSLProducto.php',
		data: {
			tab: tabl,
			pag: pagi
		},
		success : function(data, textStatus, jqXHR){
			$('#contenido').html(data);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			$.pnotify({
				title: "error...",
				text: "Error obteniendo datos...",
				icon: "glyphicon glyphicon-ban-circle",
				hide: true,
				type: "error"
			});
			/*$('#alertBoxes').html('<div id="boxError" class="alert alert-danger"></div>');
			$('#boxError').html('Ha ocurrido un error durante la ejecucion.');*/
		}
	});
};
pagina(0, 'sublinea');
</script>
<div id="contenido" class="row"></div>
<div id="formDiv" style="display: none; overflow: hidden;">
	<fieldset>
		<legend>Agregar Sub-linea</legend>
		<form id="frmAdd" name="frmAdd" method="POST" class="form-horizontal" action="" role="form">

			<div class="form-group">
				<label class="control-label col-md-2" for="sl_linea">Linea</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="sl_linea" id="sl_linea" placeholder="digite linea" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2" for="sl_sublinea">Sub-linea</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="sl_sublinea" id="sl_sublinea" placeholder="digite sub-linea" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2" for="sl_linea">Nombre</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="sl_descripcion" id="sl_descripcion" placeholder="digite descripcion" />
				</div>
			</div>
			<?php
			try {
				$db = DB::getInstance ();
				$conf = Configuracion::getInstance ();
			} catch ( Exception $e ) {
				echo $e->getMessage ();
				die ();
			}
			try {
				$sql = "Select id_tagasto, gas_tit_codigo, gas_det_codigo, gas_descripcion From " . $conf->getTbl_tagasto(). " Order By gas_tit_codigo, gas_det_codigo";
				$lista = $db->ejecutar ( $sql );
				$tablas = array();
				while ( $fila = mysqli_fetch_array ( $lista ) ) {
					if ($fila [2] != '00') {
						$tablas[] = $fila;
					}
				}
				//print_r($tablas);
			} catch ( Exception $e ) {
				echo $e->getMessage ();
				die ();
			}
			?>
			<div class="form-group text-center">
				<label  style="text-align: center !important;" class="control-label col-md-12 text-center" for="sl_linea">TABLA DE GASTO</label>
			</div>
			<?php
			try {
				$db = DB::getInstance ();
				$conf = Configuracion::getInstance ();
			} catch ( Exception $ey ) {
				echo $ey->getMessage ();
				die ();
			}
			try {
				$sql_e = "Select id_empresa, emp_nombre From " . $conf->getTbl_empresa(). " Order By emp_nombre";
				$lista_e = $db->ejecutar ( $sql_e );
			} catch ( Exception $e1 ) {
				echo $e1->getMessage ();
				die ();
			}
			?>
			<?php
			while ($row_e = mysqli_fetch_array($lista_e)){ 
			?>
			<div class="form-group">
				<label class="control-label col-md-6" for="<?php echo 'id_tabgas'.$row_e['id_empresa']; ?>"><?php echo $row_e['emp_nombre']; ?></label>
				<div class="col-md-6">
					
					<select class="form-control input-sm" name="<?php echo 'id_tabgas'.$row_e['id_empresa']; ?>" id="<?php echo 'id_tabgas'.$row_e['id_empresa']; ?>">
						<option value="0">Sin Gasto</option>
					<?php
					foreach ($tablas as $tabgas) {
					?>
						<option value="<?php echo $tabgas[0]; ?>"><?php echo $tabgas[1] . ' ' . $tabgas[2]; ?></option>
					<?php
					}
					?>
					</select>
				</div>
			</div>
			<?php 
			} 
			?>

			<input type="hidden" value="add" id="accion" name="accion">
			<input type="hidden" value="sublinea" id="tabla" name="tabla">
			<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
			<input type="hidden" value="1" id="num_pagi" name="num_pagi">

			<div class="form-group">
				<div class="col-md-offset-2 col-md-10">
					<button type="submit" class="btn btn-sm btn-primary" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-plus"></span> Adicionar</button>
					<button type="reset" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-ban-circle"></span> Limpiar</button>
					<input type="reset" value="" style="display: none;">
				</div>
			</div>
		</form>
	</fieldset>
</div>
<div id="alertBoxes"></div>
<div id="dialog-confirm" title="Eliminar Registro?"></div>
<?php
session_start ();
$_SESSION ['ie'] = $_GET ['ie'];
?>
<script>
$(document).ready(function(){
    $("#frmAdd").validate({
	    rules: {
			cc_codigo: {
				required: true
			},
			gas_codigo: {
				required: true
			},
			pres_anyo: {
				required: true
			},
			pres_pre01: {
				number: true
			},
			pres_pre02: {
				number: true
			},
			pres_pre03: {
				number: true
			},
			pres_pre04: {
				number: true
			},
			pres_pre05: {
				number: true
			},
			pres_pre06: {
				number: true
			},
			pres_pre07: {
				number: true
			},
			pres_pre08: {
				number: true
			},
			pres_pre09: {
				number: true
			},
			pres_pre10: {
				number: true
			},
			pres_pre11: {
				number: true
			},
			pres_pre12: {
				number: true
			}

	   },
	   messages: {
	     	cc_codigo: {
		     	required : "Seleccion Centro de Costo"
	     	},
			gas_codigo: {
				required: "Seleccion Gasto"
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
					$(":submit").attr("disabled","disabled");
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
								title: 'adicionado',
								text: 'registro adicionado con exito!',
								icon: 'glyphicon glyphicon-ok',
								hide: true,
								type: 'success'
							});
						    $("#formDiv input[type=reset]").click();
							$("#formDiv").dialog("close");
							pagina(campos["num_pagi"],"presupuesto");
						}
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$(":submit").removeAttr("disabled");
				    showNotification({
						type : "error",
				        message: "Ha ocurrido un error durante la Adicion.",
				        autoClose: true,
				        duration: 2
					});
				}
			});
	   }
	});
});
</script>
<script>
function pagina(pagi, tabl){
    myUrl = location.protocol + "//" + location.host + '/sics/class/PaginacionPresupuesto.php';
	$.ajax({
		type : 'POST',
		url : myUrl,
		data: {
			tab: tabl,
			pag: pagi
		},
		beforeSend: function(){
			$('input[type="submit"]').attr('disabled','disabled');
		},
		success : function(data){
			$('#contenido').hide().html(data).show();
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			$('#alertBoxes').html('<div id="boxError" class="alert alert-danger"></div>');
			$('#boxError').html('Ha ocurrido un error durante la ejecucion.'+textStatus);
		}
	});
};
pagina(0,'presupuesto');
</script>
<div id="contenido" class="row-fluid"></div>
<div id="formDiv" style="display: none; overflow: hidden;">
		<form class="form-horizontal" id="frmAdd" name="frmAdd" method="POST" role="form">
			<div class="form-group">
				<label class="control-label col-md-3" for="cc_codigo">Centro de Costo</label>
				<div class="col-md-9">
					<?php
					$db = DB::getInstance ();
					$conf = Configuracion::getInstance ();
					$sql = "Select * From " . $conf->getTbl_cecosto () . " Where id_empresa = '" . $_SESSION ['ie'] . "' Order by cc_descripcion";
					$lista = $db->ejecutar ( $sql );
					?>
					<select class="form-control input-sm" name="cc_codigo" id="cc_codigo">
					<?php
					while ( $fila = mysqli_fetch_array ( $lista ) ) {
						?>
					<option value="<?php echo $fila[2]; ?>"><?php echo $fila[3]?></option>
					<?php
					}
					?>
					</select>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-3" for="gas_codigo">Tabla de Gasto</label>
				<div class="col-md-9">
					<?php
					$sql = "Select * From " . $conf->getTbl_tagasto () . "  Order by gas_tit_codigo, gas_det_codigo";
					//$sql = "Select * From " . $conf->getTbl_tagasto () . " Where id_empresa = '" . $_SESSION ['ie'] . "' Order by gas_tit_codigo,gas_det_codigo";
					$lista = $db->ejecutar ( $sql );
					?>
					<select class="form-control input-sm" name="gas_codigo" id="gas_codigo">
					<?php
					$cierre = 'N';
					while ( $fila = mysqli_fetch_array ( $lista ) ) {
						if ($fila [3] == '00') {
							if($cierre == 'S') {
?>
							</optgroup>
							<optgroup label="<?php echo $fila[4]?>">
<?php 							} else {
							?>
							<optgroup label="<?php echo $fila[4]?>">
					<?php
}
						} else {
							?>
							<option value="<?php echo $fila[2].",".$fila[3]; ?>"><?php echo "[".$fila[2].$fila[3]."] ".$fila[4]?></option>
						<?php
						}
						$cierre = 'S';
					}
					?>
					</select>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-3" for="pres_anyo">A&ntilde;o</label>
				<div class="col-md-9">
					<select class="form-control input-sm" name="pres_anyo" id="pres_anyo">
						<option value="2014">2014</option>
						<option value="2015">2015</option>
						<option value="2016">2016</option>
						<option value="2017">2017</option>
						<option value="2018">2018</option>
						<option value="2019">2019</option>
						<option value="2020">2020</option>
						<option value="2021">2021</option>
						<option value="2022">2022</option>
						<option value="2023">2023</option>
						<option value="2024">2024</option>
						<option value="2025">2025</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-6">
					<div class="form-group">
						<label class="control-label col-md-3">enero</label>
						<div class="col-md-9">
							<input class="form-control input-sm" type="text" id="pres_pre01" name="pres_pre01" placeholder="0.00" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-3">marzo</label>
						<div class="col-md-9">
							<input class="form-control input-sm" type="text" id="pres_pre03" name="pres_pre03" placeholder="0.00" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-3">mayo</label>
						<div class="col-md-9">
							<input class="form-control input-sm" type="text" id="pres_pre05" name="pres_pre05" placeholder="0.00" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-3">julio</label>
						<div class="col-md-9">
							<input class="form-control input-sm" type="text" id="pres_pre07" name="pres_pre07" placeholder="0.00" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-3">septiembre</label>
						<div class="col-md-9">
							<input class="form-control input-sm" type="text" id="pres_pre09" name="pres_pre09" placeholder="0.00" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-3">noviembre</label>
						<div class="col-md-9">
							<input class="form-control input-sm" type="text" id="pres_pre11" name="pres_pre11" placeholder="0.00" />
						</div>
					</div>

				</div>

				<div class="col-md-6">

					<div class="form-group">
						<label class="control-label col-md-3">febrero</label>
						<div class="col-md-9">
							<input class="form-control input-sm" type="text" id="pres_pre02" name="pres_pre02" placeholder="0.00" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-3">abril</label>
						<div class="col-md-9">
							<input class="form-control input-sm" type="text" id="pres_pre04" name="pres_pre04" placeholder="0.00" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-3">junio</label>
						<div class="col-md-9">
							<input class="form-control input-sm" type="text" id="pres_pre06" name="pres_pre06" placeholder="0.00" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-3">agosto</label>
						<div class="col-md-9">
							<input class="form-control input-sm" type="text" id="pres_pre08" name="pres_pre08" placeholder="0.00" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-3">octubre</label>
						<div class="col-md-9">
							<input class="form-control input-sm" type="text" id="pres_pre10" name="pres_pre10" placeholder="0.00" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-3">diciembre</label>
						<div class="col-md-9">
							<input class="form-control input-sm" type="text" id="pres_pre12" name="pres_pre12" placeholder="0.00" />
						</div>
					</div>

					<input type="hidden" value="add" id="accion" name="accion">
					<input type="hidden" value="presupuesto" id="tabla" name="tabla">
					<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
					<input type="hidden" value="<?php echo $_SESSION['ie']; ?>" id="id_empresa" name="id_empresa">
					<input type="hidden" value="1" id="num_pagi" name="num_pagi">
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-offset-2 col-md-10">
					<button class="btn btn-sm btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-pencil"></span> Adicionar</button>
					<button type="reset" style="display: none;"></button>
				</div>
			</div>
		</form>
</div>
<div id="alertBoxes"></div>
<div id="dialog-confirm" title="Eliminar Registro?"></div>
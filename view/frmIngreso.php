<script>
	$(document).ready(function(){
		$("#frmOC").validate({
			rules:{
				empresa:{
					required: true
				},
				oc: {
					required: true,
					digits: true
				},
				bodega:{
					required: true
				},
				fecha_doc : {
					required: true
				},
				tipo_doc: {
					required: true
				},
				num_doc: {
					required: true
				},
				val_doc: {
					required: true,
					number: true
				}
			},
			submitHandler: function(form) {
				$('#waiting').show().dialog({
					modal: true,
					width: 'auto',
					height: 'auto',
					closeOnEscape: false
				});
				//$(":submit").attr("disabled","disabled");
				$(":submit").prop("disabled", true);
				var str = $('#empresa').val();
				//var strs = str.split("-");
				var oc = $('#oc').val();
				var valor = $('#val_doc').val();
				$('#fadeIn').hide('fast');
				myUrlOC = location.protocol + "//" + location.host + '/sics/json.php?c=inv&a=verificaPreOC';
				$.post(myUrlOC,
					{
						empresa: str,
						oc: oc,
						val: valor
					},
					function(json) {
						if(json == "OK"){
							$("#empresa option:selected").each(function () {
				               	strE = $(this).text() + " ";
							});
							nempresa = '<input type="hidden" id="nempresa" name="nempresa" value="'+strE+'" />';
							$('#frmOC').append(nempresa);
							$("#bodega option:selected").each(function () {
				               	strB = $(this).text() + " ";
							});
							nbodega = '<input type="hidden" id="nbodega" name="nbodega" value="'+strB+'" />';
							$('#frmOC').append(nbodega);
							f_doc = '<input type="hidden" id="fecha_doc" name="fecha_doc" value="'+$('#fecha_doc').val()+'" />';
							$('#frmOC').append(f_doc);
							t_doc = '<input type="hidden" id="tipo_doc" name="tipo_doc" value="'+$('#tipo_doc').val()+'" />';
							$('#frmOC').append(t_doc);
							v_doc = '<input type="hidden" id="val_doc" name="val_doc" value="'+$('#val_doc').val()+'" />';
							$('#frmOC').append(v_doc);
							n_doc = '<input type="hidden" id="num_doc" name="num_doc" value="'+$('#num_doc').val()+'" />';
							$('#frmOC').append(n_doc);
							form.submit();
						} else {
							//$('#fadein').html(json).fadeIn('slow');
							$('#fadein').html(json).removeClass("hide").hide().fadeIn("slow");
							$(":submit").prop("disabled", false);
							$('#waiting').dialog('close');
						}
				 	}
				);
			}
		});
		// Capturamos el evento onchange de la empresa para llenar
		// la lista de bodegas
		$("#empresa").change(function(){
			CheckAjaxCall();
			//$("#oc").keyup();
		});
		// Llenamos la lista de bodegas para la empresa seleccionada
		function CheckAjaxCall() {
			var str = $('#empresa').val();
			var strs = str.split("-");
			myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=inv&a=selectBodegas';
			$.ajax({
				type: 'POST',
				url: myUrl,
				dataType: 'json',
                data: {
                    idempresa: strs[1]
				},
				success: function(aData){
					$('#bodega').html('');
					$('#bodega').append('<option value="">--Seleccion bodega--</option>');
					$.each(aData, function(i, item){
						opcion = '<option value="'+item.bod_codigo+'">'+item.bod_descripcion+'</option>';
						$('#bodega').append(opcion);
					});
					//$('#bodegas').html(aData);
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					$.pnotify({
						title: 'Ha ocurrido un error.!',
    					text: 'Ha ocurrido un error durante la ejecucion.'+textStatus,
    					type: 'error',
    					hide: true,
						addclass: "stack-bar-top",
						stack: stack_bar_top,
						cornerclass: '',
						width: '100%'
    				});
				}
			});
			return false;
		}
		// Enviemos el formulario y verifiquemos los datos de la O.C.
		/*$('#oc').keypress(function(e) {
			$('#oc').keyup(function(){
				var str = $('#empresa').val();
				var strs = str.split("-");
				$('#hide').hide(0).hide('fast');
				$(":submit").attr("disabled","disabled");
				$('#fadein').html("<p>Verificando...</p>").fadeIn('slow');
				myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=inv&a=verificaOC';
				$.getJSON(myUrl,
						{
							empresa: strs[0],
							oc: $(this).val()
						},
						function(json) {
							if(json == "OK"){
								$('#hide').show('slow');
								$('#fadein').hide('fast');
								$(":submit").removeAttr("disabled");
							} else {
								$('#fadein').html(json).fadeIn('slow');
								$('#hide').hide(0).hide('fast');
								$(":submit").attr("disabled","disabled");
							}
				 		}
					 	);
				//$(this).focus();
			});
		});*/
	});
</script>
<?php
// Conexion a la BD
try {
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
} catch ( Exception $e ) {
	echo $e->getMessage ();
	die ();
}
?>
<h4 class="text-blue">Ingresar Orden de Compra</h4>
<form method="post" action="?c=inv&a=revisar" id="frmOC" name="frmOC" class="form-horizontal" role="form">
	<div class="form-group">
		<label for="empresa" class="control-label col-md-2">Empresa</label>
		<div class="col-md-10">
			<select class="form-control input-sm" id="empresa" name="empresa">
			<option value="">--Seleccione empresa--</option>
			<?php
			try {
				$sql = "Select * From " . $conf->getTbl_empresa ();
				$run = $db->ejecutar ( $sql );
			} catch ( Exception $e ) {
				echo $e->getMessage ();
				die ();
			}
			while ( $row = mysqli_fetch_array ( $run ) ) {
			?>
			<option value="<?php echo $row['id_empresa_oc'].'-'.$row['id_empresa']; ?>"><?php echo $row[1]; ?></option>
			<?php
			}
			?>
		</select>
		</div>
	</div>

	<div class="form-group">
		<label for="bodega" class="control-label col-md-2">Bodega</label>
		<div class="col-md-10">
			<select class="form-control input-sm" id="bodega" name="bodega">
			</select>
		</div>
	</div>

	<div class="form-group">
		<label for="oc" class="control-label col-md-2"># O.C.</label>
		<div class="col-md-10">
			<input class="form-control input-sm" id="oc" name="oc" type="text" placeholder="Ingreso O.C." />
		</div>
	</div>
	
	<div class="form-group">
		<label for="fecha_doc" class="control-label col-md-2">Fecha Documento</label>
		<div class="col-md-10">
			<div class="input-group input-group-sm date" id="dp3" data-date="" data-date-format="dd/mm/yyyy">
				<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
				<input name="fecha_doc" id="fecha_doc" class="form-control input-sm" size="16" type="text" value="" readonly>
			</div>
		</div>
	</div>
	
	<div class="form-group">
		<label for="tipo_doc" class="control-label col-md-2">Tipo Documento</label>
		<div class="col-md-10">
			<select class="form-control input-sm" id="tipo_doc" name="tipo_doc">
				<option value="F">F</option>
				<option value="R">CCF</option>
			</select>
		</div>
	</div>
	
	<div class="form-group">
		<label for="num_doc" class="control-label col-md-2">Numero Documento</label>
		<div class="col-md-10">
			<input class="form-control input-sm" id="num_doc" name="num_doc" type="text" placeholder="Numero Docuemnto" />
		</div>
	</div>
	
	<div class="form-group">
		<label for="val_doc" class="control-label col-md-2">Valor Documento</label>
		<div class="col-md-10">
			<input class="form-control input-sm" id="val_doc" name="val_doc" type="text" placeholder="Valor Docuemnto" />
		</div>
	</div>
	
	<!-- CARGANDO -->
	<div id="waiting" style="display: none;">
		<fieldset>
			<legend>procesando peticion, espere por favor...</legend>
			<img src="css/redmond/images/ajax-loader.gif" />
		</fieldset>
	</div>
	<div class="form-group">
		<div class="col-md-offset-2 col-md-10">
			<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Revisar O.C.</button>
		</div>
	</div>
	<div id="fadein" class="alert alert-warning hide"></div>
</form>
<div id="empresas"></div>
<div id="alertBoxes"></div>
<script>
$(function(){
	$('#dp3').datepicker();
});
</script>
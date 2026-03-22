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
				$('#fadeIn').hide('fast');
				myUrlOC = location.protocol + "//" + location.host + '/sics/json.php?c=inv&a=verificaOC';
				$.post(myUrlOC,
					{
						empresa: str,
						oc: oc
					},
					function(json) {
						if(json == "OK"){
							$("#empresa option:selected").each(function () {
				               	strE = $(this).text() + " ";
							});
							nempresa = '<input type="hidden" id="nempresa" name="nempresa" value="'+strE+'" />';
							$('#frmOC').append(nempresa);
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
		/*$("#empresa").change(function(){
			CheckAjaxCall();
			//$("#oc").keyup();
		});*/
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
<h4 class="text-blue">Desistir Orden de Compra</h4>
<form method="post" action="?c=inv&a=revisarD" id="frmOC" name="frmOC" class="form-horizontal" role="form">
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
		<label for="oc" class="control-label col-md-2"># O.C.</label>
		<div class="col-md-10">
			<input class="form-control input-sm" id="oc" name="oc" type="text" placeholder="Ingreso O.C." />
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
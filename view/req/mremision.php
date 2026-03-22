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
				bodega: {
					required: true
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
				var nbodega = $('#bodega').val();
				$('#fadeIn').hide('fast');
				myUrlOC = location.protocol + "//" + location.host + '/sics/json.php?c=req&a=verificaRemision';
				$.post(myUrlOC,
					{
						empresa: str,
						oc: oc,
						nbodega: nbodega
					},
					function(json) {
						if(json == "OK"){
							$("#empresa option:selected").each(function () {
				               	strE = $(this).text() + " ";
							});
							nempresa = '<input type="hidden" id="nempresa" name="nempresa" value="'+strE+'" />';
							$('#frmOC').append(nempresa);
							$("#bodega option:selected").each(function () {
				               	strB = $(this).val();
							});
							nbodega = '<input type="hidden" id="nbodega" name="nbodega" value="'+strB+'" />';
							$('#frmOC').append(nbodega);
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
			myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=req&a=listaCC';
			$.ajax({
				type: 'POST',
				url: myUrl,
				dataType: 'json',
                data: {
                    id_empresa: strs[1]
				},
				success: function(aData){
					$('#bodega').html('');
					$('#bodega').append('<option value="">--Seleccione centro de costo--</option>');
					$.each(aData, function(i, item){
						opcion = '<option value="'+item.id_cc+'">( '+item.cc_codigo+' ) '+item.cc_descripcion+'</option>';
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
<h4 class="text-blue">Marcar Nota de Remision Recibida</h4>
<form method="post" action="?c=req&a=m_rem&id=6" id="frmOC" name="frmOC" class="form-horizontal" role="form">
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
		<label for="bodega" class="control-label col-md-2">Centro de Costo</label>
		<div class="col-md-10">
			<select class="form-control input-sm" id="bodega" name="bodega">
			</select>
		</div>
	</div>

	<div class="form-group">
		<label for="oc" class="control-label col-md-2"># Nota de Remision</label>
		<div class="col-md-10">
			<input class="form-control input-sm" id="oc" name="oc" type="text" placeholder="# Remision" />
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
			<a class="btn btn-default" href="?c=menu&a=<?php echo (isset($_GET['return']) ? $_GET['return'] : 'index')?>">
				<i class="glyphicon glyphicon-arrow-left"></i>
				Regresar
			</a>
			<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Marcar de Recibido</button>
		</div>
	</div>
	<div id="fadein" class="alert alert-warning hide"></div>
</form>
<div id="empresas"></div>
<div id="alertBoxes"></div>
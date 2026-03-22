<script>
$(function(){
  	$("#frmVer").on("submit", function(event) {
      	event.preventDefault();
  	});
  	// Valida formulario
  	$("#frmVer").validate({
  	  	rules:{
      		empresa : {
        		required: true
      		},
      		o: {
          		required: true,
          		number: true
      		}
    	},
    	submitHandler: function(form) {
    		$('form').nextAll('label').remove();
        	$texto = $('#empresa').val();
    		var $textos = $texto.split("-");
    	    myUrl = location.protocol + "//" + location.host + '/sics/view/req/'+$('#origen').val()+'.php?o='+$('#o').val()+'&e='+$textos[0]+'&e_oc='+$textos[1];
    	    $('form').after('<label class="alert alert-success">ORDEN '+$('#o').val()+' IMPRESA CON EXITO.</label>');
    	    form.reset();
    	    window.open(myUrl);
    	}
  	});
  	/*$("#empresa").change(function(){
    	alert($(this).val());
  	});*/
});
</script>
<h4 class="text-blue">Imprimir Soporte de Orden de Compra</h4>
<form class="form-horizontal" role="form" id="frmVer"  method="post" action="?c=repo&a=solc&id=7&page=1">
  <div class="form-group">
    <label for="inputEmail1" class="col-md-2 control-label">Empresa</label>
    <div class="col-md-6">
      <?php if(count($emps) > 1) { ?>
			<select class="form-control input-sm" id="empresa" name="empresa">
				<option value="">-- Seleccione Empresa --</option>
				<?php foreach ($emps as $emp) { ?>
				<?php
			    if(isset($_POST['empresa']) && $_POST['empresa'] == $emp['id_empresa']) {
			    	$empresa = $emp['emp_nombre'];
			    }
			    ?>
				<option value="<?php echo $emp['id_empresa'].'-'.$emp['id_empresa_oc']; ?>"><?php echo $emp['emp_nombre']; ?></option>
				<?php } ?>
			</select>
		<?php } else { ?>
			<?php foreach ($emps as $emp) { ?>
				<span class="text-info"><?php echo $emp['emp_nombre']; ?></span>
				<input type="hidden" id="empresa" name="empresa" value="<?php echo $emp['id_empresa'].'-'.$emp['id_empresa_oc']; ?>" />
			<?php } ?>
				<script type="text/javascript">
					$(function(){
						CheckAjaxCall(<?php echo $emps[0]['id_empresa']; ?>);
					});
				</script>
		<?php } ?>
    </div>
  </div>
  <div class="form-group">
		<label class="control-label col-md-2">Imprimir para</label>
		<div class="col-md-6">
			<select id="origen" name="origen" class="form-control input-sm">
				<option value="OC">FIRMA AUTORIZAR</option>
				<option value="OC_CONTEO">CONTEO DE MERCADERIA</option>
			</select>
		</div>
  </div>
  <div class="form-group">
		<label class="control-label col-md-2">Numero de O.C.</label>
		<div class="col-md-6">
			<input class="form-control input-sm" type="text" id="o" name="o" />
		</div>
  </div>
  <div>
  	<div class="form-group">
  		<div class="col-md-6 col-md-offset-2">
			<button type="submit" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-print"></span> Imprimir</button>
		</div>
	</div>
	</div>
</form>
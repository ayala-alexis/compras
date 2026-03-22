<?php
if ($_SESSION['req'] == '0') {
	echo '<label class="alert alert-error">lo sentimos se ha revocado el acceso a esta opcion, consulte al administrador.</label>';
} else {
?>
	<script>
	$(function(){
		$('#colectar').live('click', function(){
			// Verificamos is ha seleccionado alguna para colectar
			var stack_bar_bottom = {"dir1": "up", "dir2": "right", "spacing1": 0, "spacing2": 0};
			var stack_bottomright = {"dir1": "up", "dir2": "left", "firstpos1": 25, "firstpos2": 25};
			if($('input:checkbox:checked').size() <= 0) {
				$.pnotify({
					title: 'ha ocurrido un error..',
					text: 'no ha seleccionado ninguna requisicion para colectar.',
					type: 'error',
					icon: 'icon-exclamation-sign',
					hide: true,
					addclass: "stack-bar-bottom",
					cornerclass: "",
			        width: "100%",
			        stack: stack_bar_bottom
				});
			} else {
				// Hacemos la colecta
				var datos = new Object();
				var empresa = $('span#empresa').text();
				datos['tabla'] = 'prehreq';
				datos['accion'] = 'colecta';
				datos['empresa'] = empresa;
				/* sacamos todos los datos */
				var numeros = [];
				$('tbody tr').each(function(){
					$tr = $(this);
					if($(this).find('input:checkbox').is(':checked')){
						// el numero llevara los datos : numero_req ~ id_cc ~ id_prehreq
						var numero = $(this).find('input:checkbox').val();
						numeros.push(numero);
						$tr.hide();
						$(this).find('input:checkbox:checked').removeAttr("checked");
					}
				});
				// mandamos el arrglo de datos
				datos['numero'] = numeros;
				myUrl = location.protocol + "//" + location.host + '/sics/class/Formulario.php';
				$.ajax({
					type: 'POST',
					url: myUrl,
		            data: {
		                form: datos
					},
					beforeSend: function(){
						$.pnotify({
							title: 'colectando..',
							text: 'realizando coleccion de datos, por favor espere...',
							type: 'info',
							icon: 'icon-shopping-cart',
							hide: true,
							addclass: "stack-bottomright",
							stack: stack_bottomright
						});
					},
					success: function(data){
						if(!$.isNumeric(data)){
	      					jAlert(data,'la requisicion ha sufrido cambios');
	      				} else {
	      					if(data == 1){
	      						jAlert(data,'error');
	      					} else {
								$tr.closest('tr').fadeOut(400, function(){
									$(this).next().remove();
									$(this).remove();
								});
	      					}
	      				}
					},
					error: function(XMLHttpRequest, textStatus, errorThrown){
						jAlert('ocurrio un error :'+textStatus, 'ha ocurrido un error');
					}
				}).done(function(response, textStatus, jqXHR){
					//$('#colectar').nextAll('img').remove();
				});
			};
		});

		$('#checkAll').change(function(){
			var status = $(this).attr('checked') ? 'checked' : false;
			$('input:checkbox').attr('checked',status);
		});
	});
	</script>
	<?php $empresa = ''; ?>
	<form role="form" class="form-inline" action="?c=req&a=colectar&id=6" method="post">
		<div class="form-group">
			<label for="empresa" class="sr-only">Empresa</label>
			<select class="form-control input-sm" id="empresa" name="empresa">
			  <?php foreach ($emps as $emp) { ?>
			  <?php
			  if(isset($_POST['empresa']) && $_POST['empresa'] == $emp['id_empresa']) {
				$empresa = $emp['emp_nombre'];
			  }
			  ?>
			  <option value="<?php echo $emp['id_empresa']; ?>"><?php echo $emp['emp_nombre']; ?></option>
			  <?php } ?>
			</select>
		</div>
		<button type="submit" class="btn btn-sm btn-primary"><i class="glyphicon glyphicon-search"></i> Colectar</button>
	</form>
	<br>
	<?php if(count($colectas) == 0 && isset($colectas)) { ?>
		<span class="alert alert-danger">
			No se encontraron registros para <?php echo '(<span id="empresa">'.$_POST['empresa'].'</span>)'.$empresa; ?>
		</span>
	<?php } elseif(!isset($colectas)) { ?>
		<span class="alert alert-info">
			Seleccione empresa para buscar
		</span>
	<?php } else {?>
	<span class="alert alert-primary">
	Mostrando autorizadas para <?php echo '(<span id="empresa">'.$_POST['empresa'].'</span>)'.$empresa; ?>
	</span>
	<button type="button" class="btn btn-sm btn-success" id="colectar"> Colectar Marcadas</button>
	<br><br>
	<table class="table table-condensed" id="tblColectar">
		<thead>
	  		<tr>
	    		<th>CENTRO DE COSTO</th>
	    		<th>FECHA</th>
	    		<th>NUMERO</th>
	    		<th>
	    			<ul class="list-group">
			    		<li class="list-group-item text-warning text-uppercase">
			    		SELECCIONAR TODAS
			    		<div class="material-switch pull-right">
							<input id="checkAll" type="checkbox"/>		
							<label for="checkAll" class="label-success"></label>
						</div>
						</li>
					</ul>
	    		</th>
	  		</tr>
	  	</thead>
	  	<tbody>
	  <?php foreach ($colectas as $colecta) { ?>
	  	<tr>
	  		<td><label class="text-info"><?php echo '(<span id="cecosto">'.$colecta['cc_codigo'].'</span>)'.$colecta['cc_descripcion']; ?></label></td>
	  		<td><label class="text-info"><?php echo $colecta['prehreq_fecha']; ?></label></td>
	    	<td><label class="text-info"><?php echo $colecta['prehreq_numero_req']; ?></label></td>
	    	<td>
	    	<ul class="list-group">
	    		<li class="list-group-item text-success">
	    		Colectar
	    		<div class="material-switch pull-right">
					<input value="<?php echo $colecta['prehreq_numero_req'].'~'.$colecta['id_cc'].'~'.$colecta['id_prehreq']; ?>" id="someSwitchOptionSuccess<?php echo $colecta['id_prehreq']; ?>" name="someSwitchOption<?php echo $colecta['id_prehreq']; ?>" type="checkbox"/>		
					<label for="someSwitchOptionSuccess<?php echo $colecta['id_prehreq']; ?>" class="label-success"></label>
				</div>
				</li>
			</ul>
            </td>
	    </tr>
	    <tr>
	    	<td colspan="4">
	    	<table class="table table-condensed">
	    	<tr>
	    		<th>codigo</th>
	    		<th>descripcion</th>
	    		<th>cantidad</th>
	    	</tr>
	    <?php foreach ($colecta['Det'] as $det) { ?>
	    	<tr>
	   			<td><?php echo $det['prod_codigo']; ?></td>
	   			<td><?php echo $det['predreq_descripcion']; ?></td>
	   			<td><?php echo $det['predreq_cantidad']; ?></td>
	  		</tr>
	  	<?php } ?>
	  		</table>
	  		</td>
	  	</tr>
	  <?php } ?>
	  	</tbody>
	</table>
	<script>
	$(function(){
		$('#dp4').datepicker().on('changeDate', function(ev){ $('#dp4').datepicker('hide'); });
		$('#dp5').datepicker().on('changeDate', function(ev){ $('#dp5').datepicker('hide'); });
	});
	</script>
	<?php } ?>
<?php } ?>
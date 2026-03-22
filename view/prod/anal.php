<?php session_start(); ?>
<?php
if(is_null($_REQUEST["p"]) or empty($_REQUEST["p"])){
	$_REQUEST["p"] = 0;
} 
?>
<script>
//Llenamos la lista de productos para la sublinea seleccionada
function CheckAjaxCall(str) {
	//var str = $('#empresa').val();
	myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=prov&a=listasVigentes';
	$.ajax({
		type: 'POST',
		url: myUrl,
		dataType: 'json',
           data: {
               sl: str
		},
		beforeSend: function(xhr) {
			$('#primera').html('');
			$('#primeta').after('<img src="images/FhHRx.gif"></img>');
			$('#primera').hide();
		},
		success: function(aData){
			$('#primera').append(opcion = '<option value="0,0">Ninguno</option>');
			if(aData !== null && aData !== undefined) {
				$.each(aData, function(i, item){
					opcion = '<option value="'+item.id_proveedor+','+parseFloat(item.lis_precio).toFixed(3)+'" '+item.vigente+'>'+item.mensaje+' '+parseFloat(item.lis_precio).toFixed(3)+' - '+item.prov_nombre+' - '+item.lis_fin_vigencia+'</option>';
					$('#primera').append(opcion);
				});
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(textStatus);
		}
	}).done(function(){
		$('#primera').show();
		$('#primera').nextAll('img').remove();
	});
	return false;
}

function CheckAjaxCallMay(str) {
	//var str = $('#empresa').val();
	myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=prov&a=listasVigentesMayoreo';
	$.ajax({
		type: 'POST',
		url: myUrl,
		dataType: 'json',
           data: {
               sl: str
		},
		beforeSend: function(xhr) {
			$('#segunda').html('');
			$('#segunda').after('<img src="images/FhHRx.gif"></img>');
			$('#segunda').hide();
		},
		success: function(aData){
			$('#segunda').append(opcion = '<option value="0,0">Ninguno</option>');
			if(aData !== null && aData !== undefined) {
				$.each(aData, function(i, item){
					opcion = '<option value="'+item.id_proveedor+','+parseFloat(item.lis_prec_may).toFixed(3)+'" '+item.vigente+'>'+item.mensaje+' '+parseFloat(item.lis_prec_may).toFixed(3)+' - '+item.lis_min_may+' - '+item.prov_nombre+' - '+item.lis_fin_vigencia+'</option>';
					$('#segunda').append(opcion);
				});
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(textStatus);
		}
	}).done(function(){
		$('#segunda').show();
		$('#segunda').nextAll('img').remove();
	});
	return false;
}

$(function(){

	var oTable = $('table').DataTable( {
        "language": {
            "lengthMenu": "Mostrando _MENU_ registros por pagina",
            "zeroRecords": "No se ha encontrado nada - intente mas tarde",
            "info": "Pagina _PAGE_ de _PAGES_",
            "infoEmpty": "No se encontraron registros",
            "infoFiltered": "(filtrando _MAX_ registros totales)"
        }
    } );

    oTable.page(<?php echo $_REQUEST["p"]; ?>).draw( false );
    
	$('a').tooltip();

	$('.two-digits').keyup(function(){
        if($(this).val().indexOf('.')!=-1){         
            if($(this).val().split(".")[1].length > 3){                
                if( isNaN( parseFloat( this.value ) ) ) return;
                this.value = parseFloat(this.value).toFixed(3);
            }  
         }            
         return this; //for chaining
    });
	/*
	 * Procesamos la tabla
	 */
	$('table').delegate('[rel="tooltip"]', 'click', function(event) {
		$row = $(this).closest('tr').find('td');
		$id1 = $(this).closest('tr').find('td').eq(2).attr('id');
		$id2 = $(this).closest('tr').find('td').eq(3).attr('id');
		$lbl1 = $row[2].innerHTML;
		$lbl2 = $row[3].innerHTML;
		//$id3 = $(this).closest('tr').find('td').eq(4).attr('id');
		var $tr = $(this).closest('tr')[0];
		if($row.length) {
			var id = $(this).attr('id');
			var accion = $(this).attr('data-original-title');
			if(typeof(accion) === 'undefined'){
				accion = $(this).attr('title');
			}
			if(accion == 'modificar') {
				// Lista Detalle
				CheckAjaxCall($row[0].innerHTML);
				// lista Mayoreo
				CheckAjaxCallMay($row[0].innerHTML);
				$('#myModalLabel').text($row[0].innerHTML+' - '+$row[1].innerHTML);
				$('#prod_codigo').val($row[0].innerHTML);
				//$('#primera').find('option[value="'+$id1+'"]').prop("selected", true);
				//$('select#primera option[value="' + $id1 + '"]').prop('selected',true);
				//var oTable =  $('table').DataTable();
			    //alert(oTable.page());
			    
				$("#divEdit").modal({
					backdrop: 'static',
					keyboard: false
				});
				
				$('#divEdit').on('shown.bs.modal', function () {
					/*$('#primera option').each(function() {
				        alert($(this).val());
				    });*/
					$('#primera option').prop('selected', false).filter('[value="'+$id1+'"]').prop('selected', true);
					$('#primera option').removeAttr('selected').filter('[value="'+$id1+'"]').attr('selected', true);

					$('#segunda option').prop('selected', false).filter('[value="'+$id2+'"]').prop('selected', true);
					$('#segunda option').removeAttr('selected').filter('[value="'+$id2+'"]').attr('selected', true);
				    
					/*$('#tercera option').prop('selected', false).filter('[value="'+$id3+'"]').prop('selected', true);
					$('#tercera option').removeAttr('selected').filter('[value="'+$id3+'"]').attr('selected', true);*/
			    });

				$('#divEdit').on('hide.bs.modal', function (e) {
					  // do something...
					
					/*alert($("#myModalLabel").text());
					alert('Segunda: '+$("#segunda option:selected").val());*/
				});
			}
		} else {
			jAlert('error en seleccion');
		}
	});
	

	/*$("#primera").change(function () {
        var $this = $(this);
        var prevVal = $this.data("prev");
        var otherSelects = $("#segunda").not(this);
        otherSelects.find("option[value=" + $(this).val() + "]").attr('disabled', true);
        if (prevVal) {
            otherSelects.find("option[value=" + prevVal + "]").attr('disabled', false);
        }

        $this.data("prev", $this.val());
    });*/
	/*
	 * Modificar Item
	 */
	$("#frmEdit").validate({
		rules:{
			precio2 : {
				required: true,
				number: true
			},
			precio3 : {
				number: true
			},
			min_precio3 : {
				number: true
			},
			vigencia2 : {
				required: true
			}
		},
		submitHandler: function(form) {
			var campos = xajax.getFormValues("frmEdit");
			Actualiza(campos);
			$('#frmEdit')[0].reset();
			$("#divEdit").modal('hide');
			return false;
		}
	});
	/*
	 * Manda el formulari a guardar
	 */
	function Actualiza(campos){
		// Enviamos a guardar
		myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
		$.ajax({
			type: 'POST',
			url: myUrl,
			data: {
				form: campos
			},
			beforeSend: function(){
				$('#waiting').show().dialog({
					modal: true,
					width: 'auto',
					height: 'auto',
					closeOnEscape: false
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
					if(data == 1){
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
							text: "registro actualizado",
							icon: "glyphicon glyphicon-ban-circle",
							hide: true,
							type: "success"
						});
						var locaUrl = "http://192.168.40.4/sics/?c=prod&a=anal&id=";
						locaUrl = locaUrl+"<?php echo trim($_REQUEST["id"])."&p=";?>";
						var oTable =  $('table').DataTable();
					    //alert(oTable.page());
					    location.href = locaUrl+oTable.page();
						//alert(x);
						//location.reload();
					}
				}
				$("#waiting").dialog("close");
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
			    $.pnotify({
					type : "error",
			        text: "Ha ocurrido un error durante la ejecucion.",
			        hide: true
				});
			}
		});
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
<?php
try {
	$sql = "Select sl_descripcion"
		." From " . $conf->getTbl_sublinea()
		." Where sl_linea = '".substr($_REQUEST['id'],0,2)."' And "
		."sl_sublinea = '".substr($_REQUEST['id'], 2,2)."'";
	$run = $db->ejecutar ( $sql );
} catch ( Exception $e ) {
	echo $e->getMessage ();
	die ();
}
$row = mysqli_fetch_array ( $run );
?>
<blockquote class="text-blue">
	<p><a href="./?c=prod&a=slprod&id=2"><img src="images/back-black.png"></img></a> <?php echo $row['sl_descripcion']; ?></p>
	<footer><?php echo $slinea; ?></footer>
</blockquote>
<table class="table table-condensed">
	<thead>
		<tr>
			<th>Codigo</th>
			<th>Descripcion</th>
			<th>Detalle</th>
			<th>Mayoreo</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($filas as $fila) { ?>
		<tr>
			<td><?php echo $fila['prod_codigo']; ?></td>
			<td><?php echo $fila['prod_descripcion']; ?></td>
			<td id="<?php echo $fila['prod_prov01'].",".number_format($fila['prod_prov_pre01'], 2, '.', ''); ?>">
				<?php echo nombreProveedor($fila['prod_prov01']); ?><br />
				<?php echo $fila['prod_prov_pre01']; ?>
			</td>
			<td id="<?php echo $fila['prod_prov02'].",".number_format($fila['prod_prov_pre02'], 2, '.', ''); ?>">
				<?php echo nombreProveedor($fila['prod_prov02']); ?><br />
				<?php echo $fila['prod_prov_pre02']; ?>
			</td>
			<td>
				<a id="<?php echo $fila['id_producto']; ?>" href="#" rel="tooltip" title="modificar">
					<i class="glyphicon glyphicon-pencil"></i>
				</a>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<div class="modal fade bs-example-modal-lg" aria-labelledby="myModalLabel" aria-hidden="true" tabindex="-1" data-backdrop="static" role="dialog"  id="divEdit" name="divEdit">
	
		<div class="modal-dialog">
			<div class="modal-content">
			<form id="frmEdit" name="frmEdit" role="form">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 id="myModalLabel"></h3>
				</div>
				<div class="modal-body">
				<div class="row">
                  <div class="col-xs-12">
				
					<div class="form-group">
						<label class="control-label col-md-6">Detalle</label>
						<label class="control-label col-md-6">Mayoreo</label>
					</div>
					<div class="form-group">
						<div class="col-md-6">
							<select multiple class="form-control select optional" size="15" name="primera" id="primera">
							</select>
						</div>
						<div class="col-md-6">
							<select multiple class="form-control select optional" size="15" name="segunda" id="segunda">
							</select>
						</div>
					</div>
					
					</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="tabla" id="tabla" value="producto" />
					<input type="hidden" name="accion" id="accion" value="analisis" />
					<input type="hidden" name="lis_usuario" id="lis_usuario" value="<?php echo $_SESSION['u']; ?>" />
					<input type="hidden" name="prod_codigo" id="prod_codigo" />
					<button class="btn btn-sm btn-default" data-dismiss="modal" aria-hidden="true"><span class="glyphicon glyphicon-ban-circle"></span> Cancelar</button>
					<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span> Asignar Precios</button>
					<input type="reset" style="display: none;">
				</div>
									</form>
			</div>
		</div>
</div>
<script>
$(function(){
	// Tabla
	var oTable = $('table').DataTable();
	oTable.page(<?php echo $_REQUEST["p"]; ?>).draw( false );	
});
</script>
<!-- CARGANDO -->
<div id="waiting" style="display: none;">
	<fieldset>
		<legend>procesando peticion, espere por favor...</legend>
		<img src="css/redmond/images/ajax-loader.gif" />
	</fieldset>
</div>
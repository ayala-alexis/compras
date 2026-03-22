<?php session_start (); ?>
<script>
$(document).ready(function(){
    $("#frmAdd").validate({
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
						title: 'adicionando..',
						text: 'intentando adicionar registro a tabla, por favor espere...',
						icon: 'glyphicon glyphicon-plus',
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
								title: 'adicinado',
								text: 'Registro adicionado con exito!.',
								icon: 'glyphicon glyphicon-ok',
								hide: true,
								type: "success"
							});
						    $("#formDiv input[type=reset]").click();
							//$('#myTab li:eq(1) a').tab('show');
							//$("p.form-control-static").text(campos["prod_codigo"].toUpperCase());
							$("#formDiv").dialog("close");
							pagina(campos["num_pagi"],"producto");
						}
					}
					$(":submit").removeClass("disabled");
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$.pnotify({
						title: 'error',
						text: 'Ocurrio un error durante la ejecucion',
						icon: 'glyphicon glyphicon-ban-circle',
						hide: true,
						type: "error"
					});
				    $(":submit").removeClass("disabled");
				}
			});
	   }
	});
	//
	$('#categorias').change(function(){
		myUrl = location.protocol + "//" + location.host + "/sics/json.php?c=prod&a=loadProvs";
		var catid = $.trim($(this).val());
		$.ajax({
            type: 'POST',
            url: myUrl,
            dataType: 'json',
            data: {
                idcat: catid
			},
			beforeSend: function(){
				var $this = $('#proveedores');
			    var left = $this.offset().left;
			    var top = $this.offset().top;
			    var width = $this.outerWidth();
			    var height = $this.outerHeight();

			    $this.wrap("<div id='overlay'> </div>")
			       .css('opacity', '0.2')
			       .css('z-index', '2')
			       .css('background','gray');

			},
            success: function(aData){
            	var $this = $('#proveedores');
            	$this.html('');
                $.each(aData, function(i,item) {
                	opcion = '<option value="'+item.id_proveedor+'">('+item.id_proveedor+') '+item.prov_nombre+'</option>';
					$this.append(opcion);
            	});
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
		//$('#proveedores').load(myUrl, { "idcat": $(this).val() });
	});
	$('#pasar').click(function(){
		if($('#proveedores option:selected').length > 0) {
			alert($('#proveedores option:selected').val());
		} else {
			alert('no ha seleccionado nada!');
		}
	});
	// Filtramos
	$('#btnBuscar').live('click', function(){
		pagina(0, 'producto');
	});
});
</script>
<script>
function pagina(pagi, tabl){
    myUrl = location.protocol + "//" + location.host + '/sics/class/PaginacionProducto.php';
    var $filnom = $.trim($('#filtronombre').val());
	var $filcat = $.trim($('#filtrocategoria').val());
	if($('#filtrocategoria').length <= 0) {
		$filcat = 'todos';
	}
	$.ajax({
		type : 'POST',
		url : myUrl,
		data: {
			tab: tabl,
			pag: pagi,
			filtronombre: $filnom,
			filtrocategoria: $filcat
		},
		success : function(data){
			$('#contenido').html(data);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			$.pnotify({
				title: "error",
				text: "Ocurrio un error durante la ejecucion",
				icon: "glyphicon glyphicon-ban-circle",
				hide: true,
				type: "error"
			});
		}
	});
};
pagina(0,'producto');
</script>
<div id="contenido" class="row"></div>
<div id="alertBoxes"></div>
<div id="dialog-confirm" title="Eliminar Registro?"></div>
<div id="formDiv" style="display: none; overflow: hidden;">
<ul class="nav nav-tabs" id="myTab">
	<li class="active" id="creaproducto"><a href="#login" data-toggle="tab">Crear</a></li>
	<li class="hide" id="asignaproveedor"><a href="#create" data-toggle="tab">Asignar Proveedores</a></li>
</ul>
<div id="myTabContent" class="tab-content">
	<div class="tab-pane fade active in" id="login">
		<br><br>
		<form role="form" class="form-horizontal" id="frmAdd" name="frmAdd" method="POST">
		<div class="form-group">
			<label class="control-label col-md-2" for="prod_slinea">Sublinea</label>
			<div class="col-md-10">
				<?php
				try {
					$db = DB::getInstance ();
					$conf = Configuracion::getInstance ();
				} catch ( Exception $e ) {
					echo $e->getMessage ();
					die ();
				}
				try {
					$sql = "Select * From " . $conf->getTbl_sublinea () . " Order By sl_linea, sl_sublinea";
					$lista = $db->ejecutar ( $sql );
				} catch ( Exception $e ) {
					echo $e->getMessage ();
					die ();
				}
				?>
				<select class="form-control input-sm" name="prod_slinea" id="prod_slinea">
				<?php
				$in20 = 0;
				while ( $fila = mysqli_fetch_array ( $lista ) ) {
					if ($fila [2] == '00') {
						if ($in20 == 1) {
							?>
							</optgroup>
							<?php
						}
						?>
						<optgroup label="<?php echo $fila[3]; ?>">
						<?php
					} else {
						?>
					<option value="<?php echo $fila[1].",".$fila[2]; ?>">[<?php echo $fila[1].$fila[2]; ?>]<?php echo $fila[3]; ?></option>
				<?php
					}
					$in20 = 1;
				}
				?>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-2" for="prod_codigo">Codigo</label>
			<div class="col-md-10">
				<input class="form-control input-sm" type="text" name="prod_codigo" id="prod_codigo" placeholder="digite codigo" />
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-2" for="prod_descripcion">Descripcion</label>
			<div class="col-md-10">
				<input class="form-control input-sm" type="text" name="prod_descripcion" id="prod_descripcion" placeholder="digite descripcion" />
			</div>
		</div>
		
		<div class="form-group">
		    <div class="col-sm-offset-2 col-sm-10">
		      <div class="checkbox">
		        <label>
		          <input type="checkbox" name="prod_solc" id="prod_solc"> Solicitud de Compra
		        </label>
		      </div>
		    </div>
		</div>
		  
		<div class="form-group">
		    <div class="col-sm-offset-2 col-sm-10">
		      <div class="checkbox">
		        <label>
		          <input type="checkbox" name="prod_req" id="prod_req"> Requisicion de Suministro
		        </label>
		      </div>
		    </div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-2" for="prod_observacion">Observaciones</label>
			<div class="col-md-10">
				<textarea class="form-control input-sm" rows="5" name="prod_observacion" id="prod_observacion" placeholder="digite observaciones">
				</textarea>
			</div>
		</div>

		<input type="hidden" value="add" id="accion" name="accion">
		<input type="hidden" value="producto" id="tabla" name="tabla">
		<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
		<input type="hidden" value="1" id="num_pagi" name="num_pagi">

		<div class="form-group">
			<div class="col-md-offset-2 col-md-10">
				<button class="btn btn-sm btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-plus"></span> Adicionar</button>
				<button class="btn btn-sm btn-default" type="reset"><span class="glyphicon glyphicon-ban-circle"></span> Limpiar</button>
			</div>
		</div>
		</form>
	</div>
	<div class="tab-pane fade" id="create">
		<br><br>
		<div class="col-md-6">
		<form class="form-horizontal" id="frmAddPrec" name="frmAddPrec" method="POST" role="form">
			<div class="form-group">
			<label class="col-md-2 control-label">Producto</label>
		    	<div class="col-md-10 text-info">
		      		<p class="form-control-static"></p>
		    	</div>
			</div>
			<div class="form-group">
				<div class="col-md-6">
				    <label for="prov01">Proveedor 1</label>
				    <input type="text" class="form-control" id="prov01" placeholder="Proveedor 1">
			    </div>
			    <div class="col-md-6">
				    <label for="prov01prec01">Precio 1</label>
				    <input type="text" class="form-control" id="prov01prec01" placeholder="Precio 1">
			    </div>
			</div>
			<div class="form-group">
				<div class="col-md-6">
				    <label for="prov02">Proveedor 2</label>
				    <input type="text" class="form-control" id="prov02" placeholder="Proveedor 2">
			    </div>
			    <div class="col-md-6">
				    <label for="prov02prec02">Precio 2</label>
				    <input type="text" class="form-control" id="prov02prec02" placeholder="Precio 2">
			    </div>
			</div>
			<div class="form-group">
				<div class="col-md-6">
				    <label for="prov03">Proveedor 3</label>
				    <input type="text" class="form-control" id="prov03" placeholder="Proveedor 3">
			    </div>
			    <div class="col-md-6">
				    <label for="prov03prec03">Precio 3</label>
				    <input type="text" class="form-control" id="prov03prec03" placeholder="Precio 3">
			    </div>
			</div>
			<div class="form-group">
				<div class="col-md-6">
				    <label for="prov04">Proveedor 4</label>
				    <input type="text" class="form-control" id="prov04" placeholder="Proveedor 4">
			    </div>
			    <div class="col-md-6">
				    <label for="prov04prec04">Precio 4</label>
				    <input type="text" class="form-control" id="prov04prec04" placeholder="Precio 4">
			    </div>
			</div>
			<div class="form-group">
				<div class="col-md-6">
				    <label for="prov05">Proveedor 5</label>
				    <input type="text" class="form-control" id="prov01" placeholder="Proveedor 5">
			    </div>
			    <div class="col-md-6">
				    <label for="prov05prec05">Precio 5</label>
				    <input type="text" class="form-control" id="prov05prec05" placeholder="Precio 5">
			    </div>
			</div>
			<button type="submit" class="btn btn-default">Asignar Precios</button>
		</form>
		</div>
		<div class="col-md-6">
			<?php
			try {
				$db = DB::getInstance ();
				$conf = Configuracion::getInstance ();
			} catch ( Exception $e ) {
				echo $e->getMessage ();
				die ();
			}
			try {
				$sql = "Select * From " . $conf->getTbl_categoria () . " Order By cat_descripcon";
				$lista = $db->ejecutar ( $sql );
			} catch ( Exception $e ) {
				echo $e->getMessage ();
				die ();
			}
			?>
			<label class="control-label">Categoria</label>
			<select multiple class="form-control" id="categorias">
			<?php while ( $fila = mysqli_fetch_array ( $lista ) ) { ?>
				<option value="<?php echo $fila[0]; ?>"><?php echo $fila[1]; ?></option>
			<?php } ?>
			</select>
			<label class="control-label">Proveedor</label>
			<select multiple class="form-control" id="proveedores">
			</select>
			<button type="button" class="btn btn-sm btn-default" id="pasar"><< Asignar</button>
		</div>
	</div>
</div>
</div>
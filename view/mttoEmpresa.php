<?php
session_start ();
?>
<style>
.checkbox label:after, 
.radio label:after {
    content: '';
    display: table;
    clear: both;
}

.checkbox .cr,
.radio .cr {
    position: relative;
    display: inline-block;
    border: 1px solid #a9a9a9;
    border-radius: .25em;
    width: 1.3em;
    height: 1.3em;
    float: left;
    margin-right: .5em;
}

.radio .cr {
    border-radius: 50%;
}

.checkbox .cr .cr-icon,
.radio .cr .cr-icon {
    position: absolute;
    font-size: .8em;
    line-height: 0;
    top: 50%;
    left: 20%;
}

.radio .cr .cr-icon {
    margin-left: 0.04em;
}

.checkbox label input[type="checkbox"],
.radio label input[type="radio"] {
    display: none;
}

.checkbox label input[type="checkbox"] + .cr > .cr-icon,
.radio label input[type="radio"] + .cr > .cr-icon {
    transform: scale(3) rotateZ(-20deg);
    opacity: 0;
    transition: all .3s ease-in;
}

.checkbox label input[type="checkbox"]:checked + .cr > .cr-icon,
.radio label input[type="radio"]:checked + .cr > .cr-icon {
    transform: scale(1) rotateZ(0deg);
    opacity: 1;
}
</style>

<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
<script>
$(document).ready(function(){

	$('input[name=grande]').change(function(){
		if($('input[name=grande]').is(':checked')){
		        console.log("Checked : " + $(this).val());
		    } else {
		    	console.log("Not checked : " + $(this).val());
		    }
		});

	
    $("#frmAdd").validate({
	    rules: {
			emp_nombre: {
				required: true,
				maxlength: 75
			},
			emp_razon:{
				required: true,
				maxlength: 75
			},
	     	emp_direccion: {
	       		required: true,
	       		maxlength: 250
	     	},
		    emp_nit: {
			    required: true,
			    maxlength: 20
		    },
		    emp_registro: {
			    required: true,
			    maxlength: 20
		    },
			emp_usa_presupuesto: {
				required: true
			},
			emp_observaciones: {
				required: true
			},
			emp_telefono: {
				required: true
			}
	   },
	   messages: {
	     	emp_nombre: {
		     	required : "Digite nombre"
	     	},
			emp_razon: {
				required: "Digite razon"
			},
	     	emp_direccion: {
	       		required: "Digite direccion"
	     	},
	     	emp_nit: {
		     	required: "Digite NIT"
	     	},
	     	emp_registro: {
		     	required: "Digite Registro"
	     	},
			emp_usa_presupuesto: {
				required: "Presupuesto ?"
			},
			emp_observaciones: {
				required: "Digite Observaciones"
			},
			emp_telefono: {
				required: "Digite Telefono"
			}
	   },
		submitHandler: function(form) {
		    var campos=xajax.getFormValues("frmAdd");
			campos["num_pagi"] = $("#num_pag").val();
			if($('input[name=grande]').is(':checked')){
				campos["emp_grande"] = "1";
		    } else {
		    	campos["emp_grande"] = "0";
		    }
			if(campos["emp_usa_presupuesto"] == '1'){
				campos["emp_origen_cc"] = campos["emp_origen_presupuesto"];
			}
			myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
			$.ajax({
				type : "POST",
				url : myUrl,
				data: {
					form: campos
				},
				beforeSend: function(){
					$(":submit").addClass("disabled");
					$.pnotify({
						title: 'adicionando..',
						text: 'intentando adicionar registro a tabla, por favor espere...',
						icon: 'glyphicon glyphicon-plus',
						hide: true
					});
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
								icon: 'glyphicon glyphicon-plus',
								hide: true,
								type: 'success'
							});
						    $("#formDiv input[type=reset]").click();
							$("#formDiv").dialog("close");
							$(":submit").removeClass("disabled");
							pagina(campos["num_pagi"],"empresa");
						}
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
				    showNotification({
						type : "error",
				        message: "Ha ocurrido un error durante la ejecucion.",
				        autoClose: true,
				        duration: 2
					});
				    $(":submit").removeClass("disabled");
				}
			});
	   }
	});
});
</script>
<script>
function pagina(pagi, tabl){
    myUrl = location.protocol + "//" + location.host + '/sics/class/PaginacionEmpresa.php';
	$.ajax({
		type : 'POST',
		url : myUrl,
		data: {
			tab: tabl,
			pag: pagi
		},
		success : function(data){
			$('#contenido').html(data);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			$('#alertBoxes').html('<div id="boxError" class="alert alert-danger"></div>');
			$('#boxError').html('Ha ocurrido un error durante la ejecucion.');
		}
	});
};
pagina(0,'empresa');
</script>
<div id="formDiv" style="display: none; overflow: hidden;">
	<div class="row">
	<fieldset>
		<legend>Adicionar Empresa</legend>
		<form class="form-horizontal" role="form" id="frmAdd" name="frmAdd" method="POST">
				
				<div class="form-group">
					<label class="control-label col-md-2" for="inputEmail">Email</label>
					<div class="col-md-10">
					<input class="form-control input-sm" type="text" id="inputEmail" placeholder="Email">
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2" for="emp_nombre">Nombre</label>
					<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="emp_nombre" id="emp_nombre" placeholder="digite nombre" />
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2" for="emp_razon">Razon</label>
					<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="emp_razon" id="emp_razon" placeholder="digite razon" />
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2" for="emp_direccion">Direccion</label>
					<div class="col-md-10">
					<textarea class="form-control input-sm" rows="" cols="" id="emp_direccion" name="emp_direccion" placeholder="digite direccion"></textarea>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2" for="emp_nit">NIT</label>
					<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="emp_nit" id="emp_nit" placeholder="digite NIT" />
					</div>
				</div>

				<div class="form-group">
    				<div class="col-sm-offset-2 col-sm-10">
						<div class="checkbox">
				            <label>
				                <input name="grande" id="grande" type="checkbox" value="">
				                <span class="cr"><i class="cr-icon fa fa-check"></i></span>
				                Gran Contribuyente
				            </label>
				        </div>
				    </div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2" for="emp_registro">Registro</label>
					<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="emp_registro" id="emp_registro" placeholder="digite registro" />
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2" for="emp_telefono">Telefono</label>
					<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="emp_telefono" id="emp_telefono" placeholder="digite numero de telefono" />
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-2" for="emp_usa_presupuesto">Presupuesto</label>
					<div class="col-md-10">
					<select class="form-control input-sm" id="emp_usa_presupuesto" name="emp_usa_presupuesto">
						<option value="" selected="selected">Seleccione una opcion</option>
						<option value="0">NO</option>
						<option value="1">SI</option>
					</select>
					</div>
				</div>

				<div id="origenPresupuesto"></div>
				<div id="origenCC"></div>

				<div class="form-group">
					<label class="control-label col-md-2" for="emp_observaciones">Observaciones</label>
					<div class="col-md-10">
					<textarea class="form-control input-sm" rows="" cols="" id="emp_observaciones" name="emp_observaciones" placeholder="digite observaciones"></textarea>
					</div>
				</div>

				<div class="form-group">
					<div class="col-md-offset-2 col-md-10">
					<button class="btn btn-sm btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-plus"></span> Adicionar</button>
					<button type="reset" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-ban-circle"></span> Limpiar</button>
					</div>
				</div>

				<input type="hidden" value="add" id="accion" name="accion">
				<input type="hidden" value="empresa" id="tabla" name="tabla">
				<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
				<input type="hidden" value="1" id="num_pagi" name="num_pagi">

		</form>
	</fieldset>
	</div>
	<script>
	$("#frmAdd #emp_usa_presupuesto").change(function(){
		var selectedVal = $(this).val();
		switch (selectedVal) {
		case '0':
			$("#frmAdd #origenPresupuesto").empty();
			$("#frmAdd #origenCC").empty();
			$('<div class="form-group"><label class="control-label col-md-2" for="emp_origen_cc">Origen C.C.</label><div class="col-md-10"><select class="form-control input-sm" id="emp_origen_cc" name="emp_origen_cc"><option value="LOCAL">Local</option><option value="AS400">AS/400</option></select></div></div>').hide().appendTo('#frmAdd #origenCC').fadeIn();
			$("#frmAdd #origenPresupuesto").append('<input type="hidden" value="" id="emp_origen_presupuesto" name="emp_origen_presupuesto"/>');
			break;
		case '1':
			$("#frmAdd #origenPresupuesto").empty();
		    $("#frmAdd #origenCC").empty();
			$('<div class="form-group"><label class="control-label col-md-2" for="emp_origen_presupuesto">Origen Presupuesto</label><div class="col-md-10"><select class="form-control input-sm" id="emp_origen_presupuesto" name="emp_origen_presupuesto"><option value="LOCAL">Local</option><option value="AS400">AS/400</option></select></div></div>').hide().appendTo('#frmAdd #origenPresupuesto').fadeIn();
		break;
		default:
			$("#frmAdd #origenPresupuesto").empty();
			$("#frmAdd #origenCC").empty();
			break;
		}
	});
	</script>
</div>
<div id="contenido" class="row"></div>
<div id="alertBoxes"></div>
<div id="dialog-confirm" title="Eliminar Registro?"></div>
<div id="DivBod" title="Bodegas de Empresa"></div>
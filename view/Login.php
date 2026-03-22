<script type="text/javascript">
$(document).ready(function(){
/*
	function successEvents(msg, datatable) {
		// JDR: fade in our return message block
		$(msg).fadeIn();
		// JDR: remove return message block
		$(msg).fadeOut();
	};
*/
	$.metadata.setType("attr", "validate");
	$("#loginform").validate({
		messages :{
			User: "Digite un usuario valido",
			Passwd: "Digite clave de acceso "
		}/*,
		errorContainer: "#errorblock-div1, #errorblock-div2",
		errorLabelContainer: "#errorblock-div2 ul",
		wrapper: "li"*/
	});

	$("#loginform").unbind('submit').bind('submit', function(){
		$('.alert, .alert-danger, .alert-block, .alert-success .alert-info').slideUp();
		if($("#User").val() != "" && $("#Passwd").val() != ""){
			$('#signIn').attr('disabled','disabled');
			$.ajax({
				type : 'POST',
				url : 'login.php',
				dataType : 'json',
				data: $("#loginform").serialize(),
				beforeSend: function(){
					$("#signIn").attr("disabled","disabled");
					$('#signIn').after('<img src="images/FhHRx.gif"></img>');
				},
				success : function(data){
					if(data == 'success'){
						$.pnotify({
							title: 'exito',
							text: 'bienvenido....',
							type: 'success',
							icon: 'ui-icon ui-icon-signal-diag'
						});
						document.location='?c=login&a=ingreso';
					} else {
						$("#User").focus();
						$.pnotify({
							title: 'datos incorrectos',
							text: data,
							type: 'error',
							icon: 'ui-icon ui-icon-signal-diag'
						});
						$("#signIn").removeAttr("disabled");
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$("#User").focus();
					$.pnotify({
						title: 'ha ocurrido un error en la ejecucion',
						text: data,
						type: 'error',
						icon: 'ui-icon ui-icon-signal-diag'
					});
					$("#signIn").removeAttr("disabled");
				}
			}).done(function(response, textStatus, jqXHR){
				$('#signIn').nextAll('img').remove();
			});
		} else {
			$("#User").focus();
			$.pnotify({
				title: 'falta informacion',
				text: 'digite informacion de inicio',
				type: 'error',
				icon: 'glyphicon glyphicon-pencil'
			});
			$("#signIn").removeAttr("disabled");
		}
		return false;
	});
});
</script>
<h3 class="text-center text-blue">Ingreso de Usuario</h3>
<br />
<p class="text-center">
Por favor ingrese su usuario y clave para ingresar al Sistema Integrado de Compras y Suministros&trade;.
</p>
<br />
<div class="col-md-4 col-md-offset-4">
<form role="form" id="loginform" action="?c=login&a=ingreso" method="post" class="form-horizontal" autocomplete="off">

	<div class="form-group">
		<label class="control-label col-md-3" for="User">Usuario</label>
		<div class="input-group">
			<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
			<input type="text" class="form-control" name="User" id="User" value="" validate="required:true" placeholder="Digite usuario" />
		</div>
	</div>



	<div class="form-group">
		<label class="control-label col-md-3" for="Passwd">Clave</label>
		<div class="input-group">
			<span class="input-group-addon"><i class="glyphicon glyphicon-barcode" aria-hidden="true"></i></span>
			<input type="password" class="form-control" name="Passwd" id="Passwd" validate="required:true" placeholder="Digite password" />
		</div>
	</div>


	<div class="form-group text-center">
		<button type="submit" class="btn btn-primary" name="signIn" id="signIn"><i class="glyphicon glyphicon-key"></i> Ingresar</button>
	</div>
	<div class="ui-widget ui-helper-hidden" id="errorblock-div1">
		<div class="ui-state-highlight ui-corner-all" style="padding: 0pt 0.7em;" id="errorblock-div2" style="display:none;">
			<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: 0.3em;"></span>
			<strong class="ui-state-error-text">Alerta:</strong> Errors detectados!</p>
			<ul class="ui-state-error-text"></ul>
		</div>
	</div>
	<div id="alertBoxes"></div>
</form>
</div>
<script>
$(function(){
	$('#User').focus();
});
</script>
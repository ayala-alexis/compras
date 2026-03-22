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
					$('#signIn').html('<img src="images/loading.gif"></img>');
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
						$("#signIn").removeAttr("disabled").html('Ingresar a SICS');
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
					$("#signIn").removeAttr("disabled").html('Ingresar a SICS');
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
			$("#signIn").removeAttr("disabled").html('Ingresar a SICS');
		}
		return false;
	});
});
</script>
<div class="container">
    <div class="row">
        <div class="col-sm-6 col-md-4 col-md-offset-4">
            <h1 class="text-center login-title text-blue">Ingreso de Usuario</h1>
            <div class="account-wall">
                <img class="profile-img" src="images/photo.png"
                    alt="">
                <form class="form-signin" role="form" id="loginform" action="?c=login&a=ingreso" method="post" autocomplete="off">
					<div style="display:none;">
						<input type="text" name="fake_user_name_to_remember" />
						<input type="password" name="fake_password_to_remember" />
					</div>

					<label class="control-label">
						Usuario
					</label>
					<input name="User" id="User" type="text" class="form-control" placeholder="Usuario" required autofocus autocomplete="off" />
					<br>
					<label class="control-label">
						Clave
					</label>
					<input name="Passwd" id="Passwd" type="password" class="form-control" placeholder="Clave" required autocomplete="off" />
					
					<button id="signIn" name="signIn" class="btn btn-lg btn-primary btn-block" type="submit">
						Ingresar a SICS
					</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$(function(){
	$('#User').focus();
});
</script>
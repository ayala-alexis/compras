<script type="text/javascript">
$(document).ready(function(){
	$("#loginform").validate({
		messages :{
			User: "Digite un usuario valido",
			Passwd: "Digite nueva clave de acceso "
		}
	});
	// Mandamos la actualizacion
	$("#loginform").unbind('submit').bind('submit', function(){
		if($("#User").val() != "" && $("#Passwd").val() != ""){
			$.ajax({
				type : 'POST',
				url : 'login_chg_pwd.php',
				dataType : 'json',
				data: $("#loginform").serialize(),
				beforeSend: function(){
					$("#signIn").attr("disabled","disabled");
					$('#signIn').html('<img src="images/loading.gif"></img>');
				},
				success : function(data){
					if(data == 'success'){
						var transferHelper = $('#loginform').clone();
						$('#logoneo').effect("transfer",{ clone: transferHelper, to: $("#dropdownMenu1") }, 500, function(){
							$.pnotify({
								title: 'exito',
								text: 'Clave actualizada con exito',
								type: 'success',
								hide: 'true'
							});
						});
						$('#logoneo').remove();
					} else {
						$("#Passwd").focus();
						$.pnotify({
							title: 'datos incorrectos',
							text: data,
							type: 'error',
							icon: 'ui-icon ui-icon-signal-diag'
						});
						$("#signIn").removeAttr("disabled").html('Actualizar Clave');
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$("#Passwd").focus();
					$.pnotify({
						title: 'ha ocurrido un error en la ejecucion',
						text: data,
						type: 'error',
						icon: 'ui-icon ui-icon-signal-diag'
					});
					$("#signIn").removeAttr("disabled").html('Actualizar Clave');
				}
			});
		} else {
			$("#Passwd").focus();
			$.pnotify({
				title: 'falta informacion',
				text: 'digite informacion de inicio',
				type: 'error',
				icon: 'glyphicon glyphicon-pencil'
			});
			$("#signIn").removeAttr("disabled").html('Actualizar Clave');
		}
		return false;
	});
});
</script>
<div class="container" id="logoneo">
    <div class="row">
        <div class="col-md-8">
            <h1 class="text-center login-title">Cambiar Clave de Acceso</h1>
            <div class="account-wall">
                <img class="profile-img" src="images/photo.png"  alt="">
                <p class="profile-name"><?php echo $_SESSION['u']; ?></p>
                <span class="profile-email"><?php echo $_SESSION['n']; ?></span>
                <form id="loginform" name="loginform" class="form-signin">
                <input name="Passwd" id="Passwd" type="password" class="form-control" placeholder="Password" required autofocus>
                <input type="hidden" name="User" id="User" value="<?php echo $_SESSION['u']; ?>">
                <button id="signIn" name="signIn" class="btn btn-lg btn-primary btn-block" type="submit">
                    Actualizar Clave</button>
                </form>
            </div>
        </div>
    </div>
</div>

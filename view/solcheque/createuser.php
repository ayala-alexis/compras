<link rel="stylesheet" type="text/css" href="css/style.css">
<style>
    .form-control{
        max-width: 350px !important;
        min-width: auto;
    }
</style>
<h4 class="text-blue">
    <i class="glyphicon glyphicon-user"></i>
    Crear nuevo usuario
</h4>
<div class="row">
    <div class="col-sm-5" style="background-color: #f4f4f4;">
        <h5 class="text-blue" style="font-weight: bold;font-size:14px;border-bottom:1px #999 solid;padding-bottom: 3px;">
            <i class="glyphicon glyphicon-pencil"></i>
            Información de usuario
        </h5>
        <table style="width:100%;font-size: 12px;">
            <tbody>
                <tr style="font-size: 11px;">
                    <th style="min-width: 40px;">
                        <label for="usuario">Usuario</label>
                    </th>
                    <td>
                        <input type="text" id="usuario" class="form-control" name="usuario" placeholder="Ingresar usuario"/>
                    </td>
                </tr>
                <tr style="font-size: 11px;">
                    <th>
                        <label for="rol">Rol</label>
                    </th>
                    <td>
                        <select class="form-control" name="rol" id="rol">
                            <option value="NN">Admin</option>
                            <option value="N3">Gcia. Financiera</option>
                            <option value="N5">Contabilidad</option>
                            <option value="N6">Dirección Ejecutiva</option>
                        </select>
                    </td>
                </tr>
                <tr style="font-size: 11px;">
                    <th>
                        <label for="nombre">Nombre</label>
                    </th>
                    <td>
                        <input id="nombre" class="form-control" type="text" name="nombre" placeholder="Ingresar nombre"/>
                    </td>
                </tr>
                <tr style="font-size: 11px;">
                    <th>
                        <label for="correo">Correo</label>
                    </th>
                    <td>
                        <input id="correo" class="form-control" style="text-transform: none;" type="text" name="correo" placeholder="Ingresar correo"/>
                    </td>
                </tr>
                <tr style="font-size: 11px;">
                    <th>
                        <label for="password">Contraseña</label>
                    </th>
                    <td>
                        <input id="password" class="form-control" style="text-transform: none;" type="password" name="password" placeholder="Ingresar contraseña"/>
                    </td>
                </tr>
                <tr style="font-size: 11px;">
                    <td colspan="2" style="text-align:right;">
                        <button class="btn btn-success btn-guardar">
                            Crear &nbsp; <i class="glyphicon glyphicon-pencil"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<br/>
<div class="row">
    <div class="col-sm-2">
        <a href="?c=solcheque&a=admin" class="btn btn-default">
            <i class="glyphicon glyphicon-arrow-left"></i> Regresar
        </a>
    </div>
</div>
<script type="text/javascript" src="js/js.js?v=<?php echo date('His')?>"></script>
<script>
    $(document).ready(function(){
        $('.btn-guardar').click(function(){
            var usuario = $('[name=usuario]').val().trim();
            var nombre = $('[name=nombre]').val().trim();
            var correo = $('[name=correo]').val().trim();
            var rol = $('[name=rol]').val();
            var password = $('[name=password]').val().trim();
            
            if(usuario=='' || nombre=='' || correo=='' || rol=='' || password==''){
                alert('Error: completar todos los campos.');
                return false;
            }
            
            var request=request_json_usuario_add({
                            usuario: usuario,
                            password: password,
                            nombre: nombre,
                            correo: correo,
                            rol: rol,
                            action: 'json.php?c=solcheque&a=agregar_usuario',
                            method: 'POST'
                        });
            if(request!=undefined){
                if(request.exito){
                    window.location='?c=solcheque&a=admindet&id=' + request.id;
                }else{
                    alert(request.msj);
                }
            }else{
                alert('Error: petición no se ha realizado.');
            }
            return false;
        });
    });
</script>
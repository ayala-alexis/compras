<link rel="stylesheet" type="text/css" href="css/style.css">
<h4 class="text-blue">
    <i class="glyphicon glyphicon-user"></i>
    <?php echo $data['usuario']->usuario?>
</h4>
<div class="row">
    <div class="col-sm-5" style="background-color: #f4f4f4;">
        <h5 class="text-blue" style="font-weight: bold;font-size:14px;border-bottom:1px #999 solid;padding-bottom: 3px;">
            <i class="glyphicon glyphicon-pencil"></i>
            Información de usuario
        </h5>
        <table style="width:100%;font-size: 12px;">
            <tbody>
                <?php $user = $data['usuario'];?>
                <tr style="font-size: 11px;">
                    <th for="id_usuario">ID</th>
                    <td>
                        <input id="id_usuario" type="text" readonly="readonly" class="form-control" name="id_usuario" value="<?php echo $user->id_usuario?>"/>
                    </td>
                </tr>
                <tr style="font-size: 11px;">
                    <th>
                        <label for="usuario">Usuario</label>
                    </th>
                    <td>
                        <input type="text" id="usuario" class="form-control" name="usuario" value="<?php echo $user->usuario?>"/>
                    </td>
                </tr>
                <tr style="font-size: 11px;">
                    <th>
                        <label for="rol">Rol</label>
                    </th>
                    <td>
                        <select class="form-control" name="rol" id="rol">
                            <option value="NN" <?php echo ($user->nivel=='NN' ? 'selected' : '')?>>Admin</option>
                            <option value="N3" <?php echo ($user->nivel=='N3' ? 'selected' : '')?>>Gcia. Financiera</option>
                            <option value="N5" <?php echo ($user->nivel=='N5' ? 'selected' : '')?>>Contabilidad</option>
                            <option value="N6" <?php echo ($user->nivel=='N6' ? 'selected' : '')?>>Dirección Ejecutiva</option>
                        </select>
                    </td>
                </tr>
                <tr style="font-size: 11px;">
                    <th>
                        <label for="nombre">Nombre</label>
                    </th>
                    <td>
                        <input id="nombre" class="form-control" type="text" name="nombre" value="<?php echo $user->nombre?>"/>
                    </td>
                </tr>
                <tr style="font-size: 11px;">
                    <th>
                        <label for="correo">Correo</label>
                    </th>
                    <td>
                        <input id="correo" class="form-control" style="text-transform: none;" type="text" name="correo" value="<?php echo $user->correo?>"/>
                    </td>
                </tr>
                <tr style="font-size: 11px;">
                    <td colspan="2">
                        <br/>
                        <a data-id="<?php echo $user->id_usuario?>" class="btn btn-danger btn-borrar-usuario" href="#">
                            Borrar <i class="glyphicon glyphicon-trash"></i>
                        </a>
                        &nbsp; &nbsp;
                        <a class="btn btn-primary btn-cambiar-password" href="?c=solcheque&a=cambiar_password&id=<?php echo $user->id_usuario?>">
                            Cambiar Contraseña <i class="glyphicon glyphicon-lock"></i>
                        </a>
                        &nbsp; &nbsp;
                        <button class="btn btn-success btn-guardar">
                            Guardar <i class="glyphicon glyphicon-pencil"></i>
                        </button>
                        <br/>
                        <br/>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="col-sm-offset-1 col-sm-5" style="background-color: #f4f4f4;">
        <h5 class="text-blue" style="font-weight: bold;font-size:14px;border-bottom:1px #999 solid;padding-bottom: 3px;">
            <i class="glyphicon glyphicon-home"></i>
            Empresas asignadas a usuario
        </h5>
        <?php $empresas=$data['empresa'];?>
        <?php $empresas_all=$data['empresa_all'];?>
        <table style="width:100%;">
            <tbody>
            <?php $arr_emp=array();?>
            <?php 
                $emp_user = 0;
                foreach ($empresas as $emp):
                    $arr_emp["$emp->id_empresa"] = (object)array(
                        'id_empresa' => $emp->id_empresa,
                        'id' => $emp->id,
                        'emp_nombre' => $emp->emp_nombre
                    );
                    $emp_user++;
                endforeach;?>
            <?php foreach ($empresas_all as $emp):?>
                <tr style="font-size: 12px;">
                    <?php if(!empty($arr_emp["$emp->id_empresa"])):?>
                    <td style="color: #222;"><?php echo $arr_emp["$emp->id_empresa"]->emp_nombre?></td>
                    <td>
                        <a data-id="<?php echo $arr_emp["$emp->id_empresa"]->id?>" style="min-width: 88px;" title="Quitar Empresa" href="#" class="btn btn-danger btn-borrar-empresa">
                            <i class="glyphicon glyphicon-trash"></i> Borrar
                        </a>
                    </td>
                    <?php else: ?>
                        <td style="color: #bbb;"><?php echo $emp->emp_nombre?></td>
                        <td>
                            <a style="min-width: 88px;" title="Agregar Empresa" data-id_empresa="<?php echo $emp->id_empresa?>" data-id_usuario="<?php echo $user->id_usuario?>" href="#" class="btn btn-primary btn-agregar-empresa">
                                <i class="glyphicon glyphicon-plus"></i> Agregar
                            </a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach;?>
                <tr>
                    <td colspan="2">
                        <span style="font-size:12px;color:#333;text-align:right;display: block;font-weight: bold;border-top:1px #aaa solid;margin-top:10px;padding-top: 5px;">
                            <?php if($emp_user!=1):?>
                                (<?php echo $emp_user?>) empresas asignadas.
                            <?php else: ?>
                                (1) empresa asignada.
                            <?php endif;?>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
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
            var ok = confirm('Guardar cambios realizados?');
            if(!ok) return false;
            var id_usuario = $('[name=id_usuario]').val();
            var usuario = $('[name=usuario]').val().trim();
            var nombre = $('[name=nombre]').val().trim();
            var correo = $('[name=correo]').val().trim();
            var rol = $('[name=rol]').val();
            var request=request_json_usuario({
                            id_usuario: parseInt(id_usuario),
                            usuario: usuario,
                            nombre: nombre,
                            correo: correo,
                            rol: rol,
                            action: 'json.php?c=solcheque&a=guardar_usuario',
                            method: 'POST'
                        });
            if(request!=undefined){
                if(request.exito){
                    location.reload();
                }else{
                    alert(request.msj);
                }
            }else{
                alert('Error: petición no se ha realizado.');
            }
            return false;
        });
        $('.btn-cambiar-password').click(function(){
            var contra1 = prompt('Ingresar nueva contraseña: ','');
            var contra2 = prompt('Confirmar nueva contraseña: ','');
            var ok = (contra1.toLocaleLowerCase().trim()!=contra1.toLocaleLowerCase().trim());
            if(ok){
                alert('Error: las dos contraseñas no son iguales'); 
            }
            if(ok) return false;
            var id_usuario = $('[name=id_usuario]').val();
            var request=request_json_usuario_pwd({
                            id_usuario: parseInt(id_usuario),
                            contra: contra1,
                            action: 'json.php?c=solcheque&a=cambiar_password',
                            method: 'POST'
                        });
            if(request!=undefined){
                if(request.exito){
                    location.reload();
                }else{
                    alert(request.msj);
                }
            }else{
                alert('Error: petición no se ha realizado.');
            }
            return false;
        });
        $('.btn-agregar-empresa').click(function(){
            var ok = confirm('Agregar empresa a <?php echo $user->nombre?>?');
            if(!ok) return false;
            var id_usuario = $(this).data('id_usuario');
            var id_empresa = $(this).data('id_empresa');
            var request=request_json_empresa({
                            id_usuario: parseInt(id_usuario),
                            id_empresa: parseInt(id_empresa),
                            action: 'json.php?c=solcheque&a=add_empresa_id',
                            method: 'POST'
                        });
            if(request!=undefined){
                if(request.exito){
                    location.reload();
                }else{
                    alert(request.msj);
                }
            }else{
                alert('Error: petición no se ha realizado.');
            }
            return false;
        });
        $('.btn-borrar-empresa').click(function(){
            var ok = confirm('Quitar empresa a <?php echo $user->nombre?>?');
            if(!ok) return false;
            var id_empresa = $(this).data('id');
            var request=request_json_id({
                            id: parseInt(id_empresa),
                            action: 'json.php?c=solcheque&a=quitar_empresa_id',
                            method: 'POST'
                        });
            if(request!=undefined){
                if(request.exito){
                    location.reload();
                }else{
                    alert(request.msj);
                }
            }else{
                alert('Error: petición no se ha realizado.');
            }
            return false;
        });
        $('.btn-borrar-usuario').click(function(){
            var ok = confirm('Eliminar usuario <?php echo $user->nombre?>?');
            if(!ok) return false;
            var id_usuario = $(this).data('id');
            var request=request_json_id({
                            id: parseInt(id_usuario),
                            action: 'json.php?c=solcheque&a=borrar_usuario',
                            method: 'POST'
                        });
            if(request!=undefined){
                if(request.exito){
                    window.location='?c=solcheque&a=admin';
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
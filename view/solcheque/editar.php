<link rel="stylesheet" type="text/css" href="css/style.css">
<h4 class="text-blue">Edici&oacute;n de Solicitud de Cheque No <?php echo str_pad($solc->id, 6,'0',STR_PAD_LEFT)?></h4>

<form role="form" id="frmCrearSolc" name="frmCrearSolc"  class="form-inline" method="post" action="json.php?c=solcheque&a=editar_cheque">
    <table class="table table-condensed">	
        <thead>
            <tr>
                <td bgcolor="#f5f5f5" colspan="3"><b>Creación de Solicitud</b></td>
            </tr>
        </thead>
        <tbody class="text-input">
            <tr>
                <td>Seleccione Empresa</td>
                <td colspan="2">
                    <input type="hidden" name="id" value="<?php echo $solc->id?>"/>
                    <input type="hidden" name="borrador" value=""/>
                    <select class="form-control input-sm" id="id_empresa" name="id_empresa">
                        <?php if(!empty($perfil)): ?>
                            <?php if(!empty($perfil->empresa)): ?>
                                <?php if(count($perfil->empresa) > 1): ?>
                                    <option value="">-- Seleccione Empresa --</option>
                                <?php endif; ?>
                                <?php foreach ($perfil->empresa as $emp): ?>
                                    <option value="<?php echo $emp->id; ?>" <?php echo ($solc->empresa->id==$emp->id ? 'selected' : '');?>>
                                        <?php echo $emp->nombre; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </select>
                </td>
                <td class="valid_empresa"></td>
            </tr>
        <tr>
            <td>Centro de Costo</td>
            <td>
                <div class="form-group">
                    <label id="centros" class="control-label">
                        <select class="form-control input-sm" id="id_cc" name="id_cc">
                            <option> -- Centros de Costo -- </option>
                        </select>
                    </label>
                </div>
            </td>
            <td class="valid_cc"></td>
        </tr>
        <tr>
            <td>Emitir cheque a nombre de</td>
            <td>
                <div class="form-group">
                    <input type="text" name="nombre_beneficiario" id="nombre_beneficiario" maxlength="67" placeholder="Nombre beneficiario" class="form-control input-sm" value="<?php echo $solc->nombre_beneficiario;?>"/>
                </div>
            </td>
            <td class="valid_nombre"></td>
        </tr>
        <tr>
            <td>Monto del cheque</td>
            <td>
                <div class="form-group">
                    <input type="text" name="valor_cheque" id="valor_cheque" placeholder="Monto del cheque" class="form-control input-sm" value="<?php echo $solc->valor_cheque;?>"/>
                </div>
            </td>
            <td class="valid_monto"></td>
        </tr>
        <tr>
            <td>Concepto del pago</td>
            <td>
                <div class="form-group">
                    <textarea name="concepto_pago" id="concepto_pago" maxlength="120" placeholder="Concepto del pago" class="form-control input-sm"><?php echo $solc->concepto_pago;?></textarea>
                </div>
            </td>
            <td class="valid_concepto"></td>
        </tr>
        <tr>
            <td>Fecha Máx. de pago</td>
            <td>
                <div class="form-group">
                    <input type="text" name="fecha_max_pago" id="fecha_max_pago" placeholder="Fecha máx. de pago" class="form-control input-sm" value="<?php echo $solc->fecha_max_pago;?>"/>
                </div>
            </td>
            <td class="valid_fecha"></td>
        </tr>
        <tr style="display:none;">
            <td>Negociable</td>
            <td>
                <div class="form-group">
                    <input type="checkbox" name="negociable" id="negociable" value="1" <?php echo ($solc->negociable=='1' ? 'checked' : '')?> />
                </div>
            </td>
        </tr>
        <tr>
            <td>Observación</td>
            <td>
                <div class="form-group">
                    <textarea name="observacion" id="observacion" maxlength="120" placeholder="Observación" class="form-control input-sm"><?php echo $solc->observacion?></textarea>
                </div>
            </td>
        </tr>
        <tr>
            <td>Adjunto (.pdf, .xlsx, .docx, .png, .jpeg, .jpg)</td>
            <td>
                <div class="form-group">
                    <input type="file" name="file" style="display: none;" id="file" accept=".pdf,.xlsx,.docx,.png,.jpeg,.jpg,.gif" />
                    <a class="btn btn-default btn-upload" href="#">
                        Seleccionar archivo
                        <i class="glyphicon glyphicon-upload"></i>
                    </a>
                </div>
            </td>
            <td class="file_info"><?php echo $solc->file->descripcion?></td>
        </tr>
        <?php 
        $return = (isset($_GET['return']) ? $_GET['return'] : null);
        $return = (!empty($return) ? $return : 'consultar' );
        ?>
        <tr>
            <td>
                <a class="btn btn-default" href="?c=solcheque&a=detalle&s=<?php echo $solc->id?>&return=<?php echo $return?>">
                    <i class="glyphicon glyphicon-arrow-left"></i> Regresar
                </a>
            </td>
            <td>
                <div class="form-group">
                    <button class="btn btn-sm btn-primary" data-create="S" type="submit" title="Crea solicitud sin enviar para autorización">
                        <i class="glyphicon glyphicon-pencil"></i> Guardar
                    </button>
                    &nbsp; &nbsp;
                    <button class="btn btn-sm btn-success" data-create="N" type="submit" title="Crea y envía solicitud para autorización">
                        Guardar y Enviar
                        <i class="glyphicon glyphicon-envelope"></i>
                    </button>
                    <span class='msj_error'></span>
                </div>
            </td>
            <td></td>
        </tr>
        </tbody>
	</table>
</form>

<script type="text/javascript" src="js/file.js"></script>
<script type="text/javascript" src="js/js.js"></script>
<script type="text/javascript">
	$(document).ready(function(){

            $('[name=fecha_max_pago]').datepicker({format: 'dd/mm/yyyy'}).on('changeDate', function(e){
                $(this).datepicker('hide');
            });
            var formsol=$('form#frmCrearSolc');
            var inp_empresa=$('[name=id_empresa]');
            var inp_cc = $('[name=id_cc]');
            var iniload=true;

            var pjson={
                msj: function(text){
                    $('.msj_error').text(text);
                },
                empresa_cc: function(){
                    if(inp_empresa.val()!==''){
                        var request=request_json_id({
                            id: inp_empresa.val(),
                            action: 'json.php?c=solcheque&a=json_cc_empresa',
                            method: formsol.attr('method')
                        });
                        if(request!==undefined){
                            inp_cc.html('');
                            $.each(request.cc, function(i, item){
                                inp_cc.append("<option value='" + item.id_cc + "' " + (iniload ? (parseInt(item.id_cc)==parseInt('<?php echo $solc->empresa->cc->id?>') ? 'selected' : '') : '') + ">" + item.cc_descripcion + "</option>");
                                iniload=false;
                            });
                            //$('.viewn3').show();
                        }else{
                            inp_cc.append("<option value=''>--Centros de Costo--</option>");	
                        }
                    }else{
                        inp_cc.html('');
                        inp_cc.append("<option value=''>--Centros de Costo--</option>");	
                    }
                },
                clear: function(){
                    $('[name=nombre_beneficiario]').val('');
                    $('[name=valor_cheque]').val('');
                    $('[name=concepto_pago]').val('');
                    $('[name=fecha_max_pago]').val('');
                    $('[name=negociable]').prop('checked',false);
                    $('[name=observacion]').val('');
                    $('[name=file]').val('');
                    $('.file_info').text('');
                },
                isValid: function(){
                    var error=false;
                    if($('[name=id_empresa]').val()===''){
                        $('.valid_empresa').text('Seleccionar Empresa.');
                        error=true;
                    }
                    if($('[name=id_cc]').val()===''){
                        $('.valid_cc').text('Seleccionar Centro de Costo.');
                        error=true;
                    }
                    if($('[name=nombre_beneficiario]').val()===''){
                        $('.valid_nombre').text('Ingresar nombre beneficiario.');
                        error=true;
                    }
                    if($('[name=valor_cheque]').val()==='' || isNaN($('[name=valor_cheque]').val())){
                        $('.valid_monto').text('Ingresar monto del cheque.');
                        error=true;
                    }
                    if($('[name=concepto_pago]').val()===''){
                        $('.valid_concepto').text('Ingresar concepto del pago.');
                        error=true;
                    }
                    if($('[name=fecha_max_pago]').val()===''){
                        $('.valid_fecha').text('Ingresar fecha máx. de pago.');
                        error=true;
                    }
                    return !error;
                }
            };
            pjson.empresa_cc(); // carga centro de costo asignados a la empresa
            $('.btn-upload').click(function(){
                $('[name=file]').click();
                return false;
            });
            $('[name=file]').change(function(){
                var path_file=$(this).val();
                pjson.msj('');
                if(path_file!==undefined){
                    if(path_file!==''){
                        $('.file_info').text('');
                        if(!file.validar(path_file)){
                                $(this).val('');
                                $('.file_info').text('Formato incorrecto');
                        }else{
                                $('.file_info').text(file.name(path_file));
                        }
                    }
                }
            });
            inp_empresa.change(function(){
                pjson.empresa_cc();
            });
            $('[type=submit]').click(function(){
                $('[name=borrador]').val($(this).attr('data-create'));
                $('.text-input tr td:nth-child(3):not(.file_info)').text('');
            });
            formsol.submit(function(){
                $('[type=submit]').attr('disabled','disabled');
                pjson.msj('');
                if(pjson.isValid()){
                    var request=request_json(this);
                    if(request!==undefined){
                            if(request.exito){
                                if($('[name=borrador]').val()=='N'){
                                    var rol='<?php echo $perfil->rol?>';
                                    if(rol=='N1'){
                                        request_json_email({
                                            id: parseInt($('[name=id]').val()),
                                            state: 'send',
                                            status: rol,
                                            id_user: '<?php echo $perfil->id?>'
                                        });
                                    }else if(rol=='N2'){
                                        request_json_email({
                                            id: parseInt($('[name=id]').val()),
                                            state: 'send',
                                            status: rol,
                                            id_empresa: $('[name=id_empresa]').val(),
                                            id_user: '<?php echo $perfil->id?>'
                                        });
                                    }
                                }
                                window.location='?c=solcheque&a=detalle&s=' + $('[name=id]').val() + '&return=<?php echo $return?>';
                            }else{
                                pjson.msj(request.msj);
                            }
                    }
                }
                $('[type=submit]').removeAttr('disabled');
                return false;
            });
	});
</script>

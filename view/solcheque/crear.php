<link rel="stylesheet" type="text/css" href="css/style.css?v=<?php echo date('His')?>">
<style>
    .traza .traza-item > .info-status {
        bottom: -30px !important;
        top:auto;
    }
</style>
<h4 class="text-blue">Listado de Solicitudes Propias de Cheques</h4>
<?php 
$iscorreo = FALSE;
if(!empty($perfil)){
    $iscorreo = stripos($perfil->email, '@impressarepuestos.com');
    $iscorreo = !empty($iscorreo);
    if(!$iscorreo){
        $iscorreo = stripos($perfil->email, '@impressatalleres.com');
        $iscorreo = !empty($iscorreo);
    }

    $iscorreo = TRUE;
}
?>
<form role="form" id="frmCrearSolc" name="frmCrearSolc"  class="form-inline" method="post" action="json.php?c=solcheque&a=agregar_cheque">
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
                    <input type="hidden" name="borrador" value=""/>
                    <select class="form-control input-sm" id="id_empresa" name="id_empresa">
                        <?php if(!empty($perfil)): ?>
                            <?php if(!empty($perfil->empresa)): ?>
                                <?php if(count($perfil->empresa) > 1): ?>
                                    <option value="">-- Seleccione Empresa --</option>
                                <?php endif; ?>
                                <?php foreach ($perfil->empresa as $emp): ?>
                                    <option value="<?php echo $emp->id; ?>"><?php echo $emp->nombre; ?></option>
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
                    <input type="hidden" name="id_proveedor" value="" id="id_proveedor"/>
                    <input autocomplete="off" type="text" name="nombre_beneficiario" id="nombre_beneficiario" maxlength="67" placeholder="Nombre beneficiario" class="form-control input-sm"/>
                </div>
            </td>
            <td>
                <a class="btn btn-default btn-proveedor" href="#">
                    Buscar
                    <i class="glyphicon glyphicon-search"></i>
                </a>
            </td>
            <td class="valid_nombre"></td>
        </tr>
        <tr>
            <td>
			Monto del cheque &nbsp;
                <select name="moneda">
                    <option value="$">Moneda $</option>
                </select>
			</td>
            <td>
                <div class="form-group">
                    <input autocomplete="off" type="text" name="valor_cheque" id="valor_cheque" placeholder="Monto del cheque" class="form-control input-sm"/>
                </div>
            </td>
            <td class="valid_monto"></td>
        </tr>
        <tr>
            <td>Categoría</td>
            <td>
                <div class="form-group">
                    <label id="id_categoria" class="control-label">
                        <select class="form-control input-sm" id="id_categoria" name="id_categoria">
                            <?php if(!empty($category)): ?>
                                <?php if(count($category) > 1): ?>
                                    <option value="">-- Seleccione Categoría --</option>
                                <?php endif; ?>
                                <?php $grupo = ""; ?>
                                <?php foreach ($category as $cat): ?>
                                    <?php if($grupo != $cat->gcia): ?>
                                        <?php if($grupo != ""): ?>
                                            </optgroup>
                                        <?php endif; ?>
                                        <optgroup label="<?php echo $cat->gcia; ?>">
                                        <?php $grupo = $cat->gcia; ?>
                                    <?php endif; ?>
                                    <option value="<?php echo $cat->id; ?>" data-gcia="<?php echo $cat->gcia_aprueba;?>"><?php echo $cat->categoria; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </label>
                </div>
            </td>
            <td class="valid_categoria"></td>
        </tr>
        <tr style="display:none;" class="tr_gcia">
            <td>Gerencia</td>
            <td>
                <div class="form-group">
                    <label id="id_gerencia" class="control-label">
                        <select class="form-control input-sm" id="id_gerencia" name="id_gerencia">
                            <?php if(!empty($gcias)): ?>
                                <?php if(count($gcias) > 1): ?>
                                    <option value="0">-- Seleccione Gerencia --</option>
                                <?php endif; ?>
                                <?php $grupo = ""; ?>
                                <?php foreach ($gcias as $g): ?>
                                    <option value="<?php echo $g->id_usuario; ?>"><?php echo $g->usr_usuario; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </label>
                </div>
            </td>
            <td class="valid_gerencia"></td>
        </tr>
        <tr>
            <td>Proyecto</td>
            <td>
                <div class="form-group">
                    <input autocomplete="off" type="text" name="proyecto" id="proyecto" maxlength="50" placeholder="Proyecto" class="form-control input-sm"/>
                </div>
            </td>
            <td class="valid_proyecto"></td>
        </tr>
        <tr>
            <td>Concepto del pago</td>
            <td>
                <div class="form-group">
                    <textarea autocomplete="off" name="concepto_pago" id="concepto_pago" maxlength="120" placeholder="Concepto del pago" class="form-control input-sm"></textarea>
                </div>
            </td>
            <td class="valid_concepto"></td>
        </tr>
        <!--<tr>
            <td>Fecha Máx. de pago</td>
            <td>
                <div class="form-group">
                    <input autocomplete="off" type="text" name="fecha_max_pago" id="fecha_max_pago" placeholder="Fecha máx. de pago" class="form-control input-sm"/>
                </div>
            </td>
            <td class="valid_fecha"></td>
        </tr>-->
        <tr style="display: none;">
            <td>Negociable</td>
            <td>
                <div class="form-group">
                    <input type="checkbox" name="negociable" id="negociable" value="1" />
                </div>
            </td>
        </tr>
        <tr>
            <td>Observación</td>
            <td>
                <div class="form-group">
                    <textarea autocomplete="off" name="observacion" id="observacion" maxlength="120" placeholder="Observación" class="form-control input-sm"></textarea>
                </div>
            </td>
        </tr>
        <tr>
            <td>Adjunto (.pdf, .xlsx, .xls, .docx, .png, .jpeg, .jpg), tamaño máximo 2MB.</td>
            <td>
                <div class="form-group">
                    <input type="file" name="file" style="display: none;" id="file" accept=".pdf,.xlsx,.xls,.docx,.png,.jpeg,.jpg,.gif" />
                    <a class="btn btn-default btn-upload" href="#">
                        Seleccionar archivo
                        <i class="glyphicon glyphicon-upload"></i>
                    </a>
                </div>
            </td>
            <td class="file_info"></td>
        </tr>
        <tr>
            <?php if($iscorreo):?>
            <td></td>
            <td>
                <div class="form-group">
                    <!--<button class="btn btn-sm btn-primary" data-create="S" type="submit" title="Crea solicitud sin enviar para autorización">
                        <i class="glyphicon glyphicon-plus"></i> Crear
                    </button>-->
                    <button class="btn btn-sm btn-success" data-create="N" type="submit" title="Crea y envía solicitud para autorización">
                        <i class="glyphicon glyphicon-plus"></i> Crear y Enviar
                        <i class="glyphicon glyphicon-envelope"></i>
                    </button>
                    <span class='msj_error'></span>
                </div>
            </td>
            <td></td>
            <?php else: ?>
            <td colspan="2">
            <div class="alert alert-danger" role="alert">
                <i class="glyphicon glyphicon-warning-sign" style="font-size:20px;"></i>
                No es posible crear solicitud de cheque, la cuenta de usuario no tiene asignado correo interno.
            </div>
            </td>
            <?php endif; ?>
        </tr>
        </tbody>
	</table>
</form>

<div class="row traza">
    <h3 class="title_traza">
        <i class="glyphicon glyphicon-check"></i>
        Trazabilidad de solicitud
    </h3>
    
    <div class="col-sm-2 traza-item pause">
        <p>Solicitante Cco.</p>
        <label>
                <i class="glyphicon glyphicon-user"></i>
                <?php echo $perfil->nombre; ?>                        
        </label>
        <span class="info-status">
            <i class="glyphicon glyphicon-time" style="color: #222;display: inline;text-align: center;margin: auto;margin-top: -6px;"></i>
            En proceso
        </span>
    </div>

    <div class="col-sm-2 traza-item n2 pause">
        <p>Autorizador Cco.</p>
        <label>
                <i class="glyphicon glyphicon-user"></i>
                <span class="usr_n2"></span>                    
        </label>
        <span class="info-status">
            <i class="glyphicon glyphicon-time" style="color: #222;display: inline;text-align: center;margin: auto;margin-top: -6px;"></i>
            Pendiente
        </span>
    </div>

    <div class="col-sm-2 traza-item n3 pause">
        <p>Gestor c. gasto.</p>
        <label>
                <i class="glyphicon glyphicon-user"></i>
                <span class="usr_n3"></span>                    
        </label>
        <span class="info-status">
            <i class="glyphicon glyphicon-time" style="color: #222;display: inline;text-align: center;margin: auto;margin-top: -6px;"></i>
            Pendiente
        </span>
    </div>

    <div class="col-sm-2 traza-item n4 pause">
        <p>Montos mayor o igual $5K</p>
        <label>
                <i class="glyphicon glyphicon-user"></i>
                <span class="usr_n4"></span>                                          
        </label>
        <span class="info-status">
            <i class="glyphicon glyphicon-time" style="color: #222;display: inline;text-align: center;margin: auto;margin-top: -6px;"></i>
            Pendiente
        </span>
    </div>

    <div class="col-sm-2 traza-item n5 pause">
        <p>Recepción</p>
        <label>
                <i class="glyphicon glyphicon-user"></i>
                <span class="usr_n5"><?php echo $perfil->nombre; ?></span>                         
        </label>
        <span class="info-status">
            <i class="glyphicon glyphicon-time" style="color: #222;display: inline;text-align: center;margin: auto;margin-top: -6px;"></i>
            Pendiente
        </span>
    </div>
</div>


<div class="modal fade" id="modalConfirmar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Mensaje de confirmación</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<h2 class="info_create"></h2>
				<center>
					<a href="?c=menu&a=consulta" class="btn btn-default">
						<i class="glyphicon glyphicon-search"></i> 
						Consultar Solicitud
					</a>
					<a class="btn btn-primary" href="?c=solcheque&a=crear">
						<i class="glyphicon glyphicon-pencil"></i> 
						Crear otra solicitud
					</a>
				</center>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modalProveedor" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel2">Busqueda de proveedor</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
                <label for="buscar_proveedor">Proveedor: </label>
				<input type="text" name="buscar_proveedor" id="buscar_proveedor" class="form-control" placeholder="Ingresar proveedor"/>  
                <div style="display:block;max-height:400px;overflow-y:auto;">
                <table class="table table-responsive tbl-prov">
                    <tbody>

                    </tbody>
                </table>
                </div>
			</div>
		</div>
	</div>
</div>

<div class="loading">
    <div>
        <img src="public/load.gif"/>
        <label>Procesando...</label>
    </div>
</div>

<script type="text/javascript" src="js/file.js?v=<?php echo date('His')?>"></script>
<script type="text/javascript" src="js/js.js?v=<?php echo date('His')?>"></script>
<script type="text/javascript">
	$(document).ready(function(){
            let searchTimer;
            const txtProve = document.getElementById('buscar_proveedor');

            $('a.btn-proveedor').click(function(){
                $('#modalProveedor').modal('show').on('shown.bs.modal', function() {
                    txtProve.focus();
                });
                return false;
            });

            
            $('[name=nombre_beneficiario]').click(function(){
                $('[name=nombre_beneficiario]').attr('readonly', true);
                if(inp_empresa.val()==='1' ||
                   inp_empresa.val()==='2' || 
                   inp_empresa.val()==='6' ||
                   inp_empresa.val()==='7' ||
                   inp_empresa.val()==='8'){

                    $('#modalProveedor').modal('show').on('shown.bs.modal', function() {
                        txtProve.focus();
                    });
                }else{
                    $('[name=nombre_beneficiario]').removeAttr('readonly');
                }
                return false;
            });

            $('[name=buscar_proveedor]').keyup(function(){
                const texto=$(this).val();

                // Limpiar timer anterior
                clearTimeout(searchTimer);
                var tbl=$('.tbl-prov tbody');
                tbl.html('');
                if(texto.length>=3){
                    // Configurar timer para evitar muchas peticiones
                    searchTimer = setTimeout(function() {
                        var request=request_json_proveedor({
                            id_empresa: inp_empresa.val(),
                            proveedor: texto,
                            action: 'json.php?c=solcheque&a=json_buscar_proveedor',
                            method: 'post'
                        });
                        if(request!==undefined){
                            if(request.proveedores==undefined){ 
                                tbl.append('<tr><td>No se encontraron resultados.</td></tr>');
                                return;
                            }
                            if(request.proveedores.length>0){
                                $.each(request.proveedores, function(i, item){
                                    tbl.append('<tr class="tr-prov">' + 
                                        '<td>'+item.proveedor+'</td>' + 
                                        '<td><a href="#" class="btn btn-default" title="Clic para seleccionar" data-idprov="'+item.id+'" data-nombre="'+item.proveedor+'">Seleccionar</a></td>' + 
                                    '</tr>');
                                });
                                $('tr.tr-prov a').click(function(){
                                    var nombre=$(this).attr('data-nombre');
                                    $('[name=nombre_beneficiario]').val(nombre);

                                    var idprov=$(this).attr('data-idprov');
                                    $('[name=id_proveedor]').val(idprov);

                                    $('#modalProveedor').modal('hide');
                                });
                            }else{
                                tbl.append('<tr><td>No se encontraron resultados.</td></tr>');
                            }
                        }

                    }, 300);
                }
            });

            $('[name=fecha_max_pago]').datepicker({format: 'dd/mm/yyyy'}).on('changeDate', function(e){
                $(this).datepicker('hide');
            });
            $('#modalConfirmar').on('hidden.bs.modal', function (e) {
                window.location.replace('?c=solcheque&a=crear');
                return false;
            });
            $('[name=fecha_max_pago]').keypress(function(e){
                return (e.keyCode!=13);
            });
            var formsol=$('form#frmCrearSolc');
            var inp_empresa=$('[name=id_empresa]');
            var inp_cc = $('[name=id_cc]');
            var inp_cat = $('[name=id_categoria]');
            
            var pjson={
                    msj: function(text){
                            $('.msj_error').text(text);
                    },
                    empresa_cc: function(){
                            var is_cate_ = 0;
                            if(inp_empresa.val()!==''){
                                    var request=request_json_id({
                                            id: inp_empresa.val(),
                                            action: 'json.php?c=solcheque&a=json_cc_empresa',
                                            method: formsol.attr('method')
                                    });
                                    if(request!==undefined){
                                            inp_cc.html('');
                                            $.each(request.cc, function(i, item){
                                                    is_cate_ = 1;
                                                    inp_cc.append("<option value='" + item.id_cc + "'>" + item.cc_descripcion + "</option>");
                                            });
                                            //$('.viewn3').show();
                                    }else{
                                            inp_cc.append("<option value=''>--Centros de Costo--</option>");	
                                    }
                            }else{
                                    inp_cc.html('');
                                    inp_cc.append("<option value=''>--Centros de Costo--</option>");	
                            }
                            //console.log('cambio emp: ' + inp_cc.val());
                            //categoria_valida();
                    },
                    clear: function(){
                            $('[name=nombre_beneficiario]').val('');
                            $('[name=valor_cheque]').val('');
                            $('[name=concepto_pago]').val('');
                            //$('[name=fecha_max_pago]').val('');
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
                        if($('[name=id_categoria]').val()===''){
                            $('.valid_categoria').text('Seleccionar categoría.');
                            error=true;
                        }
                        if($('[name=proyecto]').val()==='' && $('[name=id_categoria]').val()==='37'){
                            $('.valid_proyecto').text('Ingresar proyecto.');
                            error=true;
                        }
                        if($('[name=id_categoria]').find('option:selected').data('gcia')===1 && $('[name=id_gerencia]').val()===''){
                            $('.valid_gerencia').text('Seleccionar gerencia.');
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
                        /*if($('[name=fecha_max_pago]').val()===''){
                            $('.valid_fecha').text('Ingresar fecha máx. de pago.');
                            error=true;
                        }*/
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
                $('[name=nombre_beneficiario]').val('');
                pjson.empresa_cc();
               //console.log('1-.cambio emp: ' + inp_cc.val());
                //categoria_valida();
                //console.log('2-.cambio emp: ' + inp_cc.val());
                flujo_aprobacion();

                $('[name=nombre_beneficiario]').attr('readonly', true);
                if(inp_empresa.val()!=='1' && 
                   inp_empresa.val()!=='2' && 
                   inp_empresa.val()!=='6' &&
                   inp_empresa.val()!=='7' &&
                   inp_empresa.val()!=='8'){
                    $('[name=nombre_beneficiario]').removeAttr('readonly');
                }
            });

            inp_cc.change(function(){
                //console.log('1.cambio cc:' +  $(this).val());
                categoria_valida();
                //console.log('2.cambio cc:' +  $(this).val());
                flujo_aprobacion();
            });
            $('[name=id_gerencia]').change(function(){
                //console.log('1.cambio cc:' +  $(this).val());
                flujo_aprobacion();
            });
            inp_cat.change(function(){
                flujo_aprobacion();
                var gcia = $(this).find('option:selected').data('gcia');
                
                if(gcia===1){
                    $('.tr_gcia').css('display','contents');
                    console.log(gcia);
                }else{
                    $('.tr_gcia').css('display','none');
                    $('[name=id_gerencia]').val('0');
                }
            });
            $('[name=valor_cheque]').keyup(function(){
                if(!($('[name=valor_cheque]').val()==='' || isNaN($('[name=valor_cheque]').val()))){
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function() {
                        flujo_aprobacion();
                    }, 300);
                }
            });
            
            $('[type=submit]').click(function(){
                $('[name=borrador]').val($(this).attr('data-create'));
                $('.text-input tr td:nth-child(3):not(.file_info)').text('');
            });
            formsol.submit(function(e){
                //return true;
                e.preventDefault();
                $('.loading').css({display:'block'});  
                $('[type=submit]').attr('disabled','disabled');
                pjson.msj('');  
                if(pjson.isValid()){
                    var request=request_json(this);
                    if(request!==undefined){
                        if(request.exito){
                            request_json_email({
                                id: parseInt(request.msj)
                            });
                            $('.info_create').text('Se ha creado solicitud No ' + request.msj);
                            $('#modalConfirmar').modal('show');
                        }else{
                            if(request.msj!==null){
                                pjson.msj(request.msj);
                            }else{
                                pjson.msj('Error: verificar formato y tamaño de archivo adjunto.');
                            }
                        }
                    }
                }
                $('.loading').hide();
                $('[type=submit]').removeAttr('disabled');
                return false;
            });
            //$('.loading').show();
			var empresa_id = $('[name=id_empresa]');
            var moneda = $('[name=moneda]');
            empresa_id.change(function(){
                set_moneda($(this).val());
            });
            categoria_valida();
            set_moneda(empresa_id.val());
            function set_moneda(empr){
                if(empr=='6' || empr=='8'){
                    moneda.html('');
                    if(empr=='6'){
                        moneda.append("<option value='L'>Moneda L</option>");
                    }else{
                        moneda.append("<option value='C$'>Moneda C$</option>");
                    }
                    moneda.append("<option value='$'>Moneda $</option>");
                }else{
                    moneda.html('');
                    moneda.append("<option value='$'>Moneda $</option>");
                }
            }

            function categoria_valida(){
                return false;
                var is_cat = 0;
                //console.log('categoria valida: cc: ' + inp_cc.val() + ' - emp: ' + inp_empresa.val()    );   
                if(inp_cc.val()!==''){
                    var request=request_json_categoria_cc({
                        id_empresa: inp_empresa.val(),
                        id_cc: inp_cc.val(),
                        action: 'json.php?c=solcheque&a=json_categoria_cc',
                        method: 'POST'
                    });

                    if(request!==undefined){
                        //console.log(request);
                        $.each(request, function(i, item){
                            var find_ = $("[name=id_categoria] option[value='" + item.id + "']");
                            //console.log( $("[name=id_categoria] option[value='" + item.id + "']").html());
                            if(find_.length==0){
                                is_cat = 1;
                                inp_cat.append("<option value='" + item.id + "' data-gcia='1'>" + item.categoria + "</option>");
                            }else{
                                is_cat = 1;
                            }
                        });
                    }	
                }

                if(is_cat==0){
                    $("[name=id_categoria] option[data-gcia='1']").each(function() {
                        $(this).remove();
                    });
                }
            }

            function flujo_aprobacion(){
                var request = request_json_flujo_aprobacion({
                        id_empresa: inp_empresa.val(),
                        id_cc: inp_cc.val(),
                        monto: $('[name=valor_cheque]').val(),
                        moneda: $('[name=moneda]').val(),
                        id_categoria: inp_cat.val(),
                        gerencia: $('[name=id_gerencia]').val(),
                        id_usuario: <?php echo $perfil->id; ?>,
                        action: 'json.php?c=solcheque&a=json_flujo_cheque',
                        method: 'post'
                    });
                    if(request!==undefined){
                        //console.log(request);

                        var n2 = request.N2[0];
                        var n3 = request.N3;
                        var n4 = request.N4;
                        var n5 = request.N5;

                        //console.log(n2);

                        $('.traza-item.n2 label').html("<label><i class='glyphicon glyphicon-user'></i><span class='usr_n2'></span></label>");
                        $('.usr_n2').text("");
                        if(n2!==null && n2!==undefined){
                            $('.usr_n2').text(n2.usr_usuario);
                            
                            var html_auto = "";
                            for(var i=0;i<request.N2.length;i++){
                                //console.log(request.N2[i]);
                                html_auto += "<label>" + 
                                                "<i class='glyphicon glyphicon-user'></i>" +
                                                "<span class='usr_n2'> " + request.N2[i].usr_usuario + "</span>" +
                                             "</label>";
                            }
                            $('.traza-item.n2 label').html(html_auto);
                        }

                        $('.usr_n3').text("");
                        if(n3!==null && n3!==undefined){
                            $('.usr_n3').text(n3.usr_usuario);
                        }

                        //console.log(n4);

                        $('.usr_n4').text("");
                        $('.n4').hide();
                        if(n4!==null && n4!==undefined){
                            $('.n4').show();
                            $('.usr_n4').text(n4.usr_usuario);
                        }

                         //console.log(n5);

                        if(inp_cat.val()!==''){
                            $('.usr_n5').text("");
                            $('.traza-item.n5 label').html("<label><span class='usr_n5'>NO REQUERIDA</span></label>");
                            $('.traza-item.n5 .info-status').hide();
                            $('.n5').removeClass('pause');
                            if(n5!==null && n5!==undefined){
                                $('.traza-item.n5 label').html("<label><i class='glyphicon glyphicon-user'></i><span class='usr_n5'></span></label>");
                                $('.n5').addClass('pause');
                                $('.usr_n5').text(n5.usr_usuario);
                                $('.traza-item.n5 .info-status').show();
                            }
                        }else{
                            $('.traza-item.n5 label').html("<label><i class='glyphicon glyphicon-user'></i><span class='usr_n5'><?php echo $perfil->nombre; ?></span></label>");
                            $('.n5').addClass('pause');
                            $('.traza-item.n5 .info-status').show();
                        }
                    }
            }

            $('.n4').hide();
            
	});
</script>

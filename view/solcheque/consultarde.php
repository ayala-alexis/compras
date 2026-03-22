<link rel="stylesheet" type="text/css" href="css/style.css">
<style>
    select.form-control{
        font-size: 12px;
        padding: 2px;
    }
    .row label{
        margin-bottom: 0px;
        font-size: 12px;
    }
</style>
<h4 class="text-blue">Listado de Solicitudes de Cheques Igual o Mayor a $5,000</h4>
<div class="row" style="margin-bottom: 5px;">
    <div class="col-sm-2">
        <label for="fecha_anio" style="display:block;">Fecha Año/Mes: </label>
        <select class="form-control input-sm" id="fecha_anio" name="fecha_anio" style="width:58px;min-width: auto;display: inline-block;">
            <?php $cant_anio=3;?>
            <?php for($i=date('Y');$i>=2019;$i--): ?>
                <?php if($cant_anio>0):?>
                    <option value="<?php echo $i?>"><?php echo $i?></option>
                <?php endif;?>
                <?php $cant_anio--;?>
            <?php endfor; ?>
        </select>
        <select class="form-control input-sm" id="fecha_mes" name="fecha_mes" style="width:65px;min-width: auto;display: inline-block;">
        </select>
    </div>
    <div class="col-sm-3">
        <label for="id_empresa">Empresa: </label>
        <select class="form-control input-sm" id="id_empresa" name="id_empresa" style="min-width: 100%;">
            <?php if(!empty($perfil_empresa)): ?>
                    <?php if(count($perfil_empresa) > 1): ?>
                        <option value="">-- Todas --</option>
                    <?php endif; ?>
                    <?php foreach ($perfil_empresa as $emp): ?>
                        <option value="<?php echo $emp->id_empresa; ?>"><?php echo $emp->nombre_empresa; ?></option>
                    <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-sm-3">
        <label for="id_empresa">Cco: </label>
        <select class="form-control input-sm" id="id_cc" name="id_cc" style="min-width: 100%;">
            <option> -- Todos -- </option>
        </select>
    </div>
    <div class="col-sm-2">
        <label for="status">Estado: </label>
        <select class="form-control input-sm" id="estado" name="estado" style="min-width: 100%;">
            <option value="start">Pendientes</option>
            <option value="true">Aprobadas</option>
            <option value="false">Desistidas</option>
        </select>
    </div>
    <div class="col-sm-2">
        <br/>
        <a href="?c=solcheque&a=consultarde" class="btn btn-success btn-update">
            <i class="glyphicon glyphicon-search"></i> Consultar
        </a>
    </div>
</div>
<table class="table table-condensed tablesorter" style="font-size: 12px;">
	<thead>
		<tr>
			<th style="width: 60px">No. <br/>Solicitud</th>
			<th style="min-width: 180px">Cco.</th>
			<th style="min-width: 280px">Descrición</th>
			<th style="min-width: 150px;">Estado</th>
			<th style="min-width: 110px;">Creación</th>
			<th colspan="2" style="width: 350px;">Acciones</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($data as $sol): ?>
		<tr>
			<td>
				<?php echo str_pad($sol->id,6,'0',STR_PAD_LEFT);?>
			</td>
			<td style="font-size:8px;">
				(<span class="ccs"><?php echo str_pad($sol->empresa->cc->codigo,2,' ',STR_PAD_LEFT);?></span>) <?php echo $sol->empresa->cc->nombre;?><br/>
				(<span class="ccs"><?php echo str_pad($sol->empresa->id,2,' ',STR_PAD_LEFT);?></span>) <?php echo $sol->empresa->nombre;?>
			</td>
			<td>
				<?php echo $sol->observacion;?>
				<br/>
				<b style='min-width:160px;display:inline-block;'>
                    &nbsp;<span style="color: #222;background-color: #b5ceea;padding: 2px;font-size: 11px;border-radius: 2px;border: 1px #7db3f1 solid;"><?php echo $sol->moneda.number_format($sol->valor_cheque, 2, '.', ',')?></span>
				</b>
			</td>
			<td>
				<u><b><?php echo strtoupper(get_type_sol('cheque'))?>:</b></u> <br/>	
				<?php echo status_solicitud('cheque',$sol->avance.'-'.$sol->status,$sol->requiere_recepcion)?>
			</td>
			<td>
				<b><i class="glyphicon glyphicon-user"></i> <?php echo $sol->usuario?></b> <br/>
				<?php 
				echo $sol->fecha." ".$sol->hora;
				?>
			</td>
			<td>
				<?php 
				$pdf = "http://intranet.impressa.com/report/?s=".$sol->id;
				$link_det = "?c=solcheque&a=detalle&s=".$sol->id;
				$is_cheque_upload='';
                $is_cheque_upload = is_cheque_file($sol->id);
				?>
				<a href="<?php echo $pdf;?>" class="btn btn-default" target="_blank">
					<i class="icon-print"></i>
				</a>
			</td>
			<td>
				<a href="<?php echo $link_det;?>&return=<?php echo get_action();?>" class="btn btn-sm btn-warning" title="Visualizar solicitud">
                    <i class="glyphicon glyphicon-pencil" style="padding: 0;margin:0;font-size: 14px;"></i>
                    Revisar
                </a>
                <?php if($is_cheque_upload!=''):?>
                    <a target="_blank" href="<?php echo $is_cheque_upload?>" class="btn btn-sm btn-default" title="Adjunto">
                        <i class="glyphicon glyphicon-file"></i>
                    </a>
				<?php endif;?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
            <tr>
                <th colspan="8">
                    solicitud(es) pendiente(s)
                </th>
            </tr>
	</tfoot>
</table>

<?php 
if(!empty($paginador)):
if($paginador['paginar']):?>
<div class="row">
    <nav aria-label="Page navigation" class="pull-right">
        <ul class="pagination">
            <li <?php echo ($paginador['pagina_actual']==0 ? "class='disabled'" : "")?>>
                <a href="?c=solcheque&a=consultarde&pag=<?php echo ($paginador['pagina_actual']-1)?>&e=<?php echo $id_empresa?>&cc=<?php echo $id_cc?>&s=<?php echo $id_estado?>&anio=<?php echo $id_anio?>&mes=<?php echo $id_mes?>" aria-label="Previous" title="Anterior">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php $totalpag=20;?>
            <?php for($pag=0;$pag<$paginador['pagina_total'];$pag++):?>
                <?php if($totalpag+$paginador['pagina_actual']>0):?>
                <li <?php echo ($pag==$paginador['pagina_actual'] ? "class='active'" : "")?>>
                    <a href="?c=solcheque&a=consultarde&pag=<?php echo $pag?>&e=<?php echo $id_empresa?>&cc=<?php echo $id_cc?>&s=<?php echo $id_estado?>&anio=<?php echo $id_anio?>&mes=<?php echo $id_mes?>"><?php echo ($pag+1)?></a>
                </li>
                <?php $totalpag--;?>
                <?php endif;?>
            <?php endfor;?>
            <li <?php echo ($paginador['pagina_actual']>=($paginador['pagina_total']-1) ? "class='disabled'" : "")?>>
                <a href="?c=solcheque&a=consultarde&pag=<?php echo ($paginador['pagina_actual']+1)?>&e=<?php echo $id_empresa?>&cc=<?php echo $id_cc?>&s=<?php echo $id_estado?>&anio=<?php echo $id_anio?>&mes=<?php echo $id_mes?>" aria-label="Next" title="Siguiente">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
</div>
<?php endif;?>
<?php endif;?>
<script type="text/javascript" src="js/js.js?v=<?php echo date('His')?>"></script>
<script>
    $(document).ready(function(){
        $('li.disabled').click(function(){
            return false;
        });
        var inp_empresa=$('[name=id_empresa]');
        var inp_cc = $('[name=id_cc]');
        var inp_estado = $('[name=estado]');
        var inp_anio = $('[name=fecha_anio]');
        var inp_mes = $('[name=fecha_mes]');

        $('.btn-update').click(function(){
            $(this).attr('href','?c=solcheque&a=consultarde&e=' + inp_empresa.val() + '&cc=' + inp_cc.val() + '&s=' + inp_estado.val() + '&anio=' + inp_anio.val() + '&mes=' + inp_mes.val());
        });
        var pjson={
            empresa_cc: function(){
                    if(inp_empresa.val()!==''){
                            var request=request_json_id({
                                    id: inp_empresa.val(),
                                    action: 'json.php?c=solcheque&a=json_cc_empresa_all',
                                    method: 'POST'
                            });
                            if(request!==undefined){
                                    inp_cc.html('');
                                    if(request.cc.length>1){
                                        inp_cc.append("<option value=''>-- Todos --</option>");
                                    }
                                    $.each(request.cc, function(i, item){
                                            inp_cc.append("<option value='" + item.id_cc + "'>" + item.cc_descripcion + "</option>");
                                    });
                                    //$('.viewn3').show();
                            }else{
                                    inp_cc.append("<option value=''>-- Todos --</option>");    
                            }
                    }else{
                            inp_cc.html('');
                            inp_cc.append("<option value=''>-- Todos --</option>");    
                    }
            },
            mes_anio: function(){
                var fecha_anio=$('[name=fecha_anio]').val();
                var fecha = new Date();
                var anio_actual = fecha.getFullYear();
                var mes_actual = (fecha.getMonth() + 1);
                var cant_meses = 12;
                if(fecha_anio==anio_actual){
                    cant_meses=mes_actual;
                }
                var fecha_mes = $('[name=fecha_mes]');
                var item_mes = $('[name=fecha_mes] option').length;
                var mes_seleccionado = fecha_mes.val(); //mes actual seleccionado
                fecha_mes.html('');
                var strmes = ['Todos','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                if(cant_meses>1){
                    fecha_mes.append("<option value='0'>Todos</option>");
                }
                for(var m=1; m<=cant_meses;m++){
                    fecha_mes.append("<option value='" + m + "'>" + strmes[m] + "</option>");
                }
                if(item_mes<1 && fecha_anio==anio_actual){
                    fecha_mes.val(mes_actual);
                }else if(mes_seleccionado!==undefined){
                    item_mes = $('[name=fecha_mes] option').length;
                    if(mes_seleccionado=='0' && item_mes>1){
                        fecha_mes.val('0');
                    }else if(parseInt(mes_seleccionado)<item_mes){
                        fecha_mes.val(mes_seleccionado);
                    }else if(fecha_anio==anio_actual){
                        fecha_mes.val(mes_actual);
                    }
                }
            }
        };

        inp_empresa.change(function(){
            pjson.empresa_cc();
        });
        $('[name=fecha_anio]').change(function(){
            pjson.mes_anio();
        });

        <?php if(isset($id_empresa)):?>
            inp_empresa.val('<?php echo $id_empresa;?>');
        <?php endif; ?>
        pjson.empresa_cc(); // carga centro de costo asignados a la empresa
        <?php if(isset($id_cc)):?>
            inp_cc.val('<?php echo $id_cc;?>');
        <?php endif; ?>
        <?php if(isset($id_estado)):?>
            inp_estado.val('<?php echo $id_estado;?>');
        <?php endif; ?>
        <?php if(isset($id_anio)):?>
            inp_anio.val('<?php echo $id_anio;?>');
        <?php endif; ?>

        pjson.mes_anio();

        <?php if(!empty($id_mes)):?>
            inp_mes.val('<?php echo $id_mes;?>');
        <?php endif; ?>
    });
</script>
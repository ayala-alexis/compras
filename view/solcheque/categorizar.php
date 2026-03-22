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
<h4 class="text-blue">Listado de Solicitudes de Cheques Pendientes de Autorizar</h4>
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
            <?php if(!empty($perfil)): ?>
                <?php if(!empty($perfil->empresa)): ?>
                    <?php if(count($perfil->empresa) > 1): ?>
                        <option value="">-- Todas --</option>
                    <?php endif; ?>
                    <?php foreach ($perfil->empresa as $emp): ?>
                        <option value="<?php echo $emp->id; ?>"><?php echo $emp->nombre; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
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
        <select class="form-control input-sm" id="estado" name="estado" style="min-width: 115%;">
            <option value="start">Pendiente Autorizar</option>
            <option value="true">Autorizadas</option>
            <option value="false">Desistidas</option>
        </select>
    </div>
    <div class="col-sm-2">
        <br/>
        <a href="?c=solcheque&a=consultar" class="btn btn-success btn-update pull-right">
            <i class="glyphicon glyphicon-search"></i> Consultar
        </a>
    </div>
</div>
<table class="table table-condensed tablesorter" style="font-size: 12px;">
	<thead>
		<tr>
			<th>Número Sol.</th>
			<th>Empresa / Centro Costo</th>
			<th>A nombre de</th>
			<th>Monto cheque</th>
			<th>Fecha</th>
            <th>Estado</th>
			<th style="min-width: 128px;">Acciones</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($data as $solc): ?>
		<tr style="font-size: 10px;">
                    <td><?php echo str_pad($solc->id,6,'0',STR_PAD_LEFT); ?></td>
			<td>
                            <span class="text-cc">( <?php echo $solc->empresa->id;?> )</span>
                            <?php echo $solc->empresa->nombre;?>
                            <br/>
                            <span class="text-cc">( <?php echo $solc->empresa->cc->id;?> )</span>
                            <?php echo $solc->empresa->cc->nombre; ?>
                        </td>
			<td><?php echo $solc->nombre_beneficiario?></td>
			<td><?php echo number_format($solc->valor_cheque, 2, '.', ',')?></td>
			<td><?php echo $solc->fecha." ".$solc->hora; ?></td>
                        <?php 
                            $color="#222"; 
                            if($solc->status=='D'){
                                $color="red";
                            }else if($solc->avance=='N5'){
                                $color="green";
                            }else{
                                $color="#0e53a2";
                            }
                        ?>
                        <td style="max-width: 100px;font-size: 11px;color: <?php echo $color;?>">
                            <?php 
                                echo status_descripcion($solc->avance,$solc->status);
                                echo (
                                        ($solc->avance=='N3' && $solc->status=='R' && $solc->devuelta=='*') 
                                        ? " <text style='color:orange;weight:bold;'>(Dev.Categ.)</text>" 
                                        : ''
                                    );
                            ?>
                        </td>
			<td>
                            <a href="?c=solcheque&a=detalle&s=<?php echo $solc->id?>&return=categorizar" class="btn btn-sm btn-default" title="Visualizar solicitud">
                                <i class="glyphicon glyphicon-search"></i>
                            </a>
                            <?php if($solc->file!=null):?>
                                <a target="_blank" href="public/upload/<?php echo $solc->file->filename?>" class="btn btn-sm btn-default" title="Adjunto">
                                    <i class="glyphicon glyphicon-file"></i>
                                    Adjunto
                                </a>
                            <?php endif; ?>
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
<?php if(!empty($paginador)):?>
<?php if($paginador['paginar']):?>
<div class="row">
    <nav aria-label="Page navigation" class="pull-right">
        <ul class="pagination">
            <li <?php echo ($paginador['pagina_actual']==0 ? "class='disabled'" : "")?>>
                <a href="?c=solcheque&a=categorizar&pag=<?php echo ($paginador['pagina_actual']-1)?>&e=<?php echo $id_empresa?>&cc=<?php echo $id_cc?>&s=<?php echo $id_estado?>&anio=<?php echo $id_anio?>&mes=<?php echo $id_mes?>" aria-label="Previous" title="Anterior">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php $totalpag=20;?>
            <?php for($pag=0;$pag<$paginador['pagina_total'];$pag++):?>
                <?php if($totalpag+$paginador['pagina_actual']>0):?>
                <li <?php echo ($pag==$paginador['pagina_actual'] ? "class='active'" : "")?>>
                    <a href="?c=solcheque&a=categorizar&pag=<?php echo $pag?>&e=<?php echo $id_empresa?>&cc=<?php echo $id_cc?>&s=<?php echo $id_estado?>&anio=<?php echo $id_anio?>&mes=<?php echo $id_mes?>"><?php echo ($pag+1)?></a>
                </li>
                <?php $totalpag--;?>
                <?php endif;?>
            <?php endfor;?>
            <li <?php echo ($paginador['pagina_actual']>=($paginador['pagina_total']-1) ? "class='disabled'" : "")?>>
                <a href="?c=solcheque&a=categorizar&pag=<?php echo ($paginador['pagina_actual']+1)?>&e=<?php echo $id_empresa?>&cc=<?php echo $id_cc?>&s=<?php echo $id_estado?>&anio=<?php echo $id_anio?>&mes=<?php echo $id_mes?>" aria-label="Next" title="Siguiente">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
</div>
<?php endif;?>
<?php endif;?>
<script type="text/javascript" src="js/js.js"></script>
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
            $(this).attr('href','?c=solcheque&a=categorizar&e=' + inp_empresa.val() + '&cc=' + inp_cc.val() + '&s=' + inp_estado.val() + '&anio=' + inp_anio.val() + '&mes=' + inp_mes.val());
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
                if(mes_actual>1){
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
        pjson.mes_anio();
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
        <?php if(isset($id_mes)):?>
            inp_mes.val('<?php echo $id_mes;?>');
        <?php endif; ?>
	});  
</script>
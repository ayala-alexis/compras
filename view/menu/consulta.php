<link rel="stylesheet" type="text/css" href="css/sics.css?v=<?php echo date('His');?>"/>
<div class="row rw-filter">
    <h4 class="title col-sm-6" style="border-bottom: none !important;">
        <i class="glyphicon glyphicon-user"></i> Consulta Histórica de <b><?php echo $perfil->usuario_nombre?></b>.
    </h4>
    <div class="col-sm-6 row">
        <div class="col-sm-2">
            <h4 class="title col-sm-6" style="border-bottom: none !important;">Filtros:</h4>
        </div>
        <div class="col-sm-10" style="margin-top: 7px;">
            <label for="fecha_anio">Año: </label>
            <select class="form-control input-sm" id="fecha_anio" name="fecha_anio" style="width:58px;min-width: auto;display: inline-block;">
                <?php $cant_anio=10;?>
                <?php for($i=date('Y');$i>(date('Y')-$cant_anio);$i--): ?>
                    <option value="<?php echo $i?>"><?php echo $i?></option>
                <?php endfor; ?>
            </select>

            <label for="fecha_mes">Mes: </label>
            <select class="form-control input-sm" id="fecha_mes" name="fecha_mes" style="width:78px;min-width: auto;display: inline-block;">
            </select>

            <label for="tiposol">Tipo Solicitud: </label>
            <select class="form-control input-sm" id="tiposol" name="tiposol" style="width:120px;min-width: auto;display: inline-block;">
                <?php if(($db->sol_cheque + $db->sol_ci + $db->sol_req + $db->sol_compra)>1):?>
                    <option value=""> -- Todas -- </option>
                <?php endif;?>

                <?php if($db->sol_ci ==1):?>
                    <option value="ci">C. Interno</option>
                <?php endif;?>

                <?php if($db->sol_cheque == 1):?>
                    <option value="cheque">Cheque</option>
                <?php endif;?>

                <?php if($db->sol_compra == 1):?>
                    <option value="compra">Compra</option>
                <?php endif;?>

                <?php if($db->sol_req == 1):?>
                    <option value="req">Requis. Suministro</option>
                <?php endif;?>
            </select>
        </div>
    </div>
</div>
<div class="row rw-filter" style="margin-bottom: 5px;">
    <div class="col-sm-2">
        <label for="id_empresa">Empresa: </label>
        <select class="form-control input-sm" id="id_empresa" name="id_empresa" style="min-width: 100%;">
            <?php if(!empty($empresa)):
            	if(count($empresa)>1):
            		echo "<option value=''>-- Todas --</option>";
            	endif;
            	foreach ($empresa as $emp):
            		echo "<option value='".$emp->id_empresa."'>$emp->nombre</option>";
            	endforeach;
            endif; ?>
        </select>
    </div>
    <div class="col-sm-2">
        <label for="id_empresa">Cco: </label>
        <select class="form-control input-sm" id="id_cc" name="id_cc" style="min-width: 100%;">
        </select>
    </div>
    <div class="col-sm-2">
        <label for="categoria">Categoría (Cheques): </label>
        <select class="form-control input-sm" id="categoria" name="categoria" style="min-width: 100%;">
            <?php if(!empty($category)): ?>
                
                    <option value="0">-- Seleccione Categoría --</option>
                
                <?php $grupo = ""; ?>
                <?php foreach ($category as $cat): ?>
                    <?php if($grupo != $cat->gcia): ?>
                        <?php if($grupo != ""): ?>
                            </optgroup>
                        <?php endif; ?>
                        <optgroup label="<?php echo $cat->gcia; ?>">
                        <?php $grupo = $cat->gcia; ?>
                    <?php endif; ?>
                    <option value="<?php echo $cat->id; ?>"><?php echo $cat->categoria; ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-sm-2">
        <label for="proveedor">Proveedor: </label>
        <input type="text" placeholder="Proveedor" class="form-control input-sm" id="proveedor" name="proveedor" style="min-width: 100%;" value="<?php echo $proveedor;?>"/>
    </div>
    <div class="col-sm-2">
        <label for="proyecto">Proyecto: </label>
        <input type="text" placeholder="Proyecto" class="form-control input-sm" id="proyecto" name="proyecto" style="min-width: 100%;" value="<?php echo $proyecto;?>"/>
    </div>
    <div class="col-sm-1">
        <br>
        <a href="?c=<?php echo get_controller()?>&a=<?php echo get_action()?>" class="btn btn-success btn-update" style="vertical-align: text-top;min-height: 25px;margin-top:2px;">
            <i class="glyphicon glyphicon-search"></i> Consultar
        </a>
    </div>
</div>

<div class="row">

<table class="table tbl-kpi">
	<thead>
		<tr>
			<th style="width: 60px">No. <br/>Solicitud</th>
			<th style="min-width: 150px">Cco.</th>
			<th style="min-width: 270px">Descrición</th>
			<th style="min-width: 140px;">Estado</th>
			<th style="min-width: 110px;">Creación</th>
			<th colspan="2" style="min-width: 110px;">Acciones</th>
            <th style="min-width: 110px;">Adjuntos</th>
		</tr>
	</thead>
	<tbody>
		<?php if(!empty($data_sol)):
			$actu = "";
			$color1 = "tsol1";
			$color2 = "tsol2";
			$color = "tsol1";
		foreach ($data_sol as $sol):
			if($actu!=$sol->tipo):
				if($color=='tsol1'):
					$color='tsol2';
				else:
					$color='tsol1';
				endif;
			endif;
			$actu = $sol->tipo;
			?>
		<tr class="tsol <?php echo $sol->tipo?> <?php echo $color?>">
			<td>
				<?php echo str_pad($sol->correlativo,6,'0',STR_PAD_LEFT);?>
			</td>
			<td style="font-size: 9px">
				(<span class="ccs"><?php echo str_pad($sol->cc_codigo,2,' ',STR_PAD_LEFT);?></span>) <?php echo $sol->cc_descripcion;?><br/>
				(<span class="ccs"><?php echo str_pad($sol->id_empresa,2,' ',STR_PAD_LEFT);?></span>) <?php echo $sol->nombre_empresa;?>
			</td>
			<td>
				<text title="[Compras: observación] [cheque: beneficiario y concepto]">
                    <?php echo $sol->observacion;?>
                    <?php if($sol->tipo=='cheque' && !empty($sol->proyecto)):?>
                        &nbsp; | &nbsp;<b>PROY.:</b> <span class="proyecto" title="Proyecto"><?php echo $sol->proyecto?></span>
                    <?php endif?>
                </text>
                <br/>
				<b style='min-width:160px;display:inline-block;'>
                    <?php if($sol->tipo=='cheque'):?>
                        &nbsp;<span title="Monto inicial" style="color: #222;background-color: #b5ceea;padding: 2px;font-size: 11px;border-radius: 2px;border: 1px #7db3f1 solid;"><?php echo $sol->moneda.number_format($sol->monto, 2, '.', ',')?></span>
                    <?php endif?>
                </b> &nbsp; 
			</td>
			<td style="font-size: 9px;padding-left: 10px;">
                <b><u><?php echo strtoupper(get_type_sol($sol->tipo))?>: </u><br/></b>
                <?php if($sol->tipo=='cheque' && $sol->estado=='N5-E'): ?>
					RECIBIDO
				<?php else: ?>
					<?php echo status_solicitud($sol->tipo,$sol->estado,$sol->requiere_recepcion); ?>
				 <?php endif;?>
			</td>
			<td>
                <i class="glyphicon glyphicon-user"></i> <?php echo $sol->usuario?></br>
                <i class="glyphicon glyphicon-time"></i>
				<?php 
				echo Help::formatDateN($sol->fecha_creado)." ".Help::formatTimeShortN($sol->hora_creado);
				?>
			</td>
            <?php if((int)$perfil->rol==2):?>
            <td>
                <?php 
                $pdf = "http://intranet.impressa.com/report/?s=".$sol->id;
                $link_det = "?c=solcheque&a=detalle&s=".$sol->id."&return=consulta";
                if(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']!=''){
                    $link_det = "?c=solcheque&a=detalle&s=".$sol->id."&ret=".
                                base64_encode("?".$_SERVER['QUERY_STRING']);
                }
                $is_cheque_upload='';
                if($sol->tipo=='ci' || $sol->tipo=='req'):
                    $pdf = "view/".$sol->tipo."/pdf.php?id=".$sol->id;
                    $link_det = "?c=".$sol->tipo."&a=crear&id=".($sol->tipo=='ci' ? 12 : 6)."&ps=".$sol->correlativo."&cs=".$sol->id_cc."&es=".$sol->id_empresa;
                elseif($sol->tipo=='sol'):
                    $pdf = "view/solc/pdf.php?id=".$sol->id;
                    $link_det = "?c=solc&a=crear&id=5&ps=".($sol->estado>1 && $sol->estado!=10 ? $sol->correlativo2 : $sol->correlativo)."&cs=".$sol->id_cc."&es=".$sol->id_empresa;
                else:
                    $is_cheque_upload = is_cheque_file($sol->id);
                endif;
                ?>
                <?php if($sol->tipo=='cheque'):?>
                <a href="<?php echo $pdf;?>" class="btn btn-default" target="_blank">
                    <i class="icon-print"></i>
                </a>
                <?php elseif($sol->estado!='0'):?>
                <a href="<?php echo $pdf;?>" class="btn btn-default" target="_blank">
                    <i class="icon-print"></i>
                </a>
                <?php endif;?>
            </td>
            <td>
                <?php if($sol->tipo=='cheque'):?>
                    <?php if($sol->estado=='N2-R'):?>
                        <a href="<?php echo $link_det;?>" class="btn btn-sm btn-warning" title="Revisar y Autorizar solicitud">
                            <i class="glyphicon glyphicon-pencil" style="padding: 0;margin:0;font-size: 14px;"></i>
                            Revisar
                        </a>
                    <?php elseif($sol->estado=='N1-C'): ?>
                        <a href="<?php echo $link_det;?>" class="btn btn-sm btn-default" title="Visualizar solicitud">
                            <i class="glyphicon glyphicon-search"></i>
                        </a>
                    <?php elseif($sol->estado=='N1-C' && $perfil->id_usuario==$sol->id_usuario): ?>
                        <a href="<?php echo $link_det;?>" class="btn btn-sm btn-primary" title="Editar solicitud">
                            <i class="glyphicon glyphicon-pencil"></i> Editar
                        </a>
                    <?php else:?>
                        <a href="<?php echo $link_det;?>" class="btn btn-sm btn-default" title="Visualizar solicitud">
                            <i class="glyphicon glyphicon-search"></i>
                        </a>
                    <?php endif;?>
                <?php else:?>
                    <?php if($sol->estado=='1'):?>
                        <a href="<?php echo $link_det;?>&return=<?php echo get_action();?>" class="btn btn-sm btn-warning" title="Revisar y Autorizar solicitud">
                            <i class="glyphicon glyphicon-pencil" style="padding: 0;margin:0;font-size: 14px;"></i>
                            Revisar
                        </a>
                    <?php elseif($sol->estado!='0'): ?>
                        <a href="<?php echo $link_det;?>&return=<?php echo get_action();?>" class="btn btn-sm btn-default" title="Visualizar solicitud">
                            <i class="glyphicon glyphicon-search"></i>
                        </a>
                    <?php elseif($sol->estado=='0' && $perfil->id_usuario==$sol->id_usuario): ?>
                        <a href="<?php echo $link_det;?>&return=<?php echo get_action();?>" class="btn btn-sm btn-primary" title="Editar solicitud">
                            <i class="glyphicon glyphicon-pencil"></i> Editar
                        </a>
                    <?php endif;?>
                <?php endif;?>
                <?php if($is_cheque_upload!=''):?>
                    <a target="_blank" href="<?php echo $is_cheque_upload?>" class="btn btn-sm btn-default" title="Adjunto">
                        <i class="glyphicon glyphicon-file"></i>
                    </a>
                <?php endif;?>
            </td>
            <td  style="width: 120px;">
                
                    <?php if(is_file(safe_utf_decode($sol->adjunto1))):?>
					<a target="_blank" href="uploads/<?php echo basename($sol->adjunto1)?>" class="btn btn-sm btn-default" title="Adjunto">
                        <i class="glyphicon glyphicon-file"></i>
                    </a>
                <?php endif;?>
				<?php if(is_file(safe_utf_decode($sol->adjunto2))):?>
					<a target="_blank" href="uploads/<?php echo basename($sol->adjunto2)?>" class="btn btn-sm btn-default" title="Adjunto">
                        <i class="glyphicon glyphicon-file"></i>
                    </a>
                <?php endif;?>
				<?php if(is_file(safe_utf_decode($sol->adjunto3))):?>
					<a target="_blank" href="uploads/<?php echo basename($sol->adjunto3)?>" class="btn btn-sm btn-default" title="Adjunto">
                        <i class="glyphicon glyphicon-file"></i>
                    </a>
                <?php endif;?>
				<?php if(is_file(safe_utf_decode($sol->file_4))):?>
					<a target="_blank" href="uploads/<?php echo basename($sol->file_4)?>" class="btn btn-sm btn-default" title="Adjunto">
                        <i class="glyphicon glyphicon-file"></i>
                    </a>
                <?php endif;?>
            </td>
            <?php else:?>
                <td  style="width: 50px;">
                    <?php 
                    $pdf = "http://intranet.impressa.com/report/?s=".$sol->id;
                    $link_det = "?c=solcheque&a=detalle&s=".$sol->id."&return=index";
                    $is_cheque_upload='';
                    if($sol->tipo=='ci' || $sol->tipo=='req'):
                        $pdf = "view/".$sol->tipo."/pdf.php?id=".$sol->id;
                        $link_det = "?c=".$sol->tipo."&a=crear&id=".($sol->tipo=='ci' ? 12 : 6)."&ps=".$sol->correlativo."&cs=".$sol->id_cc."&es=".$sol->id_empresa;
                    elseif($sol->tipo=='sol'):
                        $link_det = "?c=solc&a=crear&id=5&ps=".$sol->correlativo."&cs=".$sol->id_cc."&es=".$sol->id_empresa;
                        $pdf = "view/solc/pdf.php?id=".$sol->id;
                    else:
                        $is_cheque_upload = is_cheque_file($sol->id);
                    endif;
                    ?>
                    <a href="<?php echo $pdf;?>" class="btn btn-default" target="_blank">
                        <i class="icon-print"></i>
                    </a>
                </td>
                <td>
                    <a href="<?php echo $link_det;?>&return=<?php echo get_action();?>" class="btn btn-sm btn-default" title="Visualizar solicitud">
                        <i class="glyphicon glyphicon-search"></i>
                    </a>
                    <?php if($is_cheque_upload!=''):?>
                        <a target="_blank" href="<?php echo $is_cheque_upload?>?v=<?php echo date('His') ?>" class="btn btn-sm btn-default" title="Adjunto">
                            <i class="glyphicon glyphicon-file"></i>
                            Adjunto
                        </a>
                    <?php endif;?>
                </td>
                <td  style="width: 120px;">
                    <?php if(is_file(safe_utf_decode($sol->adjunto1))):?>
                        <a target="_blank" href="uploads/<?php echo basename($sol->adjunto1)?>" class="btn btn-sm btn-default" title="Adjunto">
                            <i class="glyphicon glyphicon-file"></i>
                        </a>
                    <?php endif;?>
                    <?php if(is_file(safe_utf_decode($sol->adjunto2))):?>
                        <a target="_blank" href="uploads/<?php echo basename($sol->adjunto2)?>" class="btn btn-sm btn-default" title="Adjunto">
                            <i class="glyphicon glyphicon-file"></i>
                        </a>
                    <?php endif;?>
                    <?php if(is_file(safe_utf_decode($sol->adjunto3))):?>
                        <a target="_blank" href="uploads/<?php echo basename($sol->adjunto3)?>" class="btn btn-sm btn-default" title="Adjunto">
                            <i class="glyphicon glyphicon-file"></i>
                        </a>
                    <?php endif;?>
                    <?php if(is_file(safe_utf_decode($sol->file_4))):?>
                        <a target="_blank" href="uploads/<?php echo basename($sol->file_4)?>" class="btn btn-sm btn-default" title="Adjunto">
                            <i class="glyphicon glyphicon-file"></i>
                        </a>
                    <?php endif;?>
                </td>
            <?php endif;?>
		</tr>
		<?php 
		endforeach;
        else:?>
            <tr class="tsol">
                <td colspan="6">
                    <h4 class="text-center">No se han encontrado datos.</h4>
                </td>
            </tr>
		<?php endif;?>
	</tbody>
</table>
</div>

<?php 
$link_action = "?c=".get_controller()."&a=".get_action()."&e=".$id_empresa."&cc=".$id_cc."&s=".$id_tiposol."&anio=".$id_anio."&mes=".$id_mes."&p=".$proyecto."&ct=".$categoria."&pv=".$proveedor;
echo Help::paginator($paginador,$link_action);
?>

<script type="text/javascript" src="js/js.js?v=<?php echo date('His')?>"></script>
<script type="text/javascript">
	$(document).ready(function(){
        var inp_empresa=$('[name=id_empresa]');
        var inp_cc = $('[name=id_cc]');
        var inp_tiposol = $('[name=tiposol]');
        var inp_anio = $('[name=fecha_anio]');
        var inp_mes = $('[name=fecha_mes]');
        var proyecto = $('[name=proyecto]');
        var categoria = $('[name=categoria]');
        var proveedor = $('[name=proveedor]');

        $('.btn-update').click(function(){
            $(this).attr('href',
                '?c=<?php echo get_controller()?>&a=<?php echo get_action()?>&e=' + 
                inp_empresa.val() + 
                '&cc=' + inp_cc.val() + 
                '&s=' + inp_tiposol.val() + 
                '&anio=' + inp_anio.val() + 
                '&mes=' + inp_mes.val() + 
                '&p=' + proyecto.val() + 
                '&ct=' + categoria.val() + 
                '&pv=' + proveedor.val()
            );
        });

         proyecto.on('keydown', function(e) {
            if (e.keyCode === 13) { // Enter    
                window.location = '?c=<?php echo get_controller()?>&a=<?php echo get_action()?>&e=' + 
                                  inp_empresa.val() + 
                                  '&cc=' + inp_cc.val() + 
                                  '&s=' + inp_tiposol.val() + 
                                  '&anio=' + inp_anio.val() + 
                                  '&mes=' + inp_mes.val() + 
                                  '&p=' + proyecto.val() + 
                                  '&ct=' + categoria.val() +
                                  '&pv=' + proveedor.val();
            }
        });

        proveedor.on('keydown', function(e) {
            if (e.keyCode === 13) { // Enter    
                window.location = '?c=<?php echo get_controller()?>&a=<?php echo get_action()?>&e=' + 
                                  inp_empresa.val() + 
                                  '&cc=' + inp_cc.val() + 
                                  '&s=' + inp_tiposol.val() + 
                                  '&anio=' + inp_anio.val() + 
                                  '&mes=' + inp_mes.val() + 
                                  '&p=' + proyecto.val() + 
                                  '&ct=' + categoria.val() +
                                  '&pv=' + proveedor.val();
            }
        });

        var pjson={
            empresa_cc: function(){
                if(inp_empresa.val()!==''){
                    var request=request_json_id({
                        id: inp_empresa.val(),
                        action: 'json.php?c=menu&a=json_cc_empresa',
                        method: 'post'
                    });
                    if(request!==undefined){
                        inp_cc.html('');
                        if(request.length>1){
                        	inp_cc.append("<option value=''>-- Todos --</option>");	
                        }
                        $.each(request, function(i, item){
                            inp_cc.append("<option value='" + item.id + "'>" + item.nombre + "</option>");
                        });
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
                if(mes_actual>1 || parseInt(fecha_anio)<parseInt(anio_actual)){
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
        <?php if(isset($id_tiposol)):?>
            inp_tiposol.val('<?php echo $id_tiposol;?>');
        <?php endif; ?>
        <?php if(isset($id_anio)):?>
            inp_anio.val('<?php echo $id_anio;?>');
        <?php endif; ?>
        pjson.mes_anio();
        <?php if(isset($id_mes)):?>
            inp_mes.val('<?php echo $id_mes;?>');
        <?php endif; ?>
        <?php if(isset($categoria)):?>
            categoria.val('<?php echo $categoria;?>');
        <?php endif; ?>
	});
</script>
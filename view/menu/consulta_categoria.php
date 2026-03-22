<link rel="stylesheet" type="text/css" href="css/sics.css?v=<?php echo date('His');?>"/>
<h4 class="title">
	<i class="glyphicon glyphicon-user"></i> Consulta Gestión Categoría de <b><?php echo $perfil->usuario_nombre?></b>.
</h4>
<div class="row rw-filter" style="margin-bottom: 5px;">
    <div class="col-sm-2">
        <label for="fecha_anio" style="display:block;">Año/Mes: </label>
        <select class="form-control input-sm" id="fecha_anio" name="fecha_anio" style="width:58px;min-width: auto;display: inline-block;">
            <option value="0">Todos</option>
            <?php $cant_anio=10;?>
            <?php for($i=date('Y');$i>(date('Y')-$cant_anio);$i--): ?>
                <option value="<?php echo $i?>"><?php echo $i?></option>
            <?php endfor; ?>
        </select>
        <select class="form-control input-sm" id="fecha_mes" name="fecha_mes" style="width:78px;min-width: auto;display: inline-block;">
        </select>
    </div>
    <div class="col-sm-3">
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
    <div class="col-sm-1">
        <label for="status">Tipo Solicitud: </label>
        <select class="form-control input-sm" id="tiposol" name="tiposol" style="min-width: 100%;">
        	<?php if(($db->sol_cheque + $db->sol_ci + $db->sol_req + $db->sol_compra)>1):?>
            	<option value=""> -- Todas -- </option>
        	<?php endif;?>

        	<!--<?php if($db->sol_ci ==1):?>
            	<option value="ci">Consumo Interno</option>
            <?php endif;?>-->

            <?php if($db->sol_cheque == 1):?>
            	<option value="cheque">De Cheque</option>
            <?php endif;?>

            <?php if($db->sol_compra == 1):?>
            	<option value="compra">De Compra</option>
            <?php endif;?>

            <!--<?php if($db->sol_req == 1):?>
            	<option value="req">Requisición de Suministro</option>
            <?php endif;?>-->
        </select>
    </div>
    <div class="col-sm-1">
        <label for="id_estado">Estado: </label>
        <select class="form-control input-sm" id="id_estado" name="id_estado" style="min-width: 100%;">
            <option value="P">PENDIENTE</option>
            <option value="A">APROBADAS</option>
            <option value="D">DESISTIDAS</option>
        </select>
    </div>
    <div class="col-sm-2">
        <label for="proveedor">Proveedor: </label>
        <select class="form-control input-sm" id="proveedor" name="proveedor" style="min-width: 100%;">
            <?php if(!empty($data_prov)):
            	if(count($data_prov)>1):
            		echo "<option value=''>-- Todos --</option>";
            	endif;
            	foreach ($data_prov as $prov):
            		echo "<option value='".$prov->nombre_beneficiario."'>$prov->nombre_beneficiario</option>";
            	endforeach;
            else:
                echo "<option value=''>-- Todos --</option>";
            endif; ?>
        </select>
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
			<th style="min-width: 170px">Cco.</th>
			<th style="min-width: 230px">Descrición</th>
			<th style="min-width: 140px;">Estado</th>
			<th style="min-width: 110px;">Creación</th>
			<th style="min-width: 180px;">Acciones</th>
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
				<?php echo str_pad($sol->correlativo2,6,'0',STR_PAD_LEFT);?>
			</td>
			<td style="font-size:9px;">
				(<span class="ccs"><?php echo str_pad($sol->cc_codigo,2,' ',STR_PAD_LEFT);?></span>) <?php echo $sol->cc_descripcion;?><br/>
				(<span class="ccs"><?php echo str_pad($sol->id_empresa,2,' ',STR_PAD_LEFT);?></span>) <?php echo $sol->nombre_empresa;?>
			</td>
			<td>
				<?php echo $sol->observacion;?>
                <br/>
                <b style='min-width:160px;display:inline-block;'>
                    <?php if($sol->tipo=='cheque'):?>
                    &nbsp;<span style="color: #222;background-color: #b5ceea;padding: 2px;font-size: 11px;border-radius: 2px;border: 1px #7db3f1 solid;"><?php echo $sol->moneda.number_format($sol->monto, 2, '.', ',')?></span>
                    <?php endif?>
                </b>
			</td>
			<td>
                <b><u><?php echo strtoupper(get_type_sol($sol->tipo))?>: </u></b><br/>
                <?php if($sol->tipo=='cheque' && $sol->estado=='N5-E'): ?>
					RECIBIDO
				<?php else: 
					echo status_solicitud($sol->tipo,$sol->estado);
				 endif;?>
			</td>
			<td>
                <b><i class="glyphicon glyphicon-user"></i> <?php echo $sol->usuario?></b><br/>
                <i class="glyphicon glyphicon-time"></i>
				<?php 
				echo Help::formatDateN($sol->fecha_creado)." ".Help::formatTimeShortN($sol->hora_creado);
				?>
			</td>
            <td>
                <?php 
                $pdf = "http://intranet.impressa.com/report/?s=".$sol->id;
                $link_det = "?c=solcheque&a=detalle&s=".$sol->id;
                $is_cheque_upload='';
                if($sol->tipo=='sol'):
                    $pdf = "view/solc/pdf.php?id=".$sol->id;
                    $link_det = "?c=solc&a=trabajogc&ps=".$sol->correlativo."&cs=".$sol->id_cc."&es=".$sol->id_empresa;
                else:
                    $is_cheque_upload = is_cheque_file($sol->id);
                endif;
                ?>
                <a href="<?php echo $pdf;?>" class="btn btn-default" target="_blank">
                    <i class="icon-print"></i>
                </a>
                <?php if($sol->tipo=="cheque" && in_array($sol->estado, array("N5-R","N4-D","N5-E"))): ?>
                    <a href="<?php echo $link_det;?>&return=<?php echo get_action();?>" class="btn btn-sm btn-default" title="Visualizar solicitud">
                        <i class="glyphicon glyphicon-search" style="padding: 0;margin:0;font-size: 14px;"></i>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $link_det;?>&return=<?php echo get_action();?>" class="btn btn-sm btn-warning" title="Revisar y Autorizar solicitud">
                        <i class="glyphicon glyphicon-pencil" style="padding: 0;margin:0;font-size: 14px;"></i>
                        Revisar
                    </a>
                <?php endif; ?>

                <?php if($is_cheque_upload!=''):?>
                    <a target="_blank" href="<?php echo $is_cheque_upload?>" class="btn btn-sm btn-default" title="Adjunto">
                        <i class="glyphicon glyphicon-file"></i>
                    </a>
                <?php endif;?>
            </td>
           
		</tr>
		<?php 
		endforeach;
        else:?>
            <tr class="tsol">
                <td colspan="7">
                    <h4 class="text-center">No se han encontrado datos.</h4>
                </td>
            </tr>
		<?php endif;?>
	</tbody>
</table>
</div>

<?php 
$link_action = "?c=".get_controller()."&a=".get_action()."&e=".$id_empresa."&cc=".$id_cc."&s=".$id_tiposol."&anio=".$id_anio."&mes=".$id_mes."&st=".$estado."&pv=".$proveedor;
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
        var inp_estado = $('[name=id_estado]');
        var inp_prov = $('[name=proveedor]');
        
        $('.btn-update').click(function(){
            $(this).attr('href','?c=<?php echo get_controller()?>&a=<?php echo get_action()?>&e=' + inp_empresa.val() + '&cc=' + inp_cc.val() + '&s=' + inp_tiposol.val() + '&anio=' + inp_anio.val() + '&mes=' + inp_mes.val() + '&st=' + inp_estado.val() + '&pv=' + inp_prov.val());
        });

        $("[href='?c=menu&a=consulta_categoria'].active").click(function(){
            $(this).attr('href','?c=<?php echo get_controller()?>&a=<?php echo get_action()?>&e=' + inp_empresa.val() + '&cc=' + inp_cc.val() + '&s=' + inp_tiposol.val() + '&anio=' + inp_anio.val() + '&mes=' + inp_mes.val() + '&st=' + inp_estado.val()  + '&pv=' + inp_prov.val());
        });

        var pjson={
            empresa_cc: function(){
                if(inp_empresa.val()!==''){
                    var request=request_json_id({
                        id: inp_empresa.val(),
                        action: 'json.php?c=menu&a=json_cc_empresa_all',
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
                if(mes_actual>1){
                    //fecha_mes.append("<option value='0'>Todos</option>");
                }
                for(var m=0; m<=cant_meses;m++){
                    if(fecha_anio==='0'){
                        if(m==0){
                            fecha_mes.append("<option value='" + m + "'>" + strmes[m] + "</option>");    
                        }
                    }else{
                        fecha_mes.append("<option value='" + m + "'>" + strmes[m] + "</option>");
                    }
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
        <?php if(isset($estado)):?>
            inp_estado.val('<?php echo $estado;?>');
        <?php endif; ?>
        <?php if(isset($proveedor)):?>
            inp_prov.val('<?php echo $proveedor;?>');
        <?php endif; ?>
	});
</script>
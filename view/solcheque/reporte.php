<link rel="stylesheet" type="text/css" href="css/style.css?v=<?php echo date('His')?>">
<style>
	select option{
		color:#222;
	}
	form label{
		font-size: 14px;
	}
	span.info{
		color:red;
		font-weight: bold;
		font-size: 13px;
		margin-top: 10px;
		display: block;
		vertical-align: middle;
	}
</style>
<h4 class="text-blue">Reporte de Solicitudes de Cheques</h4>
<span style="display:block;margin-top:-8px;font-size: 12px;">* Fecha inicial y final de creación de solicitud.</span>
<hr/>
<form role="form" id="frmReporte" name="frmReporte" method="post" action="json.php?c=solcheque&a=reporte_request">
<div class="row">
	<div class="col-sm-2">
		<label for="id_empresa">Empresa</label>
	</div>
	<div class="col-sm-5">
		<select class="form-control" id="id_empresa" name="id_empresa">
            <?php 
            if(!empty($perfil)):
                if(!empty($perfil->empresa)):
                    if(count($perfil->empresa) > 0):
                        echo "<option value='0'>-- Todas las Empresas --</option>";
            		endif;
                    foreach ($perfil->empresa as $emp):
                        echo "<option value='$emp->id'>$emp->nombre</option>";
                    endforeach; 
                endif; 
            endif; ?>
        </select>
	</div>
</div>
<br/>
<div class="row">
	<div class="col-sm-2">
		<label for="fecha_inicial">Fecha Inicial</label>
	</div>
	<div class="col-sm-5">
		<input type="text" readonly="readonly" autocomplete="off" name="fecha_inicial" id="fecha_inicial" class="form-control" placeholder="Fecha Inicial" />
	</div>
</div>
<br/>
<div class="row">
	<div class="col-sm-2">
		<label for="fecha_final">Fecha Final</label>
	</div>
	<div class="col-sm-5">
		<input type="text" readonly="readonly" autocomplete="off" name="fecha_final" id="fecha_final" class="form-control" placeholder="Fecha Final" />
	</div>
</div>
<br/>
<div class="row">
	<div class="col-sm-2">
		<label for="desistidas">Incluir Desistidas</label>
	</div>
	<div class="col-sm-5">
		<input type="checkbox" value="1" name="desistidas" id="desistidas" />
	</div>
</div>
<br/>
<div class="row">
	<div class="col-sm-3 col-sm-offset-2">
		<button class="btn btn-success" type="submit">
			<i class="glyphicon glyphicon-download-alt"></i> Generar Excel
		</button>
	</div>
	<div class="col-sm-7">
		<span class="info"></span>
	</div>
</div>
</form>
<script type="text/javascript" src="js/js.js?v=<?php echo date('His')?>"></script>
<script>
    $(document).ready(function(){
        $('li.disabled').click(function(){
            return false;
        });
        var fechas = $('[name=fecha_inicial],[name=fecha_final]');
        fechas.datepicker({format: 'dd/mm/yyyy'}).on('changeDate', function(e){
            $(this).datepicker('hide');
        });
        fechas.keypress(function(e){
            return (e.keyCode!=13);
        });
        $('form').submit(function(e){
            e.preventDefault();
            if(formulario.isValid()){
            	var request=request_json(this);
                if(request!==undefined){
                    if(request.exito){
                        window.open("http://192.168.40.4/report/solicitudr.ashx?id_empresa=" + formulario.id_empresa() + "&fecha_inicial=" + formulario.fecha1 + "&fecha_final=" + formulario.fecha2 + "&desistida=" + (formulario.desistida() ? 1 : 0), '_blank');
                    }else{
                        formulario.info(request.msj);
                    }
                }
            }
            return false;
        });
        var formulario = {
        	info: function(text){
        		$('.info').text(text);
        	},
        	id_empresa: function(){
        		return	$('[name=id_empresa]').val().trim();
        	},
        	fecha_inicial: function(){
        		return	$('[name=fecha_inicial]').val().trim();
        	},
        	fecha_final: function(){
        		return	$('[name=fecha_final]').val().trim();
        	},
        	desistida: function(){
        		return	$('[name=desistidas]').prop('checked');
        	},
        	isValid: function(){
        		this.info('');
        		if(this.id_empresa()===''){
        			this.info('Error: seleccionar empresa.');
        			return false;
        		}
        		if(this.fecha_inicial()===''){
        			this.info('Error: ingresar fecha inicial.');
        			return false;
        		}
        		if(this.fecha_final()===''){
        			this.info('Error: ingresar fecha final.');
        			return false;
        		}
        		var fecha_ini = get_fecha(this.fecha_inicial());
        		var fecha_fin = get_fecha(this.fecha_final());
                this.fecha1=fecha_ini;
                this.fecha2=fecha_fin;
        		if(fecha_ini>fecha_fin){
        			this.info('Error: fecha inicial es mayor a fecha final.');
        			return false;
        		}
        		if(get_fecha_desc(this.fecha_inicial(),this.fecha_final())>31){
        			this.info('Error: no es posible exportar más de 31 días.');
        			return false;
        		}
        		return true;
        	},
            fecha1: 0,
            fecha2: 0
        };

    });

    function get_fecha(fecha){
        var ff = fecha.replace(/-|\//gi, function (f) { return ""; });
        return parseInt(ff.substring(4,8) + ff.substring(2,4) + ff.substring(0,2));
    }
    function get_fecha_desc(fecha1,fecha2){
        var ff1 = fecha1.replace(/-|\//gi, function (f) { return ""; });
        var ff2 = fecha2.replace(/-|\//gi, function (f) { return ""; });
        var ffecha1 = new Date(ff1.substring(4,8) + '-' + ff1.substring(2,4) + '-' + ff1.substring(0,2)).getTime();
        var ffecha2 = new Date(ff2.substring(4,8) + '-' + ff2.substring(2,4) + '-' + ff2.substring(0,2)).getTime();

        var milisecond = 1000;
        var segundo = 60;
        var minuto = 60;
        var hora = 24;

        return ((ffecha2-ffecha1)/(milisecond * segundo * minuto * hora));
    }
</script>
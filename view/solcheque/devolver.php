<link rel="stylesheet" type="text/css" href="css/style.css?v=<?php echo date('His')?>">
<h4 class="text-blue">Devolver Solicitud de Cheque No <?php echo str_pad($solc->id,6,'0',STR_PAD_LEFT)?></h4>
<div class="container-fluid solc_print">
    <form action='json.php?c=solcheque&a=json_solicitud_devolver' method='post'>
    <div class="row">
        <div class="col-sm-2">
            <label for='observacion'>Observaci&oacute;n: </label>
        </div>
        <div class="col-sm-6">
            <input type='hidden' name='id' value='<?php echo $solc->id?>'/>
            <input type='hidden' name='avance' value='<?php echo $solc->avance?>'/>
            <input type='hidden' name='status' value='D'/>
            <textarea id='observacion' name='observacion' maxlength="120" class='form-control'></textarea>
        </div>  
    </div>
    <br/>
    <br/>
    <?php 
    $return = (isset($_GET['return']) ? $_GET['return'] : null);
    $return = (!empty($return) ? $return : 'consultar' );
    ?>
    <div class="row">
        <div class="col-sm-4">
            <a class="btn btn-default" href="?c=solcheque&a=detalle&s=<?php echo $solc->id?>&return=<?php echo $return?>">
                <i class="glyphicon glyphicon-arrow-left"></i>
                Regresar
            </a>
        </div>
        <div class="col-sm-8 margin-right">
            <div>
                <button type='submit' class="btn btn-warning btn-success2">
                    Devolver
                    <i class="glyphicon glyphicon-repeat"></i>
                </button>
            </div>
        </div>
    </div>
    </form>
</div>

<script type="text/javascript" src="js/js.js?v=<?php echo date('His');?>"></script>
<script>
	$(document).ready(function(){
        $('.btn-success2').click(function(){
            return confirm('Devolver solicitud No <?php echo str_pad($solc->id,6,'0',STR_PAD_LEFT)?>?');
        });
        $('form').submit(function(){
            $('[type=submit]').attr('disabled','disabled');
            var request=request_json(this);
            if(request!==undefined){
                if(request.exito){
                    window.location='?c=solcheque&a=detalle&s=<?php echo $solc->id?>&return=<?php echo $return?>';
                }else{
                    alert(request.msj);
                }
            }
            //$('[type=submit]').removeAttr('disabled');
            return false;
        });
	});  
</script>
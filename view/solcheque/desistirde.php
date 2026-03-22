<link rel="stylesheet" type="text/css" href="css/style.css?v=<?php echo date('His')?>">
<h4 class="text-blue">Desistir Solicitud de Cheques No <?php echo str_pad($solc->id,6,'0',STR_PAD_LEFT)?></h4>
<div class="container-fluid solc_print">
    <form action='json.php?c=solcheque&a=json_solicitud_desistirde' method='post'>
	<?php $is_categoria = 0; ?>
    <?php if($solc->avance=='N3'):?>
        <div class="row">
            <div class="col-sm-2">
                <label for='observacion'>Categoría: </label>
            </div>
            <div class="col-sm-6">
                <label>
                    <?php 
                    if(!empty($solc->categoria)):
                        if($solc->categoria->id==ID_CATEGORIA_APROBACION_AUTOMATICA):
                            $is_categoria=ID_CATEGORIA_APROBACION_AUTOMATICA;
                            echo "** APROBACIÓN AUTOMÁTICA **";
                        else:
                            echo $solc->categoria->id." - ".$solc->categoria->nombre;
                        endif;
                    endif; 
                    ?>
                </label>
            </div>  
        </div>
    <?php endif;?>
    <div class="row">
        <div class="col-sm-2">
            <label for='observacion'>Observaci&oacute;n: </label>
        </div>
        <div class="col-sm-6">
            <input type='hidden' name='id' value='<?php echo $solc->id?>'/>
            <textarea id='observacion' name='observacion' class='form-control' maxlength="120"></textarea>
        </div>  
    </div>
    <br/>
    <br/>
    
    <?php 
    $return = (isset($_GET['return']) ? $_GET['return'] : null);
    $return = (!empty($return) ? $return : 'consultarde' );
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
                <button type='submit' class="btn btn-danger">
                    &nbsp; &nbsp; Desistir &nbsp; &nbsp;
                    <i class="glyphicon glyphicon-remove"></i>
                </button>
            </div>
        </div>
    </div>
    </form>
</div>

<script type="text/javascript" src="js/js.js"></script>
<script>
	$(document).ready(function(){
            $('form').submit(function(){
                var ok=confirm('Desistir solicitud No <?php echo str_pad($solc->id, 6,'0',STR_PAD_LEFT)?>?');
                if(ok){
                    $('[type=submit]').attr('disabled','disabled');
                    var request=request_json(this);
                    if(request!==undefined){
                        if(request.exito){
                            request_json_email({
                                id: parseInt('<?php echo $solc->id?>')
                            });
                            window.location='?c=solcheque&a=detalle&s=<?php echo $solc->id?>&return=<?php echo $return?>';
                        }else{
                            alert(request.msj);
                        }
                    }
                    $('[type=submit]').removeAttr('disabled');
                }
                return false;
            });
	});  
</script>
<link rel="stylesheet" type="text/css" href="css/style.css?v=<?php echo date('His')?>">
<h4 class="text-blue">Autorizar Solicitud de Cheques No <?php echo str_pad($solc->id,6,'0',STR_PAD_LEFT)?></h4>
<div class="container-fluid solc_print">
    <form action='json.php?c=solcheque&a=json_solicitud_autorizar' method='post'>
    <?php 
    $MAX_CHEQUE=MAX_CHEQUE;
    if($solc->moneda!='$'){
        if($solc->moneda=='C$'){
            $MAX_CHEQUE=MAX_CHEQUE_NI;
        }else{
            $MAX_CHEQUE=MAX_CHEQUE_HN;
        }
    }
    $is_categoria = 0; 
    ?>
    <?php if($solc->avance=='N3' || $solc->avance=='N4'):?>
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
            <input type='hidden' name='id_categoria' value='<?php echo $is_categoria?>'/>
            <input type='hidden' name='confirmar' value=''/>
            <input type='hidden' name='is5k' value='<?php echo (($solc->valor_cheque>=$MAX_CHEQUE && ($solc->avance=='N2' || $solc->avance=='N3' || $solc->avance=='N4') && $solc->categoria->id!=4) ? 1 : 0);?>'/>
            <input type='hidden' name='avance' value='<?php echo $solc->avance?>'/>
            <input type='hidden' name='status' value='A'/>
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
                <button type='submit' class="btn btn-success btn-success2">
                    Autorizar
                    <i class="glyphicon glyphicon-ok"></i>
                </button>
            </div>
        </div>
    </div>
    </form>
</div>

<div class="modal fade" id="modalConfirmar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">¿Solicitar aprobación de Dirección Ejecutiva?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h2 class="info_create"></h2>
                <center>
                    <a href="#" class="btn btn-default form-send" data-ok="0">
                        &nbsp; &nbsp;
                        NO
                        &nbsp; &nbsp;
                    </a>
                    &nbsp; &nbsp;
                    <a href="#" class="btn btn-primary form-send" data-ok="1">
                        &nbsp; &nbsp;
                        SI
                        &nbsp; &nbsp;
                    </a>
                </center>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="js/js.js?v=<?php echo date('His');?>"></script>
<script>
	$(document).ready(function(){
        $('.form-send').click(function(){
            $('[name=confirmar]').val($(this).attr('data-ok'));
            $('form').submit();
            $('#modalConfirmar').modal('hide');
        });
        $('.btn-success2').click(function(){
            return confirm('Autorizar solicitud No <?php echo str_pad($solc->id,6,'0',STR_PAD_LEFT)?>?');
        });
        $('form').submit(function(){
            <?php if($solc->valor_cheque>=$MAX_CHEQUE && $solc->avance=='N3' && $is_categoria==ID_CATEGORIA_APROBACION_AUTOMATICA && $solc->status=='T'):?>
                if($('[name=confirmar]').val()==''){
                    $('#modalConfirmar').modal('show');
                    return false;
                }else{
                    $('[name=is5k]').val($('[name=confirmar]').val());
                }
            <?php endif;?>
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
            //$('[type=submit]').removeAttr('disabled');
            return false;
        });
	});  
</script>
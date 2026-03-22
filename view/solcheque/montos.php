<link rel="stylesheet" type="text/css" href="css/style.css?v=<?php echo date('His')?>">
<h4 class="text-blue">Autorizar Solicitud de Cheques No <?php echo str_pad($solc->id,6,'0',STR_PAD_LEFT)?></h4>
<div class="container-fluid solc_print">
    <form action='json.php?c=solcheque&a=json_solicitud_monto' method='post'>
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
            <label for='monto_cheque'>Monto solicitud: </label>
        </div>
        <div class="col-sm-6">
            <input readonly=readonly autocomplete="off" value="<?php echo $solc->valor_cheque;?>" type="text" name="monto_cheque" id="monto_cheque" placeholder="Monto cheque" class="form-control input-sm"/>
        </div>  
    </div>
    <br/>
    <div class="row">
        <div class="col-sm-2">
            <label for='monto_retencion'>Retenciones <b>(-)</b>: </label>
        </div>
        <div class="col-sm-6">
            <input type='hidden' name='id' value='<?php echo $solc->id?>'/>
            <input autocomplete="off" value="<?php echo str_replace(array("*","$",",","L","C$","C"),"",$solc->monto_retencion);?>" type="text" name="monto_retencion" id="monto_retencion" placeholder="Monto retención" class="form-control input-sm"/>
        </div>  
        <div class="col-sm-4">
            <span class="monto_retencion" style="color:red"></span>
        </div>  
    </div>
    <br/>
    <div class="row">
        <div class="col-sm-2">
            <label for='monto_descuento'>Otros descuentos <b>(-)</b>: </label>
        </div>
        <div class="col-sm-6">
            <input autocomplete="off" value="<?php echo str_replace(array("*","$",",","L","C$","C"),"",$solc->monto_descuento);?>" type="text" name="monto_descuento" id="monto_descuento" placeholder="Otros descuentos" class="form-control input-sm"/>
        </div>  
        <div class="col-sm-4">
            <span class="monto_descuento" style="color:red"></span>
        </div>  
    </div>
    <br/>
    <div class="row">
        <div class="col-sm-2">
            <label for='monto_total'>Monto a pagar: </label>
        </div>
        <div class="col-sm-6">
            <input type='hidden' name='id' value='<?php echo $solc->id?>'/>
            <?php if($solc->monto_total==0) $solc->monto_total=$solc->valor_cheque + str_replace(array("*","$",",","L","C$","C"),"",$solc->monto_retencion); ?>
            <input readonly=readonly autocomplete="off" type="text" value="<?php echo str_replace(array("*","$",",","L","C$","C"),"",$solc->monto_total);?>" name="monto_total" id="monto_total" placeholder="Monto total" class="form-control input-sm"/>
        </div>  
        <div class="col-sm-4">
            <span class="monto_total" style="color:red"></span>
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
                    Guardar
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
            //return confirm('Guardar solicitud No <?php echo str_pad($solc->id,6,'0',STR_PAD_LEFT)?>?');
        });

        $('[name=monto_retencion]').keyup(function(){
            var cheque = parseFloat($('[name=monto_cheque]').val());
            var retencopm = parseFloat($('[name=monto_retencion]').val());
            var descuento = parseFloat($('[name=monto_descuento]').val());
            
            if($('[name=monto_retencion]').val()!=='' && !isNaN($('[name=monto_retencion]').val())){
                var total = cheque - retencopm - descuento;
                $('[name=monto_total]').val(total.toFixed(2));
            }else{
                $('[name=monto_total]').val(cheque.toFixed(2));
            }   
        });
        $('[name=monto_descuento]').keyup(function(){
            var cheque = parseFloat($('[name=monto_cheque]').val());
            var retencopm = parseFloat($('[name=monto_retencion]').val());
            var descuento = parseFloat($('[name=monto_descuento]').val());
            
            if($('[name=monto_descuento]').val()!=='' && !isNaN($('[name=monto_descuento]').val())){
                var total = cheque - retencopm- descuento;
                $('[name=monto_total]').val(total.toFixed(2));
            }else{
                $('[name=monto_total]').val(cheque.toFixed(2));
            }   
        });
        $('form').submit(function(){
            
            $('[type=submit]').attr('disabled','disabled');
            $('.monto_retencion').text('');
            if($('[name=monto_retencion]').val()==='' || isNaN($('[name=monto_retencion]').val())){
                $('.monto_retencion').text('Ingresar monto retención.');
                return false;
            }

            var request=request_json(this);
            if(request!==undefined){
                if(request.exito){
                    /*request_json_email({
                        id: parseInt('<?php echo $solc->id?>')
                    });*/
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
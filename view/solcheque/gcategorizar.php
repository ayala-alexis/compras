<link rel="stylesheet" type="text/css" href="css/style.css?v=<?php echo date('His')?>">
<h4 class="text-blue">Categorizar Solicitud de Cheque No <?php echo str_pad($solc->id,6,'0',STR_PAD_LEFT)?></h4>
<?php 
$MAX_CHEQUE=MAX_CHEQUE;
if($solc->moneda!='$'){
    if($solc->moneda=='C$'){
        $MAX_CHEQUE=MAX_CHEQUE_NI;
    }else{
        $MAX_CHEQUE=MAX_CHEQUE_HN;
    }
}
$id_categoria = (int)(!empty($solc->categoria) ? $solc->categoria->id : 0);
?>
<div class="container-fluid solc_print">
    <form action='json.php?c=solcheque&a=json_solicitud_categorizar' method='post'>
        
        <div class="row">
            <div class="col-sm-3">
                <label for='categoria'>Seleccionar categor&iacute;a: </label>
            </div>
            <div class="col-sm-6">
                <input type='hidden' name='id' value='<?php echo $solc->id?>'/>
                <input type='hidden' name='is5k' value='<?php echo ($solc->valor_cheque>=$MAX_CHEQUE ? 1 : 0);?>'/>
                <input type='hidden' name='confirmar' value=''/>
                <input type='hidden' name='option' value=''/>
                <select class='form-control' name='categoria' id='categoria'>
                    <option value=''>-- Seleccionar Categoria --</option>
                    <option value='<?php echo ID_CATEGORIA_APROBACION_AUTOMATICA;?>' <?php echo ($id_categoria==ID_CATEGORIA_APROBACION_AUTOMATICA ? "selected" : "")?>>** Aprobación Automática **</option>
                    <?php if(!empty($category)):?>
                        <?php foreach ($category as $cat):?>
                            <option value='<?php echo $cat->id_categoria?>' <?php echo ($id_categoria==$cat->id_categoria ? "selected" : "")?>><?php echo $cat->nombre_categoria?></option>
                        <?php endforeach;?>
                    <?php endif;?>
                </select>
            </div>
            <div class='col-sm-3'>
                <button type='submit' class="btn btn-primary btn-primary2">
                    Guardar
                    <i class="glyphicon glyphicon-pencil"></i>
                </button>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="col-sm-3">
                <label for='observacion'>Observaci&oacute;n: </label>
            </div>
            <div class="col-sm-6">
                <textarea class='form-control' name='observacion' id='observacion' maxlength="120"></textarea>
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
                <span class='info-text'></span>
                &nbsp; &nbsp; &nbsp; &nbsp;
                <button type='submit' class="btn btn-success btn-success2">
                    Guardar y Autorizar
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

<script type="text/javascript" src="js/js.js?v=<?php echo date('His')?>"></script>
<script>
	$(document).ready(function(){
            $('.form-send').click(function(){
                $('[name=confirmar]').val($(this).attr('data-ok'));
                $('form').submit();
                $('#modalConfirmar').modal('hide');
            });
            $('.btn-primary2').click(function(){
                $('[name=option]').val('T');
                if($('[name=categoria]').val()!==''){
                    return confirm('Asignar categoria a solicitud No <?php echo str_pad($solc->id,6,'0',STR_PAD_LEFT)?>');
                }else{
                    alert('Error: seleccionar categoria');
                }
                return false;
            });
            $('.btn-success2').click(function(){
                $('[name=option]').val('A');
                if($('[name=categoria]').val()!==''){
                    return confirm('Autorizar solicitud No <?php echo str_pad($solc->id,6,'0',STR_PAD_LEFT)?>');
                }else{
                    alert('Error: seleccionar categoria');
                }
                return false;
            });
            $('form').submit(function(){
                <?php if($solc->valor_cheque>=$MAX_CHEQUE):?>
                    if($('[name=categoria]').val()=='<?php echo ID_CATEGORIA_APROBACION_AUTOMATICA;?>' && $('[name=option]').val()!='T'){
                        if($('[name=confirmar]').val()==''){
                            $('#modalConfirmar').modal('show');
                            return false;
                        }else{
                            $('[name=is5k]').val($('[name=confirmar]').val());
                        }
                    }
                    if($('[name=categoria]').val()!='<?php echo ID_CATEGORIA_APROBACION_AUTOMATICA;?>'){
                        $('[name=is5k]').val('1');
                    }
                <?php endif;?>
                $('[type=submit]').attr('disabled','disabled');
                var request=request_json(this);
                if(request!==undefined){
                    if(request.exito){
                        if($('[name=option]').val()=='T'){
                            alert('Se ha asignado categoria a solicitud No <?php echo str_pad($solc->id,6,'0',STR_PAD_LEFT)?>');
                        }else{
                            request_json_email({
                                id: parseInt('<?php echo $solc->id?>')
                            }); 
                            window.location='?c=solcheque&a=detalle&s=<?php echo $solc->id?>&return=<?php echo $return?>';
                        }
                    }else{
                        alert(request.msj);
                    }
                }
                //$('[type=submit]').removeAttr('disabled');
                return false;
            });
	});  
</script>
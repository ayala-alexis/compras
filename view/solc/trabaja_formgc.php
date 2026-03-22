<link rel="stylesheet" type="text/css" href="css/style2.css?v=<?php echo date('His')?>">    
<style>
.invoice-title h2, .invoice-title h3 {
    display: inline-block;
}

.table > tbody > tr > .no-line {
    border-top: none;
}

.table > thead > tr > .no-line {
    border-bottom: none;
}

.table > tbody > tr > .thick-line {
    border-top: 2px solid;
}

#frmEnvia {
    padding: 0;
}
</style>
<script>
function GetURLParameter(sParam){
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++)
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam)
        {
            return sParameterName[1];
        }
    }
}
$(function(){

    $('[data-toggle="tooltip"]').tooltip();

    // DESISTIR SOLICITUD
    $('#negarCat').live('click', function(event){
        event.preventDefault();
        var url = $(this).attr('href');
        if( $('#prehsol_aprobacion_categoria').val().trim().length == 0 ) {
            notie.alert('error', 'debe digitar una observacion!', 3);
        } else {
            var confirm_box = confirm('Esta seguro de negar solicitud?');
            if (confirm_box) {
                var input = $("<input>").attr("type", "hidden").attr("name", "prehsol_aprobacion_categoria").val($('#prehsol_aprobacion_categoria').val().trim());
                $("#negarForm").attr("action", url);
                $('#negarForm').append($(input));
                $('#negarForm').submit();
               //window.location = url;
            }
        }
    });

    // Devolver SOLICITUD
    $('#devolverCat').live('click', function(event){
        event.preventDefault();
        var url = $(this).attr('href');
        if( $('#prehsol_aprobacion_categoria').val().trim().length == 0 ) {
            notie.alert('error', 'debe digitar una observacion!', 3);
        } else {
            var confirm_box = confirm('Esta seguro de devolver categoría de solicitud?');
            if (confirm_box) {
                var input = $("<input>").attr("type", "hidden").attr("name", "prehsol_aprobacion_categoria").val($('#prehsol_aprobacion_categoria').val().trim());
                $("#devolverForm").attr("action", url);
                $('#devolverForm').append($(input));
                $('#devolverForm').submit();
               //window.location = url;
            }
        }
    });
    
    // ENVIAR A NIVEL SUPERIOR
    $('#Niv1Cat').live('click', function(event){
        event.preventDefault();
        var url = $(this).attr('href');
        if( $('#prehsol_aprobacion_categoria').val().trim().length == 0 ) {
            notie.alert('error', 'debe digitar una observacion!', 3);
        } else {
            var confirm_box = confirm('Esta seguro de enviar la solicitud al nivel superior?');
            if (confirm_box) {
                var input = $("<input>").attr("type", "hidden").attr("name", "prehsol_aprobacion_categoria").val($('#prehsol_aprobacion_categoria').val().trim());
                $("#negarForm").attr("action", url);
                $('#negarForm').append($(input));
                $('#negarForm').submit();
               //window.location = url;
            }
        }
    });
        
    // APRUEBA SOLICITUD
    $('#frmApruebaCat').validate({
        rules:{
            prehsol_aprobacion_categoria : {
                required: true
            }
        },
        submitHandler: function(form) {
            if( $('#prehsol_aprobacion_categoria').val().trim().length == 0 ) {
                notie.alert('error', 'debe digitar una observacion!', 3);
            } else {
                $('body').removeClass('loaded');
                form.submit();
            }
        }
    });
    
});
</script>
<div class="container-fluid">
    <!-- Start Page Loading -->
    <div id="loader-wrapper">
        <h1>Espere... procesando peticion.</h1>
        <div id="loader">
        </div>        
        <div class="loader-section section-left"></div>
        <div class="loader-section section-right"></div>
    </div>
    <!-- End Page Loading -->
    <div class="row">
        <div class="col-xs-12">
            <div class="invoice-title">
                <a class="btn btn-default" href="?c=menu&a=<?php echo (isset($_GET['return']) ? $_GET['return'] : 'index')?>">
                    <i class="glyphicon glyphicon-arrow-left"></i>
                    Regresar
                </a>
                &nbsp; &nbsp; &nbsp;
                <h3>Gestion de Compra por Categoria</h3><h3 class="pull-right">Solicitud # <?php echo $infohsol[0]['prehsol_numero_sol']; ?></h3>
            </div>
            <hr>
            <div class="row">
                <div class="col-xs-4">
                    <address>
                        <strong>Fecha de solicitud :</strong><br>
                        <?php
                        setlocale(LC_TIME, "");
                        setlocale(LC_TIME, "es_ES");
                        echo iconv('ISO-8859-1', 'UTF-8', strftime('%A %d de %B, %Y %I:%M:%S %p', strtotime($infohsol[0]['prehsol_fecha'] . ' ' . $infohsol[0]['prehsol_hora'])));
                        echo '<br>Observaciones:';
                        echo '<b>'.$infohsol[0]['prehsol_obs1'].'</b>';
                        echo 'Aprobado:<br>';
                        echo '<b><p>'.$infohsol[0]['prehsol_aprobacion_usuario'].'</p>'.$infohsol[0]['prehsol_aprobacion'].'</b>';
                        if(!empty($infohsol[0]['prehsol_aprobacion_usuario'])) {
                            echo 'Autorizado nivel 2:<br>';
                            echo '<b><p>'.$infohsol[0]['prehsol_gestion_observacion'].'</p></b>';
                        }
                        if(!empty($infohsol[0]['obs_cate'])) {
                            echo 'Gestion Categoria:<br>';
                            echo '<b><p>'.$infohsol[0]['obs_cate'].'</p></b>';
                        }
                        ?>
                    </address>
                </div>

                <?php
                 if(!empty($infohsol[0]['prehsol_monto'])): ?>

                <div style='font-size:14px;background: #f5f5f5;line-height: 12px;padding: 3px;' class="col-xs-4">
                    <P><b>Proveedor:</b> <?php echo $Proveedor ?> </P>
                    <P><b>Monto:</b>
                    <?php if ($Empressa != 6 && $Empressa != 8){
						echo "$";
					}else{
						echo $infohsol[0]['moneda'];
					} ?>                    
                    <?php echo $infohsol[0]['prehsol_monto'] ?></P>
                    <P><b>Metodo de pago:</b> <?php echo $metodo_pago ?></P>   
                </div>
                
                <!--<div class="col-xs-2">
                    <table>
                        <tr><td><b>Metodo de pago</b></td></tr>
                        <tr><td>Transferencia</td></tr>
                        <tr><td>Cheque</td></tr>
                        <tr><td>Cheque de gerencia</td></tr>
                    </table>
                </div>-->

                <!--<div class="col-xs-2">
                    <table>
                        <tr><b>Condicion de pago</b></tr>
                        <tr>
                            <td><b>Anticipo</b></td>
                            <td><b>Transferencia</b></td>
                        </tr>
                        <tr>
                            <td>Texto libre %</td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>7 días</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>15 días</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>30 días</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>60 días</td>
                        </tr>                           
                    </table>
                </div>-->

                <?php else: ?>
                    <div class="col-xs-4">
                        
                    </div>
                <?php endif; ?>

                <div class="col-xs-4 text-right">
                    <address>
                    <strong>Solicitado por:</strong><br>
                        <?php echo $infohsol[0]['emp_nombre'];?><br>
                        <?php echo $infohsol[0]['cc_descripcion'];?><br>
                        <?php 
                        $cats = qCategoria($infohsol[0]['id_categoria']);
                        echo '<h3>'.$cats[0][0].'</h3>';
                        ?>
                    </address>
                </div>
            </div>
        </div>
    </div>
    

    <?php 
$is_trazabilidad = ($infohsol_stat!=null ? (count($infohsol_stat)>0 ? $infohsol_stat[0]!=null : false) : false);
            if($is_trazabilidad): ?>
<div class="row traza">
        <h3 class="title_traza">
            <i class="glyphicon glyphicon-check"></i>
            Trazabilidad de solicitud
        </h3>
        <?php 
        $estado=0;
        foreach($infohsol_stat as $state):
        $estado++;
        ?>
            <div class="col-sm-2 traza-item <?php echo (((int)$state['prehsol_stat']==10) ? 'stop' : 'ok')?>">
                <p>
                    <?php echo get_status($state['prehsol_stat'],$state['prehsol_stat_desc'])?><br/>
                </p>
                <label>
                    <i class="glyphicon glyphicon-user"></i>
                    <?php echo $state['prehsol_stat_usuario']?>
                </label>
                <span><?php echo $state['prehsol_stat_fecha']?> <?php echo $state['prehsol_stat_hora']?></span>
                <span class="info-status">
                    
                    <?php if($estado == 1){ ?>
                        <i class="glyphicon glyphicon-ok-circle"></i> Solicitado
                    <?php } else if($estado == 3){ ?>
                        <i class="glyphicon glyphicon-ok-circle"></i> Cotizado
                    <?php } else{ ?>
                        <?php if((int)$state['prehsol_stat']==10):?>
                            <i class="glyphicon glyphicon-remove"></i> rechazado
                        <?php elseif((int)$state['prehsol_stat']==20):?>
                            <i class="glyphicon glyphicon-arrow-left"></i> Devuelto
                        <?php else: ?>
                            <i class="glyphicon glyphicon-ok-circle"></i> Aprobado
                        <?php endif; ?>
                    <?php } ?>

                </span>
            </div>
        <?php endforeach;?>

        <!--<div class="col-sm-2 traza-item pause">
            <p>Aprobado Impresión.</p>
            <label>APROBADO IMPRESION</label>
            <span></span>
            <i class="glyphicon glyphicon-time"></i>
        </div>-->

    </div>
<?php 
else:

endif;
?>


    <div class="row">
        <div class="col-md-12">
            <form class="form-horizontal" id="frmApruebaCat" name="frmApruebaCat" method="post" action="?c=solc&a=aprbct">
              <div class="form-group">
                <label for="prehsol_aprobacion_categoria" class="col-sm-2 control-label">Observaciones</label>
                <div class="col-sm-10">
                  <textarea class="form-control" id="prehsol_aprobacion_categoria" name="prehsol_aprobacion_categoria"></textarea>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-offset-2 col-sm-7">
                    <input type="hidden" value="<?php echo $infohsol[0]['id_prehsol']; ?>" id="id_prehsol" name="id_prehsol">
                    <button type="submit" class="btn btn-success" id="btnEnviarAutoCat" name="btnEnviarAutoCat">AUTORIZAR<br>COMPRA</button>
                    <a href="?c=solc&a=naprbct&h=<?php echo $infohsol[0]['id_prehsol']; ?>&es=<?php echo $_GET['es']; ?>" class="btn btn-danger" id="negarCat" name="negarCat">DESISTIR<br>COMPRA</a>
                    <a href="?c=solc&a=gescat&es=<?php echo $infohsol[0]['id_categoria']; ?>" class="btn btn-default" id="backCat" name="backCat">VOLVER AL<br>LISTADO</a>
                    <?php if($infohsol[0]['gestion_nivel'] == 2) {?>
                    <a href="?c=solc&a=gesniv1&h=<?php echo $infohsol[0]['id_prehsol']; ?>" class="btn btn-warning" id="Niv1Cat" name="Niv1Cat">ENVIAR A<br>NIVEL SUPERIOR</a>
                    <?php } ?>
                </div>
                <div class="col-sm-3">
                    <a href="?c=solc&a=catDevolver&h=<?php echo $infohsol[0]['id_prehsol']; ?>&es=<?php echo $_GET['es']; ?>" class="btn btn-warning" id="devolverCat" name="devolverCat">
                        <b style="color:black;">Devolver<br>
                        Categoría</b>
                    </a>
                </div>
              </div>
            </form>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><strong>Detalle de solicitud</strong></h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table id="tablaPresol" class="table table-condensed">
                            <thead>
                                <tr>
                                    <td><strong>Descripcion</strong></td>
                                    <td class="text-center"><strong>Unidad</strong></td>
                                    <td class="text-center"><strong>Cantidad<br>Solicitada</strong></td>
                                    <?php if($infohsol[0]['gestion_nivel'] == 2) {?>
                                        <td>Acciones</td>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sumas = 0; 
                                foreach($detas as $deta) {
                                    $sumas = ($sumas + $deta['predsol_total']);
                                ?>
                                    <tr id="<?php echo $deta[0]; ?>">
                                        <td><?php echo $deta[11];?></td>
                                        <td class="text-center"><?php echo $deta[7];?></td>
                                        <td class="text-center"><?php echo $deta[4];?></td>
                                        <?php if($infohsol[0]['gestion_nivel'] == 2) {?>
                                            <td><a href="?c=solc&a=niv1e&id=<?php echo $deta[0]; ?>&ps=<?php echo $infohsol[0]['prehsol_numero']; ?>&es=<?php echo $infohsol[0]['id_empresa']; ?>&cs=<?php echo $infohsol[0]['id_cc']; ?>">ACTUALIZAR</a></td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <form id="negarForm" name="negarForm" method="post" action="">
    </form>

    <form id="devolverForm" name="devolverForm" method="post" action="">
    </form>
    
    <form id="niv1Form" name="niv1Form" method="post" action="">
    </form>
    
</div>
<script>
(function($) {
    $.fn.queued = function() {
        var self = this;
        var func = arguments[0];
        var args = [].slice.call(arguments, 1);
        return this.queue(function() {
            $.fn[func].apply(self, args).dequeue();
        });
    };
}(jQuery));
</script>
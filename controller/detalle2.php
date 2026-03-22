<link rel="stylesheet" type="text/css" href="css/style.css?v=<?php echo date('His')?>">
<h4 class="text-blue">Solicitud de Cheque No <?php echo str_pad($solc->id, 6,'0',STR_PAD_LEFT)?></h4>
<div class="container-fluid solc_print">
    <div class="row">
        <div class="col-sm-7">
            <span class="text-left"><?php echo $solc->empresa->nombre?> &nbsp; &nbsp; (NC)</span>
        </div>
        <div class="col-sm-5">
            <span class="text-right">Correlativo No.: <?php echo str_pad($solc->id, 6,'0',STR_PAD_LEFT);?></span>
        </div>  
    </div>
    <div class="row">
        <div class="col-sm-7">
            <span class="text-left">SOLICITUD DE CHEQUE &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; << <?php echo ($solc->negociable=='1' ? 'NEGOCIABLE' : 'NO NEGOCIABLE')?> >></span>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-7">
        </div>
        <div class="col-sm-5">
            <span class="text-right">Fecha: <?php echo Form::IntegerToDate($solc->fecha);?></span>
        </div>  
    </div>
    <br/>
    <div class="row">
        <div class="col-sm-12 line">
            <span class="text-left text-bold">
                Favor emitir Cheque a <br/>
                nombre de : &nbsp;
                <span class="text-underline text-left inline-block">
                    <?php echo $solc->nombre_beneficiario?>
                </span>
            </span>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-sm-12 line">
            <span class="text-left text-bold">
                Por valor : &nbsp; &nbsp;
                <span class="text-underline text-left inline-block">
                    <?php 
                    $monedas = ($solc->moneda=='$' ? 'DOLARES' : ($solc->moneda=='L' ? 'LEMPIRAS' : 'CORDOBAS'));
                    $moneda = ($solc->moneda=='$' ? 'DOLAR' : ($solc->moneda=='L' ? 'LEMPIRA' : 'CORDOBA'));
                    ?>
                    <?php echo Form::numtoletras($solc->valor_cheque,$monedas,$moneda)?>
                    <span class="text-aste">
                        <?php echo str_pad(" ",95,'*',STR_PAD_RIGHT)?>
                    </span>
                </span>
                <br/>
                &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                <span class="text-underline text-left inline-block" style="margin-left: -4px;min-width:10px !important;">
                    <?php echo Form::numtonumber($solc->valor_cheque,$solc->moneda)?>
                </span>
            </span>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-sm-12 line">
            <span class="text-left text-bold">
                En concepto de : &nbsp;
                <span class="text-underline text-left inline-block">
                    <?php echo $solc->concepto_pago?>
                </span>
            </span>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-sm-12 line">
            <span class="text-left text-bold">
                Depto. Solicitante : &nbsp;
                <span class="text-underline text-left inline-block" style="min-width:550px !important;">
                    <?php echo $solc->empresa->cc->nombre?>
                </span>
                Cco.:
                <span class="text-underline text-center inline-block" style="min-width:50px !important;">
                    <?php echo $solc->empresa->cc->codigo?>
                </span>
            </span>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-sm-12 line">
            <span class="text-left text-bold">
                Fecha M&aacute;xima de Pago : &nbsp;
                <span class="text-underline text-left inline-block" style="min-width: 90px !important;">
                    <?php echo $solc->fecha_max_pago?>
                </span>
            </span>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-sm-12 line">
            <span class="text-left text-bold">
                Observaciones : &nbsp;
                <span class="text-underline text-left inline-block">
                    <?php echo $solc->observacion?>
                </span>
            </span>
        </div>
    </div>
    <br/>
    <br/>
    <?php 
    $director5k=Help::autorizador_5k();
    //echo "<pre>";
    //print_r($solc);
    //echo "</pre>";
    if(!(($solc->avance!='N1' && $solc->status=='C') || ($solc->avance!='N2' && $solc->status=='C'))):
            $ttr=array();
            $pause=true;
            $ucategorizador="";
        foreach ($solc->trazabilidad as $traza){
                if($traza->nivel==1){
                    if($traza->status=='C'){
                        $ttr['P1']=array(
                            'title'     =>  'Solicitante Cco.',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'ok',
                            'icon'      =>  'ok-circle',
                            'info'      =>  'Completado',
                            'text'      =>  $traza->observacion
                        );
                    }
                }else if($traza->nivel==2){
                    if($traza->status=='C'){
                        $ttr['P2']=array(
                            'title'     =>  'Solicitante y Autorizador Cco.',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'ok',
                            'icon'      =>  'ok-circle',
                            'info'      =>  'Completado',
                            'text'      =>  $traza->observacion
                        );
                    }else if($traza->status=='R'){
                        $ttr['P2']=array(
                            'title'     =>  'Autorizador Cco.',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'pause',
                            'icon'      =>  'time',
                            'info'      =>  'En proceso',
                            'text'      =>  $traza->observacion
                        );
                    }else if($traza->status=='A'){
                        $ttr['P2']=array(
                            'title'     =>  'Autorizador Cco.',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'ok',
                            'icon'      =>  'ok-circle',
                            'info'      =>  'Autorizado',
                            'text'      =>  $traza->observacion
                        );
                    }else if($traza->status=='D'){
                        $ttr['P2']=array(
                            'title'     =>  'Autorizador Cco.',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'stop',
                            'icon'      =>  'remove',
                            'info'      =>  'Desistido',
                            'text'      =>  $traza->observacion
                        );
                        $pause=false;
                    }
                }else if($traza->nivel==3){
                    if($traza->status=='R' || $traza->status=='T'){
                        $ttr['P3']=array(
                            'title'     =>  'Autorizador c. gasto',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'pause',
                            'icon'      =>  'time',
                            'info'      =>  'En proceso',
                            'text'      =>  $traza->observacion
                        );
                        $ucategorizador=$traza->usuario;
                    }else if($traza->status=='D'){
                        $ttr['P3']=array(
                            'title'     =>  'Autorizador c. gasto',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'stop',
                            'icon'      =>  'remove',
                            'info'      =>  'Desistido',
                            'text'      =>  $traza->observacion
                        );
                        $pause=false;
                    }else if($traza->status=='A'){
                        $ttr['P3']=array(
                            'title'     =>  'Autorizador c. gasto',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'ok',
                            'icon'      =>  'ok-circle',
                            'info'      =>  'Autorizado',
                            'text'      =>  $traza->observacion
                        );
                    }
                    $ucategorizador=$traza->usuario;
                }else if($traza->nivel==4){
                    if($traza->status=='Y'){
                        $ttr['P6']=array(
                            'title'     =>  'Autorizador D. Ejecutiva',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'ok',
                            'icon'      =>  'ok-circle',
                            'info'      =>  'Autorizado',
                            'text'      =>  $traza->observacion
                        );
                    }else if($traza->status=='W'){
                        $ttr['P6']=array(
                            'title'     =>  'Autorizador D. Ejecutiva',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'stop',
                            'icon'      =>  'remove',
                            'info'      =>  'Desistido',
                            'text'      =>  $traza->observacion
                        );
                        $pause=false;
                    }else if($traza->status=='Z'){
                        $ttr['P6']=array(
                            'title'     =>  'Autorizador c. gasto',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'pause',
                            'icon'      =>  'time',
                            'info'      =>  'En Proceso',
                            'text'      =>  $traza->observacion
                        );
                    }
                }else if($traza->nivel==5){
                    if($traza->status=='R'){
                        $ttr['P4']=array(
                            'title'     =>  'Gestor c. gasto',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'pause',
                            'icon'      =>  'time',
                            'info'      =>  'En proceso',
                            'text'      =>  $traza->observacion
                        );
                    }else if($traza->status=='D'){
                        $ttr['P4']=array(
                            'title'     =>  'Gestor c. gasto',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'stop',
                            'icon'      =>  'remove',
                            'info'      =>  'Desistido',
                            'text'      =>  $traza->observacion
                        );
                        $pause=false;
                    }else if($traza->status=='A'){
                        $ttr['P4']=array(
                            'title'     =>  'Gestor c. gasto',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'ok',
                            'icon'      =>  'ok-circle',
                            'info'      =>  'Autorizado',
                            'text'      =>  $traza->observacion
                        );
                    }

                    if($ucategorizador==$traza->usuario){
                        $ttr['P4']['usuario']='APROBACION AUTOMATICA';
                    }
                }else if($traza->nivel==6){
                    if($traza->status=='R'){
                        $ttr['P5']=array(
                            'title'     =>  'Aprobado Impresi&oacute;n',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'pause',
                            'icon'      =>  'time',
                            'info'      =>  'En proceso',
                            'text'      =>  $traza->observacion
                        );
                    }else if($traza->status=='P'){
                        $ttr['P5']=array(
                            'title'     =>  'Aprobado Impresi&oacute;n',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'ok',
                            'icon'      =>  'ok-circle',
                            'info'      =>  'Impreso',
                            'text'      =>  $traza->observacion
                        );
                        $pause=false;
                    }else if($traza->status=='D'){
                        $ttr['P5']=array(
                            'title'     =>  'Aprobado Impresi&oacute;n',
                            'usuario'   =>  $traza->usuario,
                            'fecha'     =>  $traza->fecha,
                            'hora'      =>  $traza->hora,
                            'status'    =>  'stop',
                            'icon'      =>  'remove',
                            'info'      =>  'Desistido',
                            'text'      =>  $traza->observacion
                        );
                        $pause=false;
                    }
                }
            }?>
    <?php endif; ?>
    <?php 
    /*
    echo "<pre>";
    print_r($solc->trazabilidad);
    echo "</pre>";
    echo "<pre>";
    print_r($solc);
    echo "</pre>";
    */
    ?>
    <div class="row">
        <div class="col20">
            <span class="text-left text-bold" style="font-size: 12px;">
                <span class="text-underline text-left" style="font-weight: normal;">
                    <?php echo $solc->name_usuario?>
                </span>
                Solicitante
            </span>
        </div>
        <?php if($solc->name_usuario_autoriza_cc!='' && $solc->name_usuario!=$solc->name_usuario_autoriza_cc):?>
        <div class="col20">
            <span class="text-left text-bold" style="font-size: 12px;">
                <span class="text-underline text-left" style="font-weight: normal;">
                    <?php echo $solc->name_usuario_autoriza_cc?>
                </span>
                <?php if($solc->status=='D' && $solc->name_usuario_autoriza==''):?>
                Denegado
                <?php else: ?>
                Autorizado Cco.
                <?php endif; ?>
            </span>
        </div>
        <?php endif;?>
        
        <?php if($solc->name_usuario_autoriza!=''):?>
        <div class="col20">
            <span class="text-left text-bold" style="font-size: 12px;">
                <span class="text-underline text-left" style="font-weight: normal;">
                    <?php echo $solc->name_usuario_autoriza?>
                </span>
                <?php if($solc->status=='D' && $solc->avance!='N5'):?>
                Denegado
                <?php else: ?>
                Autorizado C. Gasto.
                <?php endif; ?>
            </span>
        </div>
        <?php endif;?>

        <?php if($solc->name_usuario_autoriza_5k!=''):?>
        <div class="col20">
            <span class="text-left text-bold" style="font-size: 12px;">
                <span class="text-underline text-left" style="font-weight: normal;">
                    <?php echo $solc->name_usuario_autoriza_5k?>
                </span>
                <?php if($solc->status=='W' && $solc->avance=='N3'):?>
                Denegado
                <?php else: ?>
                Autorizado D. Ejecutiva
                <?php endif; ?>
            </span>
        </div>
        <?php endif;?>

        <?php if($solc->name_usuario_autoriza_conta!='' && $solc->avance=='N5' && $solc->status=='D'):?>
        <div class="col20">
            <span class="text-left text-bold" style="font-size: 12px;">
                <span class="text-underline text-left" style="font-weight: normal;">
                    <?php echo $solc->name_usuario_autoriza_conta?>
                </span>
                Denegado Contabilidad
            </span>
        </div>
        <?php endif;?>
    </div>
    <br/>
    <br/>
    <?php if($solc->avance=='N4' && false):?>
    <div class="row">
        <div class="col-sm-12">
            Fecha y hora de re-impresi&oacute;n: 
            <?php echo date('d/m/Y H:i:s')?> &nbsp; &nbsp; &nbsp; &nbsp; 
            <!--Re-impresi&oacute;n No.: 
            <?php echo $solc->no_copia?>-->
        </div>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-sm-4">
            <?php 
                $rol_admin_sics = (isset($_SESSION['rol_admin_sics']) ? $_SESSION['rol_admin_sics'] :  0);
                if(!$rol_admin_sics){
                    $link="?c=solcheque&a=";
                }else{
                    $link="?c=menu&a=";
                }
                $return = (isset($_GET['return']) ? $_GET['return'] : null);
                $return = (!empty($return) ? $return : 'consultar' );
                $link.=$return;
                if($return!='consultarde'){
                    $director5k=false;
                }else{
                    $link = "?c=solcheque&a=consultarde";
                }
            ?>
            <a class="btn btn-default" href="<?php echo $link;?>">
                <i class="glyphicon glyphicon-arrow-left"></i>
                Regresar
            </a>
        </div>
    <?php if($perfil->rol=='N1' && !$director5k):?>
        <div class="col-sm-8 margin-right">
            <div>
                <?php if($solc->status=='C'):?>
                <button class="btn btn-danger btn-borrar">
                    Borrar
                    <i class="glyphicon glyphicon-trash"></i>
                </button>
                &nbsp;
                <a class="btn btn-primary" href="?c=solcheque&a=editar&s=<?php echo $solc->id?>">
                    Editar
                    <i class="glyphicon glyphicon-pencil"></i>
                </a>
                &nbsp;
                <button class="btn btn-success btn-send-n1">
                    Enviar para autorizaci&oacute;n
                    <i class="glyphicon glyphicon-envelope"></i>
                </button>
                <?php else: ?>
                <a href='http://intranet.impressa.com/report/?s=<?php echo $solc->id?>' target="_blank" class="btn btn-default">
                    &nbsp; Imprimir &nbsp;
                    <i class="glyphicon glyphicon-print"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif($perfil->rol=='N2' && !$director5k): ?>
        <div class="col-sm-8 margin-right">
            <div>
                <a href='http://intranet.impressa.com/report/?s=<?php echo $solc->id?>' target="_blank" class="btn btn-default">
                    &nbsp; Imprimir &nbsp;
                    <i class="glyphicon glyphicon-print"></i>
                </a>
                &nbsp; &nbsp;
                <?php if($solc->avance=='N2'):?>
                    <?php if($solc->status=='C'):?>
                        <button class="btn btn-danger btn-borrar">
                            Borrar
                            <i class="glyphicon glyphicon-trash"></i>
                        </button>
                        &nbsp;
                        <a class="btn btn-primary" href="?c=solcheque&a=editar&s=<?php echo $solc->id?>">
                            Editar
                            <i class="glyphicon glyphicon-pencil"></i>
                        </a>
                        &nbsp;
                        <button class="btn btn-success btn-send-n2">
                            Enviar para autorizaci&oacute;n
                            <i class="glyphicon glyphicon-envelope"></i>
                        </button>
                    <?php elseif($solc->status=='R'):?>
                        <a href='?c=solcheque&a=desistir&s=<?php echo $solc->id?>&return=<?php echo $return?>' class="btn btn-danger">
                            Desistir
                            <i class="glyphicon glyphicon-remove"></i>
                        </a>
                        &nbsp;
                        <a href='?c=solcheque&a=autorizar&s=<?php echo $solc->id?>&return=<?php echo $return?>' class="btn btn-success">
                            Autorizar
                            <i class="glyphicon glyphicon-ok"></i>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif($perfil->rol=='N3' && !$director5k): ?>
        <div class="col-sm-8 margin-right">
            <div>
                <a href='http://intranet.impressa.com/report/?s=<?php echo $solc->id?>' target="_blank" class="btn btn-default">
                    &nbsp; Imprimir &nbsp;
                    <i class="glyphicon glyphicon-print"></i>
                </a>
                &nbsp;
                <?php if($solc->avance=='N3'):?>
                    <?php if($solc->status=='R'):?>
                        <a href='?c=solcheque&a=desistir&s=<?php echo $solc->id?>&return=<?php echo $return?>' class="btn btn-danger">
                            Desistir
                            <i class="glyphicon glyphicon-remove"></i>
                        </a>
                        &nbsp;
                        <a href='?c=solcheque&a=gcategorizar&s=<?php echo $solc->id?>&return=<?php echo $return?>' class="btn btn-success">
                            Asignar categor&iacute;a
                            <i class="glyphicon glyphicon-pencil"></i>
                        </a>
                    <?php elseif($solc->status=='T'):?>
                        <a href='?c=solcheque&a=desistir&s=<?php echo $solc->id?>&return=<?php echo $return?>' class="btn btn-danger">
                            Desistir
                            <i class="glyphicon glyphicon-remove"></i>
                        </a>
                        &nbsp;
                        <a href='?c=solcheque&a=autorizar&s=<?php echo $solc->id?>&return=<?php echo $return?>' class="btn btn-success">
                            Autorizar
                            <i class="glyphicon glyphicon-ok"></i>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif($perfil->rol=='N4' && !$director5k): ?>
        <div class="col-sm-8 margin-right">
            <div>
                <a href='http://intranet.impressa.com/report/?s=<?php echo $solc->id?>' target="_blank" class="btn btn-default">
                    &nbsp; Imprimir &nbsp;
                    <i class="glyphicon glyphicon-print"></i>
                </a>
                &nbsp;
                <?php 
                    $is_catego = false;
                    $id_cate1 = $perfil->id_categoria;
					if(!empty($perfil->categoria)){
                        $id_cate1=$perfil->categoria[0]->id;
                    }
                    $id_cate2 = 0;
                    if(!empty($solc->categoria)){
                        $id_cate2 = $solc->categoria->id;
                        if($id_cate1==$id_cate2){
                            $is_catego=true;
                        }
                    }
                ?>
                <?php if($solc->avance=='N4'):?>
                    <?php if($solc->status=='R' && $is_catego):?>
                        <a href='?c=solcheque&a=desistir&s=<?php echo $solc->id?>&return=<?php echo $return?>' class="btn btn-danger">
                            Desistir
                            <i class="glyphicon glyphicon-remove"></i>
                        </a>
                         &nbsp;
                        <a href='?c=solcheque&a=devolver&s=<?php echo $solc->id?>&return=<?php echo $return?>' class="btn btn-warning">
                            &nbsp; Devolver &nbsp; &nbsp;
                            <i class="glyphicon glyphicon-repeat"></i>
                        </a>
                        &nbsp;
                        <a href='?c=solcheque&a=autorizar&s=<?php echo $solc->id?>&return=<?php echo $return?>' class="btn btn-success">
                            &nbsp; Autorizar &nbsp; &nbsp;
                            <i class="glyphicon glyphicon-ok"></i>
                        </a>
                    <?php elseif($solc->status=='T' && $is_catego):?>
                        <a href='?c=solcheque&a=desistir&s=<?php echo $solc->id?>&return=<?php echo $return?>' class="btn btn-danger">
                            Desistir
                            <i class="glyphicon glyphicon-remove"></i>
                        </a>
                        &nbsp;
                        <a href='?c=solcheque&a=autorizar&s=<?php echo $solc->id?>&return=<?php echo $return?>' class="btn btn-success">
                            Autorizar
                            <i class="glyphicon glyphicon-ok"></i>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif($perfil->rol=='N5' && !$director5k): ?>
        <div class="col-sm-8 margin-right">
            <div>
                <a href='http://intranet.impressa.com/report/?s=<?php echo $solc->id?>&m=1' target="_blank" class="btn btn-default">
                    &nbsp; Imprimir &nbsp;
                    <i class="glyphicon glyphicon-print"></i>
                </a>
                &nbsp;
                <?php if($solc->avance=='N5' && $solc->status=='R'):?>
                    <a href='?c=solcheque&a=desistir&s=<?php echo $solc->id?>&return=<?php echo $return?>' class="btn btn-danger">
                        &nbsp; Desistir &nbsp;
                        <i class="glyphicon glyphicon-remove"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif($director5k): ?>
        <div class="col-sm-8 margin-right">
            <div>
                <a href='http://intranet.impressa.com/report/?s=<?php echo $solc->id?>&m=1' target="_blank" class="btn btn-default">
                    &nbsp; Imprimir &nbsp;
                    <i class="glyphicon glyphicon-print"></i>
                </a>
                &nbsp;
                <?php if($solc->avance=='N3' && $solc->status=='Z'):?>
                    <a href='?c=solcheque&a=desistirde&s=<?php echo $solc->id?>&return=<?php echo $return?>' class="btn btn-danger">
                        Desistir
                        <i class="glyphicon glyphicon-remove"></i>
                    </a>
                    &nbsp;
                    <a href='?c=solcheque&a=autorizarde&s=<?php echo $solc->id?>&return=<?php echo $return?>' class="btn btn-success">
                        Autorizar
                        <i class="glyphicon glyphicon-ok"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif;?>
    </div>
    <?php if(!(($solc->avance!='N1' && $solc->status=='C') || ($solc->avance!='N2' && $solc->status=='C'))): ?>
    <div class='row traza'>
        <?php if(!empty($solc->trazabilidad)):?>
            <h3 class='title_traza'>
                <i class="glyphicon glyphicon-check"></i>
                Trazabilidad de solicitud
            </h3>
        <?php endif; ?>
        <?php $paso=(!empty($ttr['P1']) ? $ttr['P1'] : false); ?>
        <div class='col-sm-2 traza-item <?php echo ($paso ? $paso['status'] : '')?>'>
            <p><?php echo ($paso ? $paso['title'] : 'Solicitante Cco.')?></p>
            <label>
                <?php if($paso):?>
                    <i class="glyphicon glyphicon-user"></i>
                    <?php echo ($paso ? $paso['usuario'] : '')?>
                <?php else:?>
                    N/A
                <?php endif;?>
            </label>
            <span>
                <?php echo ($paso ? $paso['fecha'].' '.$paso['hora'] : '')?>
            </span>
            <?php if($paso):?>
            <span class='info-status'>
                <i class="glyphicon glyphicon-<?php echo $paso['icon']?>"></i>
                <?php echo ($paso ? $paso['info'] : '')?><br>
                <span><?php echo ($paso ? $paso['text'] : '')?></span>
            </span>
            <?php endif; ?>
        </div>
        <?php $paso=(!empty($ttr['P2']) ? $ttr['P2'] : false); ?>
        <div class='col-sm-2 traza-item <?php echo ($paso ? $paso['status'] : ($pause ? 'pause' : ''))?>'>
            <p><?php echo ($paso ? $paso['title'] : 'Autorizador Cco.')?></p>
            <label>
                <?php if($paso):?>
                    <i class="glyphicon glyphicon-user"></i>
                    <?php echo ($paso ? $paso['usuario'] : '')?>
                <?php elseif($pause): ?>
                    EN PROCESO
                <?php else: ?>
                    PENDIENTE
                <?php endif; ?>
            </label>
            <span>
                <?php echo ($paso ? $paso['fecha'].' '.$paso['hora'] : '')?>
            </span>
            <?php if($paso):?>
                <span class='info-status'>
                    <i class="glyphicon glyphicon-<?php echo $paso['icon']?>"></i>
                    <?php echo ($paso ? $paso['info'] : '')?><br>
                    <span><?php echo ($paso ? $paso['text'] : '')?></span>
                </span>
            <?php elseif($pause): ?>
                <i class="glyphicon glyphicon-time"></i>
                <?php $pause=false;?>
            <?php endif; ?>
        </div>

        <?php $paso=(!empty($ttr['P3']) ? $ttr['P3'] : false); ?>
        <div class='col-sm-2 traza-item <?php echo ($paso ? $paso['status'] : ($pause ? 'pause' : ''))?>'>
            <p><?php echo ($paso ? $paso['title'] : 'Categorizar c. gasto')?></p>
            <label>
                <?php if($paso):?>
                    <i class="glyphicon glyphicon-user"></i>
                    <?php echo ($paso ? $paso['usuario'] : '')?>
                <?php elseif($pause): ?>
                    EN PROCESO
                <?php else: ?>
                    PENDIENTE
                <?php endif; ?>
            </label>
            <span>
                <?php 
                    echo (
                            $paso 
                            ? $paso['fecha'].' '.$paso['hora'] 
                            : ''
                        );
                ?>
            </span>
            <?php if($paso):?>
            <span class='info-status'>
                <i class="glyphicon glyphicon-<?php echo $paso['icon']?>"></i>
                <?php echo ($paso ? $paso['info'] : '')?><br>
                <span>
                    <?php echo ($paso ? $paso['text'] : '')?>
                    <?php if($paso['status']=='pre'):?>
                        <?php if(trim($paso['text'])!=''):?>
                            <br/>
                        <?php endif;?>
                        <b style="font-size: 9px;">Pendiente de aprobar por Dirección Ejecutiva</b>
                    <?php endif; ?>
                </span>
            </span>
            <?php elseif($pause): ?>
            <span class='info-status'>
                <i class="glyphicon glyphicon-time" style="color: #222;display: block;text-align: center;margin: auto;margin-top: -6px;"></i>
                <span>
                    <?php if($solc->avance=='N3' && $solc->status=='R' && $solc->devuelta=='*'): ?>
                        <b style="font-size: 9px;">
                            Devuelto Gestor c. gasto.<br/>
                            <?php 
                                echo "<pre>";
                                print_r($paso);
                                echo "</pre>";
                            ?>
                        </b>
                    <?php endif; ?>
                </span>
                <?php $pause=false;?>
            </span>
            <?php endif; ?>
        </div>

        <?php $paso=(!empty($ttr['P4']) ? $ttr['P4'] : false); ?>
        <div class='col-sm-2 traza-item <?php echo ($paso ? $paso['status'] : ($pause ? 'pause' : ''))?>'>
            <p><?php echo ($paso ? $paso['title'] : 'Gestor c. gasto.')?></p>
            <label>
                <?php if($paso):?>
                    <i class="glyphicon glyphicon-user"></i>
                    <?php echo ($paso ? $paso['usuario'] : '')?>
                <?php elseif($pause): ?>
                    EN PROCESO <br/><br/>
                    <?php echo (!empty($solc->categoria) ? '('.$solc->categoria->id.') '.strtoupper($solc->categoria->nombre) : '')?>
                <?php else: ?>
                    PENDIENTE
                <?php endif; ?>
            </label>
            <span>
                <?php echo ($paso ? $paso['fecha'].' '.$paso['hora'] : '')?>
            </span>
            <?php if($paso):?>
            <span class='info-status'>
                <i class="glyphicon glyphicon-<?php echo $paso['icon']?>"></i>
                <?php echo ($paso ? $paso['info'] : '')?><br>
                <span><?php echo ($paso ? $paso['text'] : '')?></span>
            </span>
            <?php elseif($pause): ?>
            <i class="glyphicon glyphicon-time"></i>
            <?php $pause=false;?>
            <?php endif; ?>
        </div>


        <?php $paso=(!empty($ttr['P6']) ? $ttr['P6'] : false); 
            if(!empty($paso) || $solc->is5k==1):?>
        <div class='col-sm-2 traza-item <?php echo ($paso ? $paso['status'] : ($pause ? 'pause' : ''))?>'>
            <p><?php echo ($paso ? $paso['title'] : 'Autorizador D. Ejecutiva')?></p>
            <label>
                <?php if($paso):?>
                    <i class="glyphicon glyphicon-user"></i>
                    <?php echo ($paso ? $paso['usuario'] : '')?>
                <?php elseif($pause): ?>
                    EN PROCESO
                <?php else: ?>
                    PENDIENTE
                <?php endif; ?>
            </label>
            <span>
                <?php echo ($paso ? $paso['fecha'].' '.$paso['hora'] : '')?>
            </span>
            <?php if($paso):?>
            <span class='info-status'>
                <i class="glyphicon glyphicon-<?php echo $paso['icon']?>"></i>
                <?php echo ($paso ? $paso['info'] : '')?><br>
                <span>
                    <?php echo ($paso ? $paso['text'] : '')?>
                    <?php if($paso['status']=='pre'):?>
                        <?php if(trim($paso['text'])!=''):?>
                            <br/>
                        <?php endif;?>
                        <!--<b style="font-size: 9px;">Pendiente de aprobar por Dirección Ejecutiva</b>-->
                    <?php endif; ?>
                </span>
            </span>
            <?php elseif($pause): ?>
            <i class="glyphicon glyphicon-time"></i>
            <?php $pause=false;?>
            <?php endif; ?>
        </div>
        <?php endif;?>


        <?php $paso=(!empty($ttr['P5']) ? $ttr['P5'] : false); ?>
        <div class='col-sm-2 traza-item <?php echo ($paso ? $paso['status'] : ($pause ? 'pause' : ''))?>'>
            <p><?php echo ($paso ? $paso['title'] : 'Aprobado Impresi&oacute;n.')?></p>
            <label>
                <?php if($paso):?>
                    <i class="glyphicon glyphicon-user"></i>
                    <?php echo ($paso ? $paso['usuario'] : '')?>
                <?php elseif($pause): ?>
                    APROBADO IMPRESION
                <?php else: ?>
                    PENDIENTE
                <?php endif; ?>
            </label>
            <span>
                <?php echo ($paso ? $paso['fecha'].' '.$paso['hora'] : '')?>
            </span>
            <?php if($paso):?>
            <span class='info-status'>
                <i class="glyphicon glyphicon-<?php echo $paso['icon']?>"></i>
                <?php echo ($paso ? $paso['info'] : '')?><br>
                <span><?php echo ($paso ? $paso['text'] : '')?></span>
            </span>
            <?php elseif($pause): ?>
            <i class="glyphicon glyphicon-time"></i>
            <?php $pause=false;?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script type="text/javascript" src="js/js.js"></script>
<script>
	$(document).ready(function(){
            var id_solicitud=parseInt('<?php echo $solc->id?>');
            $('.btn-send-n1').click(function(){
                var ok = confirm('Enviar solicitud para autorizacion?');
                if(ok){
                    var request=request_json_id({
                                id: id_solicitud,
                                action: 'json.php?c=solcheque&a=json_solicitud_send_n1',
                                method: 'POST'
                            });
                    if(request!==undefined){
                        if(request.exito){
                            var rol='N1';
                            request_json_email({
                                id: id_solicitud,
                                state: 'send',
                                status: rol,
                                id_user: '<?php echo $perfil->id?>'
                            });
                            window.location='?c=solcheque&a=consultar';
                        }else{
                            alert(request.msj);
                        }
                    }
                }
                return false;
            });
            $('.btn-send-n2').click(function(){
                var ok = confirm('Enviar solicitud para autorizacion?');
                if(ok){
                    var request=request_json_id({
                                id: id_solicitud,
                                action: 'json.php?c=solcheque&a=json_solicitud_send_n2',
                                method: 'POST'
                            });
                    if(request!==undefined){
                        if(request.exito){
                            var rol='N2';
                            request_json_email({
                                id: id_solicitud,
                                state: 'send',
                                status: rol,
                                id_user: '<?php echo $perfil->id?>'
                            });
                            window.location='?c=solcheque&a=consultar';
                        }else{
                            alert(request.msj);
                        }
                    }
                }
                return false;
            });
            $('.btn-borrar').click(function(){
                var ok = confirm('Borrar solicitud de forma permanente?');
                if(ok){
                    var request=request_json_id({
                                id: id_solicitud,
                                action: 'json.php?c=solcheque&a=json_solicitud_borrar',
                                method: 'POST'
                            });
                    if(request!==undefined){
                        if(request.exito){
                            window.location='?c=solcheque&a=consultar';
                        }else{
                            alert(request.msj);
                        }
                    }
                }
                return false;
            });
	});  
</script>
<link rel="stylesheet" type="text/css" href="css/style.css?v=<?php echo date('His')?>">
<style>
    .footer,
    nav,
    .row.panal > img,
    .row.panal > .row > .col-md-3,
    .row.panal > h3,
    .row.panal:before{
        display: none !important;
    }
    .row.panal{
        background-image: none !important;
    }
    .solc_print{
        border:none;
    }
    .col-sm-2{
        width: 20%;
        float: left;
    }
    .col-sm-7{
        width: 60%;
        float: left;
    }
    .col-sm-4{
        width: 33%;
        float: left;
    }
	.col-sm-3{
        width: 24%;
        float: left;
    }
    .col-sm-4.col-sm-offset-2{
        margin-left: 30%;
    }
    .traza .traza-item{
        position:relative;
        text-align:left;
        margin:5px auto;
        border:1px #fff solid;
        margin-bottom: 55px;
        color:#888;
        display: block;
        height: 75px;
        border:1px #f0f0f0 solid;
        background-color: #ddd;
        width:150px;
    }
    
    .traza .traza-item{
        font-size: 10px;
    }
</style>
<div class="container-fluid solc_print">
	<div class="row">
        <div class="col-sm-7">
            <img src="http://intranet.impressa.com/intranetcambios/impressa-estilo/imagenes/img/ir.png"/>
        </div>
        <div class="col-sm-5">
            
        </div>  
    </div>
	<br/>
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
                    <?php echo Form::numtoletras($solc->valor_cheque)?>
                    <span class="text-aste">
                        <?php echo str_pad(" ",95,'*',STR_PAD_RIGHT)?>
                    </span>
                </span>
                <br/>
                &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                <span class="text-underline text-left inline-block" style="margin-left: -4px;min-width:10px !important;">
                    <?php echo Form::numtonumber($solc->valor_cheque)?>
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
    <?php if(!(($solc->avance!='N1' && $solc->status=='C') || ($solc->avance!='N2' && $solc->status=='C'))): ?>
    <?php 
            $ttr=array();
            $pause=true;
        ?>
    <?php foreach ($solc->trazabilidad as $traza){
                if($traza->avance=='N1'){
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
                }else if($traza->avance=='N2'){
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
                }else if($traza->avance=='N3'){
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
                }else if($traza->avance=='N4'){
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
                }else if($traza->avance=='N5'){
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
    
    <div class="row">
        <div class="col-sm-3">
            <span class="text-left text-bold" style="font-size: 12px;">
                <span class="text-underline text-left" style="font-weight: normal;">
                    <?php echo $solc->name_usuario?>
                </span>
                Solicitante
            </span>
        </div>
        <?php if($solc->name_usuario_autoriza_cc!='' && $solc->name_usuario!=$solc->name_usuario_autoriza_cc):?>
        <div class="col-sm-3">
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
        <div class="col-sm-3">
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
        <?php if($solc->name_usuario_autoriza_conta!='' && $solc->avance=='N5' && $solc->status=='D'):?>
        <div class="col-sm-3">
            <span class="text-left text-bold" style="font-size: 12px;">
                <span class="text-underline text-left" style="font-weight: normal;">
                    <?php echo $solc->name_usuario_autoriza_conta?>
                </span>
                Denegado Contabilidad
            </span>
        </div>
        <?php endif;?>
    </div>
    </div>
    <br/>
    <br/>
    <?php if($solc->avance=='N5'):?>
    <div class="row">
        <div class="col-sm-12">
            Fecha y hora de re-impresi&oacute;n: 
            <?php echo date('d/m/Y H:i:s')?> &nbsp; &nbsp; &nbsp; &nbsp; 
            Re-impresi&oacute;n No.: 
            <?php echo $solc->no_copia?>
        </div>
    </div>
    <?php endif; ?>
    
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
        <?php $paso=(!empty($ttr['P4']) ? $ttr['P4'] : false); ?>
        <div class='col-sm-2 traza-item <?php echo ($paso ? $paso['status'] : ($pause ? 'pause' : ''))?>'>
            <p><?php echo ($paso ? $paso['title'] : 'Gestor c. gasto.')?></p>
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

<script>
    window.onload=function(){
        window.print();
    };
</script>
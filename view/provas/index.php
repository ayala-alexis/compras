<link rel="stylesheet" type="text/css" href="css/sics.css?v=<?php echo date('His');?>"/>
<link href="https://fonts.googleapis.com/css?family=Lato:300,400,400i,700,700i" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet"/>
<style>
    .table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
        padding: 5px 8px;
        font-size:10px;
    }

    .table > tbody > tr > td .btn {
        padding: 6px 10px !important;
        min-height: auto !important;
    }
    .input-group-btn > .btn{
        min-height: 34px;
    }
    .form-group > label{
        font-size:14px;
        margin-top: 6px;
    }

    /* Buscador */
    .search-container {
            position: relative;
            width: 100%;
        }
        
        .searchInput {
            width: 100%;
            padding: 12px 20px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.3s;
        }
        
        .searchInput:focus {
            border-color: #4CAF50;
        }
        
        .resultsContainer {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        
        .result-item {
            padding: 12px 20px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }
        
        .result-item:hover {
            background-color: #f5f5f5;
        }
        
        .result-item.highlighted {
            background-color: #e8f5e8;
            color: #2e7d32;
        }
        
        .no-results {
            padding: 12px 20px;
            color: #999;
            font-style: italic;
        }
        
        .loading {
            padding: 12px 20px;
            color: #666;
            text-align: center;
        }
        
        .selected-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #4CAF50;
            display: none;
        }
        
        .selected-info h3 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .selected-info p {
            color: #666;
        }

        #modalCategoria .col-sm-9:has(input.searchInput),
        #modalCategoria .col-sm-9:has(input),
        #modalCategoria .col-sm-8:has(input.searchInput),
        #modalCategoria .col-sm-8:has(input){
            padding: 0 !important;
        }
        .result-item small{
            font-size:12px;
        }
         label[for] span{
            color:red;
         }


    /* Check Box */
     /* Checkbox funcional */
        .checkbox-label {
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            font-size: 18px;
            margin-bottom: 20px;
        }
        
        /* Checkbox real OCULTO */
        .real-checkbox {
            display: none;
        }
        
        /* Checkbox visual personalizado */
        .custom-checkbox {
            width: 24px;
            height: 24px;
            border: 2px solid #3498db;
            border-radius: 4px;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        /* Checkmark cuando está seleccionado */
        .custom-checkbox::after {
            content: "✓";
            color: white;
            font-size: 16px;
            display: none;
        }
        
        /* Cuando el checkbox REAL está marcado */
        .real-checkbox:checked + .custom-checkbox {
            background: #3498db;
        }
        
        .real-checkbox:checked + .custom-checkbox::after {
            display: block;
        }
        
        /* Estado actual */
        .status {
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 15px;
        }
        
        .yes {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .no {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .modal-title{
            font-size:17px;
            font-weight:bold;
            line-height:2;
            color: #175aa1;
        }
        button.close {
            font-size:20px !important;
        }
        .error-input{
            border-color:red;
        }

        .error {
            text-align: left !important;
            color: red;
            font-weight: bold;
            font-size: 13px;
            display: block;
        }
        span[data-lbl]{
            font-weight: normal;
            font-size: 14px;
            margin-left: 8px;
            padding-top: 2px;
            display: block;
        }
</style>
<h4 class="title">
	<i class="glyphicon glyphicon-th-list"></i> Mantenimiento de categorías para sol. cheques y compras.
</h4>
<form class="row rw-filter" id="filterForm" style="margin-bottom: 5px;" method="get" action="?c=<?php echo get_controller()?>&a=<?php echo get_action()?>">
    <div class="col-sm-1">
        <input type="hidden" name="c" value="<?php echo get_controller()?>"/>
        <input type="hidden" name="a" value="<?php echo get_action()?>"/>
        <label for="gcia">Gcia: </label>
        <select class="form-control input-sm" id="gcia" name="gcia" style="min-width: 100%;padding-left:3px;">
            <option value="">Todas</option>
            <?php if(!empty($data_gcia)):?>
                <?php foreach($data_gcia as $g):?>
                    <option value="<?php echo $g->gcia;?>" <?php if($gcia==$g->gcia) echo "selected";?>>
                        <?php echo mb_strtoupper($g->gcia);?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-sm-3">
        <br/>
        <button type="submit" class="btn btn-primary btn-update" style="vertical-align: text-top;min-height: 25px;margin-top:2px;">
            <i class="glyphicon glyphicon-search"></i> Consultar
        </button>

        <a href="#" class="btn btn-success btn-crear" style="vertical-align: text-top;min-height: 25px;margin-top:2px;">
            <i class="glyphicon glyphicon-plus-sign"></i> Agregar
        </a>
    </div>
</form>

<div class="row">

<table class="table tbl-kpi">
	<thead>
		<tr>
			<th style="min-width: 160px">Categoría</th>
			<th style="min-width: 145px;">Montos entre<br/>$0.00 y $200</th>
            <th style="min-width: 145px;">Montos entre<br/>$200.01 y $1000</th>
            <th style="min-width: 145px;">Montos<br/>mayor a $1000</th>
            <th style="min-width: 145px;">Montos mayor<br/>o igual $5000</th>
			<th style="min-width: 145px;">Creación</th>
            <th style="min-width: 80px;">Estado</th>
			<th style="min-width: 100px;"></th>
		</tr>
	</thead>
	<tbody>
		<?php if(!empty($data_prov)):
            $gcia = "";
		foreach ($data_prov as $prov):
			?>
            <?php if($prov->gcia!=$gcia):?>
                <tr>
                    <td colspan="8" style="padding: 1px 8px;background-color: #dee1e4;color: #175aa1;font-size:12px">
                        <b><?php echo $prov->gcia;?></b>
                    </td>
                </tr>
            <?php endif;?>
            <?php 
                $gcia = $prov->gcia;
            ?>
		<tr>
			<td><?php echo $prov->categoria;?></td>
            <td>
                <?php echo $prov->aprobador_1;?>
            </td>
            <td>
                <?php echo $prov->aprobador_2;?>
            </td>
            <td>
                <?php echo $prov->aprobador_3;?>
            </td>
            <td>
                <?php echo $prov->aprobador_5k;?>
            </td>
            <td>
                <?php echo $prov->usuario_crea;?><br/>
                <?php 
                    $fhour = str_pad($prov->hora_crea, 6, "0", STR_PAD_LEFT);
                    echo substr($prov->fecha_crea,6,2).'/'.
                         substr($prov->fecha_crea,4,2).'/'.
                         substr($prov->fecha_crea,0,4).' '.
                         substr($fhour,0,2).':'.
                         substr($fhour,2,2);
                ?>
            </td>
            <td style="vertical-align:inherit;">
                <?php if($prov->status):?>
                    <span class="label label-success" style="padding: 7px 6px;vertical-align: middle;text-align: center;font-size:10px;">
                        <i class="glyphicon glyphicon-ok-circle" style="vertical-align: text-bottom;"></i> Activa
                    </span>
                <?php else: ?>
                    <span class="label label-danger" style="padding: 7px 6px;vertical-align: middle;text-align: center;font-size:10px;">
                        <i class="glyphicon glyphicon-ban-circle" style="vertical-align: text-bottom;"></i> Inactiva
                    </span>
                <?php endif; ?>
            </td>
		
            <td>
                <a href="#" class="btn btn-success" data-action="edit" data-cat="<?php echo $prov->id;?>" title="Editar categoría: <?php echo $prov->categoria;?>">
                    <i class="glyphicon glyphicon-pencil"></i>
                </a>
                <a href="#" class="btn btn-danger" data-action="delete" data-cat="<?php echo $prov->id;?>" title="Borrar categoría: <?php echo $prov->categoria;?>" data-gcia = "<?php echo $prov->categoria;?>">
                    <i class="glyphicon glyphicon-trash"></i>
                </a>
            </td>
            
		</tr>
		<?php 
		endforeach;
        else:?>
            <tr class="tsol">
                <td colspan="6">
                    <h4 class="text-center">No se han encontrado datos.</h4>
                </td>
            </tr>
		<?php endif;?>
	</tbody>
</table>
</div>

<!-- Modal Agregar Categoría -->
<div class="modal fade" id="modalCategoriaAdd" tabindex="-1" role="dialog" aria-labelledby="modalCategoriaLabelAdd" aria-hidden="true">
	<div class="modal-dialog" role="document" style="min-width:auto;width: 90%; max-width: 850px;">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalCategoriaLabelAdd">
                    <i class="glyphicon glyphicon-pencil"></i>
                    Agregar nueva categoría

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </h5>
			</div>
			<div class="modal-body">
                <div class="form-group row">
                    <label for="gcia_add" class="col-sm-3 col-form-label">Gerencia <span>*</span> :</label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="gcia_add" id="gcia_add" placeholder="Gerencia" maxlength="20"/>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="categoria_add" class="col-sm-3 col-form-label">Categoria <span>*</span> :</label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="categoria_add" id="categoria_add" placeholder="Categoria" maxlength="50"/>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="aprobador_1_add" class="col-sm-3 col-form-label">Aprobador 1 <span>*</span> :</label>
                    <div class="col-sm-9">
                        <input type="text" id="aprobador_1_add" name="aprobador_1_add" class="form-control search-container searchInput" placeholder="Aprobador montos entre $0.00 y $200">
                        <div class="resultsContainer" data-id="aprobador_1_add"></div>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="aprobador_2_add" class="col-sm-3 col-form-label">Aprobador 2 <span>*</span> :</label>
                    <div class="col-sm-9">
                        <input type="text" id="aprobador_2_add" name="aprobador_2_add" class="form-control search-container searchInput" placeholder="Aprobador montos entre $200.01 y $1000">
                        <div class="resultsContainer" data-id="aprobador_2_add"></div>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="aprobador_3_add" class="col-sm-3 col-form-label">Aprobador 3 <span>*</span> :</label>
                    <div class="col-sm-9">
                        <input type="text" id="aprobador_3_add" name="aprobador_3_add" class="form-control search-container searchInput" placeholder="Aprobador montos mayor a $1000">
                        <div class="resultsContainer" data-id="aprobador_3_add"></div>
                    </div>
                </div>
                <div class="form-group row">
                    
                    <label for="requiere_5k_add" class="col-sm-3 col-form-label">Requiere aprobador $5K? </label>
                    <div class="col-sm-1">
                        <label class="checkbox-label">
                            <input type="checkbox" class="real-checkbox" id="requiere_5k_add" name="requiere_5k_add" />
                            <span class="custom-checkbox"></span>
                        </label>
                    </div>
                    <div class="col-sm-8">
                        <input style="display:none;" type="text" id="aprobador_5k_add" name="aprobador_5k_add" class="form-control search-container searchInput" placeholder="Aprobador montos mayor o igual $5000">
                        <div class="resultsContainer" data-id="aprobador_5k_add"></div>
                    </div>
                </div>

                <div class="form-group row">
                    
                    <label for="requiere_recepcion_add" class="col-sm-3 col-form-label">Requiere recepción? </label>
                    <div class="col-sm-1">
                        <label class="checkbox-label">
                            <input type="checkbox" class="real-checkbox" id="requiere_recepcion_add" name="requiere_recepcion_add" />
                            <span class="custom-checkbox"></span>
                        </label>
                    </div>
                </div>

                <div class="form-group row">
                    
                    <label for="estado_add" class="col-sm-3 col-form-label">Estado: </label>
                    <div class="col-sm-1">
                        <label class="checkbox-label">
                            <input type="checkbox" class="real-checkbox" id="estado_add" name="estado_add" />
                            <span class="custom-checkbox"></span>
                        </label>
                    </div>
                    <div class="col-sm-6">
                        <span data-lbl="estado_add"></span>
                    </div>
                </div>
                
                <div class="selectedInfo selected-info">
                    <h3>Registro seleccionado:</h3>
                    <p class="selectedContent"></p>
                </div>
			</div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-sm-6">
                        <span class="error error-add"></span>
                    </div>
                    <div class="col-sm-6">
                        <a href="#" class="btn btn-default" data-dismiss="modal" aria-label="Close">
                           <i class="glyphicon glyphicon-arrow-left"></i> Cancelar
                        </a>
                        <a href="#" class="btn btn-success btn-add">
                           <i class="glyphicon glyphicon-plus-sign"></i> Agregar
                        </a>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>

<!-- Modal Editar Categoría -->
<div class="modal fade" id="modalCategoriaEdit" tabindex="-1" role="dialog" aria-labelledby="modalCategoriaLabelEdit" aria-hidden="true">
	<div class="modal-dialog" role="document" style="min-width:auto;width: 90%; max-width: 850px;">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalCategoriaLabelEdit">
                    <i class="glyphicon glyphicon-pencil"></i>
                    Editar categoría

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </h5>
			</div>
			<div class="modal-body">
                <div class="form-group row">
                    <label for="gcia_edit" class="col-sm-3 col-form-label">Gerencia <span>*</span> :</label>
                    <div class="col-sm-9">
                        <input type="hidden" id="categoria_id_edit" name="categoria_id_edit" value=""/>
                        <input class="form-control" type="text" name="gcia_edit" id="gcia_edit" placeholder="Gerencia" maxlength="20"/>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="categoria_edit" class="col-sm-3 col-form-label">Categoria <span>*</span> :</label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="categoria_edit" id="categoria_edit" placeholder="Categoria" maxlength="50"/>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="aprobador_1_edit" class="col-sm-3 col-form-label">Aprobador 1 <span>*</span> :</label>
                    <div class="col-sm-9">
                        <input type="text" id="aprobador_1_edit" name="aprobador_1_edit" class="form-control search-container searchInput" placeholder="Aprobador montos entre $0.00 y $200">
                        <div class="resultsContainer" data-id="aprobador_1_edit"></div>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="aprobador_2_edit" class="col-sm-3 col-form-label">Aprobador 2 <span>*</span> :</label>
                    <div class="col-sm-9">
                        <input type="text" id="aprobador_2_edit" name="aprobador_2_edit" class="form-control search-container searchInput" placeholder="Aprobador montos entre $200.01 y $1000">
                        <div class="resultsContainer" data-id="aprobador_2_edit"></div>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="aprobador_3_edit" class="col-sm-3 col-form-label">Aprobador 3 <span>*</span> :</label>
                    <div class="col-sm-9">
                        <input type="text" id="aprobador_3_edit" name="aprobador_3_edit" class="form-control search-container searchInput" placeholder="Aprobador montos mayor a $1000">
                        <div class="resultsContainer" data-id="aprobador_3_edit"></div>
                    </div>
                </div>
                <div class="form-group row">
                    
                    <label for="requiere_5k_edit" class="col-sm-3 col-form-label">Requiere aprobador $5K? </label>
                    <div class="col-sm-1">
                        <label class="checkbox-label">
                            <input type="checkbox" class="real-checkbox" id="requiere_5k_edit" name="requiere_5k_edit" />
                            <span class="custom-checkbox"></span>
                        </label>
                    </div>
                    <div class="col-sm-8">
                        <input style="display:none;" type="text" id="aprobador_5k_edit" name="aprobador_5k_edit" class="form-control search-container searchInput" placeholder="Aprobador montos mayor o igual $5000">
                        <div class="resultsContainer" data-id="aprobador_5k_edit"></div>
                    </div>
                </div>

                <div class="form-group row">
                    
                    <label for="requiere_recepcion_edit" class="col-sm-3 col-form-label">Requiere recepción? </label>
                    <div class="col-sm-1">
                        <label class="checkbox-label">
                            <input type="checkbox" class="real-checkbox" id="requiere_recepcion_edit" name="requiere_recepcion_edit" />
                            <span class="custom-checkbox"></span>
                        </label>
                    </div>
                </div>

                <div class="form-group row">
                    
                    <label for="estado_edit" class="col-sm-3 col-form-label">Estado: </label>
                    <div class="col-sm-1">
                        <label class="checkbox-label">
                            <input type="checkbox" class="real-checkbox" id="estado_edit" name="estado_edit" />
                            <span class="custom-checkbox"></span>
                        </label>
                    </div>
                    <div class="col-sm-6">
                        <span data-lbl="estado_edit"></span>
                    </div>
                </div>
                
                <div class="selectedInfo selected-info">
                    <h3>Registro seleccionado:</h3>
                    <p class="selectedContent"></p>
                </div>
			</div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-sm-6">
                        <span class="error error-edit"></span>
                    </div>
                    <div class="col-sm-6">
                        <a href="#" class="btn btn-default" data-dismiss="modal" aria-label="Close">
                           <i class="glyphicon glyphicon-arrow-left"></i> Cancelar
                        </a>
                        <a href="#" class="btn btn-success btn-edit">
                           <i class="glyphicon glyphicon-pencil"></i> Actualizar
                        </a>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>



<?php 
$link_action = "?c=".get_controller()."&a=".get_action()."&gcia=".$gcia;
echo Help::paginator_other($paginador,$link_action);
?>

<script type="text/javascript" src="js/js.js?v=<?php echo date('His')?>"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script type="text/javascript">
    const chkRequiere5K = document.getElementById('requiere_5k_add');
    const txtAprobador5k = document.getElementById('aprobador_5k_add');
    
    // Escuchar cambios en el checkbox REAL
    chkRequiere5K.addEventListener('change', function() {
        if (this.checked) {
            txtAprobador5k.style.display = "block";
            txtAprobador5k.focus();
        } else {
            txtAprobador5k.style.display = "none";
        }
    });


	$(document).ready(function(){

        //Se valida los checkbox 
        $('.real-checkbox').change(function(){
            var checkboxId = $(this).attr('id');
            var chk = $('[data-lbl=' + checkboxId + ']');
            if(chk!==null && chk!==undefined){
                if($(this).is(':checked')){
                    $('[data-lbl=' + checkboxId + ']').text("Activo/Visible");
                }else{
                    $('[data-lbl=' + checkboxId + ']').text("Inactivo/No Visible");
                }
            }
        });

        $('a.btn-crear').click(function(){
            $('#modalCategoriaAdd').modal('show');
            $('[name=estado_add]').prop('checked', true);
            $('[data-lbl=estado_add]').text("Activo/Visible");
            return false;
        });

        //Evento Eliminar Categoría
        $('a[data-action=delete]').click(function(){
            var cat_id = $(this).data('cat');
            var cat_name = $(this).data('gcia');

            var existe_cat = request_json_id({ 
                action: "json.php?c=provas&a=json_existe_categoria_cheque", 
                method: "POST", 
                id: cat_id 
            });

            if(existe_cat!==null && existe_cat!==undefined){
                if(existe_cat.existe){
                    swal({
                        title: "No se puede eliminar categoría!",
                        text: "La categoría \"" + cat_name + "\" está asociada a una o más solicitud(es) de cheque.",
                        icon: "warning",
                        buttons: false,
                        timer: 10000
                    });
                }else{
                    swal({
                        title: "¿Está seguro de eliminar categoría?",
                        text: "Una vez eliminada, no podrá recuperar esta categoría \"" + cat_name + "\"",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            var res_delete = request_json_id({ 
                                action: "json.php?c=provas&a=json_eliminar_categoria_cheque", 
                                method: "POST", 
                                id: cat_id 
                            });

                            if(res_delete!==null && res_delete!==undefined){
                                if(res_delete.exito){
                                    swal("¡Categoría eliminada exitosamente!", {
                                        icon: "success",
                                        timer: 10000
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                }else{
                                    swal({
                                        title: "Error!",
                                        text: "No es posible eliminar la categoría en este momento. Intente nuevamente más tarde.",
                                        icon: "error"
                                    });
                                }
                            }
                        }
                    });
                }
            }

        });

        //Evento Editar Categoría
        $('a[data-action=edit]').click(function(){
            var cat_id = $(this).data('cat');

            $('[name=categoria_id_edit]').val(cat_id);
            // Cargar datos de la categoría y llenar el formulario
            // Aquí puedes hacer una llamada AJAX para obtener los datos si es necesario

            $.ajax({
                url: 'json.php?c=provas&a=json_categoria',
                method: 'GET',
                data: { cat: cat_id },
                dataType: 'json',
                success: function(data) {
                    console.log(data);
                    if (data!==null && data!==undefined){
                        var categoria = data;
                        $('[name=gcia_edit]').val(categoria.gcia);
                        $('[name=categoria_edit]').val(categoria.categoria);
                        $('[name=aprobador_1_edit]').val(categoria.aprobador_1);
                        $('[name=aprobador_2_edit]').val(categoria.aprobador_2);
                        $('[name=aprobador_3_edit]').val(categoria.aprobador_3);
                        
                        if(categoria.aprobador_5k && categoria.aprobador_5k.trim() !== ''){
                            $('[name=requiere_5k_edit]').prop('checked', true);
                            $('[name=aprobador_5k_edit]').show().val(categoria.aprobador_5k);
                        }else{
                            $('[name=requiere_5k_edit]').prop('checked', false);
                            $('[name=aprobador_5k_edit]').hide().val('');
                        }

                        if(categoria.requiere_recepcion == 1){
                            $('[name=requiere_recepcion_edit]').prop('checked', true);
                        }else{
                            $('[name=requiere_recepcion_edit]').prop('checked', false);
                        }
                        if(categoria.status == 1){
                            $('[name=estado_edit]').prop('checked', true);
                            $('[data-lbl=estado_edit]').text("Activo/Visible");
                        }else{
                            $('[name=estado_edit]').prop('checked', false);
                            $('[data-lbl=estado_edit]').text("Inactivo/No Visible");
                        }
                        $('#modalCategoriaEdit').modal('show');
                    }
                },
                error: function() {
                    $resultsContainer.html('<div class="no-results">Error al buscar</div>').show();
                }
            });

            return false;
        });

        $('.btn-add').click(function(){
            var error_add = false;
            var foco = false;
            $('span.error-add').text('');
            // Agregar validaciones
            $('[name=gcia_add]').removeClass('error-input');
            if($('[name=gcia_add]').val().trim()===''){
                $('[name=gcia_add]').addClass('error-input');
                error_add = true;
                if(!foco){
                    $('[name=gcia_add]').focus();
                    foco = true;
                }
            }

            $('[name=categoria_add]').removeClass('error-input');
            if($('[name=categoria_add]').val().trim()===''){
                $('[name=categoria_add]').addClass('error-input');
                error_add = true;
                if(!foco){
                    $('[name=categoria_add]').focus();
                    foco = true;
                }
            }
            //Si todos los campos de aprobadores están vacíos, entonces aprueba cada gerencia
            //de lo contrario se validan los campos
            $('[name=aprobador_1_add]').removeClass('error-input');
            $('[name=aprobador_2_add]').removeClass('error-input');
            $('[name=aprobador_3_add]').removeClass('error-input');
            if($('[name=aprobador_1_add]').val().trim()!=='' || $('[name=aprobador_2_add]').val().trim()!=='' || $('[name=aprobador_3_add]').val().trim()!==''){
                if($('[name=aprobador_1_add]').val().trim()===''){
                    $('[name=aprobador_1_add]').addClass('error-input');
                    error_add = true;
                    if(!foco){
                        $('[name=aprobador_1_add]').focus();
                        foco = true;
                    }
                }

                if($('[name=aprobador_2_add]').val().trim()===''){
                    $('[name=aprobador_2_add]').addClass('error-input');
                    error_add = true;
                    if(!foco){
                        $('[name=aprobador_2_add]').focus();
                        foco = true;
                    }
                }

                if($('[name=aprobador_3_add]').val().trim()===''){
                    $('[name=aprobador_3_add]').addClass('error-input');
                    error_add = true;
                    if(!foco){
                        $('[name=aprobador_3_add]').focus();
                        foco = true;
                    }
                }
            }
            $('[name=aprobador_5k_add]').removeClass('error-input');
            if($('[name=requiere_5k_add]').prop('checked')){
                if($('[name=aprobador_5k_add]').val()===''){
                    $('[name=aprobador_5k_add]').addClass('error-input');
                    error_add = true;
                    if(!foco){
                        $('[name=aprobador_5k_add]').focus();
                        foco = true;
                    }
                }
            }

            if(!error_add){
                
                var rs = request_json_categoria_add(
                    {
                        action: "json.php?c=provas&a=json_crear_categoria",
                        method: "POST",
                        gerencia: $('[name=gcia_add]').val(),
                        categoria: $('[name=categoria_add]').val(),
                        aprobador1: $('[name=aprobador_1_add]').val(),
                        aprobador2: $('[name=aprobador_2_add]').val(),
                        aprobador3: $('[name=aprobador_3_add]').val(),
                        requiere_5k: ($('[name=requiere_5k_add]').prop('checked') ? 1 : 0),
                        aprobador_5k: ($('[name=requiere_5k_add]').prop('checked') ? $('[name=aprobador_5k_add]').val() : ''),
                        requiere_recepcion: ($('[name=requiere_recepcion_add]').prop('checked') ? 1 : 0),
                        estado: ($('[name=estado_add]').prop('checked') ? 1 : 0)
                    },function(r){
                        if(r.exito){
                            swal(
                            {
                                title: "Completo!",
                                text: r.msg,
                                icon: "success",
                                buttons: ["Agregar otra categoría",true]
                            }).then((ok) => {
                                if (ok) {
                                    window.location.reload();
                                } else {
                                    $('[name=gcia_add]').val('');
                                    $('[name=categoria_add]').val('');
                                    $('[name=aprobador_1_add]').val('');
                                    $('[name=aprobador_2_add]').val('');
                                    $('[name=aprobador_3_add]').val('');
                                    $('[name=requiere_5k_add]').prop('checked',false);
                                    $('[name=aprobador_5k_add]').val('');
                                    $('[name=requiere_recepcion_add]').prop('checked',false);
                                    $('[name=estado_add]').prop('checked',true);
                                    txtAprobador5k.style.display = "none";
                                }
                            });

                        }else{
                            swal({
                                title: "Error!",
                                text: r.msg,
                                icon: "error"
                            });
                        }
                    }
                );
                
            }else{
                $('span.error-add').text("Por favor complete los campos requeridos.");
            }
            return false;
        });

        $('.btn-edit').click(function(){
            var error_add = false;
            var foco = false;
            $('span.error-edit').text('');
            // Agregar validaciones
            $('[name=gcia_edit]').removeClass('error-input');
            if($('[name=gcia_edit]').val().trim()===''){
                $('[name=gcia_edit]').addClass('error-input');
                error_add = true;
                $('[name=gcia_edit]').focus();
                foco = true;
            }

            $('[name=categoria_edit]').removeClass('error-input');
            if($('[name=categoria_edit]').val().trim()===''){
                $('[name=categoria_edit]').addClass('error-input');
                error_add = true;
                if(!foco){
                    $('[name=categoria_edit]').focus();
                    foco = true;
                }
            }
            //Si todos los campos de aprobadores están vacíos, no se valida nada
            //la aprobación será por cada gerencia
            $('[name=aprobador_1_edit]').removeClass('error-input');
            $('[name=aprobador_2_edit]').removeClass('error-input');
            $('[name=aprobador_3_edit]').removeClass('error-input');
            if($('[name=aprobador_1_edit]').val().trim()!=='' || $('[name=aprobador_2_edit]').val().trim()!=='' || $('[name=aprobador_3_edit]').val().trim()!==''){
                if($('[name=aprobador_1_edit]').val().trim()===''){
                    $('[name=aprobador_1_edit]').addClass('error-input');
                    error_add = true;
                    if(!foco){
                        $('[name=aprobador_1_edit]').focus();
                        foco = true;
                    }
                }

                if($('[name=aprobador_2_edit]').val().trim()===''){
                    $('[name=aprobador_2_edit]').addClass('error-input');
                    error_add = true;
                    if(!foco){
                        $('[name=aprobador_2_edit]').focus();
                        foco = true;
                    }
                }

                if($('[name=aprobador_3_edit]').val().trim()===''){
                    $('[name=aprobador_3_edit]').addClass('error-input');
                    error_add = true;
                    if(!foco){
                        $('[name=aprobador_3_edit]').focus();
                        foco = true;
                    }
                }
            }

            $('[name=aprobador_5k_edit]').removeClass('error-input');
            if($('[name=requiere_5k_edit]').prop('checked')){
                if($('[name=aprobador_5k_edit]').val()===''){
                    $('[name=aprobador_5k_edit]').addClass('error-input');
                    error_add = true;
                    if(!foco){
                        $('[name=aprobador_5k_edit]').focus();
                        foco = true;
                    }
                }
            }

            if(!error_add){
                
                var rs = request_json_categoria_edit(
                    {
                        action: "json.php?c=provas&a=json_editar_categoria",
                        method: "POST",
                        id: $('[name=categoria_id_edit]').val(),
                        gerencia: $('[name=gcia_edit]').val(),
                        categoria: $('[name=categoria_edit]').val(),
                        aprobador1: $('[name=aprobador_1_edit]').val(),
                        aprobador2: $('[name=aprobador_2_edit]').val(),
                        aprobador3: $('[name=aprobador_3_edit]').val(),
                        requiere_5k: ($('[name=requiere_5k_edit]').prop('checked') ? 1 : 0),
                        aprobador_5k: ($('[name=requiere_5k_edit]').prop('checked') ? $('[name=aprobador_5k_edit]').val() : ''),
                        requiere_recepcion: ($('[name=requiere_recepcion_edit]').prop('checked') ? 1 : 0),
                        estado: ($('[name=estado_edit]').prop('checked') ? 1 : 0)
                    },function(r){
                        if(r.exito){
                            swal(
                            {
                                title: "Completo!",
                                text: r.msg,
                                icon: "success",
                                timer: 10000
                           }).then(() => {
                                window.location.reload();
                            });

                        }else{
                            swal({
                                title: "Error!",
                                text: r.msg,
                                icon: "error"
                            });
                        }
                    }
                );
                
            }else{
                $('span.error-edit').text("Por favor complete los campos requeridos.");
            }
            return false;
        });


        // Variables globales
            let searchTimer;
            let currentResults = [];
            let selectedIndex = -1;
            let lastSearchTerm = '';
            
            // Elementos del DOM
            let $searchInput = $('.searchInput');
            let $resultsContainer = $('.resultsContainer');
            const $selectedInfo = $('.selectedInfo');
            const $selectedContent = $('.selectedContent');
            
            // Evento de entrada de texto
            $searchInput.on('input', function() {
                const searchTerm = $(this).val().trim();
                $resultsContainer = $('[data-id=' + $(this).attr('id') + '].resultsContainer');
                $searchInput = $(this);
                // Limpiar timer anterior
                clearTimeout(searchTimer);
                
                // Si el término de búsqueda está vacío, ocultar resultados
                if (searchTerm === '') {
                    $resultsContainer.hide().empty();
                    currentResults = [];
                    selectedIndex = -1;
                    return;
                }
                
                // Si el término no ha cambiado, no hacer nada
                if (searchTerm === lastSearchTerm) {
                    return;
                }
                
                lastSearchTerm = searchTerm;
                
                // Configurar timer para evitar muchas peticiones
                searchTimer = setTimeout(function() {
                    performSearch(searchTerm);
                }, 300);
            });
            
            // Eventos de teclado
            $searchInput.on('keydown', function(e) {
                // Ocultar resultados si se presiona Escape
                if (e.keyCode === 27) { // ESC
                    $resultsContainer.hide();
                    selectedIndex = -1;
                    return;
                }
                
                // Si no hay resultados, no hacer nada
                if (currentResults.length === 0 || !$resultsContainer.is(':visible')) {
                    return;
                }
                
                switch(e.keyCode) {
                    case 38: // Flecha arriba
                        e.preventDefault();
                        navigateResults(-1);
                        break;
                        
                    case 40: // Flecha abajo
                        e.preventDefault();
                        navigateResults(1);
                        break;
                        
                    case 13: // Enter
                        e.preventDefault();
                        selectCurrentResult();
                        break;
                }
            });
            
            // Cerrar resultados al hacer clic fuera
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-container').length) {
                    $resultsContainer.hide();
                }
            });
            
            // Función para realizar la búsqueda AJAX
            function performSearch(term) {
                // Mostrar indicador de carga
                $resultsContainer.html('<div class="loading">Buscando...</div>').show();
                
                // Aquí normalmente harías una petición AJAX a un servidor
                // Para este ejemplo, simularemos una respuesta con datos de prueba
                
                // Simular retardo de red
                /*setTimeout(function() {
                    // Datos de ejemplo (en un caso real vendrían del servidor)
                    const mockData = [
                        { id: 1, name: 'Producto Alpha', category: 'Electrónica', price: '$299' },
                        { id: 2, name: 'Producto Beta', category: 'Hogar', price: '$149' },
                        { id: 3, name: 'Producto Gamma', category: 'Electrónica', price: '$499' },
                        { id: 4, name: 'Producto Delta', category: 'Oficina', price: '$89' },
                        { id: 5, name: 'Producto Épsilon', category: 'Electrónica', price: '$199' },
                        { id: 6, name: 'Producto Zeta', category: 'Hogar', price: '$79' },
                        { id: 7, name: term + ' Plus', category: 'Nuevo', price: '$399' }
                    ].filter(item => 
                        item.name.toLowerCase().includes(term.toLowerCase()) || 
                        item.category.toLowerCase().includes(term.toLowerCase())
                    );
                    
                    // Actualizar resultados globales
                    currentResults = mockData;
                    
                    // Mostrar resultados
                    displayResults(mockData, term);
                    
                    // Si hay resultados, seleccionar el primero
                    if (mockData.length > 0) {
                        selectedIndex = 0;
                        highlightResult();
                    }
                    
                }, 500);*/
                
                // Código para una petición AJAX real (descomentar y adaptar)
                
                $.ajax({
                    url: 'json.php?c=provas&a=json_usuarios',
                    method: 'GET',
                    data: { q: term },
                    dataType: 'json',
                    success: function(data) {
                        currentResults = data;
                        displayResults(data, term);
                        
                        if (data.length > 0) {
                            selectedIndex = 0;
                            highlightResult();
                        }
                    },
                    error: function() {
                        $resultsContainer.html('<div class="no-results">Error al buscar</div>').show();
                    }
                });
                
            }
            
            // Función para mostrar resultados
            function displayResults(results, searchTerm) {
                if (results.length === 0) {
                    $resultsContainer.html('<div class="no-results">No se encontraron resultados para "' + searchTerm + '"</div>').show();
                    return;
                }
                
                let html = '';
                
                results.forEach(function(item, index) {
                    html += '<div class="result-item" data-index="' + index + '">' +
                            '<strong>' + highlightText(item.usuario, searchTerm) + '</strong><br>' +
                            '<small>Nombre: ' + highlightText(item.name,searchTerm) + ' | Correo: ' + highlightText(item.email,searchTerm) + '</small>' +
                            '</div>';
                });
                
                $resultsContainer.html(html).show();
                
                // Agregar evento click a los resultados
                $resultsContainer.find('.result-item').on('click', function() {
                    const index = parseInt($(this).data('index'));
                    selectResult(index);
                });
            }
            
            // Función para resaltar texto coincidente
            function highlightText(text, searchTerm) {
                if (!searchTerm) return text;
                
                const regex = new RegExp('(' + searchTerm + ')', 'gi');
                return text.replace(regex, '<span style="background-color: #ffeb3b">$1</span>');
            }
            
            // Función para navegar entre resultados
            function navigateResults(direction) {
                // Remover highlight actual
                $resultsContainer.find('.result-item').removeClass('highlighted');
                
                // Calcular nuevo índice
                selectedIndex += direction;
                
                // Asegurar que esté dentro de los límites
                if (selectedIndex < 0) {
                    selectedIndex = currentResults.length - 1;
                } else if (selectedIndex >= currentResults.length) {
                    selectedIndex = 0;
                }
                
                // Aplicar highlight
                highlightResult();
                
                // Asegurar que el elemento esté visible en el scroll
                const $selectedItem = $resultsContainer.find('.result-item[data-index="' + selectedIndex + '"]');
                const containerHeight = $resultsContainer.height();
                const scrollTop = $resultsContainer.scrollTop();
                const itemOffset = $selectedItem.position().top;
                const itemHeight = $selectedItem.outerHeight();
                
                if (itemOffset < 0) {
                    // Elemento arriba del área visible
                    $resultsContainer.scrollTop(scrollTop + itemOffset);
                } else if (itemOffset + itemHeight > containerHeight) {
                    // Elemento abajo del área visible
                    $resultsContainer.scrollTop(scrollTop + (itemOffset + itemHeight - containerHeight));
                }
            }
            
            // Función para resaltar el resultado actual
            function highlightResult() {
                $resultsContainer.find('.result-item[data-index="' + selectedIndex + '"]')
                    .addClass('highlighted')
                    .siblings()
                    .removeClass('highlighted');
            }
            
            // Función para seleccionar el resultado actual
            function selectCurrentResult() {
                if (selectedIndex >= 0 && selectedIndex < currentResults.length) {
                    selectResult(selectedIndex);
                }
            }
            
            // Función para seleccionar un resultado por índice
            function selectResult(index) {
                const selectedItem = currentResults[index];
                
                // Actualizar el input con el nombre seleccionado
                $searchInput.val(selectedItem.usuario);
                
                // Ocultar resultados
                $resultsContainer.hide();
                
                // Mostrar información del seleccionado
                /*$selectedContent.html(
                    '<strong>ID:</strong> ' + selectedItem.id + '<br>' +
                    '<strong>Nombre:</strong> ' + selectedItem.name + '<br>' +
                    '<strong>Categoría:</strong> ' + selectedItem.category + '<br>' +
                    '<strong>Precio:</strong> ' + selectedItem.price
                );*/
                
                //$selectedInfo.show();
                
                // Aquí puedes agregar más lógica para procesar la selección
                console.log('Registro seleccionado:', selectedItem);
                
                // También puedes enviar el ID seleccionado a otro proceso
                // $.post('/api/select-item', { id: selectedItem.id });
                
                // Resetear índices
                selectedIndex = -1;
            }
	});
</script>
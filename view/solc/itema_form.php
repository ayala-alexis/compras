<?php
//print_r($detas); 
//print_r($infohsol);
//print_r($prods);
?>
<style>
@media(max-width:991px){
#product-title
{
  text-align:center;
}
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

	$("#frmAddItemSolc").validate();

	$('#frmAuto').submit(function(e){
		$('body').removeClass('loaded');
	    return;
	    e.preventDefault();
	});
});
</script>

<script src="js/tinymce/tinymce.min.js"></script>
<script>
	tinymce.init({ 
		selector:'textarea',
		menubar: false,
		skin: "lightgray",
		statusbar: false,
		theme: 'modern',
		setup: function(ed){
            ed.on("blur", function () {
                $("#" + ed.id).val(tinyMCE.activeEditor.getContent());
            });
            ed.on("KeyDown", function(evt) {
                //if ( $(ed.getBody()).text().length+1 > ed.getParam('max_chars')){
                if ( $(ed.getBody()).text().length+1 > $(tinyMCE.get(tinyMCE.activeEditor.id).getElement()).attr('max_chars')){
                	document.getElementById("character_count").innerHTML = "Maximo de letras permitido: 250";
                	$('#character_count').closest('.form-group').addClass('has-error');
                	$('#character_count').addClass("text-error");
                	if(evt.keyCode != 8 && evt.keyCode != 46) {
                    	evt.preventDefault();
                    	evt.stopPropagation();
                    	return false;
                    }
                } else {
                	document.getElementById("character_count").innerHTML = "Letras : " + $(ed.getBody()).text().length;
                	$('#character_count').addClass("text-success");
                }
            });
        }
	});
</script>
<div class="container-fluid">
	<!-- Start Page Loading -->
    <div id="loader-wrapper">
    	<h1>Espere...</h1>
        <div id="loader">
        </div>        
        <div class="loader-section section-left"></div>
        <div class="loader-section section-right"></div>
    </div>
    
    <div class="row well">
    	<fieldset class="scheduler-border">
    	<legend class="scheduler-border">Crear Producto para Solicitud de Compra</legend>
		<form id="frmAddItemSolc" name="frmAddItemSolc" class="form-horizontal" method="post" action="?c=solc&a=trabajoia&id=5&pd=<?php echo $_REQUEST['pd']; ?>">
			<div class="form-group">
		    	<label class="col-sm-2 control-label"># Solicitud</label>
		    	<div class="col-sm-10">
		      		<p class="form-control-static"><?php echo $infohsol['prehsol_numero_sol']; ?></p>
		    	</div>
		  	</div>
		  	<div class="form-group">
		    	<label for="sublinea" class="col-sm-2 control-label">Sublinea</label>
		    	<div class="col-sm-10">
		    		<select class="form-control" name="sublinea" id="sublinea" required>
		    		<?php
		    		foreach ($prods as $prod){
						echo '<option value="'.$prod['sl_linea'].'~'.$prod['sl_sublinea'].'~'.$prod['gas_tit_codigo'].'~'.$prod['gas_det_codigo'].'">('.$prod['sl_linea'].$prod['sl_sublinea'].') '.$prod['sl_descripcion'].'</option>';
					} 
		    		?>
		    		</select>
		    	</div>
		  	</div>
		  	<div class="form-group">
		    	<label for="codigo" class="col-sm-2 control-label">Codigo</label>
		    	<div class="col-sm-10">
		      		<input type="text" class="form-control" name="codigo" id="codigo" placeholder="Codigo" required>
		    	</div>
		  	</div>
		  	<div class="form-group">
		  		<label for="descripcion" class="col-sm-2 control-label">Descripcion</label>
		    	<div class="col-sm-10">
		      		<input type="text" class="form-control" name="descripcion" id="descripcion" placeholder="Descripcion" required>
		    	</div>
		    </div>
		    <div class="form-group">
				<label class="control-label col-md-2" for="observacion">Observaciones</label>
				<div class="col-md-10">
					<textarea class="form-control input-sm" rows="5" name="observacion" id="observacion" placeholder="digite observaciones" required>
						<?php
						if(empty($detas['predsol_observacion'])) {
							echo $detas['predsol_descripcion'];
						} else {
							echo $detas['predsol_observacion'];
						} 
						?>
					</textarea>
				</div>
			</div>
		  	<div class="form-group">
		    	<div class="col-sm-offset-2 col-sm-10">
		    		<input type="hidden" name="ps" id="ps" value="<?php echo $infohsol['prehsol_numero']; ?>">
					<input type="hidden" name="cs" id="cs" value="<?php echo $infohsol['id_cc']; ?>">
					<input type="hidden" name="es" id="es" value="<?php echo $infohsol['id_empresa']; ?>">
		      		<button type="submit" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i>&nbsp;Agregar Producto Nuevo</button>
		      		<a href="?c=solc&a=trabajoi&id=5&pd=<?php echo $_REQUEST['pd']; ?>" class="btn btn-danger"><i class="glyphicon glyphicon-log-out"></i>&nbsp;Cancelar</a>
		    	</div>
		  	</div>
		</form>
		</fieldset>
	</div>
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
    }
}(jQuery));
</script>
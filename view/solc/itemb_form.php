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
    	<?php //print_r($detas); ?>
    	<fieldset class="scheduler-border">
    	<legend class="scheduler-border">Trabajar Producto de Solicitud de Compra</legend>
		<form id="frmAddItemSolc" name="frmAddItemSolc" class="form-horizontal" method="post" action="?c=solc&a=trabajogcia&pd=<?php echo $_REQUEST['id']; ?>">
			<div class="form-group">
		    	<label class="col-sm-2 control-label"># Solicitud</label>
		    	<div class="col-sm-10">
		      		<p class="form-control-static"><?php echo $infohsol[0]['prehsol_numero_sol']; ?></p>
		    	</div>
		  	</div>
		  	<?php foreach ($detas as $det) {
		  		//print_r($det);
		  		$vowels = array("<P>", "</P>", "&NBSP;");
		  		$descr = str_replace($vowels, "", $det['predsol_descripcion']); 
		  	?>
		  	<div class="form-group">
		  		<label for="cantidad" class="col-sm-2 control-label">Cantidad</label>
		    	<div class="col-sm-10">
		      		<input type="text" class="form-control" name="cantidad" id="cantidad" value="<?php echo $det['predsol_cantidad']; ?>" required>
		    	</div>
		    </div>
		  	<div class="form-group">
		  		<label for="descripcion" class="col-sm-2 control-label">Descripcion</label>
		    	<div class="col-sm-10">
		      		<input type="text" class="form-control" name="descripcion" id="descripcion" value="<?php echo trim($descr); ?>" required>
		    	</div>
		    </div>
		    <?php if(!empty($det['predsol_cantidad2'])) { ?>
		    <div class="form-group">
				<label class="control-label col-md-2" for="cantidad2">Cantidad solicitud</label>
				<div class="col-md-10">
					<p class="form-control-static">
						<?php
							echo $det['predsol_cantidad2'];
						?>
					</p>
				</div>
			</div>
			<?php } ?>
			<?php if(!empty($det['predsol_descripcion2'])) { ?>
		    <div class="form-group">
				<label class="control-label col-md-2" for="descripcion2">Descripcion solicitud</label>
				<div class="col-md-10">
					<p class="form-control-static">
						<?php
							echo $det['predsol_descripcion2'];
						?>
					</p>
				</div>
			</div>
			<?php } ?>
		  	<div class="form-group">
		    	<div class="col-sm-offset-2 col-sm-10">
		    		<input type="hidden" name="ps" id="ps" value="<?php echo $infohsol[0]['prehsol_numero']; ?>">
					<input type="hidden" name="cs" id="cs" value="<?php echo $infohsol[0]['id_cc']; ?>">
					<input type="hidden" name="es" id="es" value="<?php echo $infohsol[0]['id_empresa']; ?>">
					<input type="hidden" name="des" id="des" value="<?php echo $det['predsol_descripcion']; ?>">
					<input type="hidden" name="can" id="can" value="<?php echo $det['predsol_cantidad']; ?>">
		      		<button type="submit" class="btn btn-success"><i class="glyphicon glyphicon-edit"></i>&nbsp;ACTUALIZAR PRODUCTO</button>
		      		<a href="?c=solc&a=trabajogc&ps=<?php echo $_REQUEST['ps']; ?>&es=<?php echo $_REQUEST['es']; ?>&cs=<?php echo $_REQUEST['cs']; ?>" class="btn btn-default"><i class="glyphicon glyphicon-log-out"></i>&nbsp;CANCELAR</a>
		    	</div>
		  	</div>
		  	<?php } ?>
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
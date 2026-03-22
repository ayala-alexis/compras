<script type="text/javascript">
$(function(){
	$('button').click(function(){
		$('tbody tr').each(function(){
			var node = $(this);
			myUrl = location.protocol + "//" + location.host + '/sics/post/echo_oc.php';
			var codig_a = node.find("td").eq(1).text();
			var codig_n = node.find("td").eq(3).text();
			$.ajaxq ("queue", {
		        url: myUrl,
		        type: 'post',
		        dataType: 'json',
		        data: {
		            date: (new Date()).getTime(),
		            codig_a: codig_a,
		            codig_n: codig_n
		        },
		        beforeSend: function() {
		            node.addClass("warning");
		        },
		        error: function() {
		            node.removeClass("warning").addClass("danger");
		            node.find("td").eq(1).find("b").html('<i class="glyphicon glyphicon-remove"></i>');
		        },
		        complete: function(jqXHR, textStatus) {
			        var x = $.parseJSON(jqXHR.responseText);
		            node.removeClass("warning").removeClass("danger").addClass("success").find("td").eq(1).find("b").html('<i class="glyphicon glyphicon-ok"></i>');
					node.find('td').fadeOut('slow', function(){
						node.remove();
					});
		        },
		        success: function(response) {
		            //$(node).addClass("success");
		        }
		    });
		});
	});
});
</script>
<h4 class="text-blue">Listado de Proveedores a Cambiar O.C. - Empresa 06</h4>
<p>
	<button class="btn btn-lg btn-danger">Iniciar Proceso</button>
</p>
<table class="table table-hovered table-striped">
	<thead>
		<tr>
			<th>#</th>
			<th>Codigo</th>
			<th>Nombre</th>
			<th>Nuevo Codigo</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$i=1;
	foreach ($provs as $prov) {
	?>
		<tr>
			<td><?php echo $i++; ?>
			<td><?php echo $prov[0]; ?><b></b><i></i></td>
			<td><?php echo $prov[2]; ?></td>
			<td><?php echo $prov[17]; ?></td>
		</tr>
	<?php
	}
	?>
	</tbody>
</table>
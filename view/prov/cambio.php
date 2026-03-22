<script type="text/javascript">
$(function(){
	$('button').click(function(){
		$('tbody tr').each(function(){
			var node = $(this);
			var delay = 1;
            var destination = node.offset().top;
			myUrl = location.protocol + "//" + location.host + '/sics/post/echo.php';
			var codig_a = node.find("td").eq(0).text();
			var id_emp = node.find("td").eq(2).text();
			var codig_n = node.find("td").eq(3).text();
			$.ajaxq ("queue", {
		        url: myUrl,
		        type: 'post',
		        dataType: 'json',
		        data: {
		            date: (new Date()).getTime(),
		            delay: delay,
		            codig_a: codig_a,
		            codig_n: codig_n,
		            id_emp: id_emp
		        },
		        beforeSend: function() {
		            node.addClass("warning");
		        },
		        error: function() {
		            node.removeClass("warning").addClass("danger");
		            node.find("td").eq(0).find("b").html('<i class="glyphicon glyphicon-remove"></i>');
		        },
		        complete: function(jqXHR, textStatus) {
			        var x = $.parseJSON(jqXHR.responseText);
		            node.removeClass("warning").removeClass("danger").addClass("success").find("td").eq(0).find("b").html('<i class="glyphicon glyphicon-ok"></i>');
		            //$("html:not(:animated),body:not(:animated)").animate({ scrollTop: destination }, 500 );
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
<h4 class="text-blue">Listado de Proveedores a Cambiar</h4>
<p>
	<button class="btn btn-lg btn-danger">Iniciar Proceso</button>
</p>
<table class="table table-hovered table-striped">
	<thead>
		<tr>
			<th>Codigo</th>
			<th>Empresa</th>
			<th>ID Empresa</th>
			<th>Nuevo Codigo</th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ($provs as $prov) {
	?>
		<tr>
			<td><?php echo $prov[3]; ?><b></b><i></i></td>
			<td><?php echo $prov[1]; ?></td>
			<td><?php echo $prov[2]; ?></td>
			<td><?php echo $prov[0]; ?></td>
		</tr>
	<?php
	}
	?>
	</tbody>
</table>
<?php //print_r($ges); ?>
<?php //print_r($usr); ?>
<link
	href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css"
	rel='stylesheet' type='text/css'>
<style>
.panel-table .panel-body {
	padding: 10px 0;
}

.panel-table .panel-body .table-bordered {
	border-style: none;
	margin: 0;
}

.panel-table .panel-body .table-bordered>thead>tr>th:first-of-type {
	text-align: center;
	width: 100px;
}

.panel-table .panel-body .table-bordered>thead>tr>th:last-of-type,.panel-table .panel-body .table-bordered>tbody>tr>td:last-of-type
	{
	border-right: 0px;
}

.panel-table .panel-body .table-bordered>thead>tr>th:first-of-type,.panel-table .panel-body .table-bordered>tbody>tr>td:first-of-type
	{
	border-left: 0px;
}

.panel-table .panel-body .table-bordered>tbody>tr:first-of-type>td {
	border-bottom: 0px;
}

.panel-table .panel-body .table-bordered>thead>tr:first-of-type>th {
	border-top: 0px;
}

.panel-table .panel-footer .pagination {
	margin: 0;
}

/*
used to vertically center elements, may need modification if you're not using default sizes.
*/
.panel-table .panel-footer .col {
	line-height: 34px;
	height: 34px;
}

.panel-table .panel-heading .col h3 {
	line-height: 30px;
	height: 30px;
}

.panel-table .panel-body .table-bordered>tbody>tr>td {
	line-height: 34px;
}
</style>
<div class="container-fluid">
	<div class="row">
		<h3>Mantenimiento de usuarios gestores</h3>
		<p>Usuarios con acceso para autorizacion de solicitudes de compra.</p>
		<div class="col-md-12">
			<div class="panel panel-default panel-table">
				<div class="panel-heading">
                	<div class="row">
                  		<div class="col col-xs-6">
                    		<h3 class="panel-title">Listado de usuarios</h3>
                  		</div>
                  		<div class="col col-xs-6 text-right">
                    		<button type="button" id="btnadd" name="btnadd" class="btn btn-sm btn-primary btn-create" data-toggle="modal" data-target="#add-modal">Nuevo acceso</button>
                  		</div>
                	</div>
              	</div>
				<div class="panel-body">
					<table id="gestoresTabla" name="gestoresTabla" class="table table-striped table-bordered table-list">
						<thead>
							<tr>
								<th class="text-center"><em class="fa fa-cog"></em></th>
								<th>ID</th>
								<th>Usuario</th>
								<th>Nombre</th>
							</tr>
						</thead>
						<tbody>
								<?php
								if(is_array($ges)) {
								foreach($ges as $gestor) { 
								?>
								<tr>
									<td align="center">
										<a href="?c=solc&a=gesc&id=4&usr=<?php echo $gestor['id_usuario']; ?>" class="btn btn-default"><em class="fa fa-home"></em></a> 
										<a href="?c=solc&a=ges&id=4&usr=<?php echo $gestor['id_usuario']; ?>&del" class="btn btn-danger"><em class="fa fa-trash"></em></a>
									</td>
									<td><?php echo $gestor['id_usuario']; ?></td>
									<td><?php echo $gestor['usr_usuario']; ?></td>
									<td><?php echo $gestor['usr_nombre']; ?></td>
								</tr>
								<?php } }?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="add-modal" tabindex="-1" role="dialog" aria-labelledby="add-modallabel"> 
	<div class="modal-dialog" role="document"> 
		<div class="modal-content"> 
			<form class="form-horizontal" id="add-form" action="?c=solc&a=ges&id=4&add" method="post"> 
				<div class="modal-header"> 
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button> 
					<h4 class="modal-title" id="add-modal-label">Agregar nuevo acceso</h4> 
				</div> 
				<div class="modal-body"> 
					<div class="form-group"> 
						<label for="usuario" class="col-sm-3 control-label">Seleccione usuario :</label> 
						<div class="col-sm-9"> 
							<select class="form-control" id="usuario" name="usuario" placeholder="Usuario" required>
							<?php 
							foreach ($usr as $usuario){
								echo '<option value="'.$usuario['id_usuario'].'">'.$usuario['usr_usuario'].' - '.$usuario['usr_nombre'].'</option>';
							}
							?>
							</select> 
						</div> 
					</div> 
				</div> 
				<div class="modal-footer"> 
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button> 
					<button type="submit" class="btn btn-primary">Agregar</button> 
				</div> 
			</form> 
		</div> 
	</div> 
</div>
<script>
$(document).ready(function(){
    $('#gestoresTabla').DataTable({
    	"language": {
            "lengthMenu": "Mostrando _MENU_ registros por pagina",
            "zeroRecords": "Sin registros",
            "info": "Mostrando pagina _PAGE_ de _PAGES_",
            "infoEmpty": "No se encontraron registros",
            "infoFiltered": "(filtrada a partir de _MAX_ registros totales)"
        }
    });

    $("p.alert").fadeIn('slow').animate({opacity: 1.0}, 2500).effect("pulsate", { times: 2 }, 800).fadeOut('slow'); 
});
</script>
<!-- 
<div class="container-fluid">
    <div class="row">
    <h3>Mantenimiento de usuarios gestores</h3>
    <p>Usuarios con acceso para autorizacion de solicitudes de compra.</p>
        <div class="col-md-12">
            <div class="panel panel-default panel-table">
              <div class="panel-heading">
                <div class="row">
                  <div class="col col-xs-6">
                    <h3 class="panel-title">Listado de usuarios</h3>
                  </div>
                  <div class="col col-xs-6 text-right">
                    <button type="button" class="btn btn-sm btn-primary btn-create">Nuevo acceso</button>
                  </div>
                </div>
              </div>
              <div class="panel-body">
                <table class="table table-striped table-bordered table-list">
                  <thead>
                    <tr>
                        <th><em class="fa fa-cog"></em></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                    </tr> 
                  </thead>
                  <tbody>
                          <tr>
                            <td align="center">
                              <a class="btn btn-default"><em class="fa fa-pencil"></em></a>
                              <a class="btn btn-danger"><em class="fa fa-trash"></em></a>
                            </td>
                            <td>1</td>
                            <td>John Doe</td>
                            <td>johndoe@example.com</td>
                          </tr>
                        </tbody>
                </table>
            
              </div>
              <div class="panel-footer">
                <div class="row">
                  <div class="col col-xs-4">Page 1 of 5
                  </div>
                  <div class="col col-xs-8">
                    <ul class="pagination hidden-xs pull-right">
                      <li><a href="#">1</a></li>
                      <li><a href="#">2</a></li>
                      <li><a href="#">3</a></li>
                      <li><a href="#">4</a></li>
                      <li><a href="#">5</a></li>
                    </ul>
                    <ul class="pagination visible-xs pull-right">
                        <li><a href="#">«</a></li>
                        <li><a href="#">»</a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

</div></div></div>
-->
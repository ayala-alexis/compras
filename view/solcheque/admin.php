<link rel="stylesheet" type="text/css" href="css/style.css">
<div class="row">
    <div class="col-sm-10">
        <h5 class="text-blue">
            <i class="glyphicon glyphicon-user"></i>
            Listado de Usuarios Gcia. Finanzas y Contabilidad
        </h5>
    </div>
    <div class="col-sm-2">
        <a href="?c=solcheque&a=createuser" class="btn btn-success" style="margin: 3px 0;">
            <i class="glyphicon glyphicon-plus"></i> Agregar usuario
        </a>
    </div>
</div>
<table class="table table-condensed tablesorter" style="font-size: 12px;">
	<thead>
            <tr>
                <th>Usuario</th>
                <th>Nombre Usuario</th>
                <th>Correo</th>
                <th>Rol</th>
            </tr>
	</thead>
	<tbody>
            <?php foreach($data as $user): ?>
            <tr style="font-size: 11px;">
                <td><?php echo $user->usuario;?></td>
                <td><?php echo $user->nombre?></td>
                <td><?php echo $user->correo?></td>
                <td><?php echo ($user->nivel=='N3' ? 'Gcia. Financiera' : ($user->nivel=='N5' ? 'Contabilidad': 'Dirección Ejecutiva'))?></td>
                <td>
                    <a href="?c=solcheque&a=admindet&id=<?php echo $user->id_usuario?>" class="btn btn-sm btn-default" title="Visualizar Usuario">
                        <i class="glyphicon glyphicon-search"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
	</tbody>
</table>
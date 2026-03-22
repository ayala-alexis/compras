<?php
if ($_SESSION['req'] == '0') {
	echo '<label class="alert alert-error">lo sentimos se ha revocado el acceso a esta opcion, consulte al administrador.</label>';
} else {
?>
<form class="form-inline" action="?c=req&a=tracole&id=6" method="post">
	<label>empresa</label>
	<select id="empresa" name="empresa" class="selectpicker span3">
	<?php foreach ($emps as $emp) { ?>
	<?php
	if(isset($_POST['empresa']) && $_POST['empresa'] == $emp['id_empresa']) {
	  	$empresa = $emp['emp_nombre'];
	}
	?>
	<option value="<?php echo $emp['id_empresa']; ?>"><?php echo $emp['emp_nombre']; ?></option>
	<?php } ?>
	</select>
	<button type="submit" class="btn btn-primary">trabajar colectadas</button>
</form>
<?php if(isset($trabajar) && count($trabajar) <= 0) echo '<span class="alert alert-warning">no existen registros para trabajar</span>'; ?>
<?php if(isset($trabajar) && count($trabajar) > 0 ) { ?>
<table class="table table-condensed">
	<thead>
  		<tr>
    		<th>Centro de Costo</th>
    		<th>Fecha</th>
    		<th>Numero</th>
  		</tr>
  	</thead>
  	<tbody>
<?php } ?>
<?php foreach($trabajar as $colecta){ ?>
	<tr>
  		<td><label class="label label-info"><?php echo '(<span id="cecosto">'.$colecta['cc_codigo'].'</span>)'.$colecta['cc_descripcion']; ?></label></td>
  		<td><label class="label label-info"><?php echo $colecta['prehreq_fecha']; ?></label></td>
    	<td><label class="label label-info"><?php echo $colecta['prehreq_numero_req']; ?></label></td>
    </tr>
    <tr>
    	<td colspan="3">
    	<table class="table table-condensed">
    	<tr>
    		<th>codigo</th>
    		<th>descripcion</th>
    		<th>cantidad</th>
    		<th>Precio de Lista</th>
    	</tr>
    <?php foreach ($colecta['Det'] as $det) { ?>
    	<tr>
   			<td><?php echo $det['prod_codigo']; ?></td>
   			<td><?php echo $det['predreq_descripcion']; ?></td>
   			<td><?php echo $det['predreq_cantidad']; ?></td>
   			<td>
   				<label class="radio inline"><input type="radio" name="optionsRadio~<?php echo $colecta['cc_codigo'].'~'.$colecta['prehreq_numero_req'].'~'.$det['prod_codigo']; ?>" id="radio1~<?php echo $det['prod_codigo']; ?>" <?php if($det['prod_prov01'] == 0) echo 'class="disabled" disabled=disabled'; ?>><?php echo $det['prod_prov01']; ?></label>
   				<label class="radio inline"><input type="radio" name="optionsRadio~<?php echo $colecta['cc_codigo'].'~'.$colecta['prehreq_numero_req'].'~'.$det['prod_codigo']; ?>" id="radio2~<?php echo $det['prod_codigo']; ?>" <?php if($det['prod_prov02'] == 0) echo 'class="disabled" disabled=disabled'; ?>><?php echo $det['prod_prov02']; ?></label>
   				<label class="radio inline"><input type="radio" name="optionsRadio~<?php echo $colecta['cc_codigo'].'~'.$colecta['prehreq_numero_req'].'~'.$det['prod_codigo']; ?>" id="radio3~<?php echo $det['prod_codigo']; ?>" <?php if($det['prod_prov03'] == 0) echo 'class="disabled" disabled=disabled'; ?>><?php echo $det['prod_prov03']; ?></label>
   				<label class="radio inline"><input type="radio" name="optionsRadio~<?php echo $colecta['cc_codigo'].'~'.$colecta['prehreq_numero_req'].'~'.$det['prod_codigo']; ?>" id="radio4~<?php echo $det['prod_codigo']; ?>" <?php if($det['prod_prov04'] == 0) echo 'class="disabled" disabled=disabled'; ?>><?php echo $det['prod_prov04']; ?></label>
   				<label class="radio inline"><input type="radio" name="optionsRadio~<?php echo $colecta['cc_codigo'].'~'.$colecta['prehreq_numero_req'].'~'.$det['prod_codigo']; ?>" id="radio5~<?php echo $det['prod_codigo']; ?>" <?php if($det['prod_prov05'] == 0) echo 'class="disabled" disabled=disabled'; ?>><?php echo $det['prod_prov05']; ?></label>
   			</td>
  		</tr>
  	<?php } ?>
  		</table>
  		</td>
  	</tr>
<?php } ?>
	</tbody>
</table>
<script type="text/javascript">
window.onload=function(){
	$('.selectpicker').selectpicker();
};
</script> ​
<?php
}
?>
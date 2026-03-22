<?php if (!empty($_SESSION['n']) && !empty($_SESSION['u'])) { ?>
	<a class="btn-link pull-left" href="LogOut.php"><i class="glyphicon glyphicon-user"></i> <?php echo $_SESSION['n']; ?> - [SALIR]</a>
<?php }?>
	<label class="pie1 pull-right hidden-xs">&#0169;&#174;&#0153; <?php echo date('Y'); ?> SICS - IMPRESSA, S.A. de C.V. - EL SALVADOR, C.A.</label>
	<label class="pie2 pull-right visible-xs">&#0169;&#174;&#0153; <?php echo date('Y'); ?> SICS - I.R., E.S., C.A.</label>
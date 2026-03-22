<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'model/cheque/EntityDB.php';
$db = new EntityDB;
$perfil = $db->get_usuario();

function is_ctl_action($ctlaction){
    $query_string = $_SERVER['QUERY_STRING'] ?? '';
	return stripos("/?" . $query_string, $ctlaction);
}

// Si la sesion esta activa debera mostra el menu correspondiente
$is_ufinal_o_uatorizadorcc = false;
$user_categoria = false;
$user_gestion = false;

// Inicializar $info para evitar errores
$info = array(
    'usr_req' => 0,
    'usr_sol' => 0,
    'usr_oc' => 0,
    'id_usuario' => 0,
    'usr_usuario' => '',
    'usr_nombre' => '',
    'id_rol' => 0
);

// Usar null coalescing para acceder a variables de sesión
$session_u = $_SESSION['u'] ?? '';
$session_cheque = $_SESSION['cheque'] ?? false;

if (!empty($session_u) && empty($session_cheque)) {
	try {
		$usr = Usuario::getInstance();
		$info = $usr->infoUsuario($session_u);
		
		// Validar que $info es un array y tiene los índices necesarios
		if (!is_array($info)) {
			$info = array();
		}
		
		// Usar null coalescing para acceder a índices del array
		$_SESSION['rol_de_usuario'] = ((int)($info['id_rol'] ?? 0) - 999999993);
		$cats = $usr->accesosCategorias($info['id_usuario'] ?? 0);

		if(empty($cats)){
			if(!empty($perfil->categoria)){
				$cats = $perfil->categoria;
			}
		}

		$user_categoria = $cats;
		$gest = $usr->accesosGestor($info['id_usuario'] ?? 0);
		$user_gestion = $gest;
		
		// Actualizar sesión solo si tenemos valores válidos
		if (isset($info['usr_usuario'])) {
			$_SESSION['u'] = $info['usr_usuario'];
			$_SESSION['n'] = $info['usr_nombre'] ?? '';
			$_SESSION['i'] = $info['id_usuario'] ?? 0;
			$_SESSION['idmod'] = $_GET['id'] ?? '';
			$_SESSION['req'] = $info['usr_req'] ?? 0;
			$_SESSION['sol'] = $info['usr_sol'] ?? 0;
			$_SESSION['oc'] = $info['usr_oc'] ?? 0;
			$_SESSION['rol_admin_sics'] = false;
			
			if(!empty($info)){
				$is_ufinal_o_uatorizadorcc = ((int)($info['id_rol'] ?? 0) <= 999999995);
				$_SESSION['rol_admin_sics'] = $is_ufinal_o_uatorizadorcc;
			}
		}
		
		if (empty($_GET['c'] ?? '')) {
			$controlador = 'login';
		}
		if (empty($_GET['a'] ?? '')) {
			$accion = 'ingreso';
		}
	} catch (Exception $e) {
	?>
		<div class="alert alert-error">
			<p><?php echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
		</div>
	<?php
		die();
	}
	
	// ACCESO A MODULOS
	$user_id = $_SESSION['i'] ?? 0;
	$res = $usr->modulosUsuario($user_id);
	$categoria = "";
	$catego = array();
	
	if ($res) {
		// Llenamos la primera categoria
		while ($mods = mysqli_fetch_array($res)) {
			$mod_categoria = $mods['mod_categoria'] ?? '';
			if ($categoria != $mod_categoria) {
				$catego[$mod_categoria] = array();
			}
			$categoria = $mod_categoria;
		}
		
		// Reiniciar el puntero del resultado
		mysqli_data_seek($res, 0);
		
		// Llenamos la segunda categoria y el contenido de la misma
		$x = 0;
		while ($mods = mysqli_fetch_array($res)) {
			$mod_categoria = $mods['mod_categoria'] ?? '';
			$mod_categoria2 = $mods['mod_categoria2'] ?? '';
			$id_modulo = $mods['id_modulo'] ?? 0;
			
			foreach ($catego as $c => $v) {
				if ($c == $mod_categoria) {
					if (!isset($catego[$c][$mod_categoria2])) {
						$catego[$c][$mod_categoria2] = array();
					}
					
					if ($id_modulo == 3) {
						if ($x == 0) {
							$catego[$c][$mod_categoria2][] = $mods;
							$x = 1;
						}
					} else {
						$catego[$c][$mod_categoria2][] = $mods;
					}
				}
			}
		}
		
		// Liberar resultado
		mysqli_free_result($res);
	}
	
	/*
	 * Quitamos las opciones a las que no tiene acceso
	 * Versión optimizada y segura para PHP 8.1
	 */
	$filtered_catego = array();
	
	foreach ($catego as $k => $v) {
		if (!is_array($v)) continue;
		
		$filtered_subcats = array();
		
		foreach ($v as $l => $m) {
			if (!is_array($m)) continue;
			
			$filtered_mods = array();
			
			foreach ($m as $mod) {
				if (!is_array($mod)) continue;
				
				$mod_url = $mod['mod_url'] ?? '';
				$should_keep = true;
				
				// Verificar permisos de forma segura
				if ($mod_url) {
					$usr_req = $info['usr_req'] ?? 0;
					$usr_sol = $info['usr_sol'] ?? 0;
					
					// URLs que requieren permisos de requisición
					if (strpos($mod_url, '?c=req') === 0 && $usr_req == 0) {
						if (strpos($mod_url, 'a=colectar') !== false || 
							strpos($mod_url, 'a=tracole') !== false ||
							strpos($mod_url, 'a=oc_sp') !== false) {
							$should_keep = false;
						}
					}
					
					// URLs que requieren permisos de solicitud
					if (strpos($mod_url, '?c=solc') === 0 && $usr_sol == 0) {
						if (strpos($mod_url, 'a=colectar') !== false || 
							strpos($mod_url, 'a=tracole') !== false ||
							strpos($mod_url, 'a=oc_sp') !== false ||
							strpos($mod_url, 'a=gestor') !== false) {
							$should_keep = false;
						}
					}
					
					// URLs específicas de inventario
					if ($mod_url == '?c=inv&a=inicios&id=5' && $usr_sol == 0) {
						$should_keep = false;
					}
					
					// URLs de consumo interno
					if (strpos($mod_url, '?c=ci&') === 0 && $usr_sol == 0) {
						if (strpos($mod_url, 'a=gestor') !== false || 
							strpos($mod_url, 'a=prod') !== false ||
							strpos($mod_url, 'a=xls') !== false) {
							$should_keep = false;
						}
					}
				}
				
				if ($should_keep) {
					$filtered_mods[] = $mod;
				}
			}
			
			if (!empty($filtered_mods)) {
				$filtered_subcats[$l] = $filtered_mods;
			}
		}
		
		if (!empty($filtered_subcats)) {
			$filtered_catego[$k] = $filtered_subcats;
		}
	}
	
	$catego = $filtered_catego;
	?>
	<?php if(!$is_ufinal_o_uatorizadorcc):?>
	<div class="panel-group" id="accordion">
		<?php $i = 1; ?>
		<?php foreach($catego as $c => $v): ?>
			<?php if (is_array($v) && !empty($v)): ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a href="#collapse<?php echo $i; ?>" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse">
							<?php echo htmlspecialchars($c, ENT_QUOTES, 'UTF-8'); ?>
						</a>
					</h4>
				</div>
				<?php
				$estado = "";
				$get_a = $_GET['a'] ?? '';
				$get_id = $_GET['id'] ?? '';
				$get_c = $_GET['c'] ?? '';
				
				if($c == 'ADMINISTRACION') {
					if($get_a == 'anal' || $get_id == '2' || $get_id == '3' || $get_id == '4' || $get_id == '8' || $get_id == '11' || $get_id == '13'){
						$estado = "in";
					}
					if($get_c == 'prov' && $get_a == 'lista'){
						$estado = "in";
					}
				} elseif($c == 'PROCESOS') {
					if(($get_id == '6' && $get_a == 'colectar') || ($get_id == '5' && $get_a == 'colectar')){
						$estado = "in";
					}
					if(($get_id == '6' && $get_a == 'crearoc') || ($get_id == '5' && $get_a == 'crearoc')){
						$estado = "in";
					}
				} elseif($c == 'REQUISICION DE SUMINISTRO') {
					if($get_id == '6' && $get_a != 'colectar' && $get_a != 'crearoc' && $get_a != 'crearnr' && $get_a != 'ocpre'){
						$estado = "in";
					}
				} elseif($c == 'SOLICITUD DE COMPRA') {
					if($get_id == '5' && $get_a != 'colectar'){
						$estado = "in";
					}
				} elseif ($c == 'REPORTES' && $get_id == '7') {
					$estado = "in";
				} elseif (($c == 'INVENTARIO' && $get_c == 'inv' && $get_id == '9') || 
				         ($c == 'INVENTARIO' && $get_a == 'ocpre') || 
				         ($c == 'INVENTARIO' && $get_a == 'crearnr')) {
					$estado = "in";
				} elseif($c == 'CONSUMO INTERNO' && $get_id == '12') {
					$estado = "in";
				} else {
					$estado = "";
				}
				?>
				<div id="collapse<?php echo $i; ?>" class="panel-collapse collapse <?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>">
					<div class="panel-body">
						<?php foreach($v as $c1 => $v1): ?>
							<?php if (is_array($v1) && !empty($v1)): ?>
							<b><?php echo htmlspecialchars($c1, ENT_QUOTES, 'UTF-8'); ?></b>
							<div class="list-group">
							<?php foreach($v1 as $x): ?>
								<?php if (is_array($x) && isset($x['mod_url']) && isset($x['mod_descripcion'])): ?>
								<a class="<?php echo (is_ctl_action($x['mod_url']) ? 'list-group-item active' : 'list-group-item'); ?>" 
								   href="<?php echo htmlspecialchars($x['mod_url'], ENT_QUOTES, 'UTF-8'); ?>" 
								   target="<?php echo htmlspecialchars($x['mod_target'] ?? '_self', ENT_QUOTES, 'UTF-8'); ?>">
									<?php echo htmlspecialchars($x['mod_descripcion'], ENT_QUOTES, 'UTF-8'); ?>
								</a>
								<?php endif; ?>
							<?php endforeach; ?>
							</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<?php $i++; ?>
			<?php endif; ?>
		<?php endforeach; ?>
		
		<?php
		// Debemos verificar si tiene acceso para autorizar ya sea categorias o gestionar
		if(is_array($cats) && count($cats) > 0):
		?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a href="#collapseCategorias" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse">
							GEST. CATEGORIA DE COMPRA
						</a>
					</h4>
				</div>
				<div id="collapseCategorias" class="panel-collapse collapse <?php echo (is_ctl_action("c=solc&a=gescat") ? "in" : ""); ?>">
					<div class="panel-body">	
						<b>Gestionar por Categorias</b>
						<div class="list-group">
							<a class="<?php echo (is_ctl_action("c=solc&a=gescat") ? 'list-group-item active' : 'list-group-item'); ?>" 
							   href="?c=solc&a=gescat" target="_self">
								Gestionar
							</a>
						</div>
					</div>
				</div>
			</div>
		<?php 
		endif;
		
		if(is_array($gest) && count($gest) > 0):
		?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a href="#collapseCategorias2" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse">
							GESTIONAR SOLICITUDES
						</a>
					</h4>
				</div>
				<div id="collapseCategorias2" class="panel-collapse collapse <?php echo (is_ctl_action("c=solc&a=gest") ? "in" : ""); ?>">
					<div class="panel-body">	
						<b>Autorizar Solicitudes</b>
						<div class="list-group">
							<a class="<?php echo (is_ctl_action("c=solc&a=gest") ? 'list-group-item active' : 'list-group-item'); ?>" 
							   href="?c=solc&a=gest" target="_self">
								Gestionar
							</a>
						</div>
					</div>
				</div>
			</div>
		<?php 
		endif;
		
		if(file_exists('view/solcheque/menu_perfil.php')):
			require_once 'view/solcheque/menu_perfil.php';
		endif;
		?>
	</div>
	<?php endif; ?>
<?php } else { ?>
    <?php 
    if(file_exists('view/solcheque/menu_perfil.php')):
        require_once 'view/solcheque/menu_perfil.php';
    endif;
    ?>
<?php } ?>

<?php if($is_ufinal_o_uatorizadorcc): ?>
<div id="accordion-sticky-wrapper" class="sticky-wrapper">
	<div class="panel-group" id="accordion">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">
					<a href="#collapse1" class="accordion-toggle collapsed" data-parent="#accordion" data-toggle="collapse">
						GESTIONAR SOLICITUDES
					</a>
				</h4>
			</div>
			<?php
			$isopen = (is_ctl_action('?c=menu&a=index') || 
			          is_ctl_action('?c=solcheque&a=crear') || 
			          is_ctl_action('?c=menu&a=autorizarcc') || 
			          is_ctl_action('?c=menu&a=consulta') || 
			          is_ctl_action('?c=solcheque&a=consultarde') || 
			          is_ctl_action('?c=solc&a=VerS') || 
			          is_ctl_action('?c=solcheque&a=VerS')) && 
			          !is_ctl_action('?c=menu&a=consulta_');
			$isopen = ($isopen ? 'in' : '');
			?>
			<div id="collapse1" class="panel-collapse collapse <?php echo htmlspecialchars($isopen, ENT_QUOTES, 'UTF-8'); ?>">
				<div class="panel-body">
					<div class="list-group">
						<a class="list-group-item <?php echo (is_ctl_action('?c=menu&a=index') || is_ctl_action('?c=menu&a=autorizarcc') || is_ctl_action('?c=solcheque&a=crear') ? 'active' : ''); ?>" 
						   href="?c=menu&a=index" target="_self">
							Gestionar		
						</a>
						<a class="list-group-item <?php echo (is_ctl_action('?c=menu&a=consulta') ? 'active' : ''); ?>" 
						   href="?c=menu&a=consulta" target="_self">
							Consultar
						</a>
						<?php if(!empty($_SESSION['u'])):
							$usr_sics = strtolower($_SESSION['u'] ?? '');
							$user_traza = array('controller', 'gcia.financiera', 'jefe administracion');
							if(in_array($usr_sics, $user_traza)):
						?>
							<a class="list-group-item <?php echo (is_ctl_action('?c=solcheque&a=VerS') || is_ctl_action('?c=solc&a=VerS') ? 'active' : ''); ?>" 
							   href="?c=solc&a=VerS" target="_self">
								Consultar por trazabilidad
							</a>
						<?php 
							endif;
						endif; 
						
						if(isset($perfil->is_aprobador_5k) && $perfil->is_aprobador_5k):
							$_SESSION['user_5k'] = true;
						?>
							<a class="list-group-item <?php echo (is_ctl_action('?c=solcheque&a=consultarde') ? 'active' : ''); ?>" 
							   href="?c=solcheque&a=consultarde" target="_self">
								Autorizar Igual o Mayor a $5K
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		
		<?php
		// Panel para categorías
		if(is_array($cats) && count($cats) > 0):
		?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a href="#collapseCategorias3" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse">
							GEST. CATEGORIA DE SOLICITUD
						</a>
					</h4>
				</div>
				<div id="collapseCategorias3" class="panel-collapse collapse <?php echo (is_ctl_action('?c=menu&a=consulta_categoria') ? "in" : ""); ?>">
					<div class="panel-body">	
						<b>Gestionar por Categorias</b>
						<div class="list-group">
							<a class="list-group-item <?php echo (is_ctl_action('?c=menu&a=consulta_categoria') ? 'active' : ''); ?>" 
							   href="?c=menu&a=consulta_categoria" target="_self">
								Gestionar
							</a>
						</div>
					</div>
				</div>
			</div>
		<?php 
		endif;
		
		// Panel para gestión
		if(is_array($gest) && count($gest) > 0):
		?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a href="#collapseCategorias4" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse">
							GESTIONAR SOLICITUDES
						</a>
					</h4>
				</div>
				<div id="collapseCategorias4" class="panel-collapse collapse <?php echo (is_ctl_action("c=solc&a=gest") ? "in" : ""); ?>">
					<div class="panel-body">	
						<b>Autorizar Solicitudes</b>
						<div class="list-group">
							<a class="<?php echo (is_ctl_action("c=solc&a=gest") ? 'list-group-item active' : 'list-group-item'); ?>" 
							   href="?c=solc&a=gest" target="_self">
								Gestionar
							</a>
						</div>
					</div>
				</div>
			</div>
		<?php 
		endif;
		?>

		<?php
		// Panel de opciones adicionales
		$arr_menu = array();
		$arr_menu_opt = array();
		$is_other = '';
		$is_active_other = false;

		$modulos_extras = isset($perfil->modulos) ? $perfil->modulos : array();
		$arr_omitir = array(39, 42, 10, 15, 9, 14,45);

		foreach ($modulos_extras as $modulo) {
			if (isset($modulo) && in_array($modulo->id_acc_modulo_lista, $arr_omitir)) {
				continue;
			}
			
			if (isset($modulo)) {
				if ($is_other != $modulo->categoria) {
					if (!empty($arr_menu_opt)) {
						$arr_menu[] = (object)array(
							'name'  => $is_other,
							'links' => $arr_menu_opt
						);
					}
					$arr_menu_opt = array();
				}
				
				$arr_menu_opt[] = (object)array(
					'url'  => $modulo->url,
					'text' => $modulo->descripcion
				);
				
				if (is_ctl_action($modulo->url)) {
					$is_active_other = true;
				}
				
				$is_other = $modulo->categoria;
			}
		}
		
		// Agregar el último grupo
		if (!empty($arr_menu_opt)) {
			$arr_menu[] = (object)array(
				'name'  => $is_other,
				'links' => $arr_menu_opt
			);
		}

		if (!empty($arr_menu)):
			$opt_act = ($is_active_other ? "in" : "");
		?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a href="#collapseOther" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse">
							MAS OPCIONES
						</a>
					</h4>
				</div>
				<div id="collapseOther" class="panel-collapse collapse <?php echo htmlspecialchars($opt_act, ENT_QUOTES, 'UTF-8'); ?>">
					<div class="panel-body">
						<?php foreach ($arr_menu as $m): ?>
							<b><?php echo htmlspecialchars($m->name, ENT_QUOTES, 'UTF-8'); ?></b>
							<div class="list-group">
								<?php foreach ($m->links as $op): ?>
									<a class="<?php echo (is_ctl_action($op->url) ? 'list-group-item active' : 'list-group-item'); ?>" 
									   href="<?php echo htmlspecialchars($op->url, ENT_QUOTES, 'UTF-8'); ?>" target="_self">
										<?php echo htmlspecialchars($op->text, ENT_QUOTES, 'UTF-8'); ?>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>

<?php 
if (file_exists('view/solcheque/menu_perfil.php')) {
    require_once 'view/solcheque/menu_perfil.php';
}
?>
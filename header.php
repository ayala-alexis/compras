<nav class="navbar navbar-inverse" role="navigation" id="stickyribbon">
  <!-- Brand and toggle get grouped for better mobile display -->
  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
  </div>
  <!-- Collect the nav links, forms, and other content for toggling -->
  <div class="collapse navbar-collapse navbar-ex1-collapse">
	<ul class="nav navbar-nav">
		<li><img class="img-logo" src="images/logoir.png" alt="Logo"/></li>
		<li><a href="./"><img src="images/home.png" alt="Inicio"/></a></li>
		<li style="min-width: 800px;display: block;text-align: center;">
			<h3 class="sics-title">SISTEMA INTEGRADO DE COMPRAS Y SUMINISTROS</h3>
		</li>
<?php
// Si la sesion esta activa debera mostra el menu correspondiente
if (isset($_SESSION['u']) && !empty($_SESSION['u'])) {
	try {
		$usr = Usuario::getInstance ();
		$info = $usr->infoUsuario ( $_SESSION ['u'] );
		
		// Inicializar $info si está vacío para evitar errores
		if (empty($info)) {
			$info = array(
				'usr_usuario' => '',
				'usr_nombre' => '',
				'id_usuario' => 0,
				'usr_req' => 0,
				'usr_sol' => 0,
				'usr_oc' => 0,
				'id_rol' => 0
			);
		}
		
		if(!empty($info) && isset($info['usr_usuario'])){
			$_SESSION['u'] = $info['usr_usuario'] ?? '';
			$_SESSION['n'] = $info['usr_nombre'] ?? '';
			$_SESSION['i'] = $info['id_usuario'] ?? 0;
			$_SESSION['idmod'] = $_GET['id'] ?? '';
			$_SESSION['req'] = $info['usr_req'] ?? 0;
			$_SESSION['sol'] = $info['usr_sol'] ?? 0;
			$_SESSION['oc'] = $info['usr_oc'] ?? 0;
			$_SESSION['menu_cheque_users'] = (isset($info['id_rol']) && (int)$info['id_rol'] >= 999999996) ? true : false;
		} else if(!empty($_SESSION['cheque'])){
            $info = array(
                'id_usuario' => 0,
                'usr_req' => 0,
                'usr_sol' => 0,
                'usr_oc' => 0
            );
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
	$res = $usr->modulosUsuario($_SESSION['i'] ?? 0);
	$categoria = "";
	$catego = array();
	
	// Llenamos la primera categoria
	if ($res) {
		while ($mods = mysqli_fetch_array($res)) {
			if ($categoria != ($mods['mod_categoria'] ?? '')) {
				// Inicializar como array, no como string
				$catego[$mods['mod_categoria']] = array();
			}
			$categoria = $mods['mod_categoria'] ?? '';
		}
		
		// Reiniciamos el puntero del resultado
		mysqli_data_seek($res, 0);
		
		// Llenamos la segunda categoria y el contenido de la misma
		$x = 0;
		while ($mods = mysqli_fetch_array($res)) {
			foreach ($catego as $c => $v) {
				if ($c == ($mods['mod_categoria'] ?? '')) {
					$cat2 = $mods['mod_categoria2'] ?? '';
					
					// Inicializar subarray si no existe
					if (!isset($catego[$c][$cat2])) {
						$catego[$c][$cat2] = array();
					}
					
					if (($mods['id_modulo'] ?? 0) == 3) {
						if ($x == 0) {
							$catego[$c][$cat2][] = $mods;
							$x = 1;
						}
					} else {
						$catego[$c][$cat2][] = $mods;
					}
				}
			}
		}
		
		// Liberar resultado
		mysqli_free_result($res);
	}
	
	/*
	 * Quitamos las opciones a las que no tiene acceso
	 * Usando referencias para modificar el array directamente
	 */
	foreach ($catego as $k => &$v) {
		if (is_array($v)) {
			foreach ($v as $l => &$m) {
				if (is_array($m)) {
					foreach ($m as $n => $mod) {
						// Verificar de forma segura si debemos eliminar este módulo
						if (is_array($mod) && isset($mod['mod_url'])) {
							$mod_url = $mod['mod_url'];
							$should_remove = false;
							
							// Verificar permisos según URL
							if (strpos($mod_url, '?c=req') === 0 && ($info['usr_req'] ?? 0) == 0) {
								if (strpos($mod_url, 'a=colectar') !== false || 
									strpos($mod_url, 'a=tracole') !== false) {
									$should_remove = true;
								}
							}
							
							if (strpos($mod_url, '?c=solc') === 0 && ($info['usr_sol'] ?? 0) == 0) {
								if (strpos($mod_url, 'a=colectar') !== false || 
									strpos($mod_url, 'a=tracole') !== false) {
									$should_remove = true;
								}
							}
							
							if ($should_remove) {
								unset($m[$n]);
							}
						}
					}
					
					// Limpiar arrays vacíos
					if (empty($m)) {
						unset($v[$l]);
					}
				}
			}
			unset($m); // Romper la referencia
			
			// Limpiar categorías vacías
			if (empty($v)) {
				unset($catego[$k]);
			}
		}
	}
	unset($v); // Romper la referencia
	
	// Si necesitas usar el menú descomentado, aquí está la versión segura:
	?>
			<?php /*
			<?php foreach($catego as $c=>$v): ?>
				<?php if (!empty($v)): ?>
				<li class="dropdown">
					<a id="drop1" href="#" role="button" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
						<?php echo htmlspecialchars($c, ENT_QUOTES, 'UTF-8'); ?><b class="caret"></b>
					</a>
					<ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
					<?php foreach($v as $c1=>$v1): ?>
							<?php if (!empty($v1)): ?>
							<li class="dropdown-submenu">
								<a><?php echo htmlspecialchars($c1, ENT_QUOTES, 'UTF-8'); ?></a>
								<ul class="dropdown-menu">
							<?php foreach($v1 as $x): ?>
									<?php if (is_array($x) && isset($x['mod_url']) && isset($x['mod_descripcion'])): ?>
									<li>
										<a href="<?php echo htmlspecialchars($x['mod_url'], ENT_QUOTES, 'UTF-8'); ?>" 
										   target="<?php echo htmlspecialchars($x['mod_target'] ?? '_self', ENT_QUOTES, 'UTF-8'); ?>">
											<?php echo htmlspecialchars($x['mod_descripcion'], ENT_QUOTES, 'UTF-8'); ?>
										</a>
									</li>
									<?php endif; ?>
							<?php endforeach; ?>
								</ul>
							</li>
							<?php endif; ?>
					<?php endforeach; ?>
					</ul>
				</li>
				<?php endif; ?>
			<?php endforeach; ?>
			*/ ?>
			</ul>
			<div class="navbar-right">
			<ul class="nav pull-right">
				<li class="dropdown clearfix">
					<a id="dropdownMenu1" href="#" role="button" class="dropdown-toggle" data-toggle="dropdown">
						<i class="glyphicon glyphicon-user"></i> 
						<?php echo htmlspecialchars($_SESSION['u'] ?? '', ENT_QUOTES, 'UTF-8'); ?> 
						<b class="caret"></b>
					</a>
					<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
						<li>
							<div class="col-sm-4 col-md-4">
            					<img src="images/users-grey.png" alt="Usuario" class="img-rounded img-responsive" />
        					</div>
					        <div class="col-sm-8 col-md-8">
					            <blockquote>
					                <p><?php echo htmlspecialchars($_SESSION['u'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p> 
									<small>
										<cite title="Ubicacion"> 
											<?php echo htmlspecialchars($_SESSION['n'] ?? '', ENT_QUOTES, 'UTF-8'); ?>  
											<i class="glyphicon glyphicon-map-marker"></i>
										</cite>
									</small>
					            </blockquote>
					            <p>
					            	<i class="glyphicon glyphicon-calendar"></i> <?php echo date("d/m/Y"); ?>
					                <br />
					                <i class="glyphicon glyphicon-time"></i> <?php echo date("H:i:s"); ?>
					            </p>
					        </div>
						</li>
						<li role="presentation">
							<a role="menuitem" href="?c=usua&a=pwd">
								<i class="icon-cog"></i> Preferencias
							</a>
						</li>
						<li role="presentation">
							<a role="menuitem" href="#">
								<i class="icon-envelope"></i> Ayuda
							</a>
						</li>
						<li role="presentation" class="divider"></li>
						<li role="presentation">
							<a role="menuitem" href="LogOut.php">
								<i class="icon-off"></i> Salir
							</a>
						</li>
					</ul>
				</li>
			</ul>
			</div>
<?php } ?>
</div>
</nav>
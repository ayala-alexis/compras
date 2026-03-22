<?php 
    $cheque_perfil='';
    if(isset($_SESSION['i'])){
        if(is_numeric($_SESSION['i'])){
            require_once dirname(__FILE__).'/../../model/Cheque/Entity.php';
            require_once dirname(__FILE__).'/../../model/Cheque/Help.php';
            $db=new Entity();
            $cheque_perfil=$db->findPerfil($_SESSION['i']);
            $db->setRolUsuario($cheque_perfil->rol);
        }
    }
?>
<?php if($cheque_perfil!=null): 
    $menu_cheque_user = false;
    if(isset($_SESSION['menu_cheque_users'])){
        if(!empty($_SESSION['menu_cheque_users'])){
            $menu_cheque_user = true;
        }
    }
    if(($cheque_perfil->rol!='N1' && $cheque_perfil->rol!='N2' && $cheque_perfil->rol!='N4') || $menu_cheque_user) :
    ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a href="#solcheque" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse" data-original-title="" title="">
                SOLICITUD DE CHEQUE
            </a>
        </h4>
    </div>
    <?php 
    $ctl = (isset($_GET['c']) ? ($_GET['c']=="solcheque" ? "in" : "") : "");
    $actl = ($ctl=="in" ? (isset($_GET['a']) ? $_GET['a'] : "") : "");
    $link = "?c=".($ctl=="in" ? "solcheque" : "")."&a=".$actl;
    ?>
    <div id="solcheque" class="panel-collapse collapse <?php echo $ctl?>">
        <div class="panel-body">
            <div class="list-group">
                <?php $opt_menu=(object)Help::perfil_menu_option($cheque_perfil->rol);?>
                <?php foreach ($opt_menu as $opt):?>
                    <a class="list-group-item <?php echo ($link==$opt->link ? "active" : "")?>" href="<?php echo $opt->link;?>" target="_self" data-original-title="" title="">
                        <?php echo $opt->name;?>
                    </a>
                <?php endforeach;?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>
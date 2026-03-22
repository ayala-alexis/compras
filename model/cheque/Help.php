<?php
class Help{
    public static function getDateTime(){
        return date('Y-m-d H:i:s');
    }
    public static function formatDateTime($date,$format_type='d-m-Y H:i'){
        $formatt = new DateTime($date);
        return $formatt->format($format_type);
    }
    public static function formatDate($date,$format_type='d-m-Y'){
        $formatt = new DateTime($date);
        return $formatt->format($format_type);
    }
    public static function formatTime($date,$format_type='H:i'){
        $formatt = new DateTime($date);
        return $formatt->format($format_type);
    }
    public static function formatDateN($date,$separator="/"){
        if(strlen($date)==8){
            return substr($date,6,2).$separator.substr($date,4,2).$separator.substr($date,0,4);
        }
        return $date;
    }
    public static function fecha_alter_day($days){
        return date('Ymd', strtotime("$days day",strtotime(date('Y-m-d'))));
    }
    public static function formatTimeN($time,$separator=":"){
        $fformat = str_pad($time, 6, "0", STR_PAD_LEFT);
        return substr($fformat,0,2).$separator.substr($fformat,2,2).$separator.substr($fformat,4,2);
    }
    public static function formatTimeShortN($time,$separator=":"){
        $fformat = str_pad($time, 6, "0", STR_PAD_LEFT);
        return substr($fformat,0,2).$separator.substr($fformat,2,2);
    }
    public static function getDate(){
        return date('Y-m-d');
    }
    public static function getTime(){
        return date('H:i:s');
    }
    public static function getDateN(){
        return date('Ymd');
    }
    public static function getTimeN(){
        return date('His');
    }
    public static function emptys($obj){
        if(isset($obj)){
            if($obj!=null){
                if(is_array($obj)){
                    return (count($obj)==0);
                }elseif(is_object($obj)){
                    return false;
                }
            }
        }
        return true;
    }
    public static function file_save($file,$name){
        return move_uploaded_file($file['tmp_name'],$name);
    }
    public static function file_delete($file){
        return unlink($file);
    }
    public static function file_ext($file){
        $temporary = explode(".", $file["name"]);
		return end($temporary);
    }
    public static function file_src($name,$type){
        $src=$name.".".$type;
        if(is_file("public/upload/".$src)){
            return "public/upload/".$src;
        }
        return null;
    }
    public static function query($url){
        if(isset($_GET[$url])){
            $isdata = $_GET[$url];
            if(!empty($isdata)){
                return trim($isdata);
            }
        }
        return "";
    }
    public static function file_adjunto($file){
        if(isset($file['size'])){
            return ($file['size']>0);
        }
        return false;
    }
    public static function file_size($file){
        if(isset($file['size'])){
            return ($file['size']<=MAX_UPLOAD);
        }
        return false;
    }
    public static function autorizador_5k(){
        if(isset($_SESSION['user_5k'])){
            return !empty($_SESSION['user_5k']);
        }
        return false;
    }
    public static function perfil_menu_option($perfil){
        $menu = array();
        if($perfil=='N1' || $perfil=='N2'){
            $menu[]=(object)array(
                'name'  =>  'Crear Solicitud',
                'link'  =>  '?c=solcheque&a=crear'
            );
            $menu[]=(object)array(
                'name'  =>  'Consultar Solicitud',
                'link'  =>  '?c=solcheque&a=consultar'
            );        
            if($perfil=='N2'){
                $menu[]=(object)array(
                    'name'  =>  'Autorizar Solicitud Cco.',
                    'link'  =>  '?c=solcheque&a=consultar_autorizar'
                );
            }
        }
        if($perfil=='N3'){
            $menu[]=(object)array(
                'name'  =>  'Consultar Solicitud',
                'link'  =>  '?c=solcheque&a=consultar'
            );
            $menu[]=(object)array(
                'name'  =>  'Gestionar Solicitud',
                'link'  =>  '?c=solcheque&a=categorizar'
            );
            $menu[]=(object)array(
                'name'  =>  'Reporte Solicitudes',
                'link'  =>  '?c=solcheque&a=reporte'
            );
            $menu[]=(object)array(
                    'name'  =>  'Consulta por trazabilidad',
                    'link'  =>  '?c=solcheque&a=VerS'
                );
        }
        if($perfil=='N6'){
            $menu[]=(object)array(
                'name'  =>  'Consultar Solicitud',
                'link'  =>  '?c=solcheque&a=consultarde'
            );
        }
        if($perfil=='N4' || $perfil=='N5'){
            if($perfil=='N4'){
                $multi = (isset($_SESSION['N2N4']) ? (!empty($_SESSION['N2N4']) ? is_bool($_SESSION['N2N4']) : FALSE) : FALSE);
                if($multi){
                    $menu[]=(object)array(
                        'name'  =>  'Crear Solicitud',
                        'link'  =>  '?c=solcheque&a=crear'
                    );
                }
                $menu[]=(object)array(
                    'name'  =>  'Consultar Solicitud',
                    'link'  =>  '?c=solcheque&a=consultar'
                );
                if($multi){
                    $menu[]=(object)array(
                        'name'  =>  'Autorizar Solicitud Cco.',
                        'link'  =>  '?c=solcheque&a=consultar_autorizar'
                    );
                }
                $menu[]=(object)array(
                    'name'  =>  'Autorizar Solicitud C. Gasto',
                    'link'  =>  '?c=solcheque&a=consultar_revision'
                );
            }else{
                $menu[]=(object)array(
                    'name'  =>  'Impresión Solicitud',
                    'link'  =>  '?c=solcheque&a=consultar_impresion'
                );
            }
            /*$menu[]=(object)array(
                'name'  =>  'Panel Admin',
                'link'  =>  '?c=solcheque&a=admin'
            );*/
        }
        $_SESSION['user_5k']=false;
        if(isset($_SESSION['u'])){
            $login_user = trim(strtoupper($_SESSION['u']));
            if($login_user==strtoupper(DIRECCION_EJECUTIVA)){
                $_SESSION['user_5k']=true;
                $menu[]=(object)array(
                    'name'  =>  'Autorizar Igual o Mayor a $5K',
                    'link'  =>  '?c=solcheque&a=consultarde'
                );
            }
        }
        return $menu;
    }
    public static function paginator($paginador,$link){
        $html_pag = "";
        if(!empty($paginador)){
            if($paginador['paginar']){
            $tot_num = 1;
            $pag_act = (int)$paginador['pagina_actual'];
            $pag_tot = (int)$paginador['pagina_total'];
            $item_ini = (($paginador['pagina_item']*$pag_act)+1);
            $item_fin = (($paginador['pagina_item']*$pag_act)+$paginador['pagina_item']);
            if($item_fin>(int)$paginador['pagina_item_total']){
                $item_fin=(int)$paginador['pagina_item_total'];
            }
            $html_pag = "<div class='row'>".
                            "<div class='col-sm-4'>".
                                "Item del <b><u>".$item_ini." al ".$item_fin."</u></b> de un total de <b><u>".$paginador['pagina_item_total']." solicitud(es).</u></b>".
                            "</div>".
                            "<div class='col-sm-8'>".
                            "<nav aria-label='page navigation' class='pull-right'>".
                                "<ul class='pagination'>".
                                    "<li ".($pag_act==0 ? "class='disabled'" : "").">".
                                        "<a href='".$link."&pag=".($pag_act-1)."' aria-label='Previous' title='Anterior'>".
                                            "<span aria-hidden='true'>&laquo;</span> Anterior".
                                        "</a>".
                                    "</li>";
                for($pag=0;$pag<$pag_tot;$pag++){
                    if(((($pag + 4) >= $pag_act && $pag_act >= ($pag - 5)) || ($pag_act<=5 && $pag<=10) || ($pag+9)>=$pag_tot) && $tot_num<11){
                        $html_pag .= "<li ".($pag==$pag_act ? "class='active'" : "").">".
                                        "<a href='".$link."&pag=".$pag."'>".
                                            ($pag+1).
                                        "</a>".
                                     "</li>";
                        $tot_num++;
                    }/*else{
                        echo "<br/><b>((((pag + 4):".($pag + 4)." >= pag_act:".$pag_act." && pag_act:".$pag_act." >= (pag - 5):".($pag - 5).") || (pag_act:".$pag_act."<=5 && pag:".$pag."<=10) || (pag+9):".($pag+9).">=pag_tot:".$pag_tot.") && pag_tot:".$pag_tot."<10)</b>";
                        echo "<br/><b>Pag: $pag, Pag_act: $pag_act, Pag_tot: $pag_tot</b><br/>";
                    }*/
                }
                        $html_pag .= "<li ".($pag_act>=($pag_tot-1) ? "class='disabled'" : "").">".
                                        "<a href='".$link."&pag=".($pag_act+1)."' aria-label='Next' title='Siguiente'>".
                                            "Siguiente <span aria-hidden='true'>&raquo;</span>".
                                        "</a>".
                                    "</li>".
                                "</ul>".
                            "</nav>".
                            "</div>".
                        "</div>\n";
            }
            $html_pag .= "<script>\n".
                             "$(document).ready(function(){".
                                "$('.pagination li.disabled a, .pagination li.active a').click(function(){".
                                    "return false;".
                                "});".
                             "});".
                         "</script>";
        }
        return $html_pag;
    }
    public static function paginator_other($paginador,$link){
        $html_pag = "";
        if(!empty($paginador)){
            if($paginador['paginar']){
            $tot_num = 1;
            $pag_act = (int)$paginador['pagina_actual'];
            $pag_tot = (int)$paginador['pagina_total'];
            $item_ini = (($paginador['pagina_item']*$pag_act)+1);
            $item_fin = (($paginador['pagina_item']*$pag_act)+$paginador['pagina_item']);
            if($item_fin>(int)$paginador['pagina_item_total']){
                $item_fin=(int)$paginador['pagina_item_total'];
            }
            $html_pag = "<div class='row'>".
                            "<div class='col-sm-4'>".
                                "Item del <b><u>".$item_ini." al ".$item_fin."</u></b> de un total de <b><u>".$paginador['pagina_item_total']." registro(es).</u></b>".
                            "</div>".
                            "<div class='col-sm-8'>".
                            "<nav aria-label='page navigation' class='pull-right'>".
                                "<ul class='pagination'>".
                                    "<li ".($pag_act==0 ? "class='disabled'" : "").">".
                                        "<a href='".$link."&pag=".($pag_act-1)."' aria-label='Previous' title='Anterior'>".
                                            "<span aria-hidden='true'>&laquo;</span> Anterior".
                                        "</a>".
                                    "</li>";
                for($pag=0;$pag<$pag_tot;$pag++){
                    if(((($pag + 4) >= $pag_act && $pag_act >= ($pag - 5)) || ($pag_act<=5 && $pag<=10) || ($pag+9)>=$pag_tot) && $tot_num<11){
                        $html_pag .= "<li ".($pag==$pag_act ? "class='active'" : "").">".
                                        "<a href='".$link."&pag=".$pag."'>".
                                            ($pag+1).
                                        "</a>".
                                     "</li>";
                        $tot_num++;
                    }/*else{
                        echo "<br/><b>((((pag + 4):".($pag + 4)." >= pag_act:".$pag_act." && pag_act:".$pag_act." >= (pag - 5):".($pag - 5).") || (pag_act:".$pag_act."<=5 && pag:".$pag."<=10) || (pag+9):".($pag+9).">=pag_tot:".$pag_tot.") && pag_tot:".$pag_tot."<10)</b>";
                        echo "<br/><b>Pag: $pag, Pag_act: $pag_act, Pag_tot: $pag_tot</b><br/>";
                    }*/
                }
                        $html_pag .= "<li ".($pag_act>=($pag_tot-1) ? "class='disabled'" : "").">".
                                        "<a href='".$link."&pag=".($pag_act+1)."' aria-label='Next' title='Siguiente'>".
                                            "Siguiente <span aria-hidden='true'>&raquo;</span>".
                                        "</a>".
                                    "</li>".
                                "</ul>".
                            "</nav>".
                            "</div>".
                        "</div>\n";
            }
            $html_pag .= "<script>\n".
                             "$(document).ready(function(){".
                                "$('.pagination li.disabled a, .pagination li.active a').click(function(){".
                                    "return false;".
                                "});".
                             "});".
                         "</script>";
        }
        return $html_pag;
    }
}
?>
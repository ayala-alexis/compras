<?php
require_once dirname(__FILE__).'/DBSics.php';
require_once dirname(__FILE__).'/Solicitud.php';
require_once dirname(__FILE__).'/EntityTbl.php';
class Entity{
    private $id_usuario;
    private $name_usuario;
    private $rol_usuario;
    private $db;
    private $tbl;
    private $page_elto;
    public function get_page_elto(){
        return $this->page_elto;
    }
    public function __construct(){
        $this->db=new DBSics();
        $this->tbl=new EntityTable();
        $this->page_elto=8;
        $this->id_usuario=0;
        $this->rol_usuario='';
        if(!empty($_SESSION)){
            if(!empty($_SESSION['i'])){
                if(is_numeric($_SESSION['i'])){
                    $this->id_usuario=(int)$_SESSION['i'];
                }
            }
            if(!empty($_SESSION['rol_usuario_cheque'])){
                $this->rol_usuario=trim($_SESSION['rol_usuario_cheque']);
            }
            if(!empty($_SESSION['u'])){
                $this->name_usuario=trim($_SESSION['u']);
            }
        }
    }
    public function setPermisos($id_user,$rol,$name){
        $this->id_usuario=(int)$id_user;
        $this->rol_usuario=$rol;
        $this->name_usuario=$name;
    }
	function findEmpresaOC(){
        return $this->db->sql_select_all("select id_empresa,emp_nombre nombre_empresa,id_empresa_oc from ".$this->tbl->empresa. " where id_empresa_oc!='' order by emp_nombre");
    }
    public function admin_borrar_usuario($id_usuario){
        $arr = array(
            ':id_usuario' => $id_usuario
        );
        $result = $this->db->sql_query('delete from '.$this->tbl->cheque_usuario.' where id_usuario=:id_usuario',$arr);
        if(!empty($result)){
            $result = $this->db->sql_query('delete from '.$this->tbl->cheque_usuario_empresa.' where id_usuario=:id_usuario',$arr);
            return TRUE;
        }
        return FALSE;
    }
    public function info_correo($id_solicitud){
        $arr = array( ':id' => $id_solicitud );
        $solicitud = $this->db->sql_select_one("select ".
                                               "s.id,".
                                               "s.id_usuario,".
                                               "s.id_empresa,".
                                               "e.emp_nombre,".
                                               "s.id_cc,".
                                               "c.cc_codigo,".
                                               "c.cc_descripcion,".
                                               "s.avance,".
                                               "s.status,".
                                               "s.fecha_solicitud,".
                                               "s.hora_solicitud,".
                                               "s.id_categoria,".
                                               "ifnull(t.nombre_categoria,'') nombre_categoria,".
                                               "u.usr_nombre,".
                                               "u.usr_usuario,".
                                               "s.5k is5k,".
                                               "u.usr_email ".
                                               "FROM cheque_sol s ".
                                               "left join empresa e ".
                                               "on e.id_empresa=s.id_empresa ".
                                               "left JOIN cecosto c ".
                                               "on c.id_cc=s.id_cc ".
                                               "left JOIN tipo_categoria t ".
                                               "on t.id_categoria=s.id_categoria ".
                                               "left JOIN usuario u ".
                                               "on s.id_usuario=u.id_usuario ".
                                               "where s.id=:id",$arr);

        $msg = array();
        $lcorreo = array();
        if(!empty($solicitud)){
            $msg['id_solicitud']=$solicitud->id;
            $msg['creado_por']=array(
              'usuario'         =>  $solicitud->usr_nombre,
              'correo'          =>  $solicitud->usr_email,
              'cco'             =>  $solicitud->cc_codigo,
              'cco_descripcion' =>  $solicitud->cc_descripcion,
              'empresa'         =>  $solicitud->emp_nombre,
              'id_categoria'    =>  $solicitud->id_categoria,
              'nombre_categoria'=>  $solicitud->nombre_categoria
            );
            $msg['avance']=$solicitud->avance;
            $msg['status']=$solicitud->status;
            $seguimiento = null;
            if($solicitud->avance=='N2'){ //se ha enviado solicitud
                $seguimiento = $this->db->sql_select_one('select status,avance,usuario,nivel '.
                                                             'from '.$this->tbl->cheque_seguimiento. ' '.
                                                             'where id_solicitud=:id and nivel='.($solicitud->status=='R' ? 1 : 2),$arr);
                if(!empty($seguimiento)){
                    $msg['de_usuario']=$seguimiento->usuario;
                    if($solicitud->status=='R'){
                      $msg['body']="<b><u>".strtoupper($msg['de_usuario'])."</u></b> le ha enviado la solicitud <b>#".
                                   str_pad($solicitud->id, 6,'0',STR_PAD_LEFT)."</b> para su autorización del Cco. ".
                                   "<b>".$msg['creado_por']['cco']." - ".$msg['creado_por']['cco_descripcion']." (".$msg['creado_por']['empresa'].")</b>.";
                        $lcorreo_send = $this->db->sql_select_all("select u.usr_nombre,u.usr_email ".
                                                                  "from acc_emp_cc c ".
                                                                  "inner join usuario u ".
                                                                  "on u.id_usuario=c.id_usuario and u.id_rol=999999995 ".
                                                                  "where c.id_cc=".$solicitud->id_cc." and c.id_empresa=".$solicitud->id_empresa);
                        foreach ($lcorreo_send as $correo) {
                            $lcorreo[]=array(
                                'correo'    =>  $correo->usr_email,
                                'nombre'    =>  $correo->usr_nombre
                            );
                        }
                        $msg['estado']=true;
                    }elseif($solicitud->status=='D'){
                        $msg['body']="<b><u>".strtoupper($msg['de_usuario'])."</u></b> ha desistido la solicitud <b>#".
                                   str_pad($solicitud->id, 6,'0',STR_PAD_LEFT)."</b> del Cco. ".
                                   "<b>".$msg['creado_por']['cco']." - ".$msg['creado_por']['cco_descripcion']." (".$msg['creado_por']['empresa'].")</b>.";
                        $lcorreo[]=array(
                                'correo'    =>  $solicitud->usr_email,
                                'nombre'    =>  $solicitud->usr_nombre
                            );
                        $msg['estado']=false;
                    }
                }
            }else if($solicitud->avance=='N3'){ //se ha enviado a ufinanzas o a direccion ejecutiva
                if($solicitud->status=='R' || $solicitud->status=='D'){
                    $seguimiento = $this->db->sql_select_one('select status,avance,usuario,nivel '.
                                                                 'from '.$this->tbl->cheque_seguimiento. ' '.
                                                                 'where id_solicitud=:id and nivel='.($solicitud->status=='R' ? 2 : 3),$arr);
                    if(!empty($seguimiento)){
                        $msg['de_usuario']=$seguimiento->usuario;
                        if($solicitud->status=='R'){ //recibido por ufinanzas por autorizador de cco
                            $msg['body']="<b><u>".strtoupper($msg['de_usuario'])."</u></b> le ha enviado la solicitud <b>#".
                                   str_pad($solicitud->id, 6,'0',STR_PAD_LEFT)."</b> para la asignación de categoría del Cco. ".
                                   "<b>".$msg['creado_por']['cco']." - ".$msg['creado_por']['cco_descripcion']." (".$msg['creado_por']['empresa'].")</b>.";

                            $lcorreo_send = $this->db->sql_select_all("SELECT DISTINCT ".
                                                                  "u.correo, ".
                                                                  "u.nombre ".
                                                                  "from cheque_usuario u ".
                                                                  "inner join cheque_usuario_empresa e ".
                                                                  "on e.id_empresa=".$solicitud->id_empresa." and e.id_usuario=u.id_usuario and u.nivel='N3'");
                            foreach ($lcorreo_send as $correo) {
                                $lcorreo[]=array(
                                    'correo'    =>  $correo->correo,
                                    'nombre'    =>  $correo->nombre
                                );
                            }
                            $msg['estado']=true;
                        }else{
                          $msg['body']="<b><u>".strtoupper($msg['de_usuario'])."</u></b> ha desistido la solicitud <b>#".
                                   str_pad($solicitud->id, 6,'0',STR_PAD_LEFT)."</b> del Cco. ".
                                   "<b>".$msg['creado_por']['cco']." - ".$msg['creado_por']['cco_descripcion']." (".$msg['creado_por']['empresa'].")</b>.";
                            $lcorreo[]=array(
                                    'correo'    =>  $solicitud->usr_email,
                                    'nombre'    =>  $solicitud->usr_nombre
                                );
                            $msg['estado']=false;
                        }
                    }
                }elseif($solicitud->status=='Z' || $solicitud->status=='W'){ //Z:recibido por Dirección Ejecutiva por Ufinanzas
                    $seguimiento = $this->db->sql_select_one("select status,avance,usuario,nivel ".
                                                             "from ".$this->tbl->cheque_seguimiento." ".
                                                             "where id_solicitud=:id and nivel=".($solicitud->status=='Z' ? 5 : 4),$arr);
                    if(!empty($seguimiento)){
                        $msg['de_usuario']=$seguimiento->usuario;  
                        $msg['body']="<b><u>".strtoupper($msg['de_usuario'])."</u></b> le ha enviado la solicitud <b>#".
                                   str_pad($solicitud->id, 6,'0',STR_PAD_LEFT)."</b> para su aprobación de cheques con valor igual o mayor a <b>$".number_format(MAX_CHEQUE, 2, '.', ',')."</b> del Cco. ".
                                   "<b>".$msg['creado_por']['cco']." - ".$msg['creado_por']['cco_descripcion']." (".$msg['creado_por']['empresa'].")</b>.";                                      
                        if($solicitud->status=='Z'){
                          if((int)$solicitud->is5k==1){
                            $lcorreo_send = $this->db->sql_select_one("select usr_nombre,usr_email ".
                                                              "from usuario ".
                                                              "where usr_usuario='".DIRECCIONEJECUTIVA."'");
                            if(!empty($lcorreo_send)){
                                $lcorreo[]=array(
                                    'correo'    =>  $lcorreo_send->usr_email,
                                    'nombre'    =>  $lcorreo_send->usr_nombre
                                );
                            }
                            $msg['estado']=true;
                          }
                        }else{
                          $msg['body']="<b><u>".strtoupper($msg['de_usuario'])."</u></b> ha desistido la solicitud <b>#".
                                   str_pad($solicitud->id, 6,'0',STR_PAD_LEFT)."</b> del Cco. ".
                                   "<b>".$msg['creado_por']['cco']." - ".$msg['creado_por']['cco_descripcion']." (".$msg['creado_por']['empresa'].")</b>.";
                            $lcorreo[]=array(
                                    'correo'    =>  $solicitud->usr_email,
                                    'nombre'    =>  $solicitud->usr_nombre
                                );
                            $msg['estado']=false;
                        }
                    }
                }
            }else if($solicitud->avance=='N4'){
                $seguimiento=null;
                if($solicitud->id_categoria!=ID_CATEGORIA_APROBACION_AUTOMATICA){
                    $seguimiento = $this->db->sql_select_one('select status,avance,usuario,nivel '.
                                                                 'from '.$this->tbl->cheque_seguimiento. ' '.
                                                                 'where id_solicitud=:id and nivel='.($solicitud->status=='R' ? 3 : 5),$arr);
                }
                if(!empty($seguimiento)){
                    $msg['de_usuario']=$seguimiento->usuario;                                        
                    if($solicitud->status=='R'){ //recibido por ufinanzas por autorizador de cco
                      $msg['body']="<b><u>".strtoupper($msg['de_usuario'])."</u></b> le ha enviado la solicitud <b>#".
                                   str_pad($solicitud->id, 6,'0',STR_PAD_LEFT)."</b> para su autorización de categoría <b>(".strtoupper($solicitud->id_categoria.' - '.$solicitud->nombre_categoria).")</b> del Cco. ".
                                   "<b>".$msg['creado_por']['cco']." - ".$msg['creado_por']['cco_descripcion']." (".$msg['creado_por']['empresa'].")</b>.";
                        $lcorreo_send = $this->db->sql_select_all("select ".
                                                                  "u.usr_nombre,".
                                                                  "u.usr_email ".
                                                                  "FROM usuario u ".
                                                                  "inner JOIN gestion_categorias t ".
                                                                  "on t.id_usuario=u.id_usuario ".
                                                                  "where t.id_categoria=".$solicitud->id_categoria." and t.gestion_nivel=1");
                        foreach ($lcorreo_send as $correo) {
                            $lcorreo[]=array(
                                'correo'    =>  $correo->usr_email,
                                'nombre'    =>  $correo->usr_nombre
                            );
                        }
                        $msg['estado']=true;
                    }else{
                      $msg['body']="<b><u>".strtoupper($msg['de_usuario'])."</u></b> ha desistido la solicitud <b>#".
                                   str_pad($solicitud->id, 6,'0',STR_PAD_LEFT)."</b> del Cco. ".
                                   "<b>".$msg['creado_por']['cco']." - ".$msg['creado_por']['cco_descripcion']." (".$msg['creado_por']['empresa'].")</b>.";
                        $lcorreo[]=array(
                                'correo'    =>  $solicitud->usr_email,
                                'nombre'    =>  $solicitud->usr_nombre
                            );
                        $msg['estado']=false;
                    }
                }
            }else if($solicitud->avance=='N5'){
                    if($solicitud->status=='R'){ //recibido por ufinanzas por autorizador de cco
                      $msg['body']="Se ha autorizado la solicitud <b>#".
                                   str_pad($solicitud->id, 6,'0',STR_PAD_LEFT)."</b> del Cco. ".
                                   "<b>".$msg['creado_por']['cco']." - ".$msg['creado_por']['cco_descripcion']." (".$msg['creado_por']['empresa'].")</b>.";
                        $lcorreo_send = $this->db->sql_select_all("SELECT DISTINCT ".
                                                              "u.correo, ".
                                                              "u.nombre ".
                                                              "from cheque_usuario u ".
                                                              "inner join cheque_usuario_empresa e ".
                                                              "on e.id_empresa=".$solicitud->id_empresa." and e.id_usuario=u.id_usuario and u.nivel='N5'");
                        foreach ($lcorreo_send as $correo) {
                            $lcorreo[]=array(
                                'correo'    =>  $correo->correo,
                                'nombre'    =>  $correo->nombre
                            );
                        }
                        $lcorreo[]=array(
                            'correo'    =>  $solicitud->usr_email,
                            'nombre'    =>  $solicitud->usr_nombre
                        );
                        $msg['estado']=true;
                    }else{
                      $seguimiento = $this->db->sql_select_one('select status,avance,usuario,nivel '.
                                                                 'from '.$this->tbl->cheque_seguimiento. ' '.
                                                                 'where id_solicitud=:id and nivel=6',$arr);
                      $msg['de_usuario']=$seguimiento->usuario;                                        
                      $msg['body']="<b><u>".strtoupper($msg['de_usuario'])."</u></b> ha desistido la solicitud <b>#".
                                   str_pad($solicitud->id, 6,'0',STR_PAD_LEFT)."</b> del Cco. ".
                                   "<b>".$msg['creado_por']['cco']." - ".$msg['creado_por']['cco_descripcion']." (".$msg['creado_por']['empresa'].")</b>.";
                        $lcorreo[]=array(
                                'correo'    =>  $solicitud->usr_email,
                                'nombre'    =>  $solicitud->usr_nombre
                            );
                        $msg['estado']=false;
                    }
          }
          $msg['para_usuario']=$lcorreo;
        }
        return $msg;
    }
    public function depurar($obj){
        echo "<pre>";
        print_r($obj);
        echo "</pre>";
    }
    public function admin_borrar_empresa_id($id){
        $arr = array(
            ':id' => $id
        );
        return $this->db->sql_query('delete from '.$this->tbl->cheque_usuario_empresa.' where id=:id',$arr);
    }
    public function admin_agregar_empresa($id_usuario,$id_empresa){
        $arr = array(
            ':id_usuario' => $id_usuario,
            ':id_empresa' => $id_empresa
        );
        return $this->db->sql_save_id('insert into '.$this->tbl->cheque_usuario_empresa.'(id_usuario,id_empresa) values(:id_usuario,:id_empresa)',$arr);
    }
    public function admin_agregar_usuario($id_usuario,$usuario,$nombre,$correo,$rol){
        $arr = array(
            ':id_usuario' => $id_usuario,
            ':usuario'    => $usuario,
            ':nombre'     => $nombre,
            ':correo'     => $correo,
            ':nivel'      => $rol
        );
        return $this->db->sql_query('update '.$this->tbl->cheque_usuario.' set usuario=:usuario,nivel=:nivel,correo=:correo,nombre=:nombre where id_usuario=:id_usuario',$arr);
    }
    public function admin_crear_usuario($usuario,$pwd,$nombre,$correo,$rol){
        $arr = array(
            ':usuario'    => $usuario,
            ':password'   => $pwd,
            ':nombre'     => $nombre,
            ':correo'     => $correo,
            ':nivel'      => $rol
        );
        return $this->db->sql_save_id('insert into '.$this->tbl->cheque_usuario.'(usuario,password,nivel,correo,nombre) values(:usuario,:password,:nivel,:correo,:nombre)',$arr);
    }
    public function admin_existe_usuario($usuario){
        $arr = array(
            ':usuario'    => $usuario
        );
        $result = $this->db->sql_select_one('select id_usuario from '.$this->tbl->cheque_usuario.' where usuario=:usuario',$arr);
        return !empty($result);
    }
    public function admin_cambiar_pwd($id_usuario,$contra){
        $arr = array(
            ':id_usuario' => $id_usuario,
            ':password'   => $contra
        );
        return $this->db->sql_query('update '.$this->tbl->cheque_usuario.' set password=:password where id_usuario=:id_usuario',$arr);
    }
    public function setRolUsuario($rol_usuario){
        if(!empty($_SESSION)){
            $_SESSION['rol_usuario_cheque']=$rol_usuario;
            $this->rol_usuario=$rol_usuario;
        }
    }
    public function LoginUser($username,$password){
        $arr=array(
            ':usuario'  =>  $username,
            ':password' =>  $password
        );
        return $this->db->sql_select_one("select * from ".$this->tbl->cheque_usuario." where usuario=:usuario and password=:password",$arr);
    }
    public function findPerfilEmail($id_usuario,$rol=null){
        //Se busca perfil autorizador (Categorizador Finanzas y Contabilidad).
        //Roles: N3 (Finanzas) y N5 (Contabilidad)
        if(!empty($rol)){
            if($rol=='N3' || $rol=='N5' || $rol=='N6'){
                $perfil=$this->findPerfilN3OrN5Email($id_usuario);
                if(!empty($perfil)){
                    return $perfil;
                }
            }
        }
        
        //Se busca perfil autorizador de categorias
        //Rol: N4 (Gestor de categorias)
        $perfil=$this->findPerfilN4($id_usuario);
        if(!empty($perfil)){
            return $perfil;
        }
        
        //Se busca perfil usuario final y usuario autorizador
        //Roles: N1 (Usuario Final) y N2 (Usuario Autorizador)
        $perfil=$this->findPerfilN1orN2($id_usuario);
        if(!empty($perfil)){            
            return $perfil;
        }
        return null;
    }
    public function findPerfil($id_usuario, $rol=FALSE){
        //Se busca perfil autorizador (Categorizador Finanzas y Contabilidad).
        //Roles: N3 (Finanzas) y N5 (Contabilidad)
        $user_name=$this->name_usuario;
        $perfil=$this->findPerfilN3OrN5($user_name);
        if(!empty($perfil)){
            return $perfil;
        }
        
        //Se busca perfil autorizador de categorias
        //Rol: N4 (Gestor de categorias)
        $id_categoria=$this->findCategoriaUser($id_categoria);
        $perfil=$this->findPerfilN4($id_usuario);
        if(!empty($perfil) && !$rol){
            $user = array(
                ':id_usuario' => $id_usuario,
                ':rol'        => '999999995'
            );
            $autoriza = $this->db->sql_select_one("select id_usuario from ".$this->tbl->usuario." where id_usuario=:id_usuario and id_rol=:rol",$user);
            if(!empty($autoriza)){
                $_SESSION['N2N4']=TRUE;
            }else{
                $_SESSION['N2N4']=NULL;
            }
            $perfil->id_categoria=$id_categoria;
            return $perfil;
        }
        
        //Se busca perfil usuario final y usuario autorizador
        //Roles: N1 (Usuario Final) y N2 (Usuario Autorizador)
        $perfil=$this->findPerfilN1orN2($id_usuario);
        if(!empty($perfil)){       
            $perfil->id_categoria=$id_categoria;     
            return $perfil;
        }
        return null;
    }
    function findEmpresaAll(){
        return $this->db->sql_select_all("select id_empresa,emp_nombre nombre_empresa from ".$this->tbl->empresa. " order by emp_nombre");
    }
    private function findPerfilN3OrN5($usuario){
        $arr=array(
            ':usuario'   => $usuario
        );
        //Se busca perfil autorizador (Categorizador Finanzas y Contabilidad).
        //Roles: N3 (Finanzas) y N5 (Contabilidad)
        $result=$this->db->sql_select_one("select id_usuario,usuario,nivel,correo,nombre from ".$this->tbl->cheque_usuario. " where usuario=:usuario",$arr);
        if(!empty($result)){
            $arr=array(
                ':id_usuario'   => $result->id_usuario
            );
            $perfil=new PerfilUser;
            $perfil->id=$result->id_usuario;
            $perfil->username=$result->usuario;
            $perfil->rol=$result->nivel;
            $perfil->email=$result->correo;
            $perfil->nombre=$result->nombre;
            $perfil->empresa=null;
            $perfil->categoria=null;

            //Empresas disponibles para usuario autorizador
            $result=$this->db->sql_select_all("select e.id_empresa,e.emp_nombre,e.emp_razon,e.emp_direccion from ".$this->tbl->empresa." e inner join ".$this->tbl->cheque_usuario_empresa." u on u.id_empresa=e.id_empresa where u.id_usuario=:id_usuario order by e.emp_nombre",$arr);
            if(!empty($result)){
                $perfil->empresa=array();
                foreach ($result as $emp) {
                    $empresa=new Empresa;
                    $empresa->id=$emp->id_empresa;
                    $empresa->nombre=$emp->emp_nombre;
                    $empresa->razon=$emp->emp_razon;
                    $empresa->direccion=$emp->emp_direccion;
                    $empresa->cc=null; // No es necesario CC
                    
                    $perfil->empresa[]=$empresa;
                }
            }
            return $perfil;
        }
        return null;
    }
    public function findPerfilN3OrN5Email($usuario){
        $arr=array(
            ':id_usuario'   => $usuario
        );
        //Se busca perfil autorizador (Categorizador Finanzas y Contabilidad).
        //Roles: N3 (Finanzas) y N5 (Contabilidad)
        $result=$this->db->sql_select_one("select id_usuario,usuario,nivel,correo,nombre from ".$this->tbl->cheque_usuario. " where id_usuario=:id_usuario",$arr);
        if(!empty($result)){
            $arr=array(
                ':id_usuario'   => $result->id_usuario
            );
            $perfil=new PerfilUser;
            $perfil->id=$result->id_usuario;
            $perfil->username=$result->usuario;
            $perfil->rol=$result->nivel;
            $perfil->email=$result->correo;
            $perfil->nombre=$result->nombre;
            $perfil->empresa=null;
            $perfil->categoria=null;

            //Empresas disponibles para usuario autorizador
            $result=$this->db->sql_select_one("select e.id_empresa,e.emp_nombre,e.emp_razon,e.emp_direccion from ".$this->tbl->empresa." e inner join ".$this->tbl->cheque_usuario_empresa." u on u.id_empresa=e.id_empresa where u.id_usuario=:id_usuario",$arr);
            if(!empty($result)){
                $empresa=new Empresa;
                $empresa->id=$result->id_empresa;
                $empresa->nombre=$result->emp_nombre;
                $empresa->razon=$result->emp_razon;
                $empresa->direccion=$result->emp_direccion;
                $empresa->cc=null; // No es necesario CC
                
                $perfil->empresa=$empresa;
            }
            return $perfil;
        }
        return null;
    }
    private function findCategoriaUser($id_usuario){
        $arr=array(
            ':id_usuario'   => $id_usuario
        );
        $result=$this->db->sql_select_one("select id_categoria from ".$this->tbl->gestion_categorias." where id_usuario=:id_usuario and gestion_nivel=1",$arr);
        if(!empty($result)){
            return $result->id_categoria;
        }
        return 0;
    }
    private function findPerfilN4($id_usuario){
        $arr=array(
            ':id_usuario'   => $id_usuario
        );
        $result=$this->db->sql_select_one("select u.id_usuario,u.usr_usuario,'N4' nivel,u.usr_email,u.usr_nombre,c.id_categoria from ".$this->tbl->gestion_categorias." c inner join ".$this->tbl->usuario." u on u.id_usuario=c.id_usuario where c.id_usuario=:id_usuario and c.gestion_nivel=1",$arr);
        if(!empty($result)){
            $perfil=new PerfilUser;
            $perfil->id=$result->id_usuario;
            $perfil->username=$result->usr_usuario;
            $perfil->rol=$result->nivel;
            $perfil->email=$result->usr_email;
            $perfil->nombre=$result->usr_nombre;
            $perfil->empresa=null;
            $perfil->categoria=null;
            $perfil->id_categoria = $result->id_categoria;

            //Empresas disponibles para usuario autorizador
            $result=$this->db->sql_select_all(
                    "select e.id_empresa,e.emp_nombre,e.emp_razon,e.emp_direccion,cc.id_cc,cc.cc_codigo,cc.cc_descripcion ".
                    "from ".$this->tbl->empresa." e ".
                    "inner join ".$this->tbl->acc_emp_cc." u ".
                    "on u.id_empresa=e.id_empresa ".
                    "inner join ".$this->tbl->cecosto." cc ".
                    "on u.id_cc=cc.id_cc and u.id_empresa=cc.id_empresa ".
                    "where u.id_usuario=:id_usuario ".
                    "group by e.id_empresa,e.emp_nombre,e.emp_razon,e.emp_direccion,cc.id_cc,cc.cc_codigo,cc.cc_descripcion ".
                    "order by e.id_empresa,cc.id_cc",$arr);
            
            if(!empty($result)){
                $empresas=array();
                $ccs=array();
                $id_empresa=0;
                foreach ($result as $emp) {
                    $cc=new CentroCosto; //Se instancia objeto cc
                    $cc->id=$emp->id_cc;
                    $cc->codigo=$emp->cc_codigo;
                    $cc->nombre=$emp->cc_descripcion;
                    $ccs[]=$cc; // se agrega cc
                    
                    if($id_empresa!=$emp->id_empresa){ //se agrega empresa
                        $empresa=new Empresa; // se prepara empresa
                        $empresa->id=$emp->id_empresa;
                        $empresa->nombre=$emp->emp_nombre;
                        $empresa->razon=$emp->emp_razon;
                        $empresa->direccion=$emp->emp_direccion;
                        $empresa->cc=$ccs; // Se agregan cc a la asignados a la empresa    
                        $empresas[]=$empresa; // se agrega empresa
                        $ccs=array(); // se resetea ccs
                    }
                    $id_empresa=$emp->id_empresa; //se actualiza indicador de cambio de empresa
                }
                $perfil->empresa=$empresas;
            }
            
            //se buscan categorias asignadas al usuario
            $result=$this->db->sql_select_all(
                    "select c.id_categoria,c.nombre_categoria ".
                    "from ".$this->tbl->gestion_categorias." g ".
                    "inner join ".$this->tbl->tipo_categoria." c ".
                    "on g.id_categoria=c.id_categoria ".
                    "where g.id_usuario=:id_usuario and g.gestion_nivel=1",$arr);
            
            if(!empty($result)){
                $categoria=array();
                foreach ($result as $cat) {
                    $cats=new Categoria; // se prepara empresa
                    $cats->id=$cat->id_categoria;
                    $cats->nombre=$cat->nombre_categoria;
                    $categoria[]=$cats; // se agrega empresa
                }
                $perfil->categoria=$categoria;
            }
            return $perfil;
        }
        return null;
    }
    private function findPerfilN1orN2($id_usuario){
        $arr=array(
            ':id_usuario'   => $id_usuario
        );
        $result=$this->db->sql_select_one("select id_usuario,usr_usuario,if(id_rol=999999995,'N2','N1') nivel,usr_email,usr_nombre from ".$this->tbl->usuario." where id_usuario=:id_usuario",$arr);
        if(!empty($result)){
            $perfil=new PerfilUser;
            $perfil->id=$result->id_usuario;
            $perfil->username=$result->usr_usuario;
            $perfil->rol=$result->nivel;
            $perfil->email=$result->usr_email;
            $perfil->nombre=$result->usr_nombre;
            $perfil->empresa=null;
            $perfil->categoria=null;

            //Empresas disponibles para usuario autorizador
            $result=$this->db->sql_select_all(
                    "select e.id_empresa,e.emp_nombre,e.emp_razon,e.emp_direccion,cc.id_cc,cc.cc_codigo,cc.cc_descripcion ".
                    "from ".$this->tbl->empresa." e ".
                    "inner join ".$this->tbl->acc_emp_cc." u ".
                    "on u.id_empresa=e.id_empresa ".
                    "inner join ".$this->tbl->cecosto." cc ".
                    "on u.id_cc=cc.id_cc and u.id_empresa=cc.id_empresa ".
                    "where u.id_usuario=:id_usuario ".
                    "group by e.id_empresa,e.emp_nombre,e.emp_razon,e.emp_direccion,cc.id_cc,cc.cc_codigo,cc.cc_descripcion ".
                    "order by e.id_empresa,cc.id_cc",$arr);
            
            if(!empty($result)){
                $empresas=array();
                $ccs=array();
                $id_empresa=0;
                foreach ($result as $emp) {
                    $cc=new CentroCosto; //Se instancia objeto cc
                    $cc->id=$emp->id_cc;
                    $cc->codigo=$emp->cc_codigo;
                    $cc->nombre=$emp->cc_descripcion;
                    $ccs[]=$cc; // se agrega cc
                    
                    if($id_empresa!=$emp->id_empresa){ //se agrega empresa
                        $empresa=new Empresa; // se prepara empresa
                        $empresa->id=$emp->id_empresa;
                        $empresa->nombre=$emp->emp_nombre;
                        $empresa->razon=$emp->emp_razon;
                        $empresa->direccion=$emp->emp_direccion;
                        $empresa->cc=$ccs; // Se agregan cc a la asignados a la empresa    
                        $empresas[]=$empresa; // se agrega empresa
                        $ccs=array(); // se resetea ccs
                    }
                    $id_empresa=$emp->id_empresa; //se actualiza indicador de cambio de empresa
                }
                $perfil->empresa=$empresas;
            }
            return $perfil;
        }
    }
    function fecha_anio_mes($anio,$mes){
        $fecha = array();
        if(!empty($anio) && !empty($mes)){
            $fecha['fecha_inicial'] = $anio.str_pad($mes, 2,"0",STR_PAD_LEFT)."01";
            $fecha['fecha_final'] = $anio.str_pad($mes, 2,"0",STR_PAD_LEFT)."31";
        }elseif(!empty($anio)){
            $fecha['fecha_inicial'] = $anio."0101";
            $fecha['fecha_final'] = $anio."1231";
        }
        return $fecha;
    }
    public function findSolicitudList($filter=array()){
        $arr=array();
        $qry ="select s.id,s.id_empresa,e.emp_nombre,e.emp_razon,e.emp_direccion,s.id_cc,s.5k is5k,";
        $qry.="cc.cc_codigo,cc.cc_descripcion,s.nombre_beneficiario,s.valor_cheque,s.concepto_pago,";
        $qry.="s.fecha_max_pago,s.status,s.negociable,s.fecha_solicitud,s.hora_solicitud,s.no_copia,";
        $qry.="s.id_usuario,s.avance,s.observacion,ifnull(u.id,0) id_upload,u.filename,";
        $qry.="u.descripcion,u.fecha,u.hora,ifnull(tc.id_categoria,0) id_categoria,tc.nombre_categoria,ifnull(s.devuelta,'') devuelta "; 
        $qry.="from ".$this->tbl->cheque_sol." s ";
        $qry.="inner join ".$this->tbl->cecosto." cc ";
        $qry.="on cc.id_cc=s.id_cc and cc.id_empresa=s.id_empresa ";
        $qry.="inner join ".$this->tbl->empresa." e ";
        $qry.="on e.id_empresa=s.id_empresa ";
        $qry.="left join ".$this->tbl->cheque_upload." u ";
        $qry.="on u.id_solicitud=s.id ";
        $qry.="left join ".$this->tbl->tipo_categoria." tc ";
        $qry.="on tc.id_categoria=s.id_categoria ";
        if(!empty($filter)){
            $wqry=false;
            if(!empty($filter['rol'])){ //Si se ha filtrado por
                if($filter['rol']=='N2'){ //si es Usuario Autorizador
                    if(!empty($filter['id_usuario_rol'])){  //Se filtran empresas y centros de costo de usuario autorizador
                        $arr[':id_usuario_rol']=$filter['id_usuario_rol'];
                        $qry.="inner join ".$this->tbl->acc_emp_cc." ac ";
                        $qry.="on s.id_empresa=ac.id_empresa and s.id_cc=ac.id_cc and ac.id_usuario=:id_usuario_rol "; 
                        if(!empty($filter['id_usuario'])){
                            $arr[':id_usuario']=$filter['id_usuario'];
                            $qry.="where (ac.id_usuario=:id_usuario_rol or s.id_usuario=:id_usuario) and s.avance in ('N2','N3','N4','N5') "; 
                        }else{
                            $qry.="where ac.id_usuario=:id_usuario_rol and s.avance in ('N2','N3','N4','N5') "; 
                        }
                        $wqry=true;
                    }
                }else if($filter['rol']=='N3'){
                    if(!empty($filter['id_usuario_rol'])){  //Se filtran empresas y centros de costo de usuario autorizador
                        $arr[':id_usuario_rol']=$filter['id_usuario_rol'];
                        $qry.="inner join ".$this->tbl->cheque_usuario_empresa." ae ";
                        $qry.="on s.id_empresa=ae.id_empresa "; 
                        if(!empty($filter['id_usuario'])){
                            $arr[':id_usuario']=$filter['id_usuario'];
                            $qry.="where (ae.id_usuario=:id_usuario_rol or s.id_usuario=:id_usuario) and s.avance in ('N3','N4','N5') "; 
                        }else{
                            $qry.="where ae.id_usuario=:id_usuario_rol and s.avance in ('N3','N4','N5') "; 
                        }
                        $wqry=true;
                    }else if(!empty($filter['consultarde'])){
                        $qry.="where s.avance in ('N3','N4','N5') and s.5k=1 and s.id_empresa in (1,2,3,4,5,6,7,8,9,10,11,12,13,14) "; 
                        $wqry=true;
                    }
                }else if($filter['rol']=='N5'){
                    if(!empty($filter['id_usuario_rol'])){  //Se filtran empresas y centros de costo de usuario autorizador
                        $arr[':id_usuario_rol']=$filter['id_usuario_rol'];
                        $qry.="inner join ".$this->tbl->cheque_usuario_empresa." ae ";
                        $qry.="on s.id_empresa=ae.id_empresa "; 
                        if(!empty($filter['id_usuario'])){
                            $arr[':id_usuario']=$filter['id_usuario'];
                            $qry.="where (ae.id_usuario=:id_usuario_rol or s.id_usuario=:id_usuario) and s.avance='N5' "; 
                        }else{
                            $qry.="where ae.id_usuario=:id_usuario_rol and s.avance='N5' "; 
                        }
                        $wqry=true;
                    }
                }else if($filter['rol']=='N4'){
                    $qry.="where s.avance in ('N4','N5') "; 
                    $wqry=true;
                }
                if(!empty($filter['id_usuario']) && $filter['rol']=='N1'){ //Se agrega filtro por usuario
                    $arr[':id_usuario']=$filter['id_usuario'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_usuario=:id_usuario "; 
                    $wqry=true;
                }
                if(!empty($filter['id_empresa'])){  //Se crea filtro por empresa
                    $arr[':id_empresa']=$filter['id_empresa'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_empresa=:id_empresa "; 
                    $wqry=true;
                }
                if(!empty($filter['id_cc'])){   //Se agrega filtro por id_cc
                    $arr[':id_cc']=$filter['id_cc'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_cc=:id_cc "; 
                    $wqry=true;
                }
                if(!empty($filter['f_id_empresa'])){  //Se crea filtro por empresa
                    $arr[':f_id_empresa']=$filter['f_id_empresa'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_empresa=:f_id_empresa "; 
                    $wqry=true;
                }
                if(!empty($filter['f_id_cc'])){   //Se agrega filtro por id_cc
                    $arr[':f_id_cc']=$filter['f_id_cc'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_cc=:f_id_cc "; 
                    $wqry=true;
                }
                if(!empty($filter['f_id_anio'])){
                    $fechas = $this->fecha_anio_mes($filter['f_id_anio'],$filter['f_id_mes']);
                    if(!empty($fechas)){
                        $arr[':fecha_inicial']=$fechas['fecha_inicial'];
                        $arr[':fecha_final']=$fechas['fecha_final'];
                        $qry.=($wqry ? 'and' : 'where')." s.fecha_solicitud>=:fecha_inicial and s.fecha_solicitud<=:fecha_final "; 
                        $wqry=true;
                    }
                }
                if(!empty($filter['consultar'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." s.avance='N5' and s.status='R' "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." s.status='D' "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." s.status!='D' and s.avance!='N5' "; 
                            $wqry=true;
                        }
                    }
                }elseif(!empty($filter['autorizas_cc'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." s.avance in ('N5','N4','N3') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." ((s.status='D' and s.avance='N2') or s.status='D') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." s.status='R' and s.avance='N2' "; 
                            $wqry=true;
                        }
                    }
                }elseif(!empty($filter['autorizar_categoria'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." s.avance='N5' "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." ((s.status='D' and s.avance='N4') or s.status='D') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." s.status='R' and s.avance='N4' "; 
                            $wqry=true;
                        }
                    }
                }elseif(!empty($filter['categorizar'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." (s.avance='N5' or (s.avance='N4' and s.status!='D')) "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." ((s.status='D' and s.avance='N3') or (s.status='D' and s.avance='N4') or s.status='D') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." s.status='R' and s.avance='N3' "; 
                            $wqry=true;
                        }
                    }
                }elseif(!empty($filter['consultarde'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." s.avance='N5' "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." (s.status='W' and s.avance='N3') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." (s.status='Z' and s.avance='N3') "; 
                            $wqry=true;
                        }
                    }
                }
            }
            if(!empty($filter['status'])){
                $arr[':status']=$filter['status'];
                $qry.=($wqry ? 'and' : 'where')." s.status=:status "; 
                $wqry=true;
            }
            if(!empty($filter['avance'])){
                $arr[':avance']=$filter['avance'];
                $qry.=($wqry ? 'and' : 'where')." s.avance=:avance "; 
                $wqry=true;
            }
            if(!empty($filter['id_categoria'])){
                $arr[':id_categoria']=$filter['id_categoria'];
                $qry.=($wqry ? 'and' : 'where')." s.id_categoria=:id_categoria "; 
                $wqry=true;
            }
            if(!empty($filter['no_status'])){
                $arr[':no_status']=$filter['no_status'];
                $qry.=($wqry ? 'and' : 'where')." (s.status!=:no_status or ";
                if(!empty($filter['rol'])){
                    if($filter['rol']=='N2'){
                        $qry.=" s.avance in ('N3','N4','N5')) ";
                    }else if($filter['rol']=='N3'){
                        $qry.=" s.avance in ('N4','N5')) ";
                    }else if($filter['rol']=='N4'){
                        $qry.=" s.avance in ('N5')) ";
                    }
                }
                $wqry=true;
            }
            if(!empty($filter['order'])){
                if(strtolower($filter['order'])=='asc'){
                    $qry.="order by s.id asc ";
                }else{ //por defecto
                    $qry.="order by s.id desc ";
                }
            }else{ //por defecto
                $qry.="order by s.id desc ";
            }
            if(!empty($filter['pag'])){
                if(is_numeric($filter['pag'])){
                    $elto=(((int)$filter['pag'])*$this->page_elto);
                    if((int)$filter['pag']>0){
                        $qry.="limit $elto,".$this->page_elto;
                    }else{ // por defecto 10 items
                        $qry.="limit 0,".$this->page_elto;
                    }
                }else{ // por defecto 10 items
                    $qry.="limit 0,".$this->page_elto;
                }
            }else{ // por defecto 10 items
                $qry.="limit 0,".$this->page_elto;
            }
        }
        $result=$this->db->sql_select_all($qry,$arr);
        if(!empty($result)){
            $arr_data=array();
            foreach ($result as $ss) {
                $sol=new Solicitud();
                $sol->empresa=new Empresa;
                $sol->empresa->cc=new CentroCosto;
                
                $sol->id=$ss->id;
				        $sol->is5k=$ss->is5k;
                
                $sol->empresa->id=$ss->id_empresa;
                $sol->empresa->nombre=$ss->emp_nombre;
                $sol->empresa->razon=$ss->emp_razon;
                $sol->empresa->direccion=$ss->emp_direccion;
                
                $sol->empresa->cc->id=$ss->id_cc;
                $sol->empresa->cc->codigo=$ss->cc_codigo;
                $sol->empresa->cc->nombre=$ss->cc_descripcion;
                
                $sol->nombre_beneficiario=$ss->nombre_beneficiario;
                $sol->valor_cheque=$ss->valor_cheque;
                $sol->concepto_pago=$ss->concepto_pago;
                $sol->fecha_max_pago=Form::IntegerToDate($ss->fecha_max_pago);
                $sol->status=$ss->status;
                $sol->negociable=$ss->negociable;
                $sol->fecha=Form::IntegerToDate($ss->fecha_solicitud);
                $sol->hora=Form::IntegerToTime($ss->hora_solicitud);
                
                $sol->no_copia=$ss->no_copia;
                $sol->id_usuario=$ss->id_usuario;
                $sol->avance=$ss->avance;
                $sol->observacion=$ss->observacion;
                $sol->devuelta = $ss->devuelta;
                
                if($ss->id_upload>0){
                    $sol->file=new File;
                    $sol->file->id=$ss->id_upload;
                    $sol->file->id_solicitud=$ss->id;
                    $sol->file->descripcion=$ss->descripcion;
                    $sol->file->filename=$ss->filename;
                    $sol->file->fecha=Form::IntegerToDate($ss->fecha);
                    $sol->file->hora=Form::IntegerToTime($ss->hora);
                }else{
                    $sol->file=null;
                }
                if($ss->id_categoria>0){
                    $sol->categoria=new Categoria;
                    $sol->categoria->id=$ss->id_categoria;
                    $sol->categoria->nombre=$ss->nombre_categoria;
                }else{
                    $sol->categoria=null;
                }
                $arr_data[]=$sol;
            }
            return $arr_data;
        }
        return null;
    }
    public function findSolicitudReport($filter=array()){
        $arr=array();
        $qry ="select s.id,s.5k is5k,s.id_empresa,e.emp_nombre,e.emp_razon,e.emp_direccion,s.id_cc,";
        $qry.="cc.cc_codigo,cc.cc_descripcion,s.nombre_beneficiario,s.valor_cheque,s.concepto_pago,";
        $qry.="s.fecha_max_pago,s.status,s.negociable,s.fecha_solicitud,s.hora_solicitud,s.no_copia,";
        $qry.="s.id_usuario,s.avance,s.observacion,";
        $qry.="ifnull(tc.id_categoria,0) id_categoria,ifnull(tc.nombre_categoria,'') nombre_categoria,ifnull(u.usr_nombre,'** ELIMINADO **') usuario "; 
        $qry.="from ".$this->tbl->cheque_sol." s ";
        $qry.="inner join ".$this->tbl->cecosto." cc ";
        $qry.="on cc.id_cc=s.id_cc and cc.id_empresa=s.id_empresa ";
        $qry.="inner join ".$this->tbl->empresa." e ";
        $qry.="on e.id_empresa=s.id_empresa ";
        $qry.="left join ".$this->tbl->usuario." u ";
        $qry.="on u.id_usuario=s.id_usuario ";
        $qry.="left join ".$this->tbl->tipo_categoria." tc ";
        $qry.="on tc.id_categoria=s.id_categoria ";

        $where = false;
        if(!empty($filter[':id_empresa'])){
            $qry.="where s.id_empresa=:id_empresa ";            
            $where = true;
            $arr[':id_empresa']=$filter[':id_empresa'];
        }

        if(!empty($filter[':fecha_inicial'])){
            $qry.= (!$where ? "where " : "and ")." s.fecha_solicitud>=:fecha_inicial ";            
            $where = true;
            $arr[':fecha_inicial']=$filter[':fecha_inicial'];
        }

        if(!empty($filter[':fecha_final'])){
            $qry.= (!$where ? "where " : "and ")." s.fecha_solicitud<=:fecha_final ";            
            $where = true;
            $arr[':fecha_final']=$filter[':fecha_final'];
        }

        if(empty($filter[':incluir_desistida'])){
            $qry.= (!$where ? "where " : "and ")." s.status!='D' ";            
        }
        $qry.= "order by s.fecha_solicitud,s.hora_solicitud"; 

        $result=$this->db->sql_select_all($qry,$arr);
        if(!empty($result)){
            $arr_data=array();
            foreach ($result as $ss) {
                $sol=new Solicitud();
                $sol->empresa=new Empresa;
                $sol->empresa->cc=new CentroCosto;
                
                $sol->id=$ss->id;
				$sol->is5k = $ss->is5k;
                $sol->name_usuario=$ss->usuario;
                
                $sol->empresa->id=$ss->id_empresa;
                $sol->empresa->nombre=$ss->emp_nombre;
                $sol->empresa->razon=$ss->emp_razon;
                $sol->empresa->direccion=$ss->emp_direccion;
                
                $sol->empresa->cc->id=$ss->id_cc;
                $sol->empresa->cc->codigo=$ss->cc_codigo;
                $sol->empresa->cc->nombre=$ss->cc_descripcion;
                
                $sol->nombre_beneficiario=$ss->nombre_beneficiario;
                $sol->valor_cheque=$ss->valor_cheque;
                $sol->concepto_pago=$ss->concepto_pago;
                $sol->fecha_max_pago=Form::IntegerToDate($ss->fecha_max_pago);
                $sol->status=$ss->status;
                $sol->negociable=$ss->negociable;
                $sol->fecha=Form::IntegerToDate($ss->fecha_solicitud);
                $sol->hora=Form::IntegerToTime($ss->hora_solicitud);
                
                $sol->no_copia=$ss->no_copia;
                $sol->id_usuario=$ss->id_usuario;
                $sol->avance=$ss->avance;
                $sol->observacion=$ss->observacion;
                
                $sol->file=null;
                $sol->categoria=null;

                if($ss->id_categoria>0){
                    $sol->categoria=new Categoria;
                    $sol->categoria->id=$ss->id_categoria;
                    $sol->categoria->nombre=$ss->nombre_categoria;
                }

                $arr_data[]=$sol;
            }
            return $arr_data;
        }
        return null;
    }
public function findSolicitudReportCC($filter=array()){
        $arr=array();
        $qry ="select s.id,s.5k is5k,s.id_empresa,e.emp_nombre,e.emp_razon,e.emp_direccion,s.id_cc,";
        $qry.="cc.cc_codigo,cc.cc_descripcion,s.nombre_beneficiario,s.valor_cheque,s.concepto_pago,";
        $qry.="s.fecha_max_pago,s.status,s.negociable,s.fecha_solicitud,s.hora_solicitud,s.no_copia,";
        $qry.="s.id_usuario,s.avance,s.observacion,";
        $qry.="ifnull(tc.id_categoria,0) id_categoria,ifnull(tc.nombre_categoria,'') nombre_categoria,ifnull(u.usr_nombre,'** ELIMINADO **') usuario, ifnull(up.filename,'') adjunto, s.id_categoria id_categoria2  "; 
        $qry.="from ".$this->tbl->cheque_sol." s ";
        $qry.="inner join ".$this->tbl->cecosto." cc ";
        $qry.="on cc.id_cc=s.id_cc and cc.id_empresa=s.id_empresa ";
        $qry.="inner join ".$this->tbl->empresa." e ";
        $qry.="on e.id_empresa=s.id_empresa ";
        $qry.="left join ".$this->tbl->usuario." u ";
        $qry.="on u.id_usuario=s.id_usuario ";
        $qry.="left join ".$this->tbl->tipo_categoria." tc ";
        $qry.="on tc.id_categoria=s.id_categoria ";
        $qry.="left join cheque_upload up ";
        $qry.="on up.id_solicitud=s.id ";

        $sql2= "SELECT s.id, ifnull(s.id_categoria,0) id_categoria, case when s.id_categoria = 1000 then 'APROBACION AUTOMATICA' else ifnull(c.nombre_categoria,'') end nombre_categoria, ifnull(cs.fecha,'') fecha FROM cheque_sol s LEFT JOIN tipo_categoria c ON s.id_categoria = c.id_categoria LEFT JOIN cheque_seguimiento cs ON s.id = cs.id_solicitud AND cs.avance = 'N4' AND cs.nivel='5' ";

        $where = false;
        if(!empty($filter[':id_empresa'])){
            $qry.="where s.id_empresa=:id_empresa "; 
            $sql2.="where s.id_empresa=:id_empresa ";          
            $where = true;
            $arr[':id_empresa']=$filter[':id_empresa'];
        }

        if(!empty($filter[':fecha_inicial'])){
            $qry.= (!$where ? "where " : "and ")." s.fecha_solicitud>=:fecha_inicial ";
            $sql2.= (!$where ? "where " : "and ")." s.fecha_solicitud>=:fecha_inicial ";           
            $where = true;
            $arr[':fecha_inicial']=$filter[':fecha_inicial'];
        }

        if(!empty($filter[':fecha_final'])){
            $qry.= (!$where ? "where " : "and ")." s.fecha_solicitud<=:fecha_final ";
            $sql2.= (!$where ? "where " : "and ")." s.fecha_solicitud<=:fecha_final ";            
            $where = true;
            $arr[':fecha_final']=$filter[':fecha_final'];
        }

        if(empty($filter[':incluir_desistida'])){
            $qry.= (!$where ? "where " : "and ")." s.status!='D' ";
            $sql2.= (!$where ? "where " : "and ")." s.status!='D' ";          
        }

        $qry.= "order by s.fecha_solicitud,s.hora_solicitud";
        $sql2.="ORDER BY s.fecha_solicitud, s.hora_solicitud";      

        $result=$this->db->sql_select_all($qry,$arr);
        $result2=$this->db->sql_select_all($sql2,$arr);

        $result=$this->db->sql_select_all($qry,$arr);
        if(!empty($result)){
            $arr_data=array();
            foreach ($result as $ss) {
                $sol=new Solicitud();
                $sol->empresa=new Empresa;
                $sol->empresa->cc=new CentroCosto;
                
                $sol->id=$ss->id;
        $sol->is5k = $ss->is5k;
                $sol->name_usuario=$ss->usuario;
                
                $sol->empresa->id=$ss->id_empresa;
                $sol->empresa->nombre=$ss->emp_nombre;
                $sol->empresa->razon=$ss->emp_razon;
                $sol->empresa->direccion=$ss->emp_direccion;
                
                $sol->empresa->cc->id=$ss->id_cc;
                $sol->empresa->cc->codigo=$ss->cc_codigo;
                $sol->empresa->cc->nombre=$ss->cc_descripcion;
                
                $sol->nombre_beneficiario=$ss->nombre_beneficiario;
                $sol->valor_cheque=$ss->valor_cheque;
                $sol->concepto_pago=$ss->concepto_pago;
                $sol->fecha_max_pago=Form::IntegerToDate($ss->fecha_max_pago);
                $sol->status=$ss->status;
                $sol->negociable=$ss->negociable;
                $sol->fecha=Form::IntegerToDate($ss->fecha_solicitud);
                $sol->hora=Form::IntegerToTime($ss->hora_solicitud);
                
                $sol->no_copia=$ss->no_copia;
                $sol->id_usuario=$ss->id_usuario;
                $sol->avance=$ss->avance;
                $sol->observacion=$ss->observacion;
                $sol->adjunto = $ss->adjunto;
                
                $sol->file=null;
                $sol->categoria=null;

                /*
                if($ss->id_categoria>0){
                    $sol->categoria=new Categoria;
                    $sol->categoria->id=$ss->id_categoria;
                    $sol->categoria->nombre=$ss->nombre_categoria;
                }
                */
                
                if ($ss->id_categoria2>0) {
                    foreach ($result2 as $ss2) {
                        if ($ss->id == $ss2->id) {
                            $sol->categoria = (object) array(
                                'id' => $ss2->id_categoria,
                                'nombre' => $ss2->nombre_categoria,
                                'fecha' => Form::IntegerToDate($ss2->fecha)
                            );
                        }
                    }
                }

                $arr_data[]=$sol;
            }
            return $arr_data;
        }
        return null;
    }

    public function findSolicitudReportCount($filter=array()){
        $arr=array();
        $qry ="select count(*) contador "; 
        $qry.="from ".$this->tbl->cheque_sol." s ";
        $qry.="inner join ".$this->tbl->cecosto." cc ";
        $qry.="on cc.id_cc=s.id_cc and cc.id_empresa=s.id_empresa ";
        $qry.="inner join ".$this->tbl->empresa." e ";
        $qry.="on e.id_empresa=s.id_empresa ";
        $qry.="left join ".$this->tbl->usuario." u ";
        $qry.="on u.id_usuario=s.id_usuario ";
        $qry.="left join ".$this->tbl->tipo_categoria." tc ";
        $qry.="on tc.id_categoria=s.id_categoria ";

        $where = false;
        if(!empty($filter[':id_empresa'])){
            $qry.="where s.id_empresa=:id_empresa ";            
            $where = true;
            $arr[':id_empresa']=$filter[':id_empresa'];
        }

        if(!empty($filter[':fecha_inicial'])){
            $qry.= (!$where ? "where " : "and ")." s.fecha_solicitud>=:fecha_inicial ";            
            $where = true;
            $arr[':fecha_inicial']=$filter[':fecha_inicial'];
        }

        if(!empty($filter[':fecha_final'])){
            $qry.= (!$where ? "where " : "and ")." s.fecha_solicitud<=:fecha_final ";            
            $where = true;
            $arr[':fecha_final']=$filter[':fecha_final'];
        }

        if(empty($filter[':incluir_desistida'])){
            $qry.= (!$where ? "where " : "and ")." s.status!='D' ";            
        }
        $result = $this->db->sql_select_one($qry,$arr);
        if(!empty($result)){
            return (!empty($result->contador) ? (int)$result->contador : 0);
        }
        return 0;
    }
    public function findSolicitudListCount__($filter=array()){
        $arr=array();
        $qry ="select count(*) contador ";
        $qry.="from ".$this->tbl->cheque_sol." s ";
        $qry.="inner join ".$this->tbl->cecosto." cc ";
        $qry.="on cc.id_cc=s.id_cc and cc.id_empresa=s.id_empresa ";
        $qry.="inner join ".$this->tbl->empresa." e ";
        $qry.="on e.id_empresa=s.id_empresa ";
        $qry.="left join ".$this->tbl->cheque_upload." u ";
        $qry.="on u.id_solicitud=s.id ";
        $qry.="left join ".$this->tbl->tipo_categoria." tc ";
        $qry.="on tc.id_categoria=s.id_categoria ";
        if(!empty($filter)){
            $wqry=false;
            if(!empty($filter['rol'])){ //Si se ha filtrado por
                if($filter['rol']=='N2'){ //si es Usuario Autorizador
                    if(!empty($filter['id_usuario_rol'])){  //Se filtran empresas y centros de costo de usuario autorizador
                        $arr[':id_usuario_rol']=$filter['id_usuario_rol'];
                        $qry.="inner join ".$this->tbl->acc_emp_cc." ac ";
                        $qry.="on s.id_empresa=ac.id_empresa and s.id_cc=ac.id_cc and ac.id_usuario=:id_usuario_rol "; 
                        if(!empty($filter['id_usuario'])){
                            $arr[':id_usuario']=$filter['id_usuario'];
                            $qry.="where (ac.id_usuario=:id_usuario_rol or s.id_usuario=:id_usuario) and s.avance in ('N2','N3','N4','N5') "; 
                        }else{
                            $qry.="where ac.id_usuario=:id_usuario_rol and s.avance in ('N2','N3','N4','N5') "; 
                        }
                        $wqry=true;
                    }
                }else if($filter['rol']=='N3'){
                    if(!empty($filter['id_usuario_rol'])){  //Se filtran empresas y centros de costo de usuario autorizador
                        $arr[':id_usuario_rol']=$filter['id_usuario_rol'];
                        $qry.="inner join ".$this->tbl->cheque_usuario_empresa." ae ";
                        $qry.="on s.id_empresa=ae.id_empresa "; 
                        if(!empty($filter['id_usuario'])){
                            $arr[':id_usuario']=$filter['id_usuario'];
                            $qry.="where (ae.id_usuario=:id_usuario_rol or s.id_usuario=:id_usuario) and s.avance in ('N3','N4','N5') "; 
                        }else{
                            $qry.="where ae.id_usuario=:id_usuario_rol and s.avance in ('N3','N4','N5') "; 
                        }
                        $wqry=true;
                    }else if(!empty($filter['consultarde'])){
                        //$arr[':id_usuario_rol']=$filter['id_usuario'];
                        //$qry.="inner join ".$this->tbl->cheque_usuario_empresa." ae ";
                        //$qry.="on s.id_empresa=ae.id_empresa "; 
                        //$qry.="where ae.id_usuario=:id_usuario_rol and s.avance in ('N3','N4','N5') and s.5k=1 "; 

                        //Cambio realizado 28/Jun/2021
                        //$arr[':id_usuario_rol']=$filter['id_usuario'];
                        //$qry.="inner join ".$this->tbl->cheque_usuario_empresa." ae ";
                        //$qry.="on s.id_empresa=ae.id_empresa "; 
                        $qry.="where s.avance in ('N3','N4','N5') and s.5k=1 "; 

                        $wqry=true;
                    }
                }else if($filter['rol']=='N5'){
                    if(!empty($filter['id_usuario_rol'])){  //Se filtran empresas y centros de costo de usuario autorizador
                        $arr[':id_usuario_rol']=$filter['id_usuario_rol'];
                        $qry.="inner join ".$this->tbl->cheque_usuario_empresa." ae ";
                        $qry.="on s.id_empresa=ae.id_empresa "; 
                        if(!empty($filter['id_usuario'])){
                            $arr[':id_usuario']=$filter['id_usuario'];
                            $qry.="where (ae.id_usuario=:id_usuario_rol or s.id_usuario=:id_usuario) and s.avance='N5' "; 
                        }else{
                            $qry.="where ae.id_usuario=:id_usuario_rol and s.avance='N5' "; 
                        }
                        $wqry=true;
                    }
                }else if($filter['rol']=='N4'){
                    $qry.="where s.avance in ('N4','N5') "; 
                    $wqry=true;
                }
                if(!empty($filter['id_usuario']) && $filter['rol']=='N1'){ //Se agrega filtro por usuario
                    $arr[':id_usuario']=$filter['id_usuario'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_usuario=:id_usuario "; 
                    $wqry=true;
                }
                if(!empty($filter['id_empresa'])){  //Se crea filtro por empresa
                    $arr[':id_empresa']=$filter['id_empresa'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_empresa=:id_empresa "; 
                    $wqry=true;
                }
                if(!empty($filter['id_cc'])){   //Se agrega filtro por id_cc
                    $arr[':id_cc']=$filter['id_cc'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_cc=:id_cc "; 
                    $wqry=true;
                }
                if(!empty($filter['f_id_empresa'])){  //Se crea filtro por empresa
                    $arr[':f_id_empresa']=$filter['f_id_empresa'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_empresa=:f_id_empresa "; 
                    $wqry=true;
                }
                if(!empty($filter['f_id_cc'])){   //Se agrega filtro por id_cc
                    $arr[':f_id_cc']=$filter['f_id_cc'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_cc=:f_id_cc "; 
                    $wqry=true;
                }
                if(!empty($filter['f_id_anio'])){
                    $fechas = $this->fecha_anio_mes($filter['f_id_anio'],$filter['f_id_mes']);
                    if(!empty($fechas)){
                        $arr[':fecha_inicial']=$fechas['fecha_inicial'];
                        $arr[':fecha_final']=$fechas['fecha_final'];
                        $qry.=($wqry ? 'and' : 'where')." s.fecha_solicitud>=:fecha_inicial and s.fecha_solicitud<=:fecha_final "; 
                        $wqry=true;
                    }
                }
                if(!empty($filter['consultar'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." s.avance='N5' and s.status='R' "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." s.status='D' "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." s.status!='D' and s.avance!='N5' "; 
                            $wqry=true;
                        }
                    }
                }elseif(!empty($filter['autorizas_cc'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." s.avance in ('N5','N4','N3') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." ((s.status='D' and s.avance='N2') or s.status='D') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." s.status='R' and s.avance='N2' "; 
                            $wqry=true;
                        }
                    }
                }elseif(!empty($filter['autorizar_categoria'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." s.avance='N5' "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." ((s.status='D' and s.avance='N4') or s.status='D') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." s.status='R' and s.avance='N4' "; 
                            $wqry=true;
                        }
                    }
                }elseif(!empty($filter['categorizar'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." (s.avance='N5' or (s.avance='N4' and s.status!='D')) "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." ((s.status='D' and s.avance='N3') or (s.status='D' and s.avance='N4') or s.status='D') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." s.status='R' and s.avance='N3' "; 
                            $wqry=true;
                        }
                    }
                }elseif(!empty($filter['consultarde'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." s.avance='N5' "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." (s.status='W' and s.avance='N3') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." (s.status='Z' and s.avance='N3') "; 
                            $wqry=true;
                        }
                    }
                }
            }
            if(!empty($filter['status'])){
                $arr[':status']=$filter['status'];
                $qry.=($wqry ? 'and' : 'where')." s.status=:status "; 
                $wqry=true;
            }
            if(!empty($filter['avance'])){
                $arr[':avance']=$filter['avance'];
                $qry.=($wqry ? 'and' : 'where')." s.avance=:avance "; 
                $wqry=true;
            }
            if(!empty($filter['id_categoria'])){
                $arr[':id_categoria']=$filter['id_categoria'];
                $qry.=($wqry ? 'and' : 'where')." s.id_categoria=:id_categoria "; 
                $wqry=true;
            }
            if(!empty($filter['no_status'])){
                $arr[':no_status']=$filter['no_status'];
                $qry.=($wqry ? 'and' : 'where')." (s.status!=:no_status or ";
                if(!empty($filter['rol'])){
                    if($filter['rol']=='N2'){
                        $qry.=" s.avance in ('N3','N4','N5')) ";
                    }else if($filter['rol']=='N3'){
                        $qry.=" s.avance in ('N4','N5')) ";
                    }else if($filter['rol']=='N4'){
                        $qry.=" s.avance in ('N5')) ";
                    }
                }
                $wqry=true;
            }
        }
        $result=$this->db->sql_select_one($qry,$arr);
        if(!empty($result)){
            return (!empty($result->contador) ? (int)$result->contador : 0);
        }
        return 0;
    }
    public function findSolicitudListCount($filter=array()){
        $arr=array();
        $qry ="select count(*) contador ";
        $qry.="from ".$this->tbl->cheque_sol." s ";
        $qry.="inner join ".$this->tbl->cecosto." cc ";
        $qry.="on cc.id_cc=s.id_cc and cc.id_empresa=s.id_empresa ";
        $qry.="inner join ".$this->tbl->empresa." e ";
        $qry.="on e.id_empresa=s.id_empresa ";
        $qry.="left join ".$this->tbl->cheque_upload." u ";
        $qry.="on u.id_solicitud=s.id ";
        $qry.="left join ".$this->tbl->tipo_categoria." tc ";
        $qry.="on tc.id_categoria=s.id_categoria ";
        if(!empty($filter)){
            $wqry=false;
            if(!empty($filter['rol'])){ //Si se ha filtrado por
                if($filter['rol']=='N2'){ //si es Usuario Autorizador
                    if(!empty($filter['id_usuario_rol'])){  //Se filtran empresas y centros de costo de usuario autorizador
                        $arr[':id_usuario_rol']=$filter['id_usuario_rol'];
                        $qry.="inner join ".$this->tbl->acc_emp_cc." ac ";
                        $qry.="on s.id_empresa=ac.id_empresa and s.id_cc=ac.id_cc and ac.id_usuario=:id_usuario_rol "; 
                        if(!empty($filter['id_usuario'])){
                            $arr[':id_usuario']=$filter['id_usuario'];
                            $qry.="where (ac.id_usuario=:id_usuario_rol or s.id_usuario=:id_usuario) and s.avance in ('N2','N3','N4','N5') "; 
                        }else{
                            $qry.="where ac.id_usuario=:id_usuario_rol and s.avance in ('N2','N3','N4','N5') "; 
                        }
                        $wqry=true;
                    }
                }else if($filter['rol']=='N3'){
                    if(!empty($filter['id_usuario_rol'])){  //Se filtran empresas y centros de costo de usuario autorizador
                        $arr[':id_usuario_rol']=$filter['id_usuario_rol'];
                        $qry.="inner join ".$this->tbl->cheque_usuario_empresa." ae ";
                        $qry.="on s.id_empresa=ae.id_empresa "; 
                        if(!empty($filter['id_usuario'])){
                            $arr[':id_usuario']=$filter['id_usuario'];
                            $qry.="where (ae.id_usuario=:id_usuario_rol or s.id_usuario=:id_usuario) and s.avance in ('N3','N4','N5') "; 
                        }else{
                            $qry.="where ae.id_usuario=:id_usuario_rol and s.avance in ('N3','N4','N5') "; 
                        }
                        $wqry=true;
                    }else if(!empty($filter['consultarde'])){
                        $arr[':id_usuario_rol']=$filter['id_usuario'];
                        $qry.="inner join ".$this->tbl->cheque_usuario_empresa." ae ";
                        $qry.="on s.id_empresa=ae.id_empresa "; 
                        $qry.="where ae.id_usuario=:id_usuario_rol and s.avance in ('N3','N4','N5') and s.5k=1 "; 
                        $wqry=true;
                    }
                }else if($filter['rol']=='N5'){
                    if(!empty($filter['id_usuario_rol'])){  //Se filtran empresas y centros de costo de usuario autorizador
                        $arr[':id_usuario_rol']=$filter['id_usuario_rol'];
                        $qry.="inner join ".$this->tbl->cheque_usuario_empresa." ae ";
                        $qry.="on s.id_empresa=ae.id_empresa "; 
                        if(!empty($filter['id_usuario'])){
                            $arr[':id_usuario']=$filter['id_usuario'];
                            $qry.="where (ae.id_usuario=:id_usuario_rol or s.id_usuario=:id_usuario) and s.avance='N5' "; 
                        }else{
                            $qry.="where ae.id_usuario=:id_usuario_rol and s.avance='N5' "; 
                        }
                        $wqry=true;
                    }
                }else if($filter['rol']=='N4'){
                    $qry.="where s.avance in ('N4','N5') "; 
                    $wqry=true;
                }
                if(!empty($filter['id_usuario']) && $filter['rol']=='N1'){ //Se agrega filtro por usuario
                    $arr[':id_usuario']=$filter['id_usuario'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_usuario=:id_usuario "; 
                    $wqry=true;
                }
                if(!empty($filter['id_empresa'])){  //Se crea filtro por empresa
                    $arr[':id_empresa']=$filter['id_empresa'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_empresa=:id_empresa "; 
                    $wqry=true;
                }
                if(!empty($filter['id_cc'])){   //Se agrega filtro por id_cc
                    $arr[':id_cc']=$filter['id_cc'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_cc=:id_cc "; 
                    $wqry=true;
                }
                if(!empty($filter['f_id_empresa'])){  //Se crea filtro por empresa
                    $arr[':f_id_empresa']=$filter['f_id_empresa'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_empresa=:f_id_empresa "; 
                    $wqry=true;
                }
                if(!empty($filter['f_id_cc'])){   //Se agrega filtro por id_cc
                    $arr[':f_id_cc']=$filter['f_id_cc'];
                    $qry.=($wqry ? 'and' : 'where')." s.id_cc=:f_id_cc "; 
                    $wqry=true;
                }
                if(!empty($filter['f_id_anio'])){
                    $fechas = $this->fecha_anio_mes($filter['f_id_anio'],$filter['f_id_mes']);
                    if(!empty($fechas)){
                        $arr[':fecha_inicial']=$fechas['fecha_inicial'];
                        $arr[':fecha_final']=$fechas['fecha_final'];
                        $qry.=($wqry ? 'and' : 'where')." s.fecha_solicitud>=:fecha_inicial and s.fecha_solicitud<=:fecha_final "; 
                        $wqry=true;
                    }
                }
                if(!empty($filter['consultar'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." s.avance='N5' and s.status='R' "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." s.status='D' "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." s.status!='D' and s.avance!='N5' "; 
                            $wqry=true;
                        }
                    }
                }elseif(!empty($filter['autorizas_cc'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." s.avance in ('N5','N4','N3') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." ((s.status='D' and s.avance='N2') or s.status='D') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." s.status='R' and s.avance='N2' "; 
                            $wqry=true;
                        }
                    }
                }elseif(!empty($filter['autorizar_categoria'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." s.avance='N5' "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." ((s.status='D' and s.avance='N4') or s.status='D') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." s.status='R' and s.avance='N4' "; 
                            $wqry=true;
                        }
                    }
                }elseif(!empty($filter['categorizar'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." (s.avance='N5' or (s.avance='N4' and s.status!='D')) "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." ((s.status='D' and s.avance='N3') or (s.status='D' and s.avance='N4') or s.status='D') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." s.status='R' and s.avance='N3' "; 
                            $wqry=true;
                        }
                    }
                }elseif(!empty($filter['consultarde'])){ //Filtro de estatus
                    if(!empty($filter['f_id_estado'])){   //Se agrega filtro por id_cc
                        if($filter['f_id_estado']=="true"){
                            $qry.=($wqry ? 'and' : 'where')." s.avance='N5' "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="false"){
                            $qry.=($wqry ? 'and' : 'where')." (s.status='W' and s.avance='N3') "; 
                            $wqry=true;
                        }elseif($filter['f_id_estado']=="start"){
                            $qry.=($wqry ? 'and' : 'where')." (s.status='Z' and s.avance='N3') "; 
                            $wqry=true;
                        }
                    }
                }
            }
            if(!empty($filter['status'])){
                $arr[':status']=$filter['status'];
                $qry.=($wqry ? 'and' : 'where')." s.status=:status "; 
                $wqry=true;
            }
            if(!empty($filter['avance'])){
                $arr[':avance']=$filter['avance'];
                $qry.=($wqry ? 'and' : 'where')." s.avance=:avance "; 
                $wqry=true;
            }
            if(!empty($filter['id_categoria'])){
                $arr[':id_categoria']=$filter['id_categoria'];
                $qry.=($wqry ? 'and' : 'where')." s.id_categoria=:id_categoria "; 
                $wqry=true;
            }
            if(!empty($filter['no_status'])){
                $arr[':no_status']=$filter['no_status'];
                $qry.=($wqry ? 'and' : 'where')." (s.status!=:no_status or ";
                if(!empty($filter['rol'])){
                    if($filter['rol']=='N2'){
                        $qry.=" s.avance in ('N3','N4','N5')) ";
                    }else if($filter['rol']=='N3'){
                        $qry.=" s.avance in ('N4','N5')) ";
                    }else if($filter['rol']=='N4'){
                        $qry.=" s.avance in ('N5')) ";
                    }
                }
                $wqry=true;
            }
        }
        $result=$this->db->sql_select_one($qry,$arr);
        if(!empty($result)){
            return (!empty($result->contador) ? (int)$result->contador : 0);
        }
        return 0;
    }
    public function findCategoriaList(){
        return $this->db->sql_select_all('select * from '.$this->tbl->tipo_categoria.' order by nombre_categoria');
    }
    public function findSolicitud($id_solicitud){
        $arr=array();
        $qry ="select s.id,s.id_empresa,e.emp_nombre,e.emp_razon,s.5k is5k,e.emp_direccion,s.id_cc,s.moneda,";
        $qry.="cc.cc_codigo,cc.cc_descripcion,s.nombre_beneficiario,s.valor_cheque,s.concepto_pago,";
        $qry.="s.fecha_max_pago,s.status,s.negociable,s.fecha_solicitud,s.hora_solicitud,s.no_copia,";
        $qry.="s.id_usuario,s.avance,s.observacion,ifnull(u.id,0) id_upload,u.filename,";
        $qry.="u.descripcion,u.fecha,u.hora,ifnull(s.id_categoria,0) id_categoria,ifnull(tc.nombre_categoria,'') nombre_categoria,ifnull(s.devuelta,'') devuelta, "; 
        $qry.="ifnull(tu.id_usuario,0) id_usuario_usr,tu.usr_usuario "; 
        $qry.="from ".$this->tbl->cheque_sol." s ";
        $qry.="inner join ".$this->tbl->cecosto." cc ";
        $qry.="on cc.id_cc=s.id_cc and cc.id_empresa=s.id_empresa ";
        $qry.="inner join ".$this->tbl->empresa." e ";
        $qry.="on e.id_empresa=s.id_empresa ";
        $qry.="left join ".$this->tbl->cheque_upload." u ";
        $qry.="on u.id_solicitud=s.id ";
        $qry.="left join ".$this->tbl->tipo_categoria." tc ";
        $qry.="on tc.id_categoria=s.id_categoria ";
        $qry.="left join ".$this->tbl->usuario." tu ";
        $qry.="on s.id_usuario=tu.id_usuario ";
        $arr[':id']=$id_solicitud;
        $qry.="where s.id=:id "; 
        
        $result=$this->db->sql_select_one($qry,$arr);
        if(!empty($result)){
            $ss=$result;
            $sol=new Solicitud();
            $sol->empresa=new Empresa;
            $sol->empresa->cc=new CentroCosto;

            $sol->id=$ss->id;
            $sol->is5k=$ss->is5k;
			$sol->moneda=$ss->moneda;

            $sol->empresa->id=$ss->id_empresa;
            $sol->empresa->nombre=$ss->emp_nombre;
            $sol->empresa->razon=$ss->emp_razon;
            $sol->empresa->direccion=$ss->emp_direccion;

            $sol->empresa->cc->id=$ss->id_cc;
            $sol->empresa->cc->codigo=$ss->cc_codigo;
            $sol->empresa->cc->nombre=$ss->cc_descripcion;

            $sol->nombre_beneficiario=$ss->nombre_beneficiario;
            $sol->valor_cheque=$ss->valor_cheque;
			$monedas = ($sol->moneda=='$' ? 'DOLARES' : ($sol->moneda=='L' ? 'LEMPIRAS' : 'CORDOBAS'));
            $moneda = ($sol->moneda=='$' ? 'DOLAR' : ($sol->moneda=='L' ? 'LEMPIRA' : 'CORDOBA'));
            $sol->valor1_text=Form::numtoletras($ss->valor_cheque,$monedas,$moneda);
            $sol->valor2_text=Form::numtonumber2($ss->valor_cheque,$sol->moneda);
            $sol->concepto_pago=$ss->concepto_pago;
            $sol->fecha_max_pago=Form::IntegerToDate($ss->fecha_max_pago);
            $sol->status=$ss->status;
            $sol->negociable=$ss->negociable;
            $sol->fecha=Form::IntegerToDate($ss->fecha_solicitud);
            $sol->hora=Form::IntegerToTime($ss->hora_solicitud);
            $sol->devuelta = $ss->devuelta;

            $sol->no_copia=$ss->no_copia;
            $sol->id_usuario=$ss->id_usuario;
            $sol->avance=$ss->avance;
            $sol->observacion=$ss->observacion;

            if($ss->id_upload>0){
                $sol->file=new File;
                $sol->file->id=$ss->id_upload;
                $sol->file->id_solicitud=$ss->id;
                $sol->file->descripcion=$ss->descripcion;
                $sol->file->filename=$ss->filename;
                $sol->file->fecha=Form::IntegerToDate($ss->fecha);
                $sol->file->hora=Form::IntegerToTime($ss->hora);
            }else{
                $sol->file=null;
            }
            if($ss->id_categoria>0){
                $sol->categoria=new Categoria;
                $sol->categoria->id=$ss->id_categoria;
                $sol->categoria->nombre=$ss->nombre_categoria;
            }else{
                $sol->categoria=null;
            }
            if($ss->id_usuario_usr>0){
                $sol->name_usuario=$ss->usr_usuario;
            }else{
                $sol->name_usuario='';
            }
            $arr_traza=array();
            $traza=$this->db->sql_select_all("select * from ".$this->tbl->cheque_seguimiento." where id_solicitud=:id_solicitud order by fecha,hora,nivel",array(':id_solicitud'=>$sol->id));
            $sol->name_usuario_autoriza='';
            $sol->name_usuario_autoriza_cc='';
            $sol->name_usuario_autoriza_5k='';
            $sol->name_usuario_autoriza_conta='';
            if(!empty($traza)){
                foreach($traza as $tr){
                    $t=new Trazabilidad;
                    $t->id=$tr->id;
                    $t->id_solicitud=$tr->id_solicitud;
                    $t->id_usuario=$tr->id_usuario;
                    $t->status=$tr->status;
                    $t->fecha=Form::IntegerToDate($tr->fecha);
                    $t->hora=Form::IntegerToTime($tr->hora);
                    $t->observacion=$tr->observacion;
                    $t->avance=$tr->avance;
                    $t->nivel = $tr->nivel;
                    $t->usuario=strtoupper($tr->usuario);
                    $arr_traza[]=$t;
                    if($t->status=='D' && $sol->name_usuario_autoriza=='' && $t->avance!='N5'){
                        $sol->name_usuario_autoriza=strtoupper($t->usuario);
                    }else if($t->status=='A' && $sol->name_usuario_autoriza==''){
                        if($t->avance=='N4' && ($sol->avance=='N3' || $sol->avance=='N5')){
                            $sol->name_usuario_autoriza=strtoupper($t->usuario);
                        }
                    }
                    if($t->status=='A' && $sol->name_usuario_autoriza_cc=='' && $t->avance=='N2'){
                        $sol->name_usuario_autoriza_cc=strtoupper($t->usuario);
                    }
                    if($t->status=='D' && $t->avance=='N5'){
                        $sol->name_usuario_autoriza_conta=strtoupper($t->usuario);
                    }
                    if(($t->status=='W' || $t->status=='Y') && $t->avance=='N3'){
                        $sol->name_usuario_autoriza_5k=strtoupper($t->usuario);
                    }
                }
                $sol->trazabilidad=$arr_traza;
            }else{
                $sol->trazabilidad=null;
            }
            return $sol;
        }
        return null;
    }
    public function create_seguimiento($traza){
        if(!empty($traza)){
            $arr=array(
                ':id_solicitud' =>  $traza->id_solicitud,
                ':id_usuario'   =>  $traza->id_usuario,
                ':status'       =>  $traza->status,
                ':fecha'        =>  $traza->fecha,
                ':hora'         =>  $traza->hora,
                ':observacion'  =>  $traza->observacion,
                ':avance'       =>  $traza->avance,
                ':usuario'      =>  $traza->usuario,
                ':nivel'        =>  $traza->nivel
            );
            return $this->db->sql_save_id("insert into ".$this->tbl->cheque_seguimiento."(id_solicitud,id_usuario,status,fecha,hora,observacion,avance,usuario,nivel) values(:id_solicitud,:id_usuario,:status,:fecha,:hora,:observacion,:avance,:usuario,:nivel)",$arr);
        }
        return false;
    }
    public function to_sendmail_ok($sol){
        if($this->rol_usuario=='N1'){ //Si es usuario final.
            $arr = array(
                ':id'   => $sol['id_solicitud'],
                ':noemail'=> ''
                );
            echo "\n is rol N1";
            return $this->db->sql_select_all("select s.id_cc,s.id_empresa,u.usr_nombre nombre,u.usr_email email,ce.cc_codigo,ce.cc_descripcion,e.emp_nombre from cheque_sol s inner join acc_emp_cc ac on ac.id_empresa=s.id_empresa and ac.id_cc=s.id_cc inner join usuario u on u.id_rol=999999995 and u.id_usuario=ac.id_usuario and u.usr_email!=:noemail inner join cecosto ce on ce.id_cc=s.id_cc and ce.id_empresa=s.id_empresa inner join empresa e on e.id_empresa=s.id_empresa where s.id=:id",$arr);
        }else if($this->rol_usuario=='N2'){
            $arr=array(
                ':id_empresa' => $sol['id_empresa'],
                ':nivel'      => 'N3'
            );
            echo "\n is rol N2";
            return $this->db->sql_select_all("select u.nombre,u.correo email from cheque_usuario u inner join cheque_usuario_empresa e on u.id_usuario=e.id_usuario where e.id_empresa=:id_empresa and u.nivel=:nivel",$arr);
        }else if($this->rol_usuario=='N3'){
            $arr = array(
                ':id_categoria' => $sol['id_categoria'],
                ':noemail'      => ''
                );
            echo "\n is rol N3";
            return $this->db->sql_select_one("select gc.id_categoria,u.usr_usuario,u.usr_nombre nombre,u.usr_email email from gestion_categorias gc inner join usuario u on gc.id_usuario=u.id_usuario and gc.gestion_nivel=1 where gc.id_categoria=:id_categoria and u.usr_email!=:noemail",$arr);
        }else if($this->rol_usuario=='N4'){
            $arr=array(
                ':id_empresa' => $sol['id_empresa'],
                ':nivel'      => 'N5'
            );
            echo "\n is rol N4";
            return $this->db->sql_select_all("select u.nombre,u.correo email from cheque_usuario u inner join cheque_usuario_empresa e on u.id_usuario=e.id_usuario where e.id_empresa=:id_empresa and u.nivel=:nivel",$arr);
        }
    }
    public function to_sendmail_5k_ok($sol){
        $arr=array(
            ':id_empresa' => $sol['id_empresa'],
            ':nivel'      => 'N6'
        );
        return $this->db->sql_select_all("select u.nombre,u.correo email from cheque_usuario u inner join cheque_usuario_empresa e on u.id_usuario=e.id_usuario where e.id_empresa=:id_empresa and u.nivel=:nivel",$arr);
    }
    public function to_sendmail_fail($sol){
        $arr=array(
                ':id_solicitud' => $sol['id_solicitud'],
                ':noemail'     => ''
            );
        return $this->db->sql_select_one("select u.usr_email email,u.usr_nombre nombre from cheque_sol s inner join usuario u on s.id_usuario=u.id_usuario where s.id=:id_solicitud and u.usr_email!=:noemail",$arr);
    }
    public function create_solicitud($POST,$FILE){
        //$FILE=$_FILES['file']
        //$_POST=$POST
        $arr=array();
        $file_existe=false;
        $FFile=array();
        if(Form::InputExistFile($FILE)){
            $ok_file=Form::InputFileIsValid($FILE);
            if($ok_file==''){//Si $ok_file='' el archivo cumple con los requisitos para subir
                $file_existe=true;
                $FFile[':descripcion']=Form::InputFileName($FILE);
                $FFile[':fecha']=date('Ymd');
                $FFile[':hora']=date('His');
            }else{ //Se termina el proceso y se lanza error
                $arr['exito']=false;
                $arr['msj']=$ok_file;
            }
        }
        if(empty($arr)){ //Si array esta vacio no se ha cargado error
            if($this->rol_usuario=='N4'){
                $this->rol_usuario='N2';
            }
            $input_arr=array(
                ':id_empresa'           =>  Form::InputInt($POST['id_empresa']),
                ':id_cc'                =>  Form::InputInt($POST['id_cc']),
                ':nombre_beneficiario'  =>  strtoupper(Form::InputString($POST['nombre_beneficiario'])),
                ':valor_cheque'         =>  Form::InputDouble($POST['valor_cheque']),
                ':concepto_pago'        =>  strtoupper(Form::InputString($POST['concepto_pago'])),
                ':fecha_max_pago'       =>  Form::FechaToInteger($POST['fecha_max_pago']),
                ':negociable'           =>  Form::InputString(Form::InputCheckBox(isset($POST['negociable']) ? $POST['negociable'] : null) ? '1' : 'N'),
                ':fecha_solicitud'      =>  date('Ymd'),
                ':hora_solicitud'       =>  date('His'),
                ':id_usuario'           =>  $this->id_usuario,
                ':observacion'          =>  strtoupper(Form::InputString($POST['observacion'])),
                ':status'               =>  'C',
                ':avance'               =>  $this->rol_usuario,
                ':no_copia'             =>  0,
                ':id_categoria'         =>  0,
                ':5k'                   =>  0,
				':moneda'               =>  (isset($POST['moneda']) ? $POST['moneda'] : '$')
            );
            $nivel = 1;
            if(Form::InputString($POST['borrador'])=='S'){
                $input_arr[':status']=='C';
            }else{
                if($this->rol_usuario=='N1'){
                    $nivel = 1;
                    $input_arr[':avance']='N2';
                }else if($this->rol_usuario=='N2'){
                    $nivel = 2;
                    $input_arr[':avance']='N3';
                }else if($this->rol_usuario=='N3'){
                    $input_arr[':avance']='N4';
                }else if($this->rol_usuario=='N4'){
                    $input_arr[':avance']='N5';
                }
                $input_arr[':status']='R';
            }
            $id_solicitud=$this->db->sql_save_id("insert into ".$this->tbl->cheque_sol."(id_empresa,id_cc,nombre_beneficiario,valor_cheque,concepto_pago,fecha_max_pago,status,negociable,fecha_solicitud,hora_solicitud,no_copia,id_usuario,avance,observacion,id_categoria,5k,moneda) values(:id_empresa,:id_cc,:nombre_beneficiario,:valor_cheque,:concepto_pago,:fecha_max_pago,:status,:negociable,:fecha_solicitud,:hora_solicitud,:no_copia,:id_usuario,:avance,:observacion,:id_categoria,:5k,:moneda)",$input_arr);
            if($id_solicitud!=null){
                
                if(Form::InputString($POST['borrador'])!='S'){
                    $traza=new Trazabilidad;
                    $traza->id_solicitud=$id_solicitud;
                    $traza->id_usuario=$this->id_usuario;
                    $traza->status='C';
                    $traza->fecha=date('Ymd');
                    $traza->hora=date('His');
                    $traza->observacion="";
                    $traza->avance=$this->rol_usuario;
                    $traza->usuario=$this->name_usuario;
                    $traza->nivel = $nivel; //1: solicitud creada por usuario
                    $this->create_seguimiento($traza);
                }
                
                if($file_existe){ //Si se ha validado archivo
                    $FFile[':id_solicitud']=$id_solicitud;
                    $FFile[':filename']=$id_solicitud.".".Form::InputFileExt($FILE);
                    $FFile[':fecha']=date('Ymd');
                    $FFile[':hora']=date('His');
                    $id_upload=$this->db->sql_save_id("insert into ".$this->tbl->cheque_upload."(id_solicitud,descripcion,filename,fecha,hora) values(:id_solicitud,:descripcion,:filename,:fecha,:hora)",$FFile);
                    if($id_upload!=null){
                        Form::InputFileSave($FILE, $id_solicitud);
                    }
                }
                $arr['exito']=true;
                $arr['msj']=$id_solicitud;
            }else{
                $arr['exito']=false;
                $arr['msj']='No es posible crear solicitud';
            }
        }
        return $arr;
    }
    public function save_solicitud($POST,$FILE){
        //$FILE=$_FILES['file']
        //$_POST=$POST
        $arr=array();
        $file_existe=false;
        $FFile=array();
        if(Form::InputExistFile($FILE)){
            $ok_file=Form::InputFileIsValid($FILE);
            if($ok_file==''){//Si $ok_file='' el archivo cumple con los requisitos para subir
                $file_existe=true;
                $FFile[':descripcion']=Form::InputFileName($FILE);
                $FFile[':fecha']=date('Ymd');
                $FFile[':hora']=date('His');
            }else{ //Se termina el proceso y se lanza error
                $arr['exito']=false;
                $arr['msj']=$ok_file;
            }
        }
        if(empty($arr)){ //Si array esta vacio no se ha cargado error
            $input_arr=array(
                ':id'                   =>  Form::InputInt($POST['id']),
                ':id_empresa'           =>  Form::InputInt($POST['id_empresa']),
                ':id_cc'                =>  Form::InputInt($POST['id_cc']),
                ':nombre_beneficiario'  =>  Form::InputString($POST['nombre_beneficiario']),
                ':valor_cheque'         =>  Form::InputDouble($POST['valor_cheque']),
                ':concepto_pago'        =>  Form::InputString($POST['concepto_pago']),
                ':fecha_max_pago'       =>  Form::FechaToInteger($POST['fecha_max_pago']),
                ':negociable'           =>  Form::InputString(Form::InputCheckBox($POST['negociable']) ? '1' : 'N'),
                ':observacion'          =>  Form::InputString($POST['observacion'])
            );
            $id_solicitud=null;
            if(Form::InputString($POST['borrador'])!='S'){
                if($this->rol_usuario=='N1'){
                    $input_arr[':avance']='N2';
                }else if($this->rol_usuario=='N2'){
                    $input_arr[':avance']='N3';
                }else if($this->rol_usuario=='N3'){
                    $input_arr[':avance']='N4';
                }else if($this->rol_usuario=='N4'){
                    $input_arr[':avance']='N5';
                }
                $input_arr[':status']='R';
                $id_solicitud=$this->db->sql_query("update ".$this->tbl->cheque_sol." set id_empresa=:id_empresa,id_cc=:id_cc,nombre_beneficiario=:nombre_beneficiario,valor_cheque=:valor_cheque,concepto_pago=:concepto_pago,fecha_max_pago=:fecha_max_pago,negociable=:negociable,observacion=:observacion,status=:status,avance=:avance where id=:id",$input_arr);
            }else{
                $id_solicitud=$this->db->sql_query("update ".$this->tbl->cheque_sol." set id_empresa=:id_empresa,id_cc=:id_cc,nombre_beneficiario=:nombre_beneficiario,valor_cheque=:valor_cheque,concepto_pago=:concepto_pago,fecha_max_pago=:fecha_max_pago,negociable=:negociable,observacion=:observacion where id=:id",$input_arr);
            }
            
            if($id_solicitud){
                if(Form::InputString($POST['borrador'])!='S'){
                    $traza=new Trazabilidad;
                    $traza->id_solicitud=$id_solicitud;
                    $traza->id_usuario=$this->id_usuario;
                    $traza->status='C';
                    $traza->fecha=date('Ymd');
                    $traza->hora=date('His');
                    $traza->observacion="";
                    $traza->avance=$this->rol_usuario;
                    $traza->usuario=$this->name_usuario;
                    $this->create_seguimiento($traza);
                }
                if($file_existe){ //Si se ha validado archivo
                    $search_upload=$this->findFile($input_arr[':id']);
                    
                    $FFile[':filename']=$input_arr[':id'].".".Form::InputFileExt($FILE);
                    
                    $id_upload=null;
                    $FFile[':id_solicitud']=$input_arr[':id'];
                    if(empty($search_upload)){
                        $id_upload=$this->db->sql_save_id("insert into ".$this->tbl->cheque_upload."(id_solicitud,descripcion,filename,fecha,hora) values(:id_solicitud,:descripcion,:filename,:fecha,:hora)",$FFile);
                    }else{
                        $id_upload=$this->db->sql_query("update ".$this->tbl->cheque_upload." set descripcion=:descripcion,filename=:filename,fecha=:fecha,hora=:hora where id_solicitud=:id_solicitud",$FFile);
                    }
                    if($id_upload!=null){
                        Form::InputFileSave($FILE, $input_arr[':id']);
                    }
                }
                $arr['exito']=true;
                $arr['msj']=$input_arr[':id'];
            }else{
                $arr['exito']=false;
                $arr['msj']='No es actualizar crear solicitud';
            }
        }
        return $arr;
    }
    public function findFile($id_solicitud){
        $arr=array(
            ':id_solicitud' =>  $id_solicitud
            );
        return $this->db->sql_select_one('select * from '.$this->tbl->cheque_upload.' where id_solicitud=:id_solicitud',$arr);
    }
    public function delete_solicitud($id_solicitud){
        $se_puede_borrar=$this->EsPosibleBorrarSolicitd($id_solicitud);
        if($se_puede_borrar){
            $arr=array(
                ':id'   =>  $id_solicitud
            );
            $result=$this->db->sql_query('delete from '.$this->tbl->cheque_sol.' where id=:id',$arr);
            if(!empty($result)){
                return true;
            }
        }
        return false;
    }
    public function borrar_solicitud($id_solicitud){
        $arr=array(
            ':id'   =>  $id_solicitud
        );
        $result=$this->db->sql_query('delete from '.$this->tbl->cheque_sol.' where id=:id',$arr);
        if(!empty($result)){
            $result=$this->db->sql_select_one('select filename from '.$this->tbl->cheque_upload.' where id_solicitud=:id',$arr);
            if(!empty($result)){
                $filename=dirname(__FILE__).'/../../public/upload/'.$result->filename;
                if(file_exists($filename)){
                    if(unlink($filename)){
                        $this->db->sql_query('delete from '.$this->tbl->cheque_sol.' where id_solicitud=:id',$arr);
                    }
                }
            }
            return true;
        }
        return false;
    }
    private function EsPosibleBorrarSolicitd($id_solicitud){
        $arr=array(
            ':id'           =>  $id_solicitud,
            ':id_usuario'   =>  $this->id_usuario,
            ':borrador'     =>  'S'
        );
        $result=$this->db->sql_select_one('select id from '.$this->tbl->cheque_sol.' where id=:id and id_usuario=:id_usuario and borrador=:borrador',$arr);
        if(!empty($result)){
            return true;
        }
        return false;
    }
    public function findEmpresaCC($id_empresa){
        $arr = array(
            ':id_usuario' => $this->id_usuario,
            ':id_empresa' => $id_empresa
        );
        $result=$this->db->sql_select_all("select ce.id_cc,cc.cc_codigo,cc.cc_descripcion ".
                                          "from acc_emp_cc ce ".
                                          "inner join cecosto cc ".
                                          "on ce.id_cc=cc.id_cc ".
                                          "where ce.id_usuario=:id_usuario and ce.id_empresa=:id_empresa ".
                                          "order by cc.cc_descripcion",$arr);
        if(!empty($result)){
            return $result;
        }
        return null;
    }
    public function findEmpresaCCAll($id_empresa){
        $arr = array(
            ':id_empresa' => $id_empresa
        );
        $result=$this->db->sql_select_all("select id_cc,cc_codigo,cc_descripcion ".
                                          "from cecosto ".
                                          "where id_empresa=:id_empresa ".
                                          "order by cc_descripcion",$arr);
        if(!empty($result)){
            return $result;
        }
        return null;
    }
    public function send_solicitud($opt=array()){
        $arr=array(
            ':id'       =>  $opt['id'],
            ':status'   =>  'R',
            ':avance'   =>  $opt['avance']
        );
        $result = $this->db->sql_query("update ".$this->tbl->cheque_sol." set avance=:avance,status=:status where id=:id",$arr);
        if(!empty($result)){
            $traza=new Trazabilidad;
            $traza->id_solicitud=$id_solicitud;
            $traza->id_usuario=$this->id_usuario;
            $traza->status='C';
            $traza->fecha=date('Ymd');
            $traza->hora=date('His');
            $traza->observacion="";
            $traza->avance=$this->rol_usuario;
            $traza->usuario=$this->name_usuario;
            $this->create_seguimiento($traza);
        }
        return false;
        
    }
    private function drop_upload($id_solicitud){
        $arr=array(
            ':id_solicitud' =>  $id_solicitud
        );
        $upload=$this->db->sql_select_one("select filename from ".$this->tbl->cheque_upload." where id_solicitud=:id_solicitud",$arr);
        if(!empty($upload)){
            $filename=dirname(__FILE__).'/../../public/upload/'.$upload->filename;
            if(file_exists($filename)){
                if(unlink($filename)){
                    $this->db->sql_query('delete from '.$this->tbl->cheque_upload.' where id_solicitud=:id_solicitud',$arr);
                    return true;
                }
            }
        }
        return false;
    }
    public function autorizar_solicitud($POST){
        $arr=array(
            ':id'       =>  Form::InputInt($POST['id']),
            ':status'   =>  'R'
        );
        $avance=Form::InputString($POST['avance']);
        $nivel = 2;
        if($avance=='N2'){
            $nivel = 2;
            $arr[':avance']='N3';
        }else if($avance=='N3'){
            $nivel = 3;
            $arr[':5k']=Form::InputString($POST['is5k']);
			if(empty($arr[':5k'])){
              $arr[':5k']='0';
            }
            if($arr[':5k']=="1" && (int)Form::InputInt($POST['id_categoria'])==ID_CATEGORIA_APROBACION_AUTOMATICA){
                $arr[':avance']='N3';
                $arr[':status']='Z';
            }else{
                $arr[':avance']='N4';
            }
        }else if($avance=='N4'){
            $nivel = 5;
            $arr[':avance']='N5';
            $arr[':5k']=Form::InputString($POST['is5k']);
			if(empty($arr[':5k'])){
              $arr[':5k']='0';
            }
            if((int)$arr[':5k']==1){
              $arr[':avance']='N3';
              $arr[':status']='Z';
            }
        }
        $result = $this->db->sql_query("update ".$this->tbl->cheque_sol." set avance=:avance,status=:status".(($avance=='N3' || $avance=='N4') ? ",5k=:5k": "")." where id=:id",$arr);
        if(!empty($result)){
            
            if($arr[':avance']=='N4'){
                //$this->drop_upload($arr[':id']);
            }

            $traza=new Trazabilidad;
            $traza->id_solicitud=Form::InputInt($POST['id']);
            $traza->id_usuario=$this->id_usuario;
            $traza->status='A';
            $traza->fecha=date('Ymd');
            $traza->hora=date('His');
            $traza->observacion=Form::InputString($POST['observacion']);
            $traza->avance=$avance;
            $traza->nivel=$nivel;
            $traza->usuario=$this->name_usuario;

            if((int)Form::InputInt($POST['id_categoria'])==ID_CATEGORIA_APROBACION_AUTOMATICA && $avance=='N3'){ //no es posible aprobación automática
                $objs=array(
                    ':id'       =>  $arr[':id'],
                    ':avance'   =>  'N5',
                    ':status'   =>  'R'
                );
                if((int)Form::InputString($POST['is5k'])!=1){
                    $this->db->sql_query("update ".$this->tbl->cheque_sol." set avance=:avance,status=:status where id=:id",$objs);
                }
                $this->create_seguimiento($traza);

                $traza=new Trazabilidad;
                $traza->id_solicitud=Form::InputInt($POST['id']);
                $traza->id_usuario=$this->id_usuario;
                $traza->status='A';
                $traza->fecha=date('Ymd');
                $traza->hora=date('His');
                $traza->observacion=Form::InputString('');
                $traza->avance='N4';
                $traza->nivel=5;
                $traza->usuario=$this->name_usuario;

                return $this->create_seguimiento($traza);

            }else{
                return $this->create_seguimiento($traza);
            }
        }
    }
    public function devolver_solicitud($POST){
        $arr=array(
            ':id'       =>  Form::InputInt($POST['id']),
            ':status'   =>  'R',
            ':avance'   =>  'N3',
            ':id_categoria' => '0',
            ':devuelta' => '*'
        );
        
        $result = $this->db->sql_query(
          "update ".
            $this->tbl->cheque_sol." 
          set 
            avance=:avance,
            status=:status,
            id_categoria=:id_categoria,
            devuelta=:devuelta
          where id=:id"
          ,$arr);
        if(!empty($result)){

            $traza=new Trazabilidad;
            $traza->id_solicitud=Form::InputInt($POST['id']);
            $traza->id_usuario=$this->id_usuario;
            $traza->status='*';
            $traza->fecha=date('Ymd');
            $traza->hora=date('His');
            $traza->observacion=Form::InputString($POST['observacion']);
            $traza->avance='N4';
            $traza->nivel=5;
            $traza->usuario=$this->name_usuario;

            $this->create_seguimiento($traza);


            return $this->db->sql_query(
                      "delete from ".
                        $this->tbl->cheque_seguimiento." 
                      where id_solicitud=".$arr[':id']." and avance='N3' and nivel=3 and status='A'"
                    );
        }
        return 0;
    }
    public function autorizarde_solicitud($POST){
        $arr=array(
            ':id'       =>  Form::InputInt($POST['id']),
            ':avance'   =>  'N5',
            ':status'   =>  'R'
        );
        $result = $this->db->sql_query("update ".$this->tbl->cheque_sol." set avance=:avance,status=:status where id=:id",$arr);
        if(!empty($result)){

            $traza=new Trazabilidad;
            $traza->id_solicitud=Form::InputInt($POST['id']);
            $traza->id_usuario=$this->id_usuario;
            $traza->status='Y';
            $traza->fecha=date('Ymd');
            $traza->hora=date('His');
            $traza->observacion=Form::InputString($POST['observacion']);
            $traza->avance='N3';
            $traza->nivel=4;
            $traza->usuario=$this->name_usuario;

            return $this->create_seguimiento($traza);
        }
        return false;
    }
    public function categorizar_solicitud($POST){
        $arr=array(
            ':id'           =>  Form::InputInt($POST['id']),
            ':id_categoria' =>  Form::InputString($POST['categoria']),
            ':status'       =>  Form::InputString($POST['option'])
        );
        $result=false;
        if($arr[':status']=='A'){
            if(Form::InputString($POST['is5k'])=="1" && (int)$arr[':id_categoria']==ID_CATEGORIA_APROBACION_AUTOMATICA){ //monto del cheque es superior a $5000
                $arr[':avance']='N3';
                $arr[':status']='Z'; //status que se enviará a Dirección Ejecutivo
            }else{
                $arr[':avance']='N4';
                $arr[':status']='R';
            }
            $arr[':5k']=Form::InputString($POST['is5k']);
			if(empty($arr[':5k'])){
              $arr[':5k']='0';
            }
            $result = $this->db->sql_query("update ".$this->tbl->cheque_sol." set avance=:avance,status=:status,id_categoria=:id_categoria,5k=:5k where id=:id",$arr);
            if(!empty($result)){
                //$this->drop_upload($arr[':id']);
                $traza=new Trazabilidad;
                $traza->id_solicitud=Form::InputInt($POST['id']);
                $traza->id_usuario=$this->id_usuario;
                $traza->status='A';
                $traza->fecha=date('Ymd');
                $traza->hora=date('His');
                $traza->observacion=Form::InputString($POST['observacion']);
                $traza->avance='N3';
                $traza->nivel=3;
                $traza->usuario=$this->name_usuario;

                if((int)$arr[':id_categoria']==ID_CATEGORIA_APROBACION_AUTOMATICA){ //no es posible aprobación automática
                    $objs=array(
                        ':id'       =>  $arr[':id'],
                        ':avance'   =>  'N5',
                        ':status'   =>  'R'
                    );

                    if(Form::InputString($POST['is5k'])!="1"){
                        $this->db->sql_query("update ".$this->tbl->cheque_sol." set avance=:avance,status=:status where id=:id",$objs);
                    }
                    $this->create_seguimiento($traza);

                    $traza=new Trazabilidad;
                    $traza->id_solicitud=Form::InputInt($POST['id']);
                    $traza->id_usuario=$this->id_usuario;
                    $traza->status='A';
                    $traza->fecha=date('Ymd');
                    $traza->hora=date('His');
                    $traza->observacion=Form::InputString('');
                    $traza->avance='N4';
                    $traza->nivel=5;
                    $traza->usuario=$this->name_usuario;

                    return $this->create_seguimiento($traza);

                }else{
                    return $this->create_seguimiento($traza);
                }
            }
        }else{
            $result = $this->db->sql_query("update ".$this->tbl->cheque_sol." set status=:status,id_categoria=:id_categoria where id=:id",$arr);
        }
        return $result;
    }
    public function desistir_solicitud($POST){
        $arr=array(
            ':id'       =>  Form::InputInt($POST['id']),
            ':status'   =>  'D',
            ':avance'   =>  Form::InputString($POST['avance'])
        );
        $result = $this->db->sql_query("update ".$this->tbl->cheque_sol." set avance=:avance,status=:status where id=:id",$arr);
        if(!empty($result)){
            
            $this->drop_upload($arr[':id']);
            
            $traza=new Trazabilidad;
            $traza->id_solicitud=Form::InputInt($POST['id']);
            $traza->id_usuario=$this->id_usuario;
            $traza->status='D';
            $traza->fecha=date('Ymd');
            $traza->hora=date('His');
            $traza->observacion=Form::InputString($POST['observacion']);
            $traza->avance=$arr[':avance'];
            if($traza->avance=='N2'){
                $traza->nivel=2;
            }else if($traza->avance=='N3'){
                $traza->nivel=3;
            }else if($traza->avance=='N4'){
                $traza->nivel=5;
            }else if($traza->avance=='N5'){
                $traza->nivel=6;
            }
            $traza->usuario=$this->name_usuario;
            
            return $this->create_seguimiento($traza);
        }
    }
    public function desistirde_solicitud($POST){
        $arr=array(
            ':id'       =>  Form::InputInt($POST['id']),
            ':status'   =>  'W',
            ':avance'   =>  'N3'
        );
        $result = $this->db->sql_query("update ".$this->tbl->cheque_sol." set avance=:avance,status=:status where id=:id",$arr);
        if(!empty($result)){
            
            $this->drop_upload($arr[':id']);
            
            $traza=new Trazabilidad;
            $traza->id_solicitud=$arr[':id'];
            $traza->id_usuario=$this->id_usuario;
            $traza->status='W';
            $traza->fecha=date('Ymd');
            $traza->hora=date('His');
            $traza->observacion=Form::InputString($POST['observacion']);
            $traza->avance='N3';
            $traza->nivel=4;
            $traza->usuario=$this->name_usuario;
            
            return $this->create_seguimiento($traza);
        }
        return false;
    }
    public function admin_user(){
        return $this->db->sql_select_all('select * from '.$this->tbl->cheque_usuario.' order by nivel,usuario');
    }
    public function admin_user_id($id_user){
        $arr=array(
            ':id_usuario' => $id_user
        );
        $user=array();
        $result = $this->db->sql_select_one('select * from '.$this->tbl->cheque_usuario.' where id_usuario=:id_usuario',$arr);
        if(!empty($result)){
            $user['usuario'] = $result;
            $result = $this->db->sql_select_all('select e.id,e.id_usuario,e.id_empresa,m.emp_nombre from cheque_usuario_empresa e inner join empresa m on m.id_empresa=e.id_empresa where e.id_usuario=:id_usuario order by m.emp_nombre',$arr);
            if(!empty($result)){
                $user['empresa'] = $result;
            }else{
                $user['empresa'] = NULL;
            }
            return $user;
        }
        return null;
    }
    public function admin_empresas(){
        return $this->db->sql_select_all('select id_empresa,emp_nombre from empresa order by emp_nombre');
    }
    public function corregir_solicitud_cheque(){
      $sols = $this->db->sql_select_all("
                    SELECT
                    id_solicitud
                    from cheque_seguimiento
                    where nivel=3 and usuario not in (select usuario from cheque_usuario where nivel='N3')
              ");
      if(!empty($sols)){
        foreach ($sols as $s) {
          $sols_update = $this->db->sql_query("
                            update cheque_sol set avance='N3'
                            where id=$s->id_solicitud and avance='N4' and status='R'
                        ");
        }
        $this->db->sql_query("
                  delete 
                  from 
                  cheque_seguimiento
                  where nivel=3 and usuario not in (select usuario from cheque_usuario where nivel='N3')
              ");
      }
    }
}
?>

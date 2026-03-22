<?php
require_once dirname(__FILE__).'/../cheque/DBSics.php';
class ProveedorDB{
    private $db;
    private $id_usuario;
    private $limit=8;
    private $pag = 0;
    public function __construct(){
        $this->db=new DBSics();
        $this->id_usuario = $this->get_session("i");
    }
    function get_page_elto(){
        return $this->limit;
    }
    function set_page_elto($pag){
        $this->pag=$pag;
    }
    function ignore_link($link, $req, $sol){
        return (($link == '?c=req&a=colectar&id=6' && $req == 0)
        || ($link == '?c=solc&a=colectar&id=5' && $sol == 0)
        || ($link == '?c=req&a=tracole&id=6' && $req == 0)
        || ($link == '?c=solc&a=tracole&id=5' && $sol == 0)
        || ($link == '?c=req&a=oc_sp&id=6' && $req == 0)
        || ($link == '?c=solc&a=oc_sp&id=5' && $sol == 0)
        || ($link == '?c=solc&a=gestor&id=5' && $sol == 0)
        || ($link == '?c=inv&a=inicios&id=5' && $sol == 0)
        || ($link == '?c=ci&a=gestor&id=12' && $sol == 0)
        || ($link == '?c=ci&a=prod&id=12' && $sol == 0)
        || ($link == '?c=ci&a=xls&id=12&via=1' && $sol == 0));
    }
    function get_categoria_sics_count($gcia){
        $qry = "select ".
               "    count(*) contador ".
               "from cat_gerencias ";

        $arr = array();
        if(!empty($gcia)){
            $arr[':gcia'] = $gcia;
            $qry .= "where gcia = :gcia";
        }

        $result = $this->db->sql_select_one($qry,$arr);
        if(!empty($result)){
            if(!empty($result->contador)){
                return (int)$result->contador;
            }
        }
        return 0;
    }
    function crear_categoria($POST,$msg){
    	// id de usuarios
        $usr_aprobador_1 = 0;
        $usr_aprobador_2 = 0;
        $usr_aprobador_3 = 0;
        $usr_aprobador_5k = 0;

        //Valida si ya existe categoria en la misma gerencia
    	$arr = array(
                ':gcia' => $POST['gerencia'],
                ':cat' => $POST['categoria']
            );

    	$rs = $this->db->sql_select_one("select id ".
                                        "from cat_gerencias ".
                                        "where Upper(gcia)=Upper(:gcia) And Upper(categoria)=Upper(:cat)",$arr);
        if(!empty($rs)){
            $msg = "Ya existe categoría";
            return FALSE;
        }

        //valida si aprobadores es cada gerencia
        if(trim($POST['aprobador1'])=='' && trim($POST['aprobador2'])=='' && trim($POST['aprobador3'])==''){
            $usr_aprobador_1 = 0;
            $usr_aprobador_2 = 0;
            $usr_aprobador_3 = 0;
        }else{

            //Valida aprobador 1
            $arr = array(
                    ':usr' => $POST['aprobador1']
                );

            $rs = $this->db->sql_select_one("select id_usuario ".
                                            "from usuario ".
                                            "where Upper(usr_usuario)=Upper(:usr) And id_rol=999999995",$arr);
            if(empty($rs)){
                $msg = "No existe usuario aprobador 1";
                return FALSE;
            }else{
                $usr_aprobador_1 = $rs->id_usuario;
            }

            //Valida aprobador 2
            $arr = array(
                    ':usr' => $POST['aprobador2']
                );

            $rs = $this->db->sql_select_one("select id_usuario ".
                                            "from usuario ".
                                            "where Upper(usr_usuario)=Upper(:usr) And id_rol=999999995",$arr);
            if(empty($rs)){
                $msg = "No existe usuario aprobador 2";
                return FALSE;
            }else{
                $usr_aprobador_2 = $rs->id_usuario;
            }

            //Valida aprobador 3
            $arr = array(
                    ':usr' => $POST['aprobador3']
                );

            $rs = $this->db->sql_select_one("select id_usuario ".
                                            "from usuario ".
                                            "where Upper(usr_usuario)=Upper(:usr) And id_rol=999999995",$arr);
            if(empty($rs)){
                $msg = "No existe usuario aprobador 3";
                return FALSE;
            }else{
                $usr_aprobador_3 = $rs->id_usuario;
            }
        }
        // Valida requiere aprobador 5K
        if(!empty($POST['requiere_5k'])){
            //Valida aprobador 3
            $arr = array(
                    ':usr' => $POST['aprobador_5k']
                );

            $rs = $this->db->sql_select_one("select id_usuario ".
                                            "from usuario ".
                                            "where Upper(usr_usuario)=Upper(:usr) And id_rol=999999995",$arr);
            if(empty($rs)){
                $msg = "No existe usuario aprobador 5K";
                return FALSE;
            }else{
                $usr_aprobador_5k = $rs->id_usuario;
            }
        }
        

        $arr = array(
            ':gcia'                      =>  $POST['gerencia'],
            ':categoria'                 =>  $POST['categoria'],
            ':cod_gasto'                 =>  '',
            ':user_aproba_1'             =>  (int)$usr_aprobador_1,
            ':user_aproba_2'             =>  (int)$usr_aprobador_2,
            ':user_aproba_3'             =>  (int)$usr_aprobador_3,
            ':user_aproba_4'             =>  0,
            ':user_aproba_5'             =>  0,
            ':id_categoria_aprobador'    =>  0,
            ':mod_solicitud'             =>  'Solicitud de cheque',
            ':id_usuario_crea'           =>  (int)$this->id_usuario,
            ':fecha_crea'                =>  (int)date('Ymd'),
            ':hora_crea'                 =>  (int)date('His'),
            ':fecha_mod'                 =>  0,
            ':hora_mod'                  =>  0,
            ':status'                    =>  (int)$POST['estado'],
            ':gcia_aprueba'              =>  (int)(($usr_aprobador_1==0 && $usr_aprobador_2==0 && $usr_aprobador_3==0) ? 1 : 0),
            ':requiere_aprobador_5k'     =>  (int)$POST['requiere_5k'],
            ':id_aprueba_5k'             =>  (int)$usr_aprobador_5k,
            ':requiere_recepcion'        =>  (int)$POST['requiere_recepcion']
        );

    	return $this->db->sql_save_id(
            "Insert into cat_gerencias(".
                "gcia,".
                "categoria,".
                "cod_gasto,".
                "user_aproba_1,".
                "user_aproba_2,".
                "user_aproba_3,".
                "user_aproba_4,".
                "user_aproba_5,".
                "id_categoria_aprobador,".
                "mod_solicitud,".
                "id_usuario_crea,".
                "fecha_crea,".
                "hora_crea,".
                "fecha_mod,".
                "hora_mod,".
                "status,".
                "gcia_aprueba,".
                "requiere_aprobador_5k,".
                "id_aprueba_5k,".
                "requiere_recepcion".
            ") Values (".
                ":gcia,".
                ":categoria,".
                ":cod_gasto,".
                ":user_aproba_1,".
                ":user_aproba_2,".
                ":user_aproba_3,".
                ":user_aproba_4,".
                ":user_aproba_5,".
                ":id_categoria_aprobador,".
                ":mod_solicitud,".
                ":id_usuario_crea,".
                ":fecha_crea,".
                ":hora_crea,".
                ":fecha_mod,".
                ":hora_mod,".
                ":status,".
                ":gcia_aprueba,".
                ":requiere_aprobador_5k,".
                ":id_aprueba_5k,".
                ":requiere_recepcion".
            ")",$arr);
    }
    function editar_categoria($POST,$msg){
    	// id de usuarios
        $usr_aprobador_1 = 0;
        $usr_aprobador_2 = 0;
        $usr_aprobador_3 = 0;
        $usr_aprobador_5k = 0;

        $id_categoria = (int)$POST['id'];

        //Valida si ya existe categoria en la misma gerencia
    	$arr = array(
                ':gcia' => $POST['gerencia'],
                ':cat' => $POST['categoria'],
                ':id_categoria' => $id_categoria
            );

    	$rs = $this->db->sql_select_one("select id ".
                                        "from cat_gerencias ".
                                        "where Upper(gcia)=Upper(:gcia) And Upper(categoria)=Upper(:cat) And id!=:id_categoria",$arr);
        if(!empty($rs)){
            $msg = "Ya existe categoría";
            return FALSE;
        }

        //Si todos los campos de aprobadores están vacíos, no se valida nada
        //la aprobación será por cada gerencia
        if(trim($POST['aprobador1'])=='' && trim($POST['aprobador2'])=='' && trim($POST['aprobador3'])==''){
            $usr_aprobador_1 = 0;
            $usr_aprobador_2 = 0;
            $usr_aprobador_3 = 0;
        }else{

            //Valida aprobador 1
            $arr = array(
                    ':usr' => $POST['aprobador1']
                );

            $rs = $this->db->sql_select_one("select id_usuario ".
                                            "from usuario ".
                                            "where Upper(usr_usuario)=Upper(:usr) And id_rol=999999995",$arr);
            if(empty($rs)){
                $msg = "No existe usuario aprobador 1";
                return FALSE;
            }else{
                $usr_aprobador_1 = $rs->id_usuario;
            }

            //Valida aprobador 2
            $arr = array(
                    ':usr' => $POST['aprobador2']
                );

            $rs = $this->db->sql_select_one("select id_usuario ".
                                            "from usuario ".
                                            "where Upper(usr_usuario)=Upper(:usr) And id_rol=999999995",$arr);
            if(empty($rs)){
                $msg = "No existe usuario aprobador 2";
                return FALSE;
            }else{
                $usr_aprobador_2 = $rs->id_usuario;
            }

            //Valida aprobador 3
            $arr = array(
                    ':usr' => $POST['aprobador3']
                );

            $rs = $this->db->sql_select_one("select id_usuario ".
                                            "from usuario ".
                                            "where Upper(usr_usuario)=Upper(:usr) And id_rol=999999995",$arr);
            if(empty($rs)){
                $msg = "No existe usuario aprobador 3";
                return FALSE;
            }else{
                $usr_aprobador_3 = $rs->id_usuario;
            }
        }

        // Valida requiere aprobador 5K
        if(!empty($POST['requiere_5k'])){
            //Valida aprobador 3
            $arr = array(
                    ':usr' => $POST['aprobador_5k']
                );

            $rs = $this->db->sql_select_one("select id_usuario ".
                                            "from usuario ".
                                            "where Upper(usr_usuario)=Upper(:usr) And id_rol=999999995",$arr);
            if(empty($rs)){
                $msg = "No existe usuario aprobador 5K";
                return FALSE;
            }else{
                $usr_aprobador_5k = $rs->id_usuario;
            }
        }
        

        $arr = array(
            ':id'                        =>  $id_categoria,
            ':gcia'                      =>  $POST['gerencia'],
            ':categoria'                 =>  $POST['categoria'],
            ':user_aproba_1'             =>  (int)$usr_aprobador_1,
            ':user_aproba_2'             =>  (int)$usr_aprobador_2,
            ':user_aproba_3'             =>  (int)$usr_aprobador_3,
            ':fecha_mod'                 =>  (int)date('Ymd'),
            ':hora_mod'                  =>  (int)date('His'),
            ':gcia_aprueba'              =>  (int)(($usr_aprobador_1==0 && $usr_aprobador_2==0 && $usr_aprobador_3==0) ? 1 : 0),
            ':requiere_aprobador_5k'     =>  (int)$POST['requiere_5k'],
            ':id_aprueba_5k'             =>  (int)$usr_aprobador_5k,
            ':requiere_recepcion'        =>  (int)$POST['requiere_recepcion'],
            ':status'                    =>  (int)$POST['estado']
        );

    	return $this->db->sql_query(
            "update cat_gerencias set ".
                "gcia=:gcia,".
                "categoria=:categoria,".
                "user_aproba_1=:user_aproba_1,".
                "user_aproba_2=:user_aproba_2,".
                "user_aproba_3=:user_aproba_3,".
                "fecha_mod=:fecha_mod,".
                "hora_mod=:hora_mod,".
                "gcia_aprueba=:gcia_aprueba,".
                "requiere_aprobador_5k=:requiere_aprobador_5k,".
                "id_aprueba_5k=:id_aprueba_5k,".
                "requiere_recepcion=:requiere_recepcion,".
                "status=:status ".
            "where id=:id",$arr);
    }
    function get_proveedor_gcia(){
        $qry = "select ".
               "    distinct ".
               "    c.gcia ".
               "from cat_gerencias c ".
               "order by c.gcia ";
        return $this->db->sql_select_all($qry,array());
    }
    function get_categoria_sics($gcia){
        $qry = "select ".
               "    c.id, ".
               "    c.gcia, ".
               "    c.cod_gasto, ".
               "    c.categoria, ".
               "    c.user_aproba_1, ".
               "    ifnull(u1.usr_usuario,'C/Gerencia') aprobador_1, ".
               "    ifnull(u1.usr_email,'') email_1, ".
               "    ifnull(u1.usr_nombre,'') nom_aprobador_1, ".
               "    c.user_aproba_2, ".
               "    ifnull(u2.usr_usuario,'C/Gerencia') aprobador_2, ".
               "    ifnull(u2.usr_email,'') email_2, ".
               "    ifnull(u2.usr_nombre,'') nom_aprobador_2, ".
               "    c.user_aproba_3, ".
               "    ifnull(u3.usr_usuario,'C/Gerencia') aprobador_3, ".
               "    ifnull(u3.usr_email,'') email_3, ".
               "    ifnull(u3.usr_nombre,'') nom_aprobador_3, ".
               "    c.mod_solicitud, ".
               "    c.id_usuario_crea, ".
               "    u5.usr_usuario usuario_crea, ".
               "    c.fecha_crea, ".
               "    c.hora_crea, ".
               "    c.gcia_aprueba, ".
               "    c.requiere_aprobador_5k, ".
               "    ifnull(u4.usr_usuario,'') aprobador_5k, ".
               "    ifnull(u4.usr_email,'') email_5k, ".
               "    ifnull(u4.usr_nombre,'') nom_aprobador_5k, ".
               "    c.status  ".
               "from cat_gerencias c ".
               "left join usuario u1 ".
               "on c.user_aproba_1 = u1.id_usuario ".
               "left join usuario u2 ".
               "on c.user_aproba_2 = u2.id_usuario ".
               "left join usuario u3 ".
               "on c.user_aproba_3 = u3.id_usuario ".
               "left join usuario u4 ".
               "on c.id_aprueba_5k = u4.id_usuario ".
               "left join sicysP.usuario u5 ".
               "on c.id_usuario_crea = u5.id_usuario ";
               
        $arr = array();
        if(!empty($gcia)){
            $qry .= "where c.gcia = :gcia ";
            $arr[':gcia'] = $gcia;
        }
        $qry .= "order by c.gcia,c.categoria ";
            
        if($this->pag>0){
            $elto=($this->pag*$this->limit);
            $qry.="limit $elto,".$this->limit;
        }else{ // por defecto items
            $qry.="limit 0,".$this->limit;
        }
        return $this->db->sql_select_all($qry,$arr);
    }
    function existe_categoria_cheque($id){
        $arr = array( ':id' => $id );
        $rs = $this->db->sql_select_one("select ".
                                            "id ".
                                        "from cheque_sol ".
                                        "where id_categoria_gasto = :id ".
                                        "limit 1",$arr);
        return !empty($rs);
    }
    function eliminar_categoria_cheque($id){
        return $this->db->sql_query(
            "delete ".
            "from cat_gerencias ".
            "where id = :id",
            array( ':id' => $id )
        );
    }
    function get_categoria($id_categoria){
        $qry = "select ".
               "    c.id, ".
               "    c.gcia, ".
               "    c.cod_gasto, ".
               "    c.categoria, ".
               "    c.user_aproba_1, ".
               "    ifnull(u1.usr_usuario,'') aprobador_1, ".
               "    ifnull(u1.usr_email,'') email_1, ".
               "    ifnull(u1.usr_nombre,'') nom_aprobador_1, ".
               "    c.user_aproba_2, ".
               "    ifnull(u2.usr_usuario,'') aprobador_2, ".
               "    ifnull(u2.usr_email,'') email_2, ".
               "    ifnull(u2.usr_nombre,'') nom_aprobador_2, ".
               "    c.user_aproba_3, ".
               "    ifnull(u3.usr_usuario,'') aprobador_3, ".
               "    ifnull(u3.usr_email,'') email_3, ".
               "    ifnull(u3.usr_nombre,'') nom_aprobador_3, ".
               "    c.mod_solicitud, ".
               "    c.id_usuario_crea, ".
               "    u5.usr_usuario usuario_crea, ".
               "    c.fecha_crea, ".
               "    c.hora_crea, ".
               "    c.gcia_aprueba, ".
               "    c.requiere_aprobador_5k, ".
               "    c.requiere_recepcion, ".
               "    ifnull(u4.usr_usuario,'') aprobador_5k, ".
               "    ifnull(u4.usr_email,'') email_5k, ".
               "    ifnull(u4.usr_nombre,'') nom_aprobador_5k, ".
               "    c.status  ".
               "from cat_gerencias c ".
               "left join usuario u1 ".
               "on c.user_aproba_1 = u1.id_usuario ".
               "left join usuario u2 ".
               "on c.user_aproba_2 = u2.id_usuario ".
               "left join usuario u3 ".
               "on c.user_aproba_3 = u3.id_usuario ".
               "left join usuario u4 ".
               "on c.id_aprueba_5k = u4.id_usuario ".
               "left join sicysP.usuario u5 ".
               "on c.id_usuario_crea = u5.id_usuario ".
               "where c.id=:id_categoria ";
        return $this->db->sql_select_one($qry,array(':id_categoria'=>$id_categoria));
    }
    function get_ListaUsuarios($q){
        $qry = "SELECT ".
               "    id_usuario id, ".
               "     usr_nombre name, ".
               "     usr_usuario usuario, ".
               "     usr_email email, ".
               "     id_rol  ".
               "FROM usuario  ".
               "WHERE id_rol=999999995 and UPPER(CONVERT(CONCAT_WS(' ', usr_usuario, usr_nombre, usr_email)  ".
               "         USING utf8) COLLATE utf8_spanish_ci) ".
               "LIKE UPPER(CONVERT('%".$q."%' USING utf8) COLLATE utf8_spanish_ci) ".
               "order by name ";
    
        return $this->db->sql_select_all($qry,array());
    }
    function depurar($obj){
        echo "<pre>";
        print_r($obj);
        echo "</pre>";
    }
    function get_usuario($id_usuario=null){
    	if(!empty($id_usuario)){
    		$this->id_usuario = $id_usuario;
    	}
    	$arr = array( ':id_usuario' => $this->id_usuario );
    	$usuario = $this->db->sql_select_one("select u.id_usuario,".
    										 "u.usr_usuario,".
    										 "u.usr_nombre,".
    										 "(u.id_rol-999999993) tipo_rol,".
    										 "r.rol_descripcion,".
    										 "r.id_rol,".
    										 "u.usr_email,".
    										 "u.usr_req,".
    										 "u.usr_sol,".
    										 "u.usr_oc ".
    										 "from ".$this->tbl->usuario." u ".
    										 "inner join ".$this->tbl->rol." r ".
    										 "on r.id_rol=u.id_rol ".
    										 "where u.id_usuario=:id_usuario",$arr);
    	$perfil = array();

    	if(!empty($usuario)){

    		$perfil['id_usuario']=$usuario->id_usuario;
    		$perfil['usuario']=$usuario->usr_usuario;
    		$perfil['usuario_nombre']=$usuario->usr_nombre;
    		$perfil['rol']=$usuario->tipo_rol;
    		$perfil['rol_descripcion']=$usuario->rol_descripcion;
    		$perfil['id_rol']=$usuario->id_rol;
    		$perfil['correo']=$usuario->usr_email;

    		$perfil['req']=!empty($usuario->usr_req);
    		$perfil['solc']=!empty($usuario->usr_sol);
    		$perfil['oc']=!empty($usuario->usr_oc);

            $perfil['is_ci']=0;
            $perfil['is_req']=0;
            $perfil['is_cheque']=1;
            $perfil['is_solc']=0;

    		$modulos = $this->db->sql_select_all("SELECT id_acceso,".
												 "id_modulo,".
												 "mod_descripcion descripcion,".
												 "mod_categoria categoria,".
												 "mod_url url,".
												 "acc_edit editar,".
												 "acc_add agregar,".
												 "acc_del borrar,".
												 "acc_xls excel,".
												 "mod_target target,".
												 "acc_aut autoriza,".
												 "mod_categoria2 subcategoria,".
												 "id_acc_modulo_lista ".
												 "from acc_modulo ".
												 "where id_usuario=:id_usuario ".
												 "order by mod_categoria,mod_categoria2",$arr);
            $perfil['modulos']=null;
    		if(!empty($modulos)){
                $mod = array();
                foreach($modulos as $modulo){
                    if(!$this->ignore_link($modulo->url,$usuario->usr_req,$usuario->usr_sol)){
                        $mod[]=$modulo;
                        $perfil['is_ci']=(int)($modulo->url=='?c=ci&a=inicio&id=12' ? 1 : $perfil['is_ci']);
                        $perfil['is_req']=(int)($modulo->url=='?c=req&a=inicio&id=6' ? 1 : $perfil['is_req']);
                        $perfil['is_solc']=(int)($modulo->url=='?c=solc&a=inicio&id=5' ? 1 : $perfil['is_solc']);
                    }
                }
                $perfil['modulos']=$mod;
    		}
            $arr_cat = array(
                ':id_usuario'       =>  $usuario->id_usuario,
                ':gestion_nivel'    =>  1
            );
            $categoria_usuario = $this->db->sql_select_one('select '.
                                                 'c.id_categoria id,'.
                                                 'c.nombre_categoria categoria '.
                                                 'from gestion_categorias gc '.
                                                 'inner join tipo_categoria c '.
                                                 'on gc.id_categoria=c.id_categoria '.
                                                 'where gc.id_usuario=:id_usuario and (gc.gestion_nivel=:gestion_nivel or gc.id_usuario=247)',$arr_cat);
            if(!empty($categoria_usuario)){
                $perfil['categoria']=$categoria_usuario;
            }
    	}

        $perfil = (object)$perfil;

    	return $perfil;
    }
    private function get_session($name_session){
    	if(isset($_SESSION)){
    		if(isset($_SESSION[$name_session])){
    			return $_SESSION[$name_session];
    		}
    	}
    	return null;
    }
}
?>

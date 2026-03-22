<?php
require_once dirname(__FILE__).'/DBSics.php';
require_once dirname(__FILE__).'/EntityTbl.php';
class EntityDB{
    private $db;
    private $tbl;
    private $id_usuario;
    public $sol_cheque;
    public $sol_compra;
    public $sol_ci;
    public $sol_req;
    private $limit=8;
    private $pag = 0;
    public function __construct(){
        $this->db=new DBSics();
        $this->tbl=new EntityTable();
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
    function get_solicitud_uautorizador($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$cat,$proveedor,$proyecto="",$id_usuario=null){
        if(!empty($id_usuario)){
            $this->id_usuario = $id_usuario;
        }
        $arr = array( 
            ':id_usuario' => $this->id_usuario
        );
        if(!empty($id_empresa)){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if(!empty($id_cc)){
            $arr[':id_cc'] = $id_cc;
        }
        if(($id_tiposol=='ci' || $id_tiposol=='' || $id_tiposol=='req' || $id_tiposol=='compra') && (!empty($this->sol_ci) || !empty($this->sol_req) || !empty($this->sol_compra))){
            $arr[':fechao_ini'] = $id_anio.'-01-01';
            $arr[':fechao_fin'] = $id_anio.'-12-31';
            if((int)$id_mes>0){
                $arr[':fechao_ini'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-01';
                $arr[':fechao_fin'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-'.cal_days_in_month(CAL_GREGORIAN, $id_mes, $id_anio);
            }
        }
        $qry = "select * from (";
        $union_all = FALSE;
        if(!empty($this->sol_ci) && ($id_tiposol=='ci' || $id_tiposol=='')){
            $qry .= "(select ".
                    "'ci' tipo,".
                    "c.id_ci id,".
                    "c.id_usuario,".
                    "c.prod_usuario usuario,".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc,".
                    "c.ci_numero correlativo,".
					"0 correlativo2,".
                    "cc.cc_codigo,".
                    "cc.cc_descripcion,".
                    "c.id_empresa,".
                    "e.emp_nombre nombre_empresa,".
                    "t.descripcion tipo_solicitud,".
                    "cast(c.ci_enc_fecha AS SIGNED) fecha_creado,".
                    "cast(c.ci_enc_hora AS SIGNED) hora_creado,".
                    "concat(c.ci_estado,'') estado,".
                    "c.ci_observacion observacion, ".
                    "0 requiere_recepcion, ".
                    "'' proyecto, ".
                    "'' adjunto1, ".
                    "'' adjunto2, ".
                    "'' adjunto3 ".
                    "from ci_enc c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "left join ci_tipo_consumo t ".
                    "on t.id_tipo_consumo=c.id_tipo_consumo ".
                    "where cast(c.ci_enc_fecha as date)>=:fechao_ini and cast(c.ci_enc_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_req) && ($id_tiposol=='req' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "'req' tipo,".
                    "c.id_prehreq id,".
                    "c.id_usuario,".
                    "c.prehreq_usuario usuario,".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc,".
                    "c.prehreq_numero_req correlativo,".
					"0 correlativo2,".
                    "cc.cc_codigo,".
                    "cc.cc_descripcion,".
                    "c.id_empresa,".
                    "e.emp_nombre nombre_empresa,".
                    "'' tipo_solicitud,".
                    "cast(c.prehreq_fecha AS SIGNED) fecha_creado,".
                    "cast(c.prehreq_hora AS SIGNED) hora_creado,".
                    "concat(c.prehreq_estado,'') estado,".
                    "'' observacion, ".
                    "0 requiere_recepcion, ".
                    "'' proyecto, ".
                    "'' adjunto1, ".
                    "'' adjunto2, ".
                    "'' adjunto3 ".
                    "from prehreq c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehreq_fecha as date)>=:fechao_ini and cast(c.prehreq_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_compra) && ($id_tiposol=='compra' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
			/*
			 * SE CAMBIO ESTA PARTE POR TENER LA VISTA INCORRECTA AL CONSULTAR EN LA LUPA SOBRE EL LISTADO
			 *
			 * "(case c.prehsol_numero_sol when 0 then c.prehsol_numero else c.prehsol_numero_sol end)  correlativo, ".
			 * "(case c.prehsol_estado when 2 then c.prehsol_numero else c.prehsol_numero_sol end)  correlativo2, ".
			 * POR ESTO
			 * "(case c.prehsol_numero_sol when 0 then c.prehsol_numero else c.prehsol_numero end)  correlativo, ".
			 * "(case c.prehsol_estado when 2 then c.prehsol_numero else c.prehsol_numero end)  correlativo2, ".
			 */
            $qry .= "(select ".
                    "'sol' tipo,".
                    "c.id_prehsol id, ".
                    "c.id_usuario, ".
                    "c.prehsol_usuario usuario, ".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc, ".
                    "(case c.prehsol_numero_sol when 0 then c.prehsol_numero else c.prehsol_numero end)  correlativo, ".
					"(case c.prehsol_estado when 2 then c.prehsol_numero else c.prehsol_numero end)  correlativo2, ".
                    "cc.cc_codigo, ".
                    "cc.cc_descripcion, ".
                    "c.id_empresa, ".
                    "e.emp_nombre nombre_empresa, ".
                    "'' tipo_solicitud,".
                    "cast(c.prehsol_fecha AS SIGNED) fecha_creado, ".
                    "cast(c.prehsol_hora AS SIGNED) hora_creado, ".
                    "concat(c.prehsol_estado,'') estado, ".
                    "c.prehsol_obs1 observacion, ".
                    "0 requiere_recepcion, ".
                    "'' proyecto, ". 
                    "prehsol_coti1 adjunto1, ".
                    "prehsol_coti2 adjunto2, ".
                    "prehsol_coti3 adjunto3 ".
                    "from prehsol c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehsol_fecha as date)>=:fechao_ini and cast(c.prehsol_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_cheque) && ($id_tiposol=='cheque' || $id_tiposol=='')){
            $arr[':fecha_ini'] = $id_anio.'0101';
            $arr[':fecha_fin'] = $id_anio.'1231';

            if((int)$id_mes>0){
                $arr[':fecha_ini'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'01';
                $arr[':fecha_fin'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'31';
            }
            if($union_all){
                $qry .= " union all ";
            }
            $qry .=  "(select ".
                     "'cheque' tipo,".
                     "c.id, ".
                     "c.id_usuario, ".
                     "u.usr_usuario COLLATE utf8_spanish_ci usuario, ".
                     "concat(' ',c.valor_cheque) monto,".
                     "c.moneda moneda,".
                     "c.id_cc, ".
                     "c.id correlativo, ".
					 "c.id correlativo2,".
                     "cc.cc_codigo, ".
                     "cc.cc_descripcion, ".
                     "c.id_empresa, ".
                     "e.emp_nombre nombre_empresa, ".
                     "'' tipo_solicitud,".
                     "c.fecha_solicitud fecha_creado, ".
                     "c.hora_solicitud hora_creado, ".
                     "concat(c.avance,'-',c.status) estado, ".
                     "concat('<b class=bene>',c.nombre_beneficiario,'</b></br>',c.concepto_pago) COLLATE utf8_spanish_ci observacion, ".
                     "ifnull(cg.requiere_recepcion,0) requiere_recepcion, ".
                     "ifnull(c.proyecto,'') proyecto, ".
                     "'' adjunto1, ".
                     "'' adjunto2, ".
                     "'' adjunto3 ".
                     "from cheque_sol c ".
                     "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                     "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                     "left join usuario u ".
                     "on c.id_usuario=u.id_usuario ".
                     "left join empresa e ".
                     "on c.id_empresa=e.id_empresa ".
                     "left join cecosto cc ".
                     "on cc.id_cc=c.id_cc ".
                     "left join cat_gerencias cg ".
                     "on cg.id=c.id_categoria_gasto ".
                     "where c.fecha_solicitud>=:fecha_ini and c.fecha_solicitud<=:fecha_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }   

            if(!empty($proyecto)){
                $qry .= " and upper(ifnull(c.proyecto,'')) like concat('%',:proyecto,'%') ";
                $arr[':proyecto'] = trim(strtoupper($proyecto));
            }

            if(!empty($cat)){
                $qry .= " and ifnull(c.id_categoria_gasto,0) = :cat ";
                $arr[':cat'] = $cat;
            }

            if(!empty($proveedor)){
                $qry .= " and upper(ifnull(c.nombre_beneficiario,'')) like concat('%',:proveedor,'%') ";
                $arr[':proveedor'] = trim(strtoupper($proveedor));
            }

            $qry .= ") ";
            $union_all = TRUE;
        }
        $qry .=") w order by fecha_creado desc,hora_creado desc ";
        if($this->pag>0){
            $elto=($this->pag*$this->limit);
            $qry.="limit $elto,".$this->limit;
        }else{ // por defecto items
            $qry.="limit 0,".$this->limit;
        }
        return $this->db->sql_select_all($qry,$arr);
    }
    function get_solicitud_uautorizadorcc($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$id_usuario=null){
        if(!empty($id_usuario)){
            $this->id_usuario = $id_usuario;
        }
        $arr = array( 
            ':id_usuario' => $this->id_usuario
        );
        if(!empty($id_empresa)){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if(!empty($id_cc)){
            $arr[':id_cc'] = $id_cc;
        }
        if($id_tiposol=='ci' || $id_tiposol=='' || $id_tiposol=='req' || $id_tiposol=='compra'){
            $arr[':fechao_ini'] = $id_anio.'-01-01';
            $arr[':fechao_fin'] = $id_anio.'-12-31';
            if((int)$id_mes>0){
                $arr[':fechao_ini'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-01';
                $arr[':fechao_fin'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-'.cal_days_in_month(CAL_GREGORIAN, $id_mes, $id_anio);
            }
        }
        $qry = "select * from (";
        $union_all = FALSE;
        if(!empty($this->sol_ci) && ($id_tiposol=='ci' || $id_tiposol=='') && false){
            $qry .= "(select ".
                    "'ci' tipo,".
                    "c.id_ci id,".
                    "c.id_usuario,".
                    "c.prod_usuario usuario,".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc,".
                    "c.ci_numero correlativo,".
                    "cc.cc_codigo,".
                    "cc.cc_descripcion,".
                    "c.id_empresa,".
                    "e.emp_nombre nombre_empresa,".
                    "t.descripcion tipo_solicitud,".
                    "cast(c.ci_enc_fecha AS SIGNED) fecha_creado,".
                    "cast(c.ci_enc_hora AS SIGNED) hora_creado,".
                    "concat(c.ci_estado,'') estado,".
                    "c.ci_observacion observacion ".
                    "from ci_enc c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "left join ci_tipo_consumo t ".
                    "on t.id_tipo_consumo=c.id_tipo_consumo ".
                    "where cast(c.ci_enc_fecha as date)>=:fechao_ini and cast(c.ci_enc_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_req) && ($id_tiposol=='req' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "'req' tipo,".
                    "c.id_prehreq id,".
                    "c.id_usuario,".
                    "c.prehreq_usuario usuario,".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc,".
                    "c.prehreq_numero correlativo,".
                    "cc.cc_codigo,".
                    "cc.cc_descripcion,".
                    "c.id_empresa,".
                    "e.emp_nombre nombre_empresa,".
                    "'' tipo_solicitud,".
                    "cast(c.prehreq_fecha AS SIGNED) fecha_creado,".
                    "cast(c.prehreq_hora AS SIGNED) hora_creado,".
                    "concat(c.prehreq_estado,'') estado,".
                    "'' observacion ".
                    "from prehreq c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.prehreq_estado=1 ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehreq_fecha as date)>=:fechao_ini and cast(c.prehreq_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_compra) && ($id_tiposol=='compra' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "'sol' tipo,".
                    "c.id_prehsol id, ".
                    "c.id_usuario, ".
                    "c.prehsol_usuario usuario, ".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc, ".
                    "c.prehsol_numero correlativo, ".
                    "cc.cc_codigo, ".
                    "cc.cc_descripcion, ".
                    "c.id_empresa, ".
                    "e.emp_nombre nombre_empresa, ".
                    "'' tipo_solicitud,".
                    "cast(c.prehsol_fecha AS SIGNED) fecha_creado, ".
                    "cast(c.prehsol_hora AS SIGNED) hora_creado, ".
                    "concat(c.prehsol_estado,'') estado, ".
                    "c.prehsol_obs1 observacion ".
                    "from prehsol c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.prehsol_estado=1 ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehsol_fecha as date)>=:fechao_ini and cast(c.prehsol_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_cheque) && ($id_tiposol=='cheque' || $id_tiposol=='')){
            $arr[':fecha_ini'] = $id_anio.'0101';
            $arr[':fecha_fin'] = $id_anio.'1231';

            if((int)$id_mes>0){
                $arr[':fecha_ini'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'01';
                $arr[':fecha_fin'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'31';
            }
            if($union_all){
                $qry .= " union all ";
            }
            $qry .=  "(select ".
                     "'cheque' tipo,".
                     "c.id, ".
                     "c.id_usuario, ".
                     "u.usr_usuario COLLATE utf8_spanish_ci usuario, ".
                     "concat('',c.valor_cheque) monto,".
                     "c.moneda moneda,".
                     "c.id_cc, ".
                     "c.id correlativo, ".
                     "cc.cc_codigo, ".
                     "cc.cc_descripcion, ".
                     "c.id_empresa, ".
                     "e.emp_nombre nombre_empresa, ".
                     "'' tipo_solicitud,".
                     "c.fecha_solicitud fecha_creado, ".
                     "c.hora_solicitud hora_creado, ".
                     "concat(c.avance,'-',c.status) estado, ".
                     "concat('<b class=bene>',c.nombre_beneficiario,'</b></br>',c.concepto_pago) COLLATE utf8_spanish_ci observacion ".
                     "from cheque_sol c ".
                     "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                     "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.avance='N2' and c.status='R' ".
                     "left join usuario u ".
                     "on c.id_usuario=u.id_usuario ".
                     "left join empresa e ".
                     "on c.id_empresa=e.id_empresa ".
                     "left join cecosto cc ".
                     "on cc.id_cc=c.id_cc ".
                     "where c.fecha_solicitud>=:fecha_ini and c.fecha_solicitud<=:fecha_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }   
            $qry .= ") ";
            $union_all = TRUE;
        }
        $qry .=") w order by fecha_creado desc,hora_creado desc ";
        if($this->pag>0){
            $elto=($this->pag*$this->limit);
            $qry.="limit $elto,".$this->limit;
        }else{ // por defecto items
            $qry.="limit 0,".$this->limit;
        }
        return $this->db->sql_select_all($qry,$arr);
    }
    function get_solicitud_uautorizadorcat($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$id_categoria,$id_estado="P",$proveedor=""){
        $arr = array( 
        );
        if(!empty($id_empresa)){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if(!empty($id_categoria)){
            $arr[':id_categoria'] = $id_categoria;  
        }
        if(!empty($id_cc)){
            $arr[':id_cc'] = $id_cc;
        }
        if($id_tiposol=='ci' || $id_tiposol=='' || $id_tiposol=='req' || $id_tiposol=='compra'){
            $arr[':fechao_ini'] = $id_anio.'-01-01';
            $arr[':fechao_fin'] = $id_anio.'-12-31';
            if((int)$id_mes>0){
                $arr[':fechao_ini'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-01';
                $arr[':fechao_fin'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-'.cal_days_in_month(CAL_GREGORIAN, $id_mes, $id_anio);

                //$arr[':fechao_ini'] = $id_anio.'-01-01';
                //$arr[':fechao_fin'] = $id_anio.'-12-31';
            }
            if(empty($id_anio)){
                $arr[':fechao_ini'] = '2000-01-01';
                $arr[':fechao_fin'] = '2099-12-31';
            }
        }
        $qry = "select * from (";
        $union_all = FALSE;
        if(!empty($this->sol_ci) && ($id_tiposol=='ci' || $id_tiposol=='') && false){
            $qry .= "(select ".
                    "'ci' tipo,".
                    "c.id_ci id,".
                    "c.id_usuario,".
                    "c.prod_usuario usuario,".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc,".
                    "c.ci_numero correlativo,".
                    "cc.cc_codigo,".
                    "cc.cc_descripcion,".
                    "c.id_empresa,".
                    "e.emp_nombre nombre_empresa,".
                    "t.descripcion tipo_solicitud,".
                    "cast(c.ci_enc_fecha AS SIGNED) fecha_creado,".
                    "cast(c.ci_enc_hora AS SIGNED) hora_creado,".
                    "concat(c.ci_estado,'') estado,".
                    "c.ci_observacion observacion ".
                    "from ci_enc c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "left join ci_tipo_consumo t ".
                    "on t.id_tipo_consumo=c.id_tipo_consumo ".
                    "where cast(c.ci_enc_fecha as date)>=:fechao_ini and cast(c.ci_enc_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_req) && ($id_tiposol=='req' || $id_tiposol=='') && false){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "'req' tipo,".
                    "c.id_prehreq id,".
                    "c.id_usuario,".
                    "c.prehreq_usuario usuario,".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc,".
                    "c.prehreq_numero_req correlativo,".
                    "cc.cc_codigo,".
                    "cc.cc_descripcion,".
                    "c.id_empresa,".
                    "e.emp_nombre nombre_empresa,".
                    "'' tipo_solicitud,".
                    "cast(c.prehreq_fecha AS SIGNED) fecha_creado,".
                    "cast(c.prehreq_hora AS SIGNED) hora_creado,".
                    "concat(c.prehreq_estado,'') estado,".
                    "'' observacion ".
                    "from prehreq c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.prehreq_estado=1 ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehreq_fecha as date)>=:fechao_ini and cast(c.prehreq_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_compra) && ($id_tiposol=='compra' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "'sol' tipo,".
                    "c.id_prehsol id, ".
                    "c.id_usuario, ".
                    "c.prehsol_usuario usuario, ".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc, ".
                    "c.prehsol_numero correlativo, ".
					"c.prehsol_numero_sol correlativo2, ".
                    "cc.cc_codigo, ".
                    "cc.cc_descripcion, ".
                    "c.id_empresa, ".
                    "e.emp_nombre nombre_empresa, ".
                    "'' tipo_solicitud,".
                    "cast(c.prehsol_fecha AS SIGNED) fecha_creado, ".
                    "cast(c.prehsol_hora AS SIGNED) hora_creado, ".
                    "concat(c.prehsol_estado,'') estado, ".
                    "c.prehsol_obs1 observacion ".
                    "from prehsol c ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehsol_fecha as date)>=:fechao_ini and cast(c.prehsol_fecha as date)<=:fechao_fin and (c.prehsol_estado=2 or c.prehsol_estado=4 or (c.prehsol_estado=5 and c.prehsol_aprobacion_categoria is null)) and (c.id_categoria=:id_categoria or (c.id_categoria in (select id from cat_gerencias where ".$this->id_usuario." in (user_aproba_1) ))) ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_cheque) && ($id_tiposol=='cheque' || $id_tiposol=='')){
            $arr[':fecha_ini'] = $id_anio.'0101';
            $arr[':fecha_fin'] = $id_anio.'1231';

            if((int)$id_mes>0){
                $arr[':fecha_ini'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'01';
                $arr[':fecha_fin'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'31';

                //$arr[':fecha_ini'] = $id_anio.'0101';
                //$arr[':fecha_fin'] = $id_anio.'1231';
            }
            if(empty($id_anio)){
                $arr[':fecha_ini'] = '20000101';
                $arr[':fecha_fin'] = '20991231';
            }
            if($union_all){
                $qry .= " union all ";
            }
            $qry .=  "(select ".
                     "'cheque' tipo,".
                     "c.id, ".
                     "c.id_usuario, ".
                     "u.usr_usuario COLLATE utf8_spanish_ci usuario, ".
                     "concat('',c.valor_cheque) monto,".
                     "c.moneda moneda,".
                     "c.id_cc, ".
                     "c.id correlativo, ".
					 "c.id correlativo2, ".
                     "cc.cc_codigo, ".
                     "cc.cc_descripcion, ".
                     "c.id_empresa, ".
                     "e.emp_nombre nombre_empresa, ".
                     "'' tipo_solicitud,".
                     "c.fecha_solicitud fecha_creado, ".
                     "c.hora_solicitud hora_creado, ".
                     "concat(c.avance,'-',c.status) estado, ".
                     "concat('<b class=bene>',c.nombre_beneficiario,'</b></br>',c.concepto_pago) COLLATE utf8_spanish_ci observacion ".
                     "from cheque_sol c ".
                     "left join usuario u ".
                     "on c.id_usuario=u.id_usuario ".
                     "left join empresa e ".
                     "on c.id_empresa=e.id_empresa ".
                     "left join cecosto cc ".
                     "on cc.id_cc=c.id_cc ".
                     "where c.fecha_solicitud>=:fecha_ini and ".
                     "      c.fecha_solicitud<=:fecha_fin  ";
                     
                     if($id_estado=="P"){
                         $qry .=  " And ( c.avance = 'N4' and c.status='R') ";
                     }elseif($id_estado=="A"){
                         $qry .=  " And ( c.avance = 'N5' and c.status in ('R','D','E')) ";
                     }elseif($id_estado=="D"){
                         $qry .=  " And ( c.avance = 'N4' and c.status='D') ";
                     }
                     $qry .= " And (c.id_categoria=:id_categoria or ifnull(c.id_aprueba_categoria,0)=".$this->id_usuario.") ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }   
            if(!empty($proveedor)){
                $arr[':proveedor'] = $proveedor;
                $qry .= " and c.nombre_beneficiario=:proveedor ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        $qry .=") w order by fecha_creado desc,hora_creado desc ";
        if($this->pag>0){
            $elto=($this->pag*$this->limit);
            $qry.="limit $elto,".$this->limit;
        }else{ // por defecto items
            $qry.="limit 0,".$this->limit;
        }
		//print($qry);
		//print_r($arr);
        return $this->db->sql_select_all($qry,$arr);
    }
    function get_solicitud_uautorizadorcat_proveedor($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$id_categoria,$id_estado="P"){
        $arr = array( 
        );
        if(!empty($id_empresa)){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if(!empty($id_categoria)){
            $arr[':id_categoria'] = $id_categoria;  
        }
        if(!empty($id_cc)){
            $arr[':id_cc'] = $id_cc;
        }
        
        $qry = "select * from (";
        $union_all = FALSE;
        if(!empty($this->sol_cheque) && ($id_tiposol=='cheque' || $id_tiposol=='')){
            $arr[':fecha_ini'] = $id_anio.'0101';
            $arr[':fecha_fin'] = $id_anio.'1231';

            if((int)$id_mes>0){
                $arr[':fecha_ini'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'01';
                $arr[':fecha_fin'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'31';

                //$arr[':fecha_ini'] = $id_anio.'0101';
                //$arr[':fecha_fin'] = $id_anio.'1231';
            }
            if(empty($id_anio)){
                $arr[':fecha_ini'] = '20000101';
                $arr[':fecha_fin'] = '20991231';
            }
            if($union_all){
                $qry .= " union all ";
            }
            $qry .=  "(select ".
                     "distinct c.nombre_beneficiario ".
                     "from cheque_sol c ".
                     "left join usuario u ".
                     "on c.id_usuario=u.id_usuario ".
                     "left join empresa e ".
                     "on c.id_empresa=e.id_empresa ".
                     "left join cecosto cc ".
                     "on cc.id_cc=c.id_cc ".
                     "where c.fecha_solicitud>=:fecha_ini and ".
                     "      c.fecha_solicitud<=:fecha_fin  ";
                     
                     if($id_estado=="P"){
                         $qry .=  " And ( c.avance = 'N4' and c.status='R') ";
                     }elseif($id_estado=="A"){
                         $qry .=  " And ( c.avance = 'N5' and c.status in ('R','D','E')) ";
                     }elseif($id_estado=="D"){
                         $qry .=  " And ( c.avance = 'N4' and c.status='D') ";
                     }
                     $qry .= " And (c.id_categoria=:id_categoria or ifnull(c.id_aprueba_categoria,0)=".$this->id_usuario.") ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }   
            $qry .= ") ";
            $union_all = TRUE;
        }
        $qry .=") w order by nombre_beneficiario ";

		//print($qry);
		//print_r($arr);
        return $this->db->sql_select_all($qry,$arr);
    }
    function get_solicitud_uautorizadorcat_xx($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$id_categoria){
        $arr = array( 
        );
        if(!empty($id_empresa)){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if(!empty($id_categoria)){
            $arr[':id_categoria'] = $id_categoria;  
        }
        if(!empty($id_cc)){
            $arr[':id_cc'] = $id_cc;
        }
        if($id_tiposol=='ci' || $id_tiposol=='' || $id_tiposol=='req' || $id_tiposol=='compra'){
            $arr[':fechao_ini'] = $id_anio.'-01-01';
            $arr[':fechao_fin'] = $id_anio.'-12-31';
            if((int)$id_mes>0){
                $arr[':fechao_ini'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-01';
                $arr[':fechao_fin'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-'.cal_days_in_month(CAL_GREGORIAN, $id_mes, $id_anio);

                //$arr[':fechao_ini'] = $id_anio.'-01-01';
                //$arr[':fechao_fin'] = $id_anio.'-12-31';
            }
            if(empty($id_anio)){
                $arr[':fechao_ini'] = '2000-01-01';
                $arr[':fechao_fin'] = '2099-12-31';
            }
        }
        $qry = "select * from (";
        $union_all = FALSE;
        if(!empty($this->sol_ci) && ($id_tiposol=='ci' || $id_tiposol=='') && false){
            $qry .= "(select ".
                    "'ci' tipo,".
                    "c.id_ci id,".
                    "c.id_usuario,".
                    "c.prod_usuario usuario,".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc,".
                    "c.ci_numero correlativo,".
                    "cc.cc_codigo,".
                    "cc.cc_descripcion,".
                    "c.id_empresa,".
                    "e.emp_nombre nombre_empresa,".
                    "t.descripcion tipo_solicitud,".
                    "cast(c.ci_enc_fecha AS SIGNED) fecha_creado,".
                    "cast(c.ci_enc_hora AS SIGNED) hora_creado,".
                    "concat(c.ci_estado,'') estado,".
                    "c.ci_observacion observacion ".
                    "from ci_enc c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "left join ci_tipo_consumo t ".
                    "on t.id_tipo_consumo=c.id_tipo_consumo ".
                    "where cast(c.ci_enc_fecha as date)>=:fechao_ini and cast(c.ci_enc_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_req) && ($id_tiposol=='req' || $id_tiposol=='') && false){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "'req' tipo,".
                    "c.id_prehreq id,".
                    "c.id_usuario,".
                    "c.prehreq_usuario usuario,".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc,".
                    "c.prehreq_numero_req correlativo,".
                    "cc.cc_codigo,".
                    "cc.cc_descripcion,".
                    "c.id_empresa,".
                    "e.emp_nombre nombre_empresa,".
                    "'' tipo_solicitud,".
                    "cast(c.prehreq_fecha AS SIGNED) fecha_creado,".
                    "cast(c.prehreq_hora AS SIGNED) hora_creado,".
                    "concat(c.prehreq_estado,'') estado,".
                    "'' observacion ".
                    "from prehreq c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.prehreq_estado=1 ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehreq_fecha as date)>=:fechao_ini and cast(c.prehreq_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_compra) && ($id_tiposol=='compra' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "'sol' tipo,".
                    "c.id_prehsol id, ".
                    "c.id_usuario, ".
                    "c.prehsol_usuario usuario, ".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc, ".
                    "c.prehsol_numero correlativo, ".
					"c.prehsol_numero_sol correlativo2, ".
                    "cc.cc_codigo, ".
                    "cc.cc_descripcion, ".
                    "c.id_empresa, ".
                    "e.emp_nombre nombre_empresa, ".
                    "'' tipo_solicitud,".
                    "cast(c.prehsol_fecha AS SIGNED) fecha_creado, ".
                    "cast(c.prehsol_hora AS SIGNED) hora_creado, ".
                    "concat(c.prehsol_estado,'') estado, ".
                    "c.prehsol_obs1 observacion ".
                    "from prehsol c ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehsol_fecha as date)>=:fechao_ini and cast(c.prehsol_fecha as date)<=:fechao_fin and (c.prehsol_estado=2 or c.prehsol_estado=4 or (c.prehsol_estado=5 and c.prehsol_aprobacion_categoria is null)) and c.id_categoria=:id_categoria ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_cheque) && ($id_tiposol=='cheque' || $id_tiposol=='')){
            $arr[':fecha_ini'] = $id_anio.'0101';
            $arr[':fecha_fin'] = $id_anio.'1231';

            if((int)$id_mes>0){
                $arr[':fecha_ini'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'01';
                $arr[':fecha_fin'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'31';

                //$arr[':fecha_ini'] = $id_anio.'0101';
                //$arr[':fecha_fin'] = $id_anio.'1231';
            }
            if(empty($id_anio)){
                $arr[':fecha_ini'] = '20000101';
                $arr[':fecha_fin'] = '20991231';
            }
            if($union_all){
                $qry .= " union all ";
            }
            $qry .=  "(select ".
                     "'cheque' tipo,".
                     "c.id, ".
                     "c.id_usuario, ".
                     "u.usr_usuario COLLATE utf8_spanish_ci usuario, ".
                     "concat('',c.valor_cheque) monto,".
                     "c.moneda moneda,".
                     "c.id_cc, ".
                     "c.id correlativo, ".
					 "c.id correlativo2, ".
                     "cc.cc_codigo, ".
                     "cc.cc_descripcion, ".
                     "c.id_empresa, ".
                     "e.emp_nombre nombre_empresa, ".
                     "'' tipo_solicitud,".
                     "c.fecha_solicitud fecha_creado, ".
                     "c.hora_solicitud hora_creado, ".
                     "concat(c.avance,'-',c.status) estado, ".
                     "c.observacion COLLATE utf8_spanish_ci observacion ".
                     "from cheque_sol c ".
                     "left join usuario u ".
                     "on c.id_usuario=u.id_usuario ".
                     "left join empresa e ".
                     "on c.id_empresa=e.id_empresa ".
                     "left join cecosto cc ".
                     "on cc.id_cc=c.id_cc ".
                     "where c.fecha_solicitud>=:fecha_ini and c.fecha_solicitud<=:fecha_fin and ((c.avance='N4' and c.status='R')) and c.id_categoria=:id_categoria ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }   
            $qry .= ") ";
            $union_all = TRUE;
        }
        $qry .=") w order by fecha_creado desc,hora_creado desc ";
        if($this->pag>0){
            $elto=($this->pag*$this->limit);
            $qry.="limit $elto,".$this->limit;
        }else{ // por defecto items
            $qry.="limit 0,".$this->limit;
        }
		//print($qry);
		//print_r($arr);
        return $this->db->sql_select_all($qry,$arr);
    }
    function get_solicitud_uautorizador_count($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$cat,$proveedor,$proyecto="",$id_usuario=null){
        if(!empty($id_usuario)){
            $this->id_usuario = $id_usuario;
        }
        $arr = array( 
            ':id_usuario' => $this->id_usuario
        );
        if(!empty($id_empresa)){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if(!empty($id_cc)){
            $arr[':id_cc'] = $id_cc;
        }
        if(($id_tiposol=='ci' || $id_tiposol=='' || $id_tiposol=='req' || $id_tiposol=='compra') && (!empty($this->sol_ci) || !empty($this->sol_req) || !empty($this->sol_compra))){
            $arr[':fechao_ini'] = $id_anio.'-01-01';
            $arr[':fechao_fin'] = $id_anio.'-12-31';
            if((int)$id_mes>0){
                $arr[':fechao_ini'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-01';
                
                $arr[':fechao_fin'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-'.cal_days_in_month(CAL_GREGORIAN, $id_mes, $id_anio);
            }
        }
        $qry = "select sum(w.contador) contador from (";
        $union_all = FALSE;
        if(!empty($this->sol_ci) && ($id_tiposol=='ci' || $id_tiposol=='')){
            $qry .= "(select ".
                    "count(*) contador ".
                    "from ci_enc c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "left join ci_tipo_consumo t ".
                    "on t.id_tipo_consumo=c.id_tipo_consumo ".
                    "where cast(c.ci_enc_fecha as date)>=:fechao_ini and cast(c.ci_enc_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_req) && ($id_tiposol=='req' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "count(*) contador ".
                    "from prehreq c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehreq_fecha as date)>=:fechao_ini and cast(c.prehreq_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_compra) && ($id_tiposol=='compra' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "count(*) contador ".
                    "from prehsol c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehsol_fecha as date)>=:fechao_ini and cast(c.prehsol_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_cheque) && ($id_tiposol=='cheque' || $id_tiposol=='')){
            $arr[':fecha_ini'] = $id_anio.'0101';
            $arr[':fecha_fin'] = $id_anio.'1231';

            if((int)$id_mes>0){
                $arr[':fecha_ini'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'01';
                $arr[':fecha_fin'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'31';
            }
            if($union_all){
                $qry .= " union all ";
            }
            $qry .=  "(select ".
                     "count(*) contador ".
                     "from cheque_sol c ".
                     "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                     "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                     "left join usuario u ".
                     "on c.id_usuario=u.id_usuario ".
                     "left join empresa e ".
                     "on c.id_empresa=e.id_empresa ".
                     "left join cecosto cc ".
                     "on cc.id_cc=c.id_cc ".
                     "where c.fecha_solicitud>=:fecha_ini and c.fecha_solicitud<=:fecha_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }

            if(!empty($proyecto)){
                $qry .= " and upper(ifnull(c.proyecto,'')) like concat('%',:proyecto,'%') ";
                $arr[':proyecto'] = trim(strtoupper($proyecto));
            }

            if(!empty($cat)){
                $qry .= " and ifnull(c.id_categoria_gasto,0) = :cat ";
                $arr[':cat'] = $cat;
            }

            if(!empty($proveedor)){
                $qry .= " and upper(ifnull(c.nombre_beneficiario,'')) like concat('%',:proveedor,'%') ";
                $arr[':proveedor'] = trim(strtoupper($proveedor));
            }

            $qry .= ") ";
            $union_all = TRUE;
        }
        $qry .=") w";
        $result = $this->db->sql_select_one($qry, $arr);
        if(!empty($result)){
            if(!empty($result->contador)){
                return (int)$result->contador;
            }
        }
        return 0;
    }

    function get_solicitud_uautorizador_categoria($id_empresa,$id_cc,$id_anio,$id_mes,$proyecto="",$proveedor=""){
        $arr = array( 
            ':id_usuario' => $this->id_usuario
        );
        if(!empty($id_empresa)){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if(!empty($id_cc)){
            $arr[':id_cc'] = $id_cc;
        }
        
        $arr[':fecha_ini'] = $id_anio.'0101';
        $arr[':fecha_fin'] = $id_anio.'1231';

        if((int)$id_mes>0){
            $arr[':fecha_ini'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'01';
            $arr[':fecha_fin'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'31';
        }
        
        $qry =  "select distinct ".
                "cg.id, ".
                "(case cg.gcia_aprueba ".
                "    when 0 then cg.gcia ".
                "    else 'Aprueba C/Gerencia' ".
                "end) gcia, ".
                "cg.categoria ".
                "from cheque_sol c ".
                "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                "inner join cat_gerencias cg ".
                "on ifnull(c.id_categoria_gasto,0)=cg.id ".
                "where c.fecha_solicitud>=:fecha_ini and c.fecha_solicitud<=:fecha_fin ";
        if(!empty($id_empresa)){
            $arr[':id_empresa'] = $id_empresa;  
            $qry .= "and c.id_empresa=:id_empresa ";
        }
        if(!empty($id_cc)){
            $arr[':id_cc'] = $id_cc;
            $qry .= "and c.id_cc=:id_cc ";
        }

        if(!empty($proyecto)){
            $qry .= " and upper(ifnull(c.proyecto,'')) like concat('%',:proyecto,'%') ";
            $arr[':proyecto'] = trim(strtoupper($proyecto));
        }

        if(!empty($proveedor)){
            $qry .= " and upper(ifnull(c.nombre_beneficiario,'')) like concat('%',:proveedor,'%') ";
            $arr[':proveedor'] = trim(strtoupper($proveedor));
        }

        $qry .= " Order by gcia,categoria ";

        return $this->db->sql_select_all($qry, $arr);
        
    }
    function get_solicitud_uautorizadorcc_count($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$id_usuario=null){
        if(!empty($id_usuario)){
            $this->id_usuario = $id_usuario;
        }
        $arr = array( 
            ':id_usuario' => $this->id_usuario
        );
        if(!empty($id_empresa)){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if(!empty($id_cc)){
            $arr[':id_cc'] = $id_cc;
        }
        if($id_tiposol=='ci' || $id_tiposol=='' || $id_tiposol=='req' || $id_tiposol=='compra'){
            $arr[':fechao_ini'] = $id_anio.'-01-01';
            $arr[':fechao_fin'] = $id_anio.'-12-31';
            if((int)$id_mes>0){
                $arr[':fechao_ini'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-01';
                $arr[':fechao_fin'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-'.cal_days_in_month(CAL_GREGORIAN, $id_mes, $id_anio);
            }
        }
        $qry = "select sum(w.contador) contador from (";
        $union_all = FALSE;
        if(!empty($this->sol_ci) && ($id_tiposol=='ci' || $id_tiposol=='') && false){
            $qry .= "(select ".
                    "count(*) contador ".
                    "from ci_enc c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "left join ci_tipo_consumo t ".
                    "on t.id_tipo_consumo=c.id_tipo_consumo ".
                    "where cast(c.ci_enc_fecha as date)>=:fechao_ini and cast(c.ci_enc_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_req) && ($id_tiposol=='req' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "count(*) contador ".
                    "from prehreq c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.prehreq_estado=1 ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehreq_fecha as date)>=:fechao_ini and cast(c.prehreq_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_compra) && ($id_tiposol=='compra' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "count(*) contador ".
                    "from prehsol c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.prehsol_estado=1 ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehsol_fecha as date)>=:fechao_ini and cast(c.prehsol_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_cheque) && ($id_tiposol=='cheque' || $id_tiposol=='')){
            $arr[':fecha_ini'] = $id_anio.'0101';
            $arr[':fecha_fin'] = $id_anio.'1231';

            if((int)$id_mes>0){
                $arr[':fecha_ini'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'01';
                $arr[':fecha_fin'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'31';
            }
            if($union_all){
                $qry .= " union all ";
            }
            $qry .=  "(select ".
                     "count(*) contador ".
                     "from cheque_sol c ".
                     "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                     "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.avance='N2' and c.status='R' ".
                     "left join usuario u ".
                     "on c.id_usuario=u.id_usuario ".
                     "left join empresa e ".
                     "on c.id_empresa=e.id_empresa ".
                     "left join cecosto cc ".
                     "on cc.id_cc=c.id_cc ".
                     "where c.fecha_solicitud>=:fecha_ini and c.fecha_solicitud<=:fecha_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        $qry .=") w";
        $result = $this->db->sql_select_one($qry,$arr);
        if(!empty($result)){
            if(!empty($result->contador)){
                return (int)$result->contador;
            }
        }
        return 0;
    }
    function get_solicitud_uautorizadorcat_count($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$id_categoria,$id_estado="P",$proveedor=""){
        $arr = array( 
        );
        if(!empty($id_empresa)){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if(!empty($id_categoria)){
            $arr[':id_categoria'] = $id_categoria;  
        }
        if(!empty($id_cc)){
            $arr[':id_cc'] = $id_cc;
        }
        if(!empty($id_estado)){
            //$arr[':id_estado'] = $id_estado;
        }
        
        if($id_tiposol=='ci' || $id_tiposol=='' || $id_tiposol=='req' || $id_tiposol=='compra'){
            $arr[':fechao_ini'] = $id_anio.'-01-01';
            $arr[':fechao_fin'] = $id_anio.'-12-31';
            if((int)$id_mes>0){
                $arr[':fechao_ini'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-01';
                $arr[':fechao_fin'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-'.cal_days_in_month(CAL_GREGORIAN, $id_mes, $id_anio);

                //$arr[':fechao_ini'] = $id_anio.'-01-01';
                //$arr[':fechao_fin'] = $id_anio.'-12-31';
            }
            if(empty($id_anio)){
                $arr[':fechao_ini'] = '2000-01-01';
                $arr[':fechao_fin'] = '2099-12-31';
            }
        }
        $qry = "select sum(w.contador) contador from (";
        $union_all = FALSE;
        if(!empty($this->sol_ci) && ($id_tiposol=='ci' || $id_tiposol=='') && false){
            $qry .= "(select ".
                    "count(*) contador ".
                    "from ci_enc c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "left join ci_tipo_consumo t ".
                    "on t.id_tipo_consumo=c.id_tipo_consumo ".
                    "where cast(c.ci_enc_fecha as date)>=:fechao_ini and cast(c.ci_enc_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_req) && ($id_tiposol=='req' || $id_tiposol=='') && false){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "count(*) contador ".
                    "from prehreq c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.prehreq_estado=1 ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehreq_fecha as date)>=:fechao_ini and cast(c.prehreq_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_compra) && ($id_tiposol=='compra' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "count(*) contador ".
                    "from prehsol c ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehsol_fecha as date)>=:fechao_ini and cast(c.prehsol_fecha as date)<=:fechao_fin and (c.prehsol_estado=2 or c.prehsol_estado=4 or (c.prehsol_estado=5 and c.prehsol_aprobacion_categoria is null)) and c.id_categoria=:id_categoria ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_cheque) && ($id_tiposol=='cheque' || $id_tiposol=='')){
            $arr[':fecha_ini'] = $id_anio.'0101';
            $arr[':fecha_fin'] = $id_anio.'1231';

            if((int)$id_mes>0){
                $arr[':fecha_ini'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'01';
                $arr[':fecha_fin'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'31';

                //$arr[':fecha_ini'] = $id_anio.'0101';
                //$arr[':fecha_fin'] = $id_anio.'1231';
            }
            if(empty($id_anio)){
                $arr[':fecha_ini'] = '20000101';
                $arr[':fecha_fin'] = '20991231';
            }
            if($union_all){
                $qry .= " union all ";
            }
            $qry .=  "(select ".
                     "count(*) contador ".
                     "from cheque_sol c ".
                     "left join usuario u ".
                     "on c.id_usuario=u.id_usuario ".
                     "left join empresa e ".
                     "on c.id_empresa=e.id_empresa ".
                     "left join cecosto cc ".
                     "on cc.id_cc=c.id_cc ".
                     "where c.fecha_solicitud>=:fecha_ini and ".
                     "      c.fecha_solicitud<=:fecha_fin  ";
                     
                     if($id_estado=="P"){
                         $qry .=  " And ( c.avance = 'N4' and c.status='R') ";
                     }elseif($id_estado=="A"){
                         $qry .=  " And ( c.avance = 'N5' and c.status in ('R','D','E')) ";
                     }elseif($id_estado=="D"){
                         $qry .=  " And ( c.avance = 'N4' and c.status='D') ";
                     }
                     $qry .= " And (c.id_categoria=:id_categoria or ifnull(c.id_aprueba_categoria,0)=".$this->id_usuario.") ";

            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }

            if(!empty($proveedor)){
                $arr[':proveedor'] = $proveedor;
                $qry .= " and c.nombre_beneficiario=:proveedor ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        $qry .=") w";
        $result = $this->db->sql_select_one($qry,$arr);
        if(!empty($result)){
            if(!empty($result->contador)){
                return (int)$result->contador;
            }
        }
        return 0;
    }
    function get_solicitud_uautorizadorcat_count_xx($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$id_categoria){
        $arr = array( 
        );
        if(!empty($id_empresa)){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if(!empty($id_categoria)){
            $arr[':id_categoria'] = $id_categoria;  
        }
        if(!empty($id_cc)){
            $arr[':id_cc'] = $id_cc;
        }
        if($id_tiposol=='ci' || $id_tiposol=='' || $id_tiposol=='req' || $id_tiposol=='compra'){
            $arr[':fechao_ini'] = $id_anio.'-01-01';
            $arr[':fechao_fin'] = $id_anio.'-12-31';
            if((int)$id_mes>0){
                $arr[':fechao_ini'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-01';
                $arr[':fechao_fin'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-'.cal_days_in_month(CAL_GREGORIAN, $id_mes, $id_anio);

                //$arr[':fechao_ini'] = $id_anio.'-01-01';
                //$arr[':fechao_fin'] = $id_anio.'-12-31';
            }
            if(empty($id_anio)){
                $arr[':fechao_ini'] = '2000-01-01';
                $arr[':fechao_fin'] = '2099-12-31';
            }
        }
        $qry = "select sum(w.contador) contador from (";
        $union_all = FALSE;
        if(!empty($this->sol_ci) && ($id_tiposol=='ci' || $id_tiposol=='') && false){
            $qry .= "(select ".
                    "count(*) contador ".
                    "from ci_enc c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "left join ci_tipo_consumo t ".
                    "on t.id_tipo_consumo=c.id_tipo_consumo ".
                    "where cast(c.ci_enc_fecha as date)>=:fechao_ini and cast(c.ci_enc_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_req) && ($id_tiposol=='req' || $id_tiposol=='') && false){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "count(*) contador ".
                    "from prehreq c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.prehreq_estado=1 ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehreq_fecha as date)>=:fechao_ini and cast(c.prehreq_fecha as date)<=:fechao_fin ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_compra) && ($id_tiposol=='compra' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "count(*) contador ".
                    "from prehsol c ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where cast(c.prehsol_fecha as date)>=:fechao_ini and cast(c.prehsol_fecha as date)<=:fechao_fin and (c.prehsol_estado=2 or c.prehsol_estado=4 or (c.prehsol_estado=5 and c.prehsol_aprobacion_categoria is null)) and c.id_categoria=:id_categoria ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_cheque) && ($id_tiposol=='cheque' || $id_tiposol=='')){
            $arr[':fecha_ini'] = $id_anio.'0101';
            $arr[':fecha_fin'] = $id_anio.'1231';

            if((int)$id_mes>0){
                $arr[':fecha_ini'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'01';
                $arr[':fecha_fin'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'31';

                //$arr[':fecha_ini'] = $id_anio.'0101';
                //$arr[':fecha_fin'] = $id_anio.'1231';
            }
            if(empty($id_anio)){
                $arr[':fecha_ini'] = '20000101';
                $arr[':fecha_fin'] = '20991231';
            }
            if($union_all){
                $qry .= " union all ";
            }
            $qry .=  "(select ".
                     "count(*) contador ".
                     "from cheque_sol c ".
                     "left join usuario u ".
                     "on c.id_usuario=u.id_usuario ".
                     "left join empresa e ".
                     "on c.id_empresa=e.id_empresa ".
                     "left join cecosto cc ".
                     "on cc.id_cc=c.id_cc ".
                     "where c.fecha_solicitud>=:fecha_ini and c.fecha_solicitud<=:fecha_fin and c.avance='N4' and c.status='R' and c.id_categoria=:id_categoria ";
            if(!empty($id_empresa)){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= "and c.id_empresa=:id_empresa ";
            }
            if(!empty($id_cc)){
                $arr[':id_cc'] = $id_cc;
                $qry .= "and c.id_cc=:id_cc ";
            }
            $qry .= ") ";
            $union_all = TRUE;
        }
        $qry .=") w";
        $result = $this->db->sql_select_one($qry,$arr);
        if(!empty($result)){
            if(!empty($result->contador)){
                return (int)$result->contador;
            }
        }
        return 0;
    }
    function get_solicitud_uautorizador_contador(){
        $arr = array( 
            ':id_usuario' => $this->id_usuario
        );
        $qry = "select sum(w.contador) contador from (";
        $union_all = FALSE;
        if(!empty($this->sol_ci) && false){
            $qry .= "(select ".
                    "count(*) contador ".
                    "from ci_enc c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "left join ci_tipo_consumo t ".
                    "on t.id_tipo_consumo=c.id_tipo_consumo ";
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_req)){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "count(*) contador ".
                    "from prehreq c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.prehreq_estado=1 ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ";
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_compra)){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "count(*) contador ".
                    "from prehsol c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.prehsol_estado=1 ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ";
            $qry .= ") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_cheque)){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .=  "(select ".
                     "count(*) contador ".
                     "from cheque_sol c ".
                     "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                     "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.avance='N2' and c.status='R' ".
                     "left join usuario u ".
                     "on c.id_usuario=u.id_usuario ".
                     "left join empresa e ".
                     "on c.id_empresa=e.id_empresa ".
                     "left join cecosto cc ".
                     "on cc.id_cc=c.id_cc ";
            $qry .= ") ";
            $union_all = TRUE;
        }
        $qry .=") w";
        $result = $this->db->sql_select_one($qry,$arr);
        if(!empty($result)){
            if(!empty($result->contador)){
                return (int)$result->contador;
            }
        }
        return 0;
    }
    function get_solicitud_uautorizador_ult($id_empresa,$id_cc,$id_usuario=null){
        if(!empty($id_usuario)){
            $this->id_usuario = $id_usuario;
        }
        $arr = array( 
            ':id_usuario' => $this->id_usuario
        );
        if($id_empresa!=0){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if($id_cc!=0){
            $arr[':id_cc'] = $id_cc;
        }
        $qry = "select * from (";
        $union_all = FALSE;
        if(!empty($this->sol_ci) && false){
            $qry .= "(select ".
                    "'ci' tipo,".
                    "c.id_ci id,".
                    "c.id_usuario,".
                    "c.prod_usuario usuario,".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc,".
                    "c.ci_numero correlativo,".
                    "cc.cc_codigo,".
                    "cc.cc_descripcion,".
                    "c.id_empresa,".
                    "e.emp_nombre nombre_empresa,".
                    "t.descripcion tipo_solicitud,".
                    "cast(c.ci_enc_fecha AS SIGNED) fecha_creado,".
                    "cast(c.ci_enc_hora AS SIGNED) hora_creado,".
                    "concat(c.ci_estado,'') estado,".
                    "c.ci_observacion observacion, ".
                    "0 requiere_recepcion ".
                    "from ci_enc c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "left join ci_tipo_consumo t ".
                    "on t.id_tipo_consumo=c.id_tipo_consumo ";
            if($id_empresa!=0){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= ($id_empresa!=0 ? "where c.id_empresa=:id_empresa " : " ");
            }
            if($id_cc!=0){
                $arr[':id_cc'] = $id_cc;
                $qry .= ($id_cc!=0 ? ($id_empresa!=0 ? "where" : "and")." c.id_cc=:id_cc " : " ");
            }
            $qry .= " order by fecha_creado desc,hora_creado desc limit 0,".$this->limit.") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_req)){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "'req' tipo,".
                    "c.id_prehreq id,".
                    "c.id_usuario,".
                    "c.prehreq_usuario usuario,".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc,".
                    "c.prehreq_numero correlativo,".
                    "cc.cc_codigo,".
                    "cc.cc_descripcion,".
                    "c.id_empresa,".
                    "e.emp_nombre nombre_empresa,".
                    "'' tipo_solicitud,".
                    "cast(c.prehreq_fecha AS SIGNED) fecha_creado,".
                    "cast(c.prehreq_hora AS SIGNED) hora_creado,".
                    "concat(c.prehreq_estado,'') estado,".
                    "'' observacion, ".
                    "0 requiere_recepcion ".
                    "from prehreq c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.prehreq_estado=1 ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ";
            if($id_empresa!=0){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= ($id_empresa!=0 ? "where c.id_empresa=:id_empresa " : " ");
            }
            if($id_cc!=0){
                $arr[':id_cc'] = $id_cc;
                $qry .= ($id_cc!=0 ? ($id_empresa!=0 ? "where" : "and")." c.id_cc=:id_cc " : " ");
            }
            $qry .= "order by fecha_creado desc,hora_creado desc limit 0,".$this->limit.") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_compra)){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "'sol' tipo,".
                    "c.id_prehsol id, ".
                    "c.id_usuario, ".
                    "c.prehsol_usuario usuario, ".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc, ".
                    "c.prehsol_numero correlativo, ".
                    "cc.cc_codigo, ".
                    "cc.cc_descripcion, ".
                    "c.id_empresa, ".
                    "e.emp_nombre nombre_empresa, ".
                    "'' tipo_solicitud,".
                    "cast(c.prehsol_fecha AS SIGNED) fecha_creado, ".
                    "cast(c.prehsol_hora AS SIGNED) hora_creado, ".
                    "concat(c.prehsol_estado,'') estado, ".
                    "c.prehsol_obs1 observacion, ".
                    "0 requiere_recepcion ".
                    "from prehsol c ".
                    "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                    "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.prehsol_estado=1 ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ";
            if($id_empresa!=0){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= ($id_empresa!=0 ? "where c.id_empresa=:id_empresa " : " ");
            }
            if($id_cc!=0){
                $arr[':id_cc'] = $id_cc;
                $qry .= ($id_cc!=0 ? ($id_empresa!=0 ? "where" : "and")." c.id_cc=:id_cc " : " ");
            }
            $qry .= "order by fecha_creado desc,hora_creado desc limit 0,".$this->limit.") ";
            $union_all = TRUE;
        }
        if(!empty($this->sol_cheque)){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .=  "(select ".
                     "'cheque' tipo,".
                     "c.id, ".
                     "c.id_usuario, ".
                     "u.usr_usuario COLLATE utf8_spanish_ci usuario, ".
                     "concat('',c.valor_cheque) monto,".
                     "c.moneda moneda,".
                     "c.id_cc, ".
                     "c.id correlativo, ".
                     "cc.cc_codigo, ".
                     "cc.cc_descripcion, ".
                     "c.id_empresa, ".
                     "e.emp_nombre nombre_empresa, ".
                     "'' tipo_solicitud,".
                     "c.fecha_solicitud fecha_creado, ".
                     "c.hora_solicitud hora_creado, ".
                     "concat(c.avance,'-',c.status) estado, ".
                     "concat('<b class=bene>',c.nombre_beneficiario,'</b></br>',c.concepto_pago) COLLATE utf8_spanish_ci observacion, ".
                     "ifnull(cg.requiere_recepcion,0) requiere_recepcion ".
                     "from cheque_sol c ".
                     "inner join (SELECT id_empresa,id_cc from acc_emp_cc where id_usuario=:id_usuario group by id_empresa,id_cc) ac ".
                     "on c.id_cc=ac.id_cc and c.id_empresa=ac.id_empresa and c.avance='N2' and c.status='R' ".
                     "left join usuario u ".
                     "on c.id_usuario=u.id_usuario ".
                     "left join empresa e ".
                     "on c.id_empresa=e.id_empresa ".
                     "left join cecosto cc ".
                     "on cc.id_cc=c.id_cc ".
                     "left join cat_gerencias cg ".
                     "on cg.id=c.id_categoria_gasto ";
            if($id_empresa!=0){
                $arr[':id_empresa'] = $id_empresa;  
                $qry .= ($id_empresa!=0 ? "where c.id_empresa=:id_empresa " : " ");
            }
            if($id_cc!=0){
                $arr[':id_cc'] = $id_cc;
                $qry .= ($id_cc!=0 ? ($id_empresa!=0 ? "where" : "and")." c.id_cc=:id_cc " : " ");
            }
            $qry .= "order by fecha_creado desc,hora_creado desc limit 0,".$this->limit.") ";
            $union_all = TRUE;
        }
        $qry .=") w order by fecha_creado desc,hora_creado desc limit 0,".$this->limit;
        return $this->db->sql_select_all($qry,$arr);
    }
    function get_solicitud_ufinal($id_empresa,$id_cc,$id_usuario,$id_anio,$id_mes,$id_tiposol,$cat,$proyecto="",$proveedor=""){
        if(!empty($id_usuario)){
            $this->id_usuario = $id_usuario;
        }
        $arr = array( 
            ':id_usuario' => $this->id_usuario
        );
        if(!empty($id_empresa)){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if(!empty($id_cc)){
            $arr[':id_cc'] = $id_cc;
        }
        if($id_tiposol=='ci' || $id_tiposol=='' || $id_tiposol=='req' || $id_tiposol=='compra'){
            $arr[':fechao_ini'] = $id_anio.'-01-01';
            $arr[':fechao_fin'] = $id_anio.'-12-31';
            if((int)$id_mes>0){
                $arr[':fechao_ini'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-01';
                $arr[':fechao_fin'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-'.cal_days_in_month(CAL_GREGORIAN, $id_mes, $id_anio);
            }
        }
        $qry = "select * from (";
        $union_all = FALSE;
        if(!empty($this->sol_ci) && ($id_tiposol=='ci' || $id_tiposol=='')){
            $qry .= "select ".
                    "'ci' tipo,".
                    "c.id_ci id,".
                    "c.id_usuario,".
                    "c.prod_usuario usuario,".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc,".
                    "c.ci_numero correlativo,".
                    "cc.cc_codigo,".
                    "cc.cc_descripcion,".
                    "c.id_empresa,".
                    "e.emp_nombre nombre_empresa,".
                    "t.descripcion tipo_solicitud,".
                    "cast(c.ci_enc_fecha AS SIGNED) fecha_creado,".
                    "cast(c.ci_enc_hora AS SIGNED) hora_creado,".
                    "concat(c.ci_estado,'') estado,".
                    "c.ci_observacion observacion, ".
                    "0 requiere_recepcion, ".
                    "'' proyecto, ".
                    "'' adjunto1, ".
                    "'' adjunto2, ".
                    "'' adjunto3 ".
                    "from ci_enc c ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "left join ci_tipo_consumo t ".
                    "on t.id_tipo_consumo=c.id_tipo_consumo ".
                    "where c.id_usuario=:id_usuario and cast(c.ci_enc_fecha as date)>=:fechao_ini and cast(c.ci_enc_fecha as date)<=:fechao_fin  ".(!empty($id_cc) ? " and c.id_cc=:id_cc " : " ").(!empty($id_empresa) ? " and c.id_empresa=:id_empresa " : " ");
                    $union_all = TRUE;
        }
        if(!empty($this->sol_req) && ($id_tiposol=='req' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "select ".
                    "'req' tipo,".
                    "c.id_prehreq id,".
                    "c.id_usuario,".
                    "c.prehreq_usuario usuario,".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc,".
                    "c.prehreq_numero correlativo,".
                    "cc.cc_codigo,".
                    "cc.cc_descripcion,".
                    "c.id_empresa,".
                    "e.emp_nombre nombre_empresa,".
                    "'' tipo_solicitud,".
                    "cast(c.prehreq_fecha AS SIGNED) fecha_creado,".
                    "cast(c.prehreq_hora AS SIGNED) hora_creado,".
                    "concat(c.prehreq_estado,'') estado,".
                    "'' observacion, ".
                    "0 requiere_recepcion, ".
                    "'' proyecto, ".
                    "'' adjunto1, ".
                    "'' adjunto2, ".
                    "'' adjunto3 ".
                    "from prehreq c ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where c.id_usuario=:id_usuario and cast(c.prehreq_fecha as date)>=:fechao_ini and cast(c.prehreq_fecha as date)<=:fechao_fin ".(!empty($id_cc) ? " and c.id_cc=:id_cc " : " ").(!empty($id_empresa) ? " and c.id_empresa=:id_empresa " : " ");
                    $union_all = TRUE;
        }
        if(!empty($this->sol_compra) && ($id_tiposol=='compra' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "select ".
                    "'sol' tipo,".
                    "c.id_prehsol id, ".
                    "c.id_usuario, ".
                    "c.prehsol_usuario usuario, ".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc, ".
                    "c.prehsol_numero correlativo, ".
                    "cc.cc_codigo, ".
                    "cc.cc_descripcion, ".
                    "c.id_empresa, ".
                    "e.emp_nombre nombre_empresa, ".
                    "'' tipo_solicitud,".
                    "cast(c.prehsol_fecha AS SIGNED) fecha_creado, ".
                    "cast(c.prehsol_hora AS SIGNED) hora_creado, ".
                    "concat(c.prehsol_estado,'') estado, ".
                    "c.prehsol_obs1 observacion, ".
                    "0 requiere_recepcion, ".
                    "'' proyecto, ".
                    "c.prehsol_coti1 adjunto1, ".
                    "c.prehsol_coti2 adjunto2, ".
                    "c.prehsol_coti3 adjunto3 ".
                    "from prehsol c ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where c.id_usuario=:id_usuario and cast(c.prehsol_fecha as date)>=:fechao_ini and cast(c.prehsol_fecha as date)<=:fechao_fin ".(!empty($id_cc) ? " and c.id_cc=:id_cc " : " ").(!empty($id_empresa) ? " and c.id_empresa=:id_empresa " : " ");
                    $union_all = TRUE;
        }
        if(!empty($this->sol_cheque) && ($id_tiposol=='cheque' || $id_tiposol=='')){
            $arr[':fecha_ini'] = $id_anio.'0101';
            $arr[':fecha_fin'] = $id_anio.'1231';

            if((int)$id_mes>0){
                $arr[':fecha_ini'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'01';
                $arr[':fecha_fin'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'31';
            }
            if($union_all){
                $qry .= " union all ";
            }
            $qry .=  "select ".
                     "'cheque' tipo,".
                     "c.id, ".
                     "c.id_usuario, ".
                     "u.usr_usuario COLLATE utf8_spanish_ci usuario, ".
                     "concat('',c.valor_cheque) monto,".
                     "c.moneda moneda,".
                     "c.id_cc, ".
                     "c.id correlativo, ".
                     "cc.cc_codigo, ".
                     "cc.cc_descripcion, ".
                     "c.id_empresa, ".
                     "e.emp_nombre nombre_empresa, ".
                     "'' tipo_solicitud,".
                     "c.fecha_solicitud fecha_creado, ".
                     "c.hora_solicitud hora_creado, ".
                     "concat(c.avance,'-',c.status) estado, ".
                     "concat('<b class=bene>',c.nombre_beneficiario,'</b></br>',c.concepto_pago) COLLATE utf8_spanish_ci observacion, ".
                     "ifnull(cg.requiere_recepcion,0) requiere_recepcion, ".
                     "ifnull(c.proyecto,'') proyecto, ".
                     "'' adjunto1, ".
                     "'' adjunto2, ".
                     "'' adjunto3 ".
                     "from cheque_sol c ".
                     "left join usuario u ".
                     "on c.id_usuario=u.id_usuario ".
                     "left join empresa e ".
                     "on c.id_empresa=e.id_empresa ".
                     "left join cecosto cc ".
                     "on cc.id_cc=c.id_cc ".
                     "left join cat_gerencias cg ".
                     "on cg.id=c.id_categoria_gasto ".
                     "where c.id_usuario=:id_usuario and c.fecha_solicitud>=:fecha_ini and c.fecha_solicitud<=:fecha_fin ".(!empty($id_cc) ? " and c.id_cc=:id_cc " : " ").(!empty($id_empresa) ? " and c.id_empresa=:id_empresa " : " ");
                     $union_all = TRUE;
 
            if(!empty($proyecto)){
                $qry .= " and upper(ifnull(c.proyecto,'')) like concat('%',:proyecto,'%') ";
                $arr[':proyecto'] = trim(strtoupper($proyecto));
            }

            if(!empty($cat)){
                $qry .= " and ifnull(c.id_categoria_gasto,0)=:cat ";
                $arr[':cat'] = $cat;
            }

            if(!empty($proveedor)){
                $qry .= " and upper(ifnull(c.nombre_beneficiario,'')) like concat('%',:proveedor,'%') ";
                $arr[':proveedor'] = trim(strtoupper($proveedor));
            }
        }
        $qry .=") w order by fecha_creado desc,hora_creado desc ";
        if($this->pag>0){
            $elto=($this->pag*$this->limit);
            $qry.="limit $elto,".$this->limit;
        }else{ // por defecto items
            $qry.="limit 0,".$this->limit;
        }
        return $this->db->sql_select_all($qry,$arr);
    }
    function depurar($obj){
        echo "<pre>";
        print_r($obj);
        echo "</pre>";
    }
    function get_solicitud_ufinal_count($id_empresa,$id_cc,$id_usuario,$id_anio,$id_mes,$id_tiposol,$cat,$proyecto="",$proveedor=""){
        if(!empty($id_usuario)){
            $this->id_usuario = $id_usuario;
        }
        $arr = array( 
            ':id_usuario' => $this->id_usuario
        );
        if(!empty($id_empresa)){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if(!empty($id_cc)){
            $arr[':id_cc'] = $id_cc;
        }

        if($id_tiposol=='ci' || $id_tiposol=='' || $id_tiposol=='req' || $id_tiposol=='compra'){
            $arr[':fechao_ini'] = $id_anio.'-01-01';
            $arr[':fechao_fin'] = $id_anio.'-12-31';
            if((int)$id_mes>0){
                $arr[':fechao_ini'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-01';
                $arr[':fechao_fin'] = $id_anio.'-'.str_pad($id_mes,2,'0',STR_PAD_LEFT).'-'.cal_days_in_month(CAL_GREGORIAN, $id_mes, $id_anio);
            }
        }
        $qry = "select sum(w.contador) contador from (";
        $union_all = FALSE;
        if(!empty($this->sol_ci) && ($id_tiposol=='ci' || $id_tiposol=='')){
            $qry .= "select ".
                    "count(*) contador ".
                    "from ci_enc c ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "left join ci_tipo_consumo t ".
                    "on t.id_tipo_consumo=c.id_tipo_consumo ".
                    "where c.id_usuario=:id_usuario and cast(c.ci_enc_fecha as date)>=:fechao_ini and cast(c.ci_enc_fecha as date)<=:fechao_fin  ".(!empty($id_cc) ? " and c.id_cc=:id_cc " : " ").(!empty($id_empresa) ? " and c.id_empresa=:id_empresa " : " ");
                    $union_all = TRUE;
        }
        if(!empty($this->sol_req) && ($id_tiposol=='req' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "select ".
                    "count(*) contador ".
                    "from prehreq c ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where c.id_usuario=:id_usuario and cast(c.prehreq_fecha as date)>=:fechao_ini and cast(c.prehreq_fecha as date)<=:fechao_fin ".(!empty($id_cc) ? " and c.id_cc=:id_cc " : " ").(!empty($id_empresa) ? " and c.id_empresa=:id_empresa " : " ");
                    $union_all = TRUE;
        }
        if(!empty($this->sol_compra) && ($id_tiposol=='compra' || $id_tiposol=='')){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "select ".
                    "count(*) contador ".
                    "from prehsol c ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where c.id_usuario=:id_usuario and cast(c.prehsol_fecha as date)>=:fechao_ini and cast(c.prehsol_fecha as date)<=:fechao_fin ".(!empty($id_cc) ? " and c.id_cc=:id_cc " : " ").(!empty($id_empresa) ? " and c.id_empresa=:id_empresa " : " ");
                    $union_all = TRUE;
        }
        if(!empty($this->sol_cheque) && ($id_tiposol=='cheque' || $id_tiposol=='')){
            $arr[':fecha_ini'] = $id_anio.'0101';
            $arr[':fecha_fin'] = $id_anio.'1231';

            if((int)$id_mes>0){
                $arr[':fecha_ini'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'01';
                $arr[':fecha_fin'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'31';
            }

            if($union_all){
                $qry .= " union all ";
            }
            $qry .=  "select ".
                     "count(*) contador ".
                     "from cheque_sol c ".
                     "left join usuario u ".
                     "on c.id_usuario=u.id_usuario ".
                     "left join empresa e ".
                     "on c.id_empresa=e.id_empresa ".
                     "left join cecosto cc ".
                     "on cc.id_cc=c.id_cc ".
                     "where c.id_usuario=:id_usuario and c.fecha_solicitud>=:fecha_ini and c.fecha_solicitud<=:fecha_fin ".(!empty($id_cc) ? " and c.id_cc=:id_cc " : " ").(!empty($id_empresa) ? " and c.id_empresa=:id_empresa " : " ");
                     $union_all = TRUE;

            if(!empty($proyecto)){
                $qry .= " and upper(ifnull(c.proyecto,'')) like concat('%',:proyecto,'%') ";
                $arr[':proyecto'] = trim(strtoupper($proyecto));
            }

            if(!empty($cat)){
                $qry .= " and ifnull(c.id_categoria_gasto,0)=:cat ";
                $arr[':cat'] = $cat;
            }

            if(!empty($proveedor)){
                $qry .= " and upper(ifnull(c.nombre_beneficiario,'')) like concat('%',:proveedor,'%') ";
                $arr[':proveedor'] = trim(strtoupper($proveedor));
            }

        }
        $qry .=") w";
        $result = $this->db->sql_select_one($qry,$arr);
        if(!empty($result)){
            if(!empty($result->contador)){
                return (int)$result->contador;
            }
        }
        return 0;
    }

    function get_solicitud_ufinal_categoria($id_empresa,$id_cc,$id_anio,$id_mes,$proyecto="",$proveedor=""){
    
        $arr = array( 
            ':id_usuario' => $this->id_usuario
        );
        if(!empty($id_empresa)){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if(!empty($id_cc)){
            $arr[':id_cc'] = $id_cc;
        }
        
        $arr[':fecha_ini'] = $id_anio.'0101';
        $arr[':fecha_fin'] = $id_anio.'1231';

        if((int)$id_mes>0){
            $arr[':fecha_ini'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'01';
            $arr[':fecha_fin'] = $id_anio.str_pad($id_mes,2,'0',STR_PAD_LEFT).'31';
        }

        $qry =  "select distinct ".
                "cg.id, ".
                "(case cg.gcia_aprueba ".
                "    when 0 then cg.gcia ".
                "    else 'Aprueba C/Gerencia' ".
                "end) gcia, ".
                "cg.categoria ".
                "from cheque_sol c ".
                "inner join cat_gerencias cg ".
                "on cg.id=ifnull(c.id_categoria_gasto,0) ".
                "where c.id_usuario=:id_usuario and ifnull(c.id_categoria_gasto,0)!=0 and c.fecha_solicitud>=:fecha_ini and c.fecha_solicitud<=:fecha_fin ".(!empty($id_cc) ? " and c.id_cc=:id_cc " : " ").(!empty($id_empresa) ? " and c.id_empresa=:id_empresa " : " ");

        if(!empty($proyecto)){
            $qry .= " and upper(ifnull(c.proyecto,'')) like concat('%',:proyecto,'%') ";
            $arr[':proyecto'] = trim(strtoupper($proyecto));
        }
        if(!empty($proveedor)){
            $qry .= " and upper(ifnull(c.nombre_beneficiario,'')) like concat('%',:proveedor,'%') ";
            $arr[':proveedor'] = trim(strtoupper($proveedor));
        }

        $qry .=" Order by gcia,categoria ";

        return $this->db->sql_select_all($qry,$arr);
    }
    function get_solicitud_ufinal_ult($id_empresa,$id_cc,$id_usuario=null){
        if(!empty($id_usuario)){
            $this->id_usuario = $id_usuario;
        }
        $arr = array( 
            ':id_usuario' => $this->id_usuario
        );
        if($id_empresa!=0){
            $arr[':id_empresa'] = $id_empresa;  
        }
        if($id_cc!=0){
            $arr[':id_cc'] = $id_cc;
        }
        $qry = "select * from (";
        $union_all = FALSE;
        if(!empty($this->sol_ci)){
            $qry .= "(select ".
                    "'ci' tipo,".
                    "c.id_ci id,".
                    "c.id_usuario,".
                    "c.prod_usuario usuario,".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc,".
                    "c.ci_numero correlativo,".
                    "cc.cc_codigo,".
                    "cc.cc_descripcion,".
                    "c.id_empresa,".
                    "e.emp_nombre nombre_empresa,".
                    "t.descripcion tipo_solicitud,".
                    "cast(c.ci_enc_fecha AS SIGNED) fecha_creado,".
                    "cast(c.ci_enc_hora AS SIGNED) hora_creado,".
                    "concat(c.ci_estado,'') estado,".
                    "c.ci_observacion observacion, ".
                    "0 requiere_recepcion ".
                    "from ci_enc c ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "left join ci_tipo_consumo t ".
                    "on t.id_tipo_consumo=c.id_tipo_consumo ".
                    "where c.id_usuario=:id_usuario ".($id_cc!=0 ? " and c.id_cc=:id_cc " : " ").($id_empresa!=0 ? " and c.id_empresa=:id_empresa " : " ").
                    "order by fecha_creado desc,hora_creado desc ".
                    "limit 0,".$this->limit.") ";
                    $union_all = TRUE;
        }
        if(!empty($this->sol_req)){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "'req' tipo,".
                    "c.id_prehreq id,".
                    "c.id_usuario,".
                    "c.prehreq_usuario usuario,".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc,".
                    "c.prehreq_numero correlativo,".
                    "cc.cc_codigo,".
                    "cc.cc_descripcion,".
                    "c.id_empresa,".
                    "e.emp_nombre nombre_empresa,".
                    "'' tipo_solicitud,".
                    "cast(c.prehreq_fecha AS SIGNED) fecha_creado,".
                    "cast(c.prehreq_hora AS SIGNED) hora_creado,".
                    "concat(c.prehreq_estado,'') estado,".
                    "'' observacion, ".
                    "0 requiere_recepcion ".
                    "from prehreq c ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where c.id_usuario=:id_usuario ".($id_cc!=0 ? " and c.id_cc=:id_cc " : " ").($id_empresa!=0 ? " and c.id_empresa=:id_empresa " : " ").
                    "order by fecha_creado desc,hora_creado desc ".
                    "limit 0,".$this->limit.") ";
                    $union_all = TRUE;
        }
        if(!empty($this->sol_compra)){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .= "(select ".
                    "'sol' tipo,".
                    "c.id_prehsol id, ".
                    "c.id_usuario, ".
                    "c.prehsol_usuario usuario, ".
                    "'' monto,".
                    "'' moneda,".
                    "c.id_cc, ".
                    "c.prehsol_numero correlativo, ".
                    "cc.cc_codigo, ".
                    "cc.cc_descripcion, ".
                    "c.id_empresa, ".
                    "e.emp_nombre nombre_empresa, ".
                    "'' tipo_solicitud,".
                    "cast(c.prehsol_fecha AS SIGNED) fecha_creado, ".
                    "cast(c.prehsol_hora AS SIGNED) hora_creado, ".
                    "concat(c.prehsol_estado,'') estado, ".
                    "c.prehsol_obs1 observacion, ".
                    "0 requiere_recepcion ".
                    "from prehsol c ".
                    "left join empresa e ".
                    "on c.id_empresa=e.id_empresa ".
                    "left join cecosto cc ".
                    "on cc.id_cc=c.id_cc ".
                    "where c.id_usuario=:id_usuario ".($id_cc!=0 ? " and c.id_cc=:id_cc " : " ").($id_empresa!=0 ? " and c.id_empresa=:id_empresa " : " ").
                    "order by fecha_creado desc,hora_creado desc ".
                    "limit 0,".$this->limit.") ";
                    $union_all = TRUE;
        }
        if(!empty($this->sol_cheque)){
            if($union_all){
                $qry .= " union all ";
            }
            $qry .=  "(select ".
                     "'cheque' tipo,".
                     "c.id, ".
                     "c.id_usuario, ".
                     "u.usr_usuario COLLATE utf8_spanish_ci usuario, ".
                     "concat('',c.valor_cheque) monto,".
                     "c.moneda moneda,".
                     "c.id_cc, ".
                     "c.id correlativo, ".
                     "cc.cc_codigo, ".
                     "cc.cc_descripcion, ".
                     "c.id_empresa, ".
                     "e.emp_nombre nombre_empresa, ".
                     "'' tipo_solicitud,".
                     "c.fecha_solicitud fecha_creado, ".
                     "c.hora_solicitud hora_creado, ".
                     "concat(c.avance,'-',c.status) estado, ".
                     "concat('<b class=bene>',c.nombre_beneficiario,'</b></br>',c.concepto_pago) COLLATE utf8_spanish_ci observacion, ".
                     "ifnull(cg.requiere_recepcion,0) requiere_recepcion ".
                     "from cheque_sol c ".
                     "left join usuario u ".
                     "on c.id_usuario=u.id_usuario ".
                     "left join empresa e ".
                     "on c.id_empresa=e.id_empresa ".
                     "left join cecosto cc ".
                     "on cc.id_cc=c.id_cc ".
                     "left join cat_gerencias cg ".
                     "on cg.id=c.id_categoria_gasto ".
                     "where c.id_usuario=:id_usuario ".($id_cc!=0 ? " and c.id_cc=:id_cc " : " ").($id_empresa!=0 ? " and c.id_empresa=:id_empresa " : " ").
                     "order by fecha_creado desc,hora_creado desc ".
                     "limit 0,".$this->limit.") ";
                     $union_all = TRUE;
        }
        $qry .=") w order by fecha_creado desc,hora_creado desc limit 0,".$this->limit;
        return $this->db->sql_select_all($qry,$arr);
    }
    function get_empresa_perfil($id_usuario=null){
        if(!empty($id_usuario)){
            $this->id_usuario=$id_usuario;
        }
        $arr = array( ':id_usuario' =>  $this->id_usuario);
        return $this->db->sql_select_all('select '.
                                         'e.id_empresa,'.
                                         'e.emp_nombre nombre '.
                                         'from empresa e '.
                                         'inner join (select id_empresa,id_usuario from acc_emp_cc where id_usuario=:id_usuario group by id_empresa) ac '.
                                         'on ac.id_empresa=e.id_empresa '.
                                         'order by nombre',$arr);
    }
    function get_empresa_perfil_all(){
        return $this->db->sql_select_all('select '.
                                         'id_empresa,'.
                                         'emp_nombre nombre '.
                                         'from empresa '.
                                         'order by nombre',$arr);
    }
    function get_empresa_cc_perfil($id_empresa,$id_usuario=null){
        if(!empty($id_usuario)){
            $this->id_usuario=$id_usuario;
        }
        $arr = array( 
            ':id_usuario' =>  $this->id_usuario,
            ':id_empresa' =>  $id_empresa
        );
        return $this->db->sql_select_all('select '.
                                         'cc.id_cc id, '.
                                         'cc.cc_codigo codigo, '.
                                         'cc.cc_descripcion nombre '.
                                         'from cecosto cc '.
                                         'inner join (select id_cc,id_empresa,id_usuario from acc_emp_cc where id_usuario=:id_usuario and id_empresa=:id_empresa group by id_cc,id_empresa) ac '.
                                         'on cc.id_cc=ac.id_cc and cc.id_empresa=ac.id_empresa '.
                                         'order by nombre',$arr);
    }
    function get_empresa_cc_perfil_all($id_empresa){
        $arr = array(
            ':id_empresa'   =>  $id_empresa
        );
        return $this->db->sql_select_all('select '.
                                         'id_cc id, '.
                                         'cc_codigo codigo, '.
                                         'cc_descripcion nombre '.
                                         'from cecosto '.
                                         'where id_empresa=:id_empresa '.
                                         'order by nombre',$arr);
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
            //Valida si tiene categoria asignada cheque anterior/compras
            if(!empty($categoria_usuario)){
                $perfil['categoria']=$categoria_usuario;
            }else{
                //Si es la categoría nueva de cheques
                $categoria_usuario = $this->db->sql_select_one(
                    'select '. 
                        'id, '.
                        'categoria '.
                    'from cat_gerencias '.
                    'where :id_usuario in (user_aproba_1,user_aproba_2,user_aproba_3,user_aproba_4,user_aproba_5) '.
                    'limit 1',
                    array(':id_usuario'=>$usuario->id_usuario)
                );
                if(!empty($categoria_usuario)){
                    $perfil['categoria']=$categoria_usuario;
                }else{
                    //Si requiere aprobación de categoria por cada gerencia
                    $categoria_usuario = $this->db->sql_select_one(
                        'select '.
                        '    c.id, '.
                        '    c.categoria '. 
                        'from cheque_sol s '.
                        'inner join cat_gerencias c '.
                        'on c.id=s.id_categoria_gasto and '. 
                        'c.user_aproba_1 = 0 and '.
                        'c.user_aproba_2 = 0 and '.
                        'c.user_aproba_3 = 0 and '.
                        'c.user_aproba_4 = 0 and '.
                        'c.user_aproba_5 = 0   '.
                        'where s.id_aprueba_categoria = :id_usuario '.
                        'limit 1',
                        array(':id_usuario'=>$usuario->id_usuario)
                    );
                    if(!empty($categoria_usuario)){
                        $perfil['categoria']=$categoria_usuario;
                    }
                }
            }

            //Permisos de aprobador de valores >= $5000 de cheques
                
            $perfil['is_aprobador_5k'] = FALSE;
            $aprobador_5k = $this->db->sql_select_one(
                'select '.
                '    c.id '.
                'from cat_gerencias c '.
                'inner join cheque_sol s '.
                'on c.id = s.id_categoria_gasto '. 
                'where c.id_aprueba_5k = :id_usuario '.
                'limit 1',
                array(':id_usuario'=>$usuario->id_usuario)
            );
            if(!empty($aprobador_5k)){
                $perfil['is_aprobador_5k']=TRUE;
            }else{
                $perfil['is_aprobador_5k']=(strtoupper($usuario->usr_usuario)=='DIRECCIONEJECUTIVA' ? TRUE : FALSE);
            }
    	}

        $perfil = (object)$perfil;

    	return $perfil;
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
    public function admin_agregar_empresa($id_usuario,$id_empresa){
        $arr = array(
            ':id_usuario' => $id_usuario,
            ':id_empresa' => $id_empresa
        );
        return $this->db->sql_save_id('insert into '.$this->tbl->cheque_usuario_empresa.'(id_usuario,id_empresa) values(:id_usuario,:id_empresa)',$arr);
    }
    public function LoginUser($username,$password){
        $arr=array(
            ':usuario'  =>  $username,
            ':password' =>  $password
        );
        return $this->db->sql_select_one("select * from ".$this->tbl->cheque_usuario." where usuario=:usuario and password=:password",$arr);
    }
    public function findCategoriaList(){
        return $this->db->sql_select_all('select * from '.$this->tbl->tipo_categoria.' order by nombre_categoria');
    }

    private function get_session($name_session){
    	if(isset($_SESSION)){
    		if(isset($_SESSION[$name_session])){
    			return $_SESSION[$name_session];
    		}
    	}
    	return null;
    }
    public function findCategoriasUsuario($tipo_categoria){
        $arr=array(
            ':tipo_categoria'  =>  $tipo_categoria
        );
        return $this->db->sql_select_all(
            "select ".
            "   * ". 
            "from ".$this->tbl->cat_gerencias." ".
            "where mod_solicitud=:tipo_categoria ". 
            "Order by gcia,categoria",$arr);
    }
}
?>

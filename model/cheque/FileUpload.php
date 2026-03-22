<?php
class FileUpload{
    private $db;
    public $id;
    public $id_solicitud;
    public $descripcion;
    public $filename;
    public $fecha;
    public $hora;
    public $id_usuario;

    public function __construct($id_usuario){
        $this->db=new DBSics();
        $this->id_usuario=$id_usuario;
    }
    function truncate_str($texto){
        if(strlen($texto)>100){
            $texto=substr($texto, 0,100);
        }
        return $texto;
    }
    public function create(){
        $arr = array(
            ':id_solicitud'         => $this->id_solicitud,
            ':descripcion'          => $this->truncate_str($this->descripcion),
            ':filename'             => $this->filename,
            ':fecha'                => $this->fecha,
            ':hora'                 => $this->hora
        );
        return $this->db->sql_query("insert into ".TBL_FILE."(id_solicitud,descripcion,filename,fecha,hora) values(:id_solicitud,:descripcion,:filename,:fecha,:hora)",$arr);
    }
    public function delete($id){
        $arr = array(
            ':id' => $id
        );
        return $this->db->sql_query("delete from ".TBL_FILE." where id=:id",$arr);
    }
    public function find($id){
        $arr = array(
            ':id' => $id
        );
        $result=$this->db->sql_select_one("select id,username,password,status,adoption,buying,memorial,request,race,institution from users where status!='D' and id=:id",$arr);
        if($result!=null){
            return $result;
        }
        return null;
    }
}
?>
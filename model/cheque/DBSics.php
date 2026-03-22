<?php
require dirname(__FILE__) . '/property.php';
class DBSics
{
    protected $cnx;
    private $error;
    private $msjerror;

    function open()
    {
        try {
            $this->reset();
            $this->cnx = new PDO(DB_TYPE . ":host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT, DB_USER, DB_PWD);
            $this->cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return true;
        } catch (PDOException $e) {
            $this->exception($e->getMessage());
        }
        return false;
    }
    function reset()
    {
        $this->cnx = null;
        $this->error = false;
        $this->msjerror = "";
    }
    function exception($ex)
    {
        $this->cnx = null;
        $this->error = true;
        $this->msjerror = $ex;
    }
    function close()
    {
        if ($this->cnx != null) {
            $this->cnx = null;
        }
    }
    public function sql_save_id($query = null, $parameter = null)
    {
        $id = null;
        if ($query != null) {
            try {
                if ($this->open()) { //abre conexión a la base de datos
                    $stmt = $this->cnx->prepare($query);

                    if ($parameter != null) {
                        //$stmt->execute($parameter); //sentencia preparada con valores
                        foreach ($parameter as $key => $value) {
                            if (is_int($value)) {
                                $stmt->bindValue($key, $value, PDO::PARAM_INT);
                            } elseif (is_bool($value)) {
                                $stmt->bindValue($key, $value, PDO::PARAM_BOOL);
                            } elseif (is_null($value)) {
                                $stmt->bindValue($key, $value, PDO::PARAM_NULL);
                            } else {
                                $stmt->bindValue($key, $value, PDO::PARAM_STR);
                            }
                        }
                    }
                    $stmt->execute(); //

                    $id = $this->cnx->lastInsertId(); //retorna el id insertado
                    $this->close(); //cierra conexion a la base de datos.
                }
            } catch (PDOException $e) {
                $this->close();
            }
        }
        return $id;
    }
    public function sql_save($query = null, $parameter = null)
    {
        if ($query != null) {
            try {
                if ($this->open()) { //abre conexión a la base de datos
                    $stmt = $this->cnx->prepare($query);

                    if ($parameter != null) {
                        //$stmt->execute($parameter); //sentencia preparada con valores
                        foreach ($parameter as $key => $value) {
                            if (is_int($value)) {
                                $stmt->bindValue($key, $value, PDO::PARAM_INT);
                            } elseif (is_bool($value)) {
                                $stmt->bindValue($key, $value, PDO::PARAM_BOOL);
                            } elseif (is_null($value)) {
                                $stmt->bindValue($key, $value, PDO::PARAM_NULL);
                            } else {
                                $stmt->bindValue($key, $value, PDO::PARAM_STR);
                            }
                        }
                    }
                    $stmt->execute(); //

                    $this->close(); //cierra conexion a la base de datos.
                    return true;
                }
            } catch (PDOException $e) {
                $this->close();
            }
        }
        return false;
    }
    public function sql_query($query = null, $parameter = null)
    {
        return $this->sql_save($query, $parameter);
    }
    public function sql_select_one($query = null, $parameter = null)
    {
        $obj = null;
        if ($query != null) {
            try {
                if ($this->open()) { //abre conexión a la base de datos
                    $stmt = $this->cnx->prepare($query);

                    if ($parameter != null) {
                        //$stmt->execute($parameter); //sentencia preparada con valores
                        foreach ($parameter as $key => $value) {
                            if (is_int($value)) {
                                $stmt->bindValue($key, $value, PDO::PARAM_INT);
                            } elseif (is_bool($value)) {
                                $stmt->bindValue($key, $value, PDO::PARAM_BOOL);
                            } elseif (is_null($value)) {
                                $stmt->bindValue($key, $value, PDO::PARAM_NULL);
                            } else {
                                $stmt->bindValue($key, $value, PDO::PARAM_STR);
                            }
                        }
                    }
                    $stmt->execute(); //

                    $obj = $stmt->fetch(PDO::FETCH_OBJ);
                    $this->close();
                }
            } catch (PDOException $e) {
                $this->close();
            }
        }
        return $obj;
    }
    public function sql_select_all($query = null, $parameter = null)
    {
        $obj = null;
        if ($query != null) {
            try {
                if ($this->open()) { //abre conexión a la base de datos
                    $stmt = $this->cnx->prepare($query);
                    if ($parameter != null) {
                        //$stmt->execute($parameter); //sentencia preparada con valores
                        foreach ($parameter as $key => $value) {
                            if (is_int($value)) {
                                $stmt->bindValue($key, $value, PDO::PARAM_INT);
                            } elseif (is_bool($value)) {
                                $stmt->bindValue($key, $value, PDO::PARAM_BOOL);
                            } elseif (is_null($value)) {
                                $stmt->bindValue($key, $value, PDO::PARAM_NULL);
                            } else {
                                $stmt->bindValue($key, $value, PDO::PARAM_STR);
                            }
                        }
                    }
                    $stmt->execute(); //

                    $obj = $stmt->fetchAll(PDO::FETCH_OBJ);
                    $stmt->closeCursor();
                    $this->close();
                }
            } catch (PDOException $e) {
                $this->depurar($e->getMessage());
                $this->close();
            }
        }
        return $obj;
    }
    function depurar($obj)
    {
        echo "<pre>";
        print_r($obj);
        echo "</pre>";
    }
}
?>
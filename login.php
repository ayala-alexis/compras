<?php
// Iniciar sesión al principio (mover session_start() aquí)
session_start();

// Validar que existen los datos POST
if (!isset($_POST['User']) || !isset($_POST['Passwd'])) {
    echo json_encode('error: Datos incompletos');
    exit;
}

$user = strtoupper($_POST['User']);
$pass = strtoupper($_POST['Passwd']);
//$user = strtoupper("wserpas");
//$pass = strtoupper("wserpas");

require_once "Configuracion.php";
require_once "DB.php";
global $db;
$rtn = "error";

try {
    $db = DB::getInstance();
    
    // USAR CONSULTA PREPARADA - Corrección principal
    // OPCIÓN 1: Usando el nuevo método con parámetros
    $sql = "SELECT * FROM usuario WHERE usr_usuario = ? AND usr_password = ?";
    $consulta = $db->ejecutar($sql, [$user, $pass]);
    
    // OPCIÓN 2: Si necesitas mantener el formato antiguo temporalmente
    // $sql = "SELECT * FROM usuario WHERE usr_usuario = '" . $db->escape($user) . "' AND usr_password = '" . $db->escape($pass) . "'";
    // $consulta = $db->ejecutar($sql);
    
    // CORREGIR: Reemplazar mysqli_num_rows por el método de la clase DB
    if ($consulta && $db->numRows($consulta) > 0) {
        try {
            // CORREGIR: Reemplazar mysqli_fetch_array por el método de la clase DB
            $row = $db->obtenerAsoc($consulta);  // O usar $db->obtener($consulta)
            
            if ($row['usr_estado'] == 'A') {
                $rtn = 'success';
                
                // session_start() ya fue llamado al principio
                $_SESSION['u'] = $row['usr_usuario'];
                $_SESSION['n'] = $row['usr_nombre'];
                $_SESSION['i'] = $row['id_usuario'];
                $_SESSION['req'] = $row['usr_req'];
                $_SESSION['sol'] = $row['usr_sol'];
                $_SESSION['oc'] = $row['usr_oc'];
                $_SESSION['cheque'] = false; // Añadir para consistencia
            } else {
                $rtn = 'Usuario Deshabilitado.';
            }
        } catch (Exception $e) {
            $rtn = 'Error: ' . $e->getMessage();
        }
    } else {
        require_once 'model/cheque/Entity.php';
        $entity = new Entity(); // Cambiar nombre para evitar conflicto con $db
        
        $user_lower = strtolower($_POST['User']);
        $pass_lower = strtolower($_POST['Passwd']);
        
        $login = $entity->LoginUser($user_lower, $pass_lower);
        
        if (!empty($login)) {
            // session_start() ya fue llamado al principio
            $_SESSION['i'] = $login->id_usuario;
            $_SESSION['u'] = $login->usuario;
            $_SESSION['n'] = $login->nombre;
            $_SESSION['req'] = 0;
            $_SESSION['sol'] = 0;
            $_SESSION['oc'] = 0;
            $_SESSION['cheque'] = true;
            $rtn = 'success';
        } else {
            $rtn = 'Usuario o contraseña incorrectos.';
        }
    }
} catch (Exception $e) {
    $rtn = 'Error del sistema: ' . $e->getMessage();
}

//echo $rtn;
echo json_encode($rtn);
?>
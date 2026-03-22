<?php
require dirname(__FILE__).'/../../Parametros.php';

//Conexión DB MYSQL

define('DB_NAME',$bd);
define('DB_HOST',$servidor);
define('DB_USER',$usuario);
define('DB_PWD',$password);
define('DB_PORT','3306');
define('DB_CHARSET','utf8');
define('DB_TYPE','mysql');

//Tablas DB MYSQL

define('TBL_USUARIO',$tbl_usuario);
define('TBL_USUARIOCC',$tbl_acc_emp_cc);
define('TBL_CC',$tbl_cecosto);
define('TBL_EMPRESA',$tbl_empresa);
define('TBL_USUARIOROL', $tbl_rol_user);
define('TBL_ROL', $tbl_rol);
define('TBL_TIPO_CATEGORIA', $tbl_tipo_categoria);
define('TBL_CATEGORIA_USUARIO', $tbl_gestion_categorias);
define('TBL_FILE','cheque_upload');
define('TBL_CHEQUE','cheque_sol');
define('TBL_CHEQUE_USUARIO_EMPRESA', 'cheque_usuario_empresa');
define('TBL_CHEQUE_USUARIO', 'cheque_usuario');
define('TBL_CATEGORIA', 'cheque_categoria');
define('TBL_SEGUIMIENTO', 'cheque_seguimiento');

//Conexión DB2

define('DB2_NAME', $server);
define('DB2_USER', $user);
define('DB2_PWD', $pass);

define('MAX_UPLOAD',1024*1024*6);
define('MAX_CHEQUE',5000);
define('MAX_CHEQUE_NI',5000*34.9361);
define('MAX_CHEQUE_HN',5000*24.2164);
define('DIRECCION_EJECUTIVA','DIRECCIONEJECUTIVA'); //usuario a utilizar para la aprobación de solicitud de cheques mayores o iguales a $5K
define('ID_CATEGORIA_APROBACION_AUTOMATICA', 1000);

//Definición de status de solicitud de cheques

?>
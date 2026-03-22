<?php
class Solicitud{
    public $id;
    public $empresa;
    public $nombre_beneficiario;
    public $valor_cheque;
    public $concepto_pago;
    public $fecha_max_pago;
    public $status;
    public $negociable;
    public $fecha;
    public $hora;
    public $no_copia;
    public $id_usuario;
    public $name_usuario;
    public $name_usuario_autoriza;
    public $name_usuario_autoriza_cc;
    public $name_usuario_autoriza_5k;
    public $name_usuario_autoriza_conta;
    public $avance;
    public $observacion;
    public $categoria;
    public $file;
    public $trazabilidad;
    public $valor1_text;
    public $valor2_text;
    public $is5k;
	public $moneda;
    public $devuelta;
    public $id_categoria_gasto;
    public $nombre_categoria_gasto;
    public $proyecto;
    public $monto_retencion;
    public $monto_descuento;
    public $monto_total;
    public $requiere_recepcion;
}
class File{
    public $id;
    public $id_solicitud;
    public $descripcion;
    public $filename;
    public $fecha;
    public $hora;
}
class Trazabilidad{
    public $id;
    public $id_solicitud;
    public $id_usuario;
    public $status;
    public $fecha;
    public $hora;
    public $observacion;
    public $avance;
    public $usuario;
    public $nivel;
}
class PerfilUser{
    public $id;
    public $username;
    public $nombre;
    public $email;
    public $rol;
    public $empresa;
    public $categoria;
    public $id_categoria;
    public $categoria_gasto;
}
class Categoria{
    public $id;
    public $nombre;
}
class Empresa{
    public $id;
    public $nombre;
    public $razon;
    public $direccion;
    public $cc;
}
class CentroCosto{
    public $id;
    public $codigo;
    public $nombre;
}
class Form{
    public static function InputInt($input){
        if(!empty($input)){
            if(is_numeric($input)){
                return (int)$input;
            }
        }
        return null;
    }
    public static function Get($url){
        if(!empty($url)){
            return trim($url);
        }
        return null;
    }
    public static function InputDouble($input){
        if(!empty($input)){
            if(is_numeric($input)){
                return (float)$input;
            }
        }
        return null;
    }
    public static function InputString($input){
        if(!empty($input)){
            return trim($input);
        }
        return "";
    }
    public static function InputExistFile($file){
        if(!empty($file['size'])){
            return ($file['size']>0);
        }
        return false;
    }
    public static function InputFileSize($file){
        if(!empty($file['size'])){
            return ($file['size']<=MAX_UPLOAD);
        }
        return false;
    }
    public static function InputFileExt($file){
        if(!empty($file)){
            $tmp = explode(".", $file["name"]);
            return end($tmp);
        }
        return false;
    }
    public static function InputFileExtOk($file){
        if(!empty($file)){
            $tmp = explode(".", $file["name"]);
            $tmp=end($tmp);
            return in_array(strtolower($tmp),array('pdf','xls','xlsx','rar','7z','7zip','zip','docx','png','jpg','gif','jpeg'));
        }
        return false;
    }
    public static function InputFileIsValid($file){
        if(Form::InputExistFile($file)){ //Si se ha cargado archivo en formulario
            if(Form::InputFileExtOk($file)){
                if(!Form::InputFileSize($file)){
                    return "Tamaño de archivo es superior a lo permitido de ".((MAX_UPLOAD/1024)/1024)."MB";
                }
            }else{
                return "Formato de archivo incorrecto";
            }
        }else{
            return "*";
        }
        return ""; //Si se ha cargado un archivo correctamente
    }
    public static function InputFileSave($file,$name){
        $ok=Form::InputFileIsValid($file);
        if($ok==''){ //Si se ha cargado archivo en formulario
            if(!Form::FileSave($file,$name.".".Form::InputFileExt($file))){
                return "Error desconocido no es posible guardar archivo";
            }
        }else if($ok!='*'){
            return $ok;
        }
        return ""; //Si no se ha cargado un archivo o todo ha ido bien
    }
    public static function FileSave($file,$name){
        return move_uploaded_file($file['tmp_name'],dirname(__FILE__)."/../../public/upload/".$name);
    }
    public static function InputFileName($file){
        if(!empty($file)){
            return $file['name'];
        }
        return null;
    }
    public static function FechaToInteger($fecha){
        if(strlen($fecha)==10){
            return substr($fecha, 6,4).substr($fecha, 3,2).substr($fecha, 0,2);
        }
        return 0;
    }
    public static function InputCheckBox($input){
        if(!empty($input)){
            return ($input=='1');
        }
        return false;
    }
    public static function IntegerToDate($integer){
        if(strlen($integer)==8){
            return substr($integer,6,2).'/'.substr($integer,4,2).'/'.substr($integer, 0,4);
        }
        return $integer;
    }
    public static function IntegerToTime($integer){
        if(strlen($integer)==6){
            return substr($integer,0,2).':'.substr($integer,2,2).':'.substr($integer, 4,2);
        }else if(strlen($integer)==5){
            return '0'.substr($integer,0,1).':'.substr($integer,1,2).':'.substr($integer, 3,2);
        }
        return $integer;
    }
    public static function numtoletras($xcifra,$money_all="DOLARES",$money_one="DOLAR"){
        $xarray = array(0 => "Cero",
            1 => "UN", "DOS", "TRES", "CUATRO", "CINCO", "SEIS", "SIETE", "OCHO", "NUEVE",
            "DIEZ", "ONCE", "DOCE", "TRECE", "CATORCE", "QUINCE", "DIECISEIS", "DIECISIETE", "DIECIOCHO", "DIECINUEVE",
            "VEINTI", 30 => "TREINTA", 40 => "CUARENTA", 50 => "CINCUENTA", 60 => "SESENTA", 70 => "SETENTA", 80 => "OCHENTA", 90 => "NOVENTA",
            100 => "CIENTO", 200 => "DOSCIENTOS", 300 => "TRESCIENTOS", 400 => "CUATROCIENTOS", 500 => "QUINIENTOS", 600 => "SEISCIENTOS", 700 => "SETECIENTOS", 800 => "OCHOCIENTOS", 900 => "NOVECIENTOS"
        );

        $xcifra = trim($xcifra);
        $xlength = strlen($xcifra);
        $xpos_punto = strpos($xcifra, ".");
        $xaux_int = $xcifra;
        $xdecimales = "00";
        if (!($xpos_punto === false)) {
            if ($xpos_punto == 0) {
                $xcifra = "0" . $xcifra;
                $xpos_punto = strpos($xcifra, ".");
            }
            $xaux_int = substr($xcifra, 0, $xpos_punto); // obtengo el entero de la cifra a covertir
            $xdecimales = substr($xcifra . "00", $xpos_punto + 1, 2); // obtengo los valores decimales
        }

        $XAUX = str_pad($xaux_int, 18, " ", STR_PAD_LEFT); // ajusto la longitud de la cifra, para que sea divisible por centenas de miles (grupos de 6)
        $xcadena = "";
        for ($xz = 0; $xz < 3; $xz++) {
            $xaux = substr($XAUX, $xz * 6, 6);
            $xi = 0;
            $xlimite = 6; // inicializo el contador de centenas xi y establezco el l�mite a 6 d�gitos en la parte entera
            $xexit = true; // bandera para controlar el ciclo del While
            while ($xexit) {
                if ($xi == $xlimite) { // si ya lleg� al l�mite m�ximo de enteros
                    break; // termina el ciclo
                }

                $x3digitos = ($xlimite - $xi) * -1; // comienzo con los tres primeros digitos de la cifra, comenzando por la izquierda
                $xaux = substr($xaux, $x3digitos, abs($x3digitos)); // obtengo la centena (los tres d�gitos)
                for ($xy = 1; $xy < 4; $xy++) { // ciclo para revisar centenas, decenas y unidades, en ese orden
                    switch ($xy) {
                        case 1: // checa las centenas
                            if (substr($xaux, 0, 3) < 100) { // si el grupo de tres d�gitos es menor a una centena ( < 99) no hace nada y pasa a revisar las decenas

                            } else {
                                $key = (int) substr($xaux, 0, 3);
                                if (TRUE === array_key_exists($key, $xarray)){  // busco si la centena es n�mero redondo (100, 200, 300, 400, etc..)
                                    $xseek = $xarray[$key];
                                    $xsub = Form::subfijo($xaux); // devuelve el subfijo correspondiente (Mill�n, Millones, Mil o nada)
                                    if (substr($xaux, 0, 3) == 100)
                                        $xcadena = " " . $xcadena . " CIEN " . $xsub;
                                    else
                                        $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                                    $xy = 3; // la centena fue redonda, entonces termino el ciclo del for y ya no reviso decenas ni unidades
                                }
                                else { // entra aqu� si la centena no fue numero redondo (101, 253, 120, 980, etc.)
                                    $key = (int) substr($xaux, 0, 1) * 100;
                                    $xseek = $xarray[$key]; // toma el primer caracter de la centena y lo multiplica por cien y lo busca en el arreglo (para que busque 100,200,300, etc)
                                    $xcadena = " " . $xcadena . " " . $xseek;
                                } // ENDIF ($xseek)
                            } // ENDIF (substr($xaux, 0, 3) < 100)
                            break;
                        case 2: // checa las decenas (con la misma l�gica que las centenas)
                            if (substr($xaux, 1, 2) < 10) {

                            } else {
                                $key = (int) substr($xaux, 1, 2);
                                if (TRUE === array_key_exists($key, $xarray)) {
                                    $xseek = $xarray[$key];
                                    $xsub = Form::subfijo($xaux);
                                    if (substr($xaux, 1, 2) == 20)
                                        $xcadena = " " . $xcadena . " VEINTE " . $xsub;
                                    else
                                        $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                                    $xy = 3;
                                }
                                else {
                                    $key = (int) substr($xaux, 1, 1) * 10;
                                    $xseek = $xarray[$key];
                                    if (20 == substr($xaux, 1, 1) * 10)
                                        $xcadena = " " . $xcadena . " " . $xseek;
                                    else
                                        $xcadena = " " . $xcadena . " " . $xseek . " Y ";
                                } // ENDIF ($xseek)
                            } // ENDIF (substr($xaux, 1, 2) < 10)
                            break;
                        case 3: // checa las unidades
                            if (substr($xaux, 2, 1) < 1) { // si la unidad es cero, ya no hace nada

                            } else {
                                $key = (int) substr($xaux, 2, 1);
                                $xseek = $xarray[$key]; // obtengo directamente el valor de la unidad (del uno al nueve)
                                $xsub = Form::subfijo($xaux);
                                $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                            } // ENDIF (substr($xaux, 2, 1) < 1)
                            break;
                    } // END SWITCH
                } // END FOR
                $xi = $xi + 3;
            } // ENDDO

            if (substr(trim($xcadena), -5, 5) == "ILLON") // si la cadena obtenida termina en MILLON o BILLON, entonces le agrega al final la conjuncion DE
                $xcadena.= " DE";

            if (substr(trim($xcadena), -7, 7) == "ILLONES") // si la cadena obtenida en MILLONES o BILLONES, entoncea le agrega al final la conjuncion DE
                $xcadena.= " DE";

            // ----------- esta l�nea la puedes cambiar de acuerdo a tus necesidades o a tu pa�s -------
            if (trim($xaux) != "") {
                switch ($xz) {
                    case 0:
                        if (trim(substr($XAUX, $xz * 6, 6)) == "1")
                            $xcadena.= "UN BILLON ";
                        else
                            $xcadena.= " BILLONES ";
                        break;
                    case 1:
                        if (trim(substr($XAUX, $xz * 6, 6)) == "1")
                            $xcadena.= "UN MILLON ";
                        else
                            $xcadena.= " MILLONES ";
                        break;
                    case 2:
                        if ($xcifra < 1) {
                            $xcadena = "CERO $xdecimales/100 $money_all ";
                        }
                        if ($xcifra >= 1 && $xcifra < 2) {
                            $xcadena = "UN $xdecimales/100 $money_one ";
                        }
                        if ($xcifra >= 2) {
                            $xcadena.= " $xdecimales/100 $money_all "; //
                        }
                        break;
                } // endswitch ($xz)
            } // ENDIF (trim($xaux) != "")
            // ------------------      en este caso, para M�xico se usa esta leyenda     ----------------
            $xcadena = str_replace("VEINTI ", "VEINTI", $xcadena); // quito el espacio para el VEINTI, para que quede: VEINTICUATRO, VEINTIUN, VEINTIDOS, etc
            $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
            $xcadena = str_replace("UN UN", "UN", $xcadena); // quito la duplicidad
            $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
            $xcadena = str_replace("BILLON DE MILLONES", "BILLON DE", $xcadena); // corrigo la leyenda
            $xcadena = str_replace("BILLONES DE MILLONES", "BILLONES DE", $xcadena); // corrigo la leyenda
            $xcadena = str_replace("DE UN", "UN", $xcadena); // corrigo la leyenda
        } // ENDFOR ($xz)
        return trim($xcadena);
    }
    public static function subfijo($xx){ // esta funci�n regresa un subfijo para la cifra
        $xx = trim($xx);
        $xstrlen = strlen($xx);
        if ($xstrlen == 1 || $xstrlen == 2 || $xstrlen == 3)
            $xsub = "";
        //
        if ($xstrlen == 4 || $xstrlen == 5 || $xstrlen == 6)
            $xsub = "MIL";
        //
        return $xsub;
    }
    public static function numtonumber($valor,$money="$"){
        $valor=number_format($valor, 2, '.', ',');
        $str="";
        if(strlen($valor)<12){
           $str .= "<span class='text-aste'>".
                    str_pad('',(12-strlen($valor)),'*',STR_PAD_RIGHT).
                  "</span>";
        }
        return $money.$str.$valor."<span class='text-aste'>*</span>";
    }
    public static function numtonumber2($valor,$money="$"){
        $valor=number_format($valor, 2, '.', ',');
        $str="";
        if(strlen($valor)<12){
           $str .= str_pad('',(12-strlen($valor)),'*',STR_PAD_RIGHT);
        }
        return $money.$str.$valor."*";
    }
}
?>


<script type="text/javascript">
$("#pagination-digg li a").click(function(){
	var refer = this.href;
	var largo = refer.length;
	var ultima_pos = refer.substr((largo-1),1);
	if(ultima_pos != '#'){
		var element = refer.split('/');
		var campos = element[4].split(',');
		var pagi = campos[0];
		var tabl = campos[1];
		try{
			pagina(pagi, tabl);
		}catch(e){
			alert(e.message);
			alert(e.name);
		}
	}
	return false;
});
$("#btnAdd").click(function(){
	$("#formDiv").css('display','block');
	$("#formDiv").dialog({
		title: 'Adicionar Registro',
		width: 724,
		show: 'fold',
		hide: 'blind',
		modal: true
	});
	return false;
});
$(".btnDelete").click(function(){
    idRec = this.id;
	$("#dialog-confirm").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>El Registro '+idRec+' sera eliminado. Esta seguro?</p>').dialog({
			resizable: false,
			height:200,
			modal: true,
			show: 'fold',
			hide: 'blind',
			buttons: {
				"Borrar Registro": function() {
				    idform = 'frmDelete'+idRec;
				    var campos = xajax.getFormValues(idform);
				    campos["num_pagi"] = $("#num_pag").val();
					myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
					$.ajax({
						type : "POST",
						url : myUrl,
						data: {
							form: campos
						},
						success : function(data){
							if(!$.isNumeric(data)){
						    	$.pnotify({
									title: "error",
									text: data,
									icon: "glyphicon glyphicon-ban-circle",
									hide: true,
									type: "error"
								});
							} else {
								if(data == 1 ){
									$.pnotify({
										title: "error",
										text: data,
										icon: "glyphicon glyphicon-ban-circle",
										hide: true,
										type: "error"
									});
								} else {
								    $.pnotify({
										type : "success",
								        text: "Registro Eliminado.",
								        hide: true,
								        title: "eliminado"
									});
									pagina(campos["num_pagi"],"presupuesto");
									$("#dialog-confirm").dialog( "close" );
								}
							}
						},
						error : function(XMLHttpRequest, textStatus, errorThrown) {
						    $.pnotify({
								type : "error",
						        text: "Ha ocurrido un error durante la ejecucion.",
						        hide: true,
						        title: "error"
							});
						    $("#dialog-confirm").dialog( "close" );
						}
					});
				},
				Cancel: function() {
					$("#dialog-confirm").dialog( "close" );
				}
			}
		});
	return false;
});

function loadAuth(modal, id_empresa, id_cc, id_tagasto){
	myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=emp&a=listarAuth';
	  $.ajax({
	    type: 'POST',
	    url: myUrl,
	    dataType: 'json',
	           data: {
	               id_empresa: id_empresa,
	               id_cc: id_cc,
	               id_tagasto: id_tagasto 
	    },
	    success: function(aData){	
			var trHTML = '';
			modal.find('.modal-body table#tblAuth > tbody').html("");
			if(aData.length > 0) {
			$.each(aData, function(i, item){
				if($.type(item.aut_anyo) !== "undefined" ) {
		    	trHTML += '<tr>';
		    	trHTML += '<td>' + item.aut_anyo + '</td>';
		    	trHTML += '<td>' + item.aut_mes + '</td>';
		    	trHTML += '<td>' + item.aut_signo + '</td>';
		    	trHTML += '<td>' + item.aut_valor + '</td>';
		    	trHTML += '<td>' + item.aut_fecha + '</td>';
		    	trHTML += '<td>' + item.aut_hora + '</td>';
		    	trHTML += '<td>' + item.aut_usuario + '</td>';
		    	trHTML += '</tr>';
				}
			});
			}
			modal.find('.modal-body table#tblAuth > tbody').append(trHTML);
	    },
	    error: function(XMLHttpRequest, textStatus, errorThrown){
	      jAlert(textStatus);
	    }
	  });
}

$('#exampleModal').on('show.bs.modal', function (event) {
	  var button = $(event.relatedTarget);
	  var cc_codigo = button.data('cc_codigo');
	  var titulo = button.data('titulo');
	  var id_tagasto = button.data('id_tagasto');
	  var id_cc = button.data('id_cc');
	  var id_empresa = button.data('id_empresa');
	  var cod_tagasto = button.data('cod_tagasto');
	  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
	  var modal = $(this);
	  loadAuth(modal, id_empresa, id_cc, id_tagasto);
	  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
	  modal.find('.modal-title').text('Autorizacion para ' + titulo);
	  modal.find('.modal-body input#cc_codigo').val(cc_codigo);
	  modal.find('.modal-body input#cod_tagasto').val(cod_tagasto);
	  modal.find('.modal-body input#id_tagasto').val(id_tagasto);
	  modal.find('.modal-body input#id_cc').val(id_cc);
	  modal.find('.modal-body input#id_empresa').val(id_empresa);
});

$("#frmAuth").validate({
    rules: {
		cc_codigo: {
			required: true
		},
		cod_gasto: {
			required: true
		},
		aut_valor: {
			required: true,
			number: true,
			min: 0.05
		}
   },
   messages: {
     	cc_codigo: {
	     	required : "Seleccion Centro de Costo"
     	},
		cod_gasto: {
			required: "Seleccion Gasto"
		},
		aut_valor: {
			required: "Digite monto"
		}
   },
	submitHandler: function(form) {
	    var campos=xajax.getFormValues("frmAuth");
	    myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
		$.ajax({
			type : "POST",
			url : myUrl,
			data: {
				form: campos
			},
			beforeSend: function(){
				$(":submit").attr("disabled","disabled");
			},
			success : function(data){
				$(":submit").removeAttr("disabled");
				$(":submit").attr("disabled","");
				$(":submit").prop( "disabled", false );
				if(!$.isNumeric(data)){
			    	$.pnotify({
						title: 'error',
						text: data,
						icon: 'glyphicon glyphicon-ban-circle',
						hide: true,
						type: "error"
					});
				} else {
					if(data == 1 ){
						$.pnotify({
							title: 'error',
							text: data,
							icon: 'glyphicon glyphicon-ban-circle',
							hide: true,
							type: "error"
						});
					} else {
						$.pnotify({
							title: 'adicionado',
							text: 'registro adicionado con exito!',
							icon: 'glyphicon glyphicon-ok',
							hide: true,
							type: 'success'
						});
						var modal = $("#exampleModal");
						modal.find('.modal-body input#aut_valor').val("0");
						loadAuth(modal, campos["id_empresa"], campos["id_cc"], campos["id_tagasto"]);
					}
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
				$(":submit").removeAttr("disabled");
				$(":submit").attr("disabled","");
			    showNotification({
					type : "error",
			        message: "Ha ocurrido un error durante la Adicion.",
			        autoClose: true,
			        duration: 2
				});
			}
		});
   }
});
</script>
<script>
$(function(){
	$('a').tooltip();
});
</script>
<?
session_start ();
require '../Configuracion.php';
require '../DB.php';
// Tabla de Paginacion
$tabla = $_POST ['tab'];
// Iniciamos instancia a la clase DB
$db = DB::getInstance ();
// Parametros de Configuracion
$conf = Configuracion::getInstance ();
// Permisos de usuario en el modulo
$sqlAuthMod = "Select * From " . $conf->getTbl_acc_modulo () . " Where id_usuario = '" . $_SESSION ['i'] . "' And id_modulo = '" . $_SESSION ['idmod'] . "'";
try {
	$sqlAuthExec = $db->ejecutar ( $sqlAuthMod );
	$rowsAuth = mysqli_fetch_array ( $sqlAuthExec );
} catch ( Exception $e ) {
	$e->getMessage ();
	die ();
}
// NOMBRE DE LA EMPRESA
$sqlEmp = "Select * From " . $conf->getTbl_empresa () . " Where id_empresa = '" . $_SESSION ['ie'] . "'";
try {
	$sqlEmpExec = $db->ejecutar ( $sqlEmp );
	$rowsEmp = mysqli_fetch_array ( $sqlEmpExec );
} catch ( Exception $e ) {
	echo $e->getMessage ();
	die ();
}
$sql = "Select * from " . $tabla . " Where id_empresa='" . $_SESSION ['ie'] . "'";
// Consulta que devuelve todos los registros
try {
	$lista0 = $db->ejecutar ( $sql );
	// Se cuentan los registros devueltos por la consulta SQL $lista0
	$totalSql = mysqli_num_rows ( $lista0 );
} catch ( Exception $e ) {
	echo $e->getMessage ();
	die ();
}

// Registros a mostrar en cada p�gina
$rowCount = 15;
$pagesCount = ( int ) ceil ( $totalSql / $rowCount );
$pageIndex = isset ( $_POST ['pag'] ) ? ( int ) $_POST ['pag'] : 0;
if ($pageIndex > $pagesCount) {
	$pageIndex = $pagesCount - 1;
}
$offset = $pageIndex * $rowCount;
$_SESSION ['p'] = 0;
$_SESSION ['p'] = $pageIndex;

/* Obtenemos los registros solicitados */
$sqlPag = "SELECT * FROM " . $tabla . " Where id_empresa='" . $_SESSION ['ie'] . "' ORDER BY pres_anyo, cc_codigo, gas_tit_codigo, gas_det_codigo LIMIT " . $offset . "," . $rowCount . "";
// Consulta SQL con la que se sacar� el listado de registros
try {
	$lista1 = $db->ejecutar ( $sqlPag );
} catch ( Exception $e ) {
	echo $e->getMessage ();
	die ();
}
// Bucle para generar el listado de registros
$forms = Array ();
echo '<h4 class="text-blue"><a href="?c=emp&a=inicio&id=3"><img alt="" src="images/back-black.png"></a>  Presupuesto : <b>' . $rowsEmp ['emp_nombre'] . '</b></h4>';
echo '<table class="table table-condensed">';
echo '<thead>';
/*
echo '<tr>';
echo '<th colspan="3">
	<select>
		<option>2012</option>
		<option>2013</option>
	</select>
	</th>';
echo '<th colspan="5">
	<select>
		<option>Centro de Costo 001</option>
		<option>Centro de Costo 002</option>
	</select>
	</th>';
echo '<th colspan="2">
	<select>
		<option>Tabla de Gasto 0101</option>
		<option>Tabla de Gasto 0102</option>
	</select>
	</th>';
	*/
echo '</tr>';
echo '</thead>';
echo '<thead>';
echo '<tr>';
echo '<th>A&ntilde;o</th>';
echo '<th>Centro de costo</th>';
echo '<th>Tabla de Gasto</th>';
echo '<th colspan="6">Presupuesto</th>';
echo '<th>&nbsp;</th>';
echo '<th>&nbsp;</th>';
/*
 * echo '<th>FECHA</th>'; echo '<th>HORA</th>'; echo '<th>USUARIO</th>';
 */
echo '</tr>';
echo '</thead>';
echo '<tbody>';
while ( $fila = mysqli_fetch_array ( $lista1 ) ) {
	echo '<tr>';
	echo '<td rowspan="2">' . $fila [5] . '</td><td rowspan="2">' . $fila [2] . " " . $db->getCC ( $fila [1], $fila [2] ) . '</td><td rowspan="2">' . $fila [3] . $fila [4] . " " . $db->getTabGas ( $fila [1], $fila [3], $fila [4] ) . '</td>';
	echo '<td><b>01:</b>' . $fila [6] . '</td><td><b>02:</b>' . $fila [7] . '</td><td><b>03:</b>' . $fila [8] . '</td><td><b>04:</b>' . $fila [9] . '</td><td><b>05:</b>' . $fila [10] . '</td><td><b>06:</b>' . $fila [11] . '</td>';
	// echo '<td rowspan="2">'.$fila[30].'</td><td
	// rowspan="2">'.$fila[31].'</td><td rowspan="2">'.$fila[32].'</td>';
	//
	$sql_cc = "Select id_cc From " . $conf->getTbl_cecosto() . " Where id_empresa = '" . $fila[1] . "' And cc_codigo = '".$fila[2]."'";
	$run_cc = $db->ejecutar ( $sql_cc );
	$fila_cc = $db->obtener ( $run_cc, 0 );
	//
	
	$sql_gas = "Select id_tagasto From " . $conf->getTbl_tagasto() . " Where gas_tit_codigo = '".$fila[3]."' And gas_det_codigo='".$fila[4]."'";
	$run_gas = $db->ejecutar ( $sql_gas );
	$fila_gas = $db->obtener ( $run_gas, 0 );
	if ($rowsAuth ['acc_del'] == 1) {
		echo '<td rowspan="1" align="center">
				<a class="btnDelete" href="#" id="' . $fila [0] . '" rel="tooltip" title="borrar presupuesto">
					<span class="glyphicon glyphicon-remove"></span>
				</a>
   			</td>';
	}
	if ($rowsAuth ['acc_aut'] == 1) {
		echo '<td rowspan="1" align="center">
				<a class="btnAuth" data-toggle="modal" data-target="#exampleModal"
					data-cod_tagasto="'. $fila[3].$fila[4].'"
					 data-titulo="'. $fila [2] . " " . $db->getCC ( $fila [1], $fila [2] ).'" 
					 data-id_tagasto="'. $fila_gas[0].'"
					 data-cc_codigo="'.$fila[2] .'" 
					 data-id_cc="'.$fila_cc[0].'"
					 data-id_empresa="'.$fila[1].'"
					 href="#" id="' . $fila [0] . '" rel="tooltip" title="autorizaciones">
					<span class="glyphicon glyphicon-thumbs-up"></span>
				</a>
   			</td>';
	}
	echo '</tr>';
	echo '<tr>';
	echo '<td><b>07:</b>' . $fila [12] . '</td><td><b>08:</b>' . $fila [13] . '</td><td><b>09:</b>' . $fila [14] . '</td><td><b>10:</b>' . $fila [15] . '</td><td><b>11:</b>' . $fila [16] . '</td><td><b>12:</b>' . $fila [17] . '</td>';
	echo '</tr>';
	$frmIdDel = 'frmDelete' . $fila [0];
	$frmDivId = 'formDiv' . $fila [0];
	/*
	 * CREA FORMULARIO SOLO DE ELIMINACION
	 */
	$htmlForm = '
		<div id="' . $frmDivId . '" style="display: none;">
		<div id="stylized" class="myform">
		<form id="' . $frmIdDel . '" name="' . $frmIdDel . '" method="POST">
			<input type="hidden" name="id_presupuesto" id="id_presupuesto " value="' . $fila [0] . '" />
			<input type="hidden" value="delete" id="accion" name="accion">
			<input type="hidden" value="presupuesto" id="tabla" name="tabla">
			<input type="hidden" value="' . $_SESSION ['u'] . '" id="usr_crea" name="usr_crea">
			<input type="hidden" value="' . $pageIndex . '" id="num_pagi" name="num_pagi">
		</form>
		</div>
		</div>';
	$forms [$fila [0]] = $htmlForm;
}
echo '</tbody>';
echo '</table>';
// Se inicia el listado de p�ginas
echo '<div class="pagination">';
echo '<ul class="pagination" id="pagination-digg">';

// Anterior Deshabilitado
if ($pageIndex == 0) {
	echo '<li class="disabled"><a href="#">prev</a></li>';
}
// Si la p�gina actual no es la primera, se muestra el enlace a la p�gina
// anterior
if ($pageIndex > 0) {
	echo '<li><a href="' . ($pageIndex - 1) . ',' . $tabla . '">prev</a></li>';
}
// Se saca el listado de p�ginas mediante un bucle
$pagesToShow = 3; // P�ginas que aparecen antes y despu�s de la actual
$start = $pageIndex - $pagesToShow;
if ($start < 0) {
	$start = 0;
}
$end = $pageIndex + $pagesToShow;
if ($end >= $pagesCount) {
	$end = $pagesCount - 1;
}
// Primera y Segunda
if ($start > 0) {
	for($i = 0; $i < 2 && $i < $start; ++ $i) {
		echo '<li><a href="' . $i . ',' . $tabla . '">' . ($i + 1) . '</a></li>';
	}
}
if ($start > 2) {
	echo '<li><a href="#"><strong> . . . </strong></a></li>';
}
// Actual
for($i = $start; $i <= $end; ++ $i) {
	if ($pageIndex == $i) {
		echo '<li class="active"><a href="' . $i . ',' . $tabla . '">' . ($i + 1) . '</a></li>';
	} else {
		echo '<li><a href="' . $i . ',' . $tabla . '">' . ($i + 1) . '</a></li>';
	}
}
if ($end < ($pagesCount - 3)) {
	echo '<li><a href="#"><strong> . . . </strong></a></li>';
}
// Penultima y Ultima
if ($end < ($pagesCount - 1)) {
	for($i = max ( ($pagesCount - 2), ($end + 1) ); $i < $pagesCount; ++ $i) {
		echo '<li><a href="' . $i . ',' . $tabla . '">' . ($i + 1) . '</a></li>';
	}
}
// Siguiente
if ($pageIndex < ($pagesCount - 1)) {
	echo '<li><a href="' . ($pageIndex + 1) . ',' . $tabla . '">next</a></li>';
}
// Ultima pagina
if ($pageIndex >= ($pagesCount - 1)) {
	echo '<li class="disabled"><a href="#">next</a></li>';
}
// Se finaliza el listado de p�ginas
if ($rowsAuth ['acc_add'] == 1) {
	echo '<li><a id="btnAdd" href="#">adicionar</a></li>';
}
echo '</ul>';
// Pone link segun acceso en modulo
echo '</div>';
echo '<input type="hidden" id="num_pag" name="num_pag" value="' . $_SESSION ['p'] . '">';
foreach ( $forms as $fr ) {
	echo $fr;
}
echo '<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="exampleModalLabel">New message</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" id="frmAuth" name="frmAuth" method="POST">
		<div class="col-xs-12 col-sm-3 col-md-3">
		  <div class="form-group form-group-sm">
            <label for="id_cc" class="col-sm-3 control-label">Ce.Cos</label>
			<div class="col-sm-9">
              <input type="text" class="form-control input-sm" id="cc_codigo" name="cc_codigo" readonly>
			</div>
          </div>
		</div>
		<div class="col-xs-12 col-sm-3 col-md-3">
		  <div class="form-group form-group-sm">
            <label for="id_tagasto" class="col-sm-3 control-label">Gasto</label>
			<div class="col-sm-9">
              <input type="text" class="form-control input-sm" id="cod_tagasto" name="cod_tagasto" readonly>
			</div>
          </div>
		</div>
		<div class="col-xs-12 col-sm-3 col-md-3">
          <div class="form-group form-group-sm">
            <label for="aut_anyo" class="col-sm-2 control-label">A&ntilde;o</label>
			<div class="col-sm-10">
            <select class="form-control input-sm" id="aut_anyo" name="aut_anyo">
			';
			for($i=date('Y')-1; $i<=date('Y')+5; $i++){
				echo '<option value="'.$i.'">'.$i.'</option>';
			}
		echo '
			</select>
		    </div>
          </div>
		</div>
		<div class="col-xs-12 col-sm-3 col-md-3">
		  <div class="form-group form-group-sm">
            <label for="aut_mes" class="col-sm-2 control-label">Mes</label>
		    <div class="col-sm-10">
            <select class="form-control input-sm" id="aut_mes" name="aut_mes">
			  <option value="1">Enero</option>
			  <option value="2">Febrero</option>
			  <option value="3">Marzo</option>
			  <option value="4">Abril</option>
			  <option value="5">Mayo</option>
			  <option value="6">Junio</option>
			  <option value="7">Julio</option>
			  <option value="8">Agosto</option>
			  <option value="9">Septiembre</option>
			  <option value="10">Octubre</option>
			  <option value="11">Noviembre</option>
			  <option value="12">Diciembre</option>
			</select>
			</div>
          </div>
		</div>
		<div class="col-xs-12 col-sm-3 col-md-3">
		  <div class="form-group form-group-sm">
            <label for="aut_signo" class="col-sm-3 control-label">Signo</label>
		    <div class="col-sm-9">
            <select class="form-control input-sm" id="aut_signo" name="aut_signo">
			  <option value="+">+</option>
			  <option value="-">-</option>
			</select>
		    </div>
          </div>
		</div>
		<div class="col-xs-12 col-sm-3 col-md-3">
		  <div class="form-group form-group-sm">
            <label for="aut_valor" class="col-sm-3 control-label">Monto</label>
			<div class="col-sm-9">
              <input type="text" class="form-control input-sm" id="aut_valor" name="aut_valor">
			</div>
          </div>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-6">
			<div class="form-group">
    		  <div class="col-sm-offset-2 col-sm-10">
				<input type="hidden" id="id_cc" name="id_cc" value="">
				<input type="hidden" id="id_tagasto" name="id_tagasto" value="">
				<input type="hidden" id="id_empresa" name="id_empresa" value="">
				<input type="hidden" value="add" id="accion" name="accion">
				<input type="hidden" value="autorizacion" id="tabla" name="tabla">
				<input type="hidden" value="'.$_SESSION['u'].'" id="usr_crea" name="usr_crea">
		  	    <button type="submit" id="btnAuth" name="btnAuth" class="btn btn-default">Enviar Autorizacion</a>
			  </div>
			</div>
		</div>
        </form>
		<br /><br />
		<div class="table-responsive">
		  <table class="table" id="tblAuth" name="tblAuth">
			<thead>
		    <tr>
			  <th>A&ntilde;o</th>
			  <th>Mes</th>
			  <th>+/-</th>
			  <th>Valor</th>
			  <th>Fecha</th>
			  <th>Hora</th>
			  <th>Usuario</th>
			</tr>
			</thead>
			<tbody>
			</tbody>
		  </table>
		</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>';
?>


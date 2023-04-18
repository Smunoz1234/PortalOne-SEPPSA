<?php
require_once "includes/conexion.php";
PermitirAcceso(601);
$sw = 0;

// AGREGAR FILTRO DE CLIENTE
$Cliente = "";
if (isset($_GET['Cliente']) && $_GET['Cliente'] != "") {
    $Cliente = $_GET['Cliente'];
    $sw = 1;
}

// AGREGAR FILTROS DE FECHA DE PAGO
$FechaInicial = "";
$FechaFinal = "";
if (isset($_GET['FechaInicial']) && $_GET['FechaInicial'] != "") {
    $FechaInicial = $_GET['FechaInicial'];
    $sw = 1;
} else {
    // Restar "n" días a la fecha actual.
    $nuevaFecha = strtotime('-' . ObtenerVariable("DiasRangoFechasGestionar") . ' day');
    $nuevaFecha = date('Y-m-d', $nuevaFecha);

    // SMM, 11/04/2023
    $FechaInicial = $nuevaFecha;
}
if (isset($_GET['FechaFinal']) && $_GET['FechaFinal'] != "") {
    $FechaFinal = $_GET['FechaFinal'];
    $sw = 1;
} else {
    // SMM, 11/04/2023
    $FechaFinal = date('Y-m-d');
}

if ($sw == 1) {
    $Cliente = $_GET["Cliente"] ?? "";
    $WhereCliente = ($Cliente != "") ? "AND id_cliente = '$Cliente'" : "";

    $WhereFecha = "(fecha_registro BETWEEN '$FechaInicial' AND '$FechaFinal')";
    $Cons = "SELECT * FROM tbl_PagosClientes_Correos WHERE $WhereFecha $WhereCliente";

    // echo $Cons;
    $SQL = sqlsrv_query($conexion, $Cons);
}
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Consultar envios de correo a clientes</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {
		$("#NombreCliente").change(function(){
			var NomCliente=document.getElementById("NombreCliente");
			var Cliente=document.getElementById("Cliente");
			if(NomCliente.value==""){
				Cliente.value="";
				$("#Cliente").trigger("change");
			}
		});
		$("#Cliente").change(function(){
			var Cliente=document.getElementById("Cliente");
			$.ajax({
				type: "POST",
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value+"&sucline=1",
				success: function(response){
					$('#Sucursal').html(response).fadeIn();
					$('#Sucursal').trigger('change');
				}
			});
		});
		$("#Sucursal").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Sucursal=document.getElementById('Sucursal').value;
			var Cliente=document.getElementById("Cliente").value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=36&id="+Sucursal+"&clt="+Cliente,
				success: function(response){
					$('#Bodega').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					$('#Bodega').trigger('change');
				}
			});
		});
	});
</script>
<script>
	var json=[];
	var cant=0;
function SeleccionarOT(DocNum){
	var btnCambiarLote=document.getElementById('btnCambiarLote');
	var Check = document.getElementById('chkSelOT'+DocNum).checked;
	var sw=-1;

	json.forEach(function(element,index){
		if(json[index]==DocNum){
			sw=index;
		}
		//console.log(element,index);
	});

	if(sw>=0){
		json.splice(sw, 1);
		cant--;
	}else if(Check){
		json.push(DocNum);
		cant++;
	}

	if(cant>0){
		$("#btnCambiarLote").removeAttr("disabled");
	}else{
		$("#chkAll").prop("checked", false);
		$("#btnCambiarLote").attr("disabled","disabled");
	}

	//console.log(json);
}

function SeleccionarTodos(){
	var Check = document.getElementById('chkAll').checked;
	if(Check==false){
		json=[];
		cant=0;
		$("#btnCambiarLote").attr("disabled","disabled");
	}
	$(".chkSelOT").prop("checked", Check);
	if(Check){
		$(".chkSelOT").trigger('change');
	}
}
</script>
<style>
	.swal2-container {
	  	z-index: 9000;
	}
</style>
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include_once "includes/menu.php";?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once "includes/menu_superior.php";?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Consultar envios de correo a clientes</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Clientes</a>
                        </li>
						<li>
                            <a href="#">Asistentes</a>
                        </li>
                        <li class="active">
                            <strong>Consultar envios de correo a clientes</strong>
                        </li>
                    </ol>
				</div>
				<?php if (PermitirFuncion(601)) {?>
                <div class="col-sm-4">
					<div class="title-action">
						<a href="envio_correo_clientes.php" class="alkin btn btn-primary"><i class="fa fa-plus-circle"></i> Crear nuevo envio de correo a clientes</a>
					</div>
				</div>
				<?php }?>
            </div>
         <div class="wrapper wrapper-content">
			 <div class="modal inmodal fade" id="myModal" tabindex="1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content" id="ContenidoModal">

					</div>
				</div>
			</div>
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include "includes/spinner.php";?>
				  <form action="consultar_envio_correo_clientes.php" method="get" id="formBuscar" class="form-horizontal">
					  	<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Fechas</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial; ?>" autocomplete="off" />
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal; ?>" autocomplete="off" />
								</div>
							</div>

							<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-3">
								<input name="Cliente" type="hidden" id="Cliente" value="<?php if (isset($_GET['Cliente']) && ($_GET['Cliente'] != "")) {echo $_GET['Cliente'];}?>">
								<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Para TODOS, dejar vacio..." value="<?php if (isset($_GET['NombreCliente']) && ($_GET['NombreCliente'] != "")) {echo $_GET['NombreCliente'];}?>">
							</div>

							<div class="col-lg-4 pull-right">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>

					  	<?php if ($sw == 1 && false) {?>
					  	<div class="form-group">
							<div class="col-lg-10">
								<a href="exportar_excel.php?exp=10&Cons=<?php echo base64_encode(implode(",", $Param)); ?>&sp=<?php echo base64_encode("sp_ConsultarFormRecepcionVehiculos"); ?>">
									<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
								</a>
							</div>
						</div>
					   <?php }?>
				 </form>
			</div>
			</div>
		  </div>
         <br>
        <?php if ($sw == 1) {?>
		<div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					<?php include "includes/spinner.php";?>

					<?php if (false) {?>
						<div class="row m-b-md">
							<div class="col-lg-12">
								<button class="pull-right btn btn-success" id="btnCambiarLote" name="btnCambiarLote" onClick="CambiarEstado('',true);" disabled><i class="fa fa-pencil"></i> Cambiar estados en lote</button>
							</div>
						</div>
					<?php }?>

					<div class="table-responsive">
							<table class="table table-striped table-bordered table-hover dataTables-example" >
							<thead>
							<tr>
								<th>ID</th>
								<th>Cliente</th>
								<th>Fecha Creación</th>
								<th>Descripción</th>
								<th>Acciones</th>
							</tr>
							</thead>
							<tbody>
								<?php while ($row = sqlsrv_fetch_array($SQL)) {?>
									<tr class="odd gradeX line" >
										<td><?php echo $row['id']; ?></td>
										<td><?php echo $row['id_cliente'] . " - " . $row['cliente']; ?></td>
										<td><?php if ($row['fecha_creacion'] != "") {echo $row['fecha_creacion']->format('Y-m-d');} else {echo "--";}?></td>
										<td><?php echo $row['descripcion']; ?></td>
										<td>
											<a href="envio_correo_clientes.php?id=<?php echo $row['id']; ?>" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a>
										</td>
									</tr>
								<?php }?>
							</tbody>
							</table>
					  </div>
				</div>
			 </div>
          </div>
		  <?php }?>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once "includes/footer.php";?>

    </div>
</div>
<?php include_once "includes/pie.php";?>
<!-- InstanceBeginEditable name="EditRegion4" -->
 <script>
        $(document).ready(function(){
			$("#formBuscar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
			 $(".alkin").on('click', function(){
					$('.ibox-content').toggleClass('sk-loading');
				});
			 $('#FechaInicial').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true
            });
			 $('#FechaFinal').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true
            });

			$(".select2").select2();
			$('.chosen-select').chosen({width: "100%"});

			var options = {
				url: function(phrase) {
					return "ajx_buscar_datos_json.php?type=7&id="+phrase;
				},

				getValue: "NombreBuscarCliente",
				requestDelay: 400,
				list: {
					match: {
						enabled: true
					},
					onClickEvent: function() {
						var value = $("#NombreCliente").getSelectedItemData().CodigoCliente;
						$("#Cliente").val(value).trigger("change");
					}
				}
			};

			$("#NombreCliente").easyAutocomplete(options);

            $('.dataTables-example').DataTable({
                pageLength: 25,
				order: [[ 0, "desc" ]],
                dom: '<"html5buttons"B>lTfgitp',
				language: {
					"decimal":        "",
					"emptyTable":     "No se encontraron resultados.",
					"info":           "Mostrando _START_ - _END_ de _TOTAL_ registros",
					"infoEmpty":      "Mostrando 0 - 0 de 0 registros",
					"infoFiltered":   "(filtrando de _MAX_ registros)",
					"infoPostFix":    "",
					"thousands":      ",",
					"lengthMenu":     "Mostrar _MENU_ registros",
					"loadingRecords": "Cargando...",
					"processing":     "Procesando...",
					"search":         "Filtrar:",
					"zeroRecords":    "Ningún registro encontrado",
					"paginate": {
						"first":      "Primero",
						"last":       "Último",
						"next":       "Siguiente",
						"previous":   "Anterior"
					},
					"aria": {
						"sortAscending":  ": Activar para ordenar la columna ascendente",
						"sortDescending": ": Activar para ordenar la columna descendente"
					}
				},
                buttons: []

            });

        });

</script>
<script>
function CambiarEstado(id,lote=false){
	$('.ibox-content').toggleClass('sk-loading',true);

	if(lote){
		id=json
	}

	$.ajax({
		type: "POST",
		url: "md_frm_cambiar_estados.php",
		data:{
			id:id,
			frm: 'RecepcionVehiculos',
			nomID: 'id_recepcion_vehiculo'
		},
		success: function(response){
			$('.ibox-content').toggleClass('sk-loading',false);
			$('#ContenidoModal').html(response);
			$('#myModal').modal("show");
		}
	});
}

function PonerQuitarClase(ID){
	$(".trResum").removeClass('bg-light');
	$("#tr_Resum"+ID).addClass('bg-light');
}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>
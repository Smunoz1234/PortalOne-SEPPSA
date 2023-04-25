<?php require_once "includes/conexion.php";
PermitirAcceso(601);
$sw = 0;
$sp = 'sp_ConsultarPagosProveedores';

// AGREGAR FILTRO DE PROVEEDOR
$Cliente = "";
if (isset($_GET['Cliente']) && $_GET['Cliente'] != "") {
    $Cliente = $_GET['Cliente'];
    $sw = 1;
}

// AGREGAR FECHA DE FECHA DE REGISTRO
$FI_Registro = "";
$FF_Registro = "";
if (isset($_GET['FI_Registro']) && $_GET['FI_Registro'] != "") {
    $FI_Registro = $_GET['FI_Registro'];
    $sw = 1;
} else {
    /// Restar "n" días a la fecha actual.
    $nuevaFecha = strtotime('-' . ObtenerVariable("DiasRangoFechasGestionar") . ' day');
    $nuevaFecha = date('Y-m-d', $nuevaFecha);

    // SMM, 11/04/2023
    // $FI_Registro = $nuevaFecha;
}
if (isset($_GET['FF_Registro']) && $_GET['FF_Registro'] != "") {
    $FF_Registro = $_GET['FF_Registro'];
    $sw = 1;
} else {
    // SMM, 11/04/2023
    // $FF_Registro = date('Y-m-d');
}

// AGREGAR FILTROS DE FECHA DE PAGO
$FI_Pago = "";
$FF_Pago = "";
if (isset($_GET['FI_Pago']) && $_GET['FI_Pago'] != "") {
    $FI_Pago = $_GET['FI_Pago'];
    $sw = 1;
} else {
    // Restar "n" días a la fecha actual.
    $nuevaFecha = strtotime('-' . ObtenerVariable("DiasRangoFechasGestionar") . ' day');
    $nuevaFecha = date('Y-m-d', $nuevaFecha);

    // SMM, 11/04/2023
    $FI_Pago = $nuevaFecha;
}
if (isset($_GET['FF_Pago']) && $_GET['FF_Pago'] != "") {
    $FF_Pago = $_GET['FF_Pago'];
    $sw = 1;
} else {
    // SMM, 11/04/2023
    $FF_Pago = date('Y-m-d');
}

// AGREGAR FILTRO DE EGRESO
$PagoEfectuado = "";
if (isset($_GET['Egreso']) && $_GET['Egreso'] != "") {
    $PagoEfectuado = $_GET['Egreso'];
    $sw = 1;
}

if ($sw == 1) {
    $Param = array(
        "'" . $Cliente . "'", // Proveedor
        "'" . FormatoFecha($FI_Registro) . "'",
        "'" . FormatoFecha($FF_Registro) . "'",
        "'" . FormatoFecha($FI_Pago) . "'",
        "'" . FormatoFecha($FF_Pago) . "'",
        "'" . $PagoEfectuado . "'",
    );

    $SQL = EjecutarSP($sp, $Param);
}

// Esto debe estar al final, paran o generar conflictos con la lógica anterior.
$id = $_GET["id"] ?? "";

if ($id != "") {
    $sw = 1;

    $SQL_Encabezado = Seleccionar("tbl_PagosProveedores_Correos", "*", "id=$id");
    $row_Encabezado = sqlsrv_fetch_array($SQL_Encabezado);

    $fecha_inicial = $row_Encabezado["fecha_inicial"]->format('Y-m-d');
    $fecha_final = $row_Encabezado["fecha_final"]->format('Y-m-d');
    $id_proveedor = $row_Encabezado["id_proveedor"];
    $proveedor = $row_Encabezado["proveedor"];

    // No esta en la tabla actualmente.
    // $egreso_pago = $row_Encabezado["egreso_pago"];

    $descripcion = $row_Encabezado["descripcion"];

    // Cuerpo o detalle.
    $Cons = "SELECT * FROM tbl_PagosProveedores_Correos_Detalle WHERE id=$id";
    $SQL = sqlsrv_query($conexion, $Cons);
}

// Obtener la ruta para exportar a Excel.
$rutaExcel = "";
if (($sw == 1) && ($id == "")) {
    $encodeParam = base64_encode(implode(",", $Param));
    $encodeSP = base64_encode($sp);

    $rutaExcel = "exportar_excel.php?exp=10&Cons=$encodeParam&sp=$encodeSP";
} elseif (($sw == 1) && ($id != "")) {
    $encodeCons = base64_encode($Cons);
    // echo $Cons;

    $rutaExcel = "exportar_excel.php?exp=20&Cons=$encodeCons";
}

?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Envio correo pagos a proveedores | <?php echo NOMBRE_PORTAL; ?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<script>
	var json=[];
	var cant=0;

	function Seleccionar(ID) {
		var btnBorrarLineas=document.getElementById('btnBorrarLineas');

		var Check = document.getElementById('chkSel'+ID).checked;
		var sw=-1;

		json.forEach(function(element,index){
			// console.log(element,index);
			// console.log(json[index]);

			if(json[index]==ID){
				sw=index;
			}
		});

		if(sw>=0){
			json.splice(sw, 1);
			cant--;
		}else if(Check){
			json.push(ID);
			cant++;
		}

		if(cant>0){
			$("#btnBorrarLineas").prop('disabled', false);
		}else{
			$("#btnBorrarLineas").prop('disabled', true);
		}

		// console.log(json);
	}

	function SeleccionarTodos() {
		var Check = document.getElementById('chkAll').checked;
		if(Check==false){
			json=[];
			cant=0;
			$("#btnBorrarLineas").prop('disabled', true);
		}
		$(".chkSel:not(:disabled)").prop("checked", Check);
		if(Check){
			$(".chkSel:not(:disabled)").trigger('change');
		}
	}

	function BorrarLineas() {
		Swal.fire({
			title: "¿Está seguro que desea eliminar los registros seleccionados?",
			icon: "question",
			showCancelButton: true,
			confirmButtonText: "Si, confirmo",
			cancelButtonText: "No"
		}).then((result) => {
			if (result.isConfirmed) {
				console.log(json);

				// Obtener una referencia a la tabla DataTables
				let miTabla = $('#miTabla').DataTable();

				// Obtener la filas que se desea eliminar
				let filasEliminar = [];

				json.forEach(linea => {
					let fila = miTabla.row(`#line${linea}`);
					let indice = fila.index();

					// Agregar el indice a la lista
					filasEliminar.push(indice);
				});

				// Eliminar las filas de la tabla
				// console.log(filasEliminar);
				miTabla.rows(filasEliminar).remove().draw();

				// Restablecer arreglo
				json = [];
				cant = 0;

				// Mensaje final
				Swal.fire({
					title: "¡Listo!",
					text: "Registros eliminados exitosamente.",
					icon: "success"
				});
			} else {
				console.log("No se confirmo la eliminación.")
			}
		}); // Swal
	}
</script>

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
                    <h2>Envio correo pagos a proveedores</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Proveedores</a>
                        </li>
						<li>
                            <a href="#">Asistentes</a>
                        </li>
                        <li class="active">
                            <strong>Envio correo pagos a proveedores</strong>
                        </li>
                    </ol>
                </div>

				<div class="col-sm-4">
					<div class="title-action">
						<a href="consultar_envio_correo_proveedores.php" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar a consultar</a>
					</div>
				</div>
            </div>
         <div class="wrapper wrapper-content">
		  <div class="row">
				<div class="col-lg-12">
					<div class="ibox-content">
						 <?php include "includes/spinner.php";?>
					  <form action="envio_correo_proveedores.php" method="get" id="formBuscar" class="form-horizontal">
							<div class="form-group">
								<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
							</div>

							<div class="form-group">
								<label class="col-lg-1 control-label">Fecha de pago</label>
								<div class="col-lg-3">
									<div class="input-daterange input-group" id="datepicker">
										<input name="FI_Pago" type="text" class="input-sm form-control fecha" id="FI_Pago" placeholder="Fecha inicial" value="<?php echo ($id == "") ? $FI_Pago : $fecha_inicial; ?>" autocomplete="off"/>
										<span class="input-group-addon">hasta</span>
										<input name="FF_Pago" type="text" class="input-sm form-control fecha" id="FF_Pago" placeholder="Fecha final" value="<?php echo ($id == "") ? $FF_Pago : $fecha_final; ?>" autocomplete="off" />
									</div>
								</div>

								<label class="col-lg-1 control-label">Número Egreso</label>
								<div class="col-lg-3">
									<input name="Egreso" type="number" class="form-control" id="Egreso" value="<?php if (isset($_GET['Egreso']) && ($_GET['Egreso'] != "")) {echo $_GET['Egreso'];}?>">
								</div>
							</div>

							<div class="form-group">
								<label class="col-lg-1 control-label">Proveedor</label>
								<div class="col-lg-3">
									<input name="Cliente" type="hidden" id="Cliente" value="<?php if (isset($_GET['Cliente']) && isset($_GET['NombreCliente']) && ($_GET['NombreCliente'] != "")) {echo $_GET['Cliente'];} elseif ($id != "") {echo $id_proveedor;}?>">
									<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Para TODOS, dejar vacio..." value="<?php if (isset($_GET['NombreCliente']) && ($_GET['NombreCliente'] != "")) {echo $_GET['NombreCliente'];} elseif ($id != "") {echo $proveedor;}?>">
								</div>

								<div class="col-lg-4">
									<div class="btn-group pull-right">
										<button type="submit" class="btn btn-outline btn-success"><i class="fa fa-search"></i> Buscar</button>
									</div>
								</div>
							</div>

							<?php if (($sw == 1) && sqlsrv_has_rows($SQL)) {?>
								<div class="form-group">
									<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-envelope"></i> Información para envio de correos</h3></label>
								</div>

								<div class="form-group">
									<label class="col-lg-2 control-label">ID</label>
									<div class="col-lg-3">
										<input name="ID" type="text" class="form-control" id="ID" value="<?php echo $_GET['ID'] ?? $id; ?>" readonly>
									</div>

									<div class="col-lg-3"></div>

									<div class="col-lg-4">
										<button type="button" class="btn btn-outline btn-warning" id="Guardar"><i class="fa fa-save"></i> Guardar</button>
									</div>
								</div>

								<div class="form-group">
									<label class="col-lg-2 control-label">Descripción <span class="text-danger">*</span></label>
									<div class="col-lg-6">
										<textarea name="descripcion" id="descripcion" rows="5" cols="70" maxlength="250"><?php if (isset($_GET['descripcion']) && ($_GET['descripcion'] != "")) {echo $_GET['descripcion'];} elseif ($id != "") {echo $descripcion;}?></textarea>
									</div>

									<div class="col-lg-4">
										<a class="btn btn-outline btn-info" href="<?php echo $rutaExcel; ?>"><i class="fa fa-file-excel-o"></i> Exportar a Excel</a>
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
						<div class="table-responsive">
							<table id="miTabla" class="table table-striped table-bordered table-hover dataTables-example" >
								<thead>
									<tr>
										<th class="text-center form-inline">
											<div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div>
											<button type="button" id="btnBorrarLineas" title="Borrar lineas" class="btn btn-danger btn-xs" disabled onClick="BorrarLineas();"><i class="fa fa-trash"></i></button>
										</th>

										<th>Proveedor</th>

										<th>Núm. Factura Proveedor</th>
										<th>Núm. Factura SAP B1</th>

										<th>Fecha Factura</th>
										<th>Fecha Vencimiento</th>
										<th>Valor Factura</th>

										<th>Núm. Pago</th> <!-- Egreso -->

										<th>Fecha Pago</th>
										<th>Valor Pago</th>

										<th>Correo electrónico</th>

										<th>Contacto</th>

										<?php if ($id != "") {?>
											<th>Estado Envio</th>
										<?php }?>
									</tr>
								</thead>
								<tbody>
									<?php $cont = 0;?>
									<?php while ($row = sqlsrv_fetch_array($SQL)) {?>

										<tr class="odd gradeX line" id="line<?php echo $cont; ?>">
											<td class="text-center form-inline" style="width: 80px;">
												<div class="checkbox checkbox-success"><input type="checkbox" class="chkSel" id="chkSel<?php echo $cont; ?>" onChange="Seleccionar('<?php echo $cont; ?>');"><label></label></div>
											</td>

											<td><?php echo $row['id_proveedor'] . " - " . $row['proveedor']; ?></td>

											<td><?php echo $row['numero_factura_proveedor']; ?></td>
											<td><?php echo $row['numero_factura_SAPB1']; ?></td>

											<td><?php if ($row['fecha_factura'] != "") {echo $row['fecha_factura']->format('Y-m-d');} else {echo "--";}?></td>
											<td><?php if ($row['fecha_vencimiento_factura'] != "") {echo $row['fecha_vencimiento_factura']->format('Y-m-d');} else {echo "--";}?></td>
											<td align="right"><?php echo number_format($row['valor_factura'], 2); ?></td>

											<td><?php echo $row['numero_pago']; ?></td>

											<td><?php if ($row['fecha_pago'] != "") {echo $row['fecha_pago']->format('Y-m-d');} else {echo "--";}?></td>
											<td align="right">
												Efectivo: <?php echo number_format($row['valor_pago_efectivo'], 2); ?>
												<br>Transferencia: <?php echo number_format($row['valor_pago_transferencia'], 2); ?>
												<br>Cheque: <?php echo number_format($row['valor_pago_cheque'], 2); ?>
												<br>Núm. Cheque: <?php echo $row['numero_cheque'] ?? "--"; ?>
											</td>

											<td>
												<?php $correos = explode(';', $row['lista_correo_electronico_envio']);?>
												<?php foreach ($correos as &$correo) {?>
													<?php echo "<br>" . $correo; ?>
												<?php }?>
											</td>

											<td><?php if (isset($row['contacto']) && ($row['contacto'] != "")) {echo $row['id_contacto'] . " - " . $row['contacto'];}?></td>

											<?php if ($id != "") {?>
												<td>
													<?php $state = $row['estado_envio_correo'] ?? "";?>
													<br>Estado: <?php if ($state == "E") {echo "<i class='fa fa-check' style='color: green'></i>";} elseif ($state == "N") {echo "<b style='color: red'>x</b>";} else {echo "--";}?>
													<br>Mensaje: <?php echo $row['mensaje_envio'] ?? "--"; ?>
												</td>
											<?php }?>
										</tr>

									<?php $cont++;?>
									<?php }?>
								</tbody>
							</table>
						</div> <!-- table-responsive -->
					</div> <!-- ibox-content -->
				</div> <!-- col-lg-12 -->
          	</div> <!-- row -->
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
	// SMM, 17/04/2023
	<?php if ($id != "") {?>
		$("input").prop("disabled", true);
		$("button").prop("disabled", true);
		$("textarea").prop("disabled", true);
	<?php }?>

	function GuardarLineas() {
		let descripcion = $("#descripcion").val();
		let id_proveedor = $("#Cliente").val();
		let proveedor = $("#NombreCliente").val();
		let fecha_inicial = $("#FI_Pago").val();
		let fecha_final = $("#FF_Pago").val();

		// Obtener los valores de las columnas de todas las filas de la tabla
		let table = $('#miTabla').DataTable();
		let data = table.rows().data();

		// Armar el objeto JSON
		let errorID = false;
		let errorCorreos = false;
		let filasErroneas = [];

		let jsonData = [];
		data.each(function(rowData) {
			let datos_proveedor = rowData[1].split(" - ");

			// Buscar datos de pago
			let datos_pago = rowData[9];
			let numero_cheque = /Núm\. Cheque: (.+)/.exec(datos_pago)[1];
			let valor_pago_efectivo = parseFloat(/Efectivo: ([\d,\.]+)/.exec(datos_pago)[1].replace(',', ''));
			let valor_pago_transferencia = parseFloat(/Transferencia: ([\d,\.]+)/.exec(datos_pago)[1].replace(',', ''));
			let valor_pago_cheque = parseFloat(/Cheque: ([\d,\.]+)/.exec(datos_pago)[1].replace(',', ''));
			let valor_pago = valor_pago_efectivo + valor_pago_transferencia + valor_pago_cheque;

			// Definir la expresión regular para buscar las direcciones de correo electrónico
			let regexCorreo = /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g;
			let correos = rowData[10].match(regexCorreo);

			// Número de factura actual
			let facturaSAP = rowData[3];
			if(facturaSAP === "") {
				errorID = true;
			}

			let textoCorreos = "";
			if(correos !== null) {
				textoCorreos = correos.join(";");
			} else {
				errorCorreos = true;
				// console.log(correos);

				filasErroneas.push(facturaSAP);
			}

			// Buscar datos de contacto
			let datos_contacto = rowData[11].split(" - ");

			let rowObj = {
				id_proveedor: datos_proveedor[0],
				proveedor: datos_proveedor[1],
				numero_factura_proveedor: rowData[2],
				numero_factura_SAPB1: facturaSAP,
				fecha_factura: rowData[4],
				fecha_vencimiento_factura: rowData[5],
				valor_factura: parseFloat(rowData[6].replace(',', '')),
				numero_pago: rowData[7],
				fecha_pago: rowData[8],
				valor_pago: valor_pago,
				valor_pago_transferencia: valor_pago_transferencia,
				valor_pago_efectivo: valor_pago_efectivo,
				numero_cheque: numero_cheque,
				valor_pago_cheque: valor_pago_cheque,
				id_contacto: datos_contacto[0],
				contacto: datos_contacto[1],
				lista_correo_electronico_envio: textoCorreos
			};

			jsonData.push(rowObj);
		});

		// Imprimir JSON detalle
		// console.log(jsonData);

		if(errorID) {
			Swal.fire({
				title: "¡Advertencia!",
				text: "Todas los registros deben tener Núm. Factura SAP B1. Por favor, verifique.",
				icon: "warning"
			});
		}

		if(errorCorreos) {
			let msjFilasErroneas = `Verificar las filas con Núm. Factura SAP B1: ${filasErroneas.join(";")}`;

			Swal.fire({
				title: "¡Advertencia!",
				text: `Algunos de los registros presentan inconsistencias con los correos. ${msjFilasErroneas}`,
				icon: "warning"
			});
		}

		// SMM, 18/04/2023
		let errorData = false;
		if(data.length === 0) {
			errorData = true;

			Swal.fire({
				title: "¡Advertencia!",
				text: "No existen registros en la tabla, por favor verifique.",
				icon: "warning"
			});
		}

		if(!errorID && !errorCorreos && !errorData) {
			$.ajax({
				url: "envio_correo_proveedores_ws.php",
				method: "POST",
				data: {
					descripcion: descripcion,
					id_proveedor: id_proveedor,
					proveedor: proveedor,
					fecha_inicial: fecha_inicial,
					fecha_final: fecha_final,
					json_detalle: JSON.stringify(jsonData)
				},
				success: function(response) {
					if(response == "OK") {
						Swal.fire({
							title: "¡Listo!",
							text: "Procedimiento ejecutado exitosamente.",
							icon: "success"
						}).then(function() {
							window.location.href = "consultar_envio_correo_proveedores.php";
						});
					} else {
						Swal.fire({
							title: "¡Ha ocurrido un error!",
							text: response,
							icon: "error"
						});
					}
				},
				error: function(error) {
					console.error("GuardarLineas", error.responseText);
				}
			}); // ajax
		}
	}

	$("#Guardar").on('click', function() {
		if($("#descripcion").val() != "") {
			Swal.fire({
				title: "¿Está seguro que desea continuar con el guardado?",
				icon: "question",
				showCancelButton: true,
				confirmButtonText: "Si, confirmo",
				cancelButtonText: "No"
			}).then((result) => {
				if (result.isConfirmed) {
					GuardarLineas();
				} else {
					console.log("No se confirmo la eliminación.")
				}
			}); // Swal
		} else {
			Swal.fire({
				title: "¡Advertencia!",
				text: "Debe agregar una descripción.",
				icon: "warning"
			});
		}
	});

	$("#formBuscar").validate({
		submitHandler: function(form){
			$('.ibox-content').toggleClass('sk-loading');
			form.submit();
		}
	});

	$(".alkin").on('click', function(){
		$('.ibox-content').toggleClass('sk-loading');
	});

	$('.fecha').datepicker({
		todayBtn: "linked",
		keyboardNavigation: false,
		forceParse: false,
		calendarWeeks: true,
		autoclose: true,
		format: 'yyyy-mm-dd',
		todayHighlight: true,
	});

	$('.chosen-select').chosen({width: "100%"});
	maxLength('descripcion');

	let options = {
		url: function(phrase) {
			return "ajx_buscar_datos_json.php?type=7&id="+phrase+"&pv=1";
		},

		getValue: "NombreBuscarCliente",
		requestDelay: 400,
		list: {
			match: {
				enabled: true
			},
			onClickEvent: function() {
				var value = $("#NombreCliente").getSelectedItemData().CodigoCliente;
				$("#Cliente").val(value);
			}
		}
	};

	$("#NombreCliente").easyAutocomplete(options);

	$('.dataTables-example').DataTable({
		pageLength: 10,
		responsive: false,
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
		, order: [[ 1, "desc" ]]
	});
});
</script>

<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>

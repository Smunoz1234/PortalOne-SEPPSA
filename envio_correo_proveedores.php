<?php require_once "includes/conexion.php";
PermitirAcceso(601);
$sw = 0;
$sp = 'sp_ConsultarPagosEfectuados';

// AGREGAR FILTRO DE PROVEEDOR
$Proveedor = "";
if (isset($_GET['Cliente']) && $_GET['Cliente'] != "") {
    $Proveedor = $_GET['Cliente'];
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

if ($sw == 1) {
    $Param = array(
        "'" . $Proveedor . "'",
        "'" . FormatoFecha($FI_Registro) . "'",
        "'" . FormatoFecha($FF_Registro) . "'",
        "'" . FormatoFecha($FI_Pago) . "'",
        "'" . FormatoFecha($FF_Pago) . "'",
    );

    $SQL = EjecutarSP($sp, $Param);
}
?>

<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once "includes/cabecera.php";?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Pagos efectuados | <?php echo NOMBRE_PORTAL; ?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
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
                    <h2>Pagos efectuados</h2>
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
                            <strong>Pagos efectuados</strong>
                        </li>
                    </ol>
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
										<input name="FI_Pago" type="text" class="input-sm form-control fecha" id="FI_Pago" placeholder="Fecha inicial" value="<?php echo $FI_Pago; ?>" autocomplete="off"/>
										<span class="input-group-addon">hasta</span>
										<input name="FF_Pago" type="text" class="input-sm form-control fecha" id="FF_Pago" placeholder="Fecha final" value="<?php echo $FF_Pago; ?>" autocomplete="off" />
									</div>
								</div>

								<label class="col-lg-1 control-label">Número Egreso</label>
								<div class="col-lg-3">
									<input name="CiudadSede" type="text" class="form-control" id="CiudadSede" maxlength="100" value="<?php if (isset($_GET['CiudadSede']) && ($_GET['CiudadSede'] != "")) {echo $_GET['CiudadSede'];}?>">
								</div>
							</div>

							<div class="form-group">
								<label class="col-lg-1 control-label">Proveedor</label>
								<div class="col-lg-3">
									<input name="Cliente" type="hidden" id="Cliente" value="<?php if (isset($_GET['Cliente']) && ($_GET['Cliente'] != "")) {echo $_GET['Cliente'];}?>">
									<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Para TODOS, dejar vacio..." value="<?php if (isset($_GET['NombreCliente']) && ($_GET['NombreCliente'] != "")) {echo $_GET['NombreCliente'];}?>">
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
									<label class="col-lg-1 control-label">ID</label>
									<div class="col-lg-3">
										<input name="ID" type="text" class="form-control" id="ID" value="<?php echo $_GET['ID'] ?? ""; ?>" readonly>
									</div>

									<div class="col-lg-4"></div>

									<div class="col-lg-4">
										<button type="button" class="btn btn-outline btn-warning"><i class="fa fa-save"></i> Guardar</button>
									</div>
								</div>

								<div class="form-group">
									<label class="col-lg-1 control-label">Descripción</label>
									<div class="col-lg-7">
										<textarea name="descripcion" id="descripcion" rows="5" cols="100" maxlength="250"></textarea>
									</div>

									<div class="col-lg-4">
										<a class="btn btn-outline btn-info" href="exportar_excel.php?exp=10&Cons=<?php echo base64_encode(implode(",", $Param)); ?>&sp=<?php echo base64_encode($sp); ?>">Exportar a Excel</a>
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
                    <table class="table table-striped table-bordered table-hover dataTables-example" >
                    <thead>
                     <tr>
					 	<th class="text-center form-inline">
							<div class="checkbox checkbox-success"><input type="checkbox" id="chkAll" value="" onChange="SeleccionarTodos();" title="Seleccionar todos"><label></label></div>
							<button type="button" id="btnBorrarLineas" title="Borrar lineas" class="btn btn-danger btn-xs" disabled onClick="BorrarLinea();"><i class="fa fa-trash"></i></button>
						</th>

						<th>Núm. Factura Proveedor</th>
						<th>Núm. Factura SAP B1</th>

						<th>Fecha Factura</th>
						<th>Fecha Vencimiento</th>
						<th>Núm. Pago</th> <!-- Egreso -->
						<th>Valor Factura</th>

						<th>Proveedor</th>

						<th>Fecha Pago</th>

						<th>Valor Pago</th>

						<th>Correo electrónico</th>

						<?php if (false) {?>
							<th>Estado Envio</th>
						<?php }?>
					</tr>
                    </thead>
                    <tbody>
						   <?php while ($row = sqlsrv_fetch_array($SQL)) {?>
							<tr class="odd gradeX">
								<td class="text-center form-inline" style="width: 80px;">
									<div class="checkbox checkbox-success"><input type="checkbox" class="chkSel" id="chkSel<?php //echo $row['LineNum']; ?>" value="" onChange="Seleccionar('<?php //echo $row['LineNum']; ?>');" aria-label="Single checkbox One" <?php //if (($row['LineStatus'] == "C") && ($type == 1)) {echo "disabled='disabled'";}?>><label></label></div>
									<!--button type="button" class="btn btn-success btn-xs" onClick="ConsultarArticulo('<?php echo base64_encode($row['ItemCode']); ?>');" title="Consultar Articulo"><i class="fa fa-search"></i></button -->
								</td>

								<td><?php echo $row['numero_factura_Proveedor']; ?></td>
								<td><?php echo $row['numero_factura_SAPB1']; ?></td>

								<td><?php if ($row['fecha_factura'] != "") {echo $row['fecha_factura']->format('Y-m-d');} else {echo "--";}?></td>
								<td><?php if ($row['fecha_vencimiento_factura'] != "") {echo $row['fecha_vencimiento_factura']->format('Y-m-d');} else {echo "--";}?></td>
								<td><?php echo $row['numero_pago']; ?></td>
								<td align="right"><?php echo number_format($row['valor_factura'], 2); ?></td>

								<td><?php echo $row['proveedor']; ?></td>

								<td><?php if ($row['fecha_pago'] != "") {echo $row['fecha_pago']->format('Y-m-d');} else {echo "--";}?></td>

								<td align="right">
									Efectivo: <?php echo number_format($row['valor_pago_efectivo'], 2); ?>
									<br>Transferencia: <?php echo number_format($row['valor_pago_tranferencia'], 2); ?>
									<br>Cheque: <?php echo number_format($row['valor_pago_cheque'], 2); ?>
									<br>Núm. Cheque: <?php echo $row['numero_cheque']; ?>
								</td>

								<td>
									<?php $correos = explode(';', $row['lista_correo_electronico_envio']);?>
									<?php foreach ($correos as &$correo) {?>
										<?php echo "<br>" . $correo; ?>
									<?php }?>
								</td>

								<?php if (false) {?>
									<td>
										<br>Estado: <?php echo $row['estado_envio_correo']; ?>
										<br>Mensaje: <?php echo $row['mensaje_envio']; ?>
									</td>
								<?php }?>
							</tr>
							<?php }?>
						</tbody>
                    </table>
              </div>
			</div> <!-- table-responsive -->

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

			var options = {
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
                responsive: true,
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
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>
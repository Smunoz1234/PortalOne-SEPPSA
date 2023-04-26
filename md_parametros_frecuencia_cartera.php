<?php
require_once "includes/conexion.php";

$edit = isset($_POST['edit']) ? $_POST['edit'] : 0;
$id = isset($_POST['id']) ? $_POST['id'] : "";

$Title = "Crear Nuevo Parametro";
$Type = 1;

if ($edit == 1) {
    $Title = "Editar Parametro";
    $Type = 2;

    $SQL_Data = Seleccionar("tbl_EnvioCorreos_CarteraFrecuencia", "*", "[id]='$id'");
    $row_Data = sqlsrv_fetch_array($SQL_Data);

    $descripcion_frecuencia = $row_Data['descripcion_frecuencia'] ?? "";
    $cantidad_dias = $row_Data['cantidad_dias'] ?? "";
    $tipo_vencimiento = $row_Data['tipo_vencimiento'] ?? "";
    $hora_envio = $row_Data['hora_envio'] ?? "";
}
?>

<style>
	.select2-container {
		z-index: 10000;
	}
	.select2-search--inline {
    display: contents;
	}
	.select2-search__field:placeholder-shown {
		width: 100% !important;
	}

	.clockpicker-popover{
		z-index: 10000;
	}
</style>

<form id="frm_NewParam" method="post" action="parametros_frecuencia_cartera.php" enctype="multipart/form-data">
	<div class="modal-header">
		<h4 class="modal-title">
			<?php echo $Title; ?>
		</h4>
	</div>

	<div class="modal-body">
		<div class="form-group">
			<div class="ibox-content">
				<?php include "includes/spinner.php";?>

				<div class="form-group">
					<label class="control-label">Descripción <span class="text-danger">*</span></label>
					<textarea name="descripcion_frecuencia" rows="3" maxlength="250" class="form-control" id="descripcion_frecuencia" required><?php if ($edit == 1) {echo $descripcion_frecuencia;}?></textarea>
				</div>

				<div class="form-group">
					<label class="control-label">Cantidad Días <span class="text-danger">*</span></label>
					<input type="number" class="form-control" name="cantidad_dias" id="cantidad_dias" required value="<?php if ($edit == 1) {echo $cantidad_dias;}?>">
				</div>

				<div class="form-group">
					<label class="control-label">Tipo Vencimiento <span class="text-danger">*</span></label>
					<select name="tipo_vencimiento" class="form-control" id="tipo_vencimiento" required>
						<option value="1" <?php if (($edit == 1) && ($tipo_vencimiento == "1")) {echo "selected";}?>>Después del vencimiento</option>
						<option value="2" <?php if (($edit == 1) && ($tipo_vencimiento == "2")) {echo "selected";}?>>Antes del vencimiento</option>
					</select>
				</div>

				<div class="form-group">
					<label class="control-label">Hora Envío <span class="text-danger">*</span></label>
					<div class="input-group clockpicker" data-autoclose="true">
						<input autocomplete="off" required name="hora_envio" id="hora_envio" type="text" class="form-control" value="<?php if (($edit == 1) && ($hora_envio != "")) {echo $hora_envio->format('H:i');} else {echo date('H:i');}?>">
						<span class="input-group-addon">
							<span class="fa fa-clock-o"></span>
						</span>
					</div>
				</div>

			</div> <!-- ibox-content -->
		</div> <!-- form-group -->
	</div> <!-- modal-body -->

	<div class="modal-footer">
		<button type="submit" class="btn btn-success m-t-md"><i class="fa fa-check"></i> Aceptar</button>
		<button type="button" class="btn btn-danger m-t-md" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
	</div>

	<input type="hidden" id="MM_Insert" name="MM_Insert" value="1" />
	<input type="hidden" id="ID" name="ID" value="<?php echo $id; ?>" />
	<input type="hidden" id="type" name="type" value="<?php echo $Type; ?>" />
</form>

<script>
$(document).ready(function() {
	$(".select2").select2();

	$('.clockpicker').clockpicker({
		placement: 'left',
		autoclose: true
	});

	$("#frm_NewParam").validate({
		submitHandler: function(form){
			Swal.fire({
				title: "¿Está seguro que desea guardar los datos?",
				icon: "question",
				showCancelButton: true,
				confirmButtonText: "Si, confirmo",
				cancelButtonText: "No"
			}).then((result) => {
				if (result.isConfirmed) {
					$('.ibox-content').toggleClass('sk-loading',true);
					form.submit();
				}
			});
		}
	});
});
</script>

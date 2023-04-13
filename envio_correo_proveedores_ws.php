<?php require_once "includes/conexion.php";
print_r($_POST);
$sw_error = 0;

$descripcion = "'" . $_POST["descripcion"] . "'";
$id_proveedor = "'" . $_POST["id_proveedor"] . "'";
$proveedor = "'" . $_POST["proveedor"] . "'";
$fecha_inicial = "'" . $_POST["fecha_inicial"] . "'";
$fecha_final = "'" . $_POST["fecha_final"] . "'";
$fecha_registro = "'" . FormatoFecha(date('Y-m-d')) . "'";
$usuario = "'" . $_SESSION['CodUser'] . "'";
$fecha_hora = "'" . FormatoFecha(date('Y-m-d'), date('H:i:s')) . "'";

// Insertar Encabezado.
$Param = array(
    1, // @Type
    "NULL", // @ID
    $descripcion,
    $id_proveedor,
    $proveedor,
    $fecha_inicial,
    $fecha_final,
    $fecha_registro,
    $usuario, // @id_usuario_actualizacion
    $fecha_hora, // @fecha_actualizacion
    $fecha_hora, // @hora_actualizacion
    $usuario, // @id_usuario_creacion
    $fecha_hora, // @fecha_creacion
    $fecha_hora, // @hora_creacion
);
$SQL = EjecutarSP('sp_tbl_PagosProveedores_Correos', $Param);
if (!$SQL) {
    $sw_error = 1;
    $msg_error = "No se pudo insertar los datos del encabezado.";
}

// Insertar Detalle.
$Param_Detalle = array(
    1, // @Type
    "NULL", // @ID
    "NULL", // @id_linea
    $id_proveedor,
    $proveedor,
    $numero_factura_proveedor,
    $numero_factura_SAPB1,
    $fecha_factura,
    $fecha_vencimiento_factura,
    $valor_factura,
    $numero_pago,
    $fecha_pago,
    $valor_pago,
    $valor_pago_transferencia,
    $valor_pago_efectivo,
    $numero_cheque,
    $valor_pago_cheque,
    $id_contacto,
    $contacto,
    $lista_correo_electronico_envio,
    "NULL", // @estado_envio_correo
    "NULL", // @no_reintentos
    "NULL", // @mensaje_envio
    $fecha_registro,
    $usuario, // @id_usuario_actualizacion
    $fecha_hora, // @fecha_actualizacion
    $fecha_hora, // @hora_actualizacion
    $usuario, // @id_usuario_creacion
    $fecha_hora, // @fecha_creacion
    $fecha_hora, // @hora_creacion
);
$SQL_Detalle = EjecutarSP('sp_tbl_PagosProveedores_Correos_Detalle', $Param_Detalle);
if (!$SQL_Detalle) {
    $sw_error = 1;
    $msg_error = "No se pudo insertar los datos en el detalle.";
}

if ($sw_error == 1) {
    echo $msg_error;
} else {
    echo "OK";
}
sqlsrv_close($conexion);

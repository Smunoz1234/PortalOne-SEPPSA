<?php require_once "includes/conexion.php";
// print_r($_POST);
$sw_error = 0;

$descripcion = "'" . $_POST["descripcion"] . "'";
$id_proveedor = "'" . $_POST["id_proveedor"] . "'";
$proveedor = "'" . $_POST["proveedor"] . "'";
$fecha_inicial = "'" . $_POST["fecha_inicial"] . "'";
$fecha_final = "'" . $_POST["fecha_final"] . "'";
$fecha_registro = "'" . FormatoFecha(date('Y-m-d')) . "'";
$usuario = "'" . $_SESSION['CodUser'] . "'";
$fecha_hora = "'" . FormatoFecha(date('Y-m-d'), date('H:i:s')) . "'";

// Convierto el JSON del detalle a un arreglo.
$lineas = json_decode($_POST["json_detalle"]);
// print_r($lineas);

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

// Obtener el ID del Encabezado con la fecha y la hora de creación.
$SQL_Encabezado = Seleccionar("tbl_PagosProveedores_Correos", "id", "hora_creacion = $fecha_hora");
$row_Encabezado = sqlsrv_fetch_array($SQL_Encabezado);
$id = $row_Encabezado["id"];

foreach ($lineas as &$linea) {
    $Param_Detalle = array(
        1, // @Type
        $id, // @ID
        "NULL", // @id_linea
        "'" . $linea->id_proveedor . "'",
        "'" . $linea->proveedor . "'",
        "'" . $linea->numero_factura_proveedor . "'",
        "'" . $linea->numero_factura_SAPB1 . "'",
        "'" . $linea->fecha_factura . "'",
        "'" . $linea->fecha_vencimiento_factura . "'",
        "'" . $linea->valor_factura . "'",
        "'" . $linea->numero_pago . "'",
        "'" . $linea->fecha_pago . "'",
        "'" . $linea->valor_pago . "'",
        "'" . $linea->valor_pago_transferencia . "'",
        "'" . $linea->valor_pago_efectivo . "'",
        "'" . $linea->numero_cheque . "'", // Cuando esta vacio llega "--"
        "'" . $linea->valor_pago_cheque . "'",
        "'" . $linea->id_contacto . "'",
        "'" . $linea->contacto . "'",
        "'" . $linea->lista_correo_electronico_envio . "'",
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

    // Insertar Detalle.
    // print_r($Param_Detalle);

    $SQL_Detalle = EjecutarSP('sp_tbl_PagosProveedores_Correos_Detalle', $Param_Detalle);
    if (!$SQL_Detalle) {
        $sw_error = 1;
        $msg_error = "No se pudo insertar los datos en el detalle.";
    }
}

if ($sw_error == 1) {
    echo $msg_error;
} else {
    echo "OK";
}
sqlsrv_close($conexion);

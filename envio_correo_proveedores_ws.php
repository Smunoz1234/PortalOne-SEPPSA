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

$Param = array(
    1,
    "NULL",
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

if ($sw_error == 1) {
    echo $msg_error;
}

sqlsrv_close($conexion);

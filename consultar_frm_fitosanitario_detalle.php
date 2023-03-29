<?php
require_once( "includes/conexion.php" );

if(isset($_POST['id'])&&$_POST['id']!=""){
	$id = $_POST['id'];
}else{
	$id = "";
}

$SQL=Seleccionar("tbl_EstadoFitosanitariosDetalle","*","id_estado_fitosanitario='".$id."'");
$dir_anx=CrearObtenerDirAnx("formularios/estados_fitosanitarios/anexos");
?>
<div class="row m-t-md form-horizontal">
	 <div class="col-lg-12">
		<div class="ibox-content">
			 <?php include("includes/spinner.php"); ?>
			<div class="form-group">
				<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-list"></i> Detalle de estado fitosanitario: <?php echo $id;?></h3></label>
			</div>
			<div class="table-responsive">
				<table width="100%" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>#</th>
							<th>Motonave</th>
							<th>Producto</th>
							<th>Infestación producto</th>
							<th>Grado infestación</th>
							<th>Anexo</th>
						</tr>
					</thead>
					<tbody>
						 <?php $i=1;
							while($row=sqlsrv_fetch_array($SQL)){?>
						<tr>
							<td><?php echo $i;?></td>
							<td><?php echo $row['transporte_puerto'];?></td>
							<td><?php echo $row['producto_puerto'];?></td>
							<td><?php echo $row['tipo_infectacion_producto'];?></td>
							<td><?php echo $row['grado_infectacion'];?></td>
							<td><a href="filedownload.php?file=<?php echo base64_encode($row['anexo_muestra']);?>&dir=<?php echo base64_encode($dir_anx);?>" target="_blank" title="Descargar archivo" class="btn-link btn-xs"><i class="fa fa-download"></i> <?php echo $row['anexo_muestra'];?></a></td>
						</tr>	
						<?php $i++;}?>
					</tbody>
				</table>
			</div>
		</div>
	 </div> 
</div>
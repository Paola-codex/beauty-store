<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/conexion.php';
function respond($data, $status=200) { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
function fail($msg, $status=400) { respond(['error'=>$msg],$status); }
function positive($x) { return filter_var($x,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) !== false; }
function body() { $x=json_decode(file_get_contents('php://input'),true); return is_array($x)?$x:[]; }
$route=$_GET['route']??''; $method=$_SERVER['REQUEST_METHOD']; $data=body();
try {
 if($route==='salud' && $method==='GET') respond(['ok'=>true]);
 if($route==='clientes') {
  if($method==='GET') respond($pdo->query('SELECT * FROM clientes ORDER BY id_cliente DESC')->fetchAll());
  if($method==='POST') {
   $nombre=trim($data['nombre']??'');$correo=strtolower(trim($data['correo']??''));$telefono=trim($data['telefono']??'');
   if(!$nombre||mb_strlen($nombre)>100||!filter_var($correo,FILTER_VALIDATE_EMAIL)||mb_strlen($correo)>120||mb_strlen($telefono)>20) fail('Revisa nombre, correo y teléfono.');
   $q=$pdo->prepare('INSERT INTO clientes(nombre,correo,telefono) VALUES(?,?,?)');$q->execute([$nombre,$correo,$telefono]);
   respond(['id_cliente'=>(int)$pdo->lastInsertId(),'nombre'=>$nombre,'correo'=>$correo,'telefono'=>$telefono],201);
  }
 }
 if($route==='productos') {
  if($method==='GET') respond($pdo->query('SELECT * FROM productos ORDER BY id_producto')->fetchAll());
  if($method==='POST') {
   $nombre=trim($data['nombre']??'');$cat=trim($data['categoria']??'');$precio=$data['precio']??null;$stock=$data['stock']??null;$imagen=trim($data['imagen']??'');
   if(!$nombre||mb_strlen($nombre)>100||!$cat||mb_strlen($cat)>60||!is_numeric($precio)||(float)$precio<0||!preg_match('/^\d+$/',(string)$stock)||mb_strlen($imagen)>1000) fail('Datos del producto inválidos.');
   if($imagen!==''&&!preg_match('~^(imagenes/[a-z0-9-]+\.png|https://[^\s]+)$~i',$imagen)) fail('Ruta de imagen no válida.');
   $q=$pdo->prepare('INSERT INTO productos(nombre,categoria,precio,stock,imagen) VALUES(?,?,?,?,?)');$q->execute([$nombre,$cat,$precio,$stock,$imagen]);
   respond(['id_producto'=>(int)$pdo->lastInsertId()],201);
  }
 }
 if($route==='pedidos') {
  if($method==='GET') respond($pdo->query('SELECT pe.*,c.nombre AS cliente,p.nombre AS producto FROM pedidos pe JOIN clientes c ON c.id_cliente=pe.cliente_id JOIN productos p ON p.id_producto=pe.producto_id ORDER BY pe.id_pedido DESC')->fetchAll());
  if($method==='POST') {
   $cid=$data['cliente_id']??null;$pid=$data['producto_id']??null;$cantidad=$data['cantidad']??null;
   if(!positive($cid)||!positive($pid)||!positive($cantidad)) fail('Selecciona cliente, producto y cantidad válida.');
   $pdo->beginTransaction();
   $q=$pdo->prepare('SELECT id_cliente FROM clientes WHERE id_cliente=?');$q->execute([$cid]);if(!$q->fetch()) { $pdo->rollBack();fail('Cliente no encontrado.',404); }
   $q=$pdo->prepare('SELECT precio,stock FROM productos WHERE id_producto=? FOR UPDATE');$q->execute([$pid]);$p=$q->fetch();
   if(!$p){$pdo->rollBack();fail('Producto no encontrado.',404);}
   if((int)$p['stock']<(int)$cantidad){$pdo->rollBack();fail('No hay suficiente stock.');}
   $total=round((float)$p['precio']*(int)$cantidad,2);
   $q=$pdo->prepare('UPDATE productos SET stock=stock-? WHERE id_producto=?');$q->execute([$cantidad,$pid]);
   $q=$pdo->prepare('INSERT INTO pedidos(cliente_id,producto_id,cantidad,total) VALUES(?,?,?,?)');$q->execute([$cid,$pid,$cantidad,$total]);
   $id=(int)$pdo->lastInsertId();$pdo->commit();respond(['id_pedido'=>$id,'total'=>$total],201);
  }
  $id=$_GET['id']??null;if(!positive($id)) fail('ID inválido.');
  if($method==='PUT'||$method==='DELETE') {
   $pdo->beginTransaction();$q=$pdo->prepare('SELECT * FROM pedidos WHERE id_pedido=? FOR UPDATE');$q->execute([$id]);$old=$q->fetch();
   if(!$old){$pdo->rollBack();fail('Pedido no encontrado.',404);}
   if($method==='PUT') {
    $estado=$data['estado']??'';if(!in_array($estado,['Pendiente','Enviado','Entregado','Cancelado'],true)){$pdo->rollBack();fail('Estado inválido.');}
    if($old['estado']==='Cancelado'&&$estado!=='Cancelado'){$pdo->rollBack();fail('No se puede reactivar un pedido cancelado.');}
    if($estado==='Cancelado'&&$old['estado']!=='Cancelado'){$q=$pdo->prepare('UPDATE productos SET stock=stock+? WHERE id_producto=?');$q->execute([$old['cantidad'],$old['producto_id']]);}
    $q=$pdo->prepare('UPDATE pedidos SET estado=? WHERE id_pedido=?');$q->execute([$estado,$id]);$pdo->commit();respond(['id_pedido'=>(int)$id,'estado'=>$estado]);
   }
   if($old['estado']!=='Cancelado'){$q=$pdo->prepare('UPDATE productos SET stock=stock+? WHERE id_producto=?');$q->execute([$old['cantidad'],$old['producto_id']]);}
   $q=$pdo->prepare('DELETE FROM pedidos WHERE id_pedido=?');$q->execute([$id]);$pdo->commit();respond(['mensaje'=>'Pedido eliminado.']);
  }
 }
 fail('Ruta no encontrada.',404);
} catch(PDOException $e) {
 if($pdo->inTransaction()) $pdo->rollBack();
 if($e->getCode()==='23000') fail('El correo ya existe o el registro está relacionado con otro.',409);
 error_log($e->getMessage());fail('Error interno de la base de datos.',500);
} catch(Throwable $e) {
 if($pdo->inTransaction()) $pdo->rollBack();error_log($e->getMessage());fail('Error interno del servidor.',500);
}

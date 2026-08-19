<?php
require __DIR__ . '/bootstrap.php';
header('Content-Type: application/json');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['message'=>'Method not allowed']);exit;}
verify_csrf();$id=(int)($_POST['product_id']??0);$qty=max(1,(int)($_POST['quantity']??1));
$stmt=$db->prepare('SELECT id,name,stock FROM products WHERE id=? AND active=1');$stmt->execute([$id]);$product=$stmt->fetch();
if(!$product){http_response_code(404);echo json_encode(['message'=>'Product is unavailable.']);exit;}
$_SESSION['cart']=$_SESSION['cart']??[];
if(($_POST['action']??'add')==='remove'){unset($_SESSION['cart'][$id]);}
else{$_SESSION['cart'][$id]=min((int)$product['stock'],($_POST['action']??'add')==='set'?$qty:(int)($_SESSION['cart'][$id]??0)+$qty);if($_SESSION['cart'][$id]<1)unset($_SESSION['cart'][$id]);}
echo json_encode(['ok'=>true,'count'=>cart_count(),'message'=>($product['name'].' added to your bag.')]);


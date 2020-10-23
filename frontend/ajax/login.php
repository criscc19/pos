<?php
include_once('../../../main.inc.php');
include_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';

$username = $_POST['usuario'];
$password = $_POST['pass'];

$authmode=array('0'=>'dolibarr');
$login = checkLoginPassEntity($username,$password,$conf->entity,$authmode);

if($login){
	$sql = "SELECT rowid, lastname, firstname";
	$sql.= " FROM ".MAIN_DB_PREFIX."user";
	$sql.= " WHERE login = '".$username."'";
    $sql.= " AND entity IN (0,".$conf->entity.")";  

$result = $db->query($sql);
while($obj = $db->fetch_object($result)){
$usuario = new User($db);
$usuario->fetch($obj->rowid);
$usuario->getrights('pos', 1);
}    
  
echo json_encode($usuario);
}else{
echo '{"id":"-1"}';
}

?>
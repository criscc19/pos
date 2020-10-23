<?php 

require '../../../main.inc.php';

$provincias=array();
$cantones=array();
$distritos=array();
$barrios=array();

$id=$_POST['id'];
$nom=$_POST['nom'];
$provincia = $_POST['provincia'];
$canton= $_POST['canton'];
$distrito = $_POST['distrito'];
$barrio = $_POST['barrio'];


//PROVINCIA
 $resql=$db->query("select * from provincias");
 if ($resql)
 {
         $num = $db->num_rows($resql);
         $i = 0;
         if ($num)
         {
                 while ($i < $num)
                 {
                         $obj = $db->fetch_object($resql);
                         if ($obj)
                         {
                      $provincias[]=array('id' => $obj->id, 'provincia'=>$obj->provincia);
                         }
                         $i++;
                 }
        } };

//canton
 $resql2=$db->query("select * from cantones where fk_provincia=".$provincia."");
 if ($resql2)
 {
         $num2 = $db->num_rows($resql2);
         $i2 = 0;
         if ($num2)
         {
                 while ($i2 < $num2)
                 {
                         $obj2 = $db->fetch_object($resql2);
                         if ($obj2)
                         {
                      $cantones[]=array('id' => $obj2->id, 'canton'=>$obj2->canton);
                         }
                         $i2++;
                 }
        } };


//DISTRITO
 $resql3=$db->query("select * from distritos where fk_provincia=".$provincia." and fk_canton=".$canton."");
 if ($resql3)
 {
         $num3 = $db->num_rows($resql3);
         $i3 = 0;
         if ($num3)
         {
                 while ($i3 < $num3)
                 {
                         $obj3 = $db->fetch_object($resql3);
                         if ($obj3)
                         {
                      $distritos[]=array('id' => $obj3->id, 'distrito'=>$obj3->distrito);
                         }
                         $i3++;
                 }
        } };
 
//BARRIO
 $resql4=$db->query("select * from barrios where fk_provincia=".$provincia." and fk_canton=".$canton." and fk_distrito=".$distrito."");
 if ($resql4)
 {
         $num4 = $db->num_rows($resql4);
         $i4 = 0;
         if ($num4)
         {
                 while ($i4 < $num4)
                 {
                         $obj4 = $db->fetch_object($resql4);
                         if ($obj4)
                         {
                      $barrios[]=array('id' => $obj4->id, 'barrio'=>$obj4->barrio);
                         }
                         $i4++;
                 }
       }   } ;
		 
       $lugares ['provinvias']= $provincias;
       $lugares ['cantones']= $cantones;
       $lugares ['distritos']= $distritos;
       $lugares ['barrios']= $barrios;
echo json_encode($lugares);
?>
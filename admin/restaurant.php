<?php

include "../../main.inc.php";
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once '../lib/pos.lib.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/pos/frontend/include/funciones_restaurant.php';
require_once DOL_DOCUMENT_ROOT.'/pos/admin/qr_gen.php';
$form = new Form($db);

// Security check
if (!$user->admin)
accessforbidden();

$langs->load("admin");
$langs->load("pos@pos");

$action = GETPOST('action');

if($action == 'crear_mesa'){
$sq = 'INSERT INTO llx_pos_restaurant_mesas (
name, 
description, 
capacidad, 
ubicacion, 
fk_user
) VALUES (
"'.GETPOST('name').'",
"'.GETPOST('descripcion').'",
"'.GETPOST('capacidad').'",
"'.GETPOST('ubicacion').'",
"'.$user->id.'"
)';

$sql = $db->query($sq);
if($sql){
    setEventMessages('Mesa creada','');
    header('location: restaurant.php');exit;
    }
    else{setEventMessages('No fue posible crear la mesa: '.$sq, $db->errors, 'errors');}

}


if($action == 'crear_departamento'){
    $categories = implode(',',$_POST['categories']);
    $sq = 'INSERT INTO llx_pos_restaurant_departamentos (
    name, 
    categorias, 
    fk_user
    ) VALUES (
    "'.GETPOST('name').'",
    "'.$categories.'",
    "'.$user->id.'"
    )';
    
    $sql = $db->query($sq);
    if($sql){
        setEventMessages('Departamento creado','');
        header('location: restaurant.php');exit;
        }
        else{setEventMessages('No fue posible crear el departamento: '.$sq, $db->errors, 'errors');}
    
    }
    


/*
 * Actions
 */
if ((float) DOL_VERSION >= 6)
{
	include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';
}



/*
 * View
 */
$helpurl='EN:Module_DoliPos|FR:Module_DoliPos_FR|ES:M&oacute;dulo_DoliPos';
llxHeader('',$langs->trans("POSSetup"),$helpurl);

$html=new Form($db);

//encabezado
// Subheader

$page_name = $langs->trans("Configuracion del Restaurante");
$linkback = '<a href="'.($backtopage?$backtopage:DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_pos@pos');

// Configuration header
$head = posAdminPrepareHead();

dol_fiche_head($head, 'restaurant', '', -1, "pos@pos");
//fin de encabezado


print_titre('Creación de mesas');

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="action" value="crear_mesa">';
print '<table class="noborder" width="100%">';

print '<tr>';
print '<td>Nombre</td>';
print '<td><input type="text" name="name"></td>';
print '</tr>';

print '<tr>';
print '<td>Descripcion</td>';
print '<td><input type="text" name="descripcion"></td>';
print '</tr>';

print '<tr>';
print '<td>Capcidad de personas</td>';
print '<td><input type="text" name="capacidad"></td>';
print '</tr>';


print '<tr>';  
print '<td>Ubicacion</td>';
print '<td><input type="text" name="ubicacion"></td>';
print '</tr>';
print '</table>';
print '<center><input class="button" type="submit" value="Crear Mesa"></center></td>';
print '</form>';

print_titre('Definicion de departamentos para separar productos');
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="action" value="crear_departamento">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';  
print '<td>Nombre del departamento</td>';
print '<td>Categorias</td>';
print '<td></td>';
print '</tr>';

print '<tr>';  
print '<td><input type="text" name="name" required></td>';
print '<td>';
$cate_arbo = $form->select_all_categories(Categorie::TYPE_PRODUCT, '', 'parent', 64, 0, 1);
print $form->multiselectarray('categories', $cate_arbo, GETPOST('categories', 'array'), '', 0, '', 0, '100%');
print '</td>';

print '</tr>';
print '</table>';
print '<center><input class="button" type="submit" value="Crear departamento"></center>';
print '</form>';
print '<br>';
print '<hr>';
print '<center><h2>Mesas</h2></center>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';  
print '<td>Nombre</td>';
print '<td>Descripcion</td>';
print '<td>Capacidad</td>';
print '<td>Ubicacion</td>';
print '<td></td>';
print '</tr>';
$mesas = get_mesas();
foreach($mesas as $v){
$qrimg = qr_generator(DOL_MAIN_URL_ROOT.'/pos/frontend/orden_client.php?id='.$v->id.'',$v->id);
print '<tr>';
print '<td>'.$v->name.'</td>';
print '<td>'.$v->description.'</td>';
print '<td>'.$v->capacidad.'</td>';
print '<td><a href="restaurant.php?id='.$v->id.'&action=delete_mesa"><span class="fas fa-trash marginleftonly pictodelete" style=" color: #444;" title="Eliminar"></span></a></td>';
print '<td><a href="'.$qrimg.'"><img src="'.$qrimg.'"></a></td>';
print '<tr>';
}
print '</table>';



print '<br>';
print '<hr>';
print '<center><h2>Departamentos</h2></center>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';  
print '<td>Nombre</td>';
print '<td>Categorias</td>';
print '<td></td>';
print '</tr>';
$depa = get_departamentos();
foreach($depa as $v){
print '<tr>';
print '<td>'.$v->name.'</td>';
print '<td>';
$cate_arbo = $form->select_all_categories(Categorie::TYPE_PRODUCT, '', 'parent', 64, 0, 1);
print $form->multiselectarray('categories', $cate_arbo, explode(',',$v->categorias), '', 0, '', 0, '100%');
print '</td>';
print '<td><a href="restaurant.php?id='.$v->id.'&action=delete_depa"><span class="fas fa-trash marginleftonly pictodelete" style=" color: #444;" title="Eliminar"></span></a></td>';
print '<tr>';
}
print '</table>';
// Page end
dol_fiche_end();
llxFooter();
$db->close();

?>
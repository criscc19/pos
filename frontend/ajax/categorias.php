<?php
ini_set("memory_limit",-1);
set_time_limit(-1);
require '../../../main.inc.php';
require_once(DOL_DOCUMENT_ROOT.'/product/class/product.class.php');
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once(DOL_DOCUMENT_ROOT.'/pos/backend/class/cash.class.php');
require_once(DOL_DOCUMENT_ROOT.'/pos/frontend/include/funciones.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
include_once DOL_DOCUMENT_ROOT .'/core/lib/images.lib.php';
include_once DOL_DOCUMENT_ROOT .'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
$cashid = $_SESSION['TERMINAL_ID'];
$cash = new Cash($db);
$cash->fetch($cashid);
$warehouse = $cash->fk_warehouse;
if($_POST['action']=='getCategories'){

    $cates = get_categories((int)$_POST['parentcategory']); 
    echo json_encode($cates);
  }
  
  



  
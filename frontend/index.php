<?php
/* Copyright (C) 2011-2012	   Juanjo Menent   	   <jmenent@2byte.es>
 * Copyright (C) 2012-2013	   Ferran Marcet   	   <fmarcet@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *	\file       htdocs/pos/frontend/index.php
 * 	\ingroup	pos
 *  \brief      File to login to point of sales
 */

// Set and init common variables
// This include will set: config file variable $dolibarr_xxx, $conf, $langs and $mysoc objects

$res=@include("../../main.inc.php");                                   // For root directory
if (! $res) $res=@include("../../../main.inc.php");                // For "custom" directory

dol_include_once('/pos/backend/class/pos.class.php');
//dol_include_once('/pos/frontend/class/mobile_detect.php');

$langs->load("admin");
$langs->load("pos@pos");

if (! $user->rights->pos->frontend)
  accessforbidden();

// Test if user logged
if ( $_SESSION['uid'] > 0 && !isset($_GET['err']))
{
	header ('Location: '.dol_buildpath('/pos/frontend/disconect.php',1));
	exit;
}
global $user,$conf;

$usertxt=$user->login;
$pwdtxt=$user->pass;

$openterminal=GETPOST("openterminal");

$logo = DOL_MAIN_URL_ROOT.'/viewimage.php?modulepart=mycompany&file=logos/thumbs/'.$mysoc->logo_small; 
?>

<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/png" href="img/favicon.png"/>
		<title>Venta</title>

		<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script src="https://kit.fontawesome.com/62c4e05a29.js"></script>	
        <link rel='stylesheet'   href='css/easy-autocomplete.css' type='text/css' media='all' />	
        <link rel='stylesheet'   href='css/bootstrap.min.css' type='text/css' media='all' />       
		<link rel='stylesheet'   href='css/mdb.min.css' type='text/css' media='all' />		
        <link rel="stylesheet" href="sweetalert2/dist/sweetalert2.min.css">
		<link rel='stylesheet'   href='css/main.css' type='text/css' media='all' />
	</head>
    <body class="text-center">
    <div class="container">
    <div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4" style="margin-top:5%">
   
    <form class="form-signin"  id="frmLogin" method="POST" action="verify.php">
      <img class="mb-4" src="<?php echo $logo ?>">
      <h3 class="h2 mb-2 font-weight-normal">Iniciar Sesion</h3>
      <?php if(GETPOST("err","string")) {?>	
        <div class="alert alert-danger" role="alert"><?php print GETPOST("err","string")."<br>"; ?></div> <?php }?>
      <?php 
     $sq = 'SELECT * FROM `llx_pos_cash` WHERE `fk_user_u` ='.$user->id.'';
     $sql = $db->query($sq);
     $num = $db->num_rows($num);
     while($obj = $db->fetch_object($sql)){
     $name = $obj->name;
     $t_id = $obj->rowid;  
     }
      $terminals=POS::select_Terminals(); 

    ?>  

        <div class="input-group mb-3">      
        <div class="input-group-prepend">
        <span class="input-group-text" id="basic-addon1"><i class="fas fa-user-circle"></i></span>
        </div>
        <input name="txtUsername" id="txtUsername" type="text" class="form-control" placeholder="Usuario" aria-label="txtUsername" aria-describedby="basic-addon1">
        </div>

        <div class="input-group mb-3">      
        <div class="input-group-prepend">
        <span class="input-group-text" id="basic-addon1"><i class="fas fa-key"></i></span>
        </div>
        <input type="password" name="pwdPassword" id="pwdPassword" class="form-control" placeholder="Contraseña" aria-label="pwdPassword" aria-describedby="basic-addon1">
        </div>



        <div class="input-group mb-3">   
		<div class="input-group-prepend">
        <span class="input-group-text" id="basic-addon1"><i class="fas fa-cash-register"></i></span>
        </div> 
            <select name="txtTerminal" id="txtTerminal" class="custom-select">
            
            <?php 
					$detect = new Mobile_Detect();
          $i=0;
          if($num > 0){
          print "<option value='".$t_id."'>".$name."</option>\n";  
          }else{
            print '<option value="-1"></option>'; 
					foreach ($terminals as $terminal)
	    			{
                    print "<option value='".$terminal["rowid"]."'";
                    if(GETPOST('terminal') == $terminal["rowid"]){echo ' selected';}
                    print ">".$terminal["name"]."</option>\n";
                    $i++;
            }
          }
?>
            </select>  
        </div>    


        <div class="input-group mb-3">
		<div class="input-group-prepend">
        <span class="input-group-text" id="basic-addon1"><i class="fas fa-file-alt"></i></span>
        </div>       
            <select name="tipo" id="tipo" class="custom-select">
            <option value="1" <?php if(GETPOST('tipo')==1) echo 'selected'; ?>>TICKET</option>
            <option value="0" <?php if(GETPOST('tipo')==='0') echo 'selected'; ?>>FACTURA</option>
            <option value="3" <?php if(GETPOST('tipo')==3) echo 'selected'; ?>>APARTADO</option>
            <option value="4" <?php if(GETPOST('tipo')==4) echo 'selected'; ?>>COTIZACION</option>
            <option value="2" <?php if(GETPOST('tipo')==2) echo 'selected'; ?>>NDC</option>
            </select>  
        </div> 

        <div class="input-group mb-3">  
		<div class="input-group-prepend">
        <span class="input-group-text" id="basic-addon1"><i class="fas fa-money-bill"></i></span>
        </div>    				    
            <select name="moneda" id="moneda" class="custom-select">
            <option value="CRC" <?php if(GETPOST('moneda')=='CRC') echo 'selected'; ?>>COLONES</option>
            <option value="USD" <?php if(GETPOST('moneda')=='USD') echo 'selected'; ?>>DOLARES</option>
            </select>  
        </div>  

      <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      <p class="mt-5 mb-3 text-muted">NG TECHNOLOGY &copy; <?php echo '2018 - ',date('Y').''; ?></p>


  
    </form>

 
</div>
<div class="col-md-4"></div>
</div>
</div>
  </body>
</html> 
<script type='text/javascript' src='js/jquery.easy-autocomplete.js'></script>
<script type='text/javascript' src='js/bootstrap.min.js'></script>
<script type='text/javascript' src='js/mdb.min.js'></script>
<script src="sweetalert2/dist/sweetalert2.min.js"></script>
<script src="js/numeral/numeral.js"></script>
<script src="js/numeral/en-au.js"></script>
<script src="js/numeral/es.js"></script>
<script src="js/autocompletado.js.php"></script>
<script src="js/jquery.inputmask.js"></script>
<script src="ajax/lugares.js"></script>
<script src="js/cedulas.js"></script>
<script src="js/teclado.js"></script>
<script src="js/venta.js"></script>
<script src="js/calculadora.js"></script>



<?php
		if ($_GET['err'] < 0) 
		{

			echo ('<script type="text/javascript">');
			echo ('	document.getElementById(\'frmLogin\').pwdPassword.focus();');
			echo ('</script>');

		}	 
		else 
		{

			echo ('<script type="text/javascript">');
			echo ('	document.getElementById(\'frmLogin\').txtUsername.focus();');
			echo ('</script>');

		}
?>

<script>
// SideNav Button Initialization
$(".button-collapse").sideNav();
// SideNav Scrollbar Initialization
//var sideNavScrollbar = document.querySelector('.custom-scrollbar');
//var ps = new PerfectScrollbar(sideNavScrollbar);
 
</script>
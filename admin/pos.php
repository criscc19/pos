<?php
/* Copyright (C) 2008-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2011 	   Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2012-2013 Ferran Marcet        <fmarcet@2byte.es>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/cashdesk/admin/cashdesk.php
 *	\ingroup    cashdesk
 *	\brief      Setup page for cashdesk module
 */

$res=@include("../../main.inc.php");                                   // For root directory
if (! $res) $res=@include("../../../main.inc.php");                // For "custom" directory

//require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php"); //V3.2
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
dol_include_once("/pos/backend/class/ticket.class.php");
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/pos/frontend/include/funciones.php';
require_once '../lib/pos.lib.php';
$form = new Form($db);



// Security check
if (!$user->admin)
accessforbidden();

$langs->load("admin");
$langs->load("pos@pos");


/*
 * Actions
 */

if (GETPOST('action','string') == 'updateMask')
{
    $maskconstticket=GETPOST('maskconstticket');
    $maskconstticketcredit=GETPOST('maskconstticketcredit');
    $maskticket=GETPOST('maskticket');
    $maskcredit=GETPOST('maskcredit');
    $maskconstfacsim=GETPOST('maskconstfacsim');
    $maskconstfacsimcredit=GETPOST('maskconstfacsimcredit');
    $maskfacsim=GETPOST('maskfacsim');
    $maskfacsimcredit=GETPOST('maskfacsimcredit');
    $maskconstclosecash=GETPOST('maskconstclosecash');
    $maskconstclosecasharq=GETPOST('maskconstclosecasharq');
    $maskclosecash=GETPOST('maskclosecash');
    $maskclosecasharq=GETPOST('maskclosecasharq');
    if ($maskconstticket) dolibarr_set_const($db,$maskconstticket,$maskticket,'chaine',0,'',$conf->entity);
    if ($maskconstticketcredit) dolibarr_set_const($db,$maskconstticketcredit,$maskcredit,'chaine',0,'',$conf->entity);
    if ($maskconstfacsim) dolibarr_set_const($db,$maskconstfacsim,$maskfacsim,'chaine',0,'',$conf->entity);
    if ($maskconstfacsimcredit) dolibarr_set_const($db,$maskconstfacsimcredit,$maskfacsimcredit,'chaine',0,'',$conf->entity);
    if ($maskconstclosecash) dolibarr_set_const($db,$maskconstclosecash,$maskclosecash,'chaine',0,'',$conf->entity);
    if ($maskconstclosecasharq) dolibarr_set_const($db,$maskconstclosecasharq,$maskclosecasharq,'chaine',0,'',$conf->entity);
}

if (GETPOST("action") == 'set')
{
	$db->begin();
	$res = dolibarr_set_const($db,"POS_SERVICES", GETPOST("POS_SERVICES"),'chaine',0,'',$conf->entity);

	if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_PLACES", GETPOST("POS_PLACES"),'chaine',0,'',$conf->entity);
	
	if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_USE_TICKETS", GETPOST("POS_USE_TICKETS"),'chaine',0,'',$conf->entity);
			
	if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_STOCK", GETPOST("POS_STOCK"),'chaine',0,'',$conf->entity);
	
	if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_REGIMEN", GETPOST("POS_REGIMEN"),'chaine',0,'',$conf->entity);

	if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_PRINT", GETPOST("POS_PRINT"),'chaine',0,'',$conf->entity);
	
	if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_MAIL", GETPOST("POS_MAIL"),'chaine',0,'',$conf->entity);
	
	if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_FACTURE", GETPOST("POS_FACTURE"),'chaine',0,'',$conf->entity);
	
	if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"REWARDS_POS", GETPOST("REWARDS_POS"),'chaine',0,'',$conf->entity);
	
	if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_CHAT", GETPOST("POS_CHAT"),'chaine',0,'',$conf->entity);

if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_PREDEF_MSG", GETPOST("POS_PREDEF_MSG"),'chaine',0,'',$conf->entity);
	
if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_COND_REGLEMENT_ID_CASH", GETPOST("POS_COND_REGLEMENT_ID_CASH"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;


if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_COND_REGLEMENT_ID_CREDIT", GETPOST("POS_COND_REGLEMENT_ID_CREDIT"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;


if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_APARTADO_ENTREPOT", GETPOST("POS_APARTADO_ENTREPOT"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;

	

if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_APARTADO_BANK", GETPOST("POS_APARTADO_BANK"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;	


if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_CLIENT_LIMIT", GETPOST("POS_CLIENT_LIMIT"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;

	
if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_MIN_MONTO_APARTADO", GETPOST("POS_MIN_MONTO_APARTADO"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;	
	

if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_FORCE_LOGIN", GETPOST("POS_FORCE_LOGIN"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;	
	
	
if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"LIMIT_DESCUENTO", GETPOST("LIMIT_DESCUENTO"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;
	
	
if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"MODE_LIMIT", GETPOST("MODE_LIMIT"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;


if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"SUM_QTY", GETPOST("SUM_QTY"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;

if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_MAX_POSITIVE_MONTO", GETPOST("POS_MAX_POSITIVE_MONTO"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;	

if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_MAX_NEGATIVE_MONTO", GETPOST("POS_MAX_NEGATIVE_MONTO"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;	
	
if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_BANK_DIFERENCIAL", GETPOST("POS_BANK_DIFERENCIAL"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;		
	
if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_RESTAURANTE", GETPOST("POS_RESTAURANTE"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;	

if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_CLIENTE_GLOBAL", GETPOST("POS_CLIENTE_GLOBAL"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;
	
if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_MONEDA_GLOBAL", GETPOST("POS_MONEDA_GLOBAL"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;


if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_NIVEL_PRECIO_GLOBAL", GETPOST("POS_NIVEL_PRECIO_GLOBAL"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;


if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_TIPO_DOC_GLOBAL", GETPOST("POS_TIPO_DOC_GLOBAL"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;

if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_CLIENT_DESC", GETPOST("POS_CLIENT_DESC"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;


if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_VENDEDOR_GLOBAL", GETPOST("POS_VENDEDOR_GLOBAL"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;

	if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_CIERRE_BANCOS", GETPOST("POS_CIERRE_BANCOS"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;


if (! $res > 0) $error++;
	$res = dolibarr_set_const($db,"POS_CIERRE_DOLARES", GETPOST("POS_CIERRE_DOLARES"),'chaine',0,'',$conf->entity);	
	if (! $res > 0) $error++;	
	
	
 	if (! $error)
    {
        $db->commit();
        setEventMessages('Ajustes actualizados', array(), 'errors');
    }
    else
    {
        $db->rollback();
        setEventMessages('No se actuazo', $object->errors, 'errors');
    }
}


//sistema de puntos 
if ($_POST["save"])
{
	$db->begin();
	
	$i=0;
	
	$i+=dolibarr_set_const($db,'REWARDS_RATIO',trim($_POST['RewardsRatio']),'chaine',0,'',$conf->entity);
	$i+=dolibarr_set_const($db,'REWARDS_DISCOUNT',trim($_POST['RewardsDiscount']),'chaine',0,'',$conf->entity);
	$i+=dolibarr_set_const($db,'REWARDS_MINPAY',trim($_POST['RewardsMinPay']),'chaine',0,'',$conf->entity);
	$i+=dolibarr_set_const($db,'REWARDS_ADD_CUSTOMER',trim($_POST['RewardsAddCustomer']),'chaine',0,'',$conf->entity);
	
	if ($i >= 4)
	{
		$db->commit();
		setEventMessage($langs->trans('RewardsSetupSaved'));
	}
	else
	{
		setEventMessage($langs->trans('Error'),'errors');
		$db->rollback();
		header('Location: '.$_SERVER['PHP_SELF']);
		exit;
	}
}
//fin sistema de puntos



if ($_GET["action"] == 'setmod')
{
    dolibarr_set_const($db, "TICKET_ADDON",$_GET["value"],'chaine',0,'',$conf->entity);
}
if ($_GET["action"] == 'setmodfacsim')
{
	dolibarr_set_const($db, "FACSIM_ADDON",$_GET["value"],'chaine',0,'',$conf->entity);
}

if ($_GET["action"] == 'setmodclosecash')
{
	dolibarr_set_const($db, "CLOSECASH_ADDON",$_GET["value"],'chaine',0,'',$conf->entity);
}

/*
 * View
 */
$helpurl='EN:Module_DoliPos|FR:Module_DoliPos_FR|ES:M&oacute;dulo_DoliPos';
llxHeader('',$langs->trans("POSSetup"),$helpurl);

$html=new Form($db);

//encabezado
// Subheader

$page_name = $langs->trans("POSSetup");
$linkback = '<a href="'.($backtopage?$backtopage:DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_pos@pos');

// Configuration header
$head = posAdminPrepareHead();

dol_fiche_head($head, 'settings', '', -1, "pos@pos");
//fin de encabezado

if($conf->global->POS_USE_TICKETS == 1){
print_titre($langs->trans("TicketsNumberingModule"));

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td nowrap>'.$langs->trans("Example").'</td>';
print '<td align="center" width="60">'.$langs->trans("Status").'</td>';
print '<td align="center" width="16">'.$langs->trans("Infos").'</td>';
print '</tr>'."\n";

clearstatcache();

$var=true;
foreach ($conf->file->dol_document_root as $dirroot)
{
    $dir = $dirroot . "/pos/backend/numerotation/";

    if (is_dir($dir))
    {
        $handle = opendir($dir);
        if (is_resource($handle))
        {
            while (($file = readdir($handle))!==false)
            {
                if (! is_dir($dir.$file) || (substr($file, 0, 1) <> '.' && substr($file, 0, 3) <> 'CVS'))
                {
                    $filebis = $file;
                    $classname = preg_replace('/\.php$/','',$file);
                    // For compatibility
                    if (! is_file($dir.$filebis))
                    {
                        $filebis = $file."/".$file.".modules.php";
                        $classname = "mod_ticket_".$file;
                    }
                    //print "x".$dir."-".$filebis."-".$classname;
                    if (! class_exists($classname) && is_readable($dir.$filebis) && (preg_match('/mod_/',$filebis) || preg_match('/mod_/',$classname)) && substr($filebis, dol_strlen($filebis)-3, 3) == 'php')
                    {
                        // Chargement de la classe de numerotation
                        require_once($dir.$filebis);

                        $module = new $classname($db);

                        // Show modules according to features level
                        if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) continue;
                        if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) continue;

                        if ($module->isEnabled())
                        {
                            $var = !$var;
                            print '<tr '.$bc[$var].'><td width="100">';
                            echo preg_replace('/mod_ticket_/','',preg_replace('/\.php$/','',$file));
                            print "</td><td>\n";

                            print $module->info();

                            print '</td>';

                            // Show example of numbering module
                            print '<td nowrap="nowrap">';
                            $tmp=$module->getExample();
                            if (preg_match('/^Error/',$tmp)) print $langs->trans($tmp);
                            else print $tmp;
                            print '</td>'."\n";

                            print '<td align="center">';
                            //print "> ".$conf->global->FACTURE_ADDON." - ".$file;
                            if ($conf->global->TICKET_ADDON == $file || $conf->global->TICKET_ADDON.'.php' == $file)
                            {
                                print img_picto($langs->trans("Activated"),'on');
                            }
                            else
                            {
                                print '<a href="'.$_SERVER["PHP_SELF"].'?action=setmod&amp;value='.preg_replace('/\.php$/','',$file).'">'.img_picto($langs->trans("Disabled"),'off').'</a>';
                            }
                            print '</td>';

                            $facture=new Ticket($db);
                           // $facture->initAsSpecimen();

                            // Example for standard invoice
                            $htmltooltip='';
                            $htmltooltip.=''.$langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';
                            $facture->type=0;
                            $nextval=$module->getNextValue($mysoc,$facture);
                            if ("$nextval" != $langs->trans("NotAvailable"))	// Keep " on nextval
                            {
                                $htmltooltip.=$langs->trans("NextValueForTickets").': ';
                                if ($nextval)
                                {
                                    $htmltooltip.=$nextval.'<br>';
                                }
                                else
                                {
                                    $htmltooltip.=$langs->trans($module->error).'<br>';
                                }
                            }
                            

                            print '<td align="center">';
                            print $html->textwithpicto('',$htmltooltip,1,0);

                            if ($conf->global->TICKET_ADDON.'.php' == $file)  // If module is the one used, we show existing errors
                            {
                                if (! empty($module->error)) dol_htmloutput_mesg($module->error,'','error',1);
                            }

                            print '</td>';

                            print "</tr>\n";

                        }
                    }
                }
            }
            closedir($handle);
        }
    }
}

print '</table>';

print "<br>";
}
if($conf->global->POS_FACTURE == 1){
print_titre($langs->trans("FacsimNumberingModule"));

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td nowrap>'.$langs->trans("Example").'</td>';
print '<td align="center" width="60">'.$langs->trans("Status").'</td>';
print '<td align="center" width="16">'.$langs->trans("Infos").'</td>';
print '</tr>'."\n";

clearstatcache();

$var=true;
foreach ($conf->file->dol_document_root as $dirroot)
{
	$dir = $dirroot . "/pos/backend/numerotation/numerotation_facsim/";

	if (is_dir($dir))
	{
		$handle = opendir($dir);
		if (is_resource($handle))
		{
			while (($file = readdir($handle))!==false)
			{
				if (! is_dir($dir.$file) || (substr($file, 0, 1) <> '.' && substr($file, 0, 3) <> 'CVS'))
				{
					$filebis = $file;
					$classname = preg_replace('/\.php$/','',$file);
					// For compatibility
					if (! is_file($dir.$filebis))
					{
						$filebis = $file."/".$file.".modules.php";
						$classname = "mod_facsim_".$file;
					}
					//print "x".$dir."-".$filebis."-".$classname;
					if (! class_exists($classname) && is_readable($dir.$filebis) && (preg_match('/mod_/',$filebis) || preg_match('/mod_/',$classname)) && substr($filebis, dol_strlen($filebis)-3, 3) == 'php')
					{
						// Chargement de la classe de numerotation
						require_once($dir.$filebis);

						$module = new $classname($db);

						// Show modules according to features level
						if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) continue;
						if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) continue;

						if ($module->isEnabled())
						{
							$var = !$var;
							print '<tr '.$bc[$var].'><td width="100">';
							echo preg_replace('/mod_facsim_/','',preg_replace('/\.php$/','',$file));
							print "</td><td>\n";

							print $module->info();

							print '</td>';

							// Show example of numbering module
							print '<td nowrap="nowrap">';
							$tmp=$module->getExample();
							if (preg_match('/^Error/',$tmp)) print $langs->trans($tmp);
							else print $tmp;
							print '</td>'."\n";

							print '<td align="center">';
							//print "> ".$conf->global->FACTURE_ADDON." - ".$file;
							if ($conf->global->FACSIM_ADDON == $file || $conf->global->FACSIM_ADDON.'.php' == $file)
							{
								print img_picto($langs->trans("Activated"),'on');
							}
							else
							{
								print '<a href="'.$_SERVER["PHP_SELF"].'?action=setmodfacsim&amp;value='.preg_replace('/\.php$/','',$file).'">'.img_picto($langs->trans("Disabled"),'off').'</a>';
							}
							print '</td>';

							$facture=new Ticket($db);
							//$facture->initAsSpecimen();

							// Example for standard invoice
							$htmltooltip='';
							$htmltooltip.=''.$langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';
							$facture->type=0;
							$nextval=$module->getNextValue($mysoc,$facture);
							if ("$nextval" != $langs->trans("NotAvailable"))	// Keep " on nextval
							{
								$htmltooltip.=$langs->trans("NextValueForTickets").': ';
								if ($nextval)
								{
									$htmltooltip.=$nextval.'<br>';
								}
								else
								{
									$htmltooltip.=$langs->trans($module->error).'<br>';
								}
							}


							print '<td align="center">';
							print $html->textwithpicto('',$htmltooltip,1,0);

							if ($conf->global->FACSIM_ADDON.'.php' == $file)  // If module is the one used, we show existing errors
							{
								if (! empty($module->error)) dol_htmloutput_mesg($module->error,'','error',1);
							}

							print '</td>';

							print "</tr>\n";

						}
					}
				}
			}
			closedir($handle);
		}
	}
}

print '</table>';

print "<br>";
}


	print_titre($langs->trans("CloseCashNumberingModule"));

	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Name").'</td>';
	print '<td>'.$langs->trans("Description").'</td>';
	print '<td nowrap>'.$langs->trans("Example").'</td>';
	print '<td align="center" width="60">'.$langs->trans("Status").'</td>';
	print '<td align="center" width="16">'.$langs->trans("Infos").'</td>';
	print '</tr>'."\n";

	clearstatcache();

	$var=true;
	foreach ($conf->file->dol_document_root as $dirroot)
	{
		$dir = $dirroot . "/pos/backend/numerotation/numerotation_closecash/";

		if (is_dir($dir))
		{
			$handle = opendir($dir);
			if (is_resource($handle))
			{
				while (($file = readdir($handle))!==false)
				{
					if (! is_dir($dir.$file) || (substr($file, 0, 1) <> '.' && substr($file, 0, 3) <> 'CVS'))
					{
						$filebis = $file;
						$classname = preg_replace('/\.php$/','',$file);
						// For compatibility
						if (! is_file($dir.$filebis))
						{
							$filebis = $file."/".$file.".modules.php";
							$classname = "mod_closecash_".$file;
						}
						//print "x".$dir."-".$filebis."-".$classname;
						if (! class_exists($classname) && is_readable($dir.$filebis) && (preg_match('/mod_/',$filebis) || preg_match('/mod_/',$classname)) && substr($filebis, dol_strlen($filebis)-3, 3) == 'php')
						{
							// Chargement de la classe de numerotation
							require_once($dir.$filebis);

							$module = new $classname($db);

							// Show modules according to features level
							if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) continue;
							if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) continue;

							if ($module->isEnabled())
							{
								$var = !$var;
								print '<tr '.$bc[$var].'><td width="100">';
								echo preg_replace('/mod_closecash_/','',preg_replace('/\.php$/','',$file));
								print "</td><td>\n";

								print $module->info();

								print '</td>';

								// Show example of numbering module
								print '<td nowrap="nowrap">';
								$tmp=$module->getExample();
								if (preg_match('/^Error/',$tmp)) print $langs->trans($tmp);
								else print $tmp;
								print '</td>'."\n";

								print '<td align="center">';
								//print "> ".$conf->global->FACTURE_ADDON." - ".$file;
								if ($conf->global->CLOSECASH_ADDON == $file || $conf->global->CLOSECASH_ADDON.'.php' == $file)
								{
									print img_picto($langs->trans("Activated"),'on');
								}
								else
								{
									print '<a href="'.$_SERVER["PHP_SELF"].'?action=setmodclosecash&amp;value='.preg_replace('/\.php$/','',$file).'">'.img_picto($langs->trans("Disabled"),'off').'</a>';
								}
								print '</td>';

								$facture=new Ticket($db);
								$facture->initAsSpecimen();

								// Example for standard invoice
								$htmltooltip='';
								$htmltooltip.=''.$langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';
								$facture->type_control=1;
								$nextval=$module->getNextValue($mysoc,$facture);
								if ("$nextval" != $langs->trans("NotAvailable"))	// Keep " on nextval
								{
									$htmltooltip.=$langs->trans("NextValueForTickets").': ';
									if ($nextval)
									{
										$htmltooltip.=$nextval.'<br>';
									}
									else
									{
										$htmltooltip.=$langs->trans($module->error).'<br>';
									}
								}


								print '<td align="center">';
								print $html->textwithpicto('',$htmltooltip,1,0);

								if ($conf->global->CLOSECASH_ADDON.'.php' == $file)  // If module is the one used, we show existing errors
								{
									if (! empty($module->error)) dol_htmloutput_mesg($module->error,'','error',1);
								}

								print '</td>';

								print "</tr>\n";

							}
						}
					}
				}
				closedir($handle);
			}
		}
	}

	print '</table>';

	print "<br>";

print_titre($langs->trans("OtherOptions"));

// Mode
$var=true;
print '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="set">';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td><td>'.$langs->trans("Value").'</td>';
print "</tr>\n";


$var=! $var;
print '<tr '.$bc[$var].'><td>';
print $langs->trans("Usar funciones de restaurante");
print '<td colspan="2">';
print $html->selectyesno("POS_RESTAURANTE",$conf->global->POS_RESTAURANTE,1);
print "</td></tr>\n";


print '<tr>';
print '<td class="fieldrequired">Cliente contado global</td>';
print '<td>';
print $form->select_company(isset($conf->global->POS_CLIENTE_GLOBAL)?$conf->global->POS_CLIENTE_GLOBAL:1,'POS_CLIENTE_GLOBAL','s.client=1 or s.client=3',1,1);
print '</td>';
print '</tr>';


print '<tr>';
print '<td class="fieldrequired">Moneda global</td>';
print '<td>';
print '<select name="POS_MONEDA_GLOBAL">';
print '<option value="CRC" ';
if($conf->global->POS_MONEDA_GLOBAL == 'CRC') echo 'selected';
print '>COLONES</option>';
print '<option value="USD" ';
if($conf->global->POS_MONEDA_GLOBAL == 'USD') echo 'selected';
print '>DOLARES</option>';
print '</select>';
print '</td>';
print '</tr>';


print '<tr>';
print '<td class="fieldrequired">Nivel de precio global</td>';
print '<td>';
print '<input type="text" name="POS_NIVEL_PRECIO_GLOBAL" value="'.(isset($conf->global->POS_NIVEL_PRECIO_GLOBAL)?$conf->global->POS_NIVEL_PRECIO_GLOBAL:1).'">';
print '</td>';
print '</tr>';


print '<tr>';
print '<td class="fieldrequired">Tipo de documento global</td>';
print '<td>';
print '<select name="POS_TIPO_DOC_GLOBAL" id="POS_TIPO_DOC_GLOBAL" class="custom-select">';
print '<option value="1"'; if($conf->global->POS_TIPO_DOC_GLOBAL== 1)  echo 'selected';print '>TICKET</option>';
print '<option value="0"'; if($conf->global->POS_TIPO_DOC_GLOBAL== 0)  echo 'selected';print '>FACTURA</option>';

print '<option value="3"'; if($conf->global->POS_TIPO_DOC_GLOBAL== 3)  echo 'selected';print '>APARTADO</option>';

print '<option value="4"'; if($conf->global->POS_TIPO_DOC_GLOBAL== 4)  echo 'selected';print '>COTIZACION</option>';

print '<option value="2"'; if($conf->global->POS_TIPO_DOC_GLOBAL== 2)  echo 'selected';print '>NDC</option>';

print '</select>'; 
print '</td>';
print '</tr>';


print '<tr>';
print '<td class="fieldrequired">Vendedor global</td>';
print '<td>';
print $form->select_dolusers($conf->global->POS_VENDEDOR_GLOBAL, 'POS_VENDEDOR_GLOBAL', 1, '', 0, '', $array, 0, 0, 0, '', 0, '', 'log_user browser-default custom-select');
print '</td>';
print '</tr>';

$var=! $var;
print '<tr '.$bc[$var].'><td>';
print $langs->trans("Usar descuento automatico de clientes");
print '<td colspan="2">';
print $html->selectyesno("POS_CLIENT_DESC",$conf->global->POS_CLIENT_DESC,1);
print "</td></tr>\n";

$var=! $var;
print '<tr '.$bc[$var].'><td>';
print $langs->trans("POSUseTickets");
print '<td colspan="2">';
if($conf->global->POS_FACTURE == 0)
	$disable=true;
else
	$disable=false;
print $html->selectyesno("POS_USE_TICKETS",$conf->global->POS_USE_TICKETS,1,$disable);
if($disable)print '<input type="hidden" name="POS_USE_TICKETS" value="'.$conf->global->POS_USE_TICKETS.'">';
print "</td></tr>\n";

$var=! $var;
print '<tr '.$bc[$var].'><td>';
print $langs->trans("POSFactureTicket");
print '<td colspan="2">';
if($conf->global->POS_USE_TICKETS == 0)
	$disable=true;
else
	$disable=false;
print $html->selectyesno("POS_FACTURE",$conf->global->POS_FACTURE,1,$disable);
if($disable) print '<input type="hidden" name="POS_FACTURE" value="'.$conf->global->POS_FACTURE.'">';
print "</td></tr>\n";

$var=! $var;
print '<tr '.$bc[$var].'><td>';
print $langs->trans("Modo regimen simplificado (No se envia a hacienda)");
print '<td colspan="2">';;
print $html->selectyesno("POS_REGIMEN",$conf->global->POS_REGIMEN,1);
print "</td></tr>\n";

if ($conf->service->enabled)
{
    $var=! $var;
    print '<tr '.$bc[$var].'><td>';
    print $langs->trans("POSShowServices");
    print '<td colspan="2">';;
    print $html->selectyesno("POS_SERVICES",$conf->global->POS_SERVICES,1);
    print "</td></tr>\n";
}

	$var=! $var;
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans("POSShowPlaces");
	print '<td colspan="2">';;
	print $html->selectyesno("POS_PLACES",$conf->global->POS_PLACES,1);
	print "</td></tr>\n";
	
	$var=! $var;
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans("POSSellStock");
	print '<td colspan="2">';;
	print $html->selectyesno("POS_STOCK",$conf->global->POS_STOCK,1);
	print "</td></tr>\n";

	$var=! $var;
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans("POSPrintTicket");
	print '<td colspan="2">';;
	print $html->selectyesno("POS_PRINT",$conf->global->POS_PRINT,1);
	print "</td></tr>\n";
	

	$var=! $var;
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans("POSMailTicket");
	print '<td colspan="2">';
	print $html->selectyesno("POS_MAIL",$conf->global->POS_MAIL,1);
	print "</td></tr>\n";

	$var=! $var;
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans("POSChat");
	print '<td colspan="2">';
	print $html->selectyesno("POS_CHAT",$conf->global->POS_CHAT,1);
	print "</td></tr>\n";

	$var=! $var;
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans("Sumar cantidades de lineas");
	print '<td colspan="2">';
	print $html->selectyesno("SUM_QTY",$conf->global->SUM_QTY,1);
	print "</td></tr>\n";	
	

	print '<tr>';
	print '<td class="fieldrequired">Limitacion de descuento</td>';
	print '<td><select name="MODE_LIMIT">';
	print '<option value="global" '.($conf->global->MODE_LIMIT=='global'?'selected':'').'selected>Global</option>';
	print '<option value="producto" '.($conf->global->MODE_LIMIT=='producto'?'selected':'').'>Por producto</option>';
	print '<option value="cliente" '.($conf->global->MODE_LIMIT=='cliente'?'selected':'').'>Por cliente</option>';
	print '</select>';
	print '</td>';
	print '</tr>';

	print '<tr>';
	print '<td class="fieldrequired">Descuento maximo global</td>';
	print '<td>';
	print '<input type="text" name="LIMIT_DESCUENTO" value="'.$conf->global->LIMIT_DESCUENTO.'">';
	print '</td>';
	print '</tr>';
	
	
	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>Rango minimo positivo para pasar facturas a pagadas</td>';
	print '<td><input type="text" class="flat" name="POS_MAX_POSITIVE_MONTO" value="'. ($_POST["POS_MAX_POSITIVE_MONTO"]?$_POST["POS_MAX_POSITIVE_MONTO"]:$conf->global->POS_MAX_POSITIVE_MONTO) . '" size="8"></td>';
	print '</tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>Rango minimo Negativo para pasar facturas a pagadas</td>';
	print '<td><input type="text" class="flat" name="POS_MAX_NEGATIVE_MONTO" value="'. ($_POST["POS_MAX_NEGATIVE_MONTO"]?$_POST["POS_MAX_NEGATIVE_MONTO"]:$conf->global->POS_MAX_NEGATIVE_MONTO) . '" size="8"></td>';
	print '</tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("POSMaxTTC").'</td>';
	print '<td><input type="text" class="flat" name="POS_MAX_TTC" value="'. ($_POST["POS_MAX_TTC"]?$_POST["POS_MAX_TTC"]:$conf->global->POS_MAX_TTC) . '" size="8"> '.$langs->trans("Currency".$conf->currency).'</td>';
	print '</tr>';


	$var=! $var;
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans("Limitar facturacion por limite de credito de cliente");
	print '<td colspan="2">';
	print $html->selectyesno("POS_CLIENT_LIMIT",$conf->global->POS_CLIENT_LIMIT,1);
	print "</td></tr>\n";
	
	
	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>Porcentaje para minimo para hacer apartados</td>';
	print '<td><input type="number" class="flat" name="POS_MIN_MONTO_APARTADO" value="'. ($_POST["POS_MIN_MONTO_APARTADO"]?$_POST["POS_MIN_MONTO_APARTADO"]:$conf->global->POS_MIN_MONTO_APARTADO) . '" size="8"></td>';
	print '</tr>';


	$var=! $var;
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans("Forzar login para permisos especiales");
	print '<td colspan="2">';
	print $html->selectyesno("POS_FORCE_LOGIN",$conf->global->POS_FORCE_LOGIN,1);
	print "</td></tr>\n";


	$var=! $var;
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans("Condicion de pago para facturas de contado");
	print '<td colspan="2">';
	$form->select_conditions_paiements(isset($_POST['POS_COND_REGLEMENT_ID_CASH']) ? $_POST['POS_COND_REGLEMENT_ID_CASH'] : $conf->global->POS_COND_REGLEMENT_ID_CASH, 'POS_COND_REGLEMENT_ID_CASH');
	print "</td></tr>\n";

	
	$var=! $var;
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans("Condicion de pago para facturas a credito");
	print '<td colspan="2">';
	$form->select_conditions_paiements(isset($_POST['POS_COND_REGLEMENT_ID_CREDIT']) ? $_POST['POS_COND_REGLEMENT_ID_CREDIT'] : $conf->global->POS_COND_REGLEMENT_ID_CREDIT, 'POS_COND_REGLEMENT_ID_CREDIT');
	print "</td></tr>\n";

	
	$var=! $var;
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans("Bodega para apartados");
	print '<td colspan="2">';
	print '<select name="POS_APARTADO_ENTREPOT" id="POS_APARTADO_ENTREPOT">';
	print '<option value="-1"></option>';
	$sqlb=$db->query('SELECT rowid,ref FROM llx_entrepot');
	for ($h = 1; $h <= $db->num_rows($sqlb); $h++) {	
	$obsj = $db->fetch_object($sqlb);
	
	print '<option value="'.$obsj->rowid.'"';
	if($conf->global->POS_APARTADO_ENTREPOT==$obsj->rowid){echo 'selected="selected"';};
	print '>'.$obsj->ref.'</option>';
	}
	print '</select>';
	print "</td></tr>\n";


	$var=! $var;
	$bancos1 = select_bancos($sqs);
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans("Banco para pagos de apartados");
	print '<td colspan="2">';
	print '<select data-banco="1" class="banco browser-default custom-select" name="POS_APARTADO_BANK" id="POS_APARTADO_BANK">';
	foreach($bancos1 as $v){
		print '<option data-pago="pago1" data-currency_code="'.$v['currency_code'].'" value="'.$v['id'].'"';
	if($conf->global->POS_APARTADO_BANK==$v['id']) {print ' selected';}
	print   '>'.$v['ref'].'</option>';
	}
	print  '</select>';
	print "</td></tr>\n";
	
	
	$bancos3 = select_bancos($sqs);
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans("Banco para diferenciales");
	print '<td colspan="2">';
	print '<select data-banco="1" class="banco browser-default custom-select" name="POS_BANK_DIFERENCIAL" id="POS_BANK_DIFERENCIAL">';
	foreach($bancos3 as $v){
		print '<option data-pago="pago1" data-currency_code="'.$v['currency_code'].'" value="'.$v['id'].'"';
	if($conf->global->POS_BANK_DIFERENCIAL==$v['id']) {print ' selected';}
	print   '>'.$v['ref'].'</option>';
	}
	print  '</select>';
	print "</td></tr>\n";	
	
	
/* 	$var=! $var;
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans("POSRewards");
	if (! empty($conf->rewards->enabled))
	{
		print '<td colspan="2">';
		print $html->selectyesno("REWARDS_POS",$conf->global->REWARDS_POS,1);
	}
	else 
	{
		print '<td colspan="2">'.$langs->trans("NoRewardsInstalled").' '.$langs->trans("GetRewards","http://www.dolistore.com/search.php?orderby=position&orderway=desc&search_query=2rewards&submit_search=Buscar").'</td>';
	}
	print "</td></tr>\n"; */
	
	$var=! $var;
	print '<tr '.$bc[$var].'><td colspan="2">';
	print $langs->trans("PredefMsg").'<br>';
	print '<textarea name="POS_PREDEF_MSG" class="flat" cols="120">'.$conf->global->POS_PREDEF_MSG.'</textarea>';
	print '</td></tr>';

	print '<tr">';
	print '<td>Mostrar Totales de bancos</td>';
	print '<td>';
	print $html->selectyesno("POS_CIERRE_BANCOS",$conf->global->POS_CIERRE_BANCOS,1);
	print '</td>';
	print '</tr>';
	print '<tr">';
	print '<td>Mostrar detalle de dolares</td>';
	print '<td>';
	print $html->selectyesno("POS_CIERRE_DOLARES",$conf->global->POS_CIERRE_DOLARES,1);
	print '</td>';
	print '</tr>';
	

print '</table>';
print '<br>';

print '<center><input type="submit" class="button" value="'.$langs->trans("Save").'"></center>';

print "</form><br><hr>";


/*
* FIDELIZACION
*/
print "<h3><center>Configuracion del sistema de Fidelizacion (Sistema de puntos)</center></h3>";
$rewardsratio = dolibarr_get_const($db,'REWARDS_RATIO',$conf->entity);
$rewardsdiscount = dolibarr_get_const($db,'REWARDS_DISCOUNT',$conf->entity);
$rewardsminpay = dolibarr_get_const($db,'REWARDS_MINPAY',$conf->entity);
$addcustomer = dolibarr_get_const($db, 'REWARDS_ADD_CUSTOMER',$conf->entity);
$rewardspos = dolibarr_get_const($db,'REWARDS_POS',$conf->entity);
print '<form name="rewardssetup" action="'.$_SERVER['PHP_SELF'].'" method="post">';

print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td width="40%">'.$langs->trans('Parameter').'</td>';
print '<td>'.$langs->trans('Value').'</td>';
print '<td>'.$langs->trans('Examples').'</td>';
print '</tr>';

//Ratio
$var=!$var;
print '<tr '.$bc[$var?1:0].'>';
print '<td>'.$langs->trans('SetupRatio').'</td>';
print '<td><input type="text" class="flat" name="RewardsRatio" value="'. ($_POST["RewardsRatio"]?$_POST["RewardsRatio"]:$rewardsratio) . '" size="5"> '.$langs->trans("Currency".$conf->currency).'='.$langs->trans("SetupInfoRatio").'</td>';
print '<td>10</td>';
print '</tr>';

//Discount
$var=!$var;
print '<tr '.$bc[$var?1:0].'>';
print '<td>'.$langs->trans("SetupDiscount").'</td>';
print '<td><input type="text" class="flat" name="RewardsDiscount" value="'. ($_POST["RewardsDiscount"]?$_POST["RewardsDiscount"]:$rewardsdiscount) . '" size="5"> '.$langs->trans("Currency".$conf->currency).' '.$langs->trans("SetupInfoDiscount").'</td>';
print '<td>0.5</td>';
print '</tr>';

//Minimal payment
$var=!$var;
print '<tr '.$bc[$var?1:0].'>';
print '<td>'.$langs->trans("MinimalPayment").'</td>';
print '<td><input type="text" class="flat" name="RewardsMinPay" value="'. ($_POST["RewardsMinPay"]?$_POST["RewardsMinPay"]:$rewardsminpay) . '" size="5"> '.$langs->trans("Currency".$conf->currency).' '.$langs->trans("SetupInfoMinPay").'</td>';
print '<td>50</td>';
print '</tr>';

//Add customers automatically
$var=!$var;
print '<tr '.$bc[$var?1:0].'>';
print '<td>'.$langs->trans("AddCustomerAutomatically").'</td>';
print '<td>';
print $html->selectyesno("RewardsAddCustomer",$addcustomer,1);
print '</td>';
print '<td></td>';
print '</tr>';

print '</table>';

print '<br><center>';
print '<input type="submit" name="save" class="button" value="'.$langs->trans("Save").'">';
print '</center>';
print "</form>\n";

print '<br>';

/* clearstatcache();	
dol_htmloutput_events(); */
print '<br>';

print "<br><hr>";


/**Gestiones de la nueva tabla de los bancos */
print '<center style="margin-top: 5%;margin-bottom:5%; text-aligh:center;"><h1>Información de los bancos</h1></center>';
print '<form action="pos.php" method="POST">';
print '<input type="hidden" name="action" value="procesarbanco" />';
print '<table class="noborder" width="100%">';
print '<thead class="border border-light thead-dark">';
print '<tr style="background-color: blue; color:aliceblue;">';
print '<th scope="col">Nombre</th>';
print '<th scope="col">Tipo</th>';
print '<th scope="col">Moneda</th>';
print '<th scope="col">Comision 1</th>';
print '<th scope="col">Comision 2</th>';
print '<th scope="col">Comision 3</th>';
print '</tr>';
print '</thead>';
print '<tbody>';

require_once('../frontend/include/funciones.php');//importacion solo las funciones de la tabla llx_banco_comision

/**Validacion para poder subir la informacion a la base de datos con todas las actualizaciones de los bancos */
if($_POST['action'] == 'procesarbanco'){
	$rowref = count($_POST['ref']);
	for($i=0;$i<=$rowref;$i++){
		insertar_llx_banco_comision($_POST['ref'][$i],$_POST['label'][$i],$_POST['currency_code'][$i],price2num($_POST['comision1'][$i]),price2num($_POST['comision2'][$i]),price2num($_POST['comision3'][$i]),$_POST['fk_bank'][$i]);
	}
	//setEventMessages('Bancos actualizados',null);
}
$informacion = select_bancos();//funcion que retorna todos los bancos de la base de datos

foreach($informacion as $row){
	$comision = get_comision($row['id']);	
	print '<tr style="text-align: center;">';
	print '<input type="hidden" name="ref[]" style="width:50%;margin:auto;" value="'.$row['ref'].'"/>';
	print '<input type="hidden" name="label[]" style="width:50%;margin:auto;" value="'.$row['label'].'"/>';
	print '<input type="hidden" name="currency_code[]" style="width:50%;margin:auto;" value="'.$row['currency_code'].'"/>';
	print '<td>'.$row['ref'].'</td>';
	print '<td>'.$row['label'].'</td>';
	print '<td>'.$row['currency_code'].'</td>';
	print '<td><input type="text" name="comision1[]" style="width:50%;margin:auto;" value="'.price($comision['comision1']).'"/></td>';
	print '<td><input type="text" name="comision2[]" style="width:50%;margin:auto;" value="'.price($comision['comision2']).'"/></td>';
	print '<td><input type="text" name="comision3[]" style="width:50%;margin:auto;" value="'.price($comision['comision3']).'"/></td>';
	print '</tr>';
	print '<input type="hidden" name="fk_bank[]" value="'.$row['id'].'"/>';
	
}


print '<tr>';
print '<td colspan="2"></td>';
print '<td colspan="2" style="margin:auto;text-align: center;"><input type="submit" name="procesarbanco" class="button" value="Guardar" style="margin: 10px; paddig:40px;border-radius: 10px;"/></td>';
print '<td colspan="2></td>';
print '</tr>';
print '</form>';
print '</tbody>';
print '</table>';
print '</form>';
//dol_htmloutput_mesg($mesg);

$db->close();

llxFooter();
?>
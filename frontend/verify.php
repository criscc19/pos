<?php
/* Copyright (C) 2007-2008 Jeremie Ollivier    <jeremie.o@laposte.net>
 * Copyright (C) 2008-2010 Laurent Destailleur <eldy@uers.sourceforge.net>
 * Copyright (C) 2011	   Juanjo Menent	   <jmenent@2byte.es>
 * Copyright (C) 2012-2013 Ferran Marcet	   <fmarcet@2byte.es>
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
 *
 * This page is called after submission of login page.
 * We set here login choices into session.
 */

$res=@include("../../main.inc.php");                                   // For root directory
if (! $res) $res=@include("../../../main.inc.php");                // For "custom" directory
//require_once(DOL_DOCUMENT_ROOT.'/pos/include/environnement.php');
dol_include_once('/pos/frontend/class/auth.class.php');

global $user;

$langs->load("main");
$langs->load("admin");
$langs->load("cashdesk");

$username = GETPOST("txtUsername");
$password = GETPOST("pwdPassword");
$terminalid = GETPOST("txtTerminal");
$tipo = GETPOST("tipo");
$moneda = GETPOST("moneda");
if(isset($_POST['sbmtBackend']))
{
	if($user->rights->pos->backend)
	{
		header('Location:'.dol_buildpath('/pos/backend/listefac.php',1));
	}
	else 
	{	
		header ('Location: '.DOL_URL_ROOT);
	}
	exit;
}
// Check username
if (empty($username))
{
	$retour=$langs->trans("ErrorFieldRequired",$langs->transnoentities("Login"));
	header ('Location: '.dol_buildpath('/pos/frontend/index.php',1).'?err='.urlencode($retour).'&user='.$username.'&terminal='.$terminalid.'&moneda='.$moneda.'&tipo='.$tipo.'');
	exit;
}
// Check third party id
if (! ($terminalid > 0))
{
    $retour=$langs->trans("ErrorFieldRequired",$langs->transnoentities("CashDeskForSell"));
    header ('Location: '.dol_buildpath('/pos/frontend/index.php',1).'?err='.urlencode($retour).'&user='.$username.'&terminal='.$terminalid.'&moneda='.$moneda.'&tipo='.$tipo.'');
    exit;
}


// Check password
$auth = new Auth($db);
$retour = $auth->verif ($username, $password);

if ( $retour >= 0 )
{
	$return=array();

	$sql = "SELECT rowid, lastname, firstname";
	$sql.= " FROM ".MAIN_DB_PREFIX."user";
	$sql.= " WHERE login = '".$username."'";
	$sql.= " AND entity IN (0,".$conf->entity.")";

	$result = $db->query($sql);

	if ($result)
	{
		$tab = $db->fetch_array($res);

		foreach ( $tab as $key => $value )
		{
			$return[$key] = $value;
		}

		$_SESSION['uid'] = $tab['rowid'];
		$_SESSION['login'] = $username;
		$_SESSION['lastname'] = $tab['lastname'];
		$_SESSION['firstname'] = $tab['firstname'];
		$_SESSION['TERMINAL_ID'] = $terminalid;
		$_SESSION['TIPO_DOC'] = $tipo;	
		$_SESSION['MULTICURRENCY_CODE'] = $moneda;			
		dol_include_once('/pos/backend/class/cash.class.php');
		
		$terminal = new Cash($db);
		$terminal->fetch($terminalid);
		$userstatic=new User($db);
		$userstatic->fetch($_SESSION['uid']);
		$terminal->set_used($userstatic);			
		//require_once DOL_DOCUMENT_ROOT.'/pos/frontend/productos_json/generar_json.php';
       if($userstatic->array_options['options_terminal'] == ''){
		$retour=$langs->trans("No esta autorizado para usar esta terminal");
		header ('Location: '.dol_buildpath('/pos/frontend/index.php',1).'?err='.urlencode($retour).'&user='.$username.'&terminal='.$terminalid.'&moneda='.$moneda.'&tipo='.$tipo.'');exit;	   
	   }

		$terminales = explode(',',$userstatic->array_options['options_terminal']);
       if(!in_array($terminalid,$terminales)){
		$retour=$langs->trans("No esta autorizado para usar esta terminal");
		header ('Location: '.dol_buildpath('/pos/frontend/index.php',1).'?err='.urlencode($retour).'&user='.$username.'&terminal='.$terminalid.'&moneda='.$moneda.'&tipo='.$tipo.'');exit;		   
	   }
		if($terminal->is_closed)
		{
			$terminal->set_open($userstatic);
		}
		//var_dump($_SESSION);exit;

		if($terminal->tactil==2)
		{
			if(file_exists(dol_buildpath('/pos/frontend/movil.php'))){
				header ('Location: '.dol_buildpath('/pos/frontend/movil.php',1));
			}
			else{
				header ('Location: '.dol_buildpath('/pos/frontend/tpv.php',1));
			}
		}
		else 
		{
			header ('Location: '.dol_buildpath('/pos/frontend/tpv.php',1));
		}
		exit;
	}
	else
	{
		dol_print_error($db);
	}

}
else
{
	$langs->load("errors");
    $langs->load("other");
	$retour=$langs->trans("ErrorBadLoginPassword");
	header ('Location: '.dol_buildpath('/pos/frontend/index.php',1).'?err='.urlencode($retour).'&user='.$username.'&terminal='.$terminalid.'&moneda='.$moneda.'&tipo='.$tipo.'');
	exit;
}

?>
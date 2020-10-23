<?php
/* Copyright (C) 2011-2012 		Juanjo Menent <jmenent@2byte.es>
 * Copyright (C) 2012-2013 		Ferran Marcet <fmarcet@2byte.es>
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
 *	    \file       htdocs/pos/backend/terminal/fiche.php
 *      \ingroup    pos
 *		\brief      Page to create/view a cash
 *		\version    $Id: fiche.php,v 1.6 2011-08-19 07:54:24 jmenent Exp $
 */

$res=@include("../../../main.inc.php");                                   // For root directory
if (! $res) $res=@include("../../../../main.inc.php");                // For "custom" directory

require_once(DOL_DOCUMENT_ROOT."/core/class/html.formcompany.class.php");
dol_include_once('/pos/backend/class/cash.class.php');
dol_include_once('/pos/backend/lib/cash.lib.php');
dol_include_once('/pos/frontend/include/funciones.php');
if ($conf->banque->enabled) require_once(DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php');

if ($conf->stock->enabled) 
{
	require_once(DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php');
	require_once(DOL_DOCUMENT_ROOT."/product/class/html.formproduct.class.php");
}

$langs->load("pos@pos");
$langs->load('bills');

$action=GETPOST("action");

// Security check

$id=GETPOST('id','int');
$ref=GETPOST('ref','string');

$filtrecash='courant=2';
$filtrebank='courant=1';

if ($user->societe_id) $socid=$user->societe_id;
//$result=restrictedArea($user,'pos',$id,'pos_cash','','','rowid');



/*
 * Actions
 */
if ($_POST["action"] == 'add')
{
    $error=0;

    // Create account
    $cash = new Cash($db);

    $cash->code				= trim(GETPOST('code'));
    $cash->name 			= trim(GETPOST('name'));
    $cash->tactil			= GETPOST('tactil');
    $cash->barcode			= GETPOST('barcode');
	$cash->fk_user_u 		= GETPOST('user');
	$cash->fk_paycash 		= GETPOST('cash');
	$cash->fk_modepaycash 	= GETPOST('modecash');
	$cash->fk_paybank 		= GETPOST('bank');
	$cash->fk_paybank_extra 		= GETPOST('bank_extra');
	$cash->fk_modepaybank 	= GETPOST('modebank');
	$cash->fk_modepaybank_extra 	= GETPOST('modebank_extra');
	$cash->fk_warehouse		= GETPOST('stock');
	$cash->fk_soc			= GETPOST('soc');
	if(count($_POST['bank_active']) > 0){
	$cash->bank_active	 = json_encode($_POST['bank_active']);		
	}else{$cash->bank_active	 = json_encode(array(0));}


    //$cash->fk_device		= GETPOST('device');
    
    if (empty($cash->code))
    {
        $message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("LabelCode")).'</div>';
        $action='create';       // Force chargement page en mode creation
        $error++;
    }
    
 	if (empty($cash->name))
    {
        $message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("LabelName")).'</div>';
        $action='create';       // Force chargement page en mode creation
        $error++;
    }
    
    if (empty($cash->fk_modepaycash ))
    {
    	$message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("ModePaymentCash")).'</div>';
    	$action='create';       // Force chargement page en mode creation
    	$error++;
    }
    
    if ($cash->fk_modepaybank <= 0)
    {
    	$message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("ModePaymentBank")).'</div>';
    	$action='create';       // Force chargement page en mode creation
    	$error++;
    }
    
    if ($cash->fk_paybank_extra && ! $cash->fk_modepaybank_extra)
    {
    	$message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("ModePaymentBankExtra")).'</div>';
    	$action='create';       // Force chargement page en mode creation
    	$error++;
    }
    
	if ($cash->fk_warehouse <= 0 && $conf->stock->enabled)
    {
        $message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("CashDeskIdWareHouse")).'</div>';
        $action='create';       // Force chargement page en mode creation
        $error++;
    }

    if ($cash->fk_soc <= 0)
    {
    	$message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("CashDeskThirdPartyForSell")).'</div>';
    	$action='create';       // Force chargement page en mode creation
    	$error++;
    }

    if (! $error)
    {
        $id = $cash->create($user);
        if ($id == 0)
        {
           $url=dol_buildpath("/pos/backend/terminal/cash.php",1);
           Header("Location: ".$url);
        }
        else
        {
            $message='<div class="error">'.$cash->error.'</div>';
            $action='create';   // Force chargement page en mode creation
        }
    }
}

if (GETPOST('action','string') == 'update' && ! GETPOST('cancel'))
{
    $error=0;
	$cash = new Cash($db);
	$cash->fetch(GETPOST('id'));
    // Update account

 	$cash->code				= trim(GETPOST('code'));
    $cash->name 			= trim(GETPOST('name'));
    $cash->tactil			= GETPOST('tactil');
    $cash->barcode			= GETPOST('barcode');
	$cash->fk_paycash 		= GETPOST('cash');
	$cash->fk_modepaycash 	= GETPOST('modecash');
	$cash->fk_paybank 		= GETPOST('bank');
	$cash->fk_paybank_extra 		= GETPOST('bank_extra');
	$cash->fk_modepaybank 	= GETPOST('modebank');
	$cash->fk_modepaybank_extra 	= GETPOST('modebank_extra');
	$cash->fk_warehouse 	= GETPOST('stock');
   // $cash->fk_device		= GETPOST('device');
	$cash->fk_soc			= GETPOST('soc');
//var_dump(count($_POST['bank_active']));exit;
	if(count($_POST['bank_active']) > 0){
		$cash->bank_active	 = json_encode($_POST['bank_active']);		
		}else{$cash->bank_active	 = json_encode([0]);}

    if (empty($cash->code))
    {
        $message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("LabelCode")).'</div>';
        $action='edit';       // Force chargement page en mode creation
        $error++;
    }
    
 	if (empty($cash->name))
    {
        $message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("LabelName")).'</div>';
        $action='edit';       // Force chargement page en mode creation
        $error++;
    }
    
	if (empty($cash->fk_modepaycash ))
    {
    	$message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("ModePaymentCash")).'</div>';
    	$action='edit';       // Force chargement page en mode creation
    	$error++;
    }

    if ($cash->fk_modepaybank <= 0)
    {
    	$message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("ModePaymentBank")).'</div>';
    	$action='edit';       // Force chargement page en mode creation
    	$error++;
    }

	if ($cash->fk_warehouse <= 0 && $conf->stock->enabled)
    {
        $message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("CashDeskIdWareHouse")).'</div>';
        $action='edit';       // Force chargement page en mode creation
        $error++;
    }

    if ($cash->fk_soc <= 0)
    {
    	$message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("CashDeskThirdPartyForSell")).'</div>';
    	$action='edit';       // Force chargement page en mode creation
    	$error++;
    }
    
    if ($cash->fk_paybank_extra && ! $cash->fk_modepaybank_extra)
    {
    	$message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("ModePaymentBankExtra")).'</div>';
    	$action='create';       // Force chargement page en mode creation
    	$error++;
    }
{
    if (! $error)
   
        $result = $cash->update($user);
        if ($result >= 0)
        {
            $_GET["id"]=$_POST["id"];   // Force chargement page en mode visu
        }
        else
        {
            $message='<div class="error">'.$cash->error.'</div>';
            $action='edit';     // Force chargement page edition
        }
	}
    //header("Location: ".dol_buildpath("/pos/backend/terminal/fiche.php?id=".$cash->id."",1));

}

if (GETPOST('action','string') == 'confirm_delete' && GETPOST('confirm','string') == "yes" && $user->rights->pos->backend)
{
    // Modification
    $cash = new Cash($db);
    $cash->delete(GETPOST('id'));

    //header("Location: ".DOL_URL_ROOT."/pos/backend/terminal/cash.php");
    header("Location: ".dol_buildpath("/pos/backend/terminal/cash.php",1));
    exit;
}


/*
 * View
 */

$countrynotdefined=$langs->trans("ErrorSetACountryFirst").' ('.$langs->trans("SeeAbove").')';
$helpurl='EN:Module_DoliPos|FR:Module_DoliPos_FR|ES:M&oacute;dulo_DoliPos';
llxHeader('','',$helpurl);

$form = new Form($db);
$htmlcompany = new FormCompany($db);
if ($conf->stock->enabled)
	$formproduct=new FormProduct($db);

/* ************************************************************************** */
/*                                                                            */
/* Affichage page en mode creation                                            */
/*                                                                            */
/* ************************************************************************** */

if ($action == 'create')
{
	$cash=new Cash($db);

	print_fiche_titre($langs->trans("NewCash"));

	if ($message) { dol_htmloutput_mesg($message); }

	print '<form action="'.$_SERVER["PHP_SELF"].'" name="formsoc" method="post">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="clos" value="0">';

	print '<table class="border" width="100%">';

	// Code
	print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("Code").'</td>';
	print '<td colspan="3"><input size="8" type="text" class="flat" name="code" value="'.(GETPOST('code')?GETPOST('code'):$cash->code).'"></td></tr>';

	// Name
	print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("Name").'</td>';
	print '<td colspan="3"><input size="30" type="text" class="flat" name="name" value="'.GETPOST('name','string').'"></td></tr>';


	//Tactil
	print '<tr>';
	print '<td><span class="fieldrequired">'.$langs->trans('Type').'</span></td><td>';
	print $cash->selecttypeterminal("tactil",isset($_POST["tactil"])?$_POST["tactil"]:0,1);
	print '</td>';
	print '</tr>';

	//Barcode
	print '<tr>';
	print '<td><span class="fieldrequired">'.$langs->trans('Barcode').'</span></td><td>';
	print $form->selectyesno("barcode",isset($_POST["barcode"])?$_POST["barcode"]:0,1);
	print '</td>';
	print '</tr>';

	// Cash
	print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("PaymentCash").'</td>';
	print '<td colspan="3">';
	print $form->select_comptes(isset($_POST["cash"])?$_POST["cash"]:1,'cash',0, $filtrecash);
	print '</td></tr>';

	// Mode payment Cash
	print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("ModePaymentCash").'</td>';
	print '<td colspan="3">';
	print $form->select_types_paiements(isset($_POST["modecash"])?$_POST["modecash"]:0,'modecash','',0);
	print '</td></tr>';

	// Bank
	print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("PaymentBank").'</td>';
	print '<td colspan="3">';
	print $form->select_comptes(isset($_POST["bank"])?$_POST["bank"]:1,'bank',0, $filtrebank);
	print '</td></tr>';

	// Mode payment Bank
	print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("ModePaymentBank").'</td>';
	print '<td colspan="3">';
	print $form->select_types_paiements(isset($_POST["modebank"])?$_POST["modebank"]:0,'modebank','', 0);
	print '</td></tr>';

	// Bank_extra
	print '<tr><td valign="top" >'.$langs->trans("PaymentBankExtra").'</td>';
	print '<td colspan="3">';
	print $form->select_comptes(isset($_POST["bank_extra"])?$_POST["bank_extra"]:1,'bank_extra',0, $filtrebank);
	print '</td></tr>';

	// Mode payment Bank
	print '<tr><td valign="top" >'.$langs->trans("ModePaymentBankExtra").'</td>';
	print '<td colspan="3">';
	print $form->select_types_paiements(isset($_POST["modebank_extra"])?$_POST["modebank_extra"]:0,'modebank_extra','', 0);
	print '</td></tr>';

	if ($conf->stock->enabled)
	{
		//Stock
		print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("CashDeskIdWareHouse").'</td>';
		print '<td colspan="2">';
		print $formproduct->selectWarehouses(isset($_POST["stock"])?$_POST["stock"]:1,'stock','',1);
		print '</td></tr>';
	}

	//Soc
	print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("CashDeskThirdPartyForSell").'</td>';
	print '<td colspan="3">';
	print $form->select_company(isset($_POST["soc"])?$_POST["soc"]:1,'soc','s.client=1 or s.client=3',1,1);
	print '<script>
	$( document ).ready(function() {
		$(".select2-container").attr("style","width:600px");
	});
	
	</script>';
	print '</td></tr>';


		//BANK

		print '<tr><td>'.$langs->trans("Bancos activos").'</td>';
		print '<td>';
		$bancos = select_bancos();
		print '<ul>';
		foreach($bancos as $v){
			if(in_array($v['id'],$b_active)){
				$ac = 'checked';	
				}else{
				$ac = '';	
				}		
		print '<li>';
		print ''.$v['label'].' <input type="checkbox" name="bank_active[]" value="'.$v['id'].'" '.$ac.'>';		
		print '</li>';					
		}
		print '</ul>';		
		print '</td></tr>';		

	print '<tr><td align="center" colspan="4"><input value="'.$langs->trans("CreateCash").'" type="submit" class="button"></td></tr>';
	print '</form>';
	print '</table>';
}
/* ************************************************************************** */
/*                                                                            */
/* Visu et edition                                                            */
/*                                                                            */
/* ************************************************************************** */
else
{
    if (($id || $ref) && $action != 'edit')
	{
		$cash = new Cash($db);

		$cash->fetch($id, $ref);
		$b_active = json_decode($cash->bank_active);
		
		/*
		* Affichage onglets
		*/

		// Onglets
		$head=cash_prepare_head($cash);
		dol_fiche_head($head, 'cashname', $langs->trans("Cash"),0,'barcode');

		/*
		* Confirmation to delete
		*/
		if ($action == 'delete')
		{
			$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?id='.$cash->id,$langs->trans("DeleteCash"),$langs->trans("ConfirmDeleteCash"),"confirm_delete",'','',1);
			if ($ret == 'html') print '<br>';
		}

		print '<table class="border" width="100%">';

		// Code
		print '<tr><td valign="top" width="25%">'.$langs->trans("Code").'</td>';
		print '<td colspan="3">';
		print $form->showrefnav($cash,'ref','',1,'name','ref');
		print '</td></tr>';

		// Name
		print '<tr><td valign="top">'.$langs->trans("Name").'</td>';
		print '<td colspan="3">'.$cash->name.'</td></tr>';

		//Tactil
		print '<tr><td>';
        print $langs->trans('Type');
        print '</td><td>';
        print $cash->tactiltype($cash->tactil);
        print '</td></tr>';

        //Barcode
        print '<tr><td>';
        print $langs->trans('Barcode');
        print '</td><td>';
        print yn($cash->barcode);
        print '</td></tr>';

		// Cash
		if ($conf->banque->enabled)
		{
    		if ($cash->fk_paycash)
    		{
    			$bankline=new Account($db);
    			$bankline->fetch($cash->fk_paycash);

    			print '<tr>';
    			print '<td>'.$langs->trans('PaymentCash').'</td>';
				print '<td colspan="3">';
				print $bankline->getNomUrl(1);
    			print '</td>';
    			print '</tr>';

    			// Payment mode
    			print '<tr>';
    			print '<td>'.$langs->trans('ModePaymentCash').'</td>';
				print '<td colspan="3">';
				$form->form_modes_reglement($_SERVER['PHP_SELF'].'?id='.$cash->id,$cash->fk_modepaycash,'none');
				print "</td>";
				print '</tr>';
    		}
    		else
    		{
    			print '<tr>';
    			print '<td>'.$langs->trans('PaymentCash').'</td>';
				print '<td colspan="3">';
				//print $bankline->getNomUrl(1,0,'showall');
    			print '</td>';
    			print '</tr>';
    		}
		}

		// Bank
		if ($conf->banque->enabled)
		{
    		if ($cash->fk_paycash)
    		{
    			$bankline=new Account($db);
    			$bankline->fetch($cash->fk_paybank);

    			print '<tr>';
    			print '<td>'.$langs->trans('PaymentBank').'</td>';
				print '<td colspan="3">';
				print $bankline->getNomUrl(1);
    			print '</td>';
    			print '</tr>';

    			// Payment mode
    			print '<tr>';
    			print '<td>'.$langs->trans('ModePaymentBank').'</td>';
				print '<td colspan="3">';
				$form->form_modes_reglement($_SERVER['PHP_SELF'].'?id='.$cash->id,$cash->fk_modepaybank,'none');
				print "</td>";
				print '</tr>';
    		}
    		else
    		{
    			print '<tr>';
    			print '<td>'.$langs->trans('PaymentBank').'</td>';
				print '<td colspan="3">';
				//print $bankline->getNomUrl(1,0,'showall');
    			print '</td>';
    			print '</tr>';

    		}
		}

		// Bank_extra
		if ($conf->banque->enabled)
		{
			if ($cash->fk_paycash)
			{
				$bankline_extra=new Account($db);
				$bankline_extra->fetch($cash->fk_paybank_extra);

				print '<tr>';
				print '<td>'.$langs->trans('PaymentBankExtra').'</td>';
				print '<td colspan="3">';
				print $bankline_extra->getNomUrl(1);
				print '</td>';
				print '</tr>';

				// Payment mode
				print '<tr>';
				print '<td>'.$langs->trans('ModePaymentBankExtra').'</td>';
				print '<td colspan="3">';
				$form->form_modes_reglement($_SERVER['PHP_SELF'].'?id='.$cash->id,$cash->fk_modepaybank_extra,'none');
				print "</td>";
				print '</tr>';
			}
			else
			{
				print '<tr>';
				print '<td>'.$langs->trans('PaymentBankExtra').'</td>';
				print '<td colspan="3">';
				//print $bankline->getNomUrl(1,0,'showall');
				print '</td>';
				print '</tr>';

			}
		}

		//Stock
		if ($conf->stock->enabled)
		{
			$stock= new Entrepot($db);
			$stock->fetch($cash->fk_warehouse);
			print '<tr><td>'.$langs->trans("CashDeskIdWareHouse").'</td>';
			print '<td colspan="3">';		
			print '<a href="'.DOL_URL_ROOT.'/product/stock/fiche.php?id='.$stock->id.'">'.img_object($langs->trans("ShowWarehouse"),'stock').' '.$stock->libelle.'</a>';
			print '</td></tr>';
		}

		//Soc
		$soc = new Societe($db, $cash->fk_soc);
		$soc->fetch( $cash->fk_soc);
		print '<tr><td>'.$langs->trans("CashDeskThirdPartyForSell").'</td>';
		print '<td>';
		print $soc->getNomUrl(1,'compta');

		print '</td></tr>';


		//BANK
		print '<tr><td>'.$langs->trans("Bancos activos").'</td>';
		print '<td>';
		$bancos = select_bancos();
		print '<ul>';
		foreach($bancos as $v){
			if(in_array($v['id'],$b_active)){
				$ac = 'checked';	
				}else{
				$ac = '';	
				}		
		print '<li>';
		print ' '.$v['label'].' <input disabled type="checkbox" name="bank_active[]" value="'.$v['id'].'" '.$ac.'>';		
		print '</li>';					
		}
		print '</ul>';		
		print '</td></tr>';		


		print '</table>';

		print '</div>';


		/*
		 * Barre d'actions
		 */
		print '<div class="tabsAction">';

		if ($user->rights->pos->backend)
		{
			print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit&id='.$cash->id.'">'.$langs->trans("Modify").'</a>';
		}

		$canbedeleted=$cash->can_be_deleted();   // Renvoi vrai si compte sans mouvements
		if ($user->rights->pos->backend && $canbedeleted)
		{
			print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?action=delete&id='.$cash->id.'">'.$langs->trans("Delete").'</a>';
		}

		print '</div>';

	}

    /* ************************************************************************** */
    /*                                                                            */
    /* Edition                                                                    */
    /*                                                                            */
    /* ************************************************************************** */

    if (GETPOST("id") && $action == 'edit' && $user->rights->pos->backend)
    {
        $cash = new Cash($db);
        $cash->fetch(GETPOST("id"));
		$b_active = json_decode($cash->bank_active);

        print_fiche_titre($langs->trans("EditCash"));
        print "<br>";

   		if ($message) { dol_htmloutput_mesg($message); }

        print '<form action="'.$_SERVER["PHP_SELF"].'?id='.$cash->id.'" method="post" name="formsoc">';
        print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
        print '<input type="hidden" name="action" value="update">';
        print '<input type="hidden" name="id" value="'.$_REQUEST["id"].'">'."\n\n";

        print '<table class="border" width="100%">';


        // Code
		print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("Code").'</td>';
		print '<td colspan="3"><input size="3" type="text" class="flat" name="code" value="'.$cash->code.'"></td></tr>';

		// Name
		print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("Name").'</td>';
		print '<td colspan="3"><input size="30" type="text" class="flat" name="name" value="'.$cash->name.'"></td></tr>';

		//Tactil
		print '<tr><td class="fieldrequired">'.$langs->trans('Type').'</td>';
		print '<td>'.$cash->selecttypeterminal("tactil",$cash->tactil,1).'</td></tr>';

		//Barcode
		print '<tr><td class="fieldrequired">'.$langs->trans('Barcode').'</td>';
		print '<td>'.$form->selectyesno("barcode",$cash->barcode,1).'</td></tr>';

		if ($conf->banque->enabled)
		{
			// Cash
			print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("PaymentCash").'</td>';
			print '<td colspan="3">';
			 $form->select_comptes($cash->fk_paycash,'cash');
			print '</td></tr>';

			//Mode Cash Payment
			print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("ModePaymentCash").'</td>';
			print '<td colspan="3">';
			 $form->select_types_paiements($cash->fk_modepaycash?$cash->fk_modepaycash:1,'modecash','',0);
			print '</td></tr>';

			// Bank
			print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("PaymentBank").'</td>';
			print '<td colspan="3">';
			 $form->select_comptes($cash->fk_paybank,'bank',0, $filtrebank);
			print '</td></tr>';

			//Mode Bank Payment
			print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("ModePaymentBank").'</td>';
			print '<td colspan="3">';
			 $form->select_types_paiements($cash->fk_modepaybank?$cash->fk_modepaybank:1,'modebank','',0);
			print '</td></tr>';

			// Bank Extra
			print '<tr><td valign="top" >'.$langs->trans("PaymentBankExtra").'</td>';
			print '<td colspan="3">';
			 $form->select_comptes($cash->fk_paybank_extra,'bank_extra',0, $filtrebank);
			print '</td></tr>';

			//Mode Bank Payment Extra
			print '<tr><td valign="top" >'.$langs->trans("ModePaymentBankExtra").'</td>';
			print '<td colspan="3">';
			 $form->select_types_paiements($cash->fk_modepaybank_extra?$cash->fk_modepaybank_extra:1,'modebank_extra','',0);
			print '</td></tr>';
		}

     	//Stock
		if ($conf->stock->enabled)
		{

			print '<tr '.$bc[$var].'><td class="fieldrequired">'.$langs->trans("CashDeskIdWareHouse").'</td>';
			print '<td colspan="2">';
			print $formproduct->selectWarehouses($cash->fk_warehouse,'stock','',1);
			print '</td></tr>';
		}

		//Soc
		print '<tr><td valign="top" class="fieldrequired">'.$langs->trans("CashDeskThirdPartyForSell").'</td>';
		print '<td colspan="3">';
		print $form->select_company($cash->fk_soc,'soc','s.client=1 or s.client=3',1,1);
		print '</td></tr>';

		//BANK
		$soc = new Societe($db, $cash->fk_soc);
		$soc->fetch( $cash->fk_soc);
		print '<tr><td>'.$langs->trans("Bancos activos").'</td>';
		print '<td>';
		$bancos = select_bancos();
		print '<ul>';
		foreach($bancos as $v){
			
			if(in_array($v['id'],$b_active)){
			$ac = 'checked';	
			}else{
			$ac = '';	
			}	
		print '<li>';
		print ''.$v['label'].' <input type="checkbox" name="bank_active[]" value="'.$v['id'].'" '.$ac.'>';		
		print '</li>';					
		}
		print '</ul>';		
		print '</td></tr>';		


        print '<tr><td align="center" colspan="4"><input value="'.$langs->trans("Modify").'" type="submit" class="button">';
        print ' &nbsp; <input name="cancel" value="'.$langs->trans("Cancel").'" type="submit" class="button">';
        print '</td></tr>';

        print '</table>';

        print '</form>';
	}

}

llxFooter();

$db->close();
?>
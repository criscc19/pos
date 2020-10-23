<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2011 Regis Houssin        <regis@dolibarr.fr>
 * Copyright (C) 2011-2102 Juanjo Menent        <jmenent@2bye.es>
 * Copyright (C) 2012-2014 Ferran Marcet        <fmarcet@2byte.es>
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
 * 		\defgroup   mymodule     Module MyModule
 *      \brief      Example of a module descriptor.
 *					Such a file must be copied into htdocs/includes/module directory.
 */

/**
 *      \file       htdocs/includes/modules/modMyModule.class.php
 *      \ingroup    mymodule
 *      \brief      Description and activation file for module MyModule
 *		\version	$Id: modPos.class.php,v 1.8 2011-08-18 10:30:29 jmenent Exp $
 */
include_once(DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php");


/**
 * 		\class      modMyModule
 *      \brief      Description and activation class for module MyModule
 */
class modPos extends DolibarrModules
{
	/**
	 *   \brief      Constructor. Define names, constants, directories, boxes, permissions
	 *   \param      DB      Database handler
	 */
	function modPos($DB)
	{
        global $langs,$conf;
		
        $this->db = $DB;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 400004;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'pos';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "<span style='color:red'>Sicla</span>";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "POS module";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '3.4.6';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='pos'.'.png@'.'pos';
		$this->editor_name = 'NG Technology';
		$this->editor_url = 'https://www.ng.cr';
        // Defined if the directory /mymodule/includes/triggers/ contains triggers or not
        $this->module_parts = array(
			'models' => 1, 'triggers' => 1, 
			'css' => array(''),
			'hooks' => array('data'=>array('globalcard'), 'entity'=>'0'), 
		    );

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array("/pos/temp");
		$r=0;

		// Relative path to module style sheet if exists. Example: '/mymodule/css/mycss.css'.
		//$this->style_sheet = '/mymodule/mymodule.css.php';

		// Config pages. Put here list of php page names stored in admmin directory used to setup module.
		$this->config_page_url = array("pos.php@pos");

		// Dependencies
		$this->depends = array("modBanque","modFacture","modProduct","modStock");		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->phpmin = array(5,0);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,2);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("pos@pos");

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('MYMODULE_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('MYMODULE_MYNEWCONST2','chaine','myvalue','This is another constant to add',0) );
		//                             2=>array('MAIN_MODULE_MYMODULE_NEEDSMARTY','chaine',1,'Constant to say module need smarty',1)
		$this->const = array();
		
		$r++;
		$this->const[$r][0] = "TICKET_ADDON";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "mod_ticket_barx";
		$this->const[$r][3] = 'Nom du gestionnaire de numerotation des tickets';
		$this->const[$r][4] = 0;

		// Array to add new pages in new tabs
		// Example: $this->tabs = array('objecttype:+tabname1:Title1:@mymodule:$user->rights->mymodule->read:/mymodule/mynewtab1.php?id=__ID__',  // To add a new tab identified by code tabname1
        //                              'objecttype:+tabname2:Title2:@mymodule:$user->rights->othermodule->read:/mymodule/mynewtab2.php?id=__ID__',  // To add another new tab identified by code tabname2
        //                              'objecttype:-tabname');                                                     // To remove an existing tab identified by code tabname
		// where objecttype can be
		// 'thirdparty'       to add a tab in third party view
		// 'intervention'     to add a tab in intervention view
		// 'order_supplier'   to add a tab in supplier order view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'invoice'          to add a tab in customer invoice view
		// 'order'            to add a tab in customer order view
		// 'product'          to add a tab in product view
		// 'stock'            to add a tab in stock view
		// 'propal'           to add a tab in propal view
		// 'member'           to add a tab in fundation member view
		// 'contract'         to add a tab in contract view
		// 'user'             to add a tab in user view
		// 'group'            to add a tab in group view
		// 'contact'          to add a tab in contact view
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
 
	
        // Array to add new pages in new tabs
        $this->tabs = array(
            'thirdparty:+pos:Rewards:pos@pos:$user->rights->pos->ad_rewards:/pos/rewards/fiche.php?socid=__ID__',
            'invoice:+pos:Rewards:pos@pos:$user->rights->pos->ad_rewards:/pos/rewards/invoice.php?facid=__ID__'
        );

        // Dictionnaries
        $this->dictionnaries=array();
        /*
        $this->dictionnaries=array(
            'langs'=>'cabinetmed@cabinetmed',
            'tabname'=>array(MAIN_DB_PREFIX."cabinetmed_diaglec",MAIN_DB_PREFIX."cabinetmed_examenprescrit",MAIN_DB_PREFIX."cabinetmed_motifcons"),
            'tablib'=>array("DiagnostiqueLesionnel","ExamenPrescrit","MotifConsultation"),
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'cabinetmed_diaglec as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'cabinetmed_examenprescrit as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'cabinetmed_motifcons as f'),
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),
            'tabfield'=>array("code,label","code,label","code,label"),
            'tabfieldvalue'=>array("code,label","code,label","code,label"),
            'tabfieldinsert'=>array("code,label","code,label","code,label"),
            'tabrowid'=>array("rowid","rowid","rowid"),
            'tabcond'=>array($conf->cabinetmed->enabled,$conf->cabinetmed->enabled,$conf->cabinetmed->enabled)
        );
        */

        // Boxes
		// Add here list of php file(s) stored in includes/boxes that contains class to show a box.
        $this->boxes = array();			// List of boxes
		$r=0;
		// Example:
		/*
		$this->boxes[$r][1] = "myboxa.php";
		$r++;
		$this->boxes[$r][1] = "myboxb.php";
		$r++;
		*/

		// Permissions
		$this->rights = array();		// Permission array used by this module
		$this->rights_class = 'pos';

		$r=0;

		$r++;
		$this->rights[$r][0] = 4000051;
		$this->rights[$r][1] = 'Use POS';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'frontend';
		
		$r++;
		$this->rights[$r][0] = 4000052;
		$this->rights[$r][1] = 'Use Backend';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'backend';
		
		$r++;
		$this->rights[$r][0] = 4000053;
		$this->rights[$r][1] = 'Make Transfers';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'transfer';
		
		/*$r++;
		$this->rights[$r][0] = 400054;
		$this->rights[$r][1] = 'Read';
		$this->rights[$r][2] = 'r';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'read';
		*/
		$r++;
		$this->rights[$r][0] = 4000055;
		$this->rights[$r][1] = 'Stats';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'stats';
		
		$r++;
		$this->rights[$r][0] = 4000056;
		$this->rights[$r][1] = 'Make Closecash';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'closecash';

		/**Creacion de las notas de credito permiso */
		$r++;
		$this->rights[$r][0] = 4000057;
		$this->rights[$r][1] = 'Crear Notas de Credito';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'crear_ndc';
		/**Creacion de editar producto */
		$r++;
		$this->rights[$r][0] = 4000058;
		$this->rights[$r][1] = 'Editar producto';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'edit_product';
		/**Creacion de  descuento global */
		$r++;
		$this->rights[$r][0] = 4000059;
		$this->rights[$r][1] = 'Descuento global';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'global_discount';
		/**Creacion de cierre de caja */
		$r++;
		$this->rights[$r][0] = 4000060;
		$this->rights[$r][1] = 'Cierre Caja';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'box_closure';
		/**Creacion de pagar factura*/
		$r++;
		$this->rights[$r][0] = 4000061;
		$this->rights[$r][1] = 'Pagar Factura';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'pay_bill';
		/**Creacion de pagar apartados*/
		$r++;
		$this->rights[$r][0] = 4000062;
		$this->rights[$r][1] = 'Pagar Apartados';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'pay_sections';
		/**Creacion de lista de facturas*/
		$r++;
		$this->rights[$r][0] = 4000063;
		$this->rights[$r][1] = 'Listado De Facturas';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'invoice_list';
		/**Creacion de lista de cotizaciones*/
		$r++;
		$this->rights[$r][0] = 4000064;
		$this->rights[$r][1] = 'Listado De Cotizaciones';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'list_of_quotes';
		/**Creacion de lista de APARTADO*/
		$r++;
		$this->rights[$r][0] = 4000065;
		$this->rights[$r][1] = 'Apartado';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'pulled_apart';
		/**Creacion de lista de cotizacion*/
		$r++;
		$this->rights[$r][0] = 4000066;
		$this->rights[$r][1] = 'Cotizacion';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'quotation';
		/**Creacion de metodos de pagos*/
		$r++;
		$this->rights[$r][0] = 4000067;
		$this->rights[$r][1] = 'Metodo De Pago';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'payment_methods';
		/**Creacion de descuento*/
		$r++;
		$this->rights[$r][0] = 4000068;
		$this->rights[$r][1] = 'Descuento';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'discount';	
		/**Facturar stock negativo*/
		$r++;
		$this->rights[$r][0] = 4000069;
		$this->rights[$r][1] = 'Facturar stock negativo';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'stock';
		/**Facturar stock negativo*/
		$r++;
		$this->rights[$r][0] = 4000070;
		$this->rights[$r][1] = 'Facturar con limite de credito sobrepasado';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'limitar';
		/**cambiar iva*/
		$r++;
		$this->rights[$r][0] = 4000071;
		$this->rights[$r][1] = 'Editar IVA';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'iva';
		

		$r++;
		$this->rights[$r][0] = 4000072;
		$this->rights[$r][1] = 'Administrar Fidelizacion';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		
		$this->rights[$r][4] = 'ad_rewards';		
		$r++;
		$this->rights[$r][0] = 4000073;
		$this->rights[$r][1] = 'Mostrar cajas de estatus del cliente';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'box';				
		// Main menu entries
		$this->menus = array();			// List of menus to add
		$r=0;
		$this->menu[$r++]=array(	'fk_menu'=>0,			// Put 0 if this is a top menu
									'type'=>'top',			// This is a Top menu entry
									'titre'=>'POS',
									'mainmenu'=>'pos',
									'leftmenu'=>'',		// Use 1 if you also want to add left menu entries using this descriptor.
									'url'=>'/pos/backend/closes.php?mainmenu=pos&main=1',
									'langs'=>'',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
									'position'=>100,
									'enabled'=>'1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
									'perms'=>'$user->rights->pos->frontend',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
									'target'=>'',
									'user'=>0);				// 0=Menu for internal users, 1=external users, 2=both

									$this->menu[$r++]=array(
									'fk_menu'=>'fk_mainmenu=pos',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
									'type'=>'left',			                // This is a Left menu entry
									'titre'=>'TPV',
									'mainmenu'=>'pos',
									'leftmenu'=>'tpv',
									//'url'=>'/reportes/credito.php',
									'url'=>'/pos/frontend/tpv.php',
									'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
									'position'=>100,
									'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
									'perms'=>'$user->rights->pos->frontend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
									'target'=>'',
									'user'=>2);	
									// 0=Menu for internal users, 1=external users, 2=both	
									
									$this->menu[$r++]=array(
									'fk_menu'=>'fk_mainmenu=pos,fk_leftmenu=tpv',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
									'type'=>'left',			                // This is a Left menu entry
									'titre'=>'Cierres y arqueos',
									'mainmenu'=>'pos',
									'leftmenu'=>'cierres',
									'url'=>'/pos/backend/closes.php?mainmenu=pos',
									'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
									'position'=>100,
									'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
									'perms'=>'$user->rights->pos->backend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
									'target'=>'',
									'user'=>2);		
																
									$this->menu[$r++]=array(
									'fk_menu'=>'fk_mainmenu=pos,fk_leftmenu=cierres',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
									'type'=>'left',			                // This is a Left menu entry
									'titre'=>'Cierres',
									'mainmenu'=>'pos',
									'leftmenu'=>'',
									'url'=>'/pos/backend/closes.php?viewstatut=1&mainmenu=pos',
									'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
									'position'=>100,
									'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
									'perms'=>'$user->rights->pos->backend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
									'target'=>'',
									'user'=>2);	

									$this->menu[$r++]=array(
									'fk_menu'=>'fk_mainmenu=pos,fk_leftmenu=cierres',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
									'type'=>'left',			                // This is a Left menu entry
									'titre'=>'Arqueos',
									'mainmenu'=>'pos',
									'leftmenu'=>'',
									'url'=>'/pos/backend/closes.php?viewstatut=0&mainmenu=pos',
									'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
									'position'=>100,
									'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
									'perms'=>'$user->rights->pos->backend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
									'target'=>'',
									'user'=>2);	



									$this->menu[$r++]=array(
										'fk_menu'=>'fk_mainmenu=pos',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
										'type'=>'left',			                // This is a Left menu entry
										'titre'=>'Terminales',
										'mainmenu'=>'pos',
										'leftmenu'=>'terminales',
										'url'=>'/pos/backend/terminal/cash.php?&mainmenu=pos',
										'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
										'position'=>100,
										'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
										'perms'=>'$user->rights->pos->backend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
										'target'=>'',
										'user'=>2);	

									$this->menu[$r++]=array(
										'fk_menu'=>'fk_mainmenu=pos,fk_leftmenu=terminales',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
										'type'=>'left',			                // This is a Left menu entry
										'titre'=>'Nuevo terminal',
										'mainmenu'=>'pos',
										'leftmenu'=>'',
										'url'=>'/pos/backend/terminal/fiche.php?action=create&mainmenu=pos',
										'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
										'position'=>100,
										'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
										'perms'=>'$user->rights->pos->backend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
										'target'=>'',
										'user'=>2);	

									$this->menu[$r++]=array(
										'fk_menu'=>'fk_mainmenu=pos,fk_leftmenu=terminales',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
										'type'=>'left',			                // This is a Left menu entry
										'titre'=>'Listado',
										'mainmenu'=>'pos',
										'leftmenu'=>'',
										'url'=>'/pos/backend/terminal/cash.php?mainmenu=pos',
										'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
										'position'=>100,
										'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
										'perms'=>'$user->rights->pos->backend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
										'target'=>'',
										'user'=>2);	
										
										
										$this->menu[$r++]=array(
											'fk_menu'=>'fk_mainmenu=pos',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
											'type'=>'left',			                // This is a Left menu entry
											'titre'=>'Puesto',
											'mainmenu'=>'pos',
											'leftmenu'=>'puesto',
											'url'=>'/pos/backend/place/place.php?mainmenu=pos',
											'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
											'position'=>100,
											'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
											'perms'=>'$user->rights->pos->backend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
											'target'=>'',
											'user'=>2);	

									$this->menu[$r++]=array(
										'fk_menu'=>'fk_mainmenu=pos,fk_leftmenu=puesto',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
										'type'=>'left',			                // This is a Left menu entry
										'titre'=>'Nuevo puesto',
										'mainmenu'=>'pos',
										'leftmenu'=>'',
										'url'=>'/pos/backend/place/fiche.php?action=create&mainmenu=pos',
										'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
										'position'=>100,
										'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
										'perms'=>'$user->rights->pos->backend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
										'target'=>'',
										'user'=>2);	
										
									$this->menu[$r++]=array(
										'fk_menu'=>'fk_mainmenu=pos,fk_leftmenu=puesto',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
										'type'=>'left',			                // This is a Left menu entry
										'titre'=>'Listado',
										'mainmenu'=>'pos',
										'leftmenu'=>'',
										'url'=>'/pos/backend/place/place.php?mainmenu=pos?listado=1',
										'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
										'position'=>100,
										'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
										'perms'=>'$user->rights->pos->backend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
										'target'=>'',
										'user'=>2);
										
										

									$this->menu[$r++]=array(
										'fk_menu'=>'fk_mainmenu=pos',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
										'type'=>'left',			                // This is a Left menu entry
										'titre'=>'Informes Ventas',
										'mainmenu'=>'pos',
										'leftmenu'=>'ventas',
										'url'=>'/pos/backend/resultat/index.php?mainmenu=pos',
										'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
										'position'=>100,
										'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
										'perms'=>'$user->rights->pos->backend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
										'target'=>'',
										'user'=>2);
										
									$this->menu[$r++]=array(
										'fk_menu'=>'fk_mainmenu=pos,fk_leftmenu=ventas',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
										'type'=>'left',			                // This is a Left menu entry
										'titre'=>'Ventas',
										'mainmenu'=>'pos',
										'leftmenu'=>'',
										'url'=>'/pos/backend/resultat/ticket.php?mainmenu=pos',
										'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
										'position'=>100,
										'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
										'perms'=>'$user->rights->pos->backend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
										'target'=>'',
										'user'=>2);
										
										
									$this->menu[$r++]=array(
										'fk_menu'=>'fk_mainmenu=pos,fk_leftmenu=ventas',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
										'type'=>'left',			                // This is a Left menu entry
										'titre'=>'Clientes',
										'mainmenu'=>'pos',
										'leftmenu'=>'',
										'url'=>'/pos/backend/resultat/casoc.php?mainmenu=pos',
										'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
										'position'=>100,
										'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
										'perms'=>'$user->rights->pos->backend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
										'target'=>'',
										'user'=>2);	
										
										
									$this->menu[$r++]=array(
										'fk_menu'=>'fk_mainmenu=pos,fk_leftmenu=ventas',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
										'type'=>'left',			                // This is a Left menu entry
										'titre'=>'Usuarios',
										'mainmenu'=>'pos',
										'leftmenu'=>'',
										'url'=>'/pos/backend/resultat/causer.php?mainmenu=pos',
										'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
										'position'=>100,
										'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
										'perms'=>'$user->rights->pos->backend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
										'target'=>'',
										'user'=>2);
										
									$this->menu[$r++]=array(
										'fk_menu'=>'fk_mainmenu=pos,fk_leftmenu=ventas',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
										'type'=>'left',			                // This is a Left menu entry
										'titre'=>'Puesto',
										'mainmenu'=>'pos',
										'leftmenu'=>'',
										'url'=>'/pos/backend/resultat/place.php?mainmenu=pos',
										'langs'=>'',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
										'position'=>100,
										'enabled'=>'$conf->pos->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
										'perms'=>'$user->rights->pos->backend',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
										'target'=>'',
										'user'=>2);	
										
										$this->menu[$r++] = array(
											'fk_menu' => 'fk_mainmenu=companies',
											'type' => 'left',
											'titre' => 'Rewards',
											'mainmenu' => 'companies',
											'leftmenu' => '1',
											'url' => '/pos/rewards/rewards.php',
											'langs' => 'pos@pos',
											'position' => 100,
											'enabled' => '$conf->pos->enabled',
											'perms' => '$user->rights->pos->ad_rewards',
											'target' => '',
											'user' => 0
										);										

										
		 // Exports
		$r=1;

		// Example:
		// $this->export_code[$r]=$this->rights_class.'_'.$r;
		// $this->export_label[$r]='CustomersInvoicesAndInvoiceLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
        // $this->export_enabled[$r]='1';                               // Condition to show export in list (ie: '$user->id==3'). Set to 1 to always show when module is enabled.
		// $this->export_permission[$r]=array(array("facture","facture","export"));
		// $this->export_fields_array[$r]=array('s.rowid'=>"IdCompany",'s.nom'=>'CompanyName','s.address'=>'Address','s.cp'=>'Zip','s.ville'=>'Town','s.fk_pays'=>'Country','s.tel'=>'Phone','s.siren'=>'ProfId1','s.siret'=>'ProfId2','s.ape'=>'ProfId3','s.idprof4'=>'ProfId4','s.code_compta'=>'CustomerAccountancyCode','s.code_compta_fournisseur'=>'SupplierAccountancyCode','f.rowid'=>"InvoiceId",'f.facnumber'=>"InvoiceRef",'f.datec'=>"InvoiceDateCreation",'f.datef'=>"DateInvoice",'f.total'=>"TotalHT",'f.total_ttc'=>"TotalTTC",'f.tva'=>"TotalVAT",'f.paye'=>"InvoicePaid",'f.fk_statut'=>'InvoiceStatus','f.note'=>"InvoiceNote",'fd.rowid'=>'LineId','fd.description'=>"LineDescription",'fd.price'=>"LineUnitPrice",'fd.tva_tx'=>"LineVATRate",'fd.qty'=>"LineQty",'fd.total_ht'=>"LineTotalHT",'fd.total_tva'=>"LineTotalTVA",'fd.total_ttc'=>"LineTotalTTC",'fd.date_start'=>"DateStart",'fd.date_end'=>"DateEnd",'fd.fk_product'=>'ProductId','p.ref'=>'ProductRef');
		// $this->export_entities_array[$r]=array('s.rowid'=>"company",'s.nom'=>'company','s.address'=>'company','s.cp'=>'company','s.ville'=>'company','s.fk_pays'=>'company','s.tel'=>'company','s.siren'=>'company','s.siret'=>'company','s.ape'=>'company','s.idprof4'=>'company','s.code_compta'=>'company','s.code_compta_fournisseur'=>'company','f.rowid'=>"invoice",'f.facnumber'=>"invoice",'f.datec'=>"invoice",'f.datef'=>"invoice",'f.total'=>"invoice",'f.total_ttc'=>"invoice",'f.tva'=>"invoice",'f.paye'=>"invoice",'f.fk_statut'=>'invoice','f.note'=>"invoice",'fd.rowid'=>'invoice_line','fd.description'=>"invoice_line",'fd.price'=>"invoice_line",'fd.total_ht'=>"invoice_line",'fd.total_tva'=>"invoice_line",'fd.total_ttc'=>"invoice_line",'fd.tva_tx'=>"invoice_line",'fd.qty'=>"invoice_line",'fd.date_start'=>"invoice_line",'fd.date_end'=>"invoice_line",'fd.fk_product'=>'product','p.ref'=>'product');
		// $this->export_sql_start[$r]='SELECT DISTINCT ';
		// $this->export_sql_end[$r]  =' FROM ('.MAIN_DB_PREFIX.'facture as f, '.MAIN_DB_PREFIX.'facturedet as fd, '.MAIN_DB_PREFIX.'societe as s)';
		// $this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'product as p on (fd.fk_product = p.rowid)';
		// $this->export_sql_end[$r] .=' WHERE f.fk_soc = s.rowid AND f.rowid = fd.fk_facture';
		// $r++;
	}

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories.
	 *      @return     int             1 if OK, 0 if KO
	 */
	function init($options='')
	{
		$result=$this->_load_tables('/pos/sql/');
		if ($result < 0) return -1;	
		
        global $db,$conf;
 		// Create extrafields
         include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
         $extrafields = new ExtraFields($this->db);
         //campo extra vendedor 
             $attrname = "vendedor";
             $label="Vendedor (comision)"; 
             $type="sellist"; 
             $pos=100; 
             $size=''; 
             $elementtype="facture";
             $unique=0; 
             $required=0; 
             $default_value=''; 
             $param=array('options'=>array('user:login:rowid::rowid'=>NULL)); 
             $alwayseditable=1; 
             $perms=''; 
             $list=3; 
             $ishidden='Seleccionar el vendedor para reportes de comisiones'; 
             $computed=''; 
             $entity=$conf->entity; 
             $langfile=''; 
             $enabled='1';   
             $res = $extrafields->addExtraField($attrname, $label, $type, $pos, $size, $elementtype, $unique, $required, $default_value, $param, $alwayseditable, $perms, $list, $ishidden, $computed, $entity, $langfile, $enabled);

         //campo extra vendedor 
             $attrname = "terminal";
             $label="Terminales"; 
             $type="chkbxlst"; 
             $pos=100; 
             $size=''; 
             $elementtype="user";
             $unique=0; 
             $required=0; 
             $default_value=''; 
             $param=array('options'=>array('pos_cash:name:rowid::rowid'=>NULL)); 
             $alwayseditable=1; 
             $perms=''; 
             $list=3; 
             $ishidden='Selecciona las terminales admitidas'; 
             $computed=''; 
             $entity=$conf->entity; 
             $langfile=''; 
             $enabled='1';   
             $res = $extrafields->addExtraField($attrname, $label, $type, $pos, $size, $elementtype, $unique, $required, $default_value, $param, $alwayseditable, $perms, $list, $ishidden, $computed, $entity, $langfile, $enabled);

         //campo extra vendedor 
		 $attrname = "sucursal";
		 $label="Sucursal"; 
		 $type="sellist"; 
		 $pos=100; 
		 $size=''; 
		 $elementtype="facture";
		 $unique=0; 
		 $required=0; 
		 $default_value=''; 
		 $param=array('options'=>array('entrepot:ref:rowid::rowid'=>NULL)); 
		 $alwayseditable=1; 
		 $perms=''; 
		 $list=3; 
		 $ishidden='Seleccionar la sucursal (almacen)'; 
		 $computed=''; 
		 $entity=$conf->entity; 
		 $langfile=''; 
		 $enabled='1';   
		 $res = $extrafields->addExtraField($attrname, $label, $type, $pos, $size, $elementtype, $unique, $required, $default_value, $param, $alwayseditable, $perms, $list, $ishidden, $computed, $entity, $langfile, $enabled);



         //campo extra vendedor 
             $attrname = "tipo_doc";
             $label="Tipo"; 
             $type="int"; 
             $pos=100; 
             $size=''; 
             $elementtype="facture";
             $unique=0; 
             $required=0; 
             $default_value=''; 
             $param=''; 
             $alwayseditable=1; 
             $perms=''; 
             $list=0; 
             $ishidden='Tipo de documento creado desde el cash'; 
             $computed=''; 
             $entity=$conf->entity; 
             $langfile=''; 
             $enabled='1';   
             $extrafields->addExtraField($attrname, $label, $type, $pos, $size, $elementtype, $unique, $required, $default_value, $param, $alwayseditable, $perms, $list, $ishidden, $computed, $entity, $langfile, $enabled);
		 //fin campo extra vendedor 
		 
		 $extrafields = new ExtraFields($this->db);
		 $attrname = "exoneracion";
		 $label="Exoneracion"; 
		 $type="sellist"; 
		 $pos=100; 
		 $size=''; 
		 $elementtype="facture";
		 $unique=0; 
		 $required=0; 
		 $default_value=''; 
		 $param=array('options'=>array('facturaelectronica_societe_exonerado:numero_documento:numero_documento::($SEL$   f.fk_soc FROM llx_facture f WHERE f.rowid=$ID$ AND f.fk_soc=llx_facturaelectronica_societe_exonerado.fk_soc)'=>NULL)); 
		 $alwayseditable=1; 
		 $perms=''; 
		 $list=3; 
		 $ishidden='Seleccionar el la exoneracion'; 
		 $computed=''; 
		 $entity=$conf->entity; 
		 $langfile=''; 
		 $enabled='1';   
		 $res = $extrafields->addExtraField($attrname, $label, $type, $pos, $size, $elementtype, $unique, $required, $default_value, $param, $alwayseditable, $perms, $list, $ishidden, $computed, $entity, $langfile, $enabled);
 
		 


         //campo extra vendedor 
		 $attrname = "tipo_doc";
		 $label="Tipo"; 
		 $type="int"; 
		 $pos=100; 
		 $size=11; 
		 $elementtype="propal";
		 $unique=0; 
		 $required=0; 
		 $default_value=''; 
		 $param=''; 
		 $alwayseditable=1; 
		 $perms=''; 
		 $list=0; 
		 $ishidden='Tipo de documento creado desde el cash'; 
		 $computed=''; 
		 $entity=$conf->entity; 
		 $langfile=''; 
		 $enabled='1';   
		 $extrafields->addExtraField($attrname, $label, $type, $pos, $size, $elementtype, $unique, $required, $default_value, $param, $alwayseditable, $perms, $list, $ishidden, $computed, $entity, $langfile, $enabled);
	 //fin campo extra vendedor 

         //campo extra vendedor 
		 $attrname = "fk_cierre";
		 $label="Cierre cash"; 
		 $type="int"; 
		 $pos=100; 
		 $size=11; 
		 $elementtype="propal";
		 $unique=0; 
		 $required=0; 
		 $default_value=''; 
		 $param=''; 
		 $alwayseditable=1; 
		 $perms=''; 
		 $list=0; 
		 $ishidden='Cierre de caja que lo genero'; 
		 $computed=''; 
		 $entity=$conf->entity; 
		 $langfile=''; 
		 $enabled='1';   
		 $extrafields->addExtraField($attrname, $label, $type, $pos, $size, $elementtype, $unique, $required, $default_value, $param, $alwayseditable, $perms, $list, $ishidden, $computed, $entity, $langfile, $enabled);
	 //fin campo extra vendedor 
	 
         //campo extra vendedor 
		 $attrname = "tipo_doc";
		 $label="Tipo"; 
		 $type="int"; 
		 $pos=100; 
		 $size=11; 
		 $elementtype="commande";
		 $unique=0; 
		 $required=0; 
		 $default_value=''; 
		 $param=''; 
		 $alwayseditable=1; 
		 $perms=''; 
		 $list=0; 
		 $ishidden='Tipo de documento creado desde el cash'; 
		 $computed=''; 
		 $entity=$conf->entity; 
		 $langfile=''; 
		 $enabled='1';   
		 $extrafields->addExtraField($attrname, $label, $type, $pos, $size, $elementtype, $unique, $required, $default_value, $param, $alwayseditable, $perms, $list, $ishidden, $computed, $entity, $langfile, $enabled);
	 //fin campo extra vendedor 



         //campo extra vendedor 
             $attrname = "chashdespro";
             $label="Es del punto de venta"; 
             $type="boolean"; 
             $pos=100; 
             $size=11; 
             $elementtype="facture";
             $unique=0; 
             $required=0; 
             $default_value=''; 
             $param=''; 
             $alwayseditable=1; 
             $perms=''; 
             $list=0; 
             $ishidden=0; 
             $computed=''; 
             $entity=$conf->entity; 
             $langfile=''; 
             $enabled='1';   
             $extrafields->addExtraField($attrname, $label, $type, $pos, $size, $elementtype, $unique, $required, $default_value, $param, $alwayseditable, $perms, $list, $ishidden, $computed, $entity, $langfile, $enabled);
         //fin campo extra vendedor

         //campo extra comodin 
             $attrname = "comodin";
             $label="Comodin"; 
             $type="boolean"; 
             $pos=100; 
             $size=11; 
             $elementtype="product";
             $unique=0; 
             $required=0; 
             $default_value=''; 
             $param=''; 
             $alwayseditable=1; 
             $perms=''; 
             $list=3; 
             $ishidden=0; 
             $computed=''; 
             $entity=$conf->entity; 
             $langfile=''; 
             $enabled='1';   
             $extrafields->addExtraField($attrname, $label, $type, $pos, $size, $elementtype, $unique, $required, $default_value, $param, $alwayseditable, $perms, $list, $ishidden, $computed, $entity, $langfile, $enabled);
         //fin campo extra comodin

         
         //campo extra descuento 
             $attrname = "descuento";
             $label="Descuento maximo"; 
             $type="double"; 
             $pos=100; 
             $size='24,8'; 
             $elementtype="product";
             $unique=0; 
             $required=0; 
             $default_value=''; 
             $param=''; 
             $alwayseditable=1; 
             $perms=''; 
             $list=3; 
             $ishidden='Descuento maximo para el punto de venta'; 
             $computed=''; 
             $entity=$conf->entity; 
             $langfile=''; 
             $enabled='1';   
             $extrafields->addExtraField($attrname, $label, $type, $pos, $size, $elementtype, $unique, $required, $default_value, $param, $alwayseditable, $perms, $list, $ishidden, $computed, $entity, $langfile, $enabled);
         //fin campo extra descuento  
         
         //campo extra descuento 
             $attrname = "utilidad";
             $label="% Utilidad"; 
             $type="double"; 
             $pos=100; 
             $size='24,8'; 
             $elementtype="product";
             $unique=0; 
             $required=0; 
             $default_value=''; 
             $param=''; 
             $alwayseditable=1; 
             $perms=''; 
             $list=3; 
             $ishidden='Porcentaje de utilidad para calculos en precios proveedor'; 
             $computed=''; 
             $entity=$conf->entity; 
             $langfile=''; 
             $enabled='1';   
             $extrafields->addExtraField($attrname, $label, $type, $pos, $size, $elementtype, $unique, $required, $default_value, $param, $alwayseditable, $perms, $list, $ishidden, $computed, $entity, $langfile, $enabled);
         //fin campo extra descuento  


		require_once(DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php');
		$dirodt=DOL_DATA_ROOT.'/produit';
		dol_mkdir($dirodt);
		dol_copy(dol_buildpath('/pos/frontend/img/noimage.jpg',0),$dirodt.'/noimage.jpg',0,0);
		
		if(empty($conf->global->POS_USE_TICKETS))
		{
			
			dolibarr_set_const($db,"POS_USE_TICKETS", '1','chaine',0,'',$conf->entity);
			dolibarr_set_const($db,"POS_MAX_TTC", '100','chaine',0,'',$conf->entity);
		}
		
		$sql = array();

		return $this->_init($sql, $options);



	}

	/**
	 *		Function called when module is disabled.
	 *      Remove from database constants, boxes and permissions from Dolibarr database.
	 *		Data directories are not deleted.
	 *      @return     int             1 if OK, 0 if KO
	 */
	function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}


	/**
	 *		\brief		Create tables, keys and data required by module
	 * 					Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * 					and create data commands must be stored in directory /mymodule/sql/
	 *					This function is called by this->init.
	 * 		\return		int		<=0 if KO, >0 if OK
	 */
/* 	function load_tables($options = '')
	{
		return $this->_load_tables('/pos/sql/');
	} */
}

?>
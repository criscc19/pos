<?php


include_once DOL_DOCUMENT_ROOT.'/core/class/commoninvoice.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobjectline.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/client.class.php';
require_once DOL_DOCUMENT_ROOT.'/margin/lib/margins.lib.php';
require_once DOL_DOCUMENT_ROOT.'/multicurrency/class/multicurrency.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';


if (! empty($conf->accounting->enabled)) require_once DOL_DOCUMENT_ROOT.'/core/class/html.formaccounting.class.php';
if (! empty($conf->accounting->enabled)) require_once DOL_DOCUMENT_ROOT.'/accountancy/class/accountingaccount.class.php';

/**
 *	Class to manage invoices
 */
class Facture_cashdespro extends CommonInvoice
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element='facture_cashdespro';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element='facture_cashdespro';

	/**
	 * @var int    Name of subtable line
	 */
	public $table_element_line = 'facturedet_cashdespro';

	/**
	 * @var int Field with ID of parent key if this field has a parent
	 */
	public $fk_element = 'fk_facture';

	/**
	 * @var string String with name of icon for myobject. Must be the part after the 'object_' into object_myobject.png
	 */
	public $picto='bill';

	/**
	 * 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 * @var int
	 */
	public $ismultientitymanaged = 1;

	/**
	 * 0=Default, 1=View may be restricted to sales representative only if no permission to see all or to company of external user if external user
	 * @var integer
	 */
	public $restrictiononfksoc = 1;

	/**
	 * {@inheritdoc}
	 */
	protected $table_ref_field = 'facnumber';

	public $socid;

	public $author;

	/**
     * @var int ID
     */
	public $fk_user_author;

	/**
     * @var int ID
     */
	public $fk_user_valid;

	public $date;              // Date invoice
	public $datem;
	public $ref_client;
	public $ref_int;
	//Check constants for types
	public $type = self::TYPE_STANDARD;

	//var $amount;
	public $remise_absolue;
	public $remise_percent;
	public $total_ht=0;
	public $total_tva=0;
	public $total_localtax1=0;
	public $total_localtax2=0;
	public $total_ttc=0;
	public $revenuestamp;

	//! Fermeture apres paiement partiel: discount_vat, badcustomer, abandon
	//! Fermeture alors que aucun paiement: replaced (si remplace), abandon
	public $close_code;
	//! Commentaire si mis a paye sans paiement complet
	public $close_note;
	//! 1 if invoice paid COMPLETELY, 0 otherwise (do not use it anymore, use statut and close_code)
	public $paye;
	//! key of module source when invoice generated from a dedicated module ('cashdesk', 'takepos', ...)
	public $module_source;
	//! key of pos source ('0', '1', ...)
	public $pos_source;
	//! id of template invoice when generated from a template invoice
	public $fk_fac_rec_source;
	//! id of source invoice if replacement invoice or credit note
	public $fk_facture_source;
	public $linked_objects=array();
	public $date_lim_reglement;
	public $cond_reglement_code;		// Code in llx_c_paiement
	public $mode_reglement_code;		// Code in llx_c_paiement

	/**
     * @var int ID Field to store bank id to use when payment mode is withdraw
     */
	public $fk_bank;

	/**
	 * @deprecated
	 */
	public $products=array();

	/**
	 * @var FactureLigne[]
	 */
	public $lines=array();

	public $line;
	public $extraparams=array();
	public $specimen;

	public $fac_rec;

	// Multicurrency
	/**
     * @var int ID
     */
	public $fk_multicurrency;

	public $multicurrency_code;
	public $multicurrency_tx;
	public $multicurrency_total_ht;
	public $multicurrency_total_tva;
	public $multicurrency_total_ttc;

	/**
	 * @var int Situation cycle reference number
	 */
	public $situation_cycle_ref;

	/**
	 * @var int Situation counter inside the cycle
	 */
	public $situation_counter;

	/**
	 * @var int Final situation flag
	 */
	public $situation_final;

	/**
	 * @var array Table of previous situations
	 */
	public $tab_previous_situation_invoice=array();

	/**
	 * @var array Table of next situations
	 */
	public $tab_next_situation_invoice=array();

	public $oldcopy;
	public $fk_mesa;
    /**
     * Standard invoice
     */
    const TYPE_STANDARD = 0;

    /**
     * Replacement invoice
     */
    const TYPE_REPLACEMENT = 1;

    /**
     * Credit note invoice
     */
    const TYPE_CREDIT_NOTE = 2;

    /**
     * Deposit invoice
     */
    const TYPE_DEPOSIT = 3;

    /**
     * Proforma invoice (should not be used. a proforma is an order)
     */
    const TYPE_PROFORMA = 4;

	/**
	 * Situation invoice
	 */
	const TYPE_SITUATION = 5;

	/**
	 * Draft status
	 */
	const STATUS_DRAFT = 0;

	/**
	 * Validated (need to be paid)
	 */
	const STATUS_VALIDATED = 1;

	/**
	 * Classified paid.
	 * If paid partially, $this->close_code can be:
	 * - CLOSECODE_DISCOUNTVAT
	 * - CLOSECODE_BADDEBT
	 * If paid completely, this->close_code will be null
	 */
	const STATUS_CLOSED = 2;

	/**
	 * Classified abandoned and no payment done.
	 * $this->close_code can be:
	 * - CLOSECODE_BADDEBT
	 * - CLOSECODE_ABANDONED
	 * - CLOSECODE_REPLACED
	 */
	const STATUS_ABANDONED = 3;

	const CLOSECODE_DISCOUNTVAT = 'discount_vat';	// Abandonned remain - escompte
	const CLOSECODE_BADDEBT = 'badcustomer';		// Abandonned - bad
	const CLOSECODE_ABANDONED = 'abandon';			// Abandonned - other
	const CLOSECODE_REPLACED = 'replaced';			// Closed after doing a replacement invoice

	/**
	 * 	Constructor
	 *
	 * 	@param	DoliDB		$db			Database handler
	 */
	function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 *	Create invoice in database.
	 *  Note: this->ref can be set or empty. If empty, we will use "(PROV999)"
	 *  Note: this->fac_rec must be set to create invoice from a recurring invoice
	 *
	 *	@param	User	$user      		Object user that create
	 *	@param  int		$notrigger		1=Does not execute triggers, 0 otherwise
	 * 	@param	int		$forceduedate	1=Do not recalculate due date from payment condition but force it with value
	 *	@return	int						<0 if KO, >0 if OK
	 */
	function create(User $user, $notrigger=0, $forceduedate=0)
	{
		global $langs,$conf,$mysoc,$hookmanager;
		$error=0;
		// Insert into database
		$socid  = $this->socid;

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."facture_cashdespro (";
		$sql.= " facnumber";
		$sql.= ", entity";
		$sql.= ", ref_ext";
		$sql.= ", type";
		$sql.= ", fk_soc";
		$sql.= ", datec";
		$sql.= ", remise_absolue";
		$sql.= ", remise_percent";
		$sql.= ", datef";
		$sql.= ", date_pointoftax";
		$sql.= ", note_private";
		$sql.= ", note_public";
		$sql.= ", ref_client, ref_int";
        $sql.= ", fk_account";
		$sql.= ", module_source, pos_source, fk_fac_rec_source, fk_facture_source, fk_user_author, fk_projet";
		$sql.= ", fk_cond_reglement, fk_mode_reglement, date_lim_reglement, model_pdf";
		$sql.= ", situation_cycle_ref, situation_counter, situation_final";
		$sql.= ", fk_incoterms, location_incoterms";
        $sql.= ", fk_multicurrency";
        $sql.= ", multicurrency_code";
		$sql.= ", multicurrency_tx";
		$sql.= ", fk_mesa";		
		$sql.= ")";
		$sql.= " VALUES (";
		$sql.= "'".$this->facnumber."'";
		$sql.= ", ".$this->entity;
		$sql.= ", ".($this->ref_ext?"'".$this->db->escape($this->ref_ext)."'":"null");
		$sql.= ", '".$this->db->escape($this->type)."'";
		$sql.= ", '".$socid."'";
		$sql.= ", '".$this->db->idate($now)."'";
		$sql.= ", ".($this->remise_absolue>0?$this->remise_absolue:'NULL');
		$sql.= ", ".($this->remise_percent>0?$this->remise_percent:'NULL');
		$sql.= ", '".$this->db->idate($this->date)."'";
		$sql.= ", ".(strval($this->date_pointoftax)!='' ? "'".$this->db->idate($this->date_pointoftax)."'" : 'null');
		$sql.= ", ".($this->note_private?"'".$this->db->escape($this->note_private)."'":"null");
		$sql.= ", ".($this->note_public?"'".$this->db->escape($this->note_public)."'":"null");
		$sql.= ", ".($this->ref_client?"'".$this->db->escape($this->ref_client)."'":"null");
		$sql.= ", ".($this->ref_int?"'".$this->db->escape($this->ref_int)."'":"null");
		$sql.= ", ".($this->fk_account>0?$this->fk_account:'NULL');
		$sql.= ", ".($this->module_source ? "'".$this->db->escape($this->module_source)."'" : "null");
		$sql.= ", ".($this->pos_source != '' ? "'".$this->db->escape($this->pos_source)."'" : "null");
		$sql.= ", ".($this->fk_fac_rec_source?"'".$this->db->escape($this->fk_fac_rec_source)."'":"null");
		$sql.= ", ".($this->fk_facture_source?"'".$this->db->escape($this->fk_facture_source)."'":"null");
		$sql.= ", ".($user->id > 0 ? "'".$user->id."'":"null");
		$sql.= ", ".($this->fk_project?$this->fk_project:"null");
		$sql.= ", ".$this->cond_reglement_id;
		$sql.= ", ".$this->mode_reglement_id;
		$sql.= ", '".$this->db->idate($datelim)."', '".$this->db->escape($this->modelpdf)."'";
		$sql.= ", ".($this->situation_cycle_ref?"'".$this->db->escape($this->situation_cycle_ref)."'":"null");
		$sql.= ", ".($this->situation_counter?"'".$this->db->escape($this->situation_counter)."'":"null");
		$sql.= ", ".($this->situation_final?$this->situation_final:0);
		$sql.= ", ".(int) $this->fk_incoterms;
        $sql.= ", '".$this->db->escape($this->location_incoterms)."'";
		$sql.= ", ".(int) $this->fk_multicurrency;
		$sql.= ", '".$this->db->escape($this->multicurrency_code)."'";
		$sql.= ", ".(double) $this->multicurrency_tx;
		$sql.= ",".(empty($this->fk_mesa)?"null":$this->db->escape($this->fk_mesa))."";	
		$sql.=")";

		$resql=$this->db->query($sql);
		if ($resql)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.'facture_cashdespro');
			// Update ref with new one
			$this->ref='(PROV'.$this->id.')';
			$sql = 'UPDATE '.MAIN_DB_PREFIX."facture_cashdespro SET facnumber='".$this->db->escape($this->ref)."' WHERE rowid=".$this->id;
			return $this->id;
		}
		else
		{
			$this->error=$this->db->error();
			$this->db->rollback();
			return -1;
		}
	}



	/**
	 *	Get object and lines from database
	 *
	 *	@param      int		$rowid       	Id of object to load
	 * 	@param		string	$ref			Reference of invoice
	 * 	@param		string	$ref_ext		External reference of invoice
	 * 	@param		int		$ref_int		Internal reference of other object
	 *  @param		bool	$fetch_situation	Fetch the previous and next situation in $tab_previous_situation_invoice and $tab_next_situation_invoice
	 *	@return     int         			>0 if OK, <0 if KO, 0 if not found
	 */
	function fetch($rowid, $ref='', $ref_ext='', $ref_int='', $fetch_situation=false)
	{
		global $conf;

		if (empty($rowid) && empty($ref) && empty($ref_ext) && empty($ref_int)) return -1;

		$sql = 'SELECT f.rowid,f.entity,f.facnumber,f.ref_client,f.ref_ext,f.ref_int,f.type,f.fk_soc,f.amount';
		$sql.= ', f.tva, f.localtax1, f.localtax2, f.total, f.total_ttc, f.revenuestamp';
		$sql.= ', f.remise_percent, f.remise_absolue, f.remise';
		$sql.= ', f.datef as df, f.date_pointoftax';
		$sql.= ', f.date_lim_reglement as dlr';
		$sql.= ', f.datec as datec';
		$sql.= ', f.date_valid as datev';
		$sql.= ', f.fk_mesa';
		$sql.= ', f.tms as datem';
		$sql.= ', f.note_private, f.note_public, f.fk_statut, f.paye, f.close_code, f.close_note, f.fk_user_author, f.fk_user_valid, f.model_pdf, f.last_main_doc';
		$sql.= ', f.fk_facture_source';
		$sql.= ', f.fk_mode_reglement, f.fk_cond_reglement, f.fk_projet, f.extraparams';
		$sql.= ', f.situation_cycle_ref, f.situation_counter, f.situation_final';
		$sql.= ', f.fk_account';
		$sql.= ", f.fk_multicurrency, f.multicurrency_code, f.multicurrency_tx, f.multicurrency_total_ht, f.multicurrency_total_tva, f.multicurrency_total_ttc";
		$sql.= ', p.code as mode_reglement_code, p.libelle as mode_reglement_libelle';
		$sql.= ', c.code as cond_reglement_code, c.libelle as cond_reglement_libelle, c.libelle_facture as cond_reglement_libelle_doc';
        $sql.= ', f.fk_incoterms, f.location_incoterms';
        $sql.= ', f.module_source, f.pos_source';
        $sql.= ", i.libelle as libelle_incoterms";
		$sql.= ' FROM '.MAIN_DB_PREFIX.'facture_cashdespro as f';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_payment_term as c ON f.fk_cond_reglement = c.rowid';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_paiement as p ON f.fk_mode_reglement = p.id';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_incoterms as i ON f.fk_incoterms = i.rowid';

		if ($rowid)   $sql.= " WHERE f.rowid=".$rowid;
		else $sql.= ' WHERE f.entity IN ('.getEntity('facture').')'; // Dont't use entity if you use rowid

		if ($ref)     $sql.= " AND f.facnumber='".$this->db->escape($ref)."'";
		if ($ref_ext) $sql.= " AND f.ref_ext='".$this->db->escape($ref_ext)."'";
		if ($ref_int) $sql.= " AND f.ref_int='".$this->db->escape($ref_int)."'";
		dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);

				$this->id					= $obj->rowid;
				$this->entity				= $obj->entity;

				$this->ref					= $obj->facnumber;
				$this->ref_client			= $obj->ref_client;
				$this->ref_ext				= $obj->ref_ext;
				$this->ref_int				= $obj->ref_int;
				$this->type					= $obj->type;
				$this->date					= $this->db->jdate($obj->df);
				$this->date_pointoftax		= $this->db->jdate($obj->date_pointoftax);
				$this->date_creation		= $this->db->jdate($obj->datec);
				$this->date_validation		= $this->db->jdate($obj->datev);
				$this->date_modification	= $this->db->jdate($obj->datem);
				$this->datem				= $this->db->jdate($obj->datem);
				$this->remise_percent		= $obj->remise_percent;
				$this->remise_absolue		= $obj->remise_absolue;
				$this->total_ht				= $obj->total;
				$this->total_tva			= $obj->tva;
				$this->total_localtax1		= $obj->localtax1;
				$this->total_localtax2		= $obj->localtax2;
				$this->total_ttc			= $obj->total_ttc;
				$this->revenuestamp         = $obj->revenuestamp;
				$this->paye					= $obj->paye;
				$this->close_code			= $obj->close_code;
				$this->close_note			= $obj->close_note;
				$this->socid				= $obj->fk_soc;
				$this->statut				= $obj->fk_statut;
				$this->date_lim_reglement	= $this->db->jdate($obj->dlr);
				$this->mode_reglement_id	= $obj->fk_mode_reglement;
				$this->mode_reglement_code	= $obj->mode_reglement_code;
				$this->mode_reglement		= $obj->mode_reglement_libelle;
				$this->cond_reglement_id	= $obj->fk_cond_reglement;
				$this->cond_reglement_code	= $obj->cond_reglement_code;
				$this->cond_reglement		= $obj->cond_reglement_libelle;
				$this->cond_reglement_doc	= $obj->cond_reglement_libelle_doc;
				$this->fk_account           = ($obj->fk_account>0)?$obj->fk_account:null;
				$this->fk_project			= $obj->fk_projet;
				$this->fk_facture_source	= $obj->fk_facture_source;
				$this->note					= $obj->note_private;	// deprecated
				$this->note_private			= $obj->note_private;
				$this->note_public			= $obj->note_public;
				$this->user_author			= $obj->fk_user_author;
				$this->user_valid			= $obj->fk_user_valid;
				$this->modelpdf				= $obj->model_pdf;
				$this->last_main_doc		= $obj->last_main_doc;
				$this->situation_cycle_ref  = $obj->situation_cycle_ref;
				$this->situation_counter    = $obj->situation_counter;
				$this->situation_final      = $obj->situation_final;
				$this->fk_mesa              = $obj->fk_mesa;

				$this->extraparams			= (array) json_decode($obj->extraparams, true);

				//Incoterms
				$this->fk_incoterms         = $obj->fk_incoterms;
				$this->location_incoterms   = $obj->location_incoterms;
				$this->libelle_incoterms    = $obj->libelle_incoterms;

  				$this->module_source        = $obj->module_source;
				$this->pos_source           = $obj->pos_source;

				// Multicurrency
				$this->fk_multicurrency 		= $obj->fk_multicurrency;
				$this->multicurrency_code 		= $obj->multicurrency_code;
				$this->multicurrency_tx 		= $obj->multicurrency_tx;
				$this->multicurrency_total_ht 	= $obj->multicurrency_total_ht;
				$this->multicurrency_total_tva 	= $obj->multicurrency_total_tva;
				$this->multicurrency_total_ttc 	= $obj->multicurrency_total_ttc;

				if (($this->type == self::TYPE_SITUATION || ($this->type == self::TYPE_CREDIT_NOTE && $this->situation_cycle_ref > 0))  && $fetch_situation)
				{
					$this->fetchPreviousNextSituationInvoice();
				}

				if ($this->statut == self::STATUS_DRAFT)	$this->brouillon = 1;

				// Retreive all extrafield
				// fetch optionals attributes and labels
				$this->fetch_optionals();

				/*
				 * Lines
				 */

				$this->lines  = array();

				$result=$this->fetch_lines($this->socid,$this->user_author);
				if ($result < 0)
				{
					$this->error=$this->db->error();
					return -3;
				}
				return 1;
			}
			else
			{
				$this->error='Invoice with id='.$rowid.' or ref='.$ref.' or ref_ext='.$ref_ext.' not found';
				dol_syslog(get_class($this)."::fetch Error ".$this->error, LOG_ERR);
				return 0;
			}
		}
		else
		{
			$this->error=$this->db->error();
			return -1;
		}
	}


    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
	/**
	 *	Load all detailed lines into this->lines
	 *
	 *	@return     int         1 if OK, < 0 if KO
	 */
	function fetch_lines($fk_soc,$fk_vendedor)
	{
        // phpcs:enable
		$this->lines=array();

		$sql = 'SELECT l.rowid, l.fk_facture, l.fk_product, l.fk_parent_line, l.label as custom_label, l.description, l.product_type, l.price, l.qty, l.vat_src_code, l.tva_tx,';
		$sql.= ' l.situation_percent, l.fk_prev_id,';
		$sql.= ' l.localtax1_tx, l.localtax2_tx, l.localtax1_type, l.localtax2_type, l.remise_percent, l.fk_remise_except, l.subprice,';
		$sql.= ' l.rang, l.special_code,';
		$sql.= ' l.date_start as date_start, l.date_end as date_end,';
		$sql.= ' l.info_bits, l.total_ht, l.total_tva, l.total_localtax1, l.total_localtax2, l.total_ttc, l.fk_code_ventilation, l.fk_product_fournisseur_price as fk_fournprice, l.buy_price_ht as pa_ht,';
		$sql.= ' l.fk_unit,';
		$sql.= ' l.tipo_dococumento,
		l.numero_documento,
		l.nombre_institucion,
		l.fecha_emision,
		l.porcentaje,
		l.monto_exoneracion,
		l.exo_tva,
		l.exo_total_ht,
		l.exo_total_tva,
		l.exo_total_ttc,
		l.multicurrency_exo_total_ht,
		l.multicurrency_exo_total_tva,
		l.multicurrency_exo_total_ttc,
		l.multicurrency_monto_exoneracion,';		
		$sql.= ' l.fk_multicurrency, l.multicurrency_code, l.multicurrency_subprice, l.multicurrency_total_ht, l.multicurrency_total_tva, l.multicurrency_total_ttc,';
		$sql.= ' l.fk_soc,l.fk_user,l.fk_vendedor,multicurrency_tx,l.estado,';
		$sql.= ' p.ref as product_ref, p.fk_product_type as fk_product_type, p.label as product_label, p.description as product_desc';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'facturedet_cashdespro as l';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'product as p ON l.fk_product = p.rowid';
		$sql.= ' WHERE l.fk_facture = '.$this->id.' AND l.fk_soc='.$fk_soc.' AND fk_vendedor='.$fk_vendedor.'';
		$sql.= ' ORDER BY l.rang, l.rowid';
		dol_syslog(get_class($this).'::fetch_lines', LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			$num = $this->db->num_rows($result);
			$i = 0;
			while ($i < $num)
			{
				$objp = $this->db->fetch_object($result);
				$line = new FactureLigne_cashdespro($this->db);

				$line->id               = $objp->rowid;
				$line->rowid	        = $objp->rowid;             // deprecated
				$line->fk_facture       = $objp->fk_facture;
				$line->label            = $objp->custom_label;		// deprecated
				$line->desc             = $objp->description;		// Description line
				$line->description      = $objp->description;		// Description line
				$line->product_type     = $objp->product_type;		// Type of line
				$line->ref              = $objp->product_ref;		// Ref product
				$line->product_ref      = $objp->product_ref;		// Ref product
				$line->libelle          = $objp->product_label;		// TODO deprecated
				$line->product_label	= $objp->product_label;		// Label product
				$line->product_desc     = $objp->product_desc;		// Description product
				$line->fk_product_type  = $objp->fk_product_type;	// Type of product
				$line->qty              = $objp->qty;
				$line->subprice         = $objp->subprice;

                $line->vat_src_code     = $objp->vat_src_code;
				$line->tva_tx           = $objp->tva_tx;
				$line->localtax1_tx     = $objp->localtax1_tx;
				$line->localtax2_tx     = $objp->localtax2_tx;
				$line->localtax1_type   = $objp->localtax1_type;
				$line->localtax2_type   = $objp->localtax2_type;
				$line->remise_percent   = $objp->remise_percent;
				$line->fk_remise_except = $objp->fk_remise_except;
				$line->fk_product       = $objp->fk_product;
				$line->date_start       = $this->db->jdate($objp->date_start);
				$line->date_end         = $this->db->jdate($objp->date_end);
				$line->date_start       = $this->db->jdate($objp->date_start);
				$line->date_end         = $this->db->jdate($objp->date_end);
				$line->info_bits        = $objp->info_bits;
				$line->total_ht         = $objp->total_ht;
				$line->total_tva        = $objp->total_tva;
				$line->total_localtax1  = $objp->total_localtax1;
				$line->total_localtax2  = $objp->total_localtax2;
				$line->total_ttc        = $objp->total_ttc;
				$line->code_ventilation = $objp->fk_code_ventilation;
				$line->fk_fournprice 	= $objp->fk_fournprice;
				$marginInfos			= getMarginInfos($objp->subprice, $objp->remise_percent, $objp->tva_tx, $objp->localtax1_tx, $objp->localtax2_tx, $line->fk_fournprice, $objp->pa_ht);
				$line->pa_ht 			= $marginInfos[0];
				$line->marge_tx			= $marginInfos[1];
				$line->marque_tx		= $marginInfos[2];
				$line->rang				= $objp->rang;
				$line->special_code		= $objp->special_code;
				$line->fk_parent_line	= $objp->fk_parent_line;
				$line->situation_percent= $objp->situation_percent;
				$line->fk_prev_id       = $objp->fk_prev_id;
				$line->fk_unit	        = $objp->fk_unit;
				$line->fk_soc	        = $objp->fk_soc;
				$line->fk_user	        = $objp->fk_user;				
				$line->fk_vendedor	    = $objp->fk_vendedor;
				$line->multicurrency_tx	    = $objp->multicurrency_tx;
				$line->estado	    = $objp->estado;

				$line->tipo_dococumento = $objp->tipo_dococumento;
				$line->numero_documento = $objp->numero_documento;
				$line->nombre_institucion = $objp->multicurrency_tx;
				$line->fecha_emision = $objp->nombre_institucion;
				$line->porcentaje = $objp->porcentaje;
				$line->monto_exoneracion = $objp->monto_exoneracion;
				$line->exo_tva = $objp->exo_tva;
				$line->exo_total_ht = $objp->exo_total_ht;
				$line->exo_total_tva = $objp->exo_total_tva;
				$line->exo_total_ttc = $objp->exo_total_ttc;
				$line->multicurrency_exo_total_ht = $objp->multicurrency_exo_total_ht;
				$line->multicurrency_exo_total_tva = $objp->multicurrency_exo_total_tva;
				$line->multicurrency_exo_total_ttc = $objp->multicurrency_exo_total_ttc;
				$line->multicurrency_monto_exoneracion = $objp->multicurrency_monto_exoneracion;	

				// Accountancy
				$line->fk_accounting_account	= $objp->fk_code_ventilation;

				// Multicurrency
				$line->fk_multicurrency 		= $objp->fk_multicurrency;
				$line->multicurrency_code 		= $objp->multicurrency_code;
				$line->multicurrency_subprice 	= $objp->multicurrency_subprice;
				$line->multicurrency_total_ht 	= $objp->multicurrency_total_ht;
				$line->multicurrency_total_tva 	= $objp->multicurrency_total_tva;
				$line->multicurrency_total_ttc 	= $objp->multicurrency_total_ttc;

                $line->fetch_optionals();

				$this->lines[$i] = $line;

				$i++;
			}
			$this->db->free($result);
			return 1;
		}
		else
		{
			$this->error=$this->db->error();
			return -3;
		}
	}


	/**
	 *      Update database
	 *
	 *      @param      User	$user        	User that modify
	 *      @param      int		$notrigger	    0=launch triggers after, 1=disable triggers
	 *      @return     int      			   	<0 if KO, >0 if OK
	 */
	function update(User $user, $notrigger=0)
	{
		global $conf;

		$error=0;

		// Clean parameters
		if (empty($this->type)) $this->type= self::TYPE_STANDARD;
		if (isset($this->facnumber)) $this->facnumber=trim($this->ref);
		if (isset($this->ref_client)) $this->ref_client=trim($this->ref_client);
		if (isset($this->increment)) $this->increment=trim($this->increment);
		if (isset($this->close_code)) $this->close_code=trim($this->close_code);
		if (isset($this->close_note)) $this->close_note=trim($this->close_note);
		if (isset($this->note) || isset($this->note_private)) $this->note=(isset($this->note) ? trim($this->note) : trim($this->note_private));		// deprecated
		if (isset($this->note) || isset($this->note_private)) $this->note_private=(isset($this->note_private) ? trim($this->note_private) : trim($this->note));
		if (isset($this->note_public)) $this->note_public=trim($this->note_public);
		if (isset($this->modelpdf)) $this->modelpdf=trim($this->modelpdf);
		if (isset($this->import_key)) $this->import_key=trim($this->import_key);
		if (isset($this->fk_mesa)) $this->fk_mesa=trim($this->fk_mesa);
		// Check parameters
		// Put here code to add control on parameters values

		// Update request
		$sql = "UPDATE ".MAIN_DB_PREFIX."facture_cashdespro SET";
		$sql.= " facnumber=".(isset($this->ref)?"'".$this->db->escape($this->ref)."'":"null").",";
		$sql.= " type=".(isset($this->type)?$this->db->escape($this->type):"null").",";
		$sql.= " ref_client=".(isset($this->ref_client)?"'".$this->db->escape($this->ref_client)."'":"null").",";
		$sql.= " increment=".(isset($this->increment)?"'".$this->db->escape($this->increment)."'":"null").",";
		$sql.= " fk_soc=".(isset($this->socid)?$this->db->escape($this->socid):"null").",";
		$sql.= " datec=".(strval($this->date_creation)!='' ? "'".$this->db->idate($this->date_creation)."'" : 'null').",";
		$sql.= " datef=".(strval($this->date)!='' ? "'".$this->db->idate($this->date)."'" : 'null').",";
		$sql.= " date_pointoftax=".(strval($this->date_pointoftax)!='' ? "'".$this->db->idate($this->date_pointoftax)."'" : 'null').",";
		$sql.= " date_valid=".(strval($this->date_validation)!='' ? "'".$this->db->idate($this->date_validation)."'" : 'null').",";
		$sql.= " paye=".(isset($this->paye)?$this->db->escape($this->paye):"null").",";
		$sql.= " remise_percent=".(isset($this->remise_percent)?$this->db->escape($this->remise_percent):"null").",";
		$sql.= " remise_absolue=".(isset($this->remise_absolue)?$this->db->escape($this->remise_absolue):"null").",";
		$sql.= " close_code=".(isset($this->close_code)?"'".$this->db->escape($this->close_code)."'":"null").",";
		$sql.= " close_note=".(isset($this->close_note)?"'".$this->db->escape($this->close_note)."'":"null").",";
		$sql.= " tva=".(isset($this->total_tva)?$this->total_tva:"null").",";
		$sql.= " localtax1=".(isset($this->total_localtax1)?$this->total_localtax1:"null").",";
		$sql.= " localtax2=".(isset($this->total_localtax2)?$this->total_localtax2:"null").",";
		$sql.= " total=".(isset($this->total_ht)?$this->total_ht:"null").",";
		$sql.= " total_ttc=".(isset($this->total_ttc)?$this->total_ttc:"null").",";
		$sql.= " revenuestamp=".((isset($this->revenuestamp) && $this->revenuestamp != '')?$this->db->escape($this->revenuestamp):"null").",";
		$sql.= " fk_statut=".(isset($this->statut)?$this->db->escape($this->statut):"null").",";
		$sql.= " fk_user_author=".(isset($this->user_author)?$this->db->escape($this->user_author):"null").",";
		$sql.= " fk_user_valid=".(isset($this->fk_user_valid)?$this->db->escape($this->fk_user_valid):"null").",";
		$sql.= " fk_facture_source=".(isset($this->fk_facture_source)?$this->db->escape($this->fk_facture_source):"null").",";
		$sql.= " fk_projet=".(isset($this->fk_project)?$this->db->escape($this->fk_project):"null").",";
		$sql.= " fk_cond_reglement=".(isset($this->cond_reglement_id)?$this->db->escape($this->cond_reglement_id):"null").",";
		$sql.= " fk_mode_reglement=".(isset($this->mode_reglement_id)?$this->db->escape($this->mode_reglement_id):"null").",";
		$sql.= " date_lim_reglement=".(strval($this->date_lim_reglement)!='' ? "'".$this->db->idate($this->date_lim_reglement)."'" : 'null').",";
		$sql.= " note_private=".(isset($this->note_private)?"'".$this->db->escape($this->note_private)."'":"null").",";
		$sql.= " note_public=".(isset($this->note_public)?"'".$this->db->escape($this->note_public)."'":"null").",";
		$sql.= " model_pdf=".(isset($this->modelpdf)?"'".$this->db->escape($this->modelpdf)."'":"null").",";
		$sql.= " import_key=".(isset($this->import_key)?"'".$this->db->escape($this->import_key)."'":"null").",";
		$sql.= " situation_cycle_ref=".(empty($this->situation_cycle_ref)?"null":$this->db->escape($this->situation_cycle_ref)).",";
		$sql.= " situation_counter=".(empty($this->situation_counter)?"null":$this->db->escape($this->situation_counter)).",";
		$sql.= " fk_mesa=".(empty($this->fk_mesa)?"null":$this->db->escape($this->fk_mesa)).",";		
		$sql.= " situation_final=".(empty($this->situation_final)?"0":$this->db->escape($this->situation_final));
		
		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error++; $this->errors[]="Error ".$this->db->lasterror();
		}

		if (! $error && empty($conf->global->MAIN_EXTRAFIELDS_DISABLED) && is_array($this->array_options) && count($this->array_options)>0)
		{
			$result=$this->insertExtraFields();
			if ($result < 0)
			{
				$error++;
			}
		}

		if (! $error && ! $notrigger)
		{
			// Call trigger
			$result=$this->call_trigger('BILL_MODIFY_CASDESPRO',$user);
			if ($result < 0) $error++;
			// End call triggers
		}

		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
				$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}



	/**
	 *	Delete invoice
	 *
	 *	@param     	User	$user      	    User making the deletion.
	 *	@param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@param		int		$idwarehouse	Id warehouse to use for stock change.
	 *	@return		int						<0 if KO, 0=Refused, >0 if OK
	 */
	function delete($user, $notrigger=0, $idwarehouse=-1)
	{

	}


	/**
	 * 		Add an invoice line into database (linked to product/service or not).
	 * 		Les parametres sont deja cense etre juste et avec valeurs finales a l'appel
	 *		de cette methode. Aussi, pour le taux tva, il doit deja avoir ete defini
	 *		par l'appelant par la methode get_default_tva(societe_vendeuse,societe_acheteuse,produit)
	 *		et le desc doit deja avoir la bonne valeur (a l'appelant de gerer le multilangue)
	 *
	 * 		@param    	string		$desc            	Description of line
	 * 		@param    	double		$pu_ht              Unit price without tax (> 0 even for credit note)
	 * 		@param    	double		$qty             	Quantity
	 * 		@param    	double		$txtva           	Force Vat rate, -1 for auto (Can contain the vat_src_code too with syntax '9.9 (CODE)')
	 * 		@param		double		$txlocaltax1		Local tax 1 rate (deprecated, use instead txtva with code inside)
	 *  	@param		double		$txlocaltax2		Local tax 2 rate (deprecated, use instead txtva with code inside)
	 *		@param    	int			$fk_product      	Id of predefined product/service
	 * 		@param    	double		$remise_percent  	Percent of discount on line
	 * 		@param    	int			$date_start      	Date start of service
	 * 		@param    	int			$date_end        	Date end of service
	 * 		@param    	int			$ventil          	Code of dispatching into accountancy
	 * 		@param    	int			$info_bits			Bits de type de lignes
	 *		@param    	int			$fk_remise_except	Id discount used
	 *		@param		string		$price_base_type	'HT' or 'TTC'
	 * 		@param    	double		$pu_ttc             Unit price with tax (> 0 even for credit note)
	 * 		@param		int			$type				Type of line (0=product, 1=service). Not used if fk_product is defined, the type of product is used.
	 *      @param      int			$rang               Position of line
	 *      @param		int			$special_code		Special code (also used by externals modules!)
	 *      @param		string		$origin				'order', ...
	 *      @param		int			$origin_id			Id of origin object
	 *      @param		int			$fk_parent_line		Id of parent line
	 * 		@param		int			$fk_fournprice		Supplier price id (to calculate margin) or ''
	 * 		@param		int			$pa_ht				Buying price of line (to calculate margin) or ''
	 * 		@param		string		$label				Label of the line (deprecated, do not use)
	 *		@param		array		$array_options		extrafields array
	 *      @param      int         $situation_percent  Situation advance percentage
	 *      @param      int         $fk_prev_id         Previous situation line id reference
	 * 		@param 		string		$fk_unit 			Code of the unit to use. Null to use the default one
	 * 		@param		double		$pu_ht_devise		Unit price in currency
	 *    	@return    	int             				<0 if KO, Id of line if OK
	 */
	function addline($desc, $pu_ht, $qty, $txtva, $txlocaltax1=0, $txlocaltax2=0, $fk_product=0, $remise_percent=0, $date_start='', $date_end='', $ventil=0, $info_bits=0, $fk_remise_except='', $price_base_type='HT', $pu_ttc=0, $type=self::TYPE_STANDARD, $rang=-1, $special_code=0, $origin='', $origin_id=0, $fk_parent_line=0, $fk_fournprice=null, $pa_ht=0, $label='', $array_options=0, $situation_percent=100, $fk_prev_id=0, $fk_unit = null, $pu_ht_devise = 0,$fk_soc=0,$fk_user=0,$fk_vendedor=0,$multicurrency_tx=1)
	{
		// Deprecation warning
		if ($label) {
			dol_syslog(__METHOD__ . ": using line label is deprecated", LOG_WARNING);
			//var_dump(debug_backtrace(false));exit;
		}

		global $mysoc, $conf, $langs;

		dol_syslog(get_class($this)."::addline id=$this->id,desc=$desc,pu_ht=$pu_ht,qty=$qty,txtva=$txtva, txlocaltax1=$txlocaltax1, txlocaltax2=$txlocaltax2, fk_product=$fk_product,remise_percent=$remise_percent,date_start=$date_start,date_end=$date_end,ventil=$ventil,info_bits=$info_bits,fk_remise_except=$fk_remise_except,price_base_type=$price_base_type,pu_ttc=$pu_ttc,type=$type, fk_unit=$fk_unit", LOG_DEBUG);

			include_once DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php';
//var_dump($pu_ht);exit;
			// Clean parameters
			if (empty($remise_percent)) $remise_percent=0;
			if (empty($qty)) $qty=0;
			if (empty($info_bits)) $info_bits=0;
			if (empty($rang)) $rang=0;
			if (empty($ventil)) $ventil=0;
			if (empty($txtva)) $txtva=0;
			if (empty($txlocaltax1)) $txlocaltax1=0;
			if (empty($txlocaltax2)) $txlocaltax2=0;
			if (empty($fk_parent_line) || $fk_parent_line < 0) $fk_parent_line=0;
			if (empty($fk_prev_id)) $fk_prev_id = 'null';
			if (! isset($situation_percent) || $situation_percent > 100 || (string) $situation_percent == '') $situation_percent = 100;

			$remise_percent=price2num($remise_percent);
			$qty=price2num($qty);
			$pu_ht=price2num($pu_ht);
			$pu_ht_devise=price2num($pu_ht_devise);
			$pu_ttc=price2num($pu_ttc);
			$pa_ht=price2num($pa_ht);
			if (!preg_match('/\((.*)\)/', $txtva)) {
				$txtva = price2num($txtva);               // $txtva can have format '5.0(XXX)' or '5'
			}
			$txlocaltax1=price2num($txlocaltax1);
			$txlocaltax2=price2num($txlocaltax2);

			if ($price_base_type=='HT')
			{
				$pu=$pu_ht;
			}
			else
			{
				$pu=$pu_ht;
			}

			// Check parameters
			if ($type < 0) return -1;

			$this->db->begin();

			$product_type=$type;
			if (!empty($fk_product))
			{
				$product=new Product($this->db);
				$result=$product->fetch($fk_product);
				$product_type=$product->type;

				if (! empty($conf->global->STOCK_MUST_BE_ENOUGH_FOR_INVOICE) && $product_type == 0 && $product->stock_reel < $qty) {
                    $langs->load("errors");
				    $this->error=$langs->trans('ErrorStockIsNotEnoughToAddProductOnInvoice', $product->ref);
					$this->db->rollback();
					return -3;
				}
			}

			$localtaxes_type=getLocalTaxesFromRate($txtva, 0, $this->thirdparty, $mysoc);

			// Clean vat code
			$vat_src_code='';
			if (preg_match('/\((.*)\)/', $txtva, $reg))
			{
				$vat_src_code = $reg[1];
				$txtva = preg_replace('/\s*\(.*\)/', '', $txtva);    // Remove code into vatrate.
			}

			// Calcul du total TTC et de la TVA pour la ligne a partir de
			// qty, pu, remise_percent et txtva
			// TRES IMPORTANT: C'est au moment de l'insertion ligne qu'on doit stocker
			// la part ht, tva et ttc, et ce au niveau de la ligne qui a son propre taux tva.

			$tabprice = calcul_price_total($qty, $pu, $remise_percent, $txtva, $txlocaltax1, $txlocaltax2, 0, $price_base_type, $info_bits, $product_type, $mysoc, $localtaxes_type, $situation_percent, $this->multicurrency_tx, $pu_ht_devise);

			$total_ht  = $tabprice[0];
			$total_tva = $tabprice[1];
			$total_ttc = $tabprice[2];
			$total_localtax1 = $tabprice[9];
			$total_localtax2 = $tabprice[10];
			$pu_ht = $tabprice[3];

			// MultiCurrency
			$multicurrency_total_ht  = $tabprice[16];
            $multicurrency_total_tva = $tabprice[17];
            $multicurrency_total_ttc = $tabprice[18];
			$pu_ht_devise = $tabprice[19];

			// Rank to use
			$rangtouse = $rang;
			if ($rangtouse == -1)
			{
				$rangmax = $this->line_max($fk_parent_line);
				$rangtouse = $rangmax + 1;
			}

			// Insert line
			$this->line=new FactureLigne_cashdespro($this->db);

			$this->line->context = $this->context;

			$this->line->fk_facture=$this->id;
			$this->line->label=$label;	// deprecated
			$this->line->desc=$desc;

			$this->line->qty=            ($this->type==self::TYPE_CREDIT_NOTE?abs($qty):$qty);	    // For credit note, quantity is always positive and unit price negative
			$this->line->subprice=       ($this->type==self::TYPE_CREDIT_NOTE?-abs($pu_ht):$pu_ht); // For credit note, unit price always negative, always positive otherwise

			$this->line->vat_src_code=$vat_src_code;
			$this->line->tva_tx=$txtva;
			$this->line->localtax1_tx=($total_localtax1?$localtaxes_type[1]:0);
			$this->line->localtax2_tx=($total_localtax2?$localtaxes_type[3]:0);
			$this->line->localtax1_type = $localtaxes_type[0];
			$this->line->localtax2_type = $localtaxes_type[2];

			$this->line->total_ht=       (($this->type==self::TYPE_CREDIT_NOTE||$qty<0)?-abs($total_ht):$total_ht);    // For credit note and if qty is negative, total is negative
			$this->line->total_ttc=      (($this->type==self::TYPE_CREDIT_NOTE||$qty<0)?-abs($total_ttc):$total_ttc);  // For credit note and if qty is negative, total is negative
			$this->line->total_tva=      (($this->type==self::TYPE_CREDIT_NOTE||$qty<0)?-abs($total_tva):$total_tva);  // For credit note and if qty is negative, total is negative
			$this->line->total_localtax1=(($this->type==self::TYPE_CREDIT_NOTE||$qty<0)?-abs($total_localtax1):$total_localtax1);  // For credit note and if qty is negative, total is negative
			$this->line->total_localtax2=(($this->type==self::TYPE_CREDIT_NOTE||$qty<0)?-abs($total_localtax2):$total_localtax2);  // For credit note and if qty is negative, total is negative

			$this->line->fk_product=$fk_product;
			$this->line->product_type=$product_type;
			$this->line->remise_percent=$remise_percent;
			$this->line->date_start=$date_start;
			$this->line->date_end=$date_end;
			$this->line->ventil=$ventil;
			$this->line->rang=$rangtouse;
			$this->line->info_bits=$info_bits;
			$this->line->fk_remise_except=$fk_remise_except;

			$this->line->special_code=$special_code;
			$this->line->fk_parent_line=$fk_parent_line;
			$this->line->origin=$origin;
			$this->line->origin_id=$origin_id;
			$this->line->situation_percent = $situation_percent;
			$this->line->fk_prev_id = $fk_prev_id;
			$this->line->fk_unit=$fk_unit;

			// infos marge
			$this->line->fk_fournprice = $fk_fournprice;
			$this->line->pa_ht = $pa_ht;

			// Multicurrency
			$this->line->fk_multicurrency			= $this->fk_multicurrency;
			$this->line->multicurrency_code			= $this->multicurrency_code;
			$this->line->multicurrency_subprice		= $pu_ht_devise;
			$this->line->multicurrency_total_ht 	= $multicurrency_total_ht;
            $this->line->multicurrency_total_tva 	= $multicurrency_total_tva;
            $this->line->multicurrency_total_ttc 	= $multicurrency_total_ttc;
			$this->line->fk_soc 	= $fk_soc;
			$this->line->fk_user 	= $fk_user;			
			$this->line->fk_vendedor 	= $fk_vendedor;
			$this->line->multicurrency_tx 	= $multicurrency_tx;			
			if (is_array($array_options) && count($array_options)>0) {
				$this->line->array_options=$array_options;
			}

			$result=$this->line->insert();
			if ($result > 0)
			{
				// Reorder if child line
				if (! empty($fk_parent_line)) $this->line_order(true,'DESC');

				// Mise a jour informations denormalisees au niveau de la facture meme
				$result=$this->update_price(1,'auto',0,$mysoc);	// The addline method is designed to add line from user input so total calculation with update_price must be done using 'auto' mode.

				if ($result > 0)
				{
					$this->db->commit();
					return $this->line->id;
				}
				else
				{
					$this->error=$this->db->lasterror();
					$this->db->rollback();
					return -1;
				}
			}
			else
			{
				$this->error=$this->line->error;
				$this->db->rollback();
				return -2;
			}

	}

	/**
	 *  Update a detail line
	 *
	 *  @param     	int			$rowid           	Id of line to update
	 *  @param     	string		$desc            	Description of line
	 *  @param     	double		$pu              	Prix unitaire (HT ou TTC selon price_base_type) (> 0 even for credit note lines)
	 *  @param     	double		$qty             	Quantity
	 *  @param     	double		$remise_percent  	Pourcentage de remise de la ligne
	 *  @param     	int		$date_start      	Date de debut de validite du service
	 *  @param     	int		$date_end        	Date de fin de validite du service
	 *  @param     	double		$txtva          	VAT Rate (Can be '8.5', '8.5 (ABC)')
	 * 	@param		double		$txlocaltax1		Local tax 1 rate
	 *  @param		double		$txlocaltax2		Local tax 2 rate
	 * 	@param     	string		$price_base_type 	HT or TTC
	 * 	@param     	int			$info_bits 		    Miscellaneous informations
	 * 	@param		int			$type				Type of line (0=product, 1=service)
	 * 	@param		int			$fk_parent_line		Id of parent line (0 in most cases, used by modules adding sublevels into lines).
	 * 	@param		int			$skip_update_total	Keep fields total_xxx to 0 (used for special lines by some modules)
	 * 	@param		int			$fk_fournprice		Id of origin supplier price
	 * 	@param		int			$pa_ht				Price (without tax) of product when it was bought
	 * 	@param		string		$label				Label of the line (deprecated, do not use)
	 * 	@param		int			$special_code		Special code (also used by externals modules!)
     *  @param		array		$array_options		extrafields array
	 * 	@param      int         $situation_percent  Situation advance percentage
	 * 	@param 		string		$fk_unit 			Code of the unit to use. Null to use the default one
	 * 	@param		double		$pu_ht_devise		Unit price in currency
	 * 	@param		int			$notrigger			disable line update trigger
	 *  @return    	int             				< 0 if KO, > 0 if OK
	 */
	function updateline($rowid, $desc, $pu, $qty, $remise_percent, $date_start, $date_end, $txtva, $txlocaltax1=0, $txlocaltax2=0, $price_base_type='HT', $info_bits=0, $type= self::TYPE_STANDARD, $fk_parent_line=0, $skip_update_total=0, $fk_fournprice=null, $pa_ht=0, $label='', $special_code=0, $array_options=0, $situation_percent=100, $fk_unit = null, $pu_ht_devise = 0,$fk_soc=0,$fk_facture=0,$fk_user=0,$fk_vendedor=0,$multicurrency_code=0,$multicurrency_tx=1)
	{
		global $conf,$user;
		// Deprecation warning
		if ($label) {
			dol_syslog(__METHOD__ . ": using line label is deprecated", LOG_WARNING);
		}

		include_once DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php';

		global $mysoc,$langs;

		dol_syslog(get_class($this)."::updateline rowid=$rowid, desc=$desc, pu=$pu, qty=$qty, remise_percent=$remise_percent, date_start=$date_start, date_end=$date_end, txtva=$txtva, txlocaltax1=$txlocaltax1, txlocaltax2=$txlocaltax2, price_base_type=$price_base_type, info_bits=$info_bits, type=$type, fk_parent_line=$fk_parent_line pa_ht=$pa_ht, special_code=$special_code, fk_unit=$fk_unit, pu_ht_devise=$pu_ht_devise", LOG_DEBUG);



			$this->db->begin();

			// Clean parameters
			if (empty($qty)) $qty=0;
			if (empty($fk_parent_line) || $fk_parent_line < 0) $fk_parent_line=0;
			if (empty($special_code) || $special_code == 3) $special_code=0;
			if (! isset($situation_percent) || $situation_percent > 100 || (string) $situation_percent == '') $situation_percent = 100;

			$remise_percent	= price2num($remise_percent);
			$qty			= price2num($qty);
			$pu 			= price2num($pu);
        	$pu_ht_devise	= price2num($pu_ht_devise);
			$pa_ht			= price2num($pa_ht);
			$txtva			= price2num($txtva);
			$txlocaltax1	= price2num($txlocaltax1);
			$txlocaltax2	= price2num($txlocaltax2);

			// Check parameters
			if ($type < 0) return -1;

			// Calculate total with, without tax and tax from qty, pu, remise_percent and txtva
			// TRES IMPORTANT: C'est au moment de l'insertion ligne qu'on doit stocker
			// la part ht, tva et ttc, et ce au niveau de la ligne qui a son propre taux tva.

			$localtaxes_type=getLocalTaxesFromRate($txtva,0,$this->thirdparty, $mysoc);

			// Clean vat code
    		$vat_src_code='';
    		if (preg_match('/\((.*)\)/', $txtva, $reg))
    		{
    		    $vat_src_code = $reg[1];
    		    $txtva = preg_replace('/\s*\(.*\)/', '', $txtva);    // Remove code into vatrate.
    		}

			$tabprice=calcul_price_total($qty, $pu, $remise_percent, $txtva, $txlocaltax1, $txlocaltax2, 0, $price_base_type, $info_bits, $type, $mysoc, $localtaxes_type, $situation_percent, $this->multicurrency_tx, $pu_ht_devise);

			$total_ht  = $tabprice[0];
			$total_tva = $tabprice[1];
			$total_ttc = $tabprice[2];
			$total_localtax1=$tabprice[9];
			$total_localtax2=$tabprice[10];
			$pu_ht  = $tabprice[3];
			$pu_tva = $tabprice[4];
			$pu_ttc = $tabprice[5];

			// MultiCurrency
			$multicurrency_total_ht  = $tabprice[16];
            $multicurrency_total_tva = $tabprice[17];
            $multicurrency_total_ttc = $tabprice[18];
			$pu_ht_devise = $tabprice[19];



			// Old properties: $price, $remise (deprecated)
			$price = $pu;
			$remise = 0;
			if ($remise_percent > 0)
			{
				$remise = round(($pu * $remise_percent / 100),2);
				$price = ($pu - $remise);
			}
			$price    = price2num($price);

			//Fetch current line from the database and then clone the object and set it in $oldline property
			$line = new FactureLigne_cashdespro($this->db);
			$line->fetch($rowid);

			if (!empty($line->fk_product))
			{
				$product=new Product($this->db);
				$result=$product->fetch($line->fk_product);
				$product_type=$product->type;
//comprobancion de precio minimo
$nivel = $_POST['price_level'];

				if($this->multicurrency_code == 'CRC'){
					if($product->multiprices_base_type[$nivel] == 'HT'){
					  $precio_min = $product->multiprices_min[$nivel];
					if($precio_min > $pu_ht){
						$this->error = 'El precio no puede ser menor que el precio minimo';
						return - 9;
					}

					}else{
					 $precio_min_ttc = $product->multiprices_min_ttc[$nivel];  
					if($precio_min_ttc > $total_ttc){
						$this->error = 'El precio no puede ser menor que el precio minimo';						
						return - 9;
					}
					}  
				  }
					else{

				   }   
				   
//fin comprobancion de precio minimo
			   

				if (! empty($conf->global->STOCK_MUST_BE_ENOUGH_FOR_INVOICE) && $product_type == 0 && $product->stock_reel < $qty) {
                    $langs->load("errors");
				    $this->error=$langs->trans('ErrorStockIsNotEnoughToAddProductOnInvoice', $product->ref);
					$this->db->rollback();
					return -3;
				}
			}

			$staticline = clone $line;

			$line->oldline = $staticline;
			$this->line = $line;
            $this->line->context = $this->context;

			// Reorder if fk_parent_line change
			if (! empty($fk_parent_line) && ! empty($staticline->fk_parent_line) && $fk_parent_line != $staticline->fk_parent_line)
			{
				$rangmax = $this->line_max($fk_parent_line);
				$this->line->rang = $rangmax + 1;
			}

			$this->line->rowid				= $rowid;
			$this->line->label				= $label;
			$this->line->desc				= $desc;
			$this->line->qty				= ($this->type==self::TYPE_CREDIT_NOTE?abs($qty):$qty);	// For credit note, quantity is always positive and unit price negative

			$this->line->vat_src_code       = $vat_src_code;
			$this->line->tva_tx				= $txtva;
			$this->line->localtax1_tx		= $txlocaltax1;
			$this->line->localtax2_tx		= $txlocaltax2;
			$this->line->localtax1_type		= $localtaxes_type[0];
			$this->line->localtax2_type		= $localtaxes_type[2];

			$this->line->remise_percent		= $remise_percent;
			$this->line->subprice			= ($this->type==2?-abs($pu_ht):$pu_ht); // For credit note, unit price always negative, always positive otherwise
			$this->line->date_start			= $date_start;
			$this->line->date_end			= $date_end;
			$this->line->total_ht			= (($this->type==self::TYPE_CREDIT_NOTE||$qty<0)?-abs($total_ht):$total_ht);  // For credit note and if qty is negative, total is negative
			$this->line->total_tva			= (($this->type==self::TYPE_CREDIT_NOTE||$qty<0)?-abs($total_tva):$total_tva);
			$this->line->total_localtax1	= $total_localtax1;
			$this->line->total_localtax2	= $total_localtax2;
			$this->line->total_ttc			= (($this->type==self::TYPE_CREDIT_NOTE||$qty<0)?-abs($total_ttc):$total_ttc);
			$this->line->info_bits			= $info_bits;
			$this->line->special_code		= $special_code;
			$this->line->product_type		= $type;
			$this->line->fk_parent_line		= $fk_parent_line;
			$this->line->skip_update_total	= $skip_update_total;
			$this->line->situation_percent  = $situation_percent;
			$this->line->fk_unit				= $fk_unit;

			$this->line->fk_fournprice = $fk_fournprice;
			$this->line->pa_ht = $pa_ht;

			// Multicurrency
			$this->line->multicurrency_code		    = $multicurrency_code;			
			$this->line->multicurrency_subprice		= $pu_ht_devise;
			$this->line->multicurrency_total_ht 	= $multicurrency_total_ht;
            $this->line->multicurrency_total_tva 	= $multicurrency_total_tva;
            $this->line->multicurrency_total_ttc 	= $multicurrency_total_ttc;
            $this->line->fk_soc 	= $fk_soc;
			$this->line->fk_facture	= $fk_facture;
			$this->line->fk_user	= $fk_user;			
			$this->line->fk_vendedor	= $fk_vendedor;
			$this->line->multicurrency_tx	= $multicurrency_tx;			
			if (is_array($array_options) && count($array_options)>0) {
				$this->line->array_options=$array_options;
			}

			$result=$this->line->update($user, $notrigger);
			if ($result > 0)
			{
				// Reorder if child line
				if (! empty($fk_parent_line)) $this->line_order(true,'DESC');

				// Mise a jour info denormalisees au niveau facture
				$this->update_price(1);
				$this->db->commit();
				return $result;
			}
			else
			{
			    $this->error=$this->line->error;
				$this->db->rollback();
				return -1;
			}

	}

	function validate(){
		$sq = 'UPDATE `llx_facture_cashdespro` SET `fk_statut` = "1" WHERE `llx_facture_cashdespro`.`rowid` = '.$this->id.'';
		$res = $this->db->query($sq);
		if($res){
			$mascara = 'ORDEN-{000000+000000}';
			$element = 'facture_cashdespro';
			$referencia = 'facnumber';
			$numero = get_next_value($this->db,$mascara,$element,$referencia ,$where,$soc,$obj->date,'next');	
		$sq = 'UPDATE `llx_facture_cashdespro` SET `facnumber` = "'.$numero.'" WHERE `llx_facture_cashdespro`.`rowid` = '.$this->id.'';
		$res = $this->db->query($sq);	
		}
		return $res;
		}
	/**
	 *	Delete line in database
	 *
	 *	@param		int		$rowid		Id of line to delete
	 *	@return		int					<0 if KO, >0 if OK
	 */
	function deleteline($rowid)
	{
        global $user;

	}


}




/**
 *	Class to manage invoice lines.
 *  Saved into database table llx_facturedet
 */
class FactureLigne_cashdespro extends CommonInvoiceLine
{
    /**
	 * @var string ID to identify managed object
	 */
	public $element='facturedet_cashdespro';

    /**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element='facturedet_cashdespro';

	public $oldline;

	//! From llx_facturedet
	//! Id facture
	public $fk_facture;
	//! Id parent line
	public $fk_parent_line;
	/**
	 * @deprecated
	 */
	public $label;
	//! Description ligne
	public $desc;

	public $localtax1_type;	// Local tax 1 type
	public $localtax2_type;	// Local tax 2 type
	public $fk_remise_except;	// Link to line into llx_remise_except
	public $rang = 0;

    public $fk_fournprice;
	public $fk_soc; 
	public $fk_user; 	
	public $fk_vendedor; 	
	public $pa_ht;
	public $marge_tx;
	public $marque_tx;

	public $special_code;	// Liste d'options non cumulabels:
	// 1: frais de port
	// 2: ecotaxe
	// 3: ??

	public $origin;
	public $origin_id;

	public $fk_code_ventilation = 0;

	public $date_start;
	public $date_end;

	// From llx_product
	/**
	 * @deprecated
	 * @see product_ref
	 */
	public $ref;				// Product ref (deprecated)
	public $product_ref;       // Product ref
	/**
	 * @deprecated
	 * @see product_label
	 */
	public $libelle;      		// Product label (deprecated)
	public $product_label;     // Product label
	public $product_desc;  	// Description produit

	public $skip_update_total; // Skip update price total for special lines

	/**
	 * @var int Situation advance percentage
	 */
	public $situation_percent;

	/**
	 * @var int Previous situation line id reference
	 */
	public $fk_prev_id;

	// Multicurrency
	public $fk_multicurrency;
	public $multicurrency_tx;
	public $multicurrency_code;
	public $multicurrency_subprice;
	public $multicurrency_total_ht;
	public $multicurrency_total_tva;
	public $multicurrency_total_ttc;
	public $estado;

	public $tipo_dococumento;
	public $numero_documento;
	public $nombre_institucion;
	public $fecha_emision;
	public $porcentaje;
	public $monto_exoneracion;
	public $exo_tva;
	public $exo_total_ht;
	public $exo_total_tva;
	public $exo_total_ttc;
	public $multicurrency_exo_total_ht;
	public $multicurrency_exo_total_tva;
	public $multicurrency_exo_total_ttc;
	public $multicurrency_monto_exoneracion;



	/**
	 *	Load invoice line from database
	 *
	 *	@param	int		$rowid      id of invoice line to get
	 *	@return	int					<0 if KO, >0 if OK
	 */
	function fetch($rowid)
	{
		$sql = 'SELECT fd.rowid, fd.fk_facture, fd.fk_parent_line, fd.fk_product, fd.product_type, fd.label as custom_label, fd.description, fd.price, fd.qty, fd.vat_src_code, fd.tva_tx,';
		$sql.= ' fd.localtax1_tx, fd. localtax2_tx, fd.remise, fd.remise_percent, fd.fk_remise_except, fd.subprice,';
		$sql.= ' fd.date_start as date_start, fd.date_end as date_end, fd.fk_product_fournisseur_price as fk_fournprice, fd.buy_price_ht as pa_ht,';
		$sql.= ' fd.info_bits, fd.special_code, fd.total_ht, fd.total_tva, fd.total_ttc, fd.total_localtax1, fd.total_localtax2, fd.rang,fk_soc,';
		$sql.= ' fd.fk_user,fd.fk_vendedor,multicurrency_tx,';		
		$sql.= ' fd.fk_code_ventilation,';
		$sql.= ' fd.fk_unit, fd.fk_user_author, fd.fk_user_modif,fd.multicurrency_code,';
		$sql.= ' fd.situation_percent, fd.fk_prev_id,';
		$sql.= ' fd.multicurrency_subprice,';
		$sql.= ' fd.multicurrency_total_ht,';
		$sql.= ' fd.multicurrency_total_tva,';
		$sql.= ' fd.multicurrency_total_ttc,';
		$sql.= ' fd.estado,';		
		$sql.= ' fd.tipo_dococumento,
		fd.numero_documento,
		fd.nombre_institucion,
		fd.fecha_emision,
		fd.porcentaje,
		fd.monto_exoneracion,
		fd.exo_tva,
		fd.exo_total_ht,
		fd.exo_total_tva,
		fd.exo_total_ttc,
		fd.multicurrency_exo_total_ht,
		fd.multicurrency_exo_total_tva,
		fd.multicurrency_exo_total_ttc,
		fd.multicurrency_monto_exoneracion,';
		$sql.= ' p.ref as product_ref, p.label as product_libelle, p.description as product_desc';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'facturedet_cashdespro as fd';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'product as p ON fd.fk_product = p.rowid';
		$sql.= ' WHERE fd.rowid = '.$rowid;
      
		$result = $this->db->query($sql);
		if ($result)
		{
			$objp = $this->db->fetch_object($result);

			$this->rowid				= $objp->rowid;
			$this->id					= $objp->rowid;
			$this->fk_facture			= $objp->fk_facture;
			$this->fk_parent_line		= $objp->fk_parent_line;
			$this->label				= $objp->custom_label;
			$this->desc					= $objp->description;
			$this->qty					= $objp->qty;
			$this->subprice				= $objp->subprice;
			$this->vat_src_code  		= $objp->vat_src_code;
			$this->tva_tx				= $objp->tva_tx;
			$this->localtax1_tx			= $objp->localtax1_tx;
			$this->localtax2_tx			= $objp->localtax2_tx;
			$this->remise_percent		= $objp->remise_percent;
			$this->fk_remise_except		= $objp->fk_remise_except;
			$this->fk_product			= $objp->fk_product;
			$this->product_type			= $objp->product_type;
			$this->date_start			= $this->db->jdate($objp->date_start);
			$this->date_end				= $this->db->jdate($objp->date_end);
			$this->info_bits			= $objp->info_bits;
			$this->tva_npr              = ($objp->info_bits & 1 == 1) ? 1 : 0;
			$this->special_code			= $objp->special_code;
			$this->total_ht				= $objp->total_ht;
			$this->total_tva			= $objp->total_tva;
			$this->total_localtax1		= $objp->total_localtax1;
			$this->total_localtax2		= $objp->total_localtax2;
			$this->total_ttc			= $objp->total_ttc;
			$this->fk_code_ventilation	= $objp->fk_code_ventilation;
			$this->rang					= $objp->rang;
			$this->fk_fournprice		= $objp->fk_fournprice;
			$marginInfos				= getMarginInfos($objp->subprice, $objp->remise_percent, $objp->tva_tx, $objp->localtax1_tx, $objp->localtax2_tx, $this->fk_fournprice, $objp->pa_ht);
			$this->pa_ht				= $marginInfos[0];
			$this->marge_tx				= $marginInfos[1];
			$this->marque_tx			= $marginInfos[2];

			$this->ref					= $objp->product_ref;      // deprecated
			$this->product_ref			= $objp->product_ref;
			$this->libelle				= $objp->product_libelle;  // deprecated
			$this->product_label		= $objp->product_libelle;
			$this->product_desc			= $objp->product_desc;
			$this->fk_unit				= $objp->fk_unit;
			$this->fk_user_modif		= $objp->fk_user_modif;
			$this->fk_user_author		= $objp->fk_user_author;

			$this->situation_percent    = $objp->situation_percent;
			$this->fk_prev_id           = $objp->fk_prev_id;
			$this->multicurrency_code   =   $objp->multicurrency_code;
			$this->multicurrency_tx   =   $objp->multicurrency_tx;			
			$this->multicurrency_subprice = $objp->multicurrency_subprice;
			$this->multicurrency_total_ht = $objp->multicurrency_total_ht;
			$this->multicurrency_total_tva= $objp->multicurrency_total_tva;
			$this->multicurrency_total_ttc= $objp->multicurrency_total_ttc;
			$this->fk_soc                 = $objp->fk_soc;
			$this->fk_user                = $objp->fk_user;
			$this->fk_vendedor            = $objp->fk_vendedor;	
			
			$this->estado	    = $objp->estado;

			$this->tipo_dococumento = $objp->tipo_dococumento;
			$this->numero_documento = $objp->numero_documento;
			$this->nombre_institucion = $objp->multicurrency_tx;
			$this->fecha_emision = $objp->nombre_institucion;
			$this->porcentaje = $objp->porcentaje;
			$this->monto_exoneracion = $objp->monto_exoneracion;
			$this->exo_tva = $objp->exo_tva;
			$this->exo_total_ht = $objp->exo_total_ht;
			$this->exo_total_tva = $objp->exo_total_tva;
			$this->exo_total_ttc = $objp->exo_total_ttc;
			$this->multicurrency_exo_total_ht = $objp->multicurrency_exo_total_ht;
			$this->multicurrency_exo_total_tva = $objp->multicurrency_exo_total_tva;
			$this->multicurrency_exo_total_ttc = $objp->multicurrency_exo_total_ttc;
			$this->multicurrency_monto_exoneracion = $objp->multicurrency_monto_exoneracion;			


			$this->db->free($result);

			return 1;
		}
		else
		{
		    $this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 *	Insert line into database
	 *
	 *	@param      int		$notrigger		                 1 no triggers
	 *  @param      int     $noerrorifdiscountalreadylinked  1=Do not make error if lines is linked to a discount and discount already linked to another
	 *	@return		int						                 <0 if KO, >0 if OK
	 */
	function insert($notrigger=0, $noerrorifdiscountalreadylinked=0)
	{
		global $langs,$user,$conf;

		$error=0;

        $pa_ht_isemptystring = (empty($this->pa_ht) && $this->pa_ht == ''); // If true, we can use a default value. If this->pa_ht = '0', we must use '0'.

        dol_syslog(get_class($this)."::insert rang=".$this->rang, LOG_DEBUG);

		// Clean parameters
		$this->desc=trim($this->desc);
		if (empty($this->tva_tx)) $this->tva_tx=0;
		if (empty($this->localtax1_tx)) $this->localtax1_tx=0;
		if (empty($this->localtax2_tx)) $this->localtax2_tx=0;
		if (empty($this->localtax1_type)) $this->localtax1_type=0;
		if (empty($this->localtax2_type)) $this->localtax2_type=0;
		if (empty($this->total_localtax1)) $this->total_localtax1=0;
		if (empty($this->total_localtax2)) $this->total_localtax2=0;
		if (empty($this->rang)) $this->rang=0;
		if (empty($this->remise_percent)) $this->remise_percent=0;
		if (empty($this->info_bits)) $this->info_bits=0;
		if (empty($this->subprice)) $this->subprice=0;
		if (empty($this->special_code)) $this->special_code=0;
        if (empty($this->fk_parent_line)) $this->fk_parent_line=0;
		if (empty($this->fk_soc)) $this->fk_soc=0;
		if (empty($this->fk_user)) $this->fk_user=0;		
		if (empty($this->fk_vendedor)) $this->fk_vendedor=0;		
		if (empty($this->fk_prev_id)) $this->fk_prev_id = 0;
		if (! isset($this->situation_percent) || $this->situation_percent > 100 || (string) $this->situation_percent == '') $this->situation_percent = 100;

		if (empty($this->pa_ht)) $this->pa_ht=0;
		if (empty($this->multicurrency_subprice)) $this->multicurrency_subprice=0;
		if (empty($this->multicurrency_total_ht)) $this->multicurrency_total_ht=0;
		if (empty($this->multicurrency_total_tva)) $this->multicurrency_total_tva=0;
		if (empty($this->multicurrency_total_ttc)) $this->multicurrency_total_ttc=0;

		// if buy price not defined, define buyprice as configured in margin admin
		if ($this->pa_ht == 0 && $pa_ht_isemptystring)
		{
			if (($result = $this->defineBuyPrice($this->subprice, $this->remise_percent, $this->fk_product)) < 0)
			{
				return $result;
			}
			else
			{
				$this->pa_ht = $result;
			}
		}

		// Check parameters
		if ($this->product_type < 0)
		{
			$this->error='ErrorProductTypeMustBe0orMore';
			return -1;
		}
		if (! empty($this->fk_product))
		{
			// Check product exists
			$result=Product::isExistingObject('product', $this->fk_product);
			if ($result <= 0)
			{
				$this->error='ErrorProductIdDoesNotExists';
				return -1;
			}
		}

		$this->db->begin();

		// Insertion dans base de la ligne
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'facturedet_cashdespro';
		$sql.= ' (fk_facture, fk_parent_line, label, description, qty,';
		$sql.= ' vat_src_code, tva_tx, localtax1_tx, localtax2_tx, localtax1_type, localtax2_type,';
		$sql.= ' fk_product, product_type, remise_percent, subprice, fk_remise_except,';
		$sql.= ' date_start, date_end, fk_code_ventilation, ';
		$sql.= ' rang, special_code, fk_product_fournisseur_price, buy_price_ht,';
		$sql.= ' info_bits, total_ht, total_tva, total_ttc, total_localtax1, total_localtax2,';
		$sql.= ' situation_percent, fk_prev_id,';
		$sql.= ' fk_unit, fk_user_author, fk_user_modif,';
		$sql.= ' fk_multicurrency, multicurrency_code, multicurrency_subprice, multicurrency_total_ht, multicurrency_total_tva, multicurrency_total_ttc,fk_soc,fk_user,fk_vendedor,multicurrency_tx';
		$sql.= ')';
		$sql.= " VALUES (".(! empty($this->fk_facture)?$this->fk_facture:"0").",";
		$sql.= " ".($this->fk_parent_line>0 ? $this->fk_parent_line:"null").",";
		$sql.= " ".(! empty($this->label)?"'".$this->db->escape($this->label)."'":"null").",";
		$sql.= " '".$this->db->escape($this->desc)."',";
		$sql.= " ".price2num($this->qty).",";
        $sql.= " ".(empty($this->vat_src_code)?"''":"'".$this->db->escape($this->vat_src_code)."'").",";
		$sql.= " ".price2num($this->tva_tx).",";
		$sql.= " ".price2num($this->localtax1_tx).",";
		$sql.= " ".price2num($this->localtax2_tx).",";
		$sql.= " '".$this->db->escape($this->localtax1_type)."',";
		$sql.= " '".$this->db->escape($this->localtax2_type)."',";
		$sql.= ' '.(! empty($this->fk_product)?$this->fk_product:"null").',';
		$sql.= " ".$this->product_type.",";
		$sql.= " ".price2num($this->remise_percent).",";
		$sql.= " ".price2num($this->subprice).",";
		$sql.= ' '.(! empty($this->fk_remise_except)?$this->fk_remise_except:"null").',';
		$sql.= " ".(! empty($this->date_start)?"'".$this->db->idate($this->date_start)."'":"null").",";
		$sql.= " ".(! empty($this->date_end)?"'".$this->db->idate($this->date_end)."'":"null").",";
		$sql.= ' '.$this->fk_code_ventilation.',';
		$sql.= ' '.$this->rang.',';
		$sql.= ' '.$this->special_code.',';
		$sql.= ' '.(! empty($this->fk_fournprice)?$this->fk_fournprice:"null").',';
		$sql.= ' '.price2num($this->pa_ht).',';
		$sql.= " '".$this->db->escape($this->info_bits)."',";
		$sql.= " ".price2num($this->total_ht).",";
		$sql.= " ".price2num($this->total_tva).",";
		$sql.= " ".price2num($this->total_ttc).",";
		$sql.= " ".price2num($this->total_localtax1).",";
		$sql.= " ".price2num($this->total_localtax2);
		$sql.= ", " . $this->situation_percent;
		$sql.= ", " . (!empty($this->fk_prev_id)?$this->fk_prev_id:"null");
		$sql.= ", ".(!$this->fk_unit ? 'NULL' : $this->fk_unit);
		$sql.= ", ".$user->id;
		$sql.= ", ".$user->id;
		$sql.= ", ".(int) $this->fk_multicurrency;
		$sql.= ", '".$this->db->escape($this->multicurrency_code)."'";
		$sql.= ", ".price2num($this->multicurrency_subprice);
		$sql.= ", ".price2num($this->multicurrency_total_ht);
		$sql.= ", ".price2num($this->multicurrency_total_tva);
        $sql.= ", ".price2num($this->multicurrency_total_ttc);
		$sql.= ", ".$this->fk_soc;  
		$sql.= ", ".$this->fk_user;  		
		$sql.= ", ".$this->fk_vendedor;  		
		$sql.= ", ".$this->multicurrency_tx; 		
		$sql.= ')';

		dol_syslog(get_class($this)."::insert", LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$this->id=$this->db->last_insert_id(MAIN_DB_PREFIX.'facturedet_cashdespro');
			$this->rowid=$this->id;	// For backward compatibility

            if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
            {
            	$result=$this->insertExtraFields();
            	if ($result < 0)
            	{
            		$error++;
            	}
            }



			if (! $notrigger)
			{
                // Call trigger
                $result=$this->call_trigger('LINEBILL_INSERT_CASDESPRO',$user);
                if ($result < 0)
                {
					$this->db->rollback();
					return -2;
				}
                // End call triggers
			}

			$this->db->commit();
			return $this->id;
		}
		else
		{
			$this->error=$this->db->lasterror();
			$this->db->rollback();
			return -2;
		}
	}

	/**
	 *	Update line into database
	 *
	 *	@param		User	$user		User object
	 *	@param		int		$notrigger	Disable triggers
	 *	@return		int					<0 if KO, >0 if OK
	 */
	function update($user='',$notrigger=0)
	{
		global $user,$conf;

		$error=0;

		$pa_ht_isemptystring = (empty($this->pa_ht) && $this->pa_ht == ''); // If true, we can use a default value. If this->pa_ht = '0', we must use '0'.

		// Clean parameters
		$this->desc=trim($this->desc);
		if (empty($this->tva_tx)) $this->tva_tx=0;
		if (empty($this->localtax1_tx)) $this->localtax1_tx=0;
		if (empty($this->localtax2_tx)) $this->localtax2_tx=0;
		if (empty($this->localtax1_type)) $this->localtax1_type=0;
		if (empty($this->localtax2_type)) $this->localtax2_type=0;
		if (empty($this->total_localtax1)) $this->total_localtax1=0;
		if (empty($this->total_localtax2)) $this->total_localtax2=0;
		if (empty($this->remise_percent)) $this->remise_percent=0;
		if (empty($this->info_bits)) $this->info_bits=0;
		if (empty($this->special_code)) $this->special_code=0;
		if (empty($this->product_type)) $this->product_type=0;
		if (empty($this->fk_parent_line)) $this->fk_parent_line=0;
		if (! isset($this->situation_percent) || $this->situation_percent > 100 || (string) $this->situation_percent == '') $this->situation_percent = 100;
		if (empty($this->pa_ht)) $this->pa_ht=0;

		if (empty($this->multicurrency_subprice)) $this->multicurrency_subprice=0;
		if (empty($this->multicurrency_total_ht)) $this->multicurrency_total_ht=0;
		if (empty($this->multicurrency_total_tva)) $this->multicurrency_total_tva=0;
		if (empty($this->multicurrency_total_ttc)) $this->multicurrency_total_ttc=0;
		if (empty($this->fk_soc)) $this->fk_soc=0;
		if (empty($this->fk_user)) $this->fk_user=0;		
		if (empty($this->fk_vendedor)) $this->fk_vendedor=0;		
		// Check parameters
		if ($this->product_type < 0) return -1;

		// if buy price not defined, define buyprice as configured in margin admin
		if ($this->pa_ht == 0 && $pa_ht_isemptystring)
		{
			if (($result = $this->defineBuyPrice($this->subprice, $this->remise_percent, $this->fk_product)) < 0)
			{
				return $result;
			}
			else
			{
				$this->pa_ht = $result;
			}
		}

		$this->db->begin();

        // Mise a jour ligne en base
        $sql = "UPDATE ".MAIN_DB_PREFIX."facturedet_cashdespro SET";
        $sql.= " fk_facture='".$this->fk_facture."'";        
        $sql.= ", description='".$this->db->escape($this->desc)."'";
        $sql.= ", label=".(! empty($this->label)?"'".$this->db->escape($this->label)."'":"null");
        $sql.= ", subprice=".price2num($this->subprice)."";
        $sql.= ", remise_percent=".price2num($this->remise_percent)."";
        if ($this->fk_remise_except) $sql.= ", fk_remise_except=".$this->fk_remise_except;
        else $sql.= ", fk_remise_except=null";
		$sql.= ", vat_src_code = '".(empty($this->vat_src_code)?'':$this->db->escape($this->vat_src_code))."'";
        $sql.= ", tva_tx=".price2num($this->tva_tx)."";
        $sql.= ", localtax1_tx=".price2num($this->localtax1_tx)."";
        $sql.= ", localtax2_tx=".price2num($this->localtax2_tx)."";
		$sql.= ", localtax1_type='".$this->db->escape($this->localtax1_type)."'";
		$sql.= ", localtax2_type='".$this->db->escape($this->localtax2_type)."'";
        $sql.= ", qty=".price2num($this->qty);
        $sql.= ", date_start=".(! empty($this->date_start)?"'".$this->db->idate($this->date_start)."'":"null");
        $sql.= ", date_end=".(! empty($this->date_end)?"'".$this->db->idate($this->date_end)."'":"null");
        $sql.= ", product_type=".$this->product_type;
        $sql.= ", info_bits='".$this->db->escape($this->info_bits)."'";
        $sql.= ", special_code='".$this->db->escape($this->special_code)."'";
        if (empty($this->skip_update_total))
        {
        	$sql.= ", total_ht=".price2num($this->total_ht);
        	$sql.= ", total_tva=".price2num($this->total_tva);
        	$sql.= ", total_ttc=".price2num($this->total_ttc);
        	$sql.= ", total_localtax1=".price2num($this->total_localtax1);
        	$sql.= ", total_localtax2=".price2num($this->total_localtax2);
        }
		$sql.= ", fk_product_fournisseur_price=".(! empty($this->fk_fournprice)?"'".$this->db->escape($this->fk_fournprice)."'":"null");
		$sql.= ", buy_price_ht='".price2num($this->pa_ht)."'";
		$sql.= ", fk_parent_line=".($this->fk_parent_line>0?$this->fk_parent_line:"null");
		if (! empty($this->rang)) $sql.= ", rang=".$this->rang;
		$sql.= ", situation_percent=" . $this->situation_percent;
		$sql.= ", fk_unit=".(!$this->fk_unit ? 'NULL' : $this->fk_unit);
		$sql.= ", fk_user_modif =".$user->id;

		// Multicurrency
		$sql.= ", multicurrency_code='".$this->multicurrency_code."'";		
		$sql.= ", multicurrency_subprice=".price2num($this->multicurrency_subprice)."";
        $sql.= ", multicurrency_total_ht=".price2num($this->multicurrency_total_ht)."";
        $sql.= ", multicurrency_total_tva=".price2num($this->multicurrency_total_tva)."";
        $sql.= ", multicurrency_total_ttc=".price2num($this->multicurrency_total_ttc)."";
		$sql.= ", fk_soc=".$this->fk_soc."";  
		$sql.= ", fk_user=".$this->fk_user.""; 		
		$sql.= ", fk_vendedor=".$this->fk_vendedor.""; 		
		$sql.= ", multicurrency_tx=".$this->multicurrency_tx.""; 
		$sql.= " WHERE rowid = ".$this->rowid;

		dol_syslog(get_class($this)."::update", LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
        	if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
        	{
        		$this->id=$this->rowid;
        		$result=$this->insertExtraFields();
        		if ($result < 0)
        		{
        			$error++;
        		}
        	}

			if (! $error && ! $notrigger)
			{
                // Call trigger
                $result=$this->call_trigger('LINEBILL_UPDATE_CASHDESPRO',$user);
                if ($result < 0)
 				{
					$this->db->rollback();
					return -2;
				}
                // End call triggers
			}
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->error=$this->db->error();
			$this->db->rollback();
			return -2;
		}
	}

	/**
	 * 	Delete line in database
	 *  TODO Add param User $user and notrigger (see skeleton)
     *
	 *	@return	    int		           <0 if KO, >0 if OK
	 */
	function delete()
	{
		global $user;

		$this->db->begin();

		// Call trigger
		$result=$this->call_trigger('LINEBILL_DELETE_CASDESPRO',$user);
		if ($result < 0)
		{
			$this->db->rollback();
			return -1;
		}
		// End call triggers


		$sql = "DELETE FROM ".MAIN_DB_PREFIX."facturedet_cashdespro WHERE rowid = ".$this->rowid;
		dol_syslog(get_class($this)."::delete", LOG_DEBUG);
		if ($this->db->query($sql) )
		{
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->error=$this->db->error()." sql=".$sql;
			$this->db->rollback();
			return -1;
		}
	}

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
	/**
     *	Update DB line fields total_xxx
	 *	Used by migration
	 *
	 *	@return		int		<0 if KO, >0 if OK
	 */
	function update_total()
	{
        // phpcs:enable
		$this->db->begin();
		dol_syslog(get_class($this)."::update_total", LOG_DEBUG);

		// Clean parameters
		if (empty($this->total_localtax1)) $this->total_localtax1=0;
		if (empty($this->total_localtax2)) $this->total_localtax2=0;

		// Mise a jour ligne en base
		$sql = "UPDATE ".MAIN_DB_PREFIX."facturedet_cashdespro SET";
		$sql.= " total_ht=".price2num($this->total_ht)."";
		$sql.= ",total_tva=".price2num($this->total_tva)."";
		$sql.= ",total_localtax1=".price2num($this->total_localtax1)."";
		$sql.= ",total_localtax2=".price2num($this->total_localtax2)."";
		$sql.= ",total_ttc=".price2num($this->total_ttc)."";
		$sql.= " WHERE rowid = ".$this->rowid;

		dol_syslog(get_class($this)."::update_total", LOG_DEBUG);

		$resql=$this->db->query($sql);
		if ($resql)
		{
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->error=$this->db->error();
			$this->db->rollback();
			return -2;
		}
	}

}



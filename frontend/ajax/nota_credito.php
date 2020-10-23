<?php 
/**
 *  SI NOTA DE CRREDITO
 */
if(GETPOST('tipo')==2){
    $fk_facture = GETPOST('fk_facture');
    $multicurrency_tx = GETPOST('multicurrency_tx');
    $fk_soc = GETPOST('fk_soc');
    $type = GETPOST('tipo');
    $public_note = GETPOST('public_note');
    $moneda = GETPOST('moneda');
    $default_vat_code = GETPOST('default_vat_code');
    $actividad = GETPOST('actividad');
    $options_sucursal = GETPOST('options_sucursal');
    $vendedor = GETPOST('fk_vendedor');
    $usuario = GETPOST('user_author');
    $feng_codref = GETPOST('feng_codref');
    $fk_facture_source = GETPOST('fk_facture_source');
    $usuario = new User($db);
    $usuario->fetch($user->id);
    
    //creando permisos necesarios
    $usuario->rights->facture = new stdClass();
    $usuario->rights->facture->invoice_advance = new stdClass();
    $usuario->rights->facture->invoice_advance->validate=1;
    $usuario->rights->facture->creer = 1;
    
    //obteniendo factura origen
    $factura_origen = new Facture($db);
    $factura_origen->fetch($fk_facture_source);
    $factura_origen->fetch_optionals();
    
    //CREANDO FACTURA
    $fac_cash = new Facture_cashdespro($db);
    $fac_cash->fetch($fk_facture);
    //$resf = $fac_cash->fetch_lines($fk_soc,$usuario->id);
    
    $fac = new DocumentObject($db);
    $fac->socid          = $fac_cash->socid;	// Put id of third party (rowid in llx_societe table)
    $fac->date           = strtotime(date('Y-m-d'));
    $fac->note_public    = $fac_cash->public_note;
    $fac->note_private   = '';
    $fac->cond_reglement_id   = $fac_cash->cond_reglement_id;
    //$fac->cond_reglement_id   = $conf->global->POS_COND_REGLEMENT_ID_CASH;
    $fac->ref_client = $fac_cash->ref_client;
    $fac->mode_reglement_id    = $fac_cash->mode_reglement_id;
    $fac->date_livraison      = $fac_cash->date_livraison;
    //$fac->shipping_method_id  = 6;
    $fac->multicurrency_code = $fac_cash->multicurrency_code;
    $fac->multicurrency_tx = $fac_cash->multicurrency_tx;
    $fac->public_note = $fac_cash->public_note;
    $fac->fk_facture_source= $fk_facture_source;  
    $fac->user_author = $user->id;
    $fac->type = 2;
    //extrafields
    $fac->array_options[ "options_facturetype" ] = $factura_origen->array_options[ "options_facturetype" ];	
    $fac->array_options[ "options_vendedor" ] = $factura_origen->array_options[ "options_vendedor" ];
    $fac->array_options[ "options_sucursal" ] = $options_sucursal;
    $fac->array_options[ "tipo_doc" ] =GETPOST('tipo');
    $idobject=$fac->create($usuario);
    
    
    
    
    if($idobject > 0){
      $db->query('UPDATE `llx_facture` SET `act_eco` = "'.$actividad.'",`feng_codref`="'.$feng_codref.'" WHERE `llx_facture`.`rowid` = '.$idobject.'');
    
    //llenando lineas
    $sq = 'SELECT * FROM llx_facturedet_cashdespro WHERE fk_facture='.GETPOST('fk_facture').'';
    $sql = $db->query($sq);
    
    while($obj = $db->fetch_object($sql)){
      if($obj->fk_product > 0){
        $type = 0;
      }else{
      $type = 1;  
      }
      $pu_ht = $obj->subprice; 
      $qty = $obj->qty; 
      $txtva = $obj->tva_tx; 
      $txlocaltax1=0; 
      $txlocaltax2=0; 
      $fk_product=$obj->fk_product;
      $remise_percent=$obj->remise_percent; 
      $date_start=''; 
      $date_end=''; 
      $ventil=0; 
      $info_bits=0;
      $fk_remise_except='';
      $price_base_type='HT'; 
      $pu_ttc=0; 
      $type=$type; 
      $rang=-1; 
      $special_code=0;
      $origin=''; 
      $origin_id=0; 
      $fk_parent_line=0; 
      $fk_fournprice=null; 
      $pa_ht=0;
      $label=''; 
      $array_options=0; 
      $situation_percent=100; 
      $fk_prev_id=0; 
      $fk_unit = null;
      $pu_ht_devise = $obj->multicurrency_subprice;
      
      $resp = $fac->addline($desc, $pu_ht, $qty, $txtva, $txlocaltax1, $txlocaltax2, $fk_product, $remise_percent, $date_start, $date_end, $ventil, $info_bits, $fk_remise_except, $price_base_type, $pu_ttc, $type, $rang, $special_code0, $origin, $origin_id, $fk_parent_line, $fk_fournprice, $pa_ht, $label, $array_options, $situation_percent, $fk_prev_id, $fk_unit, $pu_ht_devise);

      $db->query('UPDATE `llx_facturedet` SET `codtax` = "01",vat_src_code="'.$default_vat_code.'" WHERE `llx_facturedet`.`rowid` = '.$resp.'');
        
    }
    
    $cashid = $_SESSION['TERMINAL_ID'];
    $cash = new Cash($db);
    $cash->fetch($cashid);
    $idwarehouse = $cash->fk_warehouse;
    

    
    
    
    $fac->fetch($idobject);
    $fac->fetchFE($idobject);
    $fac->fetch_optionals();
    $fac->user_author = $usuario->id;
    $res = $fac->validate($usuario,'', $idwarehouse);
    
    if($res > 0){
        if($fk_facture == 0){ 
            $sq = 'DELETE FROM llx_facturedet_cashdespro WHERE fk_facture = 0 AND fk_soc='.$fk_soc.' AND fk_vendedor='.$vendedor.'';
            $resl = $db->query($sq);   
            }else{
              $sq = 'DELETE FROM llx_facture_cashdespro WHERE rowid = '.$_POST['fk_facture'].'';
              $resl = $db->query($sq);
              if($resl){
              $sq = 'DELETE FROM llx_facturedet_cashdespro WHERE fk_facture = '.$_POST['fk_facture'].'';
              $resl = $db->query($sq);    
              }
            } 


    //guardando relacion de facturas echas desde el cash
    set_facture_log($cash->id,$fk_cierre,$idobject,$idwarehouse,$type,$usuario->id);
    //fin guardando relacion de gcarueas echas desde el cash  
    
    
      // Convertir en reduc
        $object = new Facture($db);
            $object->fetch($idobject);
            $object->fetch_thirdparty();
            //$object->fetch_lines();	// Already done into fetch
    
            // Check if there is already a discount (protection to avoid duplicate creation when resubmit post)
            $discountcheck=new DiscountAbsolute($db);
            $result=$discountcheck->fetch(0,$object->id);
    
            $canconvert=0;
            if ($object->type == Facture::TYPE_DEPOSIT && empty($discountcheck->id)) $canconvert=1;	// we can convert deposit into discount if deposit is payed (completely, partially or not at all) and not already converted (see real condition into condition used to show button converttoreduc)
            if (($object->type == Facture::TYPE_CREDIT_NOTE || $object->type == Facture::TYPE_STANDARD) && $object->paye == 0 && empty($discountcheck->id)) $canconvert=1;	// we can convert credit note into discount if credit note is not payed back and not already converted and amount of payment is 0 (see real condition into condition used to show button converttoreduc)
            if ($canconvert)
            {
                $db->begin();
    
                $amount_ht = $amount_tva = $amount_ttc = array();
                $multicurrency_amount_ht = $multicurrency_amount_tva = $multicurrency_amount_ttc = array();
    
                // Loop on each vat rate
                $i = 0;
                foreach ($object->lines as $line)
                {
                    if ($line->product_type < 9 && $line->total_ht != 0) // Remove lines with product_type greater than or equal to 9
                    { 	// no need to create discount if amount is null
                        $amount_ht[$line->tva_tx] += $line->total_ht;
                        $amount_tva[$line->tva_tx] += $line->total_tva;
                        $amount_ttc[$line->tva_tx] += $line->total_ttc;
                        $multicurrency_amount_ht[$line->tva_tx] += $line->multicurrency_total_ht;
                        $multicurrency_amount_tva[$line->tva_tx] += $line->multicurrency_total_tva;
                        $multicurrency_amount_ttc[$line->tva_tx] += $line->multicurrency_total_ttc;
                        $i++;
                    }
                }
    
                // Insert one discount by VAT rate category
                $discount = new DiscountAbsolute($db);
                if ($object->type == Facture::TYPE_CREDIT_NOTE)
                    $discount->description = '(CREDIT_NOTE)';
                elseif ($object->type == Facture::TYPE_DEPOSIT)
                    $discount->description = '(DEPOSIT)';
                elseif ($object->type == Facture::TYPE_STANDARD || $object->type == Facture::TYPE_REPLACEMENT || $object->type == Facture::TYPE_SITUATION)
                    $discount->description = '(EXCESS RECEIVED)';
                else {
                    setEventMessages($langs->trans('CantConvertToReducAnInvoiceOfThisType'), null, 'errors');
                }
                $discount->fk_soc = $object->socid;
                $discount->fk_facture_source = $object->id;
    
                $error = 0;
    
                if ($object->type == Facture::TYPE_STANDARD || $object->type == Facture::TYPE_REPLACEMENT || $object->type == Facture::TYPE_SITUATION)
                {
                    // If we're on a standard invoice, we have to get excess received to create a discount in TTC without VAT
    
                    // Total payments
                    $sql = 'SELECT SUM(pf.amount) as total_paiements';
                    $sql.= ' FROM '.MAIN_DB_PREFIX.'paiement_facture as pf, '.MAIN_DB_PREFIX.'paiement as p';
                    $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_paiement as c ON p.fk_paiement = c.id';
                    $sql.= ' WHERE pf.fk_facture = '.$object->id;
                    $sql.= ' AND pf.fk_paiement = p.rowid';
                    $sql.= ' AND p.entity IN (' . getEntity('facture').')';
                    $resql = $db->query($sql);
                    if (! $resql) dol_print_error($db);
    
                    $res = $db->fetch_object($resql);
                    $total_paiements = $res->total_paiements;
    
                    // Total credit note and deposit
                    $total_creditnote_and_deposit = 0;
                            $sql = "SELECT re.rowid, re.amount_ht, re.amount_tva, re.amount_ttc,";
                            $sql .= " re.description, re.fk_facture_source";
                            $sql .= " FROM " . MAIN_DB_PREFIX . "societe_remise_except as re";
                            $sql .= " WHERE fk_facture = " . $object->id;
                            $resql = $db->query($sql);
                            if (!empty($resql)) {
                                    while ($obj = $db->fetch_object($resql)) $total_creditnote_and_deposit += $obj->amount_ttc;
                            } else dol_print_error($db);
    
                    $discount->amount_ht = $discount->amount_ttc = $total_paiements + $total_creditnote_and_deposit - $object->total_ttc;
                    $discount->amount_tva = 0;
                    $discount->tva_tx = 0;
    
                    $result = $discount->create($user);
                    if ($result < 0)
                    {
                        $error++;
                    }
                }
                if ($object->type == Facture::TYPE_CREDIT_NOTE || $object->type == Facture::TYPE_DEPOSIT)
                {
                    foreach ($amount_ht as $tva_tx => $xxx)
                    {
                        $discount->amount_ht = abs($amount_ht[$tva_tx]);
                        $discount->amount_tva = abs($amount_tva[$tva_tx]);
                        $discount->amount_ttc = abs($amount_ttc[$tva_tx]);
                        $discount->multicurrency_amount_ht = abs($multicurrency_amount_ht[$tva_tx]);
                        $discount->multicurrency_amount_tva = abs($multicurrency_amount_tva[$tva_tx]);
                        $discount->multicurrency_amount_ttc = abs($multicurrency_amount_ttc[$tva_tx]);
                        $discount->tva_tx = abs($tva_tx);
    
                        $result = $discount->create($user);
                        if ($result < 0)
                        {
                            $error++;
                            break;
                        }
                    }
                }
    
                if (empty($error))
                {
                    if($object->type != Facture::TYPE_DEPOSIT) {
                        // Classe facture
                        $result = $object->set_paid($user);
                        if ($result >= 0)
                        {
                            $db->commit();
                        }
                        else
                        {
                            setEventMessages($object->error, $object->errors, 'errors');
                            $db->rollback();
                        }
                    } else {
                        $db->commit();
                    }
                }
                else
                {
                    setEventMessages($discount->error, $discount->errors, 'errors');
                    $db->rollback();
                }
            }
    
    
        // fin Convertir en reduc
      }
      $data = ['id'=>$idobject,'tipo'=>GETPOST('tipo'),'error'=>0,'msg'=>''];
      echo json_encode($data); 
       
    
      
    }
     else{
      $data = ['id'=>$idobject,'tipo'=>GETPOST('tipo'),'error'=>1,'msg'=>$fac->error];
      echo json_encode($data); 
         }
    
    }  
    /**
     * FIN SI NOTA DE CRREDITO
     */ 
?>
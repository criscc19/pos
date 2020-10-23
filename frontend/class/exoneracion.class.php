<?php
class PosExoneracion{

    var $socid;
    var $tipoDocumento;
    var $descripcionDocumento;
    var $author;
    var $numeroDocumento;
    var $nombreInstitucion;
    var $fechaEmision;
    var $porcentaje;
    var $productList;
    var $productListRef;
    var $status;
    var $datec;
    var $dateu;
    var $arrayObject;

    private $db;

    function __construct($db){
        $this->db = $db;
    }

    function create(){
        global $user;

        $this->delete();

        $now = date('Y-m-d H:i:s');

        $sql='INSERT INTO `llx_facturaelectronica_societe_exonerado`(`fk_soc`, `tipo_dococumento`, `descripcion_documento`, `numero_documento`, `fk_author`, `nombre_institucion`, `fecha_emision`, `porcentaje`, `product_list`, `status`, `datec`, `dateu`) VALUES (';
        $sql.=$this->socid.',';
        $sql.='"'.$this->tipoDocumento.'",';
        $sql.='"'.$this->descripcionDocumento.'",';
        $sql.='"'.$this->numeroDocumento.'",';
        $sql.=''.$user->id.',';
        $sql.='"'.$this->nombreInstitucion.'",';
        $sql.='"'.$this->fechaEmision.'",';
        $sql.=''.$this->porcentaje.',';
        $sql.='"'.$this->productList.'",';
        $sql.=''.$this->status.',';
        $sql.='"'.$now.'",';
        $sql.='"'.$now.'")';
        //echo $sql;
        $result = $this->db->query($sql);

        if($result){
            return 1;
        }else{
            return -1;
        }
    }


    function delete(){
        $sql='DELETE FROM `llx_facturaelectronica_societe_exonerado` WHERE `fk_soc` = '.$this->socid.' AND `numero_documento` = "'.$this->numeroDocumento.'"';

        $result = $this->db->query($sql);

        if($result){
            return 1;
        }else{
            return -1;
        }
    }

    function getTva($lineid,$tva,$element = 'facturedet'){
        $sql='SELECT `exo_tva` FROM llx_'.$element.' WHERE `rowid` = '.$lineid;
       
        $result = $this->db->query($sql);
        if ($result)
        {
            if ($this->db->num_rows($result))
            {
                $obj = $this->db->fetch_object($result);
               
                return (!empty($obj->exo_tva)) ? $obj->exo_tva : $tva;
            }else{
                return $tva;
            }
        }else{
            return $tva;
        }
    }

    function fetchExtraObjects($id,$element = 'facture'){
        $sql = 'SELECT `multicurrency_currency_code`, `multicurrency_currency`, `multicurrency_code`, `multicurrency_tx`, (SELECT SUM(dd.total_tva) FROM llx_facturaelectronica_'.$element.'det dd WHERE dd.fk_object = '.$id.') AS revenuestamp FROM '.MAIN_DB_PREFIX.$element.' WHERE rowid = '.$id;
        //echo $sql;
        $result = $this->db->query($sql);
        if ($result)
        {
            if ($this->db->num_rows($result))
            {
                $obj = $this->db->fetch_object($result);
                $obj2 = new StdClass();
                $obj2->multicurrency_currency_code = (!empty($obj->multicurrency_currency_code)) ? $obj->multicurrency_currency_code : $obj->multicurrency_code;
                        
                $obj2->multicurrency_tx = (!empty($obj->multicurrency_currency)) ? $obj->multicurrency_currency : $obj->multicurrency_tx;

                $obj2->revenuestamp = (!empty($obj->revenuestamp)) ? price2num($obj->revenuestamp,'MU') : 0;
                return $obj2;
            }else{
                return -2;
            }
        }else{
            return -1;
        }
    }


    function fetch($numero_documento,$refProduct = 1){
        $sql='SELECT `fk_soc` as socid, `tipo_dococumento`, `descripcion_documento`, `numero_documento`, `nombre_institucion`, `fecha_emision`, `porcentaje`,`product_list`, `status`, `datec`, `dateu`, `fk_author` FROM `llx_facturaelectronica_societe_exonerado` WHERE `numero_documento` = "'.$numero_documento.'"';

        $result = $this->db->query($sql);
        
        $num = $this->db->num_rows($result);

        if($result && $num > 0){
            while ($obj = $this->db->fetch_object($result)) {
                # code...
                $exo = new Exoneracion($this->db);
                $exo->socid = $obj->socid;
                $exo->tipoDocumento = $obj->tipo_dococumento;
                $exo->descripcionDocumento = $obj->descripcion_documento;
                $exo->numeroDocumento = $obj->numero_documento;
                $exo->nombreInstitucion = $obj->nombre_institucion;
                $exo->fechaEmision = $obj->fecha_emision;
                $exo->porcentaje = $obj->porcentaje;
                $exo->productList = $obj->product_list;
                $exo->author = $obj->fk_author;

                if($refProduct == 1){

                    if($obj->product_list != 'all'){
                        $sql='SELECT ref as product FROM `llx_product` WHERE rowid IN ('.$obj->product_list.')';
                        //echo $sql;
                        $result2 = $this->db->query($sql);
                        
                        $num = $this->db->num_rows($result2);
                        $arrayList = array();
                        if($result2 && $num > 0){
                            while ($obj2 = $this->db->fetch_object($result2)) {
                                $arrayList[] = $obj2->product;
                            }
                        }

                        if(count($arrayList) > 0){
                            $exo->productListRef = implode(', ',$arrayList);
                        }
                    }else{
                        $exo->productListRef = "Todos los productos disponibles a la venta.";
                    }
            
                }

                $exo->status = $obj->status;
                $exo->datec = $obj->datec;
                $exo->dateu = $obj->dateu;                

                $this->arrayObject[] = $exo;
            }
        }
    }


    function fetchAll($socid,$refProduct = 1){
        $sql='SELECT `fk_soc` as socid, `tipo_dococumento`, `descripcion_documento`, `numero_documento`, `nombre_institucion`, `fecha_emision`, `porcentaje`,`product_list`, `status`, `datec`, `dateu`, `fk_author` FROM `llx_facturaelectronica_societe_exonerado` WHERE `fk_soc` = '.$socid;

        $result = $this->db->query($sql);
        
        $num = $this->db->num_rows($result);

        if($result && $num > 0){
            while ($obj = $this->db->fetch_object($result)) {
                # code...
                $exo = new Exoneracion($this->db);
                $exo->socid = $obj->socid;
                $exo->tipoDocumento = $obj->tipo_dococumento;
                $exo->descripcionDocumento = $obj->descripcion_documento;
                $exo->numeroDocumento = $obj->numero_documento;
                $exo->nombreInstitucion = $obj->nombre_institucion;
                $exo->fechaEmision = $obj->fecha_emision;
                $exo->porcentaje = $obj->porcentaje;
                $exo->productList = $obj->product_list;
                $exo->author = $obj->fk_author;

                if($refProduct == 1){

                    if($obj->product_list != 'all'){
                        $sql='SELECT ref as product FROM `llx_product` WHERE rowid IN ('.$obj->product_list.')';
                        //echo $sql;
                        $result2 = $this->db->query($sql);
                        
                        $num = $this->db->num_rows($result2);
                        $arrayList = array();
                        if($result2 && $num > 0){
                            while ($obj2 = $this->db->fetch_object($result2)) {
                                $arrayList[] = $obj2->product;
                            }
                        }

                        if(count($arrayList) > 0){
                            $exo->productListRef = implode(', ',$arrayList);
                        }
                    }else{
                        $exo->productListRef = "Todos los productos disponibles a la venta.";
                    }
            
                }

                $exo->status = $obj->status;
                $exo->datec = $obj->datec;
                $exo->dateu = $obj->dateu;                

                $this->arrayObject[] = $exo;
            }
        }
    }

    function searchProduct($value){
        $sql='SELECT rowid as id, CONCAT(ref," - ",label) as label FROM `llx_product` WHERE (ref LIKE "%'.$value.'%" OR label LIKE "%'.$value.'%") AND tosell = 1 LIMIT 20';
        //echo $sql;
        $result = $this->db->query($sql);
        
        $num = $this->db->num_rows($result);

        $arrayObject = array();

        if($result && $num > 0){
            while ($obj = $this->db->fetch_object($result)) {
                # code...
                $arrayObject[$obj->id] = $obj->label;
            }
        }

        return $arrayObject;
    }

    function updateCamposDet($id,$element,$tipo_dococumento='',$numero_documento='',$nombre_institucion='',$fecha_emision='null',$porcentaje='0',$monto_exoneracion='0',$exo_tva='0',$exo_total_ht='0',$exo_total_tva='0',$exo_total_ttc='0',$multicurrency_exo_total_ht='0',$multicurrency_exo_total_tva='0',$multicurrency_exo_total_ttc='0',$multicurrency_monto_exoneracion=0,$codtax='',$devimp=0,$vat_src_code=''){

        if(in_array($element,array('facture_fourn_det','commande_fournisseurdet'))){
            $sql='UPDATE '.MAIN_DB_PREFIX.$element.' SET `codtax`="'.$codtax.'", `vat_src_code`="'.$vat_src_code.'" WHERE `rowid` = '.$id;
        }else{
            $sql='UPDATE '.MAIN_DB_PREFIX.$element.' SET `tipo_dococumento`="'.$tipo_dococumento.'",`numero_documento`="'.$numero_documento.'",`nombre_institucion`="'.$nombre_institucion.'",`fecha_emision`="'.$fecha_emision.'",`porcentaje`='.$porcentaje.',`monto_exoneracion`='.$monto_exoneracion.',`exo_tva`='.$exo_tva.',`exo_total_ht`='.$exo_total_ht.',`exo_total_tva`='.$exo_total_tva.',`exo_total_ttc`='.$exo_total_ttc.',`multicurrency_exo_total_ht`='.$multicurrency_exo_total_ht.',`multicurrency_exo_total_tva`='.$multicurrency_exo_total_tva.',`multicurrency_exo_total_ttc`='.$multicurrency_exo_total_ttc.',`multicurrency_monto_exoneracion`='.$multicurrency_monto_exoneracion.',`codtax`="'.$codtax.'",`devimp`='.$devimp.' WHERE `rowid` = '.$id;
        }

        $result = $this->db->query($sql);
    }

}

?>
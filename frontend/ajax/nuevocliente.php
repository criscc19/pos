<?php 
	require '../../../main.inc.php';
	require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$object = new Societe($db);
		//$extralabels=$extrafields->fetch_name_optionals_label('societe');
			if($_POST['action']=='crear'){  
    			$mascara = $conf->global->COMPANY_ELEPHANT_MASK_CUSTOMER;
				$element = 'societe';
				$referencia = 'code_client';
				$numero = get_next_value($db,$mascara,$element,$referencia ,$where,$soc,$obj->date,'next');

				$sql = 'SELECT * FROM llx_societe WHERE siren="'.$_POST['cedula'].'"';
				$sql = $db->query($sql);
				if($db->num_rows($sql) > 0){
					$datos = ['id'=>-2,'error'=>'El cliente ya existe con ese numero de cedula','errors'=>''];
					echo json_encode($datos);
				}else{


				$soc = new Societe($db);
				$soc->nom = GETPOST('firstname');
				$soc->forme_juridique_code = GETPOST('tipo_cedula');
    			$soc->idprof1 =GETPOST('cedula');
				$soc->firstname = GETPOST('firstname');
				$soc->lastname = GETPOST('lastname');
				$soc->email = GETPOST('email');
                $soc->name_alias = GETPOST('lastname');
				$soc->country_id = 75;
				$soc->phone = GETPOST('phone');
				$soc->address = GETPOST('direccion');	
				$soc->client = 1;
				$soc->fournisseur = 0;
				$soc->status = 1;
				/*var_dump(GETPOST('provincia'));
				var_dump(GETPOST('canton'));
				var_dump(GETPOST('distrito'));*/
				$soc->array_options["options_provincia"]= GETPOST('provincia');
				$soc->array_options["options_canton"]= GETPOST('canton');
				$soc->array_options["options_distrito"]= GETPOST('distrito');
				$soc->array_options["options_barrio"]= GETPOST('barrio');

				$soc->provincia = GETPOST('provincia');
				$soc->canton = GETPOST('canton');
				$soc->distrito = GETPOST('distrito');
				$soc->barrio = GETPOST('barrio');
				$soc->code_client = $numero;
				//$ret = $extrafields->setOptionalsFromPost($extralabels,$soc);
    			$societe = $soc->create($user);
    			//a la fuerza en la tabla del cliente 
				$sq = 'UPDATE llx_societe SET provincia='.GETPOST('provincia').',canton='.GETPOST('canton').',distrito='.GETPOST('distrito').',barrio='.GETPOST('barrio').'';
				$result = $db->query($sq);
                //var_dump($soc->errors,$soc->error);
                //var_dump($soc->error,$soc->errors);
        
					if($societe > 0){
					$datos = ['id'=>$societe,'nom'=>GETPOST('firstname'),'name_alias'=>GETPOST('lastname')];
					echo json_encode($datos);
					}else{
						$datos = ['id'=>$societe,'error'=>$soc->error,'errors'=>$soc->errors];
						echo json_encode($datos);
					}

				}
					
        	}
        
?>
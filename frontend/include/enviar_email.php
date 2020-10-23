<?php

require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
require_once(DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php');
require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php');
require_once(DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php');
require_once(DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php');
require_once(DOL_DOCUMENT_ROOT."/feng/class/feng.document.class.php");
include_once(DOL_DOCUMENT_ROOT.'/feng/includes/pref_document.php');


function enviar_correo($fk_facture){
global $db,$user,$langs,$conf,$mysoc;
//inciando objeto factura
$object = New Facture($db);
$object->fetch($fk_facture);
//inciando objeto para plantilla
$form_mail = New Formmail($db);
//iniciando variable de lenguaje
$outputlangs = $langs;
//ruta para poder adjuntar los archivos
$diroutput = $conf->facture->dir_output;
$empresa = $mysoc->email;
$object->fetch_thirdparty();

$cliente = $object->thirdparty->email;
if(empty($cliente))
	$cliente = 'desarrollo4@ng.cr';

/*
BLOUE PARA CARGAR LAS SUSTITUCIONES DE PLANTILLA
*/
	//$arrayoffamiliestoexclude=array('system', 'mycompany', 'object', 'objectamount', 'date', 'user', ...);
	if (! isset($arrayoffamiliestoexclude)) $arrayoffamiliestoexclude=null;

	// Make substitution in email content
	$substitutionarray = getCommonSubstitutionArray($outputlangs, 0, $arrayoffamiliestoexclude, $object);
	$substitutionarray['__CHECK_READ__'] = (is_object($object) && is_object($object->thirdparty)) ? '<img src="' . DOL_MAIN_URL_ROOT . '/public/emailing/mailing-read.php?tag=' . $object->thirdparty->tag . '&securitykey=' . urlencode($conf->global->MAILING_EMAIL_UNSUBSCRIBE_KEY) . '" width="1" height="1" style="width:1px;height:1px" border="0"/>' : '';
	$substitutionarray['__PERSONALIZED__'] = '';	// deprecated
	$substitutionarray['__CONTACTCIVNAME__'] = '';
	$parameters = array('mode' => 'formemail'
	);
complete_substitutions_array($substitutionarray, $outputlangs, $object, $parameters);
/*
BLOUE PARA CARGAR LAS SUSTITUCIONES DE PLANTILLA
*/

//obteniendo la plantilla de factura
$plantilla = $form_mail->getEMailTemplate($db, 'facture_send', $user, $outputlangs, 0, 1, $label='');

/*
BLOUE PARA REEMPLAZAR LAS SUSTITUCIONES DE PLANTILLA
*/
$mensaje = $plantilla->content;
$titulo = $plantilla->topic;
foreach($substitutionarray as $k=>$sb){
	$mensaje = str_replace($k, $sb, $mensaje);
	$titulo = str_replace($k, $sb, $titulo);	
}
$plantilla->content = $mensaje;
$plantilla->topic = $titulo;

/*
FIN BLOUE PARA REEMPLAZAR LAS SUSTITUCIONES DE PLANTILLA
*/

/*
BLOUE PARA OBTEBER LOS ARCHIVOS ADJUNTOS
*/
$ref = dol_sanitizeFileName($object->ref);
include_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
$fileparams = dol_most_recent_file($diroutput . '/' . $ref, preg_quote($ref, '/').'[^\-]+');
//
if ($object->element == 'invoice_supplier')
{
	$fileparams = dol_most_recent_file($diroutput . '/' . get_exdir($object->id,2,0,0,$object,$object->element).$ref, preg_quote($ref,'/').'([^\-])+');
}

$file = $fileparams['fullname'];

/*
FIN BLOUE PARA OBTEBER LOS ARCHIVOS ADJUNTOS
*/

/*
BLOUE PARA OBTEBER LOS ARCHIVOS ADJUNTOS XML
*/
//objeto para obtener la clave numerica
$fengxml=new DocumentObject($db);
$statusFeng = $fengxml->returnStat($object->id);
include_once(DOL_DOCUMENT_ROOT.'/feng/includes/pref_document.php');
$name = $object->ref.preg_replace('/[^0-9 \?!]/', '', $statusFeng->NumConsecutivoCompr).'.xml';
$file_xml = DOL_MAIN_URL_ROOT.'/feng/documentviewer.php?clavenumerica='.$statusFeng->claveNumerica.'&document=1&facid='.$object->id.'&download=0&name='.$pref.$statusFeng->claveNumerica;
$file_RMH =DOL_MAIN_URL_ROOT.'/feng/documentviewer.php?clavenumerica='.$statusFeng->claveNumerica.'&document=2&facid='.$object->id.'&download=0&name='.'RMH-'.$statusFeng->claveNumerica;
/*
FIN BLOUE PARA OBTEBER LOS ARCHIVOS ADJUNTOS XML
*/

//ARRAY DE ARCHIVOS ADJUNTOS
$files[]=$file;
if($statusFeng->claveNumerica !=''){
$files[]=$file_xml;
$files[]=$file_RMH;	
}


$types[]=dol_mimetype($file);
if($statusFeng->claveNumerica !=''){
$types[]=dol_mimetype($file_xml);
$types[]=dol_mimetype($file_RMH);
}

$file_names[]=basename($file);
if($statusFeng->claveNumerica !=''){
$file_names[]=$pref.$statusFeng->claveNumerica.'.xml';
$file_names[]='RMH-'.$statusFeng->claveNumerica.'.xml';
}

/*  var_dump($file_names);exit;
var_dump($name);exit;
var_dump($plantilla);exit;
var_dump($substitutionarray);exit; */
$newsubject=$plantilla->topic;
$sendto=$cliente;
$from=''.$mysoc->name.' <'.$mysoc->email.'>';
$newmessage=$plantilla->content;
$errorsto = 'criscc19@hotmail.com';
$msgishtml=1;
$mail = new CMailFile(
$newsubject,
$sendto,
$from,
$newmessage,
$files,
$types,
$file_names,
'',
'',
0,
$msgishtml,
$errorsto
);
$res=$mail->sendfile();
return $res;
}


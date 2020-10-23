<?php
$res=@include("../../main.inc.php");                                   // For root directory
if (! $res) $res=@include("../../../main.inc.php");                // For "custom" directory

dol_include_once('/pos/backend/class/pos.class.php');
//dol_include_once('/pos/frontend/class/mobile_detect.php');
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture-rec.class.php';
require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/modules/facture/modules_facture.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/discount.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formmargin.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/invoice.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
require_once(DOL_DOCUMENT_ROOT."/pos/frontend/include/funciones.php");
global $db, $langs,$conf;
$langs->load("pos@pos");
$langs->load("rewards@rewards");
$langs->load("bills");
$langs->load("companies");
if(empty($_SESSION['login']) || empty($_SESSION['TERMINAL_ID']))
{
	accessforbidden();
}

$logo = DOL_MAIN_URL_ROOT.'/viewimage.php?modulepart=mycompany&file=logos/thumbs/'.$mysoc->logo_small; 

$cashid = $_SESSION['TERMINAL_ID'];
$cash = new Cash($db);
$cash->fetch($cashid);
$warehouse = $cash->fk_warehouse;

$bank = New Account($db);


$bank->fetch($cash->fk_paybank);
$banco1_moneda = $bank->currency_code;
if($banco1_moneda=='CRC'){$signo1 = 'CRC';$color1='black';$color1_2='#dcdcdc';}
if($banco1_moneda=='USD'){$signo1 = 'USD';$color1='green';$color1_2='#d7fbd7';}

$bank->fetch($cash->fk_paybank_extra);
$banco2_moneda = $bank->currency_code;
if($banco2_moneda=='CRC'){$signo2 = 'CRC';$color2='black';$color2_2='#dcdcdc';}
if($banco2_moneda=='USD'){$signo2 = 'USD';$color2='green';$color2_2='#d7fbd7';}

$cliente = new Societe($db);
$cliente->fetch($cash->fk_soc);
$pendiente = $cliente->getOutstandingBills();


$cur = new Multicurrency($db);
$cur->fetch($conf->global->ID_MULTIMONEDA);
$cambio = $cur->rates[0]->rate;
$multicurrency_tx = $cambio; 
$cambio_dolar = 1/$cambio;

$control = new ControlCash($db,$_SESSION['TERMINAL_ID']);
(int)$open = $control->get_cash_id_open($user->id); 

$control->fetch((int)$open);

$form = New Form($db);

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
    <link rel="stylesheet" type="text/css" href="<?php print DOL_URL_ROOT ?>/includes/jquery/plugins/select2/dist/css/select2.css?layout=classic&version=9.0.2">
	</head>
 <body class="fixed-sn mdb-skin">
<?php include('tpl/menu2.tpl.php') ?>

    <div class="container-fluid ">
    <?php include('tpl/doc_success_propal.tpl.php') ?>
    </div>
  <!--Main Layout-->
<?php 
llxFooter();
$db->close();
?>  
 </body>
</html> 
<script type='text/javascript' src='https://unpkg.com/@popperjs/core@2'></script>
<script type='text/javascript' src='js/jquery.easy-autocomplete.js'></script>
<script type='text/javascript' src='js/bootstrap.min.js'></script>
<script type='text/javascript' src='js/mdb.min.js'></script>
<script src="sweetalert2/dist/sweetalert2.min.js"></script>
<?php print '<script type="text/javascript" src="'.DOL_URL_ROOT.'/includes/ckeditor/ckeditor/ckeditor.js?layout=classic&version=9.0.2"></script>'."\n"; ?>
<script type="text/javascript" src="<?php print DOL_URL_ROOT ?>/includes/jquery/plugins/select2/dist/js/select2.full.min.js?layout=classic&version=9.0.2"></script>
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

<script type="text/javascript">var CKEDITOR_BASEPATH = '<?php print DOL_URL_ROOT ?>/includes/ckeditor/ckeditor/';
var ckeditorConfig = '<?php print DOL_URL_ROOT ?>/theme/md/ckeditor/config.js?layout=classic&version=9.0.2';
var ckeditorFilebrowserBrowseUrl = '<?php print DOL_URL_ROOT ?>/core/filemanagerdol/browser/default/browser.php?Connector=/pruebas/core/filemanagerdol/connectors/php/connector.php';
var ckeditorFilebrowserImageBrowseUrl = '<?php print DOL_URL_ROOT ?>/core/filemanagerdol/browser/default/browser.php?Type=Image&Connector=/pruebas/core/filemanagerdol/connectors/php/connector.php';
</script>

<script type="text/javascript">
$(".button-collapse").sideNav();

/* $('div.center').remove();*/
$('.button').addClass('btn btn-primary btn-sm waves-effect waves-light');
$('select').addClass('browser-default custom-select');
$('#sendto').addClass('form-control');
$('#sendto').attr('style','width: 85%;float: left;');
$('#subject').addClass('form-control');
 
            			$(document).ready(function () {
                            /* if (CKEDITOR.loadFullCore) CKEDITOR.loadFullCore(); */
                            /* should be editor=CKEDITOR.replace but what if serveral editors ? */
                            CKEDITOR.replace('message',
            					{
            						/* property:xxx is same than CKEDITOR.config.property = xxx */
            						customConfig : ckeditorConfig,
            						readOnly : false,
                            		htmlEncodeOutput :false,
            						allowedContent :false,
            						extraAllowedContent : '',
            						fullPage : false,
                            		toolbar: 'dolibarr_notes',
            						toolbarStartupExpanded: true,
            						width: '',
            						height: 280,
                                    skin: 'moono-lisa',
                                    language: 'es_ES',
                                    textDirection: 'ltr',
                                    on :
                                            {
                                                instanceReady : function( ev )
                                                {
                                                    // Output paragraphs as <p>Text</p>.
                                                    this.dataProcessor.writer.setRules( 'p',
                                                        {
                                                            indent : false,
                                                            breakBeforeOpen : true,
                                                            breakAfterOpen : false,
                                                            breakBeforeClose : false,
                                                            breakAfterClose : true
                                                        });
                                                }
                                            },
    filebrowserBrowseUrl : ckeditorFilebrowserBrowseUrl,    filebrowserImageBrowseUrl : ckeditorFilebrowserImageBrowseUrl,
    filebrowserWindowWidth : '900',
                               filebrowserWindowHeight : '500',
                               filebrowserImageWindowWidth : '900',
                               filebrowserImageWindowHeight : '500'	})});
</script>
<?php
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';	
include_once DOL_DOCUMENT_ROOT .'/core/lib/files.lib.php';
include_once DOL_DOCUMENT_ROOT .'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
class imagenes extends CommonObject{
	
	public function __construct(DoliDB $db)
	{
		global $conf, $langs, $user;
        $this->db = $db;
	}
	
function productImage($index,$fk_product){
global $conf, $langs, $user;	
$prod = new Product($this->db);
$prod->fetch($fk_product);
//obteniendo imagen
$sdir = $conf->product->multidir_output[$prod->entity];
$dir = $sdir . '/';
$pdir = '/';
$dir .= get_exdir(0,0,0,0,$prod,'product').$prod->ref.'/';
$pdir .= get_exdir(0,0,0,0,$prod,'product').$prod->ref.'/';


// Defined relative dir to DOL_DATA_ROOT
$relativedir = '';

if ($dir)
{
$relativedir = preg_replace('/^'.preg_quote(DOL_DATA_ROOT,'/').'/', '', $dir);
$relativedir = preg_replace('/^[\\/]/','',$relativedir);
$relativedir = preg_replace('/[\\/]$/','',$relativedir);
}

$dirthumb = $dir.'thumbs/';
$pdirthumb = $pdir.'thumbs/';

$return ='<!-- Photo -->'."\n";
$nbphoto=0;

$filearray=dol_dir_list($dir,"files",0,'','(\.meta|_preview.*\.png)$',$sortfield,(strtolower($sortorder)=='desc'?SORT_DESC:SORT_ASC),1);
$img = $filearray[$index]['name'];
$ext = explode('.',$filearray[$index]['name']);
$img_nom = $ext[$index];
$img_ext = $ext[$index];

//obteniendo url publica
include_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmfiles.class.php';
$ecmfile=new EcmFiles($this->db);
$result = $ecmfile->fetch('','', 'produit/'.$prod->ref.'/'.$img.'', '', '', $hashp);
if($ecmfile->share == ''){
require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
$ecmfile->share = getRandomPassword(true);
$ecmfile->update($user);  	   			   
}
//fin obteniendo url publica

if(count($filearray) > 0){
if($_SERVER['HTTPS'] == 'on'){$protocol = 'https://';}else{$protocol = 'http://';}	
$url = $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/documents/produit/'.$prod->ref.'/'.rawurlencode($img).'';	
$realpath = DOL_MAIN_URL_ROOT.'/viewimage.php?modulepart=product&entity='.$prod->entity.'&file='.urlencode($prod->ref).'/'.urlencode($img).'&hashp='.$ecmfile->share.'';
$realpath_thumb = DOL_MAIN_URL_ROOT.'/viewimage.php?modulepart=product&entity='.$prod->entity.'&file='.urlencode($prod->ref).'/thumbs/'.urlencode($img_nom.'_mini.'.$img_ext).'&hashp='.$ecmfile->share.'';
$share_phath = $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/document.php?hashp='.$ecmfile->share;		

}
if(count($filearray) <= 0){
if($_SERVER['HTTPS'] == 'on'){$protocol = 'https://';}else{$protocol = 'http://';}	
$url = $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/public/theme/common/nophoto.png';
$realpath = $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/public/theme/common/nophoto.png';
$realpath_thumb = $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/public/theme/common/nophoto.png';
$share_phath = $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/public/theme/common/nophoto.png';
}
$imagen = new stdClass();
$imagen->url = (string)$url;
$imagen->host = $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/documents/produit/'.$prod->ref.'/';
$imagen->encode_name = rawurlencode($img);
$imagen->nombre = $filearray[$index]['name'];
$imagen->index = $index;
$imagen->imagenes = $filearray;
$imagen->protocol = $protocol;
$imagen->realpath = $realpath;
$imagen->realpath_thumb = $realpath_thumb;
$imagen->share_phath = $share_phath;
return $imagen;
}	




function productImages($fk_product){
global $conf, $langs, $user;	
$prod = new Product($this->db);
$prod->fetch($fk_product);	
//obteniendo imagen
$sdir = $conf->product->multidir_output[$prod->entity];
$dir = $sdir . '/';
$pdir = '/';
$dir .= get_exdir(0,0,0,0,$prod,'product').$prod->ref.'/';
$pdir .= get_exdir(0,0,0,0,$prod,'product').$prod->ref.'/';


// Defined relative dir to DOL_DATA_ROOT
$relativedir = '';

if ($dir)
{
$relativedir = preg_replace('/^'.preg_quote(DOL_DATA_ROOT,'/').'/', '', $dir);
$relativedir = preg_replace('/^[\\/]/','',$relativedir);
$relativedir = preg_replace('/[\\/]$/','',$relativedir);
}

$dirthumb = $dir.'thumbs/';
$pdirthumb = $pdir.'thumbs/';

$return ='<!-- Photo -->'."\n";
$nbphoto=0;

$filearray=dol_dir_list($dir,"files",0,'','(\.meta|_preview.*\.png)$',$sortfield,(strtolower($sortorder)=='desc'?SORT_DESC:SORT_ASC),1);
$array = [];
foreach($filearray as $k=>$f){
$img = $f['name'];
$ext = explode('.',$f['name']);
$img_nom = $ext[0];
$img_ext = $ext[1];	
//obteniendo url publica
include_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmfiles.class.php';
$ecmfile=new EcmFiles($this->db);
$result = $ecmfile->fetch('','', 'produit/'.$prod->ref.'/'.$img.'', '', '', $hashp);
if($ecmfile->share == ''){
require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
$ecmfile->share = getRandomPassword(true);
$ecmfile->update($user);  	   			   
}
//fin obteniendo url publica
$imagen= new stdClass();
$imagen->nombre = $f['name'];
$imagen->index = $k;
if(count($filearray) > 0){
	if($_SERVER['HTTPS'] == 'on'){$protocol = 'https://';}else{$protocol = 'http://';}	
	$imagen->url =  $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/documents/produit/'.$prod->ref.'/'.rawurlencode($img_nom.'.'.$img_ext).'';
	$imagen->protocol = $protocol;
	$imagen->realpath = DOL_MAIN_URL_ROOT.'/viewimage.php?modulepart=produit&entity='.$prod->entity.'&file='.urlencode($prod->ref).'/'.urlencode($img).'&hashp='.$ecmfile->share.'';
	$imagen->realpath_thumb = DOL_MAIN_URL_ROOT.'/viewimage.php?modulepart=produit&entity='.$prod->entity.'&file='.urlencode($prod->ref).'/thumbs/'.urlencode($img_nom.'_mini.'.$img_ext).'&hashp='.$ecmfile->share.'';
	$imagen->share_phath = $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/document.php?hashp='.$ecmfile->share;		
	
	}
	if(count($filearray) <= 0){
	if($_SERVER['HTTPS'] == 'on'){$protocol = 'https://';}else{$protocol = 'http://';}	
	$imagen->url = $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/public/theme/common/nophoto.png';
	$imagen->protocol = $protocol;	
	$imagen->realpath = DOL_MAIN_URL_ROOT.'/viewimage.php?modulepart=produit&file='.urlencode('noimage.jpg');
	$imagen->realpath_thumb = DOL_MAIN_URL_ROOT.'/viewimage.php?modulepart=produit&file='.urlencode('noimage.jpg');
	$imagen->share_phath = $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/public/theme/common/nophoto.png';	
	}
	$imagen->host = $protocol.$_SERVER['HTTP_HOST'].DOL_URL_ROOT.'/documents/produit/'.$prod->ref.'/';
	$imagen->encode_name = rawurlencode($f['name']);
array_push($array,$imagen);

};

return $array;
}

}
?>
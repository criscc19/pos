<?php
include('phpqrcode/qrlib.php');
function qr_generator($url,$name){


     
    // outputs image directly into browser, as PNG stream
 

    // how to save PNG codes to server
    
    $tempDir = 'qr_image/';
    
    $codeContents = $url;
    
    // we need to generate filename somehow, 
    // with md5 or with database ID used to obtains $codeContents...
    $fileName = 'mesa_'.$name.'.png';
    
    $pngAbsoluteFilePath = $tempDir.$fileName;
    $urlRelativeFilePath = $tempDir.$fileName;
    
    // generating
    if (!file_exists($pngAbsoluteFilePath)) {
        QRcode::png($codeContents, $pngAbsoluteFilePath);
        echo 'File generated!';
        echo '<hr />';
    } else {
        echo 'File already generated! We can use this cached file to speed up site on common codes!';
        echo '<hr />';
    }
    
 /*    echo 'Server PNG File: '.$pngAbsoluteFilePath;
    echo '<hr />'; */
    
    // displaying
    return $urlRelativeFilePath;
    
}
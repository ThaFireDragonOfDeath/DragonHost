<?php
    //By Voldracarno Draconor (2017-2018)
    
    require_once 'security.php';
    
    $sRawPw = "Test1";
    $sEncPw = Security::cryptopass($sRawPw, Security::encrypt);
    $sReRawPw = Security::cryptopass($sEncPw, Security::decrypt);
    echo $sReRawPw;
?>

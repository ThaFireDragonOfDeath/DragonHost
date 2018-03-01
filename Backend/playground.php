<?php
    require_once 'projectconfigs.php';
    require_once 'systeminterface.php';
    require_once 'inc_backend.php';
    
    
    $objSysIf = new SystemInterface;
    //$bCreateUserSuccess = $objSysIf->addUser_database_db('dh_internal');
    
    //$bCreateUserSuccess = $objSysIf->checkUserExists_database_db('dh_internal');
    //$bCreateUserSuccess = $objSysIf->addUser_database_user('dh_internal', '');
    //$bCreateUserSuccess = $objSysIf->delUser_database_db('dh_internal');
    
    //$bCreateUserSuccess = $objSysIf->checkUserExists_passwd("dh_test");
    //$bCreateUserSuccess = $objSysIf->addUser_passwd("dh_test", "testpass");
    //$bCreateUserSuccess = $objSysIf->delUser_passwd("dh_test");
    
    //$bCreateUserSuccess = $objSysIf->addUser_database_user("dh_internal", "testpass");
    //$bCreateUserSuccess = $objSysIf->checkUserExists_database_user("dh_internal");
    //$bCreateUserSuccess = $objSysIf->delUser_database_user("dh_internal");
    
    
    Logger::debugmsg("Result: ${bCreateUserSuccess}");
?>

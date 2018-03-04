<?php
    require_once 'projectconfigs.php';
    require_once 'systeminterface.php';
    require_once 'inc_backend.php';
    require_once 'dbinterface.php';
    
    //$objSysIf = new SystemInterface;
    $objDbInterface = new DbInterface;
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
    
    //$objDbInterface->addUser("testuser1", "h123456789");
    
    //$bCreateUserSuccess = $objDbInterface->checkuserlogin("testuser1", "h123456789");
    //$bCreateUserSuccess = $objDbInterface->checkuserlogin("testuser1", "h1234569");
    
    //$bCreateUserSuccess = $objDbInterface->getUserExists("testuser1");
    //$bCreateUserSuccess = $objDbInterface->getUserExists("testuser");
    
    //$bCreateUserSuccess = $objDbInterface->getUserId("testuser1");
    
    //$objDbInterface->addJob(1, "testuser1", "h123456789");
    
    //Logger::debugmsg("Result: ${bCreateUserSuccess}");
?>

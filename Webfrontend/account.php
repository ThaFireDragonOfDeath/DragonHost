<?php
    require_once 'projectconfigs.php';
    require_once 'security.php';
    require_once 'inc_frontend.php';
    require_once 'dbinterface.php';
    
    $sUsername = $_POST['user'];
    $sPassword = $_POST['pwd'];
    $sAccAction = $_POST['accaction'];
    
    $bUsernameCheck = Security::checkstring($sUsername, Security::USERNAME);
    $bPasswordCheck = Security::checkstring($sPassword, Security::PASSWORD);
    
    if(!$bUsernameCheck || !$bPasswordCheck) {
        echo "Benutzer oder Passwort entspricht nicht den Richtlinien!\n";
    } else {
        $objDbInterface = new DbInterface;
        $bAccExists = $objDbInterface->getUserExists($sUsername);
    
        if ($sAccAction == "createAcc") {
            if ($bAccExists) {
                echo "Der Benutzername ist bereits vergeben";
            } else {
                $sPasswordHash = Security::hashpw($sPassword);
                $iCurrentUsers = $objDbInterface->countUsers();
                
                if ($iCurrentUsers <= ProjectConfigs::user_limit) {
                    $objDbInterface->addUser($sUsername, $sPasswordHash, ProjectConfigs::user_space);
                    $objDbInterface->addJob(DbInterface::ADDUSER, $sUsername, $sPassword);
                    echo "User wurde angelegt und wird in wenigen Minuten eingerichtet.";
                } else {
                    echo "Es sind keine Slots mehr frei!";
                }
            }
        }
        else if($sAccAction == "deleteAcc") {
            $bLoginCheck = $objDbInterface->checkuserlogin($sUsername, $sPassword);
            
            if (!$bAccExists || !$bLoginCheck) {
                echo "Benutzername und/oder Passwort ist falsch!";
            } else {
                //TODO
            }
        }
    }
?>

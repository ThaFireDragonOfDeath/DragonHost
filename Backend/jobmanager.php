<?php
    //By Voldracarno Draconor (2017-2018)

    require_once 'logger.php';
    require_once 'security.php';
    require_once 'systeminterface.php';
    require_once 'dbinterface.php';    
    
    class Jobmanager {

        //Enums
        //Fail Points
        const PASSWD = 0;
        const DATABASE_DB = 1;
        const DATABASE_USER = 2;
        const APACHE_CONFIG = 3;
        const APACHE_CONFIG_ENABLE = 4;
        const QUOTAS_CONFIG = 5;
        const APACHE_RELOAD = 6;
    
        //Job states
        const NOT_PROCESSED = -1;
        const SUCCESS = 0;
        const FAILED = 1;
        
        //Job types
        const ADDUSER = 1;
	const DELUSER = 2;
        
        private $objSystemInterface;
    
        function __construct() {
            try {
                $this->objSystemInterface = new SystemInterface;
            } catch(PDOException $ex) {
                $iErrorCode = $ex->getCode();
                Logger::critical_logmsg("ERROR: Failed to establish root connection to the database! Errorcode: ${iErrorCode}");
                exit(2);
            }
        }
    
        public function addUser(string $sUserName, string $sUserPass, int $iJobId, int $iUserMemoryInMB = 500) {
            Logger::debugmsg('Begin adduser()');
            
            //Check incoming strings	
            $bUserNameCheck = Security::checkstring($sUserName, Security::USERNAME);
            $bUserPassCheck = Security::checkstring($sUserPass, Security::PASSWORD);
            $bUserSpaceCheck = Security::checkinteger($iUserMemoryInMB, true);
            $bUserNameAlreadyExist = $this->objSystemInterface->checkUserExists_passwd($sUserName);
            $bUser_HaveDB = $this->objSystemInterface->checkUserExists_database_db($sUserName);
            $bUser_HaveDBUser = $this->objSystemInterface->checkUserExists_database_user($sUserName);
            $bUser_HaveApacheConfig = $this->objSystemInterface->checkUserExists_config_apache($sUserName);
            $bUser_HaveApacheEnabled = $this->objSystemInterface->checkUserExists_config_apache_enable($sUserName);
            $bUser_HaveQuota = $this->objSystemInterface->checkUserExists_quotas($sUserName);

	    Logger::debugmsg("bUserNameCheck: $bUserNameCheck");
	    Logger::debugmsg("bUserPassCheck: $bUserPassCheck");
	    Logger::debugmsg("bUserSpaceCheck: $bUserSpaceCheck");
	    Logger::debugmsg("bUserNameAlreadyExist: $bUserNameAlreadyExist");
	    Logger::debugmsg("bUser_HaveDB: $bUser_HaveDB");
	    Logger::debugmsg("bUser_HaveDBUser: $bUser_HaveDBUser");
	    Logger::debugmsg("bUser_HaveApacheConfig: $bUser_HaveApacheConfig");
	    Logger::debugmsg("bUser_HaveApacheEnabled: $bUser_HaveApacheEnabled");
	    Logger::debugmsg("bUser_HaveQuota: $bUser_HaveQuota");
            
            //Adds the user, if the Parameters are valid
            if($bUserNameCheck && $bUserPassCheck && $bUserSpaceCheck 
	       	&& !$bUserNameAlreadyExist && !$bUser_HaveDB && !$bUser_HaveDBUser 
	       	&& !$bUser_HaveApacheConfig && !$bUser_HaveApacheEnabled && !$bUser_HaveQuota) {

                //Add user to system
                $bSucces = $this->objSystemInterface->addUser_passwd($sUserName, $sUserPass);
                if($bSucces != true) {
                    $this->addUser_cleanAF(self::PASSWD, $sUserName, $iJobId);
                    return false;
                }
                
                //Add database
                $bSucces = $this->objSystemInterface->addUser_database_db($sUserName);
                if($bSucces != true) {
                    $this->addUser_cleanAF(self::DATABASE_DB, $sUserName, $iJobId);
                    return false;
                }
                
                //Add database user
                $bSucces = $this->objSystemInterface->addUser_database_user($sUserName, $sUserPass);
                if($bSucces != true) {
                    $this->addUser_cleanAF(self::DATABASE_USER, $sUserName, $iJobId);
                    return false;
                }
                
                //Add Apache config
                $bSucces = $this->objSystemInterface->addUser_config_apache($sUserName);
                if($bSucces != true) {
                    $this->addUser_cleanAF(self::APACHE_CONFIG, $sUserName, $iJobId);
                    return false;
                }
                
                //Enable Apache config
                $bSucces = $this->objSystemInterface->addUser_config_apache_enable($sUserName);
                if($bSucces != true) {
                    $this->addUser_cleanAF(self::APACHE_CONFIG_ENABLE, $sUserName, $iJobId);
                    return false;
                }
                
                //Add quota
                $bSucces = $this->objSystemInterface->addUser_quotas($sUserName, $iUserMemoryInMB);
                if($bSucces != true) {
                    $this->addUser_cleanAF(self::QUOTAS_CONFIG, $sUserName, $iJobId);
                    return false;
                }
                
                //Reload Apache
                $bSucces = $this->objSystemInterface->addUser_apache_reload();
                if($bSucces != true) {
                    $this->addUser_cleanAF(self::APACHE_RELOAD, $sUserName, $iJobId);
                    return false;
                }

		return true;
            }
            else {
                Logger::logmsg("Adduser: Error in username ('${sUserName}') or password!");
                return false;
            }	
        }
    
        public function addUser_cleanAF(int $iFailPoint, string $sUserName, int $iJobid) {
            Logger::debugmsg('Begin addUser_cleanAF()');
        
	    $bReturnValue = true;

            //If failed in quota part or higher
            if ($iFailPoint >= self::QUOTAS_CONFIG) {
                $bUserStillExist = $this->objSystemInterface->checkUserExists_quotas($sUserName);
                    
                if($bUserStillExist == true) {
                    if($this->objSystemInterface->delUser_quotas($sUserName) == FALSE) {
                        //Send notofication to the sysadmin
                        Logger::sendMaintenance($iJobid, "Remove quotas from user \"${sUserName}\" by hand!");
			$bReturnValue = false;
                    }
                }
            }
        
            //If failed in apache config enable part or higher
            if ($iFailPoint >= self::APACHE_CONFIG_ENABLE) {
                $bUserStillExist = $this->objSystemInterface->checkUserExists_config_apache_enable($sUserName);
                    
                if($bUserStillExist == true) {
                    if($this->objSystemInterface->delUser_config_apache_enable($sUserName) == FALSE) {
                        //Send notofication to the sysadmin
                        Logger::sendMaintenance($iJobid, "Disable the site from user \"${sUserName}\" by hand!");
			$bReturnValue = false;
                    }
                }
            }
        
            //If failed in apache config part or higher
            if ($iFailPoint >= self::APACHE_CONFIG) {
                $bUserStillExist = $this->objSystemInterface->checkUserExists_config_apache($sUserName);
                    
                if($bUserStillExist == true) {
                    if($this->objSystemInterface->delUser_config_apache($sUserName) == FALSE) {
                        //Send notofication to the sysadmin
                        Logger::sendMaintenance($iJobid, "Remove the apache config from user \"${sUserName}\" by hand!");
			$bReturnValue = false;
                    }
                }
            }
        
            //If failed in database_user part or higher
            if ($iFailPoint >= self::DATABASE_USER) {
                $bUserStillExist = $this->objSystemInterface->checkUserExists_database_user($sUserName);
                    
                if($bUserStillExist == true) {
                    if($this->objSystemInterface->delUser_database_user($sUserName) == FALSE) {
                        //Send notofication to the sysadmin
                        Logger::sendMaintenance($iJobid, "Remove the database user \"${sUserName}\" by hand!");
			$bReturnValue = false;
                    }
                }
            }
            
            //If failed in database_db part or higher
            if ($iFailPoint >= self::DATABASE_DB) {
                $bUserStillExist = $this->objSystemInterface->checkUserExists_database_db($sUserName);
                    
                if($bUserStillExist == true) {
                    if($this->objSystemInterface->delUser_database_db($sUserName) == FALSE) {
                        //Send notofication to the sysadmin
                        Logger::sendMaintenance($iJobid, "Remove the database \"${sUserName}\" by hand!");
			$bReturnValue = false;
                    }
                }
            }
            
            //If failed in passwd part or higher
            if($iFailPoint >= self::PASSWD) {
                $bUserStillExist = $this->objSystemInterface->checkUserExists_passwd($sUserName);
                
                //CAREFUL! CAN BE SECURITY RISK IF USERNAME IS ROOT OR SOMETHING LIKE THAT
                //MAKE SHURE THAT THE USER DIDNT EXISTED BEFORE CALLING THIS SHIT
                if($bUserStillExist == true) {
                    if($this->objSystemInterface->deluser_passwd($sUserName) == FALSE) {
                        //Send notofication to the sysadmin
                        Logger::sendMaintenance($iJobid, "Remove the user \"${sUserName}\" from passwd by hand!");
			$bReturnValue = false;
                    }
                }
            }

	    return $bReturnValue;
        }
    
	function delUser(string $sUserName, int $iJobId) {
	    $bDelSuccess = $this->addUser_cleanAF(self::QUOTAS_CONFIG, $sUserName, $iJobId);
	    return $bDelSuccess;
	}

        function execJobsFromDb() {
            Logger::debugmsg('Begin execJobsFromDb()');
            
            $objDbInterface = new DbInterface;
            $objSqlResult = $objDbInterface->getJobsFromDb();
            $iJobState = -1;
            $sJobMessage = "";
            
            foreach($objSqlResult as $objRow) {
                $sJobType = $objRow['jobtype'];
                
                if($sJobType == self::ADDUSER) {
                    //Get parameters for the adduser job
                    $sUserName = $objRow['username'];
                    $sUserPassEnc = $objRow['password_enc'];
		    $sUserPass = Security::cryptopass($sUserPassEnc, Security::DECRYPT);
                    $iJobId = $objRow['jobid'];
                    $iUserSpace = $objRow['userspace'];
                    
                    //Call the adduser method
                    $bAddSuccess = $this->addUser($sUserName, $sUserPass, $iJobId, $iUserSpace);
                    
                    if ($bAddSuccess === true) {
                        $iJobState = 0;
                        $sJobMessage = "Success";
                    }
                    else {
                        $iJobState = 1;
                        $sJobMessage = "Failed";
			$objDbInterface->delUser($sUserName);
                    }
                    
                    $objDbInterface->setJobState($iJobId, $iJobState, $sJobMessage);
                }
		else if($sJobType == self::DELUSER) {
		    //Get parameters for the deluser job
                    $sUserName = $objRow['username'];
		    $iJobId = $objRow['jobid'];

		    //Call the deluser method
                    $bDelSuccess = $this->delUser($sUserName, $iJobId);
                    
                    if ($bDelSuccess === true) {
                        $iJobState = 0;
                        $sJobMessage = "Success";
			$objDbInterface->delUser($sUserName);
                    }
                    else {
                        $iJobState = 1;
                        $sJobMessage = "Failed";
                    }
                    
                    $objDbInterface->setJobState($iJobId, $iJobState, $sJobMessage);
		}
            }
        }
    }
?>

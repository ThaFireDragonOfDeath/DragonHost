<?php
    //By Voldracarno Draconor (2017-2018)
    
    require_once 'logger.php';
    require_once 'projectconfigs.php';
    require_once 'sqlbackend.php';
    require_once 'dbrootinterface.php';
    
    class SystemInterface {
        //Enums
        const QUOTA_CURRENT_USED = 4;
        const QUOTA_HARD_LIMIT = 6;
        
        //Class members
        private $objRootDbInterface;
        
        //Constructor
        public function __construct() {
            try {
                $this->objRootDbInterface = new DbRootInterface();
            } catch(PDOException $ex) {
                throw $ex;
            }
        }
        
        //Functions
        public function addUser_apache_reload() {
            $slShellCommand = "systemctl reload apache2";
            
            $slOutputs = null;
            $iShellReturn = -1;
            exec($sCurrentCommand, $slOutputs, $iShellReturn);
        
            if($iShellReturn != 0) {
                return false;
            }
            else {
                return true;
            }
            
        }
        
        public function addUser_config_apache($sUserName) {
            $apache_template_path = ProjectConfigs::apache_template_path;
            $apache_config_path = ProjectConfigs::apache_config_path;
            
            $bTemplateExist = is_readable($apache_template_path);
            
            if ($bTemplateExist === false) {
                Logger::debugmsg("Adduser_Config_Apache: Failed to load template!");
                return false;
            }
            
            $sConfigContent = file_get_contents($apache_template_path);
            str_replace(":sUserName", $sUserName, $sConfigContent);
            
            $sSaveConfigFileName = "${sUserName}_config.conf";
            $sSaveConfigFilePath = $apache_config_path."/".$sSaveConfigFileName;
            
            $bWriteCheck = file_put_contents($sSaveConfigFilePath, $sConfigContent);
            
            if($bWriteCheck === false) {
                Logger::debugmsg("Adduser_Config_Apache: Failed to write user config file!");
                return false;
            }
            else {
                return true;
            }
        }
        
        public function addUser_config_apache_enable($sUserName) {
            $sShellCommand = "a2ensite ${sUserName}_config.conf";
            $slOutputs = null;
            $iShellReturn = -1;
            exec($sShellCommand, $slOutputs, $iShellReturn);
            
            if($iShellReturn != 0) {
                return false;
            }
            else {
                return true;
            }
        }

        public function addUser_database_db($sUserName) {
            Logger::debugmsg('Begin addUser_database_db');
        
            return $this->objRootDbInterface->createNewDatabase($sUserName);
        }
        
        public function addUser_database_user($sUserName, $sUserPass) {
            Logger::debugmsg('Begin addUser_database_user()');
        
            return $this->objRootDbInterface->createNewDatabaseUser($sUserName, $sUserPass, $sUserName);
        }

        public function addUser_passwd($sUserName, $sUserPass) {
            Logger::debugmsg('Begin addUser_passwd()');
        
            //Add user to system
            //TODO: Check useradd commend and path

            $slShellCommands = array(
                0 => "useradd -d /srv/dragonhost/users/${sUserName} -g webspace_user -m -s /bin/false ${sUserName}",
                1 => "echo \"${sUserName}:${sUserPass}\" | chpasswd",
                2 => "mkdir /srv/dragonhost/users/${sUserName}/www",
                3 => "chmod -R 600 /srv/dragonhost/users/${sUserName}",
            );
            
            foreach($slShellCommands as $sCurrentCommand) {
                $slOutputs = null;
                $iShellReturn = -1;
                exec($sCurrentCommand, $slOutputs, $iShellReturn);
            
                if($iShellReturn != 0) {
                    return false;
                }
            }
            
            return true;
        }
        
        public function addUser_quotas($sUserName, $iUserSpace) {
            return $this->setquotas($sUserName, $iUserSpace);
        }

        public function checkUserExists_config_apache($sUserName) {
            $sSaveConfigFileName = "${sUserName}_config.conf";
            $sSaveConfigFilePath = ProjectConfigs::apache_config_path."/${sSaveConfigFileName}";
            $bUserExist = is_readable($sSaveConfigFilePath);
            
            return $bUserExist;
        }
        
        public function checkUserExists_config_apache_enable($sUserName) {
            $sSaveConfigFileName = "${sUserName}_config.conf";
            $sSaveConfigFilePath = ProjectConfigs::apache_enabled_config_path."/${sSaveConfigFileName}";
            $bUserExist = is_readable($sSaveConfigFilePath);
            
            return $bUserExist;
        }
        
        public function checkUserExists_database_db($sUserName) {
            Logger::debugmsg('Begin checkUserExists_database_db()');
            
            $bDbExists = false;
            try {
                $bDbExists = $this->objRootDbInterface->checkdbexist($sUserName);
            } catch(PDOException $ex) {
                throw $ex;
            }
            
            return $bDbExists;
        }
        
        public function checkUserExists_database_user($sUserName) {
            Logger::debugmsg('Begin checkUserExists_database_user()');
            
            $bUserExists = false;
            try {
                $bUserExists = $this->objRootDbInterface->checkuserexist($sUserName);
            } catch(PDOException $ex) {
                throw $ex;
            }
            
            return $bUserExists;
        }

        public function checkUserExists_passwd($sUsername) {
            Logger::debugmsg('Begin checkUserExists_passwd()');
            
            //Execute grep over /etc/passwd
            $sShellCommand = "cat /etc/passwd | grep -i \"${sUsername}:\"";
            $sShellReturn = shell_exec($sShellCommand);
        
            //If grep haven't made any outputs --> the user doesn't exist in the passwd
            if($sShellReturn != null) {
                return true;
            }
            else {
                return false;
            }
        }
        
        public function checkUserExists_quotas($sUserName): bool {
            $iCurrentUserLimit = $this->getQuotaSpaceLimit($sUserName);
            
            if ($iCurrentUserLimit <= 0) {
                return false;
            }
            else {
                return true;
            }
        }
        
        public function getQuotaSpaceLimit($sUserName): int {
            return $this->getQuotaInfo(self::QUOTA_HARD_LIMIT);
        }
        
        public function delUser_config_apache($sUserName): bool {
            $sSaveConfigFileName = "${sUserName}_config.conf";
            $sSaveConfigFilePath = $this->apache_config_path & "/" & $sSaveConfigFileName;
            $bUserExist = is_readable($sSaveConfigFilePath);
            
            if ($bUserExist == true) {
                $bRemoveSuccess = unlink($sSaveConfigFilePath);
                return $bRemoveSuccess;
            }
            else {
                return true;
            }
        }
        
        public function delUser_config_apache_enable($sUserName): bool {
            $sShellCommand = "a2dissite ${sUserName}_config.conf";
            $slOutputs = null;
            $iShellReturn = null;
            exec($sShellCommand, $slOutputs, $iShellReturn);
            
            if($iShellReturn != 0) {
                return false;
            }
            else {
                return true;
            }
        }
        
        public function delUser_database_db($sUserName) {
            Logger::debugmsg('Begin delUser_database_db()');
            
            return $this->objRootDbInterface->deletedatabase($sUserName);
            
        }
        
        public function delUser_database_user($sUserName) {
            Logger::debugmsg('Begin delUser_database_user()');
            
            return $this->objRootDbInterface->deletedbuser($sUserName);
        }
        
        public function deluser_passwd($sUserName) {
            Logger::debugmsg('Begin deluser_passwd()');
        
            //Prepare Shell Command (WARNING: INSECURE)
            $sUserAdd_Shell = "userdel -r -f ${sUserName}";
            $slOutputs = null;
            $iShellReturn = -1;
            exec($sUserAdd_Shell, $slOutputs, $iShellReturn);
            
            if($iShellReturn != 0) {
                return false;
            }
            else {
                return true;
            }
        }
        
        public function delUser_quotas(string $sUserName) {
            return $this->setquotas($sUserName, 0);
        }
        
        private function getQuotaInfo(string $sUserName, int $iQuotaInfo) {
            $sShellCommand1 = "repquota -a -O csv | grep \"${sUserName}\"";
            $slOutputs = null;
            $iShellReturn = -1;
            exec($sShellCommand1, $slOutputs, $iShellReturn);
            
            $sGrepOutput = $slOutputs[0];
            
            if ($sGrepOutput === "") {
                return -1;
            }
            else {
                $sShellCommand2 = "echo \"${sGrepOutput}\" | cut -d, -f${iQuotaInfo}";
                unset($slOutputs);
                $iShellReturn = -1;
                exec($sShellCommand2, $slOutputs, $iShellReturn);
                return $slOutputs[0];
            }
        }
        
        public function setquotas(string $sUsername, int $iUserSpaceInMB) {
            $iUserSpaceInBlocks = $iUserSpaceInMB * 1024;
            $sShellCommand = "setquota -u ${sUserName} 0 ${iUserSpaceInBlocks} 0 0";
            $slOutputs = null;
            $iShellReturn = -1;
            exec($sShellCommand, $slOutputs, $iShellReturn);
            
            if($iShellReturn != 0) {
                return false;
            }
            else {
                return true;
            }
        }
	
	}
?>

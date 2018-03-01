<?php
    //By Voldracarno Draconor (2017-2018)

    require_once 'dbinterface.php';
    require_once 'projectconfigs.php';
    
    class Logger {
        const FRONTEND = 0;
        const BACKEND = 1;
        
        //private static $iLogContext = -1;
    
        public static function critical_logmsg(string $sMessage) {
            $sLogPath = '';
            
            if (self::$iLogContext === self::FRONTEND) {
                $sLogPath = ProjectConfigs::frontend_critical_log_path;
            }
            elseif (self::$iLogContext === self::BACKEND) {
                $sLogPath = ProjectConfigs::backend_critical_log_path;
            }
            
            if ($sLogPath != '') {
                $bWriteCheck = file_put_contents($sLogPath, $sMessage, FILE_APPEND);
                if ($bWriteCheck === false) {
                    return false;
                }
                else {
                    return true;
                }
            }
        }
    
        public static function debugmsg(string $sMessage = '') {
            if(ProjectConfigs::enable_debug === true) {
                self::sendMessage($sMessage, 'DEBUG');
            }
        }
        
        public static function logmsg(string $sMessage = '') {
            if (ProjectConfigs::enable_log === true) {
                self::sendMessage($sMessage, 'LOG');
            }
        }
        
        public static function sendMaintenance(int $iJobId, string $sMessage) {
            try {
                $objDbInterface = new DbInterface;
                $objDbInterface->addMaintanaceEntry($iJobId, $sMessage);
            } catch(PDOException $ex) {
                $sErrorMessage = "ERROR: Wartungsauftrag konnte nicht in die Datenbank geschrieben werden! JobID: ${iJobId}, Nachricht: ${sMessage}";
                self::critical_logmsg($sMessage);
            }
        }
        
        public static function sendMessage(string $sMessage = '', $sPrefix = '') {
            //Only log if this part is executed in backend context
            if (SCRIPT_CONTEXT === self::BACKEND) {
                echo "${sPrefix}: ${sMessage}\n";
            }
        }
        
//         public static function setContext(int $iContext) {
//             self::$iLogContext = $iContext;
//         }
	}
?>

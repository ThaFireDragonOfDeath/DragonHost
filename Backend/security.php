<?php
    //By Voldracarno Draconor (2017-2018)

    require_once 'projectconfigs.php';
    
    function exception_handler($exception) {
        echo "Uncaught exception: " , $exception->getMessage(), "\n";
    }

    if (ProjectConfigs::enable_stacktraces == false) {
        set_exception_handler('exception_handler');
    }

    class Security {
        const USERNAME = 0;
        const PASSWORD = 1;
        const ENCRYPT = 0;
        const DECRYPT = 1;
        
        public static function checkinteger($iIntToCheck, bool $bGreaterThenZero = true): bool {
            Logger::debugmsg('Begin checkinteger()');
            
            //Check Datatype
            $sVarType = gettype($iIntToCheck);
            
            if($sVarType != 'integer') {
                return false;
            }
            elseif($iIntToCheck <= 0 && $bGreaterThenZero) {
                return false;
            }
            else {
                return true;
            }
        }
        
        public static function checkstring(string $sStringToCheck, int $iStringType): bool {
            Logger::debugmsg('Begin checkstring()');
            
            $sSearchPattern = '';
            $iMinStringLenght = -1;
            $iMaxStringLenght = -1;
            
            //Username can only contain lowercase letters or numbers
            if($iStringType === self::USERNAME) {
                $sSearchPattern = '/[^a-z0-9]/';
                $iMinStringLenght = ProjectConfigs::minimum_username_lenght;
                $iMaxStringLenght = ProjectConfigs::maximum_username_lenght;
            }
            else if($iStringType === self::PASSWORD) {
                $sSearchPattern = '/[^A-Za-z0-9]/';
                $iMinStringLenght = ProjectConfigs::minimum_password_lenght;
                $iMaxStringLenght = ProjectConfigs::maximum_password_lenght;
            }
            else {
                throw new Exception("Invalid action argument");
            }
            
            //Execute RegEx
            $iRegexReturn = preg_match($sSearchPattern, $sStringToCheck);
            Logger::debugmsg("Regex return: ${iRegexReturn}");
            
            //Check for matches and return TRUE if there are no matches
            if($iRegexReturn != 0) {
                return false;
            }
            elseif($iStringType === self::USERNAME) {
                //Filter internal and common system names
                switch ($sStringToCheck) {
                    case 'internal':
                    case 'root':
                    case 'ftp':
                    case 'dbus':
                    case 'nobody':
                    case 'dnsmasq':
                    case 'rpc':
                    case 'http':
                    case 'uuidd':
                    case 'www':
                    case 'polkitd':
                    case 'mail':
                    case 'daemon':
                    case 'colord':
                    case 'ntp':
                    case 'gdm':
                    case 'git':
                    case 'usbmux':
                    case 'rtkit':
                    case 'bin':
                    case 'apache':
                    case ProjectConfigs::admin_username:
                        return false;
                }
            }
            
            $iCurrentStringLenght = strlen($sStringToCheck);
            
            if ($iCurrentStringLenght < $iMinStringLenght or $iCurrentStringLenght > $iMaxStringLenght) {
                    return false;
            }
            else {
                return true;
            }
        }
        
        //Encrypts and Decrypts passwords from the job db
        public static function cryptopass(string $sText, int $iAction): string {
            $sOutput = '';
            $sCryptMethod = "AES-256-CTR";
            $sKey = ProjectConfigs::pass_key;
            $sIv = ProjectConfigs::pass_iv;
            
            $sHashedIv = hash('sha256', $sIv);
            $sFinalIv = substr($sHashedIv, 0, 16); //We need 16 Bytes (256 bits)
            $sHashedKey = hash('sha256', $sKey);
            
            if($iAction === self::ENCRYPT) {
                $sEncrypted = openssl_encrypt($sText, $sCryptMethod, $sHashedKey, 0, $sFinalIv);
                $sOutput = base64_encode($sEncrypted);
            } 
            else if($iAction === self::DECRYPT) {
                $sDecoded = base64_decode($sText);
                $sOutput = openssl_decrypt($sDecoded, $sCryptMethod, $sHashedKey, 0, $sFinalIv);
            }
            
            return $sOutput;
        }
        
        //Hash a password
        public static function hashpw(string $sRawPassword): string {
            return password_hash($sRawPassword, PASSWORD_DEFAULT);
        }
        
        //Checks if password and hash fit together
        public static function verify_hashpw(string $sRawPassword, string $sPwHash): bool {
            return password_verify($sRawPassword, $sPwHash);
        }
    }
?>

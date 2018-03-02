<?php
    //By Voldracarno Draconor (2017-2018)
    
    require_once 'logger.php';
    require_once 'projectconfigs.php';
    require_once 'sqlbackend.php';

    class DbInterface {
        const ADDUSER = 1;
        const DELUSER = 2;
        const CHPASS = 3;
    
        protected $objSqlBackend;
        
//         function __construct() {
//             $objFuncArgs = func_get_args();
//             $iNumArgs = func_num_args();
//             $sMethodName = "__construct${iNumArgs}";
//             
//             if (method_exists($this, $sMethodName)) {
//                 $objCallableFunction = array(
//                     $this,
//                     $sMethodName,
//                 );
//                 
//                 call_user_func_array($objCallableFunction, $objFuncArgs);
//             }
//             else {
//                 $objException = new Error("Wrong arguments count");
//                 throw $objException;
//             }
//         }
        
        function __construct() {
            try {
                $this->objSqlBackend = new SqlBackend(ProjectConfigs::db_host, ProjectConfigs::db_user, ProjectConfigs::db_password, ProjectConfigs::db_dbname);
            } catch(PDOException $ex) {
                throw $ex;
            }
        }
        
//         function __construct4(string $sDbHost, string $sDbUser, string $sDbPassword, string $sDbName) {
//             try {
//                 $this->objSqlBackend = new SqlBackend(ProjectConfigs::db_host, ProjectConfigs::db_user, ProjectConfigs::db_password, ProjectConfigs::db_dbname);
//             } catch(PDOException $ex) {
//                 throw $ex;
//             }
//         }
        
        public function addJob(int $iJobType, string $sUserName, string $sPassword) {
            //TODO
            $iUserId = $this->getUserId($sUserName);
            $sPasswordEnc = Security::cryptopass($sPassword, Security::ENCRYPT);
            
            $sSqlQuery1 = "INSERT INTO jobs (jobtype, jobstate) VALUES (:jobtype, -1)";
            $sSqlQuery2 = "INSERT INTO jobs_user (jobid, userid, password_enc) VALUES (LAST_INSERT_ID(), :userid, :password_enc)";
            
            $slSqlParameters1 = array(
                ':jobtype' => $iJobType,
            ):
            $slSqlParameters2 = array(
                ':useid' => $iUserId,
                ':password_enc' => $sPasswordEnc,
            );
            
            $this->doSqlQuery($sSqlQuery1, $slSqlParameters1);
            $this->doSqlQuery($sSqlQuery2, $slSqlParameters2);
        }
        
        public function addUser(string $sUserName, string $sPasswordHash, int $iUserSpace = 500) {
            $sSqlQuery = "INSERT INTO users (username, password_hash, userspace, userstate) VALUES (:usrname, :pwhash, :usrspace, -1)";
            $slSqlParameters = array(
                ':usrname' => $sUserName,
                ':pwhash' => $sPasswordHash,
                ':usrspace' => $iUserSpace,
            );
            return $this->doSqlQuery($sSqlQuery, $slSqlParameters);
        }
        
        public function addMaintanaceEntry(int $iJobId, string $sMaintMessage) {
            $sSqlQuery = "INSERT INTO maintanance (jobid, maint_state, maint_message) VALUES (:jobid, -1, :maintmsg)";
            $slSqlParameters = array(
                ':jobid' => $iJobId,
                ':maintmsg' => $sMaintMessage,
            );
            return $this->doSqlQuery($sSqlQuery, $slSqlParameters);
        }
        
        public function checkuserlogin(string $sUserName, string $sPassword) {
            $sSqlQuery = "SELECT username, password_hash FROM users WHERE username = :username;";
            $slSqlParameters = array(
                ':username' => $sUserName,
            );
            
            try {
                $objSqlresult = $this->objSqlBackend->sqlSelect($sSqlQuery, $slSqlParameters);
                if ($objSqlresult->rowCount() != 1) {
                    return false;
                } else {
                    $objResultRow = $objSqlresult->fetch();
                    return Security::verify_hashpw($sPassword, $objResultRow['password_hash']);
                }
            } catch(PDOException $ex) {
                return false;
            }
        }
        
        protected function countSqlResults(string $sSqlQuery, $slBindings = null) {
            try {
                return $this->objSqlBackend->countRows($sSqlQuery, $slBindings);
            }
            catch(PDOException $ex) {
                $sExMessage = $ex->getMessage();
                Logger::debugmsg("SQL Exception: ${sExMessage}");
                return 0;
            }
        }
        
        public function countUsers() {
            $sSqlQuery = "SELECT * FROM users";
            return $this->countSqlResults($sSqlQuery);
        }
        
        protected function doSqlQuery(string $sSqlQuery, $slBindings = null) {
            try {
                $this->objSqlBackend->sqlExec($sSqlQuery, $slBindings);
            }
            catch(PDOException $ex) {
                $sExMessage = $ex->getMessage();
                Logger::debugmsg("SQL Exception: ${sExMessage}");
                return false;
            }
            
            return true;
        }
    
        public function getJobsFromDb() {
            //Generate SQL Query text
            $sSqlQuery = 'SELECT * FROM jobs JOIN jobs_user ON jobs.jobid = jobs_user.jobid JOIN users ON jobs_user.userid = users.userid;';
            $objSqlResult = null;
            
            try {
                $objSqlResult = $this->objSqlBackend->sqlSelect($sSqlQuery);
            } catch(PDOException $ex) {
                return null;
            }
            
            return $objSqlResult;
        }
        
        public function getUserExists(string $sUserName) {
            $sSqlQuery = "SELECT username FROM users WHERE username = :username";
            $slSqlParameters = array(
                ':username' => $sUserName,
            );
            
            $iUserCount = $this->countSqlResults($sSqlQuery, $slSqlParameters);
            
            if ($iUserCount == 0) {
                return false;
            }
            else {
                return true;
            }
        }
        
        public function getUserId($sUserName) {
            $sSqlQuery = "SELECT username, userid FROM users WHERE username = :username;";
            $slSqlParameters = array(
                ':username' => $sUserName,
            );
            
            try {
                $objSqlresult = $this->objSqlBackend->sqlSelect($sSqlQuery, $slSqlParameters);
                if ($objSqlresult->rowCount() != 1) {
                    return -1;
                } else {
                    $objResultRow = $objSqlresult->fetch();
                    return intval($objResultRow['userid']);
                }
            } catch(PDOException $ex) {
                return -1;
            }
        }
        
        public function setDatabaseState(string $sDbName, int $iState) {
            Logger::debugmsg('Begin setDatabaseState()');
            
            $sSqlQuery = "UPDATE databases SET dbstate = :dbstate WHERE database_name = :dbname;";
            $slSqlParameters = array(
                ':dbstate' => $iState,
                ':dbname' => $sDbName,
            );
            return $this->doSqlQuery($sSqlQuery, $slSqlParameters);
        }
        
        public function setJobState(int $iJobId, int $iState, string $sStateMessage) {
            Logger::debugmsg('Begin setJobState()');
            
            $sSqlQuery = "UPDATE jobs SET password_enc = '',jobstate = :jobstate,jobmessage = :statemsg WHERE jobid = :jobid;";
            $slSqlParameters = array(
                ':jobstate' => $iState,
                ':jobid' => $iJobId,
                ':statemsg' => $sStateMessage,
            );
            
            return $this->doSqlQuery($sSqlQuery, $slSqlParameters);
        }
        
        public function setUserState(int $iUserId, int $iState) {
            Logger::debugmsg('Begin setUserState()');
            
            $sSqlQuery = "UPDATE users SET userstate = :usrstate WHERE userid = :usrid;";
            $slSqlParameters = array(
                ':usrstate' => $iState,
                ':usrid' => $iUserId,
            );
            
            return $this->doSqlQuery($sSqlQuery, $slSqlParameters);
        }
    }
?>

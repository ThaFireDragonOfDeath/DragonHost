<?php
    //By Voldracarno Draconor (2017-2018)

    require_once 'logger.php';
    require_once 'security.php';
    
    class SqlBackend {
    
        private $objPDO;
    
        function __construct(string $sNewSqlHost, string $sNewSqlUser, string $sNewSqlPass, string $sNewSqlDbName) {
            try {
                $this->objPDO = new PDO("mysql:host=${sNewSqlHost};dbname=${sNewSqlDbName}", $sNewSqlUser, $sNewSqlPass);
            } catch(PDOException $ex) {
                Logger::logmsg("Error while connecting to the database");
                throw $ex;
            }
            
            $this->objPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    
        public function countRows(string $sSqlCommand, array $slBindings = null) {
            Logger::debugmsg('Begin countRows()');
            
            $iRowsFound = -1;
            
            //Exec SQL SELECT and count the results
            try {
                $objResult = $this->sqlSelect($sSqlCommand, $slBindings);
                $iRowsFound = $objResult->rowCount();
            }
            catch(PDOException $ex) {
                throw $ex;
            }
            
            return $iRowsFound;
        }
    
        public function sqlExec(string $sSqlCommand, array $sSqlBindings = null) {
            Logger::debugmsg('Begin sqlExec()');
            Logger::debugmsg("SQL Statement: ${sSqlCommand}");
            
            try {
                //If we have an prepared statement: Prepare and execute
                if($sSqlBindings != null) {
                    $objStatement = $this->objPDO->prepare($sSqlCommand);
                    $objStatement->execute($sSqlBindings);
                }
                else {
                    $this->objPDO->exec($sSqlCommand);
                }
            }
            catch(PDOException $ex) {
                Logger::debugmsg("SQL Exception");
                throw $ex;
            }
        }

        public function sqlSelect(string $sSqlCommand, array $sSqlBindings = null) {
            Logger::debugmsg('Begin sqlSelect()');
            Logger::debugmsg("SQL Statement: ${sSqlCommand}");
            
            try {
                //If we have an prepared statement: Prepare and execute
                if($sSqlBindings != NULL) {
                    $objStatement = $this->objPDO->prepare($sSqlCommand);
                    $objStatement->execute($sSqlBindings);
                    //$objResult = $objStatement->fetchAll();
                    return $objStatement;
            }
                $objResult = $this->objPDO->query($sSqlCommand);
                return $objResult;
            }
            catch(PDOException $ex) {
                throw $ex;
            }
        }
    }
?>

<?php
    //By Voldracarno Draconor (2017-2018)
    
    require_once 'dbinterface.php';
    require_once 'projectconfigs.php';

    class DbRootInterface extends DbInterface {
        function __construct() {
            try {
                $this->objSqlBackend = new SqlBackend(ProjectConfigs::db_host, ProjectConfigs::db_root_user, ProjectConfigs::db_root_password, ProjectConfigs::db_dbname);
            } catch(PDOException $ex) {
                throw $ex;
            }
        }
        
        public function checkdbexist(string $sDbName): bool {
            $sSqlCommand = "SHOW DATABASES LIKE :sDbName;";
            $slParameters = array(
                ':sDbName' => $sDbName
            );
            
            $iMatchesFound = -1;
            
            //Count matches in the database
            try {
                $iMatchesFound = $this->objSqlBackend->countRows($sSqlCommand, $slParameters);
            } catch(PDOException $ex) {
                throw $ex;
            }
            
            //Return true if we have one or more matches
            if ($iMatchesFound >= 1) {
                return true;
            }
            else {
                return false;
            }
        }
        
        public function checkuserexist(string $sUserName): bool {
            $sSqlCommand = "SELECT DISTINCT User FROM mysql.user WHERE User = '${sUserName}';";
            
            $iMatchesFound = -1;
            
            //Count matches in the database
            try {
                $iMatchesFound = $this->objSqlBackend->countRows($sSqlCommand);
            } catch(PDOException $ex) {
                throw $ex;
            }
            
            //Return true if we have one or more matches
            if ($iMatchesFound >= 1) {
                return true;
            }
            else {
                return false;
            }
        }
        
        public function createNewDatabase(string $sDatabaseName): bool {
            $sSqlQuery = "CREATE DATABASE ${sDatabaseName};";
            return $this->doSqlQuery($sSqlQuery);
        }
        
        public function createNewDatabaseUser(string $sUserName, string $sUserPass, string $sLinkedDatabase): bool {
            $sDbHost = ProjectConfigs::db_host;
            $sSqlQuery1 = "CREATE USER '${sUserName}'@'${sDbHost}' IDENTIFIED BY '${sUserPass}';";
            $sSqlQuery2 = "GRANT ALL PRIVILEGES ON ${sLinkedDatabase}.* TO '${sUserName}'@'${sDbHost}';";
            $sSqlQuery3 = "FLUSH privileges;";
            return $this->doSqlQuery($sSqlQuery1.$sSqlQuery2.$sSqlQuery3);
        }
        
        public function deletedatabase(string $sDbName): bool {
            $sSqlCommand = "DROP DATABASE ${sDbName};";
            
            return $this->doSqlQuery($sSqlCommand);
        }
        
        public function deletedbuser(string $sDbUserName): bool {
	    $sDbHost = ProjectConfigs::db_host;
            $sSqlCommand = "DROP USER '${sDbUserName}'@'${sDbHost}';";
            
            return $this->doSqlQuery($sSqlCommand);
        }
    }
?>

<?php
    require_once 'inc_backend.php';
    require_once 'jobmanager.php';
    
    $objJobManager = new Jobmanager;
    $objJobManager->execJobsFromDb();
?>

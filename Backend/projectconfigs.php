<?php
    //By Voldracarno Draconor (2017-2018)

    class ProjectConfigs {
        //File paths
        const apache_template_path = "/etc/dragonhost/templates/apache-template.conf";
        const apache_config_path = "/etc/apache2/conf-available";
        const apache_enabled_config_path = "/etc/apache2/conf-enabled";
        const frontend_critical_log_path = "";
        const backend_critical_log_path = "";
        
        //DB access data
        const db_host = 'localhost';
        const db_user = 'dh_internal';
        const db_root_user = 'root';
        const db_root_password = '';
        const db_password = '';
        const db_dbname = 'demo';
        
        //Pass access data
        const pass_key = 'WpbXnKbfvrMta55WTQjYyztKb22fecuagFHcYFzQnD8bK5bHRR8XynFPsG4AzezU';
        const pass_iv = 'kt5YEhjo6KCQ6DV9nEp9wPtZ6HLut7hdVwK6hVdHDfVZ9pxtVEKM92ADEdbhihnZ';
        
        //Site configurations
        const user_limit = 5;
        const user_space = 500;
        const software_version = 0.1;
        const admin_username = 'dh_admin';
        const minimum_username_lenght = 3;
        const maximum_username_lenght = 30;
        const minimum_password_lenght = 7;
        const maximum_password_lenght = 100;
        
        //Debuging and logging
        const enable_debug = true;
        const enable_log = true;
        const enable_stacktraces = true;
    }
?>

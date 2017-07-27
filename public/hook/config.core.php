<?php
chdir('../../');


#
# MAIN configuration file
#
# Cache and project configs folders
define('__CACHE_DIR__',		getcwd().'/storage/logs/sync-cache');
define('__SYNC_CONFIGS_DIR__',	getcwd().'/storage/logs/sync-configs');
# Log file
define('LOG_FILENAME',		getcwd().'/storage/logs/git-sync.log');
# Clone and Sync git commands
define('__CMD_CLONE__',		'git clone $repo_url $cache_dir');
define('__CMD_SYNC__',		'cd $cache_dir && git pull');
# Protocol-specific configurations
$proto_conf = array(
    'rsync+ssh' => array(
        'exec' => 'rsync -avz --exclude \'.git*\' --delete $from $user@$host:$path$repo_path/ 2>&1',
    ),
    'ftp' => array(
        'exec' => 'ncftpput -F -D -R -u $user -p $password $host $srv_path/ $from/* 2>&1',
    )
);
<?php
# Separated with comma. Empty string - all ips allowed.
# Look in admin panel for GitHub public IP's
# Example: '127.0.0.1, 192.168.1.1'
define('__ALLOWED_IPS__',	'192.30.252.41, 192.30.252.42');
# Emails to send sync results
define('MAIL_TO',		'boz14676@qq.com');
# Mail everything, including logs.
define('MAIL_LOGS',		true);
# Mail only if error occured
define('MAIL_ERRORS',		true);
#
# HOST configuration
#
# Config file for storing information about production servers
# in which web site must be synced (allowed configuration when
# one repository sync to multiple web nodes)
#
# [server short name]
# proto = ''
#
$hosts_conf = array(
    # Development server with SSH server and rsync
    'dev' => array(
        'proto'		=> 'rsync+ssh',
        'host'		=> '47.92.129.194',
        'user'		=> 'root',
        'password'	=> '098pwd.com',
        'path'		=> '/pro/pzjhw/appapi.pzjhw.com'
    ),
    # Shared Hosting with FTP access
    'prod' => array(
        'proto'		=> 'sftp',
        'host'		=> '47.92.129.194',
        'user'		=> 'root',
        'password'	=> '098pwd.com',
        'path'		=> '/pro/pzjhw/appapi.pzjhw.com'
    )
);
#
# REPOSITORIES configuration
#
$repo_conf = array(
    'https://github.com/boz14676/culture-app-server' => array(
        'branch'	=> 'develop',
        'hosts'		=> 'http://appapi.pzjhw.com/',
        'repo_path'	=> 'culture-app-server',
        'server_path'	=> 'culture-app-server',
        'config_folder'	=> ''
    )
);
<?php
#Youtorr config
$config['trackerUrl'] = "http://tracker.openbittorrent.com:80/announce,udp://tracker.openbittorrent.com:80/announce";
$config['torrentFileDir'] = ""; #For daemon
$config['torrentDataDir'] = "";#For daemon
$config['tmpDir'] = "/tmp/youtorr/";
$config['channelSymlink'] = true; #
$config['httpDownload'] = true;#don't trust if your files can be access via your web server
$config['httpUserDownload'] = true;#Only logged user can download in http
$config['sitePath'] = ""; 
$config['frontTorrentFileDir'] = "torrent/torrent_active";
$config['frontTorrentDataDir'] = "torrent/data";
$config['nbDownloads'] = 1; #unused
$config['logDir'] = 'log'; #unused
$config['keepLogs'] = false;
$config['maxSize'] = '50G';
$config['verbose'] = true; #daemon only
$config['exitOnError'] = false; #daemon only
$config['channelCheckStep'] = 10;
$config['DownloadSpeedLimit'] = 0; #unused
$config['daemonLogFile'] = 'log/daemon.log'; #unused
$config['timeToSleep'] = 30;
$config['nbLastVideo'] = 5; #0 for all
$config['nbLastChannel'] = 5;
$config['nbVideoChannel'] = 10;
$config['deleteFile']=true;
$config['zipUserTorrent']=true; #Only for channel
$config['zipPrefix']="YOUTORR-"; #May / cause bug ? Prefix name of zip file
$config['torrentExt']=''; #transmission add .added to the .torrent file
$config['daemonLog']=LOG_PID | LOG_PERROR;
#End youtorr config

#Database config
#sqlite
#$config['dbEngine']="sqlite";
#$config['dbName']="/home/sybix/public_html/youtorr.db";
#mysql
$config['dbEngine']="mysql";
$config['dbName']="youtorr";
$config['dbUser']="youtorr";
$config['dbPassword']="";
$config['dbHost']="localhost";
$config['dbPort']=3306;
#End database config

#Do no edit until you know what's you're doing
$config['pythonPath'] = "python2";
$config['youtubedlPath'] = "/usr/bin/youtube-dl ";
$config['youtubedlErrors']['402']='ERROR: unable to download video info webpage: HTTP Error 402: Payment Required';
$config['youtubedlErrors']['404']='ERROR: YouTube said: This video does not exist.'; #This variable is used to handle a 'know error'
$config['youtubedlErrors']['Country']='ERROR: YouTube said: The uploader has not made this video available in your country.';
$config['youtubedlErrors']['Invalid']='ERROR: Invalid URL:';
$config['youtubedlErrors']['Unknown']='ERROR: unknown url type:';
$config['youtubedlErrors']['Nickname']='WARNING: unable to extract uploader nickname';
define('YOUTORR','');

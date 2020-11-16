#!/opt/zbox/bin/php
<?php
array_shift($argv);
$flipArgv = array_flip($argv);
$binPath  = dirname(__FILE__);
$basePath = dirname($binPath);

if($basePath != '/opt/zbox') die("Run it in path /opt/zbox/\n");
if(empty($argv) or isset($flipArgv['--help']) or isset($flipArgv['-h']))
{
    echo <<<EOD
Usage: zbox.php {start|stop|restart|status}

Options:
    -h --help Show help.
    -ap --aport Apache port, default 80.
    -mp --mport Mysql port, default 3306.

EOD;
    exit;
}

if(is_dir("$basePath/app/zentao/"))
{
    `chmod -R 777 $basePath/app/zentao/tmp`;
    `chmod -R 777 $basePath/app/zentao/www/data`;
}
if(is_dir("$basePath/app/zentaopro/"))
{
    `chmod -R 777 $basePath/app/zentaopro/tmp`;
    `chmod -R 777 $basePath/app/zentaopro/www/data`;
}
if(is_dir("$basePath/app/zentaoep/"))
{
    `chmod -R 777 $basePath/app/zentaoep/tmp`;
    `chmod -R 777 $basePath/app/zentaoep/www/data`;
}

/* Process argv. */
$params = array();
foreach($flipArgv as $key => $val)
{
    if(strpos($key, '-') !== 0) continue;
    if($key == '--aport') $key = '-ap';
    if($key == '--mport') $key = '-mp';
    if(isset($argv[$val + 1]) and is_numeric($argv[$val + 1]))
    {
        $params[$key] = $argv[$val + 1];
        unset($argv[$val]);
        unset($argv[$val + 1]);
    }
}

if(isset($params['-ap'])) changePort($basePath . '/etc/apache/httpd.conf', $params['-ap'], array('^Listen +([0-9]+)', '<VirtualHost +.*:([0-9]+)>'));

if(isset($params['-mp']))
{
    changePort($basePath . '/etc/mysql/my.cnf', $params['-mp'], '^port *= *([0-9]+)');
    changePort($basePath . '/app/htdocs/index.php', $params['-mp'], 'localhost\:([0-9]+)\&');

    $myReg = '^\$config->db->port *= *.([0-9]+)..*;';
    if(file_exists("$basePath/app/zentao/config/my.php"))
    {
        `chmod 777 $basePath/app/zentao/config/my.php`;
        $myFile = "$basePath/app/zentao/config/my.php";
        changePort($myFile, $params['-mp'], $myReg);
    }
    if(file_exists("$basePath/app/zentaopro/config/my.php"))
    {
        `chmod 777 $basePath/app/zentaopro/config/my.php`;
        $myFile = "$basePath/app/zentaopro/config/my.php";
        changePort($myFile, $params['-mp'], $myReg);
    }
    if(file_exists("$basePath/app/zentaoep/config/my.php"))
    {
        `chmod 777 $basePath/app/zentaoep/config/my.php`;
        $myFile = "$basePath/app/zentaoep/config/my.php";
        changePort($myFile, $params['-mp'], $myReg);
    }
    if(file_exists("$basePath/app/chanzhi/system/config/my.php"))
    {
        `chmod 777 $basePath/app/chanzhi/system/config/my.php`;
        $myFile = "$basePath/app/chanzhi/system/config/my.php";
        changePort($myFile, $params['-mp'], $myReg);
    }
    if(file_exists("$basePath/app/ranzhi/config/my.php"))
    {
        `chmod 777 $basePath/app/ranzhi/config/my.php`;
        $myFile = "$basePath/app/ranzhi/config/my.php";
        changePort($myFile, $params['-mp'], $myReg);
    }
}

if(!empty($argv)) $params['-k'] = reset($argv);
if(isset($params['-k']))
{
    if(strpos(file_get_contents('/etc/group'), 'nogroup') === false) echo `groupadd nogroup`;
    if(strpos(file_get_contents('/etc/passwd'), 'nobody') === false) echo `useradd nobody`;
    `chmod -R 777 $basePath/tmp`;
    `chmod -R 777 $basePath/logs`;
    `chown -R nobody $basePath/data/mysql`;

    switch($params['-k'])
    {
    case 'start':
        $httpd = `ps aux|grep '\/opt\/zbox\/run\/apache\/httpd '`;
        if($httpd)
        {
            echo "Apache is running\n";
        }
        else
        {
            echo `$basePath/run/apache/apachectl start`;
            sleep(2);
            $httpd = `ps aux|grep '\/opt\/zbox\/run\/apache\/httpd '`;
            echo empty($httpd) ? "Start Apache fail. You can see the log /opt/zbox/logs/apache_error.log\n" : "Start Apache success\n";
        }

        $mysql = `ps aux|grep '\/opt\/zbox\/run\/mysql\/mariadbd '`;
        if($mysql)
        {
            echo "Mysql is running\n";
        }
        else
        {
            echo `$basePath/run/mysql/mysql.server start --defaults-file=$basePath/etc/mysql/my.cnf`;
            sleep(2);
            $mysql = `ps aux|grep '\/opt\/zbox\/run\/mysql\/mariadbd '`;
            echo empty($mysql) ? "Start Mysql fail. You can see the log /opt/zbox/logs/mysql_error.log\n"   : "Start Mysql success\n";
        }

        $xxd = `ps aux|grep '.\/xxd'|grep -v 'grep'`;
        if($xxd)
        {
            echo "XXD is running\n";
        }
        else
        {
            $xxConfig = getXuanXuanKey();
            file_put_contents('/opt/zbox/run/xxd/config/xxd.conf', file_get_contents('/opt/zbox/run/xxd/config/xxd.conf.res') . "\n" . $xxConfig);

            $oldDir = getcwd();
            chdir("$basePath/run/xxd");
            echo `./xxd > /opt/zbox/logs/xxd.log &`;
            chdir($oldDir);

            sleep(2);
            $xxd = `ps aux|grep '.\/xxd'|grep -v 'grep'`;
            echo empty($xxd) ? "Start xxd fail.\n" : "Start xxd success\n";
        }
        break;
    case 'stop':
        $httpd = `ps aux|grep '\/opt\/zbox\/run\/apache\/httpd '`;
        if($httpd)
        {
            echo `$basePath/run/apache/apachectl stop`;
            sleep(2);
            $httpd = `ps aux|grep '\/opt\/zbox\/run\/apache\/httpd '`;
            echo empty($httpd) ? "Stop Apache success\n" : "Stop Apache fail. You can see the log /opt/zbox/logs/apache_error.log\n";
        }
        else
        {
            echo "Apache is not running\n";
        }

        $mysql = `ps aux|grep '\/opt\/zbox\/run\/mysql\/mariadbd '`;
        if($mysql)
        {
            echo `$basePath/run/mysql/mysql.server stop`;
            sleep(2);
            $mysql = `ps aux|grep '\/opt\/zbox\/run\/mysql\/mariadbd '`;
            echo empty($mysql) ? "Stop Mysql success\n"  : "Stop Mysql fail. You can see the log /opt/zbox/logs/mysql_error.log\n";
        }
        else
        {
            echo "Mysql is not running\n";
        }

        $xxd = `ps aux|grep '.\/xxd'|grep -v 'grep'`;
        if($xxd)
        {
            `ps aux|grep './xxd'| awk '{print $2}'|xargs sudo kill -9 `;
            sleep(2);
            $xxd = `ps aux|grep '.\/xxd'|grep -v 'grep'`;
            echo empty($xxd) ? "Stop xxd success.\n" : "Stop xxd fail\n";
        }
        else
        {
            echo "XXD is not running\n";
        }
        break;
    case 'restart':
        echo `$basePath/run/apache/apachectl restart`;
        sleep(2);
        $httpd = `ps aux|grep '\/opt\/zbox\/run\/apache\/httpd '`;
        echo empty($httpd) ? "Restart Apache fail. You can see the log /opt/zbox/logs/apache_error.log\n" : "Restart Apache success\n";

        echo `$basePath/run/mysql/mysql.server stop`;
        sleep(2);
        echo `$basePath/run/mysql/mysql.server start --defaults-file=$basePath/etc/mysql/my.cnf`;
        sleep(2);
        $mysql = `ps aux|grep '\/opt\/zbox\/run\/mysql\/mariadbd '`;
        echo empty($mysql) ? "Restart Mysql fail. You can see the log /opt/zbox/logs/mysql_error.log\n"   : "Restart Mysql success\n";

        echo `ps aux|grep './xxd'| awk '{print $2}'|xargs sudo kill -9`;
        $xxConfig = getXuanXuanKey();
        file_put_contents('/opt/zbox/run/xxd/config/xxd.conf', file_get_contents('/opt/zbox/run/xxd/config/xxd.conf.res') . "\n" . $xxConfig);

        $oldDir = getcwd();
        chdir("$basePath/run/xxd");
        echo `./xxd > /opt/zbox/logs/xxd.log &`;
        chdir($oldDir);

        sleep(2);
        $xxd = `ps aux|grep '.\/xxd'|grep -v 'grep'`;
        echo empty($xxd) ? "Restart xxd fail.\n"   : "Restart xxd success\n";
        break;
    case 'status':
        $httpd = `ps aux|grep '\/opt\/zbox\/run\/apache\/httpd '`;
        $mysql = `ps aux|grep '\/opt\/zbox\/run\/mysql\/mariadbd '`;
        $xxd   = `ps aux|grep '.\/xxd'|grep -v 'grep'`;
        echo empty($httpd) ? "Apache is not running\n" : "Apache is running\n";
        echo empty($mysql) ? "Mysql is not running\n" : "Mysql is running\n";
        echo empty($xxd) ? "XXD is not running\n" : "XXD is running\n";
    }
}

function changePort($file, $port, $regs)
{
    if(!is_array($regs)) $regs = array($regs);
    $lines = file($file);
    foreach($lines as $i => $line)
    {
        foreach($regs as $reg)
        {
            if(preg_match("/$reg/", $line, $matches)) $lines[$i] = str_replace($matches[1], $port, $line);
        }
    }
    file_put_contents($file, join($lines));
}

function getXuanXuanKey()
{
    global $basePath;
    $xxConfig = "[ranzhi]\n";
    if(file_exists("$basePath/app/zentao/config/my.php"))
    {
        $myFile = "$basePath/app/zentao/config/my.php";
        $dbh    = connectDB(getDbConfig($myFile));
        if($dbh)
        {
            $row = $dbh->query("select * from zt_config where `owner`='system' and `module`='xuanxuan' and `key`='key'")->fetch();
            $key = $row['value'];
            $xxConfig .= "zentao=http://127.0.0.1/zentao/xuanxuan.php,{$key},default\n";
        }
    }
    if(file_exists("$basePath/app/zentaopro/config/my.php"))
    {
        $myFile = "$basePath/app/zentaopro/config/my.php";
        $dbh    = connectDB(getDbConfig($myFile));
        if($dbh)
        {
            $row = $dbh->query("select * from zt_config where `owner`='system' and `module`='xuanxuan' and `key`='key'")->fetch();
            $key = $row['value'];
            $xxConfig .= "pro=http://127.0.0.1/pro/xuanxuan.php,{$key},default\n";
        }
    }
    if(file_exists("$basePath/app/zentaoep/config/my.php"))
    {
        $myFile = "$basePath/app/zentaoep/config/my.php";
        $dbh    = connectDB(getDbConfig($myFile));
        if($dbh)
        {
            $row = $dbh->query("select * from zt_config where `owner`='system' and `module`='xuanxuan' and `key`='key'")->fetch();
            $key = $row['value'];
            $xxConfig .= "biz=http://127.0.0.1/biz/xuanxuan.php,{$key},default\n";
        }
    }
    return $xxConfig;
}

function getDbConfig($configFile)
{
    $files = file($configFile);
    $dbConfig = new stdclass();
    foreach($files as $line)
    {
        if(strpos($line, '//') === 0) continue;
        $line = trim(trim($line), ';');
        if(strpos($line, 'db->host') !== false)     list($tmp, $dbConfig->host)     = explode('=', $line);
        if(strpos($line, 'db->port') !== false)     list($tmp, $dbConfig->port)     = explode('=', $line);
        if(strpos($line, 'db->user') !== false)     list($tmp, $dbConfig->user)     = explode('=', $line);
        if(strpos($line, 'db->password') !== false) list($tmp, $dbConfig->password) = explode('=', $line);
        if(strpos($line, 'db->name') !== false)     list($tmp, $dbConfig->name)     = explode('=', $line);
    }
    $dbConfig->host     = trim(trim(trim($dbConfig->host), "'"), '"');
    $dbConfig->port     = trim(trim(trim($dbConfig->port), "'"), '"');
    $dbConfig->user     = trim(trim(trim($dbConfig->user), "'"), '"');
    $dbConfig->password = trim(trim(trim($dbConfig->password), "'"), '"');
    $dbConfig->name     = trim(trim(trim($dbConfig->name), "'"), '"');

    return $dbConfig;
}

function connectDB($dbConfig)
{
    $dbh = null;
    $dsn = "mysql:host={$dbConfig->host}; port={$dbConfig->port}; dbname={$dbConfig->name}";
    try
    {
        $dbh = new PDO($dsn, $dbConfig->user, $dbConfig->password);
        $dbh->exec("SET NAMES UTF-8");
    }
    catch (PDOException $exception)
    {
    }
    return $dbh;
}

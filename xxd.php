#!/opt/zbox/bin/php
<?php
array_shift($argv);
$flipArgv = array_flip($argv);
$basePath = dirname(dirname(__FILE__));

//if($basePath != '/opt/zbox') die("Run it in path /opt/zbox/\n");
if(empty($argv) or isset($flipArgv['--help']) or isset($flipArgv['-h']))
{
    echo <<<EOD
Usage: xxd.php {start|stop|restart|status}

Options:
    -h --help Show help.

EOD;
    exit;
}

/* Process argv. */
$params = array();
foreach($flipArgv as $key => $val)
{
    if(strpos($key, '-') !== 0) continue;
    if(isset($argv[$val + 1]) and is_numeric($argv[$val + 1]))
    {
        $params[$key] = $argv[$val + 1];
        unset($argv[$val]);
        unset($argv[$val + 1]);
    }
}

if(!empty($argv)) $params['-k'] = reset($argv);
if(isset($params['-k']))
{
    switch($params['-k'])
    {
    case 'start':
        $xxd = `ps aux|grep '.\/xxd'|grep -v 'grep'|grep -v 'xxd.php'`;
        if($xxd)
        {
            echo "XXD is running\n";
        }
        else
        {
            while(true)
            {
                if(file_exists('/opt/zbox/run/xxd/config/xxd.conf')) break;
                getXXKey();
                sleep(1);
            }
            echo `cd $basePath/run/xxd; ./xxd > /opt/zbox/logs/xxd.log &`;

            sleep(2);
            $xxd = `ps aux|grep '.\/xxd'|grep -v 'grep'|grep -v 'xxd.php'`;
            echo empty($xxd) ? "Start xxd fail.\n" : "Start xxd success\n";
        }
        break;
    case 'stop':
        $xxd = `ps aux|grep '.\/xxd'|grep -v 'grep'|grep -v 'xxd.php'`;
        if($xxd)
        {
            `ps aux|grep './xxd'| awk '{print $2}'|xargs sudo kill -9 `;
            sleep(2);
            $xxd = `ps aux|grep '.\/xxd'|grep -v 'grep'|grep -v 'xxd.php'`;
            echo empty($xxd) ? "Stop xxd success.\n" : "Stop xxd fail\n";
        }
        else
        {
            echo "XXD is not running\n";
        }
        break;
    case 'restart':
        echo `ps aux|grep './xxd'| awk '{print $2}'|xargs sudo kill -9`;

        while(true)
        {
            if(file_exists('/opt/zbox/run/xxd/config/xxd.conf')) break;
            getXXKey();
            sleep(1);
        }
        echo `cd $basePath/run/xxd; ./xxd > /opt/zbox/logs/xxd.log &`;

        sleep(2);
        $xxd = `ps aux|grep '.\/xxd'|grep -v 'grep'|grep -v 'xxd.php'`;
        echo empty($xxd) ? "Restart xxd fail.\n"   : "Restart xxd success\n";
        break;
    case 'status':
        $xxd   = `ps aux|grep '.\/xxd'|grep -v 'grep'|grep -v 'xxd.php'`;
        echo empty($xxd) ? "XXD is not running\n" : "XXD is running\n";
    }
}

function getXXKey()
{
    global $basePath;
    $xxKey    = array();
    $xxConfig = '';
    if(file_exists("$basePath/app/zentao/config/my.php"))
    {
        $myFile = "$basePath/app/zentao/config/my.php";
        $dbh    = connectDB(getDbConfig($myFile));
        if($dbh)
        {
            $xxKey     = setXXKey($dbh);
            $key       = $xxKey['key'];
            $default   = empty($xxConfig) ? ',default' : '';
            $xxConfig .= "zentao=http://127.0.0.1/zentao/x.php,{$key}{$default}\n";
        }
    }
    if(file_exists("$basePath/app/zentaopro/config/my.php"))
    {
        $myFile = "$basePath/app/zentaopro/config/my.php";
        $dbh    = connectDB(getDbConfig($myFile));
        if($dbh)
        {
            $xxKey     = setXXKey($dbh);
            $key       = $xxKey['key'];
            $default   = empty($xxConfig) ? ',default' : '';
            $xxConfig .= "pro=http://127.0.0.1/pro/x.php,{$key}{$default}\n";
        }
    }
    if(file_exists("$basePath/app/zentaoep/config/my.php"))
    {
        $myFile = "$basePath/app/zentaoep/config/my.php";
        $dbh    = connectDB(getDbConfig($myFile));
        if($dbh)
        {
            $xxKey     = setXXKey($dbh);
            $key       = $xxKey['key'];
            $default   = empty($xxConfig) ? ',default' : '';
            $xxConfig .= "biz=http://127.0.0.1/biz/x.php,{$key}{$default}\n";
        }
    }

    if($xxConfig) $xxKey['server'] = $xxConfig;

    if($xxKey)
    {
        if(!file_exists('/opt/zbox/run/xxd/config/xxd.conf'))
        {
            $xxdConf  = str_replace(array('%ip%', '%chatPort%', '%commonPort%', '%isHttps%', '%server%'), 
            array($xxKey['ip'], $xxKey['chatPort'], $xxKey['commonPort'], $xxKey['isHttps'], $xxKey['server']),
            file_get_contents('/opt/zbox/run/xxd/config/xxd.conf.res'));
            file_put_contents('/opt/zbox/run/xxd/config/xxd.conf', $xxdConf);
        }
        else
        {
            $lines     = file('/opt/zbox/run/xxd/config/xxd.conf');
            $xxdConf   = '';
            $newServer = true;
            foreach($lines as $line)
            {
                if(strpos($line, 'x.php') !== false)
                {
                    if($newServer) $xxdConf .= $xxConfig;
                    $newServer = false;
                    continue;
                }
                $xxdConf .= $line;
            }
        }
    }
}

function setXXKey($dbh)
{
    $sn = md5(mt_rand(0, 99999999) . microtime());
    $xxKey['turnon']     = 1;
    $xxKey['key']        = $sn;
    $xxKey['chatPort']   = 11444;
    $xxKey['commonPort'] = 11443;
    $xxKey['ip']         = '0.0.0.0';
    $xxKey['isHttps']    = 'off';

    $rows = $dbh->query("select * from zt_config where `owner`='system' and `module`='common' and `section`='xuanxuan' and `key`='key'")->fetchAll();
    if(!empty($rows))
    {
        foreach($rows as $row) $xxKey[$row['key']] = $row['value'];
        return $xxKey;
    }

    $dbh->exec("REPLACE INTO zt_config SET `owner`='system', `module`='common', `section`='xuanxuan', `key`='turnon', `value`='1'");
    $dbh->exec("REPLACE INTO zt_config SET `owner`='system', `module`='common', `section`='xuanxuan', `key`='key', `value`='{$sn}'");
    $dbh->exec("REPLACE INTO zt_config SET `owner`='system', `module`='common', `section`='xuanxuan', `key`='chatPort', `value`='11444'");
    $dbh->exec("REPLACE INTO zt_config SET `owner`='system', `module`='common', `section`='xuanxuan', `key`='commonPort', `value`='11443'");
    $dbh->exec("REPLACE INTO zt_config SET `owner`='system', `module`='common', `section`='xuanxuan', `key`='ip', `value`='0.0.0.0'");
    $dbh->exec("REPLACE INTO zt_config SET `owner`='system', `module`='common', `section`='xuanxuan', `key`='isHttps', `value`='off'");

    return $xxKey;
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

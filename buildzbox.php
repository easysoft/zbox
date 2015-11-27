<?php
$opath = dirname(__FILE__);
chdir($opath);
array_shift($argv);
$type = empty($argv) ? 'common' : reset($argv);
if(!file_exists("$opath/httpd.$type.conf")) die("Do not found file httpd.$type.conf.");
if(!is_dir($opath . '/build'))mkdir($opath . '/build');
$buildPath = $opath . '/build';

function zexec($command)
{
    echo $command . "\n";
    echo system($command, $code);
    echo "\n";
    if($code) exit;
}

$basePath = '/opt/zbox';
if(!is_dir($basePath))
{
    zexec("mkdir $basePath");
    zexec("cp zbox $basePath/");
    chdir($basePath);
    zexec("mkdir app auth bin data etc logs run tmp");
    zexec("mkdir app/htdocs data/mysql tmp/php tmp/apache tmp/mysql etc/php etc/mysql");
    zexec("chmod -R 777 tmp");
    zexec("cp -r $opath/adminer $basePath/app");
    zexec("cp -r $opath/adduser.sh $basePath/auth");
    zexec("touch $basePath/auth/users");
}

chdir($buildPath);
if(!file_exists('/root/bin/patchelf'))
{
    if(!file_exists('patchelf-0.8.tar.gz')) zexec('wget http://nixos.org/releases/patchelf/patchelf-0.8/patchelf-0.8.tar.gz');
    zexec('rm -rf patchelf-0.8; tar zxvf patchelf-0.8.tar.gz');
    chdir($buildPath . '/patchelf-0.8/');
    zexec("./configure --prefix=$buildPath/patchelf");
    zexec('make && make install');
    zexec("mkdir /root/bin; cp $buildPath/patchelf/bin/patchelf /root/bin");
}

if(!file_exists("$basePath/apachefinish"))
{
    /* Download apache. */
    $apacheVersion = '2.4.17';
    if(!file_exists("httpd-{$apacheVersion}.tar.gz"))   zexec("wget http://apache.fayea.com//httpd/httpd-{$apacheVersion}.tar.gz");
    if(!file_exists('apr-1.5.2.tar.gz'))      zexec('wget http://mirrors.hust.edu.cn/apache/apr/apr-1.5.2.tar.gz');
    if(!file_exists('apr-util-1.5.4.tar.gz')) zexec('wget http://mirrors.hust.edu.cn/apache/apr/apr-util-1.5.4.tar.gz');
    zexec("rm -rf httpd-{$apacheVersion}; tar zxvf httpd-{$apacheVersion}.tar.gz");

    zexec("cp apr-1.5.2.tar.gz apr-util-1.5.4.tar.gz $buildPath/httpd-{$apacheVersion}/srclib");
    chdir($buildPath . "/httpd-{$apacheVersion}/srclib/");
    zexec('tar zxvf apr-1.5.2.tar.gz');
    zexec('tar zxvf apr-util-1.5.4.tar.gz');
    zexec('mv apr-1.5.2 apr; mv apr-util-1.5.4 apr-util; rm apr-1.5.2.tar.gz apr-util-1.5.4.tar.gz');

    /* Compile apache. */
    chdir($buildPath . "/httpd-{$apacheVersion}/");
    zexec('./configure --prefix=/opt/zbox/run/apache --bindir=/opt/zbox/run/apache \
        --sbindir=/opt/zbox/run/apache \
        --sysconfdir=/opt/zbox/etc/apache \
        --libdir=/opt/zbox/run/lib \
        --enable-mods-shared=all --enable-so --with-included-apr');
    zexec('make && make install');
    zexec("touch $basePath/apachefinish");
}

chdir($buildPath);
if(!file_exists("$basePath/mysqlfinish"))
{
    /* Download mysql. */
    $mysqlVersion = '5.5.45';
    if(!file_exists("mysql-{$mysqlVersion}.tar.gz")) zexec("wget http://dev.mysql.com/get/Downloads/MySQL-5.5/mysql-{$mysqlVersion}.tar.gz");
    zexec("rm -rf mysql-{$mysqlVersion}; tar zxvf mysql-{$mysqlVersion}.tar.gz");

    /* Compile php. */
    chdir($buildPath . "/mysql-{$mysqlVersion}");
    zexec('cmake . -DCMAKE_INSTALL_PREFIX=/opt/zbox/run/mysql \
        -DSYSCONFDIR=/opt/zbox/etc/mysql \
        -DINSTALL_LIBDIR=/opt/zbox/run/lib/ \
        -DINSTALL_PLUGINDIR=/opt/zbox/run/lib/mysql/plugin \
        -DEXTRA_CHARSETS=all \
        -DDEFAULT_CHARSET=utf8 -DDEFAULT_COLLATION=utf8_general_ci');
    zexec('make && make install');
    zexec("touch $basePath/mysqlfinish");
}

chdir($buildPath);
if(!file_exists("$basePath/phpfinish"))
{
    /* Download php. */
    `apt-get install mysql-server`;
    $phpVersion = '5.6.13';
    if(!file_exists("php-{$phpVersion}.tar.bz2")) zexec("wget http://cn2.php.net/distributions/php-{$phpVersion}.tar.bz2");
    //if(!file_exists('php-7.0.0RC3.tar.gz')) zexec('wget https://downloads.php.net/~ab/php-7.0.0RC3.tar.gz');
    zexec("rm -rf php-{$phpVersion}; tar jxvf php-{$phpVersion}.tar.bz2");
    //zexec('rm -rf php-7.0.0RC3; tar zxvf php-7.0.0RC3.tar.gz');


    /* Compile php. */
    chdir($buildPath . "/php-{$phpVersion}");
    //chdir($buildPath . '/php-7.0.0RC3');
    zexec('./configure --prefix=/opt/zbox/run/ \
        --bindir=/opt/zbox/run/php \
        --libdir=/opt/zbox/run/lib \
        --sysconfdir=/opt/zbox/etc/apache \
        --with-apxs2=/opt/zbox/run/apache/apxs \
        --with-config-file-path=/opt/zbox/etc/php \
        --enable-mbstring --enable-bcmath --enable-sockets --disable-ipv6 \
        --with-curl --with-openssl \
        --with-gd --enable-gd-native-ttf --enable-gd-jis-conv \
        --with-ldap --with-ldap-sasl \
        --enable-zip --with-zlib --with-bz2 \
        --with-mysqli --with-pdo-mysql');
    zexec('make && make install');
    zexec("touch $basePath/phpfinish");
}

/* Simplify apache */
zexec("mkdir $basePath/run/newapache");
chdir("$basePath/run/apache");
zexec("cp ab htpasswd httpd $basePath/run/newapache/");
zexec("cp $opath/apachectl $basePath/run/newapache;chmod a+x $basePath/run/newapache/apachectl");
zexec("mkdir $basePath/run/newapache/modules");
chdir("$basePath/run/apache/modules");
zexec("cp libphp5.so mod_authn_file.so mod_access_compat.so mod_alias.so mod_authn_core.so mod_auth_basic.so mod_authz_core.so mod_authz_host.so mod_authz_user.so mod_autoindex.so mod_deflate.so mod_dir.so mod_env.so mod_expires.so mod_filter.so mod_log_config.so mod_mime.so mod_rewrite.so mod_setenvif.so mod_unixd.so mod_ssl.so mod_macro.so mod_headers.so $basePath/run/newapache/modules");
chdir($basePath);
zexec("rm -rf $basePath/run/apache");
zexec("rm -rf $basePath/run/lib/pkgconfig");
zexec("mv $basePath/run/newapache $basePath/run/apache");
zexec("mkdir $basePath/etc/newapache");
chdir($opath);
zexec("cp httpd.$type.conf $basePath/etc/newapache/httpd.conf");
zexec("cp $basePath/etc/apache/mime.types $basePath/etc/newapache");
zexec("rm -rf $basePath/etc/apache");
zexec("mv $basePath/etc/newapache $basePath/etc/apache");
$indexFile = file_exists($opath . "/index.$type.php") ? "index.$type.php" : 'index.common.php';
zexec("cp $indexFile $basePath/app/htdocs/index.php");

/* Simplify mysql */
zexec("mkdir $basePath/run/newmysql");
chdir($opath);
zexec("cp my.cnf $basePath/etc/mysql/my.cnf");
chdir("$basePath/run/mysql");
zexec("scripts/mysql_install_db --basedir=$basePath/run/mysql --datadir=$basePath/data/mysql --defaults-file=$basePath/etc/mysql/my.cnf --user=nobody");
chdir("$basePath/run/mysql/bin");
zexec("cp my_print_defaults mysql mysqld mysqld_safe mysqldump $basePath/run/newmysql");
zexec("cp $opath/mysql.server $basePath/run/newmysql;chmod a+x $basePath/run/newmysql/mysql.server");
zexec("mkdir -p $basePath/run/newmysql/share/english");
zexec("cp $basePath/run/mysql/share/english/errmsg.sys $basePath/run/newmysql/share/english");
chdir($basePath);
zexec("rm -rf $basePath/run/mysql $basePath/run/lib/mysql");
if(is_dir("$basePath/data/mysql/test")) zexec("rm -rf $basePath/data/mysql/test");
zexec("mv $basePath/run/newmysql $basePath/run/mysql");
zexec("sed -i 's/\/opt\/zbox\/run\/mysql\/bin/\/opt\/zbox\/run\/mysql/g' $basePath/run/mysql/mysqld_safe");
zexec("sed -i 's/\/opt\/zbox\/run\/mysql\/\/opt\/zbox\/run/\/opt\/zbox\/run/g' $basePath/run/mysql/mysqld_safe");

/* Simplify php */
chdir($opath);
zexec("cp php.ini $basePath/etc/php/php.ini");
zexec("mkdir -p $basePath/run/newphp/lib");
chdir("$basePath/run/php");
zexec("cp php $basePath/run/newphp");
zexec("cp $opath/php_ioncube.so $basePath/run/newphp/lib/php_ioncube.so");
$uname = `uname -m`;
if(strpos($uname, '_64') !== false) zexec("cp $opath/php_ioncube_x64.so $basePath/run/newphp/lib/php_ioncube.so");
exec("find $basePath/run/lib/extensions/ -name *opcache.so", $output);
foreach($output as $opcachePath) zexec("cp $opcachePath $basePath/run/newphp/lib/php_opcache.so");
chdir("$basePath/run");
zexec("rm -rf $basePath/run/php $basePath/run/include $basePath/run/var $basePath/run/lib/build $basePath/run/lib/extensions $basePath/run/lib/php");
zexec("mv $basePath/run/newphp $basePath/run/php");

zexec("cp $opath/README $basePath");

/* Copy lib */
chdir("$basePath/run");
$allLib       = array();
$runDirs      = array('apache', 'mysql', 'php', 'lib');
$interpreters = array();
foreach($runDirs as $runDir)
{
    exec("find $basePath/run/$runDir -name '*'", $files);
    foreach($files as $file)
    {
        if(is_dir($file)) continue;
        exec("ldd $file", $ldds, $result);
        if($result) continue;
        $interpreter = '';
        foreach($ldds as $ldd)
        {
            if(strpos($ldd, '=>') === false)
            {
                $interpreter = trim($ldd);
                continue;
            }
            list($so, $realSo) = explode('=>', $ldd);
            list($realSo) = explode(' ', trim($realSo));
            if(!file_exists($realSo)) continue;
            $so = basename($realSo);
            if(is_link($realSo) and file_exists("$basePath/run/lib/" . $so))
            {
                $readlink = readlink($realSo);
                $linkSo   = basename($readlink);
                $allLib[$linkSo] = $linkSo;
            }
            if(!file_exists("$basePath/run/lib/" . $so)) zexec("cp $realSo $basePath/run/lib/");
            $allLib[$so] = $so;
        }

        zexec("strip --strip-debug $file");
        if($interpreter)
        {
            $interpreter = trim(substr($interpreter, 0, strpos($interpreter, '(')));
            $interpreterName = basename($interpreter);
            if($interpreterName)
            {
                if(!file_exists($basePath . '/run/lib/' . $interpreterName)) zexec("cp $interpreter $basePath/run/lib/$interpreterName");
                echo system("/root/bin/patchelf --set-interpreter $basePath/run/lib/$interpreterName $file", $result);
                $interpreters[$interpreter] = $interpreter;
            }
        }
    }
}

foreach(glob("$basePath/run/lib/*") as $file)
{
    if(is_dir($file)) continue;
    $so = basename($file);
    if(!isset($allLib[$so])) zexec("rm $file");
}

foreach($runDirs as $runDir)
{
    exec("find $basePath/run/$runDir -name '*'", $files);
    foreach($files as $file)
    {
        if(is_dir($file) or !file_exists($file)) continue;
        exec("ldd $file", $ldds, $result);
        if(!$result) echo `/root/bin/patchelf --set-rpath $basePath/run/lib/ $file`;
    }
}

foreach($interpreters as $interpreter)
{
    $interpreterName = basename($interpreter);
    zexec("cp $interpreter $basePath/run/lib/$interpreterName");
}

zexec("ln -s $basePath/run/apache/apachectl $basePath/bin/");
zexec("ln -s $basePath/run/apache/htpasswd $basePath/bin/");
zexec("ln -s $basePath/run/apache/httpd $basePath/bin/");
zexec("ln -s $basePath/run/mysql/mysqld_safe $basePath/bin/");
zexec("ln -s $basePath/run/mysql/mysql.server $basePath/bin/");
zexec("ln -s $basePath/run/mysql/mysql $basePath/bin/");
zexec("ln -s $basePath/run/php/php $basePath/bin/");

zexec("rm $basePath/apachefinish $basePath/mysqlfinish $basePath/phpfinish");

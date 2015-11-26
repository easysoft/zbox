<?php
error_reporting(0);

if(isset($_GET['mode']) and $_GET['mode'] == 'phpinfo') die(phpinfo());
if(isset($_GET['mode']) and $_GET['mode'] == 'getlogo')
{
    header('Content-type: image/png');  
    die(base64_decode('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'));
}

$config->langs['cn'] = '简体';
$config->langs['en'] = 'English';

$lang->cn->title = '欢迎使用蝉知集成运行环境！';

$lang->cn->links['chanzhi']['link']       = '/';
$lang->cn->links['chanzhi']['text']       = '访问蝉知';
$lang->cn->links['chanzhi']['target']     = '_self';

$lang->cn->links['official']['link']     = 'http://www.chanzhi.org/';
$lang->cn->links['official']['text']     = '蝉知官网';
$lang->cn->links['official']['target']   = '_blank';

$lang->cn->links['adminer']['link']   = '/adminer/index.php?server=localhost:3306&username=root';
$lang->cn->links['adminer']['text']   = '数据库管理';
$lang->cn->links['adminer']['target'] = '_blank';

$lang->cn->links['phpinfo']['link']      = '?mode=phpinfo';
$lang->cn->links['phpinfo']['text']      = 'PHP信息';
$lang->cn->links['phpinfo']['target']    = '_blank';

$lang->en->title = 'Welcome to use chanzhi!';

$lang->en->links['chanzhi']['link']       = '/';
$lang->en->links['chanzhi']['text']       = 'ChanZhi';
$lang->en->links['chanzhi']['target']     = '_self';

$lang->en->links['official']['link']     = 'http://www.chanzhi.org/';
$lang->en->links['official']['text']     = 'Community';
$lang->en->links['official']['target']   = '_blank';

$lang->en->links['adminer']['link']   = '/adminer/index.php?server=localhost:3306&username=root';
$lang->en->links['adminer']['text']   = 'MySQL';
$lang->en->links['adminer']['target'] = '_blank';

$lang->en->links['phpinfo']['link']      = '?mode=phpinfo';
$lang->en->links['phpinfo']['text']      = 'PHP';
$lang->en->links['phpinfo']['target']    = '_blank';

$acceptLang = stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'zh-CN') !== false ? 'cn' : 'en';
$acceptLang = isset($_GET['lang']) ? $_GET['lang'] : $acceptLang;
$clientLang = $lang->$acceptLang;
?>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
  <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
  <meta http-equiv="refresh" content="10; url=/chanzhi/" />
  <title><?php echo $clientLang->title;?></title>
  <style>
    html {background-color:#06294e;}
    body {font-family: Tahoma; font-size:14px}
    table{margin-top:200px; background:white; border:none}
    tr, th, td{border:none}
    a{text-decoration:none}

    #welcome{font-size:20px; border-bottom:1px solid #efefef; padding:10px}
    #logo{width:120px; height:120px; border-right:1px solid #efefef}
    
    #links{padding-left:25px; font-size:14px}
    #links a{display:block; width:100px; height:25px; line-height:25px; float:left; margin-right:5px; border:1px solid gray; background:#efefef; text-align:center}
    #links #chanzhi{background:green; color:#efefef}
    
    #lang{background:#efefef; font-size:13px; padding:3px}
  </style>
</head>
<body>
  <table align='center' width='600'>
    <tr><th colspan='2' id='welcome'><?php echo $clientLang->title;?></th></tr>
    <tr valign='middle'>
      <td id='logo'><img src='?mode=getlogo' /></td>
      <td id='links'><?php foreach($clientLang->links as $linkID => $link) echo "<a id='$linkID' href='$link[link]' target='$link[target]'>$link[text]</a>";?></td>
    </tr>   
    <tr id='lang'>
      <td colspan='2' align='right'>
        <div style='float:right;'><?php foreach($config->langs as $langCode => $langName) echo "<a href='?lang=$langCode'>$langName</a> ";?></div>
      </td>
    </tr>
  </table>
</body>
</html>

<?php
error_reporting(0);

if(isset($_GET['mode']) and $_GET['mode'] == 'getlogo')
{
    header('Content-type: image/png');  
    die(base64_decode('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'));
}

$config->langs['cn'] = '简体';
$config->langs['en'] = 'English';

$lang->cn->links['zentao']['link']       = '/zentao/';
$lang->cn->links['zentao']['text']       = '开源版';
$lang->cn->links['zentao']['target']     = '_self';

$lang->cn->links['zentaopro']['link']    = '/pro/';
$lang->cn->links['zentaopro']['text']    = '专业版';
$lang->cn->links['zentaopro']['target']  = '_self';

$lang->cn->title    = '欢迎使用禅道集成运行环境！';
$lang->cn->official = " <a href='http://www.zentao.net' target='_blank'>禅道官网</a>";
$lang->cn->adminer  = "<a href='/adminer/index.php?server=localhost:3306&username=root' target='_blank'>数据库管理</a>";

$lang->en->links['zentao']['link']       = '/zentao/';
$lang->en->links['zentao']['text']       = 'Zentao';
$lang->en->links['zentao']['target']     = '_self';

$lang->en->links['zentaopro']['link']    = '/pro/';
$lang->en->links['zentaopro']['text']    = 'Pro version';
$lang->en->links['zentaopro']['target']  = '_self';

$lang->en->title    = 'Welcome to use zentao!';
$lang->en->official = " <a href='http://www.zentao.net/en' target='_blank'>Community</a>";
$lang->en->adminer  = "<a href='/adminer/index.php?server=localhost:3306&username=root' target='_blank'>MySQL</a>";

$acceptLang = stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'zh-CN') !== false ? 'cn' : 'en';
$acceptLang = isset($_GET['lang']) ? $_GET['lang'] : $acceptLang;
$clientLang = $lang->$acceptLang;

?>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
  <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
  <title><?php echo $clientLang->title;?></title>
  <style>
    html {background-color:#06294e;}
    body {font-family: Tahoma; font-size:14px}
    table{margin-top:200px; background:white; border:none}
    tr, th, td{border:none}
    a{text-decoration:none;}

    #welcome{font-size:20px; border-bottom:1px solid #efefef; padding:10px}
    #logo{width:120px; border-right:1px solid #efefef}
   
    #links{padding-left:110px; font-size:14px;}
    #links a{display:block; width:100px; height:25px; line-height:25px; float:left; margin-right:10px; border:1px solid gray; background:#efefef; text-align:center}
    #links #zentao{background:green; color:#efefef}
    
    #lang{background:#efefef; font-size:13px}
  </style>
</head>
<script src='/zentao/js/jquery/lib.js' type='text/javascript'></script>
<body>
  <table align='center' width='700'>
    <tr><th colspan='2' id='welcome'><?php echo $clientLang->title;?></th></tr>
    <tr>
      <td id='logo'><img src='?mode=getlogo' /></td>
      <td id='links'><?php foreach($clientLang->links as $linkID => $link) echo "<a id='$linkID' href='$link[link]' target='$link[target]'>$link[text]</a>";?></td>
    </tr>   
    <tr id='lang'>
      <td colspan='2'>
        <div style='float:left;'><?php foreach($config->langs as $langCode => $langName) echo "<a href='?lang=$langCode'>$langName</a> ";?></div>
        <div style='float:right'><?php echo $clientLang->official . ' ' . $clientLang->adminer;?></div>
      </td>
    </tr>
  </table>
</body>
<script>
$(function()
{
	$.getJSON('/pro/api-getLicenses.html', function(data)
	{
    	if(!data) return false;
        if(data.company == 'try') $('#zentaopro').append('<?php echo $_GET['lang'] == 'en' ? ' try' : ' 试用'?>');
        <?php
        $allLife    = $_GET['lang'] == 'en' ? 'All Life' : '永久授权';
        $expireDate = $_GET['lang'] == 'en' ? 'Expire Date : ' : '到期时间：';
        $unlimited  = $_GET['lang'] == 'en' ? 'Unlimited' : '不限人数';
        $limitUser  = $_GET['lang'] == 'en' ? 'Authorized : ' : '授权人数：';
        ?>
        var expireDate = data.expireDate.toLowerCase() == 'all life' ? '<?php echo $allLife;?>' : '<?php echo $expireDate;?>' + data.expireDate;
        var users      = data.user == 0 ? '<?php echo $unlimited;?>' : '<?php echo $limitUser;?>' + data.user;
		$('#zentaopro').attr('title', expireDate + ' ' + users);
	});
});
</script>
</html>

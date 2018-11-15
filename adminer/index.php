<?php
function getwebroot(){};
function adminer_object()
{
    class AdminerHost extends Adminer
    {
	    function loginForm()
		{
			$config = new stdclass();
            $config->db = new stdclass();
            $config->default = new stdclass();
            $myFiles = glob(dirname(dirname(__FILE__)) . '/zentao*/config/my.php');
            if(!empty($myFiles)) include $myFiles[0];

			$drivers = array("server" => "MySQL");
		    echo "<table cellspacing='0'>\n";
		    echo $this->loginFormField('driver', '<tr><th>' . lang('System') . '<td>', html_select("auth[driver]", $drivers, 'server') . "\n");
		    echo $this->loginFormField('server', '<tr><th>' . lang('Server') . '<td>', '<input name="auth[server]" value="' . "{$config->db->host}:{$config->db->port}" . '" title="hostname[:port]" placeholder="localhost" autocapitalize="off">' . "\n");
		    echo $this->loginFormField('username', '<tr><th>' . lang('Username') . '<td>', '<input name="auth[username]" id="username" value="' . $config->db->user . '" autocapitalize="off">' . script("focus(qs('#username'));"));
		    echo $this->loginFormField('password', '<tr><th>' . lang('Password') . '<td>', '<input type="password" name="auth[password]">' . "\n");
		    echo $this->loginFormField('db', '<tr><th>' . lang('Database') . '<td>', '<input name="auth[db]" value="' . h($_GET["db"]) . '" autocapitalize="off">' . "\n");
		    echo "</table>\n";
		    echo "<p><input type='submit' value='" . lang('Login') . "'>\n";
		    echo checkbox("auth[permanent]", 1, $_COOKIE["adminer_permanent"], lang('Permanent login')) . "\n";
	    }
    }
    return new AdminerHost;
}
include "./adminer.php";
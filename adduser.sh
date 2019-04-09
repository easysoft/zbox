echo "This tool is used to add user to access adminer";
read -p "Account: " account
read -s -p "Password: " password
/opt/zbox/bin/htpasswd -b /opt/zbox/auth/users $account $password
chmod 777 /opt/zbox/auth/users

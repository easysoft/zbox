echo "This tool is used to add user to access adminer";
read -p "Account: " account
read -s -p "Password: " password
../bin/htpasswd -b users $account $password

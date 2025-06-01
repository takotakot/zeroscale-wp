sudo apt update
sudo apt install mysql-server nano -y

cat init_mysql.sql | sudo mysql

# sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf 
# bind 0.0.0.0 

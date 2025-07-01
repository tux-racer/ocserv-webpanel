# ocserv-webpanel
ocserv admin webpanel This is a simple tool for ocserv or openconnect server that helps to add, delete and monitor users.

Requirement
  Apache or Nginx with PHP and of course ocsrv which is already running well
  
1. 	add sudo www-data on /etc/sudoers

	  www-data ALL=(ALL) NOPASSWD: /usr/bin/ocpasswd, /usr/bin/occtl, /bin/chmod

3. 	file admin panel etc/ocserv/paneladmin
   
	on terminal with apache2-utils htpasswd -c /etc/ocserv/paneladmin admin

   	like admin:$2y$10$jjKj/0MP5Eb9AJuWK6qbv2134sgacdEoUWhRHc6aNP458a9yzs6Ic

	or on terminal php -r 'echo password_hash("thispasswd", PASSWORD_DEFAULT) . PHP_EOL;'

![Screenshot at 2025-07-01 11-54-34](https://github.com/user-attachments/assets/272cfb28-b63a-4947-8ea1-f4a593c09867)

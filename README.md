BoApi 
===== 

common services for our portals 

Install 
------- 

	git clone git@github.com:berlinonline/BoApi.git 
	( git checkout geo ) 
	git submodule init 
	git submodule update 

Dependencies 
------------ 

* MySQL - for the user service 
* elasticsearch - for geo service 

Setup 
----- 

	bin/configure-env --init 
	bin/cli pulq.database -db geocache -action create 

Apache Konfiguration 
-------------------- 

	RewriteEngine On 
	Alias /boapi /srv/www/BoApi/pub 
	RewriteCond %{REQUEST_URI} !^/boapi/(static|favicon.ico|robots.txt) 
	RewriteRule ^/boapi(.*)$ /srv/www/BoApi/pub/index.php?$1 [QSA,L] 
	<Directory /srv/www/BoApi/pub> 
		Order deny,allow 
		AllowOverride All 
		Options None 
		DirectoryIndex index.php 
	</Directory>

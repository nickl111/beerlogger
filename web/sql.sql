CREATE DATABASE IF NOT EXISTS `beerlogger`;

CREATE USER IF NOT EXISTS 'beerman' IDENTIFIED BY 'c21n09487b';
GRANT USAGE ON *.* TO 'beerman'@localhost IDENTIFIED BY 'c21n09487b';
GRANT ALL privileges ON `beerlogger`.* TO 'beerman'@localhost;
FLUSH PRIVILEGES;

USE `beerlogger`;

CREATE TABLE brew (
	id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	recipe_id INTEGER default NULL,
	name TEXT default NULL,
	color INTEGER default NULL,
	ts_start INTEGER default NULL,
	ts_end INTEGER default NULL,
	notes TEXT default NULL,
	g_orig DECIMAL(4,3) default NULL,
	mash_ph DECIMAL(3,2) default NULL,
	vol_ferment DECIMAL(5,2) default NULL,
	g_final DECIMAL(4,3) default NULL,
	vol_bottle DECIMAL(5,2) default NULL,
	target_temp DECIMAL(3,1) default NULL
);

CREATE TABLE recipe (
	id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name TEXT default NULL,
	notes TEXT default NULL,
	yeast_id INTEGER default NULL
);

CREATE TABLE yeast (
	id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name TEXT default NULL,
	description TEXT default NULL
);

CREATE TABLE sample (
	id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	brew_id INTEGER default NULL,
	ts INTEGER default NULL,
	sg DECIMAL(8,4) default NULL,
	note TEXT default NULL
);

CREATE TABLE data (
	ts INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	color INTEGER default NULL,
	beer_temp INTEGER default NULL,
	sg INTEGER default NULL
);

CREATE TABLE archive (
	ts INTEGER NOT NULL,
	binLength INTEGER default 0,
	color INTEGER default NULL,
	datacount INTEGER default NULL,
	beer_temp DECIMAL(5,2) default NULL,
	sg DECIMAL(6,2) default NULL,
	sg_sd DECIMAL(4,2) default NULL,
	PRIMARY KEY (ts,binLength)
);

CREATE TABLE note (
	id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	parent_id INTEGER default NULL,
	parent_class TEXT default NULL,
	ts_created INTEGER default NULL,
	ts_event INTEGER default NULL,
	text TEXT default NULL,
	acknowledged INTEGER default 0
)
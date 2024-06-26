-- Create DB
CREATE DATABASE IF NOT EXISTS `|SQL_DB|`;

-- Create user and set privileges
CREATE USER IF NOT EXISTS '|SQL_USER|' IDENTIFIED BY '|SQL_PASS|';
GRANT USAGE ON *.* TO '|SQL_USER|'@|SQL_HOST| IDENTIFIED BY '|SQL_PASS|';
GRANT ALL privileges ON `|SQL_DB|`.* TO '|SQL_USER|'@|SQL_HOST|;
FLUSH PRIVILEGES;

USE `|SQL_DB|`;

-- Brewing Session
CREATE TABLE brew (
	id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	recipe_id INTEGER default NULL KEY,
	schedule_id INTEGER default NULL KEY,
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
	target_temp DECIMAL(3,1) default NULL,
	ts_dryhop INTEGER default NULL,
	g_dryhop DECIMAL(4,3) default NULL,
	calib_g_low_ref DECIMAL(4,3) default NULL,
	calib_g_high_ref DECIMAL(4,3) default NULL,
	calib_g_low_raw DECIMAL(4,3) default NULL,
	calib_g_high_raw DECIMAL(4,3) default NULL
);

-- Fermentation Schedule
CREATE TABLE schedule (
	id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name TEXT default NULL
);

CREATE TABLE schedule_step (
	schedule_id INTEGER NOT NULL,
	sortOrder INTEGER NOT NULL,
	stepTrigger ENUM('gravity', 'time', 'attenuation'),
	stepValue DECIMAL default NULL,
	notify INTEGER default 0,
	stepAction ENUM('none', 'temp'),
	stepActionValue DECIMAL default NULL,
	KEY(schedule_id, sortOrder)
);

-- Recipe
CREATE TABLE recipe (
	id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name TEXT default NULL,
	notes TEXT default NULL,
	yeast_id INTEGER default NULL
);

-- Yeast
CREATE TABLE yeast (
	id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name TEXT default NULL,
	description TEXT default NULL
);

-- Main data store
CREATE TABLE data (
	ts INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	color INTEGER default NULL,
	beer_temp INTEGER default NULL,
	sg INTEGER default NULL
);

-- Data Warehouse
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

-- Note and Notification
CREATE TABLE note (
	id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	parent_id INTEGER default NULL,
	parent_class TEXT default NULL,
	ts_created INTEGER default NULL,
	ts_event INTEGER default NULL,
	content TEXT default NULL,
	acknowledged INTEGER default 0,
	notify INTEGER default 0
);


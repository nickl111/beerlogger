CREATE TABLE session (
	id INTEGER PRIMARY KEY,
	recipe_id INTEGER default NULL,
	name TEXT default NULL,
	ts_start INTEGER default NULL,
	ts_end INTEGER default NULL,
	notes TEXT default NULL,
	g_orig DECIMAL(4,3) default NULL,
	mash_ph DECIMAL(3,2) default NULL,
	g_post_mash DECIMAL(4,3) default NULL,
	mash_eff INTEGER default NULL,
	vol_pre_boil DECIMAL(5,2) default NULL,
	g_pre_boil DECIMAL(4,3) default NULL,
	vol_ferment DECIMAL(5,2) default NULL,
	g_final DECIMAL(4,3) default NULL,
	vol_bottle DECIMAL(5,2) default NULL,
	abv DECIMAL(4,2) default NULL,
	attenuation DECIMAL(4,1) default NULL,
	carb_level DECIMAL(2,1) default NULL,
	target_temp DECIMAL(3,1) default NULL
);

CREATE TABLE recipe (
	id INTEGER PRIMARY KEY,
	name TEXT default NULL,
	notes TEXT default NULL
);

CREATE TABLE sample (
	id INTEGER PRIMARY KEY,
	session_id INTEGER default NULL,
	ts INTEGER default NULL,
	sg DECIMAL(8,4) default NULL,
	note TEXT default NULL
);

CREATE TABLE data (
	ts INTEGER PRIMARY KEY,
	bloops INTEGER default 0,
	beer_temp DECIMAL(5,3) default NULL,
	amb_temp DECIMAL(5,3) default NULL
);
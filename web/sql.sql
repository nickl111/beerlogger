CREATE TABLE session (
	id INTEGER PRIMARY KEY,
	recipe_id INTEGER,
	name TEXT,
	start INTEGER,
	end INTEGER,
	notes TEXT
);

CREATE TABLE recipe (
	id INTEGER PRIMARY KEY,
	name TEXT,
	notes TEXT
)

CREATE TABLE sample (
	id INTEGER PRIMARY KEY,
	session_id INTEGER,
	ts INTEGER,
	sg DECIMAL(8,4),
	note TEXT
);

CREATE TABLE data (
	ts INTEGER PRIMARY KEY,
	bloops INTEGER,
	beer_temp DECIMAL(5,3),
	amb_temp DECIMAL(5,3)
);
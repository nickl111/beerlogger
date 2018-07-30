
CREATE TABLE session (
	id INTEGER PRIMARY KEY,
	name VARCHAR(255),
	start INTEGER,
	end INTEGER,
	notes TEXT
);

CREATE TABLE sample (
	id INTEGER PRIMARY KEY,
	sg DECIMAL(8,4),
	note TEXT
);

CREATE TABLE data (
	ts INTEGER PRIMARY KEY,
	bloops INTEGER,
	beer_temp DECIMAL(5,3),
	amb_temp DECIMAL(5,3)
);
CREATE TABLE watches (
  id         INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  name       TEXT NOT NULL UNIQUE,
  brand      TEXT NOT NULL,
  type       TEXT NOT NULL,
  price      Integer NOT NULL,
  file_name    TEXT NOT NULL,
  file_ext    TEXT NOT NULL
);

INSERT INTO watches (id, name, brand, type, price, file_name, file_ext) VALUES (1, 'Oyster Perpetual 36',  'Rolex',    'Oyster',   5013, '1.jpg', 'jpg');
INSERT INTO watches (id, name, brand, type, price, file_name, file_ext) VALUES (2, 'Oyster Perpetual 31',  'Rolex',    'Oyster',   3551, '2.jpg', 'jpg');
INSERT INTO watches (id, name, brand, type, price, file_name, file_ext) VALUES (3, 'Automatic Top Gun',  'IWC',    'Pilot',   5600, '3.jpg', 'jpg');
INSERT INTO watches (id, name, brand, type, price, file_name, file_ext) VALUES (4, 'Automatic Spitfire',  'IWC',    'Pilot',   4450, '4.jpg', 'jpg');
INSERT INTO watches (id, name, brand, type, price, file_name, file_ext) VALUES (5, 'Success Road',  'Swatch',    'Skin Irony',   185, '5.jpg', 'jpg');
INSERT INTO watches (id, name, brand, type, price, file_name, file_ext) VALUES (6, 'Timetric',  'Swatch',    'Skin Irony',   185, '6.jpg', 'jpg');
INSERT INTO watches (id, name, brand, type, price, file_name, file_ext) VALUES (7, 'Avigation Bigeye',  'Logines',    'Heritage',  3225, '7.jpg', 'jpg');
INSERT INTO watches (id, name, brand, type, price, file_name, file_ext) VALUES (8, 'Flagship Heritage',  'Logines',    'Heritage',   1675, '8.jpg', 'jpg');
INSERT INTO watches (id, name, brand, type, price, file_name, file_ext) VALUES (9, 'Ronde Solo De Cartier',  'Cartier', 'Ronde Solo',   2830, '9.jpg', 'jpg');
INSERT INTO watches (id, name, brand, type, price, file_name, file_ext) VALUES (10, '1980s Swiss',  'Piaget','Automatic Dress Watch',   4050, '10.jpg', 'jpg');


CREATE TABLE tags (
  id         INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  tags       TEXT NOT NULL UNIQUE
);

INSERT INTO tags (id, tags) VALUES (1, "High End");
INSERT INTO tags (id, tags) VALUES (2, "Mid Range");
INSERT INTO tags (id, tags) VALUES (3, "Affordable");
INSERT INTO tags (id, tags) VALUES (4, "Daily");


CREATE TABLE watch_tags (
  id         INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  watch_id   INTEGER NOT NULL,
  tags_id    INTEGER NOT NULL,

  FOREIGN KEY(watch_id) REFERENCES watches(id),
  FOREIGN KEY(tags_id) REFERENCES tags(id)
);

INSERT INTO watch_tags (id, watch_id, tags_id) VALUES (1, 1, 1);
INSERT INTO watch_tags (id, watch_id, tags_id) VALUES (2, 1, 4);
INSERT INTO watch_tags (id, watch_id, tags_id) VALUES (3, 2, 2);
INSERT INTO watch_tags (id, watch_id, tags_id) VALUES (4, 3, 2);
INSERT INTO watch_tags (id, watch_id, tags_id) VALUES (5, 4, 3);
INSERT INTO watch_tags (id, watch_id, tags_id) VALUES (6, 5, 3);
INSERT INTO watch_tags (id, watch_id, tags_id) VALUES (7, 6, 2);
INSERT INTO watch_tags (id, watch_id, tags_id) VALUES (8, 7, 2);
INSERT INTO watch_tags (id, watch_id, tags_id) VALUES (9, 8, 2);
INSERT INTO watch_tags (id, watch_id, tags_id) VALUES (10, 9, 2);
INSERT INTO watch_tags (id, watch_id, tags_id) VALUES (11, 10, 2);



--- Users ---

CREATE TABLE users (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	username TEXT NOT NULL UNIQUE,
	password TEXT NOT NULL
);

INSERT INTO users (id, username, password) VALUES (1, 'admin', '$2y$10$QtCybkpkzh7x5VN11APHned4J8fu78.eFXlyAMmahuAaNcbwZ7FH.'); -- password: monkey
INSERT INTO users (id,username, password) VALUES (2, 'user', '$2y$10$QtCybkpkzh7x5VN11APHned4J8fu78.eFXlyAMmahuAaNcbwZ7FH.'); -- password: monkey


--- Sessions ---

CREATE TABLE sessions (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	user_id INTEGER NOT NULL,
	session TEXT NOT NULL UNIQUE,
  last_login   TEXT NOT NULL,

  FOREIGN KEY(user_id) REFERENCES users(id)
);


--- Groups ----

CREATE TABLE groups (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	name TEXT NOT NULL UNIQUE
);

INSERT INTO groups (id, name) VALUES (1, 'admin');


--- Group Membership

CREATE TABLE memberships (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  group_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,

  FOREIGN KEY(group_id) REFERENCES groups(id),
  FOREIGN KEY(user_id) REFERENCES users(id)
);

INSERT INTO memberships (group_id, user_id) VALUES (1, 1);

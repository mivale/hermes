BEGIN TRANSACTION;
CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT, key TEXT, naam TEXT, created DATETIME, updated DATETIME);
INSERT INTO "user" VALUES(1,'1b75df50-17b4-11df-b5c1-61856f3a2e36','DMM Websolutions BV','2010-02-18 15:01:08','2010-02-18 15:01:08');
CREATE TABLE tag (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, tag_id TEXT, ident TEXT);
INSERT INTO "tag" VALUES(1,1,'1b763f80-17b4-11df-9313-c1e90bfb96df','matchmail');
CREATE TABLE run (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, run_id TEXT, created DATETIME, updated DATETIME);
CREATE TABLE run_tag (run_id INTEGER, tag_id INTEGER);
CREATE TABLE mail (id INTEGER PRIMARY KEY AUTOINCREMENT, run_id TEXT, uniq TEXT, headers TEXT, body TEXT, created DATETIME, updated DATETIME, sent DATETIME);
DELETE FROM sqlite_sequence;
INSERT INTO "sqlite_sequence" VALUES('user',1);
INSERT INTO "sqlite_sequence" VALUES('tag',1);
INSERT INTO "sqlite_sequence" VALUES('mail',1);
CREATE TRIGGER insert_user_created AFTER INSERT ON user
BEGIN
	UPDATE user SET created = DATETIME('NOW')  WHERE rowid = new.rowid;
END;
CREATE TRIGGER update_user_created AFTER UPDATE ON user
BEGIN
	UPDATE user SET updated = DATETIME('NOW')  WHERE rowid = new.rowid;
END;
CREATE TRIGGER insert_run_created AFTER INSERT ON run
BEGIN
	UPDATE run SET created = DATETIME('NOW')  WHERE rowid = new.rowid;
END;
CREATE TRIGGER update_run_created AFTER UPDATE ON run
BEGIN
	UPDATE run SET updated = DATETIME('NOW')  WHERE rowid = new.rowid;
END;
CREATE TRIGGER insert_mail_created AFTER INSERT ON mail
BEGIN
	UPDATE mail SET created = DATETIME('NOW')  WHERE rowid = new.rowid;
END;
CREATE TRIGGER update_mail_created AFTER UPDATE ON mail
BEGIN
	UPDATE mail SET updated = DATETIME('NOW')  WHERE rowid = new.rowid;
END;
COMMIT;

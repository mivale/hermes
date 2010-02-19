BEGIN TRANSACTION;
CREATE TABLE klant (id INTEGER PRIMARY KEY AUTOINCREMENT, key TEXT, naam TEXT, created DATETIME, updated DATETIME);
INSERT INTO "klant" VALUES(1,'1b75df50-17b4-11df-b5c1-61856f3a2e36','DMM Websolutions BV','2010-02-18 15:01:08');
CREATE TABLE tag (id INTEGER PRIMARY KEY AUTOINCREMENT, klant_id INTEGER, tag_id TEXT, ident TEXT);
INSERT INTO "tag" VALUES(1,1,'1b763f80-17b4-11df-9313-c1e90bfb96df','matchmail');
CREATE TABLE run (id INTEGER PRIMARY KEY AUTOINCREMENT, klant_id INTEGER, runid TEXT, created DATETIME, updated DATETIME);
CREATE TABLE tag_run (run_id INTEGER, tag_id INTEGER);
DELETE FROM sqlite_sequence;
INSERT INTO "sqlite_sequence" VALUES('klant',1);
INSERT INTO "sqlite_sequence" VALUES('tag',1);
CREATE TRIGGER insert_klant_created AFTER INSERT ON klant
BEGIN
	UPDATE klant SET created = DATETIME('NOW')  WHERE rowid = new.rowid;
END;
CREATE TRIGGER update_klant_created AFTER UPDATE ON klant
BEGIN
	UPDATE klant SET updated = DATETIME('NOW')  WHERE rowid = new.rowid;
END;
CREATE TRIGGER insert_run_created AFTER INSERT ON run
BEGIN
	UPDATE run SET created = DATETIME('NOW')  WHERE rowid = new.rowid;
END;
CREATE TRIGGER update_run_created AFTER UPDATE ON run
BEGIN
	UPDATE run SET updated = DATETIME('NOW')  WHERE rowid = new.rowid;
END;
COMMIT;

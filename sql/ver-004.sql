ALTER TABLE opinions DROP FOREIGN KEY FK_BEAF78D0F30FAE2D;
DROP INDEX IDX_BEAF78D0F30FAE2D ON opinions;
ALTER TABLE opinions CHANGE user_opinion user_id INT DEFAULT NULL;
ALTER TABLE opinions ADD CONSTRAINT FK_BEAF78D0A76ED395 FOREIGN KEY (user_id) REFERENCES users (id);
CREATE INDEX IDX_BEAF78D0A76ED395 ON opinions (user_id);
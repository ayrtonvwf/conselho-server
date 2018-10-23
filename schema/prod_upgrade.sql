
-- -----------------------------------------------------
-- Table `observation_topic`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `observation_topic` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `name` VARCHAR(50) NOT NULL,
  `school_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `observation_topic-school_id`
  FOREIGN KEY (`school_id`)
  REFERENCES `school` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;

CREATE INDEX `observation_topic-school_id_idx` ON `observation_topic` (`school_id` ASC);

CREATE UNIQUE INDEX `observation_topic_unique` ON `observation_topic` (`school_id` ASC, `name` ASC);


-- -----------------------------------------------------
-- Table `council_observation_topic`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `council_observation_topic` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `council_id` INT UNSIGNED NOT NULL,
  `observation_topic_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `council_observation_topic-council_id`
  FOREIGN KEY (`council_id`)
  REFERENCES `council` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `council_observation_topic-observation_topic_id`
  FOREIGN KEY (`observation_topic_id`)
  REFERENCES `observation_topic` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;

CREATE INDEX `council_observation_topic-council_id_idx` ON `council_observation_topic` (`council_id` ASC);

CREATE INDEX `council_observation_topic-observation_topic_id_idx` ON `council_observation_topic` (`observation_topic_id` ASC);

CREATE UNIQUE INDEX `council_observation_topic_unique` ON `council_observation_topic` (`council_id` ASC, `observation_topic_id` ASC);

-- CREATE DEFAULT OBSERVATION_TOPIC
INSERT INTO `observation_topic` (`id`, `active`, `name`, `school_id`, `created_at`, `updated_at`)
  VALUES ('1', '1', 'Observações gerais', '1', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

-- CREATE COUNCIL_OBSERVATION_TOPICS
INSERT INTO `council_observation_topic` (`id`, `council_id`, `observation_topic_id`, `created_at`, `updated_at`)
  VALUES
         (NULL, '1', '1', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
         (NULL, '6', '1', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

-- CREATE NEW COLUMN ON STUDENT_OBSERVATION TABLE
ALTER TABLE `student_observation`
  ADD `observation_topic_id` INT UNSIGNED NOT NULL AFTER `user_id`;

-- UPDATE EXISTENT STUDENT_OBSERVATIONS
UPDATE `student_observation` SET `observation_topic_id` = 1;

-- CREATE FOREIGN KEY
ALTER TABLE `student_observation`
  ADD FOREIGN KEY (`observation_topic_id`) REFERENCES `observation_topic`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- UPDATE UNIQUE INDEX
ALTER TABLE `student_observation`
  DROP INDEX `student_observation_unique`,
  ADD UNIQUE `student_observation_unique` (`student_id`, `user_id`, `subject_id`, `council_id`, `observation_topic_id`) USING BTREE;

-- CREATE NEW OBSERVATION_TOPICS
INSERT INTO `observation_topic` (`id`, `active`, `name`, `school_id`, `created_at`, `updated_at`)
	VALUES
		(NULL, '1', 'Conteúdos não assimilados', '1', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
		(NULL, '1', 'Ações efetivadas', '1', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

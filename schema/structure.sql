-- MySQL Script generated by MySQL Workbench
-- qui 21 fev 2019 14:54:43 -03
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema conselho
-- -----------------------------------------------------
SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `email` VARCHAR(100) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `password` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE UNIQUE INDEX `email_UNIQUE` ON `user` (`email` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `school`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `school` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE UNIQUE INDEX `name_UNIQUE` ON `school` (`name` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `subject`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `subject` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `name` VARCHAR(50) NOT NULL,
  `school_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `subject-school_id`
    FOREIGN KEY (`school_id`)
    REFERENCES `school` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `school_id_idx` ON `subject` (`school_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `subject_unique` ON `subject` (`school_id` ASC, `name` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `grade`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `grade` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `level` INT UNSIGNED NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `school_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `grade-school_id`
    FOREIGN KEY (`school_id`)
    REFERENCES `school` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `school_id_idx` ON `grade` (`school_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `grade_unique` ON `grade` (`school_id` ASC, `name` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `grade_subject`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `grade_subject` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `grade_id` INT UNSIGNED NOT NULL,
  `subject_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `grade_subject-grade_id`
    FOREIGN KEY (`grade_id`)
    REFERENCES `grade` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `grade_subject-subject_id`
    FOREIGN KEY (`subject_id`)
    REFERENCES `subject` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE UNIQUE INDEX `id_UNIQUE` ON `grade_subject` (`id` ASC);

SHOW WARNINGS;
CREATE INDEX `grade_id_idx` ON `grade_subject` (`grade_id` ASC);

SHOW WARNINGS;
CREATE INDEX `subject_id_idx` ON `grade_subject` (`subject_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `grade_subject_unique` ON `grade_subject` (`grade_id` ASC, `subject_id` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `student`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `student` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `school_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `student-school_id`
    FOREIGN KEY (`school_id`)
    REFERENCES `school` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `school_id_idx` ON `student` (`school_id` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `topic_option`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `topic_option` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `name` VARCHAR(50) NOT NULL,
  `value` INT UNSIGNED NOT NULL,
  `topic_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `topic_option-topic_id`
    FOREIGN KEY (`topic_id`)
    REFERENCES `topic` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `topic_option-topic_id_idx` ON `topic_option` (`topic_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `topic_option_unique_name` ON `topic_option` (`topic_id` ASC, `name` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `topic_option_unique_value` ON `topic_option` (`topic_id` ASC, `value` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `topic`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `topic` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `name` VARCHAR(50) NOT NULL,
  `school_id` INT UNSIGNED NOT NULL,
  `topic_option_id` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `topic-school_id`
    FOREIGN KEY (`school_id`)
    REFERENCES `school` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `topic-topic_option_id`
    FOREIGN KEY (`topic_option_id`)
    REFERENCES `topic_option` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `school_id_idx` ON `topic` (`school_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `topic_unique` ON `topic` (`name` ASC, `school_id` ASC);

SHOW WARNINGS;
CREATE INDEX `topic-topic_option_id_idx` ON `topic` (`topic_option_id` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `council`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `council` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `name` VARCHAR(50) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `school_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `council-school_id`
    FOREIGN KEY (`school_id`)
    REFERENCES `school` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `school_id_idx` ON `council` (`school_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `council_unique` ON `council` (`school_id` ASC, `name` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `evaluation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `evaluation` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `council_id` INT UNSIGNED NOT NULL,
  `grade_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `subject_id` INT UNSIGNED NOT NULL,
  `topic_option_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `evaluation-council_id`
    FOREIGN KEY (`council_id`)
    REFERENCES `council` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `evaluation-grade_id`
    FOREIGN KEY (`grade_id`)
    REFERENCES `grade` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `evaluation-student_id`
    FOREIGN KEY (`student_id`)
    REFERENCES `student` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `evaluation-subject_id`
    FOREIGN KEY (`subject_id`)
    REFERENCES `subject` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `evaluation-topic_option_id`
    FOREIGN KEY (`topic_option_id`)
    REFERENCES `topic_option` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `evaluation-user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `council_id_idx` ON `evaluation` (`council_id` ASC);

SHOW WARNINGS;
CREATE INDEX `grade_id_idx` ON `evaluation` (`grade_id` ASC);

SHOW WARNINGS;
CREATE INDEX `student_id_idx` ON `evaluation` (`student_id` ASC);

SHOW WARNINGS;
CREATE INDEX `subject_id_idx` ON `evaluation` (`subject_id` ASC);

SHOW WARNINGS;
CREATE INDEX `user_id_idx` ON `evaluation` (`user_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `evaluation_unique` ON `evaluation` (`student_id` ASC, `user_id` ASC, `topic_option_id` ASC, `subject_id` ASC, `council_id` ASC);

SHOW WARNINGS;
CREATE INDEX `evaluation-topic_option_id_idx` ON `evaluation` (`topic_option_id` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `grade_observation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `grade_observation` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` VARCHAR(3000) NOT NULL,
  `council_id` INT UNSIGNED NOT NULL,
  `grade_id` INT UNSIGNED NOT NULL,
  `subject_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `grade_observation-council_id`
    FOREIGN KEY (`council_id`)
    REFERENCES `council` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `grade_observation-grade_id`
    FOREIGN KEY (`grade_id`)
    REFERENCES `grade` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `grade_observation-subject_id`
    FOREIGN KEY (`subject_id`)
    REFERENCES `subject` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `grade_observation-user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `council_id_idx` ON `grade_observation` (`council_id` ASC);

SHOW WARNINGS;
CREATE INDEX `grade_id_idx` ON `grade_observation` (`grade_id` ASC);

SHOW WARNINGS;
CREATE INDEX `subject_id_idx` ON `grade_observation` (`subject_id` ASC);

SHOW WARNINGS;
CREATE INDEX `user_id_idx` ON `grade_observation` (`user_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `grade_observation_unique` ON `grade_observation` (`grade_id` ASC, `council_id` ASC, `user_id` ASC, `subject_id` ASC);

SHOW WARNINGS;

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

SHOW WARNINGS;
CREATE INDEX `observation_topic-school_id_idx` ON `observation_topic` (`school_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `observation_topic_unique` ON `observation_topic` (`school_id` ASC, `name` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `student_observation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `student_observation` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` VARCHAR(3000) NOT NULL,
  `council_id` INT UNSIGNED NOT NULL,
  `grade_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `subject_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `observation_topic_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `student_observation-council_id`
    FOREIGN KEY (`council_id`)
    REFERENCES `council` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `student_observation-grade_id`
    FOREIGN KEY (`grade_id`)
    REFERENCES `grade` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `student_observation-student_id`
    FOREIGN KEY (`student_id`)
    REFERENCES `student` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `student_observation-subject_id`
    FOREIGN KEY (`subject_id`)
    REFERENCES `subject` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `student_observation-user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `student_observation-observation_topic_ic`
    FOREIGN KEY (`observation_topic_id`)
    REFERENCES `observation_topic` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `council_id_idx` ON `student_observation` (`council_id` ASC);

SHOW WARNINGS;
CREATE INDEX `grade_id_idx` ON `student_observation` (`grade_id` ASC);

SHOW WARNINGS;
CREATE INDEX `student_id_idx` ON `student_observation` (`student_id` ASC);

SHOW WARNINGS;
CREATE INDEX `subject_id_idx` ON `student_observation` (`subject_id` ASC);

SHOW WARNINGS;
CREATE INDEX `user_id_idx` ON `student_observation` (`user_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `student_observation_unique` ON `student_observation` (`student_id` ASC, `user_id` ASC, `subject_id` ASC, `council_id` ASC, `observation_topic_id` ASC);

SHOW WARNINGS;
CREATE INDEX `student_observation-observation_topic_ic_idx` ON `student_observation` (`observation_topic_id` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `role_type`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `role_type` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `school_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `role_type-school_id`
    FOREIGN KEY (`school_id`)
    REFERENCES `school` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `role_type-school_id_idx` ON `role_type` (`school_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `role_unique` ON `role_type` (`school_id` ASC, `name` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `role`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `role` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `approved` TINYINT(1) NOT NULL DEFAULT 0,
  `role_type_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `role-role_type_id`
    FOREIGN KEY (`role_type_id`)
    REFERENCES `role_type` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `role-user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `role_type_id_idx` ON `role` (`role_type_id` ASC);

SHOW WARNINGS;
CREATE INDEX `user_id_idx` ON `role` (`user_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `role_unique` ON `role` (`user_id` ASC, `role_type_id` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `student_grade`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `student_grade` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `number` INT UNSIGNED NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `grade_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `disabled_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `student_grade-grade_id`
    FOREIGN KEY (`grade_id`)
    REFERENCES `grade` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `student_grade-student_id`
    FOREIGN KEY (`student_id`)
    REFERENCES `student` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `grade_id_idx` ON `student_grade` (`grade_id` ASC);

SHOW WARNINGS;
CREATE INDEX `student_id_idx` ON `student_grade` (`student_id` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `medical_report`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `medical_report` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` VARCHAR(50) NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `medical_report-student_id`
    FOREIGN KEY (`student_id`)
    REFERENCES `student` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `student_id_idx` ON `medical_report` (`student_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `medical_report_unique` ON `medical_report` (`student_id` ASC, `description` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `medical_report_subject`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `medical_report_subject` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `medical_report_id` INT UNSIGNED NOT NULL,
  `subject_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `medical_report_subject-medical_report_id`
    FOREIGN KEY (`medical_report_id`)
    REFERENCES `medical_report` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `medical_report_subject-subject_id`
    FOREIGN KEY (`subject_id`)
    REFERENCES `subject` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `medical_report_id_idx` ON `medical_report_subject` (`medical_report_id` ASC);

SHOW WARNINGS;
CREATE INDEX `subject_id_idx` ON `medical_report_subject` (`subject_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `medical_report_subject_unique` ON `medical_report_subject` (`medical_report_id` ASC, `subject_id` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `user_token`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_token` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `value` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `user_token-user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `user_id_idx` ON `user_token` (`user_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `value_UNIQUE` ON `user_token` (`value` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `council_topic`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `council_topic` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `council_id` INT UNSIGNED NOT NULL,
  `topic_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `council_topic-topic_id`
    FOREIGN KEY (`topic_id`)
    REFERENCES `topic` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `council_topic-council_id`
    FOREIGN KEY (`council_id`)
    REFERENCES `council` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `council_topic-topic_id_idx` ON `council_topic` (`topic_id` ASC);

SHOW WARNINGS;
CREATE INDEX `council_topic-council_id_idx` ON `council_topic` (`council_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `council_topic_unique` ON `council_topic` (`council_id` ASC, `topic_id` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `permission`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `permission` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `reference` VARCHAR(50) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE UNIQUE INDEX `name_UNIQUE` ON `permission` (`name` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `reference_UNIQUE` ON `permission` (`reference` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `role_type_permission`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `role_type_permission` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permission_id` INT UNSIGNED NOT NULL,
  `role_type_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `role_type_permission-role_type_id`
    FOREIGN KEY (`role_type_id`)
    REFERENCES `role_type` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `role_type_permission-permission_id`
    FOREIGN KEY (`permission_id`)
    REFERENCES `permission` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE UNIQUE INDEX `role_type_permission_unique` ON `role_type_permission` (`role_type_id` ASC, `permission_id` ASC);

SHOW WARNINGS;
CREATE INDEX `role_type_permission-role_type_id_idx` ON `role_type_permission` (`role_type_id` ASC);

SHOW WARNINGS;
CREATE INDEX `role_type_permission-permission_id_idx` ON `role_type_permission` (`permission_id` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `teacher`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `teacher` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `start_date` DATE NOT NULL,
  `end_date` DATE NULL,
  `grade_id` INT UNSIGNED NOT NULL,
  `subject_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `teacher-grade_id`
    FOREIGN KEY (`grade_id`)
    REFERENCES `grade` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `teacher-subject_id`
    FOREIGN KEY (`subject_id`)
    REFERENCES `subject` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `teacher-user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `teacher-grade_id_idx` ON `teacher` (`grade_id` ASC);

SHOW WARNINGS;
CREATE INDEX `teacher-subject_id_idx` ON `teacher` (`subject_id` ASC);

SHOW WARNINGS;
CREATE INDEX `teacher-user_id_idx` ON `teacher` (`user_id` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `teacher_request`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `teacher_request` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `grade_id` INT UNSIGNED NOT NULL,
  `subject_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `teacher_request-grade_id`
    FOREIGN KEY (`grade_id`)
    REFERENCES `grade` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `teacher_request-subject_id`
    FOREIGN KEY (`subject_id`)
    REFERENCES `subject` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `teacher_request-user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `teacher_request-grade_id_idx` ON `teacher_request` (`grade_id` ASC);

SHOW WARNINGS;
CREATE INDEX `teacher_request-subject_id_idx` ON `teacher_request` (`subject_id` ASC);

SHOW WARNINGS;
CREATE INDEX `teacher_request-user_id_idx` ON `teacher_request` (`user_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `teacher_request_unique` ON `teacher_request` (`user_id` ASC, `subject_id` ASC, `grade_id` ASC);

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `council_grade`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `council_grade` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `council_id` INT UNSIGNED NOT NULL,
  `grade_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `council_grade-council_id`
    FOREIGN KEY (`council_id`)
    REFERENCES `council` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `council_grade-grade_id`
    FOREIGN KEY (`grade_id`)
    REFERENCES `grade` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
CREATE INDEX `council_grade-council_id_idx` ON `council_grade` (`council_id` ASC);

SHOW WARNINGS;
CREATE INDEX `council_grade-grade_id_idx` ON `council_grade` (`grade_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `council_grade_unique` ON `council_grade` (`grade_id` ASC, `council_id` ASC);

SHOW WARNINGS;

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

SHOW WARNINGS;
CREATE INDEX `council_observation_topic-council_id_idx` ON `council_observation_topic` (`council_id` ASC);

SHOW WARNINGS;
CREATE INDEX `council_observation_topic-observation_topic_id_idx` ON `council_observation_topic` (`observation_topic_id` ASC);

SHOW WARNINGS;
CREATE UNIQUE INDEX `council_observation_topic_unique` ON `council_observation_topic` (`council_id` ASC, `observation_topic_id` ASC);

SHOW WARNINGS;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `permission`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `permission` (`id`, `name`, `reference`, `created_at`, `updated_at`) VALUES (1, 'Gerenciar conselho', 'council', DEFAULT, DEFAULT);
INSERT INTO `permission` (`id`, `name`, `reference`, `created_at`, `updated_at`) VALUES (2, 'Avaliar alunos', 'evaluate', DEFAULT, DEFAULT);
INSERT INTO `permission` (`id`, `name`, `reference`, `created_at`, `updated_at`) VALUES (3, 'Gerenciar turmas', 'grade', DEFAULT, DEFAULT);
INSERT INTO `permission` (`id`, `name`, `reference`, `created_at`, `updated_at`) VALUES (4, 'Aceitar/negar permissões', 'role', DEFAULT, DEFAULT);
INSERT INTO `permission` (`id`, `name`, `reference`, `created_at`, `updated_at`) VALUES (5, 'Gerenciar tipos de usuários', 'role_type', DEFAULT, DEFAULT);
INSERT INTO `permission` (`id`, `name`, `reference`, `created_at`, `updated_at`) VALUES (6, 'Gerenciar alunos', 'student', DEFAULT, DEFAULT);
INSERT INTO `permission` (`id`, `name`, `reference`, `created_at`, `updated_at`) VALUES (7, 'Gerenciar matérias', 'subject', DEFAULT, DEFAULT);
INSERT INTO `permission` (`id`, `name`, `reference`, `created_at`, `updated_at`) VALUES (8, 'Gerenciar professores', 'teacher', DEFAULT, DEFAULT);
INSERT INTO `permission` (`id`, `name`, `reference`, `created_at`, `updated_at`) VALUES (9, 'Tópicos', 'topic', DEFAULT, DEFAULT);
INSERT INTO `permission` (`id`, `name`, `reference`, `created_at`, `updated_at`) VALUES (10, 'Ver relatórios de conselhos', 'council_report', DEFAULT, DEFAULT);

COMMIT;


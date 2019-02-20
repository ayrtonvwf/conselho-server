ALTER
  TABLE `student_observation`
  CHANGE `description` `description` VARCHAR(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

ALTER
  TABLE `grade_observation`
  CHANGE `description` `description` VARCHAR(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

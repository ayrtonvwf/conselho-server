ALTER
  TABLE `student_grade`
  CHANGE `end_date` `end_date` DATE NULL;

UPDATE `student_grade`
  SET `end_date` = NULL
  WHERE `student_grade`.`end_date` = '2018-12-31';
ALTER
  TABLE `student_grade`
  ADD `disabled_at` DATETIME NULL;

UPDATE `student_grade`
  SET `disabled_at`=`end_date`
  WHERE
    `end_date` != '2018-12-31' AND
    `end_date` != '2018-12-31';
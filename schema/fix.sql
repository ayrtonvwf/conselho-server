-- SELECT old duplicated evaluations 186 + 4
SELECT
    MIN(evaluation.id)
FROM evaluation
INNER JOIN topic_option ON topic_option_id = topic_option.id
GROUP BY council_id, grade_id, student_id, subject_id, topic_id, user_id
HAVING COUNT(*) > 1;

-- DELETE old duplicated evaluation (may need to run multiple times)
DELETE FROM evaluation
WHERE id IN (
    SELECT id FROM (
        SELECT
            MIN(evaluation_search.id) AS id
        FROM evaluation AS evaluation_search
        INNER JOIN topic_option ON topic_option_id = topic_option.id
        GROUP BY council_id, grade_id, student_id, subject_id, topic_id, user_id
        HAVING COUNT(*) > 1
    ) AS duplicated_evaluation
);

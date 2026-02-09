<?php
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib/gradelib.php');

/**
 * Ensure grade_grades rows exist for grade items that match the plugin regex.
 * Searches all courses where the user is enrolled and creates grade placeholders.
 * Returns array with result info for display feedback.
 */
function local_lancamento_notas_ensure_grade_rows($userid, $maxperrun = 50)
{
    global $DB;

    $result = ['created' => 0, 'skipped' => 0, 'error' => '', 'courses_checked' => 0, 'debug' => []];

    if (empty($userid) || (int)$userid <= 0) {
        $result['error'] = 'ID de usuário inválido';
        return $result;
    }

    try {
        $regex = '((AE|RE)[0-9](A|R)[0-9]$|RF3R1)';

        // Find all courses where user is enrolled.
        $sql_courses = "SELECT DISTINCT c.id AS courseid
            FROM {course} c
            JOIN {enrol} e ON e.courseid = c.id
            JOIN {user_enrolments} ue ON ue.enrolid = e.id
            WHERE ue.userid = :userid";

        $courses = $DB->get_records_sql($sql_courses, ['userid' => (int)$userid]);

        if (empty($courses)) {
            $result['error'] = 'Usuário não está matriculado em nenhum curso';
            return $result;
        }

        $processed = 0;

        foreach ($courses as $course_rec) {
            $courseid = (int)$course_rec->courseid;
            $result['courses_checked']++;

            // For this course, find all grade_items matching regex.
            // Do NOT filter by itemtype = 'category'; check all.
            $sql_items = "SELECT gi.id, gi.courseid, gi.idnumber, gi.itemtype
                FROM {grade_items} gi
                WHERE gi.courseid = :courseid
                  AND gi.idnumber ~ :regex
                LIMIT 100";

            $items = $DB->get_records_sql($sql_items, 
                ['courseid' => $courseid, 'regex' => $regex]);

            if (empty($items)) {
                continue;
            }

            foreach ($items as $item) {
                if ($processed >= (int)$maxperrun) {
                    break 2; // Break both loops
                }

                // Check if record already exists.
                $exists = $DB->record_exists('grade_grades', 
                    ['itemid' => $item->id, 'userid' => (int)$userid]);

                if ($exists) {
                    $result['skipped']++;
                    continue;
                }

                // Try to create placeholder grade record directly via INSERT or grade_item API.
                try {
                    // Fetch the grade_item object to use its update_final_grade method.
                    $gradeitem = \grade_item::fetch(['id' => $item->id]);
                    if (!$gradeitem) {
                        error_log('local_lancamento_notas: grade_item not found id=' . $item->id);
                        continue;
                    }

                    // Use update_final_grade with null to create placeholder.
                    $feedback = 'Registro criado em ' . userdate(time());
                    $gradeitem->update_final_grade(
                        (int)$userid,
                        null,
                        'local_lancamento_notas',
                        $feedback,
                        FORMAT_MOODLE,
                        null,
                        null,
                        true
                    );

                    // Verify the record was actually created.
                    $created = $DB->record_exists('grade_grades', 
                        ['itemid' => $item->id, 'userid' => (int)$userid]);

                    if ($created) {
                        $result['created']++;
                        $result['debug'][] = "OK: itemid=" . $item->id . " courseid=" . $courseid . " itemtype=" . $item->itemtype;
                        $processed++;
                    } else {
                        $result['debug'][] = "FAIL: update_final_grade did not create record for itemid=" . $item->id . " (itemtype=" . $item->itemtype . ")";
                        error_log('local_lancamento_notas: update_final_grade failed for itemid=' . $item->id);
                    }
                } catch (Exception $e) {
                    error_log('local_lancamento_notas ensure_grade_rows: ' . $e->getMessage());
                    $result['debug'][] = "ERROR: itemid=" . $item->id . " msg=" . $e->getMessage();
                }
            }
        }
    } catch (Exception $e) {
        $result['error'] = 'Erro ao criar registros de grades: ' . $e->getMessage();
        error_log('local_lancamento_notas ensure_grade_rows: ' . $e->getMessage());
    }

    return $result;
}

function local_lancamento_notas_atualizar($userid, $maxperrun = 50)
{
    global $DB;

    $result = ['updated' => 0, 'error' => ''];

    if (empty($userid) || (int)$userid <= 0) {
        $result['error'] = 'ID de usuário inválido';
        return $result;
    }

    try {
        $regex = '((AE|RE)[0-9](A|R)[0-9]$|RF3R1)';

        $sql = "SELECT gi.id, gi.courseid, gg.id as gradeid
            FROM {grade_grades} gg
            JOIN {grade_items} gi ON gi.id = gg.itemid
            WHERE gg.finalgrade IS NULL
              AND gg.userid = :userid
              AND gi.idnumber ~ :regex
              AND gi.itemtype = 'category'
            LIMIT 100";

        $params = ['userid' => (int)$userid, 'regex' => $regex];
        $gradeitems = $DB->get_records_sql($sql, $params);

        if (empty($gradeitems)) {
            $result['updated'] = 0;
            return $result;
        }

        $processed = 0;
        foreach ($gradeitems as $rec) {
            if ($processed >= (int)$maxperrun) {
                break;
            }

            try {
                $nota_aleatoria = round(mt_rand(0, 59) / 10, 1);
                $gradeitem = \grade_item::fetch(['id' => $rec->id]);
                if (!$gradeitem) {
                    continue;
                }

                $feedback = 'Nota: ' . $nota_aleatoria . ' | ' . userdate(time());
                $gradeitem->update_final_grade(
                    (int)$userid,
                    $nota_aleatoria,
                    'local_lancamento_notas',
                    $feedback,
                    FORMAT_MOODLE,
                    null,
                    null,
                    true
                );
                $result['updated']++;
                $processed++;
            } catch (Exception $e) {
                error_log('local_lancamento_notas atualizar: ' . $e->getMessage());
                continue;
            }
        }
    } catch (Exception $e) {
        $result['error'] = 'Erro ao lançar notas: ' . $e->getMessage();
        error_log('local_lancamento_notas atualizar: ' . $e->getMessage());
    }

    return $result;
}

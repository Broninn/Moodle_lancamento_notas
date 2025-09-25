<?php
defined('MOODLE_INTERNAL') || die();

function local_lancamento_notas_atualizar($userid)
{
    global $DB;

    $regex = '((AE|RE)[0-9](A|R)[0-9]$|RF3R1)';

    $sql = "SELECT gi.id AS grade_item_id
        FROM {grade_grades} gg
        JOIN {grade_items} gi ON gi.id = gg.itemid
        WHERE gg.finalgrade IS NULL
          AND gg.userid = :userid
          AND gi.idnumber ~ :regex
          AND gi.itemtype = 'category'";

    $params = ['userid' => $userid, 'regex' => $regex];
    $gradeitems = $DB->get_records_sql($sql, $params);

    foreach ($gradeitems as $gradeitemrecord) {
        $nota_aleatoria = round(mt_rand(0, 59) / 10, 1);

        // Busca o objeto grade_item
        $gradeitem = \grade_item::fetch(['id' => $gradeitemrecord->grade_item_id]);
        if (!$gradeitem) {
            continue;
        }

        // Chama update_final_grade para atualizar a nota
        $feedback = 'Nota lançada automaticamente em ' . userdate(time());
        $gradeitem->update_final_grade(
            $userid,
            $nota_aleatoria,
            'local_lancamento_notas',
            $feedback,
            FORMAT_MOODLE,
            null,
            null,
            true
        );
    }
}

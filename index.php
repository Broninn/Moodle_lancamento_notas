<?php
// Arquivo local/lancamento_notas/index.php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$context = context_system::instance();
require_login();
require_capability('moodle/site:config', $context); // Pode ajustar permissão conforme necessário

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/lancamento_notas/index.php'));
$PAGE->set_title('Lançamento de Notas Aleatórias');
$PAGE->set_heading('Lançamento de Notas Aleatórias');

echo $OUTPUT->header();

if (!empty(optional_param('userid', 0, PARAM_INT))) {
    $userid = optional_param('userid', 0, PARAM_INT);

    if ($userid > 0) {
        // Release PHP/Moodle session write lock so long-running grade operations
        // do not block navigation for other users.
        if (function_exists('session_write_close')) {
            session_write_close();
        }

        $msg = "<strong>Processo de lançamento de notas:</strong><br />\n";

        // Ensure placeholder grade rows exist before attempting to update final grades.
        if (function_exists('local_lancamento_notas_ensure_grade_rows')) {
            $result_ensure = local_lancamento_notas_ensure_grade_rows($userid, 50);
            $msg .= "Cursos verificados: " . (int)$result_ensure['courses_checked'] . "<br />\n";
            $msg .= "Registros criados: " . (int)$result_ensure['created'] . "<br />\n";
            $msg .= "Registros já existentes: " . (int)$result_ensure['skipped'] . "<br />\n";
            if ($result_ensure['error']) {
                $msg .= "Aviso ao criar registros: " . htmlspecialchars($result_ensure['error']) . "<br />\n";
            }
            // Show debug info if there are failures
            if (!empty($result_ensure['debug'])) {
                $msg .= "<br /><strong>Debug logs:</strong><br />\n";
                foreach ($result_ensure['debug'] as $line) {
                    $msg .= htmlspecialchars($line) . "<br />\n";
                }
            }
        }

        // Now launch grades on existing records.
        $result_update = local_lancamento_notas_atualizar($userid, 50);
        $msg .= "Notas lançadas: " . (int)$result_update['updated'] . "<br />\n";
        if ($result_update['error']) {
            $msg .= "Erro ao lançar notas: " . htmlspecialchars($result_update['error']) . "\n";
            echo $OUTPUT->notification($msg, 'notifyproblem');
        } else {
            echo $OUTPUT->notification($msg, 'notifysuccess');
        }
    } else {
        echo $OUTPUT->notification("ID de usuário inválido.", 'notifyproblem');
    }
}

?>

<form method="post" action="">
    <label for="userid">Informe o ID do usuário:</label>
    <input type="number" name="userid" id="userid" required min="1" />
    <input type="submit" value="Lançar Notas" />
</form>

<?php
echo $OUTPUT->footer();
?>

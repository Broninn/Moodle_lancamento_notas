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
        local_lancamento_notas_atualizar($userid);
        echo $OUTPUT->notification("Notas lançadas para o usuário ID: $userid", 'notifysuccess');
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

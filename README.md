## Moodle Local Plugin — Lançamento de Notas Aleatórias

Plugin local para automatizar lançamentos de notas em ambientes de teste/homologação.

Principais pontos
- Compatibilidade: Moodle 4.5+ (testado até 5.1)
- Uso: somente em ambientes de teste. Não usar em produção.

Como funciona (resumo)
- Ao submeter `local/lancamento_notas/index.php` com um `userid`, o plugin:
   1. Busca os cursos em que o usuário está matriculado.
   2. Para cada curso, localiza `grade_items` cujo `idnumber` casa com o regex `(AE|RE)[0-9](A|R)[0-9]$|RF3R1`.
   3. Cria registros-placeholder em `mdl_grade_grades` (com `finalgrade=NULL`) para os itens que ainda não têm registro.
   4. Atualiza (lança) notas aleatórias (0.0–5.9) apenas nos registros com `finalgrade IS NULL`.

Comportamento e segurança
- Processamento em blocos: por segurança o plugin processa um número limitado de itens por execução (padrão 50). Se houver muitos itens, execute o formulário múltiplas vezes.
- O plugin libera o lock de sessão (`session_write_close()`) antes do processamento para não bloquear navegação de outros usuários.
- O plugin verifica se já existe um registro em `mdl_grade_grades` antes de criar para evitar operações desnecessárias.

Consulta de verificação recomendada
Use esta query para checar registros do usuário em um curso específico:

```
SELECT gi.id, gi.courseid, gi.idnumber
FROM mdl_grade_grades gg
JOIN mdl_grade_items gi ON gi.id = gg.itemid
WHERE gg.userid = :userid
   AND gi.courseid = :courseid
   AND gi.idnumber ~ '((AE|RE)[0-9](A|R)[0-9]$|RF3R1)';
```

Instalação
1. Coloque a pasta `lancamento_notas` em `local/` do Moodle.
2. Acesse Administração → Notificações para concluir a instalação.

Testes rápidos
1. Adicione um usuário de teste a um curso que contenha `grade_items` com o `idnumber` desejado.
2. Acesse `local/lancamento_notas/index.php`, informe o `userid` e submeta.
3. A página exibirá quantos registros foram criados/pulados e quantas notas foram lançadas.
4. Verifique `mdl_grade_grades` com a query acima.

Arquivos principais
- `index.php`: UI e fluxo (chama `ensure` e `atualizar`).
- `lib.php`: `local_lancamento_notas_ensure_grade_rows()` e `local_lancamento_notas_atualizar()`.

Aviso legal
Este plugin altera dados de notas — use apenas em ambientes de homologação.

Autor
Bruno Henrique da Silva Mosko

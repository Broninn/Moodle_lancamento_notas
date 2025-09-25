# Moodle Local Plugin: Lançamento de Notas Aleatórias

Este projeto é um plugin local para Moodle, desenvolvido para facilitar o lançamento automático de notas em ambientes de testes. **Não deve ser utilizado em ambientes de produção.**

## Objetivo
Automatizar o lançamento de notas aleatórias para usuários, útil para cenários de teste e homologação.

## Requisitos
- Moodle 4.5 ou superior
- Permissão de administrador (`moodle/site:config`)

## Como funciona
1. Acesse a página do plugin (`local/lancamento_notas/index.php`).
2. Informe o ID do usuário desejado.
3. O plugin irá lançar notas aleatórias (de 0.0 a 5.9) para todos os itens de nota que:
   - Possuem `finalgrade` nulo
   - Têm `idnumber` compatível com o regex: `(AE|RE)[0-9](A|R)[0-9]$|RF3R1`
   - São do tipo `category`

## Instalação
1. Copie os arquivos para o diretório `local/lancamento_notas` do seu Moodle.
2. Acesse o ambiente Moodle para concluir a instalação do plugin.

## Aviso Importante
- **Uso exclusivo para ambientes de teste.**
- Não há garantias ou suporte para uso em produção.
- O plugin pode sobrescrever notas existentes em itens compatíveis.

## Arquivos principais
- `index.php`: Interface web para lançamento de notas.
- `lib.php`: Lógica principal de atualização de notas.
- `version.php`: Metadados do plugin.
- `settings.php`: Reservado para configurações futuras.

## Autor
Bruno Henrique da Silva Mosko

---
Plugin desenvolvido para fins de homologação e testes internos.

<?php
declare(strict_types=1);

/* ============================================================
 *  Ações (POST): criar, editar e excluir registros.
 *  Todas validam o token CSRF e usam prepared statements.
 * ============================================================ */

function fail(string $message, string $redirectTo): never
{
    with_old_input();
    flash($message, 'error');
    redirect($redirectTo);
}

/* ---------- Times ---------- */

function action_team_save(): void
{
    $id   = int_or_null(input('id'));
    $back = url('team_form', $id ? ['id' => $id] : []);

    $data = [
        'league_id'       => int_or_null(input('league_id')),
        'name'            => (string) input('name', ''),
        'short_name'      => mb_strtoupper((string) input('short_name', '')),
        'city'            => (string) input('city', '') ?: null,
        'stadium'         => (string) input('stadium', '') ?: null,
        'founded'         => int_or_null(input('founded')),
        'coach'           => (string) input('coach', '') ?: null,
        'color_primary'   => safe_color((string) input('color_primary')),
        'color_secondary' => safe_color((string) input('color_secondary'), '#ffffff'),
    ];

    if (mb_strlen($data['name']) < 2)                                   fail('Informe o nome do time.', $back);
    if (!preg_match('/^[A-Z0-9À-Ü]{2,4}$/u', $data['short_name']))      fail('A sigla deve ter de 2 a 4 letras.', $back);
    if ($data['founded'] && ($data['founded'] < 1850 || $data['founded'] > (int) date('Y'))) fail('Ano de fundação inválido.', $back);

    if ($id) {
        $data['id'] = $id;
        db()->prepare(
            'UPDATE teams SET league_id=:league_id, name=:name, short_name=:short_name, city=:city, stadium=:stadium,
             founded=:founded, coach=:coach, color_primary=:color_primary, color_secondary=:color_secondary WHERE id=:id'
        )->execute($data);
        flash('Time atualizado com sucesso!');
    } else {
        db()->prepare(
            'INSERT INTO teams (league_id, name, short_name, city, stadium, founded, coach, color_primary, color_secondary)
             VALUES (:league_id, :name, :short_name, :city, :stadium, :founded, :coach, :color_primary, :color_secondary)'
        )->execute($data);
        $id = (int) db()->lastInsertId();
        flash('Time criado com sucesso!');
    }
    redirect(url('team', ['id' => $id]));
}

function action_team_delete(): void
{
    db()->prepare('DELETE FROM teams WHERE id = ?')->execute([(int) input('id')]);
    flash('Time excluído (com seus jogadores e partidas).');
    redirect(url('teams'));
}

/* ---------- Jogadores ---------- */

function action_player_save(): void
{
    $id   = int_or_null(input('id'));
    $back = url('player_form', $id ? ['id' => $id] : []);

    $foot = (string) input('foot', '');
    $data = [
        'team_id'     => (int) input('team_id'),
        'name'        => (string) input('name', ''),
        'number'      => int_or_null(input('number')),
        'position'    => (string) input('position', ''),
        'nationality' => (string) input('nationality', '') ?: null,
        'birth_date'  => (string) input('birth_date', '') ?: null,
        'height'      => int_or_null(input('height')),
        'foot'        => in_array($foot, ['Direito', 'Esquerdo', 'Ambidestro'], true) ? $foot : null,
        'rating'      => input('rating') === '' || input('rating') === null ? null : round((float) input('rating'), 1),
    ];

    if (mb_strlen($data['name']) < 2)                         fail('Informe o nome do jogador.', $back);
    if (!team($data['team_id']))                              fail('Selecione um time válido.', $back);
    if (!isset(POSITIONS[$data['position']]))                 fail('Selecione uma posição.', $back);
    if ($data['number'] !== null && ($data['number'] < 1 || $data['number'] > 99)) fail('O número deve ser entre 1 e 99.', $back);
    if ($data['rating'] !== null && ($data['rating'] < 0 || $data['rating'] > 10)) fail('A nota deve ser entre 0 e 10.', $back);
    if ($data['birth_date'] && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['birth_date'])) fail('Data de nascimento inválida.', $back);

    if ($id) {
        $data['id'] = $id;
        db()->prepare(
            'UPDATE players SET team_id=:team_id, name=:name, number=:number, position=:position, nationality=:nationality,
             birth_date=:birth_date, height=:height, foot=:foot, rating=:rating WHERE id=:id'
        )->execute($data);
        flash('Jogador atualizado!');
    } else {
        db()->prepare(
            'INSERT INTO players (team_id, name, number, position, nationality, birth_date, height, foot, rating)
             VALUES (:team_id, :name, :number, :position, :nationality, :birth_date, :height, :foot, :rating)'
        )->execute($data);
        $id = (int) db()->lastInsertId();
        flash('Jogador cadastrado!');
    }
    redirect(url('player', ['id' => $id]));
}

function action_player_delete(): void
{
    $player = player((int) input('id'));
    db()->prepare('DELETE FROM players WHERE id = ?')->execute([(int) input('id')]);
    flash('Jogador excluído.');
    redirect($player ? url('team', ['id' => $player['team_id']]) : url('players'));
}

/* ---------- Partidas ---------- */

function action_match_save(): void
{
    $id   = int_or_null(input('id'));
    $back = url('match_form', $id ? ['id' => $id] : []);

    $status = (string) input('status', 'scheduled');
    $date   = str_replace('T', ' ', (string) input('match_date', ''));
    $data   = [
        'league_id'    => int_or_null(input('league_id')),
        'round'        => int_or_null(input('round')),
        'home_team_id' => (int) input('home_team_id'),
        'away_team_id' => (int) input('away_team_id'),
        'match_date'   => strlen($date) === 16 ? $date . ':00' : $date,
        'status'       => isset(STATUSES[$status]) ? $status : 'scheduled',
        'minute'       => int_or_null(input('minute')),
        'home_score'   => (int) input('home_score', 0),
        'away_score'   => (int) input('away_score', 0),
        'stadium'      => (string) input('stadium', '') ?: null,
    ];
    foreach (['home_possession', 'home_shots', 'away_shots', 'home_shots_on', 'away_shots_on',
              'home_corners', 'away_corners', 'home_fouls', 'away_fouls'] as $field) {
        $data[$field] = int_or_null(input($field));
    }

    if (!team($data['home_team_id']) || !team($data['away_team_id'])) fail('Selecione os dois times.', $back);
    if ($data['home_team_id'] === $data['away_team_id'])            fail('Um time não pode jogar contra ele mesmo.', $back);
    if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $data['match_date'])) fail('Informe data e horário.', $back);
    if ($data['home_score'] < 0 || $data['away_score'] < 0)         fail('Placar inválido.', $back);
    if ($data['home_possession'] !== null && ($data['home_possession'] < 0 || $data['home_possession'] > 100)) fail('Posse deve ser 0–100%.', $back);
    if ($data['status'] === 'scheduled') { $data['home_score'] = 0; $data['away_score'] = 0; $data['minute'] = null; }
    if (!$data['stadium']) $data['stadium'] = team($data['home_team_id'])['stadium'] ?? null;

    $cols = array_keys($data);
    if ($id) {
        $set = implode(', ', array_map(fn ($c) => "{$c} = :{$c}", $cols));
        db()->prepare("UPDATE matches SET {$set} WHERE id = :id")->execute($data + ['id' => $id]);
        flash('Partida atualizada!');
    } else {
        $sql = 'INSERT INTO matches (' . implode(', ', $cols) . ') VALUES (:' . implode(', :', $cols) . ')';
        db()->prepare($sql)->execute($data);
        $id = (int) db()->lastInsertId();
        flash('Partida criada!');
    }
    redirect(url('match', ['id' => $id]));
}

function action_match_delete(): void
{
    db()->prepare('DELETE FROM matches WHERE id = ?')->execute([(int) input('id')]);
    flash('Partida excluída.');
    redirect(url('matches'));
}

/* ---------- Eventos da partida (gols e cartões) ---------- */

/** Ajusta o placar quando um gol é adicionado (+1) ou removido (-1). */
function adjust_score(array $match, array $event, int $delta): void
{
    if (!in_array($event['type'], GOAL_TYPES, true)) return;

    $scorerIsHome = (int) $event['team_id'] === (int) $match['home_team_id'];
    // gol contra: o ponto vai para o adversário
    $homeGets = $event['type'] === 'gol_contra' ? !$scorerIsHome : $scorerIsHome;
    $col      = $homeGets ? 'home_score' : 'away_score';

    db()->prepare("UPDATE matches SET {$col} = MAX(0, {$col} + ?) WHERE id = ?")->execute([$delta, $match['id']]);
}

function action_event_save(): void
{
    $match = find_match((int) input('match_id')) ?? not_found('Partida não encontrada');
    $back  = url('match', ['id' => $match['id'], 'tab' => 'manage']);

    $event = [
        'match_id'         => (int) $match['id'],
        'team_id'          => (int) input('team_id'),
        'player_id'        => int_or_null(input('player_id')),
        'assist_player_id' => int_or_null(input('assist_player_id')),
        'minute'           => (int) input('minute', 0),
        'type'             => (string) input('type', ''),
    ];

    if (!in_array($event['team_id'], [(int) $match['home_team_id'], (int) $match['away_team_id']], true)) fail('Time inválido.', $back);
    if (!isset(EVENT_TYPES[$event['type']]))                    fail('Tipo de evento inválido.', $back);
    if ($event['minute'] < 1 || $event['minute'] > 130)         fail('Minuto deve ser entre 1 e 130.', $back);
    if ($event['player_id'] && ($p = player($event['player_id'])) && (int) $p['team_id'] !== $event['team_id']) {
        fail('O jogador escolhido não pertence ao time selecionado.', $back);
    }
    if ($event['type'] !== 'gol' || $event['assist_player_id'] === $event['player_id']) {
        $event['assist_player_id'] = null; // só gol normal tem assistência
    }

    db()->prepare(
        'INSERT INTO match_events (match_id, team_id, player_id, assist_player_id, minute, type)
         VALUES (:match_id, :team_id, :player_id, :assist_player_id, :minute, :type)'
    )->execute($event);

    if (input('update_score')) {
        adjust_score($match, $event, +1);
    }

    flash(EVENT_TYPES[$event['type']] . " aos {$event['minute']}' registrado!");
    redirect($back);
}

function action_event_delete(): void
{
    $event = fetch_one('SELECT * FROM match_events WHERE id = ?', [(int) input('id')]) ?? not_found('Evento não encontrado');
    $match = find_match((int) $event['match_id']);

    db()->prepare('DELETE FROM match_events WHERE id = ?')->execute([$event['id']]);
    if ($match && input('update_score')) {
        adjust_score($match, $event, -1);
    }

    flash('Evento removido.');
    redirect(url('match', ['id' => $event['match_id'], 'tab' => 'manage']));
}

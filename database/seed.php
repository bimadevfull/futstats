<?php
declare(strict_types=1);

/**
 * Dados de demonstração.
 * Os clubes são reais (nome, cidade, estádio, fundação), mas JOGADORES, PLACARES
 * E ESTATÍSTICAS SÃO FICTÍCIOS, gerados aleatoriamente só para popular o sistema.
 */
function seed_database(PDO $pdo): void
{
    mt_srand(2026);
    $pdo->beginTransaction();

    $pdo->exec("INSERT INTO leagues (name, country, season) VALUES ('Brasileirão Série A', 'Brasil', '2026')");
    $leagueId = (int) $pdo->lastInsertId();

    // nome, sigla, cidade, estádio, fundação, cor 1, cor 2, força (para simular placares)
    $clubs = [
        ['Flamengo',          'FLA', 'Rio de Janeiro', 'Maracanã',            1895, '#c8102e', '#111111', 86],
        ['Palmeiras',         'PAL', 'São Paulo',      'Allianz Parque',      1914, '#006437', '#ffffff', 85],
        ['Botafogo',          'BOT', 'Rio de Janeiro', 'Estádio Nilton Santos', 1904, '#111111', '#ffffff', 80],
        ['São Paulo',         'SAO', 'São Paulo',      'MorumBIS',            1930, '#e4002b', '#ffffff', 77],
        ['Atlético Mineiro',  'CAM', 'Belo Horizonte', 'Arena MRV',           1908, '#1b1b1b', '#f5f5f5', 76],
        ['Internacional',     'INT', 'Porto Alegre',   'Beira-Rio',           1909, '#d50000', '#ffffff', 75],
        ['Fluminense',        'FLU', 'Rio de Janeiro', 'Maracanã',            1902, '#7a0026', '#00a35c', 74],
        ['Cruzeiro',          'CRU', 'Belo Horizonte', 'Mineirão',            1921, '#0038a8', '#ffffff', 74],
        ['Grêmio',            'GRE', 'Porto Alegre',   'Arena do Grêmio',     1903, '#0d80bf', '#111111', 73],
        ['Corinthians',       'COR', 'São Paulo',      'Neo Química Arena',   1910, '#f2f2f2', '#111111', 72],
        ['Bahia',             'BAH', 'Salvador',       'Arena Fonte Nova',    1931, '#0057b8', '#e30613', 71],
        ['Santos',            'SAN', 'Santos',         'Vila Belmiro',        1912, '#ffffff', '#111111', 68],
    ];

    $insTeam = $pdo->prepare(
        'INSERT INTO teams (league_id, name, short_name, city, stadium, founded, color_primary, color_secondary)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $insPlayer = $pdo->prepare(
        'INSERT INTO players (team_id, name, number, position, nationality, birth_date, height, foot, rating)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $first = ['Lucas', 'Gabriel', 'Matheus', 'Pedro', 'Rafael', 'Thiago', 'Bruno', 'Felipe', 'Gustavo', 'João',
              'Vinícius', 'Caio', 'Diego', 'Eduardo', 'Henrique', 'Igor', 'Léo', 'Marcos', 'Rodrigo', 'Samuel',
              'Vitor', 'André', 'Danilo', 'Renan', 'Wesley', 'Yuri', 'Kauã', 'Arthur', 'Davi', 'Enzo', 'Murilo', 'Otávio'];
    $last  = ['Silva', 'Santos', 'Oliveira', 'Souza', 'Lima', 'Pereira', 'Costa', 'Almeida', 'Ferreira', 'Rodrigues',
              'Carvalho', 'Gomes', 'Martins', 'Rocha', 'Ribeiro', 'Barbosa', 'Teixeira', 'Moura', 'Nunes', 'Freitas',
              'Cardoso', 'Araújo', 'Batista', 'Dias', 'Moreira', 'Cunha', 'Pires', 'Campos', 'Vieira', 'Monteiro'];
    $foreign = [
        ['Uruguai',   ['Nicolás', 'Agustín', 'Federico', 'Matías'], ['Fernández', 'Acosta', 'Olivera', 'Cabrera']],
        ['Argentina', ['Facundo', 'Lautaro', 'Tomás', 'Gonzalo'],   ['Pereyra', 'Medina', 'Romero', 'Benítez']],
        ['Colômbia',  ['Andrés', 'Juan', 'Santiago', 'Camilo'],     ['Restrepo', 'Valencia', 'Mosquera', 'Cuesta']],
        ['Paraguai',  ['Óscar', 'Miguel', 'Ramón', 'Hernán'],       ['Giménez', 'Villalba', 'Ortiz', 'Duarte']],
    ];
    $pick = fn (array $a) => $a[mt_rand(0, count($a) - 1)];

    $squadPlan = [
        'GOL' => [1, 12],
        'DEF' => [2, 3, 4, 6, 13, 14],
        'MEI' => [5, 8, 10, 15, 16, 20],
        'ATA' => [7, 9, 11, 19],
    ];

    $teams = []; // id => ['strength' => int, 'players' => [...]]
    foreach ($clubs as [$name, $short, $city, $stadium, $founded, $c1, $c2, $strength]) {
        $insTeam->execute([$leagueId, $name, $short, $city, $stadium, $founded, $c1, $c2]);
        $teamId = (int) $pdo->lastInsertId();
        $teams[$teamId] = ['strength' => $strength, 'stadium' => $stadium, 'players' => []];

        $used = [];
        foreach ($squadPlan as $pos => $numbers) {
            foreach ($numbers as $number) {
                do {
                    if (mt_rand(1, 100) <= 15) {
                        [$nat, $fn, $ln] = $pick($foreign);
                        $pname = $pick($fn) . ' ' . $pick($ln);
                    } else {
                        $nat   = 'Brasil';
                        $pname = $pick($first) . ' ' . $pick($last);
                    }
                } while (isset($used[$pname]));
                $used[$pname] = true;

                $birth  = sprintf('%d-%02d-%02d', mt_rand(1991, 2006), mt_rand(1, 12), mt_rand(1, 28));
                $height = match ($pos) { 'GOL' => mt_rand(185, 198), 'DEF' => mt_rand(176, 192), default => mt_rand(166, 186) };
                $foot   = mt_rand(1, 100) <= 70 ? 'Direito' : (mt_rand(1, 100) <= 85 ? 'Esquerdo' : 'Ambidestro');
                $rating = round(min(8.6, max(6.0, 6.2 + ($strength - 65) / 25 + mt_rand(-40, 70) / 100)), 1);

                $insPlayer->execute([$teamId, $pname, $number, $pos, $nat, $birth, $height, $foot, $rating]);
                $teams[$teamId]['players'][] = ['id' => (int) $pdo->lastInsertId(), 'pos' => $pos];
            }
        }
    }

    /* ---------- Tabela de jogos (turno e returno, método do círculo) ---------- */
    $ids = array_keys($teams);
    $n   = count($ids);
    $firstHalf = [];
    $rot = $ids;
    for ($r = 0; $r < $n - 1; $r++) {
        $pairs = [];
        for ($i = 0; $i < $n / 2; $i++) {
            [$a, $b] = [$rot[$i], $rot[$n - 1 - $i]];
            $pairs[] = (($r + $i) % 2 === 0) ? [$a, $b] : [$b, $a];
        }
        $firstHalf[] = $pairs;
        $last1 = array_pop($rot);
        array_splice($rot, 1, 0, [$last1]);
    }
    $schedule = $firstHalf;
    foreach ($firstHalf as $pairs) {
        $schedule[] = array_map(fn ($p) => [$p[1], $p[0]], $pairs);
    }

    $insMatch = $pdo->prepare(
        'INSERT INTO matches (league_id, round, home_team_id, away_team_id, match_date, status, minute, home_score, away_score,
            stadium, home_possession, home_shots, away_shots, home_shots_on, away_shots_on, home_corners, away_corners, home_fouls, away_fouls)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $insEvent = $pdo->prepare(
        'INSERT INTO match_events (match_id, team_id, player_id, assist_player_id, minute, type) VALUES (?, ?, ?, ?, ?, ?)'
    );

    $currentRound = 15;
    $now   = new DateTimeImmutable('now');
    $today = new DateTimeImmutable('today');
    $slots = ['16:00', '16:00', '18:30', '18:30', '19:00', '21:30'];

    foreach ($schedule as $rIdx => $pairs) {
        $round = $rIdx + 1;
        foreach ($pairs as $mIdx => [$home, $away]) {
            // datas relativas a hoje: rodadas passadas = encerradas, rodada atual mista, futuras = agendadas
            if ($round < $currentRound) {
                $status = 'finished';
                $date   = $today->modify(sprintf('%+d days', ($round - $currentRound) * 7))->modify($slots[$mIdx]);
            } elseif ($round === $currentRound) {
                $status = match (true) { $mIdx < 2 => 'finished', $mIdx < 4 => 'live', default => 'scheduled' };
                $date   = match ($status) {
                    'finished' => $today->modify('-1 day')->modify($slots[$mIdx]),
                    'live'     => $now->modify('-' . (50 + $mIdx * 12) . ' minutes'),
                    default    => $today->modify('+1 day')->modify($slots[$mIdx]),
                };
            } else {
                $status = 'scheduled';
                $date   = $today->modify(sprintf('+%d days', ($round - $currentRound) * 7 + 1))->modify($slots[$mIdx]);
            }

            $minute = $status === 'live' ? [2 => 38, 3 => 71][$mIdx] : null;
            $sh = $teams[$home]['strength'];
            $sa = $teams[$away]['strength'];

            $events = [];
            $hs = $as = 0;
            $stats = array_fill(0, 9, null);

            if ($status !== 'scheduled') {
                $hg = seed_poisson(max(0.3, 1.35 + ($sh - $sa) / 30));
                $ag = seed_poisson(max(0.25, 1.05 + ($sa - $sh) / 30));
                $events = array_merge(
                    seed_goal_events($teams, $home, $away, $hg),
                    seed_goal_events($teams, $away, $home, $ag),
                    seed_card_events($teams, $home),
                    seed_card_events($teams, $away),
                );
                if ($minute !== null) {
                    $events = array_values(array_filter($events, fn ($e) => $e['minute'] <= $minute));
                }
                foreach ($events as $e) {
                    if (!in_array($e['type'], ['gol', 'penalti', 'gol_contra'], true)) continue;
                    $forHome = (($e['team'] === $home) !== ($e['type'] === 'gol_contra'));
                    $forHome ? $hs++ : $as++;
                }

                $factor = $minute ? $minute / 90 : 1;
                $hShots = (int) round(($hs + mt_rand(5, 13)) * $factor);
                $aShots = (int) round(($as + mt_rand(4, 11)) * $factor);
                $stats = [
                    max(30, min(70, 50 + intdiv($sh - $sa, 2) + mt_rand(-7, 7))),
                    max($hShots, $hs), max($aShots, $as),
                    min(max($hShots, $hs), $hs + mt_rand(1, 5)), min(max($aShots, $as), $as + mt_rand(1, 4)),
                    (int) round(mt_rand(2, 9) * $factor), (int) round(mt_rand(1, 8) * $factor),
                    (int) round(mt_rand(8, 17) * $factor), (int) round(mt_rand(8, 18) * $factor),
                ];
            }

            $insMatch->execute([
                $leagueId, $round, $home, $away, $date->format('Y-m-d H:i:00'), $status, $minute, $hs, $as,
                $teams[$home]['stadium'], ...$stats,
            ]);
            $matchId = (int) $pdo->lastInsertId();

            foreach ($events as $e) {
                $insEvent->execute([$matchId, $e['team'], $e['player'], $e['assist'], $e['minute'], $e['type']]);
            }
        }
    }

    $pdo->commit();
}

function seed_poisson(float $lambda): int
{
    $l = exp(-$lambda);
    $k = 0;
    $p = 1.0;
    do {
        $k++;
        $p *= mt_rand() / mt_getrandmax();
    } while ($p > $l);
    return min($k - 1, 6);
}

function seed_weighted_player(array $players, array $weights, ?int $exclude = null): int
{
    $pool = [];
    foreach ($players as $p) {
        if ($p['id'] === $exclude) continue;
        $pool = array_merge($pool, array_fill(0, $weights[$p['pos']], $p['id']));
    }
    return $pool[mt_rand(0, count($pool) - 1)];
}

function seed_goal_events(array $teams, int $teamId, int $opponentId, int $goals): array
{
    $events = [];
    for ($i = 0; $i < $goals; $i++) {
        $minute = mt_rand(1, 94);
        $roll   = mt_rand(1, 100);
        if ($roll <= 4) {
            // gol contra: marcado por um jogador do adversário
            $events[] = ['team' => $opponentId, 'type' => 'gol_contra', 'minute' => $minute, 'assist' => null,
                         'player' => seed_weighted_player($teams[$opponentId]['players'], ['GOL' => 0, 'DEF' => 5, 'MEI' => 2, 'ATA' => 0])];
            continue;
        }
        $scorer = seed_weighted_player($teams[$teamId]['players'], ['GOL' => 0, 'DEF' => 1, 'MEI' => 3, 'ATA' => 7]);
        $type   = $roll <= 14 ? 'penalti' : 'gol';
        $assist = ($type === 'gol' && mt_rand(1, 100) <= 72)
            ? seed_weighted_player($teams[$teamId]['players'], ['GOL' => 0, 'DEF' => 2, 'MEI' => 5, 'ATA' => 3], $scorer)
            : null;
        $events[] = ['team' => $teamId, 'type' => $type, 'minute' => $minute, 'player' => $scorer, 'assist' => $assist];
    }
    return $events;
}

function seed_card_events(array $teams, int $teamId): array
{
    $events = [];
    $yellows = mt_rand(0, 4);
    for ($i = 0; $i < $yellows; $i++) {
        $events[] = ['team' => $teamId, 'type' => 'amarelo', 'minute' => mt_rand(8, 92), 'assist' => null,
                     'player' => seed_weighted_player($teams[$teamId]['players'], ['GOL' => 1, 'DEF' => 5, 'MEI' => 5, 'ATA' => 2])];
    }
    if (mt_rand(1, 100) <= 7) {
        $events[] = ['team' => $teamId, 'type' => 'vermelho', 'minute' => mt_rand(30, 90), 'assist' => null,
                     'player' => seed_weighted_player($teams[$teamId]['players'], ['GOL' => 1, 'DEF' => 5, 'MEI' => 3, 'ATA' => 1])];
    }
    return $events;
}

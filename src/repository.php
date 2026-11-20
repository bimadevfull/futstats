<?php
declare(strict_types=1);

/* ============================================================
 *  Consultas ao banco (camada de dados)
 * ============================================================ */

function fetch_all(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetch_one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function fetch_value(string $sql, array $params = []): mixed
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

/* ---------- Ligas ---------- */

function leagues(): array
{
    return fetch_all('SELECT * FROM leagues ORDER BY name');
}

function league(int $id): ?array
{
    return fetch_one('SELECT * FROM leagues WHERE id = ?', [$id]);
}

function current_league(): ?array
{
    $id = (int) ($_GET['league'] ?? 0);
    return ($id ? league($id) : null) ?? fetch_one('SELECT * FROM leagues ORDER BY id LIMIT 1');
}

/* ---------- Times ---------- */

function teams(?int $leagueId = null): array
{
    if ($leagueId) {
        return fetch_all('SELECT * FROM teams WHERE league_id = ? ORDER BY name', [$leagueId]);
    }
    return fetch_all('SELECT * FROM teams ORDER BY name');
}

function team(int $id): ?array
{
    return fetch_one(
        'SELECT t.*, l.name AS league_name FROM teams t LEFT JOIN leagues l ON l.id = t.league_id WHERE t.id = ?',
        [$id]
    );
}

/* ---------- Jogadores ---------- */

const PLAYER_GOALS_SQL = "(SELECT COUNT(*) FROM match_events e WHERE e.player_id = p.id AND e.type IN ('gol','penalti'))";

function players_by_team(int $teamId): array
{
    return fetch_all(
        'SELECT p.*, ' . PLAYER_GOALS_SQL . " AS goals
         FROM players p WHERE p.team_id = ?
         ORDER BY CASE p.position WHEN 'GOL' THEN 1 WHEN 'DEF' THEN 2 WHEN 'MEI' THEN 3 ELSE 4 END, p.number",
        [$teamId]
    );
}

function players_search(string $q = '', ?int $teamId = null, ?string $position = null): array
{
    $where  = ['1=1'];
    $params = [];
    if ($q !== '') {
        $where[]  = '(p.name LIKE ? OR p.nationality LIKE ?)';
        $params[] = "%{$q}%";
        $params[] = "%{$q}%";
    }
    if ($teamId) {
        $where[]  = 'p.team_id = ?';
        $params[] = $teamId;
    }
    if ($position && isset(POSITIONS[$position])) {
        $where[]  = 'p.position = ?';
        $params[] = $position;
    }
    return fetch_all(
        'SELECT p.*, t.name AS team_name, t.short_name, t.color_primary, t.color_secondary, '
        . PLAYER_GOALS_SQL . ' AS goals,
           (SELECT COUNT(*) FROM match_events e WHERE e.assist_player_id = p.id) AS assists
         FROM players p JOIN teams t ON t.id = p.team_id
         WHERE ' . implode(' AND ', $where) . '
         ORDER BY goals DESC, p.rating DESC, p.name
         LIMIT 500',
        $params
    );
}

function player(int $id): ?array
{
    return fetch_one(
        'SELECT p.*, t.name AS team_name, t.short_name, t.color_primary, t.color_secondary
         FROM players p JOIN teams t ON t.id = p.team_id WHERE p.id = ?',
        [$id]
    );
}

function player_stats(int $playerId): array
{
    $row = fetch_one(
        "SELECT
            SUM(CASE WHEN type IN ('gol','penalti') AND player_id = :id THEN 1 ELSE 0 END) AS goals,
            SUM(CASE WHEN type = 'penalti' AND player_id = :id THEN 1 ELSE 0 END)        AS penalties,
            SUM(CASE WHEN assist_player_id = :id THEN 1 ELSE 0 END)                      AS assists,
            SUM(CASE WHEN type = 'amarelo' AND player_id = :id THEN 1 ELSE 0 END)        AS yellows,
            SUM(CASE WHEN type = 'vermelho' AND player_id = :id THEN 1 ELSE 0 END)       AS reds
         FROM match_events WHERE player_id = :id OR assist_player_id = :id",
        ['id' => $playerId]
    ) ?? [];
    return array_map(fn ($v) => (int) $v, $row);
}

function player_events(int $playerId): array
{
    return match_query(
        MATCH_SELECT_SQL . ' JOIN match_events e ON e.match_id = m.id
         WHERE e.player_id = :id OR e.assist_player_id = :id
         ORDER BY m.match_date DESC, e.minute',
        ['id' => $playerId],
        'e.type AS ev_type, e.minute AS ev_minute, e.player_id AS ev_player, e.assist_player_id AS ev_assist'
    );
}

function top_scorers(int $leagueId, int $limit = 10): array
{
    return fetch_all(
        "SELECT p.id, p.name, p.position, t.id AS team_id, t.name AS team_name, t.short_name,
                t.color_primary, t.color_secondary, COUNT(*) AS goals
         FROM match_events e
         JOIN players p ON p.id = e.player_id
         JOIN teams t   ON t.id = p.team_id
         JOIN matches m ON m.id = e.match_id
         WHERE e.type IN ('gol','penalti') AND m.league_id = ?
         GROUP BY p.id
         ORDER BY goals DESC, p.name
         LIMIT ?",
        [$leagueId, $limit]
    );
}

/* ---------- Partidas ---------- */

const MATCH_SELECT_SQL = '
    SELECT m.*, l.name AS league_name,
           ht.name AS home_name, ht.short_name AS home_short, ht.color_primary AS home_c1, ht.color_secondary AS home_c2,
           at.name AS away_name, at.short_name AS away_short, at.color_primary AS away_c1, at.color_secondary AS away_c2
           /*EXTRA*/
    FROM matches m
    JOIN teams ht ON ht.id = m.home_team_id
    JOIN teams at ON at.id = m.away_team_id
    LEFT JOIN leagues l ON l.id = m.league_id';

function match_query(string $sql, array $params = [], string $extraCols = ''): array
{
    $sql = str_replace('/*EXTRA*/', $extraCols ? ', ' . $extraCols : '', $sql);
    return fetch_all($sql, $params);
}

/**
 * @param array{league?:int,round?:int,status?:string,team?:int,limit?:int,order?:string} $f
 */
function matches(array $f = []): array
{
    $where  = ['1=1'];
    $params = [];
    if (!empty($f['league'])) { $where[] = 'm.league_id = ?'; $params[] = $f['league']; }
    if (!empty($f['round']))  { $where[] = 'm.round = ?';     $params[] = $f['round']; }
    if (!empty($f['status']) && isset(STATUSES[$f['status']])) { $where[] = 'm.status = ?'; $params[] = $f['status']; }
    if (!empty($f['team']))   { $where[] = '(m.home_team_id = ? OR m.away_team_id = ?)'; $params[] = $f['team']; $params[] = $f['team']; }

    $order = ($f['order'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
    $sql   = MATCH_SELECT_SQL . ' WHERE ' . implode(' AND ', $where) . " ORDER BY m.match_date {$order}, m.id";
    if (!empty($f['limit'])) {
        $sql .= ' LIMIT ' . (int) $f['limit'];
    }
    return match_query($sql, $params);
}

function find_match(int $id): ?array
{
    return match_query(MATCH_SELECT_SQL . ' WHERE m.id = ?', [$id])[0] ?? null;
}

function match_events(int $matchId): array
{
    return fetch_all(
        'SELECT e.*, p.name AS player_name, a.name AS assist_name
         FROM match_events e
         LEFT JOIN players p ON p.id = e.player_id
         LEFT JOIN players a ON a.id = e.assist_player_id
         WHERE e.match_id = ?
         ORDER BY e.minute, e.id',
        [$matchId]
    );
}

function rounds(int $leagueId): array
{
    return array_map('intval', array_column(
        fetch_all('SELECT DISTINCT round FROM matches WHERE league_id = ? AND round IS NOT NULL ORDER BY round', [$leagueId]),
        'round'
    ));
}

/** Rodada "atual": a que tem jogo ao vivo, senão a primeira com jogo agendado, senão a última. */
function current_round(int $leagueId): ?int
{
    $r = fetch_value("SELECT MIN(round) FROM matches WHERE league_id = ? AND status = 'live'", [$leagueId])
        ?: fetch_value("SELECT MIN(round) FROM matches WHERE league_id = ? AND status = 'scheduled'", [$leagueId])
        ?: fetch_value('SELECT MAX(round) FROM matches WHERE league_id = ?', [$leagueId]);
    return $r ? (int) $r : null;
}

/* ---------- Classificação ---------- */

function standings(int $leagueId): array
{
    $table = [];
    foreach (teams($leagueId) as $t) {
        $table[$t['id']] = $t + ['p' => 0, 'w' => 0, 'd' => 0, 'l' => 0, 'gf' => 0, 'ga' => 0, 'gd' => 0, 'pts' => 0, 'form' => []];
    }

    $finished = fetch_all(
        "SELECT * FROM matches WHERE league_id = ? AND status = 'finished' ORDER BY match_date, id",
        [$leagueId]
    );

    foreach ($finished as $m) {
        $h = $m['home_team_id'];
        $a = $m['away_team_id'];
        if (!isset($table[$h], $table[$a])) continue;

        [$hs, $as] = [(int) $m['home_score'], (int) $m['away_score']];
        foreach ([[$h, $hs, $as], [$a, $as, $hs]] as [$id, $for, $against]) {
            $row = &$table[$id];
            $row['p']++;
            $row['gf'] += $for;
            $row['ga'] += $against;
            if ($for > $against)      { $row['w']++; $row['pts'] += 3; $row['form'][] = 'V'; }
            elseif ($for === $against) { $row['d']++; $row['pts'] += 1; $row['form'][] = 'E'; }
            else                      { $row['l']++; $row['form'][] = 'D'; }
            unset($row);
        }
    }

    foreach ($table as &$row) {
        $row['gd']   = $row['gf'] - $row['ga'];
        $row['form'] = array_slice($row['form'], -5);
        $row['pct']  = pct($row['pts'], $row['p'] * 3);
    }
    unset($row);

    usort($table, fn ($x, $y) =>
        [$y['pts'], $y['w'], $y['gd'], $y['gf'], $x['name']] <=> [$x['pts'], $x['w'], $x['gd'], $x['gf'], $y['name']]
    );

    foreach ($table as $i => &$row) {
        $row['pos'] = $i + 1;
    }
    return $table;
}

/** Zona da tabela (cores da classificação). */
function zone(int $pos, int $total): string
{
    if ($pos <= 4) return 'z-champions';
    if ($pos <= 6) return 'z-continental';
    if ($total >= 8 && $pos > $total - 2) return 'z-relegation';
    return '';
}

/* ---------- Dashboard ---------- */

function dashboard_counts(?int $leagueId): array
{
    return [
        'teams'   => (int) fetch_value('SELECT COUNT(*) FROM teams'),
        'players' => (int) fetch_value('SELECT COUNT(*) FROM players'),
        'played'  => (int) fetch_value("SELECT COUNT(*) FROM matches WHERE status = 'finished'"),
        'goals'   => (int) fetch_value("SELECT COALESCE(SUM(home_score + away_score), 0) FROM matches WHERE status = 'finished'"),
        'live'    => (int) fetch_value("SELECT COUNT(*) FROM matches WHERE status = 'live'"),
    ];
}

function global_search(string $q): array
{
    if (mb_strlen($q) < 2) return ['teams' => [], 'players' => []];
    $like = "%{$q}%";
    return [
        'teams'   => fetch_all('SELECT * FROM teams WHERE name LIKE ? OR short_name LIKE ? OR city LIKE ? ORDER BY name LIMIT 20', [$like, $like, $like]),
        'players' => players_search($q),
    ];
}

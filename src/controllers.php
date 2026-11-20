<?php
declare(strict_types=1);

/* ============================================================
 *  Páginas (GET). Cada função monta os dados e renderiza a view.
 * ============================================================ */

function page_home(): void
{
    $league   = current_league();
    $leagueId = (int) ($league['id'] ?? 0);

    render('home', [
        'league'   => $league,
        'counts'   => dashboard_counts($leagueId),
        'live'     => matches(['status' => 'live']),
        'recent'   => matches(['status' => 'finished', 'order' => 'desc', 'limit' => 8]),
        'upcoming' => matches(['status' => 'scheduled', 'limit' => 6]),
        'table'    => $leagueId ? standings($leagueId) : [],
        'scorers'  => $leagueId ? top_scorers($leagueId, 8) : [],
    ], 'Início');
}

function page_matches(): void
{
    $league   = current_league();
    $leagueId = (int) ($league['id'] ?? 0);
    $teamId   = int_or_null($_GET['team'] ?? null);
    $status   = (string) ($_GET['status'] ?? '');
    $rounds   = $leagueId ? rounds($leagueId) : [];

    // Com filtro de time a lista mostra todas as rodadas; sem ele, a rodada atual.
    $round = isset($_GET['round']) ? (int) $_GET['round'] : ($teamId ? 0 : ($leagueId ? (int) current_round($leagueId) : 0));

    $list = matches([
        'league' => $leagueId,
        'round'  => $round ?: null,
        'status' => $status,
        'team'   => $teamId,
    ]);

    $grouped = [];
    foreach ($list as $m) {
        $grouped[substr($m['match_date'], 0, 10)][] = $m;
    }

    render('matches', compact('league', 'rounds', 'round', 'status', 'teamId', 'grouped') + [
        'teams' => teams($leagueId ?: null),
    ], 'Partidas');
}

function page_match(): void
{
    $match = find_match((int) ($_GET['id'] ?? 0)) ?? not_found('Partida não encontrada');

    $standingsPos = [];
    if ($match['league_id']) {
        foreach (standings((int) $match['league_id']) as $row) {
            $standingsPos[$row['id']] = $row;
        }
    }

    render('match', [
        'm'            => $match,
        'events'       => match_events((int) $match['id']),
        'homePlayers'  => players_by_team((int) $match['home_team_id']),
        'awayPlayers'  => players_by_team((int) $match['away_team_id']),
        'standingsPos' => $standingsPos,
        'h2h'          => match_query(
            MATCH_SELECT_SQL . " WHERE m.status = 'finished' AND m.id != ?
              AND ((m.home_team_id = ? AND m.away_team_id = ?) OR (m.home_team_id = ? AND m.away_team_id = ?))
              ORDER BY m.match_date DESC LIMIT 5",
            [$match['id'], $match['home_team_id'], $match['away_team_id'], $match['away_team_id'], $match['home_team_id']]
        ),
    ], $match['home_name'] . ' x ' . $match['away_name']);
}

function page_match_form(): void
{
    $id    = int_or_null($_GET['id'] ?? null);
    $match = $id ? (find_match($id) ?? not_found('Partida não encontrada')) : null;

    render('match_form', [
        'm'       => $match,
        'teams'   => teams(),
        'leagues' => leagues(),
    ], $match ? 'Editar partida' : 'Nova partida');
}

function page_standings(): void
{
    $league = current_league();
    render('standings', [
        'league'  => $league,
        'leagues' => leagues(),
        'table'   => $league ? standings((int) $league['id']) : [],
        'scorers' => $league ? top_scorers((int) $league['id'], 15) : [],
    ], 'Classificação');
}

function page_teams(): void
{
    $positions = [];
    foreach (leagues() as $l) {
        foreach (standings((int) $l['id']) as $row) {
            $positions[$row['id']] = $row;
        }
    }
    $teams = fetch_all(
        'SELECT t.*, (SELECT COUNT(*) FROM players p WHERE p.team_id = t.id) AS players_count
         FROM teams t ORDER BY t.name'
    );
    render('teams', compact('teams', 'positions'), 'Times');
}

function page_team(): void
{
    $team = team((int) ($_GET['id'] ?? 0)) ?? not_found('Time não encontrado');
    $id   = (int) $team['id'];

    $row = null;
    if ($team['league_id']) {
        foreach (standings((int) $team['league_id']) as $r) {
            if ((int) $r['id'] === $id) { $row = $r; break; }
        }
    }

    $squad = [];
    foreach (players_by_team($id) as $p) {
        $squad[$p['position']][] = $p;
    }

    render('team', [
        't'        => $team,
        'row'      => $row,
        'squad'    => $squad,
        'recent'   => matches(['team' => $id, 'status' => 'finished', 'order' => 'desc', 'limit' => 6]),
        'upcoming' => array_merge(
            matches(['team' => $id, 'status' => 'live']),
            matches(['team' => $id, 'status' => 'scheduled', 'limit' => 5])
        ),
    ], $team['name']);
}

function page_team_form(): void
{
    $id   = int_or_null($_GET['id'] ?? null);
    $team = $id ? (team($id) ?? not_found('Time não encontrado')) : null;
    render('team_form', ['t' => $team, 'leagues' => leagues()], $team ? 'Editar time' : 'Novo time');
}

function page_players(): void
{
    $q        = trim((string) ($_GET['q'] ?? ''));
    $teamId   = int_or_null($_GET['team'] ?? null);
    $position = (string) ($_GET['position'] ?? '');

    render('players', [
        'players'  => players_search($q, $teamId, $position ?: null),
        'teams'    => teams(),
        'q'        => $q,
        'teamId'   => $teamId,
        'position' => $position,
    ], 'Jogadores');
}

function page_player(): void
{
    $player = player((int) ($_GET['id'] ?? 0)) ?? not_found('Jogador não encontrado');
    render('player', [
        'p'      => $player,
        'stats'  => player_stats((int) $player['id']),
        'events' => player_events((int) $player['id']),
    ], $player['name']);
}

function page_player_form(): void
{
    $id     = int_or_null($_GET['id'] ?? null);
    $player = $id ? (player($id) ?? not_found('Jogador não encontrado')) : null;
    render('player_form', [
        'p'      => $player,
        'teams'  => teams(),
        'teamId' => int_or_null($_GET['team'] ?? null),
    ], $player ? 'Editar jogador' : 'Novo jogador');
}

function page_search(): void
{
    $q = trim((string) ($_GET['q'] ?? ''));
    render('search', ['q' => $q, 'results' => global_search($q)], 'Busca');
}

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS leagues (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    country    TEXT,
    season     TEXT
);

CREATE TABLE IF NOT EXISTS teams (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    league_id       INTEGER REFERENCES leagues(id) ON DELETE SET NULL,
    name            TEXT NOT NULL,
    short_name      TEXT NOT NULL,
    city            TEXT,
    stadium         TEXT,
    founded         INTEGER,
    coach           TEXT,
    color_primary   TEXT NOT NULL DEFAULT '#3b5bfd',
    color_secondary TEXT NOT NULL DEFAULT '#ffffff',
    created_at      TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS players (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    team_id     INTEGER NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
    name        TEXT NOT NULL,
    number      INTEGER,
    position    TEXT NOT NULL CHECK (position IN ('GOL','DEF','MEI','ATA')),
    nationality TEXT,
    birth_date  TEXT,
    height      INTEGER,
    foot        TEXT CHECK (foot IN ('Direito','Esquerdo','Ambidestro') OR foot IS NULL),
    rating      REAL,
    created_at  TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS matches (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    league_id        INTEGER REFERENCES leagues(id) ON DELETE SET NULL,
    round            INTEGER,
    home_team_id     INTEGER NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
    away_team_id     INTEGER NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
    match_date       TEXT NOT NULL,
    status           TEXT NOT NULL DEFAULT 'scheduled' CHECK (status IN ('scheduled','live','finished')),
    minute           INTEGER,
    home_score       INTEGER NOT NULL DEFAULT 0,
    away_score       INTEGER NOT NULL DEFAULT 0,
    stadium          TEXT,
    home_possession  INTEGER,
    home_shots       INTEGER, away_shots     INTEGER,
    home_shots_on    INTEGER, away_shots_on  INTEGER,
    home_corners     INTEGER, away_corners   INTEGER,
    home_fouls       INTEGER, away_fouls     INTEGER
);

-- type: gol | penalti | gol_contra | amarelo | vermelho
-- team_id = time do jogador que gerou o evento (num gol contra, o gol conta para o adversário)
CREATE TABLE IF NOT EXISTS match_events (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    match_id         INTEGER NOT NULL REFERENCES matches(id) ON DELETE CASCADE,
    team_id          INTEGER NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
    player_id        INTEGER REFERENCES players(id) ON DELETE SET NULL,
    assist_player_id INTEGER REFERENCES players(id) ON DELETE SET NULL,
    minute           INTEGER NOT NULL,
    type             TEXT NOT NULL CHECK (type IN ('gol','penalti','gol_contra','amarelo','vermelho'))
);

CREATE INDEX IF NOT EXISTS idx_players_team   ON players(team_id);
CREATE INDEX IF NOT EXISTS idx_matches_league ON matches(league_id, round);
CREATE INDEX IF NOT EXISTS idx_matches_date   ON matches(match_date);
CREATE INDEX IF NOT EXISTS idx_events_match   ON match_events(match_id);
CREATE INDEX IF NOT EXISTS idx_events_player  ON match_events(player_id);

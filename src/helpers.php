<?php
declare(strict_types=1);

/* ============================================================
 *  Configuração / banco
 * ============================================================ */

function app_config(): array
{
    static $config = null;
    return $config ??= require BASE_PATH . '/config.php';
}

function db(): PDO
{
    return Database::get();
}

/* ============================================================
 *  HTTP / rotas
 * ============================================================ */

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function url(string $page = 'home', array $params = []): string
{
    if ($page === 'home' && !$params) {
        return 'index.php';
    }
    return 'index.php?' . http_build_query(['p' => $page] + $params);
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function back(string $fallback = 'index.php'): never
{
    redirect($_SERVER['HTTP_REFERER'] ?? $fallback);
}

function input(string $key, mixed $default = null): mixed
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $value;
}

function int_or_null(mixed $value): ?int
{
    return ($value === '' || $value === null) ? null : (int) $value;
}

/* ============================================================
 *  Segurança (CSRF)
 * ============================================================ */

function csrf_token(): string
{
    return $_SESSION['_csrf'] ??= bin2hex(random_bytes(16));
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals(csrf_token(), (string) ($_POST['_csrf'] ?? ''))) {
        http_response_code(419);
        exit('Requisição inválida (token CSRF). Volte e tente novamente.');
    }
}

/* ============================================================
 *  Flash messages / old input
 * ============================================================ */

function flash(?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['_flash'] = ['msg' => $message, 'type' => $type];
        return null;
    }
    $flash = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return $flash;
}

function with_old_input(): void
{
    $_SESSION['_old'] = $_POST;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function clear_old_input(): void
{
    unset($_SESSION['_old']);
}

/* ============================================================
 *  Renderização
 * ============================================================ */

function render(string $view, array $data = [], string $title = ''): void
{
    extract($data, EXTR_SKIP);
    ob_start();
    require BASE_PATH . "/views/{$view}.php";
    $content = (string) ob_get_clean();
    clear_old_input();
    require BASE_PATH . '/views/layout.php';
}

function partial(string $name, array $data = []): string
{
    extract($data, EXTR_SKIP);
    ob_start();
    require BASE_PATH . "/views/partials/{$name}.php";
    return (string) ob_get_clean();
}

function not_found(string $message = 'Página não encontrada'): never
{
    http_response_code(404);
    render('404', ['message' => $message], 'Não encontrado');
    exit;
}

/* ============================================================
 *  Apresentação
 * ============================================================ */

function safe_color(?string $color, string $fallback = '#3b5bfd'): string
{
    return ($color && preg_match('/^#[0-9a-fA-F]{6}$/', $color)) ? $color : $fallback;
}

/** Escudo gerado com as cores do time (sem imagens externas). */
function badge(array $team, string $size = 'md'): string
{
    $c1    = safe_color($team['color_primary'] ?? null);
    $c2    = safe_color($team['color_secondary'] ?? null, '#ffffff');
    $short = mb_strtoupper(mb_substr((string) ($team['short_name'] ?? $team['name'] ?? '?'), 0, 3));
    return sprintf(
        '<span class="crest crest-%s" style="--c1:%s;--c2:%s" title="%s"><span>%s</span></span>',
        e($size), $c1, $c2, e($team['name'] ?? ''), e($short)
    );
}

/** Extrai os dados do mandante/visitante de uma linha de partida. */
function side(array $m, string $side): array
{
    return [
        'id'              => $m[$side . '_team_id'],
        'name'            => $m[$side . '_name'],
        'short_name'      => $m[$side . '_short'],
        'color_primary'   => $m[$side . '_c1'],
        'color_secondary' => $m[$side . '_c2'],
    ];
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $first = mb_substr($parts[0] ?? '', 0, 1);
    $last  = count($parts) > 1 ? mb_substr((string) end($parts), 0, 1) : '';
    return mb_strtoupper($first . $last);
}

const POSITIONS = ['GOL' => 'Goleiro', 'DEF' => 'Defensor', 'MEI' => 'Meio-campista', 'ATA' => 'Atacante'];
const POSITIONS_PLURAL = ['GOL' => 'Goleiros', 'DEF' => 'Defensores', 'MEI' => 'Meio-campistas', 'ATA' => 'Atacantes'];
const STATUSES  = ['scheduled' => 'Agendado', 'live' => 'Ao vivo', 'finished' => 'Encerrado'];
const EVENT_TYPES = [
    'gol'        => 'Gol',
    'penalti'    => 'Gol de pênalti',
    'gol_contra' => 'Gol contra',
    'amarelo'    => 'Cartão amarelo',
    'vermelho'   => 'Cartão vermelho',
];
const GOAL_TYPES = ['gol', 'penalti', 'gol_contra'];

function position_label(?string $pos): string
{
    return POSITIONS[$pos] ?? '—';
}

function rating_class(?float $rating): string
{
    if ($rating === null) return 'r-none';
    return match (true) {
        $rating >= 8.0 => 'r-elite',
        $rating >= 7.5 => 'r-great',
        $rating >= 7.0 => 'r-good',
        $rating >= 6.5 => 'r-ok',
        default        => 'r-low',
    };
}

function age(?string $birthDate): ?int
{
    if (!$birthDate) return null;
    try {
        return (new DateTime($birthDate))->diff(new DateTime('today'))->y;
    } catch (Exception) {
        return null;
    }
}

function fmt_date(?string $datetime, string $format = 'd/m/Y H:i'): string
{
    if (!$datetime) return '—';
    try {
        return (new DateTime($datetime))->format($format);
    } catch (Exception) {
        return '—';
    }
}

/** "Hoje", "Amanhã", "Ontem" ou "Sáb, 21 nov". */
function friendly_day(string $datetime): string
{
    $d     = new DateTime(substr($datetime, 0, 10));
    $today = new DateTime('today');
    $diff  = (int) $today->diff($d)->format('%r%a');
    if ($diff === 0)  return 'Hoje';
    if ($diff === 1)  return 'Amanhã';
    if ($diff === -1) return 'Ontem';

    $weekdays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
    $months   = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
    return sprintf('%s, %d %s', $weekdays[(int) $d->format('w')], (int) $d->format('j'), $months[(int) $d->format('n') - 1]);
}

function pct(int|float $part, int|float $total): int
{
    return $total > 0 ? (int) round($part / $total * 100) : 0;
}

function active(string $section): string
{
    $page = $_GET['p'] ?? 'home';
    $map  = [
        'home'      => ['home'],
        'matches'   => ['matches', 'match', 'match_form'],
        'standings' => ['standings'],
        'teams'     => ['teams', 'team', 'team_form'],
        'players'   => ['players', 'player', 'player_form'],
    ];
    return in_array($page, $map[$section] ?? [], true) ? 'active' : '';
}

/* ============================================================
 *  Ícones SVG (traço, estilo Lucide)
 * ============================================================ */

function icon(string $name, int $size = 18): string
{
    static $paths = [
        'ball'     => '<circle cx="12" cy="12" r="10"/><path d="m12 7 4.3 3.1-1.6 5H9.3l-1.6-5z"/><path d="M12 2v5M21.5 9.2l-5.2.9M17.8 20l-3.1-4.9M6.2 20l3.1-4.9M2.5 9.2l5.2.9"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'trophy'   => '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6M18 9h1.5a2.5 2.5 0 0 0 0-5H18M4 22h16M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0z"/>',
        'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'users'    => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'home'     => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
        'search'   => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>',
        'moon'     => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9z"/>',
        'menu'     => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'edit'     => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'trash'    => '<path d="M3 6h18M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>',
        'pin'      => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
        'clock'    => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'left'     => '<path d="m15 18-6-6 6-6"/>',
        'right'    => '<path d="m9 18 6-6-6-6"/>',
        'whistle'  => '<path d="M2 12a5 5 0 0 0 10 0V7h10v4h-6"/><circle cx="7" cy="12" r="1.5"/>',
        'flag'     => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1zM4 22v-7"/>',
        'chart'    => '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>',
        'target'   => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
        'check'    => '<path d="M20 6 9 17l-5-5"/>',
        'alert'    => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',
    ];
    return sprintf(
        '<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%2$s</svg>',
        $size, $paths[$name] ?? ''
    );
}

/** Ícone de evento da partida (gol, cartão…). */
function event_icon(string $type): string
{
    return match ($type) {
        'amarelo'    => '<span class="ev-icon"><i class="card-yellow"></i></span>',
        'vermelho'   => '<span class="ev-icon"><i class="card-red"></i></span>',
        'gol_contra' => '<span class="ev-icon ev-og">' . icon('ball', 18) . '</span>',
        default      => '<span class="ev-icon ev-goal">' . icon('ball', 18) . '</span>',
    };
}

function delete_button(string $action, int $id, string $confirm, string $label = 'Excluir', string $extra = ''): string
{
    return '<form method="post" action="' . e(url($action)) . '" class="inline-form" data-confirm="' . e($confirm) . '">'
        . csrf_field() . '<input type="hidden" name="id" value="' . $id . '">' . $extra
        . '<button class="btn btn-danger btn-sm" type="submit">' . icon('trash', 14) . ($label ? ' ' . e($label) : '') . '</button></form>';
}

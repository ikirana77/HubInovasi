<?php
declare(strict_types=1);

/**
 * Lightweight bilingual layer for HubInovasi.
 * BM is deliberately the default. English is opt-in and persisted in session/cookie.
 */
const HUBINOVASI_DEFAULT_LANGUAGE = 'ms';
const HUBINOVASI_SUPPORTED_LANGUAGES = ['ms', 'en'];
const HUBINOVASI_LANGUAGE_COOKIE = 'hubinovasi_lang';

function normalize_app_language(?string $language): string
{
    $language = strtolower(trim((string) $language));
    return in_array($language, HUBINOVASI_SUPPORTED_LANGUAGES, true)
        ? $language
        : HUBINOVASI_DEFAULT_LANGUAGE;
}

function initialise_app_language(): void
{
    $requested = isset($_GET['lang']) ? normalize_app_language((string) $_GET['lang']) : null;

    if ($requested !== null) {
        $_SESSION['app_language'] = $requested;

        if (PHP_SAPI !== 'cli' && !headers_sent()) {
            $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            setcookie(HUBINOVASI_LANGUAGE_COOKIE, $requested, [
                'expires' => time() + 31536000,
                'path' => '/',
                'secure' => $isHttps,
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
        }
        return;
    }

    if (!empty($_SESSION['app_language'])) {
        $_SESSION['app_language'] = normalize_app_language((string) $_SESSION['app_language']);
        return;
    }

    if (!empty($_COOKIE[HUBINOVASI_LANGUAGE_COOKIE])) {
        $_SESSION['app_language'] = normalize_app_language((string) $_COOKIE[HUBINOVASI_LANGUAGE_COOKIE]);
        return;
    }

    $_SESSION['app_language'] = HUBINOVASI_DEFAULT_LANGUAGE;
}

function app_language(): string
{
    return normalize_app_language((string) ($_SESSION['app_language'] ?? HUBINOVASI_DEFAULT_LANGUAGE));
}

function is_english(): bool
{
    return app_language() === 'en';
}

function tr(string $malay, string $english): string
{
    return is_english() ? $english : $malay;
}

/** @return array<string,mixed> */
function language_dictionary(?string $language = null): array
{
    static $cache = [];
    $language = normalize_app_language($language ?? app_language());

    if (!isset($cache[$language])) {
        $file = __DIR__ . '/../lang/' . $language . '.php';
        $cache[$language] = is_file($file) ? (array) require $file : [];
    }

    return $cache[$language];
}

function t(string $key, array $replace = []): string
{
    $dictionary = language_dictionary();
    $value = $dictionary;

    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            $fallback = language_dictionary(HUBINOVASI_DEFAULT_LANGUAGE);
            $value = $fallback;
            foreach (explode('.', $key) as $fallbackSegment) {
                if (!is_array($value) || !array_key_exists($fallbackSegment, $value)) {
                    $value = $key;
                    break;
                }
                $value = $value[$fallbackSegment];
            }
            break;
        }
        $value = $value[$segment];
    }

    $text = is_scalar($value) ? (string) $value : $key;
    foreach ($replace as $placeholder => $replacement) {
        $text = str_replace('{' . $placeholder . '}', (string) $replacement, $text);
    }
    return $text;
}

function language_switch_url(string $language): string
{
    $language = normalize_app_language($language);
    $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? 'index.php');
    $path = (string) (parse_url($requestUri, PHP_URL_PATH) ?: 'index.php');
    $query = (string) (parse_url($requestUri, PHP_URL_QUERY) ?: '');
    $params = [];
    if ($query !== '') parse_str($query, $params);
    $params['lang'] = $language;

    return $path . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
}

/**
 * Overlay verified English copy for public projects while retaining BM as the source language.
 * Future projects safely fall back to their BM database copy until an English translation is added.
 */
function localize_project(array $project): array
{
    if (!is_english() || empty($project['slug'])) return $project;

    $dictionary = language_dictionary('en');
    $translation = $dictionary['projects'][(string) $project['slug']] ?? null;
    return is_array($translation) ? array_replace($project, $translation) : $project;
}

initialise_app_language();

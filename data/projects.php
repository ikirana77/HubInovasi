<?php
/**
 * Compatibility loader untuk halaman katalog.
 * Sumber tunggal projek awam kini ialah MySQL, bukan array hardcoded.
 */
require_once __DIR__ . '/../includes/project-repository.php';
$projects = get_public_projects();

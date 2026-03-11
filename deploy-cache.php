#!/usr/bin/env php
<?php
/**
 * Script di cache per il deploy in produzione.
 * Eseguire dopo ogni deploy: php deploy-cache.php
 *
 * Ottimizza le prestazioni eseguendo config:cache, route:cache e view:cache.
 */

$commands = [
    'config:cache',
    'route:cache',
    'view:cache',
];

$baseDir = __DIR__;

foreach ($commands as $cmd) {
    $fullCmd = sprintf('php %s/artisan %s', $baseDir, $cmd);
    echo "Esecuzione: php artisan {$cmd}\n";
    passthru($fullCmd, $code);
    if ($code !== 0) {
        fprintf(STDERR, "Errore durante php artisan %s (exit code %d)\n", $cmd, $code);
        exit(1);
    }
}

echo "\nCache artefact completata con successo.\n";

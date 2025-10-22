<?php
// public/run-queue.php

// Change directory to Laravel root (one level up from public)
chdir(__DIR__ . '../');

// Optional: Increase limits
ini_set('max_execution_time', 0);
set_time_limit(0);
ignore_user_abort(true);

// Run the artisan queue:work command in the background
$output = shell_exec('php artisan queue:work --stop-when-empty > storage/logs/queue-worker.log 2>&1 &');

echo json_encode([
    "status" => "started",
    "message" => "Queue worker initiated.",
    "log" => "storage/logs/queue-worker.log",
    "output" => $output
], JSON_PRETTY_PRINT);

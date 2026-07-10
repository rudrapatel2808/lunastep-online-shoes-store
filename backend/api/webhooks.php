<?php
// backend/api/webhooks.php — n8n Webhook Helper
// Fire-and-forget POST to n8n webhook URL

include_once __DIR__ . '/../config/database.php';

// Set your n8n webhook base URL here (or leave empty to disable)
define('N8N_WEBHOOK_URL', '');

function triggerWebhook($event, $payload = []) {
    if (empty(N8N_WEBHOOK_URL)) return; // disabled
    $url = rtrim(N8N_WEBHOOK_URL, '/') . '/' . $event;
    $data = json_encode(array_merge(['event' => $event, 'timestamp' => date('c')], $payload));
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 3, // don't block the main request
        CURLOPT_CONNECTTIMEOUT => 2,
    ]);
    curl_exec($ch);
    curl_close($ch);
}
?>

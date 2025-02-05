<?php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Singapore');

function isSupportAvailable() {
    $current_time = new DateTime();
    $current_hour = (int)$current_time->format('G');
    $current_minute = (int)$current_time->format('i');
    $current_day = (int)$current_time->format('N');
    
    $is_weekday = ($current_day >= 1 && $current_day <= 5);
    $total_minutes = ($current_hour * 60) + $current_minute;
    
    $start_time = 9 * 60; 
    $end_time = 17 * 60; 
    $closing_warning = $end_time - 30;
    
    if ($is_weekday) {
        if ($total_minutes >= $start_time && $total_minutes < $closing_warning) {
            return 'open';
        } elseif ($total_minutes >= $closing_warning && $total_minutes < $end_time) {
            return 'closing_soon';
        }
    }
    return 'closed';
}

$status = [
    'status' => isSupportAvailable(),
    'timestamp' => (new DateTime())->format('Y-m-d H:i:s')
];

echo json_encode($status);
<?php
function esc(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function calculateExpiry(string $expiryDate): array {
    $now = new DateTime('today');
    $expiry = new DateTime($expiryDate);
    $diff = $now->diff($expiry);
    $days = (int)$diff->format('%r%a');
    
    if ($days < 0) {
        $absDays = abs($days);
        $text = ($absDays === 1) ? "Expired Yesterday" : "Expired $absDays Days Ago";
        return [
            'status' => 'expired',
            'class' => 'text-danger',
            'badge_class' => 'bg-danger bg-opacity-10 text-danger border border-danger-subtle',
            'icon' => 'bi-x-circle-fill',
            'text' => $text,
            'days' => $days
        ];
    } elseif ($days === 0) {
        return [
            'status' => 'today',
            'class' => 'text-danger fw-bold',
            'badge_class' => 'bg-danger bg-opacity-10 text-danger border border-danger-subtle fw-bold',
            'icon' => 'bi-exclamation-triangle-fill',
            'text' => 'Expires Today',
            'days' => $days
        ];
    } elseif ($days === 1) {
        return [
            'status' => 'tomorrow',
            'class' => 'text-warning fw-bold',
            'badge_class' => 'bg-warning bg-opacity-10 text-warning border border-warning-subtle fw-bold',
            'icon' => 'bi-exclamation-triangle-fill',
            'text' => 'Expires Tomorrow',
            'days' => $days
        ];
    } elseif ($days <= 3) {
        return [
            'status' => 'urgent',
            'class' => 'text-warning',
            'badge_class' => 'bg-warning bg-opacity-10 text-warning border border-warning-subtle',
            'icon' => 'bi-exclamation-triangle-fill',
            'text' => "Expires in $days Days",
            'days' => $days
        ];
    } elseif ($days <= 7) {
        return [
            'status' => 'near',
            'class' => 'text-info',
            'badge_class' => 'bg-info bg-opacity-10 text-info border border-info-subtle',
            'icon' => 'bi-clock',
            'text' => "Expires in $days Days",
            'days' => $days
        ];
    } else {
        return [
            'status' => 'safe',
            'class' => 'text-success',
            'badge_class' => 'bg-success bg-opacity-10 text-success border border-success-subtle',
            'icon' => 'bi-check-circle-fill',
            'text' => "Expires in $days Days",
            'days' => $days
        ];
    }
}

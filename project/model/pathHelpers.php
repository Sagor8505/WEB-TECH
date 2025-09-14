<?php
// model/pathHelpers.php

if (!function_exists('room_image_url')) {
    function room_image_url(?string $dbPath): string {
        $dbPath = trim($dbPath ?? '');
        if ($dbPath === '') return '';

        // If already a full http(s) URL, keep it
        if (preg_match('/^https?:\/\//i', $dbPath)) {
            return $dbPath;
        }

        // Normalize path (remove wrong prefixes)
        $dbPath = str_replace(['asset/uploads/', 'asset/upload/', 'uploads/rooms/'], '', $dbPath);

        // Build correct absolute URL
        return '/project/uploads/rooms/' . ltrim($dbPath, '/');
    }
}

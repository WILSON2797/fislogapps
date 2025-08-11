<?php
/**
 * File: functions.php
 * Fungsi utilitas untuk website
 */

/**
 * Fungsi untuk cache busting yang otomatis berdasarkan waktu modifikasi file
 */
function asset_url($path) {
    // Jika path dimulai dengan http atau https, gunakan timestamp sebagai versi
    if (strpos($path, 'http') === 0) {
        return $path . '?v=' . time();
    }
    
    // Jika file lokal, gunakan filemtime untuk mendapatkan waktu modifikasi file
    $file_path = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($path, '/');
    $version = file_exists($file_path) ? filemtime($file_path) : time();
    return $path . '?v=' . $version;
}

/**
 * Alternativ implementation dengan static version untuk produksi
 * Ubah versi secara manual saat melakukan update
 */
function asset_url_static($path) {
    $version = "1.0.1"; // Ubah versi ini setiap kali ada update
    return $path . '?v=' . $version;
}
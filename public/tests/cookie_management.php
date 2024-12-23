<?php
/**
 * Menetapkan cookie dengan nama, nilai, dan durasi yang diberikan.
 *
 * @param string $name Nama cookie
 * @param string $value Nilai cookie
 * @param int $duration Waktu berlaku cookie dalam detik (default: 3600 detik = 1 jam)
 */
function set_cookie($name, $value, $duration = 3600) {
    // Durasi dalam detik. time() + durasi.
    $expiry = time() + $duration;
    setcookie($name, $value, $expiry, "/"); // Path "/" agar cookie berlaku di seluruh domain
}

/**
 * Mendapatkan nilai dari cookie dengan nama yang diberikan.
 *
 * @param string $name Nama cookie
 * @return string|null Nilai cookie atau null jika tidak ditemukan
 */
function get_cookie($name) {
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
}

/**
 * Menghapus cookie dengan nama yang diberikan.
 *
 * @param string $name Nama cookie
 */
function delete_cookie($name) {
    // Set waktu kedaluwarsa ke waktu lampau untuk menghapus cookie
    setcookie($name, "", time() - 3600, "/");
}
?>

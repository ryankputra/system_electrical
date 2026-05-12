# webTa

Proyek ini adalah aplikasi manajemen stok berbasis CodeIgniter 3 (fork dari `electrical-system`).

Tujuan repo: `webTa` — web admin sederhana untuk manajemen stok electrical.

Instruksi singkat setup:

- Pastikan PHP (7.x/8.x) dan Composer terpasang.
- Copy/atur konfigurasi database di `application/config/database.php`.
- Jalankan `composer install` jika ada dependency.
- Pastikan `base_url` di `application/config/config.php` sesuai.
- Berikan permission write ke `application/logs` dan `application/cache` jika diperlukan.

Menjalankan lokal (XAMPP):

1. Letakkan folder di `htdocs` (sudah ada).
2. Akses lewat browser: `http://localhost/electrical-system/` atau sesuai `base_url`.

Membuat repository GitHub (opsional via CLI):

- Saya akan mencoba membuat repo `webTa` dengan `gh repo create webTa --public --source=. --push`.
- Jika tidak tersedia, Anda bisa membuat repo di https://github.com/new lalu jalankan:

```bash
git remote add origin https://github.com/<username>/webTa.git
git push -u origin main
```

Catatan:
- File lisensi asli tersimpan di `license.txt`.
- Jika Anda ingin saya push ke akun GitHub Anda, pastikan `gh` CLI sudah terpasang dan sudah login, atau berikan remote URL.


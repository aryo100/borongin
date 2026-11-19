# Borongin v1

Identitas publik dan panel admin menggunakan Koperasi JSIT. Foto logo berasal dari aset JSIT pada aplikasi sebelumnya. Banner anak sekolah merupakan ilustrasi yang mengacu pada motif batik produk contoh.

Dependensi PHP disalin ke volume Linux Docker berdasarkan `composer.lock` ketika `start` atau `test` dijalankan. Ini menghindari pembacaan ribuan file vendor melalui bind-mount Windows pada setiap permintaan. Kode aplikasi tetap memakai folder proyek. Jalankan `start` setelah mengubah dependensi agar volume disinkronkan. Indikator loading tersedia untuk navigasi, formulir, dan pencarian langsung.

Storefront koperasi dan panel AdminLTE. Laravel 13, PHP 8.5, Livewire 4 dan PostgreSQL 17.

## Menjalankan di Windows

Nyalakan Docker Desktop dan buka PowerShell di folder proyek.

```powershell
.\borongin.cmd start
.\borongin.cmd status
.\borongin.cmd stop
```

Buka [toko](http://127.0.0.1:8090/), [admin](http://127.0.0.1:8090/admin) dan [email uji Mailpit](http://127.0.0.1:8025/). Start pertama menyiapkan dependensi, aset, kunci aplikasi dan migrasi. Apabila `DB_URL` kosong dalam mode lokal, launcher memakai PostgreSQL demo dan mengisi 24 produk contoh. Volume demo tetap tersimpan saat stop/start. Seed dapat diulang dengan `.\borongin.cmd seed`.

Buat administrator pertama melalui `.\borongin.cmd admin admin@example.test`. Masukkan kata sandi saat diminta. Setelah masuk, buka pengaturan keamanan, konfirmasi kata sandi, pindai kode QR dengan aplikasi autentikator, lalu keluar dan masuk lagi dengan kode autentikator untuk membuka panel. Email dan rekening seed adalah contoh. Gunakan alamat email pengujian ketika mencoba checkout lokal.

## PostgreSQL yang dikelola sendiri

Salin `.env.example` ke `.env` jika belum ada. Isi `DB_CONNECTION=pgsql` dan `DB_URL=postgresql://USER:PASSWORD@HOST:5432/DB`, dengan karakter khusus pada username dan password di-percent-encode. Host PostgreSQL yang berada di Windows dari container adalah `host.docker.internal`. Gunakan database khusus Borongin. Saat URL diisi, launcher memigrasikan schema tanpa menjalankan demo seeder. URL pengujian berasal dari service `test-db` terpisah.

Untuk produksi, isi APP_URL HTTPS, gunakan `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, SMTP tepercaya dan verifikasi TLS PostgreSQL sesuai CA provider. Jalankan migrasi dengan akun deploy yang berhak mengubah schema lalu gunakan akun aplikasi dengan hak minimum. Bind-mount dan port loopback pada Compose ditujukan untuk pengembangan lokal. Jalankan audit paket terkunci secara rutin dan perbarui patch yang relevan.

## Verifikasi

```powershell
.\borongin.cmd test
docker compose run --rm app php artisan route:list
docker compose run --rm app composer audit --locked
```

Browser lokal menguji katalog, checkout, tautan email tamu, bukti pembayaran privat, invoice, serta rekap admin. Konfirmasi dan pengiriman memakai transfer dan tarif manual. Peta desain dan sumber visual tercatat di [DESIGN.md](DESIGN.md).

Tes menggunakan service `test-db` terpisah. TestCase menghentikan tes sebelum migrasi jika URL koneksinya mengarah ke database lain. Akun sekolah contoh memakai kata sandi acak. Untuk mencoba akun sendiri, daftar melalui toko dan verifikasi email dari Mailpit, kemudian ajukan keanggotaan melalui profil.

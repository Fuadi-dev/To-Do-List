# To-Do List

## Deskripsi Aplikasi

**To-Do List** adalah aplikasi manajemen tugas yang memungkinkan pengguna untuk mencatat, mengorganisir, dan melacak tugas-tugas harian mereka. Aplikasi ini dirancang untuk memudahkan pengguna dalam mengelola aktivitas sehari-hari dengan fitur-fitur seperti pembuatan daftar tugas, penandaan tugas selesai, pengelompokan tugas, dan pengingat.

### Bagian-bagian Aplikasi
- **Manajemen Tugas**: Tambah, edit, hapus, dan tandai tugas sebagai selesai.
- **Kategori/Group**: Pengelompokan tugas berdasarkan kategori.
- **Pengingat**: Notifikasi untuk tugas yang mendekati deadline.
- **Dashboard**: Ringkasan tugas aktif dan statistik penyelesaian.

## Halaman

1. **Halaman Login/Registrasi**  
   Autentikasi pengguna untuk mengakses fitur aplikasi.
2. **Halaman Dashboard**  
   Menampilkan ringkasan tugas dan statistik harian.
3. **Halaman Daftar Tugas**  
   List semua tugas dengan opsi filter dan pencarian.
4. **Halaman Detail Tugas**  
   Menampilkan detail dari setiap tugas, deadline, dan catatan tambahan.
5. **Halaman Kelola Kategori**  
   Mengelola kategori atau grup tugas.

## Database yang Digunakan

- **MySQL**: Sebagai basis data utama untuk menyimpan data pengguna, tugas, dan kategori.

## API

- **RESTful API**:  
  API digunakan untuk pengelolaan data tugas, kategori, dan autentikasi pengguna.  
  Endpoint utama:
  - `/api/tasks` untuk operasi CRUD tugas
  - `/api/categories` untuk kategori
  - `/api/auth` untuk autentikasi pengguna

## Software yang Digunakan

- **Backend**: PHP (Laravel)
- **Frontend**: Blade (Laravel Blade template), Dart (Flutter untuk versi mobile)
- **Database**: MySQL
- **Lainnya**: CMake, C++ (untuk kebutuhan build tools atau integrasi), Swift (jika ada dukungan iOS)

## Cara Instalasi

1. **Clone Repo**
    ```bash
    git clone https://github.com/Fuadi-dev/To-Do-List.git
    cd To-Do-List
    ```
2. **Install Dependencies**
    - Untuk Laravel (Backend):
        ```bash
        cd backend
        composer install
        cp .env.example .env
        php artisan key:generate
        ```
    - Untuk Flutter (Mobile):
        ```bash
        cd frontend
        flutter pub get
        ```

3. **Konfigurasi Database**
    - Ubah file `.env` dan sesuaikan konfigurasi database MySQL Anda.

4. **Migrasi Database**
    ```bash
    php artisan migrate
    ```

5. **Jalankan Server**
    ```bash
    php artisan serve
    ```

## Cara Menjalankan

- **Web**:  
  Jalankan perintah `php artisan serve`, lalu akses aplikasi di `http://localhost:8000`.
- **Mobile (Flutter)**:  
  Pastikan sudah menginstal Flutter SDK, lalu jalankan perintah berikut di folder `mobile`:
    ```bash
    flutter run
    ```

## Demo

**Video demo penggunaan aplikasi:**  
<div align="center">
  <video width="700" controls>
    <source src="https://github.com/Fuadi-dev/To-Do-List/releases/tag/v1.0/demo.mp4" type="video/mp4">
    <p>Browser Anda tidak mendukung video HTML5. <a href="https://github.com/Fuadi-dev/To-Do-List/releases/download/v1.0/demo.mp4">Download video</a></p>
  </video>
</div>

## Identitas Pembuat

- **Nama**: Daiyan Nur Fuadi
- **GitHub**: [Fuadi-dev](https://github.com/Fuadi-dev)
- **Email**: [daiyannurfuadi@gmail.com]

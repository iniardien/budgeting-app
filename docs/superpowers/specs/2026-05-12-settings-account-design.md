# Settings Account Design

## Goal

Mengubah halaman `Settings` dari tampilan placeholder menjadi halaman pengaturan akun yang nyata untuk user login, dengan dua panel terpisah: update profil dan ubah password.

## Scope

Dalam pengerjaan ini:

- `Settings` berubah dari halaman statis menjadi halaman berbasis controller.
- User dapat memperbarui `name` dan `email`.
- User dapat mengubah password dengan verifikasi `current_password`.

Di luar scope pengerjaan ini:

- Tidak ada fitur export data.
- Tidak ada fitur clear data.
- Tidak ada preferensi tambahan seperti currency atau theme.
- Tidak ada manajemen akun selain profil dasar dan password.

## Existing Context

- Auth login/register/logout sudah aktif.
- User model saat ini hanya memiliki field inti: `name`, `email`, `password`.
- Halaman `Settings` saat ini masih menampilkan input readonly dan action placeholder.

## Proposed Approach

Gunakan satu `SettingsController` dengan satu halaman index dan dua aksi update terpisah:

- `index` untuk menampilkan halaman settings
- `updateProfile` untuk update `name` dan `email`
- `updatePassword` untuk update password

Pendekatan ini dipilih karena:

- validasi profil dan password tidak saling bercampur
- flash message bisa lebih spesifik
- perilaku form lebih mudah diuji dan dipelihara

## Route Design

Tambahkan route berikut di bawah middleware `auth`:

- `GET /settings`
- `PUT /settings/profile`
- `PUT /settings/password`

## Controller Behavior

### SettingsController@index

- Mengirim data user login ke view.
- Menampilkan dua panel form pada halaman yang sama.

### SettingsController@updateProfile

- Memvalidasi `name` dan `email`.
- Mengubah data user login tanpa menerima ID user dari request.
- Menyimpan perubahan dan redirect kembali ke settings dengan flash message sukses.

### SettingsController@updatePassword

- Memvalidasi `current_password`, `password`, dan `password_confirmation`.
- Mengubah password user login jika password saat ini benar.
- Menjaga sesi user tetap aktif setelah password berubah.
- Redirect kembali ke settings dengan flash message sukses.

## Validation Rules

### Profile Update

- `name`: required, string, max `255`
- `email`: required, email valid, max `255`, unique pada tabel `users` kecuali user login sendiri

### Password Update

- `current_password`: required, harus cocok dengan password user saat ini
- `password`: required, string, min `8`, confirmed

## UI Behavior

Halaman settings mempertahankan layout umum yang ada, tetapi panel account settings diubah menjadi dua panel nyata:

### Panel Profile

- field `name`
- field `email`
- tombol submit khusus profile

### Panel Password

- field `current_password`
- field `password`
- field `password_confirmation`
- tombol submit khusus password

## Flash Message Behavior

Gunakan pesan sukses yang spesifik:

- `Profil berhasil diperbarui.`
- `Password berhasil diperbarui.`

Pesan ini membantu user memahami panel mana yang berhasil diproses.

## Validation Error Behavior

- Error profil hanya ditampilkan pada panel profil.
- Error password hanya ditampilkan pada panel password.
- Nilai lama pada form profil harus dipertahankan saat validasi gagal.
- Field password tidak perlu diisi ulang otomatis setelah gagal.

## Data Management Panel

Panel `Data Management` tetap boleh tampil untuk saat ini, tetapi harus jelas diberi label sebagai fitur yang belum tersedia agar tidak menyesatkan user.

## Authorization And Safety

- Semua operasi settings hanya berlaku untuk user login.
- Tidak ada parameter user ID dari request.
- Update email harus aman terhadap konflik dengan email milik user lain.
- Update password tidak boleh berhasil jika `current_password` salah.

## Testing Strategy

Gunakan feature test sebagai coverage utama.

Test utama:

- halaman settings menampilkan data user login
- user bisa memperbarui `name` dan `email`
- email yang sudah dipakai user lain ditolak
- user bisa memperbarui password dengan `current_password` yang benar
- password tidak berubah jika `current_password` salah

## Implementation Order

1. Tulis feature test settings yang gagal dulu.
2. Tambahkan `SettingsController`.
3. Ubah route `/settings` ke controller dan tambah route update profile/password.
4. Sambungkan Blade settings ke form nyata.
5. Jalankan test settings.
6. Jalankan verifikasi suite terkait.

## Risks And Mitigations

- Risiko validasi dua form saling bertabrakan.
  Mitigasi: gunakan action terpisah untuk profil dan password.
- Risiko password berubah tanpa verifikasi yang kuat.
  Mitigasi: wajibkan `current_password`.
- Risiko user bingung dengan panel data management yang belum aktif.
  Mitigasi: tampilkan copy yang tegas bahwa fitur itu belum tersedia.

## Success Criteria

- Halaman `Settings` tidak lagi sekadar readonly placeholder.
- User bisa update `name` dan `email`.
- User bisa update password dengan verifikasi password lama.
- Error validasi dan flash message tampil jelas pada panel yang tepat.

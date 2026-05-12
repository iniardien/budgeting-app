# Reports Monthly Data Design

## Goal

Mengubah halaman `Reports` yang masih memakai data dummy menjadi halaman laporan bulanan berbasis data nyata, dengan filter `month` dan `year` untuk user yang sedang login.

## Scope

Dalam pengerjaan ini:

- `Reports` berubah dari halaman statis menjadi halaman report berbasis controller.
- Halaman report mendukung filter bulan dan tahun lewat query string.
- Semua ringkasan dan breakdown diambil dari transaksi nyata milik user login pada periode terpilih.

Di luar scope pengerjaan ini:

- Tidak ada ekspor PDF/CSV.
- Tidak ada tren multi-periode otomatis seperti rolling 6 bulan.
- Tidak ada chart interaktif berbasis JavaScript.
- Tidak ada perubahan ke modul `Settings` pada siklus ini.

## Existing Context

- `Dashboard`, `Budgets`, `Categories`, dan `Transactions` sudah memakai data nyata.
- `Reports` masih memakai `Route::view` dan array statis di Blade.
- Data transaksi yang dibutuhkan untuk report sudah tersedia melalui modul transaksi yang baru dihidupkan.

## Proposed Approach

Gunakan pendekatan controller-based yang konsisten dengan modul lain.

- Tambahkan `ReportController@index`.
- Ganti `Route::view('/reports', ...)` menjadi route controller.
- Hitung seluruh agregasi report di controller dan kirim ke Blade.
- Pertahankan struktur visual report yang ada, tetapi ganti konten statis menjadi data nyata.

Pendekatan ini dipilih karena paling konsisten dengan pola app saat ini dan cukup ringan untuk scope report bulanan tahap pertama.

## Route Design

- `GET /reports` diarahkan ke `ReportController@index`.

Parameter query:

- `month`
- `year`

Contoh:

- `/reports`
- `/reports?month=5&year=2026`

## Filter Rules

- Jika `month` dan `year` tidak diberikan, gunakan bulan dan tahun berjalan.
- `month` hanya valid untuk nilai `1` sampai `12`.
- `year` hanya valid untuk rentang kecil yang relevan, misalnya sekitar tahun berjalan.
- Jika nilai filter tidak valid, controller akan fallback ke bulan dan tahun berjalan.

## Data Requirements

Semua data diambil hanya dari transaksi milik user login pada bulan dan tahun terpilih.

### Summary Cards

- `totalIncome`: total transaksi `income`
- `totalExpenses`: total transaksi `expense`
- `netSavings`: `totalIncome - totalExpenses`

### Category Breakdowns

- `incomeByCategory`: total income per kategori
- `expenseByCategory`: total expense per kategori

Setiap breakdown akan diurutkan dari nominal terbesar ke terkecil agar lebih mudah dibaca.

## Controller Behavior

`ReportController@index` bertanggung jawab untuk:

- menentukan `selectedMonth` dan `selectedYear`
- membangun pilihan bulan dan tahun untuk filter
- mengambil transaksi user login pada periode terpilih
- menghitung summary cards
- menghitung breakdown kategori income
- menghitung breakdown kategori expense
- mengirim semua data yang diperlukan ke view

## UI Behavior

Halaman report tetap mempertahankan visual dasar yang ada, dengan perubahan berikut:

- kartu summary menampilkan nominal nyata dalam format rupiah
- bagian atas memiliki form filter bulan dan tahun
- area utama menampilkan dua blok breakdown:
  - breakdown income per kategori
  - breakdown expense per kategori
- setiap kategori memakai progress bar relatif terhadap kategori terbesar di grup yang sama
- label periode menampilkan bulan dan tahun yang sedang aktif

## Empty State Behavior

Jika tidak ada transaksi pada periode terpilih:

- summary cards menampilkan `Rp 0,00`
- breakdown income menampilkan pesan kosong
- breakdown expense menampilkan pesan kosong
- halaman tetap render normal tanpa error atau layout rusak

## Authorization And Data Safety

- Semua query dibatasi ke transaksi milik user login.
- Input filter tidak boleh membuka akses ke data user lain karena filter hanya berbasis periode, bukan user-supplied IDs lintas akun.
- Nilai filter yang tidak valid dinormalkan ke periode default agar halaman tetap aman.

## Testing Strategy

Gunakan feature test sebagai coverage utama.

Test utama:

- report memakai periode default saat query kosong
- report bisa memfilter berdasarkan `month` dan `year`
- summary cards menampilkan total income, total expense, dan net savings yang benar
- breakdown income hanya memuat kategori income pada periode terpilih
- breakdown expense hanya memuat kategori expense pada periode terpilih
- report tidak menampilkan transaksi dari user lain
- empty state muncul saat periode tidak memiliki transaksi

## Implementation Order

1. Tulis feature test report yang gagal dulu.
2. Tambahkan `ReportController@index`.
3. Ubah route `/reports` ke controller.
4. Sambungkan Blade report ke data nyata dan filter.
5. Jalankan test report.
6. Jalankan verifikasi suite terkait.

## Risks And Mitigations

- Risiko filter tidak valid menghasilkan tampilan aneh.
  Mitigasi: normalisasi `month` dan `year` ke default yang aman.
- Risiko report kosong terlihat seperti bug.
  Mitigasi: tampilkan empty state yang jelas dan nominal nol.
- Risiko query report mencampur data user lain.
  Mitigasi: semua query dimulai dari relasi transaksi milik user login.

## Success Criteria

- Halaman `Reports` tidak lagi memakai array dummy.
- Filter `month` dan `year` bekerja.
- Summary cards memakai data nyata dari transaksi user login.
- Breakdown kategori income dan expense memakai data nyata pada periode terpilih.
- Empty state tampil baik saat belum ada data di periode yang dipilih.

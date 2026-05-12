# Transactions And Dashboard Data Design

## Goal

Mengganti halaman `Transactions` dan `Dashboard` yang masih memakai data dummy menjadi halaman berbasis data nyata untuk user yang sedang login, tanpa menambah filter periode pada dashboard di tahap ini.

## Scope

Dalam pengerjaan ini:

- `Transactions` berubah dari halaman statis menjadi modul CRUD yang membaca dan menulis data transaksi user.
- `Dashboard` berubah dari halaman statis menjadi ringkasan read-only yang membaca data transaksi dan budget nyata.
- Seeder demo ditambah agar akun demo langsung memiliki transaksi yang bisa ditampilkan di halaman transaksi dan dashboard.

Di luar scope pengerjaan ini:

- Tidak ada filter bulan/tahun di dashboard.
- Tidak ada ekspor, impor, atau analitik lanjutan untuk reports/settings.
- Tidak ada perubahan besar pada layout visual yang sudah ada.

## Existing Context

- `budgets` dan `categories` sudah memakai pola controller + Blade + validasi request langsung di controller.
- `transactions`, `dashboard`, `reports`, dan `settings` masih memakai `Route::view`.
- Tabel `transactions` sudah tersedia di database, tetapi model, relasi, controller, route, dan seeder-nya belum ada.

## Proposed Approach

Gunakan pendekatan controller-based yang konsisten dengan modul yang sudah ada.

- Tambahkan `Transaction` model beserta relasinya.
- Tambahkan `TransactionController` untuk list, create, store, edit, update, dan destroy.
- Tambahkan `DashboardController@index` untuk menyiapkan seluruh ringkasan dashboard.
- Ganti `Route::view` untuk `transactions` dan `dashboard` menjadi route controller.

Pendekatan ini dipilih karena paling dekat dengan pola app saat ini, cepat diimplementasikan, dan tidak menambah abstraksi prematur seperti service layer.

## Data Model

### Transaction

Field yang dipakai:

- `user_id`
- `category_id`
- `amount`
- `type`
- `date`
- `description`

### Relationships

- `User` memiliki `transactions()`.
- `Category` memiliki `transactions()`.
- `Transaction` milik `user()` dan `category()`.

## Transactions Module Design

### Routes

Tambahkan resource route untuk transaksi, minimal:

- `GET /transactions`
- `GET /transactions/create`
- `POST /transactions`
- `GET /transactions/{transaction}/edit`
- `PUT/PATCH /transactions/{transaction}`
- `DELETE /transactions/{transaction}`

### Controller Behavior

`TransactionController@index`

- Mengambil transaksi milik user login.
- Memuat relasi kategori.
- Mendukung filter GET untuk `type` dan `category_id`.
- Mengurutkan transaksi dari tanggal terbaru, lalu data terbaru.

`TransactionController@create`

- Mengirim daftar kategori milik user ke form.
- Menyediakan pilihan tipe `income` dan `expense`.

`TransactionController@store`

- Memvalidasi input.
- Memastikan kategori milik user login.
- Memastikan `type` transaksi sama dengan `type` kategori yang dipilih.
- Menyimpan transaksi baru dan redirect kembali ke index dengan flash message.

`TransactionController@edit`

- Memastikan transaksi milik user login.
- Memuat data transaksi dan daftar kategori user.

`TransactionController@update`

- Validasi sama dengan store.
- Hanya boleh mengubah transaksi milik user login.

`TransactionController@destroy`

- Hanya boleh menghapus transaksi milik user login.

### Validation Rules

- `category_id`: wajib, ada, dan milik user login.
- `type`: wajib, hanya `income` atau `expense`.
- `type` harus sama dengan tipe kategori yang dipilih.
- `amount`: wajib numeric dan lebih dari `0`.
- `date`: wajib dan valid.
- `description`: nullable string.

### UI Behavior

Halaman index tetap mengikuti layout tabel yang sudah ada, dengan perubahan berikut:

- Data tabel berasal dari query database, bukan array dummy.
- Tombol `Add Transaction` mengarah ke halaman create.
- Kolom action berisi tombol edit dan delete nyata.
- Filter `type` dan `category` dikirim sebagai query string GET.
- Tombol reset menghapus filter dan kembali ke index bersih.
- Jika belum ada transaksi, tampilkan empty state yang jelas.

Form create/edit mengikuti pola UI modul lain:

- Memakai struktur field yang konsisten dengan `budgets` dan `categories`.
- Menampilkan error validasi per field.
- Menyimpan nilai lama saat validasi gagal.

## Dashboard Module Design

### Route

- `GET /` diarahkan ke `DashboardController@index`.

### Controller Behavior

Dashboard menghitung data untuk user login:

- `totalBalance`: total income dikurangi total expense dari seluruh transaksi user.
- `totalIncome`: total transaksi bertipe income.
- `totalExpenses`: total transaksi bertipe expense.
- `expenseByCategory`: total pengeluaran per kategori untuk bulan berjalan.
- `budgetUsage`: daftar budget bulan berjalan beserta total pengeluaran aktual per kategori budget.

### Period Rules

Untuk tahap ini:

- Kartu ringkasan memakai seluruh transaksi user.
- Bagian `Expenses by Category` memakai transaksi expense pada bulan berjalan.
- Bagian `Budget Usage` memakai budget bulan berjalan dan transaksi expense pada bulan berjalan.

### UI Behavior

View dashboard tetap mempertahankan struktur visual yang ada, tetapi:

- Semua angka berasal dari data nyata.
- Nama user tetap memakai user login.
- Label periode memakai bulan berjalan.
- Jika tidak ada data pengeluaran, panel kategori menampilkan empty state.
- Jika tidak ada budget aktif, panel budget usage menampilkan empty state.

## Authorization And Error Handling

- Semua query transaksi dibatasi pada user login.
- Semua operasi edit/update/delete transaksi harus mengembalikan `403` jika data bukan milik user.
- Filter kategori hanya memakai daftar kategori milik user, sehingga input filter asing tidak menghasilkan akses lintas user.
- Dashboard selalu merender dengan aman walau data nol.

## Seeder Changes

Seeder demo akan ditambah transaksi untuk akun `demo@budgeting-app.test`.

Data seed minimal harus mencakup:

- beberapa transaksi income
- beberapa transaksi expense
- kategori yang sudah ada
- transaksi pada bulan berjalan agar dashboard langsung berisi

## Testing Strategy

Gunakan feature test sebagai coverage utama.

### Transaction tests

- user bisa melihat daftar transaksi miliknya
- user bisa filter transaksi berdasarkan type
- user bisa filter transaksi berdasarkan kategori miliknya
- user bisa membuat transaksi valid
- user tidak bisa memakai kategori milik user lain
- user tidak bisa menyimpan type yang tidak sesuai kategori
- user bisa mengubah transaksi miliknya
- user bisa menghapus transaksi miliknya
- user tidak bisa mengedit atau menghapus transaksi user lain

### Dashboard tests

- dashboard menampilkan total income, total expense, dan balance yang sesuai
- dashboard menampilkan agregasi pengeluaran per kategori bulan berjalan
- dashboard menampilkan progress budget berdasarkan transaksi expense bulan berjalan
- dashboard tetap tampil saat tidak ada transaksi atau budget

## Implementation Order

1. Tambah `Transaction` model dan relasi di model terkait.
2. Tulis feature test transaksi.
3. Implementasikan route, controller, dan view transaksi sampai test hijau.
4. Tulis feature test dashboard.
5. Implementasikan `DashboardController` dan ubah dashboard view ke data nyata.
6. Tambahkan seed transaksi demo.
7. Jalankan verifikasi test yang relevan.

## Risks And Mitigations

- Risiko inkonsistensi `type` transaksi dan kategori.
  Mitigasi: validasi eksplisit bahwa keduanya harus sama.
- Risiko dashboard kosong terlihat rusak.
  Mitigasi: empty state yang jelas dan nilai default `0`.
- Risiko akses data lintas user.
  Mitigasi: semua query dan binding diverifikasi terhadap user login.

## Success Criteria

- Halaman `Transactions` tidak lagi memakai array dummy.
- User dapat CRUD transaksi miliknya sendiri.
- Filter transaksi bekerja.
- Dashboard tidak lagi memakai angka dummy.
- Dashboard menampilkan ringkasan nyata dari data transaksi dan budget.
- Akun demo memiliki data transaksi untuk dicoba segera setelah seeding.

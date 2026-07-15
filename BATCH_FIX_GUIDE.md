# Panduan Perbaikan: Batch Masih Ada Stock Tapi Dianggap Habis

## Masalah

Batch #2 menunjukkan Sisa: 0 (HABIS) padahal sebenarnya masih ada 8 unit dalam stock. Ini terjadi karena sistem tidak memiliki cara yang reliable untuk melacak item mana yang benar-benar keluar dari batch mana.

## Root Cause

Tabel `as_history` tidak memiliki foreign key yang menghubungkan transaksi "Keluar" ke transaksi "Masuk" (batch) yang spesifik. Sistem saat ini menggunakan parsing teks dari kolom `keterangan` untuk mencoba mendeteksi batch number, yang sangat rentan terhadap kesalahan:

1. Jika `keterangan` tidak berisi "Batch #2" dengan format yang tepat, sistem tidak bisa menemukan batch yang dimaksud
2. Jika multiple `Keluar` transactions semuanya mereferensi "Batch #2" dalam keterangan mereka, semua akan dijumlahkan bersama, bahkan jika beberapa seharusnya tidak dari batch tersebut

## Solusi

### Step 1: Backup Database Anda
```sql
-- Backup table sebelum melakukan perubahan
CREATE TABLE as_history_backup AS SELECT * FROM as_history;
```

### Step 2: Jalankan Migration Script
File: `database/sql/migrate_add_from_batch_id.sql`

Script ini akan:
1. Menambahkan kolom `from_batch_id` ke tabel `as_history` jika belum ada
2. Mencoba mengisi `from_batch_id` dengan referensi batch yang benar berdasarkan FIFO logic
3. Menampilkan records yang tidak bisa di-resolve otomatis (perlu pengecekan manual)

**Cara menjalankan:**
- Buka MySQL/MariaDB console
- Gunakan database Anda: `USE nama_database;`
- Paste seluruh isi file SQL dan jalankan

### Step 3: Verifikasi Data Setelah Migration
Jalankan query ini untuk melihat records yang mungkin masih bermasalah:
```sql
SELECT 
    h.id,
    h.electric_id,
    h.type,
    h.qty,
    h.keterangan,
    h.from_batch_id,
    h.date
FROM `as_history` h
WHERE h.type = 'Keluar' 
AND (h.from_batch_id IS NULL OR h.from_batch_id = 0)
ORDER BY h.date ASC;
```

Jika ada records di atas, mereka perlu dikurasi manual:
- Tentukan batch mana yang seharusnya menjadi source
- Update `from_batch_id` dengan ID yang benar

### Step 4: Recalculate Batch Totals

Setelah `from_batch_id` sudah terisi dengan benar, sistem akan otomatis menghitung ulang berdasarkan data baru. Refresh halaman atau buat transaksi baru untuk melihat pembaruan.

## Code Changes (Already Applied)

### File: `application/models/History_model.php`
- Diupdate fungsi `recalculateSisaBatchInPlace()` untuk mengutamakan `from_batch_id` column
- Diupdate fungsi `get_all_history()` untuk menggunakan `from_batch_id` jika tersedia
- Diupdate fungsi `addTransaction()` untuk auto-populate `from_batch_id` saat insert Keluar baru

### File: `database/sql/create_as_history.sql`
- Ditambahkan kolom `from_batch_id` pada template table creation

## Batch Assignment Priority (Setelah Update)

1. **Explicit (dari_batch_id)**: Jika dari_batch_id sudah diisi, gunakan itu
2. **Legacy (keterangan)**: Jika tidak ada dari_batch_id, coba extract dari keterangan (untuk backward compatibility)
3. **FIFO Fallback**: Jika keduanya tidak bisa digunakan, assign ke batch tertua yang masih punya stock

## FAQ

**Q: Apakah perubahan ini akan mempengaruhi data yang sudah ada?**
A: Tidak akan menghapus atau mengubah data yang ada. Hanya menambah kolom baru dan mencoba populate berdasarkan data yang sudah ada.

**Q: Apa yang harus dilakukan jika masih ada data yang salah setelah migration?**
A: Bisa manually update `from_batch_id` untuk records yang salah, atau hubungi admin untuk investigasi lebih lanjut.

**Q: Apakah saya perlu re-entry data lama?**
A: Tidak perlu. Migration script akan mencoba auto-populate. Jika ada yang gagal, hanya itu saja yang perlu di-review.

**Q: Bagaimana pencegahan masalah ini di masa depan?**
A: Mulai dari sekarang, setiap Keluar transaction akan otomatis menyimpan from_batch_id, sehingga tidak ada ambiguitas lagi.

## Testing

Setelah menjalankan semua steps:

1. Buka halaman History: `localhost/electrical-system/index.php/history`
2. Lihat detail Batch #2 - seharusnya sekarang menunjukkan sisa yang benar (8, bukan 0)
3. Test membuat transaksi Keluar baru - lihat apakah batch reference sudah benar

## Pertanyaan atau Masalah?

Jika ada error saat menjalankan migration script:
1. Check error message
2. Pastikan file `/database/sql/migrate_add_from_batch_id.sql` ada
3. Verify kolom `from_batch_id` sudah ada: `DESCRIBE as_history;`
4. Cek apakah data migration berhasil: 
   ```sql
   SELECT COUNT(*) as total_keluar, 
          COUNT(CASE WHEN from_batch_id IS NOT NULL THEN 1 END) as with_batch_ref
   FROM as_history 
   WHERE type = 'Keluar';
   ```

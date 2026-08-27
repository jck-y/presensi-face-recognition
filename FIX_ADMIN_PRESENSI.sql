-- ============================================
-- 1. Cek data admin & face enrollment
-- ============================================
SELECT k.id, k.nama_karyawan, k.email, k.role,
       CASE WHEN w.karyawan_id IS NOT NULL THEN 'SUDAH DAFTAR' ELSE 'BELUM DAFTAR' END AS status_wajah
FROM karyawans k
LEFT JOIN wajah_karyawan w ON w.karyawan_id = k.id
WHERE k.role = 'admin';

-- ============================================
-- 2. Cek absensi hari ini untuk admin
-- ============================================
SELECT a.*, k.nama_karyawan
FROM absensis a
JOIN karyawans k ON k.id = a.karyawan_id
WHERE k.role = 'admin'
  AND a.tanggal = CURRENT_DATE;

-- ============================================
-- 3. Hapus record absensi test lama admin hari ini (jika ada)
--    Ganti ID di bawah dengan ID admin yang benar dari query #1
-- ============================================
-- DELETE FROM absensis WHERE karyawan_id = <ID_ADMIN> AND tanggal = CURRENT_DATE;

-- ============================================
-- 4. Tambah value 'hadir' dan 'tidak_hadir' ke enum
-- ============================================
ALTER TYPE enum_absensis_status_absensi ADD VALUE IF NOT EXISTS 'hadir';
ALTER TYPE enum_absensis_status_absensi ADD VALUE IF NOT EXISTS 'tidak_hadir';

-- ============================================
-- 5. Jika enum belum punya nama type, cek dulu:
-- ============================================
-- SELECT typname, enumlabel FROM pg_enum WHERE enumtypid = (
--   SELECT oid FROM pg_type WHERE typname = 'enum_absensis_status_absensi'
-- );

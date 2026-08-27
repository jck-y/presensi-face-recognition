-- Tambah value 'hadir' dan 'tidak_hadir' ke enum status_absensi
-- Jalankan ini di Supabase SQL Editor

-- Cek nama enum type dulu (kalau belum tahu):
SELECT t.typname, e.enumlabel
FROM pg_type t
JOIN pg_enum e ON e.enumtypid = t.oid
WHERE t.typname LIKE '%absensi%'
ORDER BY e.enumsortorder;

-- Tambah 'hadir' ke enum (PostgreSQL 9.3+)
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_enum
        JOIN pg_type ON pg_enum.enumtypid = pg_type.oid
        WHERE pg_type.typname = 'enum_absensis_status_absensi'
          AND pg_enum.enumlabel = 'hadir'
    ) THEN
        ALTER TYPE enum_absensis_status_absensi ADD VALUE 'hadir';
    END IF;
END
$$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_enum
        JOIN pg_type ON pg_enum.enumtypid = pg_type.oid
        WHERE pg_type.typname = 'enum_absensis_status_absensi'
          AND pg_enum.enumlabel = 'tidak_hadir'
    ) THEN
        ALTER TYPE enum_absensis_status_absensi ADD VALUE 'tidak_hadir';
    END IF;
END
$$;

-- Hapus record test lama admin hari ini (ganti ID_ADMIN dengan ID yang benar)
-- Cek dulu:
SELECT a.id, a.karyawan_id, k.nama_karyawan, a.status_absensi, a.tanggal
FROM absensis a
JOIN karyawans k ON k.id = a.karyawan_id
WHERE k.role = 'admin' AND a.tanggal = CURRENT_DATE;

-- Kalau ada, hapus:
-- DELETE FROM absensis WHERE id = <ID_RECORD>;

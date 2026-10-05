CREATE TABLE pengguna (
    pengguna_id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('pj_lab','petugas','koordinator') NOT NULL
) ENGINE=InnoDB;

CREATE TABLE ruangan (
    ruangan_id INT AUTO_INCREMENT PRIMARY KEY,
    kode_ruangan VARCHAR(20) NOT NULL UNIQUE,
    nama_ruangan VARCHAR(100) NOT NULL,
    jenis_ruangan ENUM('lab_biologi','lab_kimia','lab_fisika','kelas','gudang','lainnya') NOT NULL,
    lantai SMALLINT NOT NULL,
    pj_id INT NULL,
    petugas_id INT NULL,
    CONSTRAINT fk_ruangan_pj
        FOREIGN KEY (pj_id) REFERENCES pengguna(pengguna_id),
    CONSTRAINT fk_ruangan_petugas
        FOREIGN KEY (petugas_id) REFERENCES pengguna(pengguna_id),
    CONSTRAINT ck_ruangan_penanggung CHECK ((jenis_ruangan IN ('lab_biologi','lab_kimia','lab_fisika') AND pj_id IS NOT NULL AND petugas_id IS NULL) OR (jenis_ruangan IN ('kelas','gudang','lainnya') AND pj_id IS NULL AND petugas_id IS NOT NULL))
) ENGINE=InnoDB;

CREATE TABLE inventaris (
    inventaris_id INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    kategori VARCHAR(30) NOT NULL,
    spesifikasi TEXT NULL,
    nomor_seri VARCHAR(100) NULL,
    ruangan_id INT NOT NULL,
    kondisi ENUM('baik','rusak_ringan','rusak_berat','belum_diperiksa') NOT NULL DEFAULT 'belum_diperiksa',
    status_penggunaan ENUM('tersedia','digunakan','tidak_digunakan') NOT NULL DEFAULT 'tidak_digunakan',
    CONSTRAINT fk_inventaris_ruangan
        FOREIGN KEY (ruangan_id) REFERENCES ruangan(ruangan_id)
) ENGINE=InnoDB;

CREATE TABLE laporan_kerusakan (
    laporan_id INT AUTO_INCREMENT PRIMARY KEY,
    pelapor_id INT NOT NULL,
    inventaris_id INT NOT NULL,
    ruangan_id INT NOT NULL COMMENT 'Lokasi barang saat laporan dibuat',
    tanggal_laporan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    kerusakan TEXT NOT NULL,
    prioritas ENUM('tinggi','rendah') NULL,
    status_laporan ENUM('masuk','diperiksa','disetujui','ditangani','selesai','dihentikan') NOT NULL DEFAULT 'masuk',
    tanggal_ditutup DATETIME NULL,
    alasan_penghentian TEXT NULL,
    CONSTRAINT fk_laporan_pelapor
        FOREIGN KEY (pelapor_id) REFERENCES pengguna(pengguna_id),
    CONSTRAINT fk_laporan_inventaris
        FOREIGN KEY (inventaris_id) REFERENCES inventaris(inventaris_id),
    CONSTRAINT fk_laporan_ruangan
        FOREIGN KEY (ruangan_id) REFERENCES ruangan(ruangan_id)
) ENGINE=InnoDB;

CREATE TABLE pemeriksaan (
    pemeriksaan_id INT AUTO_INCREMENT PRIMARY KEY,
    laporan_id INT NOT NULL,
    petugas_id INT NOT NULL,
    tanggal_penugasan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tanggal_pemeriksaan DATETIME NULL,
    status_pemeriksaan ENUM('ditugaskan','berjalan','selesai','dibatalkan') NOT NULL DEFAULT 'ditugaskan',
    temuan TEXT NULL,
    alasan_penggantian TEXT NULL,
    rekomendasi ENUM('perbaikan','penggantian') NULL,
    jenis_penggantian ENUM('unit','sparepart') NULL,
    sumber_pengganti ENUM('stok_gudang','pengadaan') NULL,
    status_persetujuan ENUM('belum_diajukan','menunggu','disetujui','revisi','dihentikan') NOT NULL DEFAULT 'belum_diajukan',
    catatan_koordinator TEXT NULL,
    CONSTRAINT fk_pemeriksaan_laporan
        FOREIGN KEY (laporan_id) REFERENCES laporan_kerusakan(laporan_id),
    CONSTRAINT fk_pemeriksaan_petugas
        FOREIGN KEY (petugas_id) REFERENCES pengguna(pengguna_id),
    CONSTRAINT ck_pemeriksaan_jenis CHECK (((rekomendasi IS NULL AND jenis_penggantian IS NULL) OR (rekomendasi = 'perbaikan' AND jenis_penggantian IS NULL) OR (rekomendasi = 'penggantian' AND (jenis_penggantian = 'unit' OR (jenis_penggantian = 'sparepart' AND sumber_pengganti = 'pengadaan')))) IS TRUE),
    CONSTRAINT ck_pemeriksaan_sumber CHECK (((rekomendasi IS NULL AND sumber_pengganti IS NULL) OR (rekomendasi = 'perbaikan' AND sumber_pengganti IS NULL) OR (rekomendasi = 'penggantian' AND sumber_pengganti IS NOT NULL)) IS TRUE)
) ENGINE=InnoDB;

CREATE TABLE pengajuan_dana (
    pengajuan_id INT AUTO_INCREMENT PRIMARY KEY,
    pemeriksaan_id INT NOT NULL,
    pengajuan_sebelumnya_id INT NULL UNIQUE,
    pembuat_id INT NOT NULL,
    koordinator_id INT NULL,
    tanggal_dibuat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    rincian_kebutuhan TEXT NOT NULL,
    estimasi_biaya DECIMAL(14,2) NOT NULL,
    status_pengajuan ENUM('diajukan','revisi','disetujui') NOT NULL DEFAULT 'diajukan',
    catatan TEXT NULL,
    CONSTRAINT fk_pengajuan_pemeriksaan
        FOREIGN KEY (pemeriksaan_id) REFERENCES pemeriksaan(pemeriksaan_id),
    CONSTRAINT fk_pengajuan_sebelumnya
        FOREIGN KEY (pengajuan_sebelumnya_id, pemeriksaan_id) REFERENCES pengajuan_dana(pengajuan_id, pemeriksaan_id),
    CONSTRAINT fk_pengajuan_pembuat
        FOREIGN KEY (pembuat_id) REFERENCES pengguna(pengguna_id),
    CONSTRAINT fk_pengajuan_koordinator
        FOREIGN KEY (koordinator_id) REFERENCES pengguna(pengguna_id),
    UNIQUE KEY uq_pengajuan_pemeriksaan (pengajuan_id, pemeriksaan_id),
    CONSTRAINT ck_pengajuan_biaya CHECK (estimasi_biaya >= 0)
) ENGINE=InnoDB;

CREATE TABLE penindaklanjutan (
    penugasan_id INT AUTO_INCREMENT PRIMARY KEY,
    pemeriksaan_id INT NOT NULL,
    pengajuan_id INT NULL,
    petugas_id INT NOT NULL,
    tanggal_mulai DATETIME NULL,
    catatan_tindakan TEXT NULL,
    kendala TEXT NULL,
    hasil TEXT NULL,
    inventaris_pengganti_id INT NULL,
    status_tindakan ENUM('ditugaskan','berjalan','terkendala','selesai','dibatalkan') NOT NULL DEFAULT 'ditugaskan',
    CONSTRAINT fk_tindak_pemeriksaan
        FOREIGN KEY (pemeriksaan_id) REFERENCES pemeriksaan(pemeriksaan_id),
    CONSTRAINT fk_tindak_pengajuan
        FOREIGN KEY (pengajuan_id, pemeriksaan_id) REFERENCES pengajuan_dana(pengajuan_id, pemeriksaan_id),
    CONSTRAINT fk_tindak_petugas
        FOREIGN KEY (petugas_id) REFERENCES pengguna(pengguna_id),
    CONSTRAINT fk_tindak_pengganti
        FOREIGN KEY (inventaris_pengganti_id) REFERENCES inventaris(inventaris_id)
) ENGINE=InnoDB;

CREATE TABLE konfirmasi_hasil (
    konfirmasi_id INT AUTO_INCREMENT PRIMARY KEY,
    penugasan_id INT NOT NULL UNIQUE,
    pelapor_id INT NOT NULL,
    tanggal_konfirmasi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    hasil_konfirmasi ENUM('sesuai','masih_bermasalah') NOT NULL,
    catatan TEXT NULL,
    CONSTRAINT fk_konfirmasi_penugasan
        FOREIGN KEY (penugasan_id) REFERENCES penindaklanjutan(penugasan_id),
    CONSTRAINT fk_konfirmasi_pelapor
        FOREIGN KEY (pelapor_id) REFERENCES pengguna(pengguna_id)
) ENGINE=InnoDB;


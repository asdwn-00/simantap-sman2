INSERT INTO pengguna (pengguna_id, nama, email, password_hash, role) VALUES
(1, 'Dina - PJ Kimia', 'pj.kimia@example.test', '$2y$10$qCAsjXbgmDluMwyxDC4n8OHUUUIJr9APaww80ZHJOANLMXNZ5.RI6', 'pj_lab'),
(2, 'Fajar - PJ Fisika', 'pj.fisika@example.test', '$2y$10$qCAsjXbgmDluMwyxDC4n8OHUUUIJr9APaww80ZHJOANLMXNZ5.RI6', 'pj_lab'),
(3, 'Bela - PJ Biologi', 'pj.biologi@example.test', '$2y$10$qCAsjXbgmDluMwyxDC4n8OHUUUIJr9APaww80ZHJOANLMXNZ5.RI6', 'pj_lab'),
(4, 'Andi - Petugas A', 'petugas.a@example.test', '$2y$10$qCAsjXbgmDluMwyxDC4n8OHUUUIJr9APaww80ZHJOANLMXNZ5.RI6', 'petugas'),
(5, 'Budi - Petugas B', 'petugas.b@example.test', '$2y$10$qCAsjXbgmDluMwyxDC4n8OHUUUIJr9APaww80ZHJOANLMXNZ5.RI6', 'petugas'),
(6, 'Ratna - Koordinator', 'koordinator@example.test', '$2y$10$qCAsjXbgmDluMwyxDC4n8OHUUUIJr9APaww80ZHJOANLMXNZ5.RI6', 'koordinator');

INSERT INTO ruangan (ruangan_id, kode_ruangan, nama_ruangan, jenis_ruangan, lantai, pj_id, petugas_id) VALUES
(1, 'D-LAB-KIM', 'Laboratorium Kimia', 'lab_kimia', 1, 1, NULL),
(2, 'D-LAB-FIS', 'Laboratorium Fisika', 'lab_fisika', 1, 2, NULL),
(3, 'D-LAB-BIO', 'Laboratorium Biologi', 'lab_biologi', 1, 3, NULL),
(4, 'D-X-6', 'Kelas X-6', 'kelas', 2, NULL, 4),
(5, 'D-X-7', 'Kelas X-7', 'kelas', 2, NULL, 5),
(6, 'D-GUDANG', 'Gudang Sarpras', 'gudang', 1, NULL, 4),
(7, 'D-AULA', 'Aula Sekolah', 'lainnya', 1, NULL, 5);

INSERT INTO inventaris (inventaris_id, nama_barang, kategori, spesifikasi, nomor_seri, ruangan_id, kondisi, status_penggunaan) VALUES
(1, 'Neraca Digital', 'Alat Laboratorium', 'Kapasitas 500 gram', 'D-SN-001', 1, 'belum_diperiksa', 'tidak_digunakan'),
(2, 'Kipas Laboratorium Fisika', 'Elektronik', 'Kipas dinding', 'D-SN-002', 2, 'belum_diperiksa', 'tidak_digunakan'),
(3, 'Mikroskop Biologi', 'Alat Laboratorium', 'Mikroskop monokuler', 'D-SN-003', 3, 'rusak_ringan', 'tidak_digunakan'),
(4, 'Proyektor Kelas X-6', 'Elektronik', 'Proyektor ruang kelas', 'D-SN-004', 4, 'rusak_berat', 'tidak_digunakan'),
(5, 'Kursi Kelas X-7', 'Mebel', 'Kursi rangka besi', NULL, 5, 'rusak_ringan', 'tidak_digunakan'),
(6, 'Stopkontak Aula', 'Instalasi Listrik', 'Titik stopkontak dekat panggung', NULL, 7, 'baik', 'digunakan'),
(7, 'Kipas Lama Kelas X-6', 'Elektronik', 'Motor rusak; sudah diganti', 'D-SN-007', 6, 'rusak_berat', 'tidak_digunakan'),
(8, 'Kipas Pengganti Kelas X-6', 'Elektronik', 'Berasal dari stok gudang', 'D-SN-008', 4, 'baik', 'digunakan'),
(9, 'Pengaduk Magnetik', 'Alat Laboratorium', 'Putaran belum stabil', 'D-SN-009', 1, 'rusak_ringan', 'tidak_digunakan'),
(10, 'Kursi Cadangan', 'Mebel', 'Unit cadangan siap digunakan', NULL, 6, 'baik', 'tersedia');

INSERT INTO laporan_kerusakan (laporan_id, pelapor_id, inventaris_id, ruangan_id, tanggal_laporan, kerusakan, prioritas, status_laporan, tanggal_ditutup) VALUES
(1, 1, 1, 1, '2026-09-19 08:00:00', 'Layar neraca tidak menyala.', NULL, 'masuk', NULL),
(2, 2, 2, 2, '2026-09-18 08:00:00', 'Kipas berputar lambat.', NULL, 'diperiksa', NULL),
(3, 3, 3, 3, '2026-09-15 08:00:00', 'Pengatur fokus mikroskop macet.', NULL, 'disetujui', NULL),
(4, 4, 4, 4, '2026-09-14 08:00:00', 'Proyektor mati total; kelas belum memiliki pengganti.', 'tinggi', 'disetujui', NULL),
(5, 5, 5, 5, '2026-09-16 08:00:00', 'Sambungan rangka kursi longgar.', 'rendah', 'ditangani', NULL),
(6, 5, 6, 7, '2026-09-13 08:00:00', 'Stopkontak longgar dan tidak dapat dipakai.', 'tinggi', 'ditangani', NULL),
(7, 4, 7, 4, '2026-09-10 08:00:00', 'Motor kipas kelas X-6 tidak berfungsi.', NULL, 'selesai', '2026-09-12 13:00:00'),
(8, 1, 9, 1, '2026-09-11 08:00:00', 'Putaran pengaduk magnetik tidak stabil.', NULL, 'diperiksa', NULL);

INSERT INTO pemeriksaan (pemeriksaan_id, laporan_id, petugas_id, tanggal_penugasan, tanggal_pemeriksaan, status_pemeriksaan, temuan, rekomendasi, sumber_pengganti, status_persetujuan, catatan_koordinator, alasan_penggantian, jenis_penggantian) VALUES
(1, 2, 4, '2026-09-18 09:00:00', NULL, 'berjalan', NULL, NULL, NULL, 'belum_diajukan', NULL, NULL, NULL),
(2, 3, 5, '2026-09-15 09:00:00', '2026-09-15 10:00:00', 'selesai', 'Pengatur fokus perlu servis.', 'perbaikan', NULL, 'disetujui', 'Rencana servis diterima; ajukan rincian biaya.', NULL, NULL),
(3, 4, 4, '2026-09-14 09:00:00', '2026-09-14 10:00:00', 'selesai', 'Kerusakan berat; tidak ada unit pengganti di gudang.', 'penggantian', 'pengadaan', 'disetujui', 'Penggantian diterima. Pelaksanaan menunggu petugas tersedia.', 'Komponen utama rusak berat dan tidak dapat diperbaiki; stok pengganti di gudang tidak tersedia.', 'unit'),
(4, 5, 5, '2026-09-16 09:00:00', '2026-09-16 10:00:00', 'selesai', 'Baut rangka longgar, dapat dikencangkan.', 'perbaikan', NULL, 'disetujui', 'Gunakan peralatan yang tersedia; tanpa pengajuan dana.', NULL, NULL),
(5, 6, 4, '2026-09-13 09:00:00', '2026-09-13 10:00:00', 'selesai', 'Dudukan stopkontak longgar.', 'perbaikan', NULL, 'disetujui', 'Perbaiki dudukan dengan material yang tersedia.', NULL, NULL),
(6, 7, 5, '2026-09-10 09:00:00', '2026-09-10 10:00:00', 'selesai', 'Motor kipas rusak berat; unit cadangan tersedia.', 'penggantian', 'stok_gudang', 'disetujui', 'Gunakan unit dengan ID 8 dari gudang.', 'Motor kipas rusak berat dan tidak dapat dipulihkan; unit cadangan layak tersedia di gudang.', 'unit'),
(7, 8, 4, '2026-09-11 09:00:00', '2026-09-11 10:00:00', 'selesai', 'Dugaan sambungan pengatur putaran longgar.', 'perbaikan', NULL, 'disetujui', 'Periksa dan kencangkan sambungan; tanpa pengajuan dana.', NULL, NULL),
(8, 8, 5, '2026-09-14 09:00:00', NULL, 'ditugaskan', NULL, NULL, NULL, 'belum_diajukan', 'Pemeriksaan ulang karena pelapor menyatakan putaran masih bermasalah.', NULL, NULL);


INSERT INTO pengajuan_dana (pengajuan_id, pemeriksaan_id, pengajuan_sebelumnya_id, pembuat_id, koordinator_id, tanggal_dibuat, rincian_kebutuhan, estimasi_biaya, status_pengajuan, catatan) VALUES
(1, 2, NULL, 5, 6, '2026-09-15 13:00:00', 'Servis pengatur fokus mikroskop dan biaya transportasi.', 450000.00, 'revisi', 'Pisahkan rincian servis dan transportasi; sesuaikan estimasi.'),
(2, 2, 1, 5, NULL, '2026-09-16 09:00:00', 'Servis pengatur fokus Rp300.000 dan transportasi Rp50.000.', 350000.00, 'diajukan', NULL),
(3, 3, NULL, 4, 6, '2026-09-14 13:00:00', 'Pembelian satu unit proyektor pengganti untuk kelas X-6.', 4500000.00, 'disetujui', 'Disetujui; lakukan pengadaan sesuai kebutuhan yang diajukan.');

INSERT INTO penindaklanjutan (penugasan_id, pemeriksaan_id, pengajuan_id, petugas_id, tanggal_mulai, catatan_tindakan, kendala, hasil, inventaris_pengganti_id, status_tindakan) VALUES
(1, 4, NULL, 5, '2026-09-19 09:00:00', 'Sedang mengencangkan baut rangka kursi.', NULL, NULL, NULL, 'berjalan'),
(2, 5, NULL, 4, '2026-09-14 09:00:00', 'Mengencangkan dudukan dan sambungan stopkontak.', NULL, 'Stopkontak terpasang kuat dan kembali berfungsi.', NULL, 'selesai'),
(3, 6, NULL, 5, '2026-09-11 09:00:00', 'Memasang unit cadangan dengan ID 8 di kelas X-6; memindahkan unit lama ke gudang.', NULL, 'Kipas pengganti berfungsi dengan baik.', 8, 'selesai'),
(4, 7, NULL, 4, '2026-09-12 09:00:00', 'Mengencangkan sambungan pengatur putaran.', NULL, 'Pada pengujian awal putaran terlihat stabil.', NULL, 'selesai');

INSERT INTO konfirmasi_hasil (konfirmasi_id, penugasan_id, pelapor_id, tanggal_konfirmasi, hasil_konfirmasi, catatan) VALUES
(1, 3, 4, '2026-09-12 11:00:00', 'sesuai', 'Kipas kelas X-6 sudah dapat digunakan.'),
(2, 4, 1, '2026-09-13 11:00:00', 'masih_bermasalah', 'Setelah digunakan beberapa menit, putaran kembali tidak stabil.');

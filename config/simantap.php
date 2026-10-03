<?php

return [
    'role' => ['pj_lab' => 'PJ Lab', 'petugas' => 'Petugas Sarpras', 'koordinator' => 'Koordinator Sarpras'],
    'status' => ['masuk' => 0, 'diperiksa' => 25, 'disetujui' => 50, 'ditangani' => 75, 'selesai' => 100],
    'ruangan' => ['lab_biologi' => 'Laboratorium Biologi', 'lab_kimia' => 'Laboratorium Kimia', 'lab_fisika' => 'Laboratorium Fisika', 'kelas' => 'Kelas', 'gudang' => 'Gudang', 'lainnya' => 'Lainnya'],
    'warna_laporan' => [
        'masuk' => 'simantap-status-masuk',
        'diperiksa' => 'simantap-status-diperiksa',
        'disetujui' => 'simantap-status-disetujui',
        'ditangani' => 'simantap-status-ditangani',
        'selesai' => 'simantap-status-selesai',
    ],
    'warna_tugas' => [
        'ditugaskan' => 'simantap-tugas-ditugaskan',
        'berjalan' => 'simantap-tugas-berjalan',
        'terkendala' => 'simantap-tugas-terkendala',
        'selesai' => 'simantap-tugas-selesai',
        'dibatalkan' => 'simantap-tugas-dibatalkan',
    ],
];

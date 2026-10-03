<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('pengguna')->exists()) {
            throw new \RuntimeException('Data awal hanya boleh diimpor ke database kosong.');
        }
        DB::transaction(function () {
            $sql = file_get_contents(database_path('schema/dummy.sql'));
            preg_match_all('/INSERT INTO .*?;(?=\s*(?:--|INSERT|$))/s', $sql, $statements);
            foreach ($statements[0] as $statement) {
                DB::unprepared($statement);
            }
        });
    }
}

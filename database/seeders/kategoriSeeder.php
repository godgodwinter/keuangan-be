<?php

namespace Database\Seeders;

use App\Models\Gurubk;
use App\Models\masterdeteksi;
use App\Models\Ortu;
use App\Models\Owner;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Yayasan;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class kategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('kategori')->truncate();

        $kategoriList = [
            (object)[
                'nama' => 'Asuransi',
                'jenis' => 'Pengeluaran',
            ],
            (object)[
                'nama' => 'Bayi',
                'jenis' => 'Pengeluaran',
            ],
            (object)[
                'nama' => 'Belanja',
                'jenis' => 'Pengeluaran',
            ],
            (object)[
                'nama' => 'Buah-buahan',
                'jenis' => 'Pengeluaran',
            ],
            (object)[
                'nama' => 'Cemilan',
                'jenis' => 'Pengeluaran',
            ],
            (object)[
                'nama' => 'Hadiah',
                'jenis' => 'Pengeluaran',
            ],
            (object)[
                'nama' => 'Kesehatan',
                'jenis' => 'Pengeluaran',
            ],
            (object)[
                'nama' => 'Lain-lain',
                'jenis' => 'Pengeluaran',
            ],

            (object)[
                'nama' => 'Deposito',
                'jenis' => 'Pemasukan',
            ],
            (object)[
                'nama' => 'Gaji',
                'jenis' => 'Pemasukan',
            ],
            (object)[
                'nama' => 'Hibah',
                'jenis' => 'Pemasukan',
            ],
            (object)[
                'nama' => 'Penjualan',
                'jenis' => 'Pemasukan',
            ],
            (object)[
                'nama' => 'Tabungan',
                'jenis' => 'Pemasukan',
            ],
            (object)[
                'nama' => 'Lain-lain',
                'jenis' => 'Pemasukan',
            ],
        ];
        //
        foreach ($kategoriList as $data) {

            DB::table('kategori')->insert([
                'nama' => $data->nama,
                'jenis' => $data->jenis,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}

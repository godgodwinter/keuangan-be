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

class oneseeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->truncate();
        //settings SEEDER
        DB::table('settings')->insert([
            'app_nama' => 'Nama App',
            'app_namapendek' => 'Bae',
            'paginationjml' => '10',
            'login' => 'Aktif',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('users')->truncate();
        // admin
        User::insert([
            'nama' => 'Admin Paijo',
            'email' => 'admin@gmail.com',
            'username' => 'admin',
            'nomeridentitas' => '123',
            'password' => Hash::make('admin'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}

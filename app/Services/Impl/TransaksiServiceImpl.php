<?php

namespace App\Services\Impl;

use App\Services\TransaksiService;
use Illuminate\Support\Facades\DB;

class TransaksiServiceImpl implements TransaksiService
{
    public function getAll()
    {
        return DB::table('transaksi')->get();
    }
}

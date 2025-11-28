<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\kategori;
use App\Models\transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class adminTransaksiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard()->user();
        $isAdmin = $user && $user->username === 'admin';

        // Query dasar
        $query = transaksi::with(['kategori', 'users']);

        // Jika bukan admin maka filter data hanya milik user tersebut
        if (!$isAdmin) {
            $query->where('users_id', $user->id);
        }

        $items = $query->get();

        $data = [];
        $dataRekap = (object)[
            'pemasukan'   => 0,
            'pengeluaran' => 0,
            'saldo'       => 0,
        ];

        foreach ($items as $item) {
            $milikSendiri = ($item->users_id == $user->id);
            $data[] = (object)[
                'id'             => $item->id,
                'users_id'       => $item->users_id,
                'users_nama'     => optional($item->users)->nama,
                'users_username' => optional($item->users)->username,
                'milik_sendiri'  => $milikSendiri,
                'kategori_id'    => $item->kategori_id,
                'kategori_nama'  => optional($item->kategori)->nama,
                'tgl'            => $item->tgl,
                'nama'           => $item->nama,
                'jenis'          => $item->jenis,
                'nominal'        => $item->nominal,
                'created_at'     => $item->created_at,
                'updated_at'     => $item->updated_at,
            ];

            // Rekapitulasi
            if ($item->jenis === 'Pemasukan') {
                $dataRekap->pemasukan += $item->nominal;
            } else {
                $dataRekap->pengeluaran += $item->nominal;
            }
        }

        $dataRekap->saldo = $dataRekap->pemasukan - $dataRekap->pengeluaran;

        return response()->json([
            'success'   => true,
            'data'      => $data,
            'dataRekap' => $dataRekap,
        ], 200);
    }

    public function rekap(Request $request)
    {
        $user = Auth::guard()->user();
        $isAdmin = $user && $user->username === 'admin';

        // Ambil month & year dari request, fallback ke tanggal sekarang
        $month = $request->input('month', date('m'));
        // Support kedua-duanya: "year" dan typo lama "yaer"
        $year  = $request->input('year', $request->input('yaer', date('Y')));

        // Query dasar
        $query = transaksi::with(['kategori', 'users'])
            ->whereMonth('tgl', $month)
            ->whereYear('tgl', $year);

        // Jika bukan admin, filter berdasarkan users_id
        if (!$isAdmin) {
            $query->where('users_id', $user->id);
        }

        $items = $query->get();

        $data = [];
        $dataRekap = (object)[
            'pemasukan'   => 0,
            'pengeluaran' => 0,
            'saldo'       => 0,
        ];

        foreach ($items as $item) {
            $milikSendiri = ($item->users_id == $user->id);
            $tempData = (object)[
                'id'             => $item->id,
                'users_id'       => $item->users_id,
                'users_nama'     => optional($item->users)->nama,
                'users_username' => optional($item->users)->username,
                'milik_sendiri'  => $milikSendiri,
                'kategori_id'    => $item->kategori_id,
                'kategori_nama'  => optional($item->kategori)->nama,
                'tgl'            => $item->tgl,
                'nama'           => $item->nama,
                'jenis'          => $item->jenis,
                'nominal'        => $item->nominal,
                'created_at'     => $item->created_at,
                'updated_at'     => $item->updated_at,
            ];

            $data[] = $tempData;

            if ($item->jenis === 'Pemasukan') {
                $dataRekap->pemasukan += $item->nominal;
            } else {
                $dataRekap->pengeluaran += $item->nominal;
            }
        }

        $dataRekap->saldo = $dataRekap->pemasukan - $dataRekap->pengeluaran;

        return response()->json([
            'success'   => true,
            'data'      => $data,
            'dataRekap' => $dataRekap,
            'filter'    => [
                'month' => $month,
                'year'  => $year,
            ],
        ], 200);
    }

    public function rekap_perkategori(Kategori $kategori, Request $request)
    {
        $user = Auth::guard()->user();
        $isAdmin = $user && $user->username === 'admin';

        // Ambil month & year dari request, fallback ke tanggal sekarang
        $month = $request->input('month', date('m'));
        // Support "year" dan typo lama "yaer"
        $year  = $request->input('year', $request->input('yaer', date('Y')));

        // Query dasar
        $query = transaksi::with(['kategori', 'users'])
            ->whereMonth('tgl', $month)
            ->whereYear('tgl', $year)
            ->where('kategori_id', $kategori->id);

        // Jika bukan admin, filter berdasarkan users_id
        if (!$isAdmin) {
            $query->where('users_id', $user->id);
        }

        $items = $query->get();

        $data = [];
        $dataRekap = (object)[
            'pemasukan'   => 0,
            'pengeluaran' => 0,
            'saldo'       => 0,
            'total'       => 0,
            'nama'        => $kategori->nama,
        ];

        foreach ($items as $item) {
            $tempData = (object)[
                'id'             => $item->id,
                'users_id'       => $item->users_id,
                'users_nama'     => optional($item->users)->nama,
                'users_username' => optional($item->users)->username,
                'kategori_id'    => $item->kategori_id,
                'kategori_nama'  => optional($item->kategori)->nama,
                'tgl'            => $item->tgl,
                'nama'           => $item->nama,
                'jenis'          => $item->jenis,
                'nominal'        => $item->nominal,
                'created_at'     => $item->created_at,
                'updated_at'     => $item->updated_at,
            ];

            $data[] = $tempData;

            if ($item->jenis === 'Pemasukan') {
                $dataRekap->pemasukan += $item->nominal;
            } else {
                $dataRekap->pengeluaran += $item->nominal;
            }

            // total nominal, terlepas dari jenis
            $dataRekap->total += $item->nominal;
        }

        $dataRekap->saldo = $dataRekap->pemasukan - $dataRekap->pengeluaran;

        return response()->json([
            'success'   => true,
            'data'      => $data,
            'dataRekap' => $dataRekap,
            'filter'    => [
                'month'       => $month,
                'year'        => $year,
                'kategori_id' => $kategori->id,
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'tgl'         => 'required',
            'nama'        => 'required',
            // 'desc'      => 'required', //keterangan
            'jenis'       => 'required',
            'nominal'     => 'required',
            'kategori_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $items = 'Data berhasil di tambahkan';

        $data_id = DB::table('transaksi')->insertGetId([
            'tgl'         => $request->tgl,
            'nama'        => $request->nama,
            'desc'        => $request->desc,
            'jenis'       => $request->jenis,
            'nominal'     => $request->nominal,
            'kategori_id' => $request->kategori_id,
            'users_id'    => Auth::guard()->user()->id,
            'created_at'  => date("Y-m-d H:i:s"),
            'updated_at'  => date("Y-m-d H:i:s"),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $items,
            'users'   => Auth::guard()->user()->id,
            'id'      => $data_id
        ], 200);
    }

    public function edit(transaksi $item)
    {
        // non-admin hanya boleh edit miliknya sendiri
        if (!$this->fnPeriksaOwner($item->users_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses data ini!',
            ], 401);
        }

        $data = transaksi::with('kategori', 'users')
            ->where('id', $item->id)
            ->first();

        $data->kategori_nama = $data->kategori ? $data->kategori->nama : null;
        $data->users_nama    = $data->users ? $data->users->nama : null;

        return response()->json([
            'success' => true,
            'data'    => $data,
        ], 200);
    }

    public function update(transaksi $item, Request $request)
    {
        // non-admin hanya boleh update miliknya sendiri
        if (!$this->fnPeriksaOwner($item->users_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses data ini!',
            ], 401);
        }

        //set validation
        $validator = Validator::make($request->all(), [
            'tgl'         => 'required',
            'nama'        => 'required',
            // 'desc'      => 'required',
            'jenis'       => 'required',
            'nominal'     => 'required',
            'kategori_id' => 'required',
        ]);

        //response error validation
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        transaksi::where('id', $item->id)
            ->update([
                'tgl'         => $request->tgl,
                'nama'        => $request->nama,
                'desc'        => $request->desc,
                'jenis'       => $request->jenis,
                'nominal'     => $request->nominal,
                'kategori_id' => $request->kategori_id,
                'updated_at'  => date("Y-m-d H:i:s"),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil di update!',
            'id'      => $item->id
        ], 200);
    }

    public function destroy(transaksi $item)
    {
        $msg    = 'Data berhasil di hapus!';
        $status = true;
        $code   = 200;

        $periksa = $this->fnPeriksaOwner($item->users_id);
        if ($periksa == false) {
            $msg    = 'Anda tidak memiliki akses data ini!';
            $status = false;
            $code   = 401;
        } else {
            transaksi::destroy($item->id);
            // delete permanent
            // transaksi::where('id', $item->id)->forcedelete();
        }

        return response()->json([
            'success' => $status,
            'message' => $msg,
            'id'      => $item->users_id,
        ], $code);
    }

    public function destroyForce($item)
    {
        $msg    = 'Data berhasil di hapus!';
        $status = true;
        $code   = 200;

        $data = DB::table('transaksi')->where('id', $item)->first();

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan!',
                'id'      => $item,
            ], 404);
        }

        $periksa = $this->fnPeriksaOwner($data->users_id);
        if ($periksa == false) {
            $msg    = 'Anda tidak memiliki akses data ini!';
            $status = false;
            $code   = 401;
        } else {
            // delete permanent
            transaksi::where('id', $item)->forceDelete();
        }

        return response()->json([
            'success' => $status,
            'message' => $msg,
            'id'      => $item,
        ], $code);
    }

    public function fnPeriksaOwner($ownerId)
    {
        $user = Auth::guard()->user();

        // Jika belum login, pasti tidak boleh
        if (!$user) {
            return false;
        }

        // ADMIN BOLEH SEMUA DATA
        if ($user->username === 'admin') {
            return true;
        }

        // selain admin, hanya boleh data miliknya sendiri
        return $user->id == $ownerId;
    }
}

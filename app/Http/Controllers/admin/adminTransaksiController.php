<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class adminTransaksiController extends Controller
{
    public function index(Request $request)
    {
        $items = transaksi::with('kategori')
            ->with('users')
            ->where('users_id', Auth::guard()->user()->id)
            ->get();
        $data = [];
        foreach ($items as $item) {
            $tempData = (object)[];
            $tempData->id = $item->id;
            $tempData->users_id = $item->users_id;
            $tempData->users_nama = $item->users ? $item->users->nama : null;
            $tempData->kategori_id = $item->kategori_id;
            $tempData->kategori_nama = $item->kategori ? $item->kategori->nama : null;
            $tempData->tgl = $item->tgl;
            $tempData->nama = $item->nama;
            $tempData->jenis = $item->jenis;
            $tempData->nominal = $item->nominal;
            $tempData->created_at = $item->created_at;
            $tempData->updated_at = $item->updated_at;
            $data[] = $tempData;
        }
        return response()->json([
            'success'    => true,
            'data'    => $data,
        ], 200);
    }

    public function store(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'tgl'   => 'required',
            'nama'   => 'required',
            // 'desc'   => 'required',
            'jenis'   => 'required',
            'nominal'   => 'required',
            'kategori_id'   => 'required',
        ]);

        $items = 'Data berhasil di tambahkan';
        // $data = $request->except('_token');
        // apiprobk::create($data);

        $data_id = DB::table('transaksi')->insertGetId(
            array(
                'tgl'     =>   $request->tgl,
                'nama'     =>   $request->nama,
                // 'desc'     =>   $request->desc,
                'jenis'     =>   $request->jenis,
                'nominal'     =>   $request->nominal,
                'kategori_id'     =>   $request->kategori_id,
                'users_id'     =>    Auth::guard()->user()->id,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            )
        );

        return response()->json([
            'success'    => true,
            'data'    => $items,
            'users' => Auth::guard()->user()->id,
            'id' => $data_id
        ], 200);
    }

    public function edit(transaksi $item)
    {
        $data = transaksi::with('kategori')->where('id', $item->id)->first();
        $data->kategori_nama = $data->kategori ? $data->kategori->nama : null;
        $data->users_nama = $data->users ? $data->users->nama : null;
        return response()->json([
            'success'    => true,
            'data'    => $data,
        ], 200);
    }
    public function update(transaksi $item, Request $request)
    {

        //set validation
        $validator = Validator::make($request->all(), [
            'tgl'   => 'required',
            'nama'   => 'required',
            // 'desc'   => 'required',
            'jenis'   => 'required',
            'nominal'   => 'required',
            'kategori_id'   => 'required',
        ]);
        //response error validation
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        transaksi::where('id', $item->id)
            ->update([
                'tgl'     =>   $request->tgl,
                'nama'     =>   $request->nama,
                // 'desc'     =>   $request->desc,
                'jenis'     =>   $request->jenis,
                'nominal'     =>   $request->nominal,
                'kategori_id'     =>   $request->kategori_id,
                'users_id'     =>    Auth::guard()->user()->id,
                'updated_at' => date("Y-m-d H:i:s")
            ]);

        return response()->json([
            'success'    => true,
            'message'    => 'Data berhasil di update!',
            'id' => $item->id
        ], 200);
    }
    public function destroy(transaksi $item)
    {

        $msg = 'Data berhasil di hapus!';
        $status = true;
        $code = 200;
        $periksa = $this->fnPeriksaOwner($item->users_id);
        if ($periksa == false) {
            $msg = 'Anda tidak memiliki akses  data ini!';
            $status = false;
            $code = 401;
        } else {
            transaksi::destroy($item->id);
            // delete permanent
            // transaksi::where('id', $item->id)->forcedelete();
        }
        return response()->json([
            'success'    => $status,
            'message'    => $msg,
            'id' => $item->users_id,
        ], $code);
    }
    public function destroyForce($item)
    {
        $msg = 'Data berhasil di hapus!';
        $status = true;
        $code = 200;
        $data = DB::table('transaksi')->where('id', $item)->first();
        // dd($data);
        $periksa = $this->fnPeriksaOwner($data->users_id);
        if ($periksa == false) {
            $msg = 'Anda tidak memiliki akses  data ini!';
            $status = false;
            $code = 401;
        } else {
            // transaksi::destroy($item->id);
            // delete permanent
            transaksi::where('id', $item)->forcedelete();
        }
        return response()->json([
            'success'    => $status,
            'message'    => $msg,
            'id' => $item,
        ], $code);
    }

    public function fnPeriksaOwner($id)
    {
        $users_id = Auth::guard()->user()->id;
        if ($users_id == $id) {
            return true;
        }
        return false;
    }
}

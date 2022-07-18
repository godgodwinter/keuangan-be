<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class adminKategoriController extends Controller
{
    public function index(Request $request)
    {
        $items = kategori::get();
        return response()->json([
            'success'    => true,
            'data'    => $items,
        ], 200);
    }

    public function store(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'nama'   => 'required',
            'jenis'   => 'required',
        ]);

        $items = 'Data berhasil di tambahkan';
        // $data = $request->except('_token');
        // apiprobk::create($data);

        $data_id = DB::table('kategori')->insertGetId(
            array(
                'nama'     =>   $request->nama,
                'jenis'     =>   $request->jenis,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            )
        );

        return response()->json([
            'success'    => true,
            'data'    => $items,
            'id' => $data_id
        ], 200);
    }

    public function edit(kategori $item)
    {
        return response()->json([
            'success'    => true,
            'data'    => $item,
        ], 200);
    }
    public function update(kategori $item, Request $request)
    {

        //set validation
        $validator = Validator::make($request->all(), [
            'nama'   => 'required',
            'jenis'   => 'required',
        ]);
        //response error validation
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        kategori::where('id', $item->id)
            ->update([
                'nama'     =>   $request->nama,
                'jenis'     =>   $request->jenis,
                'updated_at' => date("Y-m-d H:i:s")
            ]);

        return response()->json([
            'success'    => true,
            'message'    => 'Data berhasil di update!',
            'id' => $item->id
        ], 200);
    }
    public function destroy(kategori $item)
    {

        // kategori::destroy($item->id);
        // delete permanent
        kategori::where('id', $item->id)->forcedelete();

        return response()->json([
            'success'    => true,
            'message'    => 'Data berhasil di hapus!',
        ], 200);
    }
}

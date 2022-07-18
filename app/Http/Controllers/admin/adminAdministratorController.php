<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class adminAdministratorController extends Controller
{
    public function index(Request $request)
    {
        $items = User::get();
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
        ]);

        $items = 'Data berhasil di tambahkan';
        // $data = $request->except('_token');
        // apiprobk::create($data);

        $user = User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'username' => $request->username,
            'nomeridentitas' => $request->nomeridentitas,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success'    => true,
            'data'    => $items,
            'id' => $user->id
        ], 200);
    }

    public function edit(User $item)
    {
        return response()->json([
            'success'    => true,
            'data'    => $item,
        ], 200);
    }
    public function update(User $item, Request $request)
    {

        //set validation
        $validator = Validator::make($request->all(), [
            'nama'   => 'required',
        ]);
        //response error validation
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        User::where('id', $item->id)
            ->update([
                'nama' => $request->nama,
                'email' => $request->email,
                'username' => $request->username,
                'nomeridentitas' => $request->nomeridentitas,
                // 'password' => Hash::make($request->password),
                'updated_at' => date("Y-m-d H:i:s")
            ]);

        // update password
        if ($request->password) {
            User::where('id', $item->id)
                ->update([
                    'password' => Hash::make($request->password),
                    'updated_at' => date("Y-m-d H:i:s")
                ]);
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Data berhasil di update!',
            'id' => $item->id
        ], 200);
    }
    public function destroy(User $item)
    {

        User::destroy($item->id);
        return response()->json([
            'success'    => true,
            'message'    => 'Data berhasil di hapus!',
        ], 200);
    }
    public function destroyForce($item)
    {

        User::where('id', $item)->forcedelete();
        return response()->json([
            'success'    => true,
            'message'    => 'Data berhasil di hapus!',
        ], 200);
    }
}

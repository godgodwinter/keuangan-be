<?php

namespace App\Http\Controllers;

use App\Exports\exportSiswaPerkelas;
use App\Models\catatankasussiswa;
use App\Models\catatanpengembangandirisiswa;
use App\Models\catatanprestasisiswa;
use App\Models\klasifikasiakademis;
use App\Models\Siswa;
use App\Services\hasilPsikologiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
// use Barryvdh\DomPDF\Facade\Pdf;
use PDF;

class cetakController extends Controller
{
    // public function __construct(hasilPsikologiService $hasilPsikologiService)
    // {
    //     $this->hasilPsikologiService = $hasilPsikologiService;
    // }

    // public function catatankasus($siswa_id, Request $request)
    // {
    //     $req = $siswa_id;
    //     $datenow = base64_decode($request->token); //tanggal untuk random kode harian
    //     $siswa = base64_decode($req);
    //     $datasiswa = Siswa::with('kelas')->with('sekolah')->where('id', $siswa)->first();
    //     $items = catatankasussiswa::with('siswa')
    //         ->where('siswa_id', $siswa)
    //         ->get();
    //     if ($datenow == date('Y-m-d')) {
    //         // dd($req, $siswa, $items, $datenow);
    //         $tgl = date("YmdHis");
    //         $pdf = PDF::loadview('dev.cetak.catatankasus', compact('items', 'datasiswa'))->setPaper('legal', 'potrait');
    //         return $pdf->stream('data' . $tgl . '.pdf');
    //     } else {
    //         echo 'Token Invalid!';
    //     }
    // }
}

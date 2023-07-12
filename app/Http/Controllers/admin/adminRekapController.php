<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\kategori;
use App\Models\transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class adminRekapController extends Controller
{
    public function ringkasan(Request $request)
    {
        // request month, year
        $month = $request->month ? $request->month : date('m');
        $year = $request->yaer ? $request->yaer : date('Y');


        // Ambil data pemasukan
        $pemasukan = transaksi::where('jenis', 'pemasukan')
            ->whereMonth('tgl', $month)
            ->whereYear('tgl', $year)
            ->sum('nominal');

        // Ambil data pengeluaran
        $pengeluaran = transaksi::where('jenis', 'pengeluaran')
            ->whereMonth('tgl', $month)
            ->whereYear('tgl', $year)
            ->sum('nominal');

        $data = [];
        if ($pengeluaran + $pemasukan > 0) {
            // Hitung persentase pengeluaran
            $total = $pemasukan + $pengeluaran;
            $persentasePengeluaran = ($pengeluaran / $total) * 100;
            $persentasePemasukan = ($pemasukan / $total) * 100;

            $tempData =
                [
                    'id' => 1,
                    'nama' => 'Pemasukan',
                    'nominal' => $pemasukan,
                    'persentase' =>  number_format($persentasePemasukan, 2),
                ];
            $data[] = $tempData;

            $tempData =
                [
                    'id' => 2,
                    'nama' => 'Pengeluaran',
                    'nominal' => $pengeluaran,
                    'persentase' =>  number_format($persentasePengeluaran, 2),
                ];
            $data[] = $tempData;

            // Urutkan data kategori berdasarkan persentase terbesar
            usort($data, function ($a, $b) {
                return $b['persentase'] - $a['persentase'];
            });
        }
        return response()->json([
            'success'    => true,
            'data'    => $data,
            // 'dataRekap'    => $dataRekap,
        ], 200);
    }

    public function rekap_kategori(Request $request)
    {
        // request month, year
        $month = $request->month ? $request->month : date('m');
        $year = $request->yaer ? $request->yaer : date('Y');
        $tipe = $request->tipe ? $request->tipe : "pengeluaran";
        $dataKategori = [];
        if ($tipe == "pengeluaran") {
            // Ambil data pengeluaran per kategori
            $kategoriPengeluaran = kategori::where('jenis', 'Pengeluaran')->get();
            foreach ($kategoriPengeluaran as $kategori) {
                // Ambil data pengeluaran per kategori dan bulan
                $pengeluaran = transaksi::where('jenis', 'Pengeluaran')
                    ->where('kategori_id', $kategori->id)
                    ->whereMonth('tgl', $month)
                    ->whereYear('tgl', $year)
                    ->sum('nominal');

                if ($pengeluaran > 0) {
                    // Hitung persentase pengeluaran per kategori
                    $totalPengeluaran = transaksi::where('jenis', 'Pengeluaran')
                        ->where('kategori_id', $kategori->id)
                        ->whereMonth('tgl', $month)
                        ->whereYear('tgl', $year)
                        ->sum('nominal');

                    $total = transaksi::where('jenis', 'Pengeluaran')
                        ->whereMonth('tgl', $month)
                        ->whereYear('tgl', $year)
                        ->sum('nominal');
                    $persentasePengeluaran = $total > 0 ? ($totalPengeluaran / $total) * 100 : 0;

                    $dataKategori[] = [
                        'nama' => $kategori->nama,
                        'nominal' => $pengeluaran,
                        'persentase' => number_format($persentasePengeluaran, 2),
                    ];
                }
            }
        } else {
            // Ambil data pengeluaran per kategori
            $kategoriPemasukan = kategori::where('jenis', 'Pemasukan')->get();
            foreach ($kategoriPemasukan as $kategori) {
                // Ambil data pengeluaran per kategori dan bulan
                $Pemasukan = transaksi::where('jenis', 'Pemasukan')
                    ->where('kategori_id', $kategori->id)
                    ->whereMonth('tgl', $month)
                    ->whereYear('tgl', $year)
                    ->sum('nominal');

                if ($Pemasukan > 0) {
                    // Hitung persentase Pemasukan per kategori
                    $totalPemasukan = transaksi::where('jenis', 'Pemasukan')
                        ->where('kategori_id', $kategori->id)
                        ->whereMonth('tgl', $month)
                        ->whereYear('tgl', $year)
                        ->sum('nominal');

                    $total = transaksi::where('jenis', 'Pemasukan')
                        ->whereMonth('tgl', $month)
                        ->whereYear('tgl', $year)
                        ->sum('nominal');
                    $persentasePemasukan = $total > 0 ? ($totalPemasukan / $total) * 100 : 0;

                    $dataKategori[] = [
                        'nama' => $kategori->nama,
                        'nominal' => $Pemasukan,
                        'persentase' => number_format($persentasePemasukan, 2),
                    ];
                }
            }
        }

        // Urutkan data kategori berdasarkan persentase terbesar
        usort($dataKategori, function ($a, $b) {
            return $b['persentase'] - $a['persentase'];
        });
        return response()->json([
            'success'    => true,
            'data'    => $dataKategori,
            // 'dataRekap'    => $dataRekap,
        ], 200);
    }


    public function pertahun_ringkasan(Request $request)
    {
        // request month, year
        $month = $request->month ? $request->month : date('m');
        $year = $request->yaer ? $request->yaer : date('Y');


        // Ambil data pemasukan
        $pemasukan = transaksi::where('jenis', 'pemasukan')
            // ->whereMonth('tgl', $month)
            ->whereYear('tgl', $year)
            ->sum('nominal');

        // Ambil data pengeluaran
        $pengeluaran = transaksi::where('jenis', 'pengeluaran')
            // ->whereMonth('tgl', $month)
            ->whereYear('tgl', $year)
            ->sum('nominal');

        $data = [];
        if ($pengeluaran + $pemasukan > 0) {
            // Hitung persentase pengeluaran
            $total = $pemasukan + $pengeluaran;
            $persentasePengeluaran = ($pengeluaran / $total) * 100;
            $persentasePemasukan = ($pemasukan / $total) * 100;

            $tempData =
                [
                    'id' => 1,
                    'nama' => 'Pemasukan',
                    'nominal' => $pemasukan,
                    'persentase' =>  number_format($persentasePemasukan, 2),
                ];
            $data[] = $tempData;

            $tempData =
                [
                    'id' => 2,
                    'nama' => 'Pengeluaran',
                    'nominal' => $pengeluaran,
                    'persentase' =>  number_format($persentasePengeluaran, 2),
                ];
            $data[] = $tempData;

            // Urutkan data kategori berdasarkan persentase terbesar
            usort($data, function ($a, $b) {
                return $b['persentase'] - $a['persentase'];
            });
        }
        return response()->json([
            'success'    => true,
            'data'    => $data,
            // 'dataRekap'    => $dataRekap,
        ], 200);
    }

    public function pertahun_rekap_kategori(Request $request)
    {
        // request month, year
        $month = $request->month ? $request->month : date('m');
        $year = $request->yaer ? $request->yaer : date('Y');
        $tipe = $request->tipe ? $request->tipe : "pengeluaran";
        $dataKategori = [];
        if ($tipe == "pengeluaran") {
            // Ambil data pengeluaran per kategori
            $kategoriPengeluaran = kategori::where('jenis', 'Pengeluaran')->get();
            foreach ($kategoriPengeluaran as $kategori) {
                // Ambil data pengeluaran per kategori dan bulan
                $pengeluaran = transaksi::where('jenis', 'Pengeluaran')
                    ->where('kategori_id', $kategori->id)
                    // ->whereMonth('tgl', $month)
                    ->whereYear('tgl', $year)
                    ->sum('nominal');

                if ($pengeluaran > 0) {
                    // Hitung persentase pengeluaran per kategori
                    $totalPengeluaran = transaksi::where('jenis', 'Pengeluaran')
                        ->where('kategori_id', $kategori->id)
                        // ->whereMonth('tgl', $month)
                        ->whereYear('tgl', $year)
                        ->sum('nominal');

                    $total = transaksi::where('jenis', 'Pengeluaran')
                        // ->whereMonth('tgl', $month)
                        ->whereYear('tgl', $year)
                        ->sum('nominal');
                    $persentasePengeluaran = $total > 0 ? ($totalPengeluaran / $total) * 100 : 0;

                    $dataKategori[] = [
                        'nama' => $kategori->nama,
                        'nominal' => $pengeluaran,
                        'persentase' => number_format($persentasePengeluaran, 2),
                    ];
                }
            }
        } else {
            // Ambil data pengeluaran per kategori
            $kategoriPemasukan = kategori::where('jenis', 'Pemasukan')->get();
            foreach ($kategoriPemasukan as $kategori) {
                // Ambil data pengeluaran per kategori dan bulan
                $Pemasukan = transaksi::where('jenis', 'Pemasukan')
                    ->where('kategori_id', $kategori->id)
                    // ->whereMonth('tgl', $month)
                    ->whereYear('tgl', $year)
                    ->sum('nominal');

                if ($Pemasukan > 0) {
                    // Hitung persentase Pemasukan per kategori
                    $totalPemasukan = transaksi::where('jenis', 'Pemasukan')
                        ->where('kategori_id', $kategori->id)
                        // ->whereMonth('tgl', $month)
                        ->whereYear('tgl', $year)
                        ->sum('nominal');

                    $total = transaksi::where('jenis', 'Pemasukan')
                        // ->whereMonth('tgl', $month)
                        ->whereYear('tgl', $year)
                        ->sum('nominal');
                    $persentasePemasukan = $total > 0 ? ($totalPemasukan / $total) * 100 : 0;

                    $dataKategori[] = [
                        'nama' => $kategori->nama,
                        'nominal' => $Pemasukan,
                        'persentase' => number_format($persentasePemasukan, 2),
                    ];
                }
            }
        }

        // Urutkan data kategori berdasarkan persentase terbesar
        usort($dataKategori, function ($a, $b) {
            return $b['persentase'] - $a['persentase'];
        });
        return response()->json([
            'success'    => true,
            'data'    => $dataKategori,
            // 'dataRekap'    => $dataRekap,
        ], 200);
    }
}

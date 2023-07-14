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
        $year = $request->year ? $request->year : date('Y');


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
        $year = $request->year ? $request->year : date('Y');
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
        $year = $request->year ? $request->year : date('Y');

        // dd('aa');
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
        $year = $request->year ? $request->year : date('Y');
        $tipe = $request->tipe ? $request->tipe : "pengeluaran";
        // dd($year);
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


    public function transaksi_detail(Request $request)
    {
        // request month, year
        $month = $request->month ? $request->month : date('m');
        $year = $request->year ? $request->year : date('Y');
        $data = [];
        // Inisialisasi total pemasukan dan pengeluaran
        $totalPemasukan = 0;
        $totalPengeluaran = 0;

        // Ambil tanggal sekarang
        $tanggalSekarang = date('Y-m-d');

        // Ambil data transaksi berdasarkan bulan dan tahun ini
        $transaksi = DB::table('transaksi')
            ->join('kategori', 'transaksi.kategori_id', '=', 'kategori.id')
            ->select('transaksi.*', 'kategori.nama AS kategori_nama')
            ->whereMonth('tgl', $month)
            ->whereYear('tgl', $year)
            ->where('tgl', '<=', $tanggalSekarang)
            ->whereNull('transaksi.deleted_at')
            ->orderBy('tgl', 'desc')
            ->get();

        // Inisialisasi array hasil
        $hasil = [];
        $nomer_trans = 0;
        // Looping data transaksi
        foreach ($transaksi as $item) {
            $tgl = date('Y-m-d', strtotime($item->tgl));

            // Cek apakah tanggal sudah ada dalam hasil
            if (!array_key_exists($tgl, $hasil)) {
                $nomer_trans++;
                // Inisialisasi data tanggal pada hasil
                $hasil[$tgl] = [
                    'id' => $nomer_trans, // Menggunakan tanggal sebagai ID
                    'nama' => $tgl,
                    'pemasukan' => 0,
                    'pengeluaran' => 0,
                    'saldo' => 0,
                    'jml_transaksi' => 0,
                    'detail' => []
                ];
            }

            // Tambahkan transaksi ke detail
            $hasil[$tgl]['detail'][] = $item;

            // Update total pemasukan atau pengeluaran
            if ($item->jenis === 'Pemasukan') {
                $hasil[$tgl]['pemasukan'] += $item->nominal;
                $totalPemasukan += $item->nominal;
            } else {
                $hasil[$tgl]['pengeluaran'] += $item->nominal;
                $totalPengeluaran += $item->nominal;
            }

            // Update jumlah transaksi
            $hasil[$tgl]['jml_transaksi']++;
        }

        // Hitung saldo
        $rekap = [
            'pemasukan' => $totalPemasukan,
            'pengeluaran' => $totalPengeluaran,
            'saldo' => $totalPemasukan - $totalPengeluaran
        ];

        // Ubah bentuk hasil menjadi array objek
        $data = array_values($hasil);

        return response()->json([
            'success' => true,
            'data' => $data,
            'rekap' => $rekap
        ], 200);
    }
    public function transaksi_detail_bulanan(Request $request)
    {
        // request month, year
        $year = $request->year ? $request->year : date('Y');
        $data = [];
        // Ambil bulan ini
        $bulanIni = date('m');
        // Inisialisasi array bulan
        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        // Inisialisasi total pemasukan dan pengeluaran
        $totalPemasukan = 0;
        $totalPengeluaran = 0;

        // Looping bulan
        foreach ($bulan as $id => $namaBulan) {
            // Hanya proses bulan sebelum atau pada bulan ini
            if ($year == date('Y') && $id > $bulanIni) {
                break;
            }
            // Ambil data transaksi per bulan
            $transaksiBulan = DB::table('transaksi')
                ->select('tgl', 'nama', 'jenis', 'nominal')
                ->whereMonth('tgl', $id)
                ->whereYear('tgl', $year)
                ->whereNull('deleted_at')
                ->get();

            // Inisialisasi total pemasukan dan pengeluaran
            $totalPemasukanBulan = 0;
            $totalPengeluaranBulan = 0;
            $jmlTransaksi = $transaksiBulan->count(); // Menghitung jumlah transaksi

            // Looping data transaksi
            foreach ($transaksiBulan as $item) {
                if ($item->jenis === 'Pemasukan') {
                    $totalPemasukanBulan += $item->nominal;
                } else {
                    $totalPengeluaranBulan += $item->nominal;
                }
            }

            // Hitung saldo
            $saldoBulan = $totalPemasukanBulan - $totalPengeluaranBulan;

            // Tambahkan data bulan ke hasil
            $data[] = [
                'id' => $id,
                'nama' => $namaBulan,
                'pemasukan' => $totalPemasukanBulan,
                'pengeluaran' => $totalPengeluaranBulan,
                'saldo' => $saldoBulan,
                'jml_transaksi' => $jmlTransaksi
            ];

            // Update total pemasukan dan pengeluaran secara keseluruhan
            $totalPemasukan += $totalPemasukanBulan;
            $totalPengeluaran += $totalPengeluaranBulan;
        }

        // Hitung saldo
        $saldo = $totalPemasukan - $totalPengeluaran;

        // Buat array rekap
        $rekap = [
            'pemasukan' => $totalPemasukan,
            'pengeluaran' => $totalPengeluaran,
            'saldo' => $saldo
        ];

        // Urutkan hasil secara descending berdasarkan ID bulan
        usort($data, function ($a, $b) {
            return $b['id'] - $a['id'];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'rekap' => $rekap,
        ], 200);
    }
    public function transaksi_detail_tahunan(Request $request)
    {
        $hasil = [];
        // Ambil tahun ini
        $tahunIni = date('Y');

        // Inisialisasi array hasil
        $hasil = [];

        // Ambil data transaksi per tahun
        $transaksiTahun = DB::table('transaksi')
            ->select(DB::raw('YEAR(tgl) as tahun'), 'jenis', DB::raw('SUM(nominal) as total_nominal'), DB::raw('COUNT(*) as jml_transaksi'))
            ->whereYear('tgl', '<=', $tahunIni)
            ->whereNull('deleted_at')
            ->groupBy('tahun', 'jenis')
            ->get();

        // Inisialisasi total pemasukan dan pengeluaran
        $totalPemasukan = 0;
        $totalPengeluaran = 0;

        // Looping data transaksi per tahun
        $index = 1; // Inisialisasi index
        foreach ($transaksiTahun as $item) {
            $tahun = $item->tahun;

            // Cek apakah tahun sudah ada dalam hasil
            if (!array_key_exists($tahun, $hasil)) {
                // Inisialisasi data tahun pada hasil
                $hasil[$tahun] = [
                    'id' => $index, // Contoh mengambil 2 digit terakhir dari tahun sebagai ID
                    'nama' => $tahun,
                    'pemasukan' => 0,
                    'pengeluaran' => 0,
                    'saldo' => 0,
                    'jml_transaksi' => 0
                ];
                $index++; // Increment index
            }

            // Update total pemasukan, pengeluaran, saldo, dan jumlah transaksi
            if ($item->jenis === 'Pemasukan') {
                $hasil[$tahun]['pemasukan'] += $item->total_nominal;
                $totalPemasukan += $item->total_nominal;
            } else {
                $hasil[$tahun]['pengeluaran'] += $item->total_nominal;
                $totalPengeluaran += $item->total_nominal;
            }

            $hasil[$tahun]['saldo'] = $hasil[$tahun]['pemasukan'] - $hasil[$tahun]['pengeluaran'];
            $hasil[$tahun]['jml_transaksi'] += $item->jml_transaksi;
        }

        // Hitung saldo
        $rekap = [
            'pemasukan' => $totalPemasukan,
            'pengeluaran' => $totalPengeluaran,
            'saldo' => $totalPemasukan - $totalPengeluaran
        ];

        // Urutkan hasil berdasarkan tahun secara menurun
        krsort($hasil);

        return response()->json([
            'success' => true,
            'data' => array_values($hasil), // Mengambil nilai-nilai objek hasil sebagai array
            'rekap' => $rekap
        ], 200);
    }
}

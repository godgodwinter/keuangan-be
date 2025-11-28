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
        $user  = Auth::guard()->user();
        $month = $request->input('month', date('m'));
        $year  = $request->input('year', date('Y'));

        // Base query per bulan & tahun, nanti dipakai ulang
        $baseQuery = transaksi::whereMonth('tgl', $month)
            ->whereYear('tgl', $year)
            ->when($user->id != 1, function ($q) use ($user) {
                $q->where('users_id', $user->id);
            });

        // Ambil data pemasukan & pengeluaran
        $pemasukan = (clone $baseQuery)
            ->where('jenis', 'Pemasukan')
            ->sum('nominal');

        $pengeluaran = (clone $baseQuery)
            ->where('jenis', 'Pengeluaran')
            ->sum('nominal');

        $data = [];

        if ($pengeluaran + $pemasukan > 0) {
            $total                  = $pemasukan + $pengeluaran;
            $persentasePemasukan    = $total > 0 ? ($pemasukan / $total) * 100 : 0;
            $persentasePengeluaran  = $total > 0 ? ($pengeluaran / $total) * 100 : 0;

            $data[] = [
                'id'         => 1,
                'nama'       => 'Pemasukan',
                'nominal'    => $pemasukan,
                'persentase' => number_format($persentasePemasukan, 2),
            ];

            $data[] = [
                'id'         => 2,
                'nama'       => 'Pengeluaran',
                'nominal'    => $pengeluaran,
                'persentase' => number_format($persentasePengeluaran, 2),
            ];

            // Urutkan berdasarkan persentase terbesar
            usort($data, function ($a, $b) {
                return $b['persentase'] <=> $a['persentase'];
            });
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ], 200);
    }



    public function rekap_kategori(Request $request)
    {
        $user  = Auth::guard()->user();
        $month = $request->input('month', date('m'));
        $year  = $request->input('year', date('Y'));
        $tipe  = $request->input('tipe', 'pengeluaran'); // "pengeluaran" / "pemasukan"

        $dataKategori = [];

        if ($tipe === 'pengeluaran') {
            // Base query semua pengeluaran bulan + tahun ini
            $baseQuery = transaksi::where('jenis', 'Pengeluaran')
                ->whereMonth('tgl', $month)
                ->whereYear('tgl', $year)
                ->when($user->id != 1, function ($q) use ($user) {
                    $q->where('users_id', $user->id);
                });

            $totalSemua = (clone $baseQuery)->sum('nominal');

            $kategoriPengeluaran = kategori::where('jenis', 'Pengeluaran')->get();

            foreach ($kategoriPengeluaran as $kategori) {
                $nominal = (clone $baseQuery)
                    ->where('kategori_id', $kategori->id)
                    ->sum('nominal');

                if ($nominal > 0) {
                    $persentase = $totalSemua > 0 ? ($nominal / $totalSemua) * 100 : 0;

                    $dataKategori[] = [
                        'id'         => $kategori->id,
                        'nama'       => $kategori->nama,
                        'nominal'    => $nominal,
                        'persentase' => number_format($persentase, 2),
                    ];
                }
            }
        } else {
            // Base query semua pemasukan bulan + tahun ini
            $baseQuery = transaksi::where('jenis', 'Pemasukan')
                ->whereMonth('tgl', $month)
                ->whereYear('tgl', $year)
                ->when($user->id != 1, function ($q) use ($user) {
                    $q->where('users_id', $user->id);
                });

            $totalSemua = (clone $baseQuery)->sum('nominal');

            $kategoriPemasukan = kategori::where('jenis', 'Pemasukan')->get();

            foreach ($kategoriPemasukan as $kategori) {
                $nominal = (clone $baseQuery)
                    ->where('kategori_id', $kategori->id)
                    ->sum('nominal');

                if ($nominal > 0) {
                    $persentase = $totalSemua > 0 ? ($nominal / $totalSemua) * 100 : 0;

                    $dataKategori[] = [
                        'id'         => $kategori->id,
                        'nama'       => $kategori->nama,
                        'nominal'    => $nominal,
                        'persentase' => number_format($persentase, 2),
                    ];
                }
            }
        }

        // Urutkan data kategori berdasarkan persentase terbesar
        usort($dataKategori, function ($a, $b) {
            return $b['persentase'] <=> $a['persentase'];
        });

        return response()->json([
            'success' => true,
            'data'    => $dataKategori,
        ], 200);
    }



    public function rekap_kategori_detail(Request $request, kategori $kategori_id)
    {
        $user = Auth::guard()->user();

        $k_id  = $kategori_id->id;
        $month = $request->input('month', date('m'));
        $year  = $request->input('year', date('Y'));
        $hasil = [];

        $tanggalSekarang = date('Y-m-d');

        $query = DB::table('transaksi')
            ->join('kategori', 'transaksi.kategori_id', '=', 'kategori.id')
            ->leftJoin('users', 'transaksi.users_id', '=', 'users.id')
            ->select(
                'transaksi.*',
                'kategori.nama AS kategori_nama',
                'users.nama AS users_nama',
                'users.username AS users_username'
            )
            ->whereMonth('transaksi.tgl', $month)
            ->whereYear('transaksi.tgl', $year)
            ->where('transaksi.kategori_id', $k_id)
            ->where('transaksi.tgl', '<=', $tanggalSekarang)
            ->whereNull('transaksi.deleted_at')
            ->orderBy('transaksi.tgl', 'desc');

        // Filter non-admin per user
        if ($user->id != 1) {
            $query->where('transaksi.users_id', $user->id);
        }

        $transaksi = $query->get();

        $jml_tgl       = 0;
        $jml_trans     = 0;
        $total_nominal = 0;

        foreach ($transaksi as $item) {
            $tgl = date('Y-m-d', strtotime($item->tgl));

            // cari index tanggal di $hasil
            $tglIndex = null;
            foreach ($hasil as $key => $data) {
                if ($data['nama'] === $tgl) {
                    $tglIndex = $key;
                    break;
                }
            }

            // jika belum ada, inisialisasi
            if ($tglIndex === null) {
                $tglIndex   = count($hasil);
                $hasil[]    = [
                    'id'           => $tglIndex + 1,
                    'nama'         => $tgl,
                    'total_nominal' => 0,
                    'detail'       => [],
                ];
                $jml_tgl++;
            }

            // Tambah properti milik_sendiri
            $item->milik_sendiri = ($item->users_id == $user->id);

            // Tambahkan ke detail
            $hasil[$tglIndex]['detail'][] = $item;
            $jml_trans++;
            $hasil[$tglIndex]['total_nominal'] += $item->nominal;
            $total_nominal                  += $item->nominal;
        }

        $rekap = [
            'jml_tgl'        => $jml_tgl,
            'jml_transaksi'  => $jml_trans,
            'kategori_nama'  => $kategori_id->nama,
            'total_nominal'  => $total_nominal,
        ];

        return response()->json([
            'success' => true,
            'data'    => $hasil,
            'rekap'   => $rekap,
        ], 200);
    }


    public function pertahun_ringkasan(Request $request)
    {
        $user = Auth::guard()->user();
        $year = $request->input('year', date('Y'));

        $baseQuery = transaksi::whereYear('tgl', $year)
            ->when($user->id != 1, function ($q) use ($user) {
                $q->where('users_id', $user->id);
            });

        $pemasukan = (clone $baseQuery)
            ->where('jenis', 'Pemasukan')
            ->sum('nominal');

        $pengeluaran = (clone $baseQuery)
            ->where('jenis', 'Pengeluaran')
            ->sum('nominal');

        $data = [];

        if ($pengeluaran + $pemasukan > 0) {
            $total                 = $pemasukan + $pengeluaran;
            $persentasePemasukan   = $total > 0 ? ($pemasukan / $total) * 100 : 0;
            $persentasePengeluaran = $total > 0 ? ($pengeluaran / $total) * 100 : 0;

            $data[] = [
                'id'         => 1,
                'nama'       => 'Pemasukan',
                'nominal'    => $pemasukan,
                'persentase' => number_format($persentasePemasukan, 2),
            ];

            $data[] = [
                'id'         => 2,
                'nama'       => 'Pengeluaran',
                'nominal'    => $pengeluaran,
                'persentase' => number_format($persentasePengeluaran, 2),
            ];

            usort($data, function ($a, $b) {
                return $b['persentase'] <=> $a['persentase'];
            });
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ], 200);
    }


    public function pertahun_rekap_kategori(Request $request)
    {
        $user = Auth::guard()->user();

        $year = $request->input('year', date('Y'));
        $tipe = $request->input('tipe', 'pengeluaran'); // "pengeluaran" / "pemasukan"

        $dataKategori = [];

        if ($tipe === 'pengeluaran') {
            $baseQuery = transaksi::where('jenis', 'Pengeluaran')
                ->whereYear('tgl', $year)
                ->when($user->id != 1, function ($q) use ($user) {
                    $q->where('users_id', $user->id);
                });

            $totalSemua = (clone $baseQuery)->sum('nominal');

            $kategoriPengeluaran = kategori::where('jenis', 'Pengeluaran')->get();

            foreach ($kategoriPengeluaran as $kategori) {
                $nominal = (clone $baseQuery)
                    ->where('kategori_id', $kategori->id)
                    ->sum('nominal');

                if ($nominal > 0) {
                    $persentase = $totalSemua > 0 ? ($nominal / $totalSemua) * 100 : 0;

                    $dataKategori[] = [
                        'nama'       => $kategori->nama,
                        'nominal'    => $nominal,
                        'persentase' => number_format($persentase, 2),
                    ];
                }
            }
        } else {
            $baseQuery = transaksi::where('jenis', 'Pemasukan')
                ->whereYear('tgl', $year)
                ->when($user->id != 1, function ($q) use ($user) {
                    $q->where('users_id', $user->id);
                });

            $totalSemua = (clone $baseQuery)->sum('nominal');

            $kategoriPemasukan = kategori::where('jenis', 'Pemasukan')->get();

            foreach ($kategoriPemasukan as $kategori) {
                $nominal = (clone $baseQuery)
                    ->where('kategori_id', $kategori->id)
                    ->sum('nominal');

                if ($nominal > 0) {
                    $persentase = $totalSemua > 0 ? ($nominal / $totalSemua) * 100 : 0;

                    $dataKategori[] = [
                        'nama'       => $kategori->nama,
                        'nominal'    => $nominal,
                        'persentase' => number_format($persentase, 2),
                    ];
                }
            }
        }

        usort($dataKategori, function ($a, $b) {
            return $b['persentase'] <=> $a['persentase'];
        });

        return response()->json([
            'success' => true,
            'data'    => $dataKategori,
        ], 200);
    }



    public function transaksi_detail_less(Request $request)
    {
        $user  = Auth::guard()->user();

        // request month, year
        $month = $request->input('month', date('m'));
        $year  = $request->input('year', date('Y'));

        // Inisialisasi total pemasukan dan pengeluaran
        $totalPemasukan   = 0;
        $totalPengeluaran = 0;

        // Ambil tanggal sekarang
        $tanggalSekarang = date('Y-m-d');

        // Ambil data transaksi berdasarkan bulan dan tahun ini
        $query = DB::table('transaksi')
            ->join('kategori', 'transaksi.kategori_id', '=', 'kategori.id')
            ->leftJoin('users', 'transaksi.users_id', '=', 'users.id')
            ->select(
                'transaksi.*',
                'kategori.nama AS kategori_nama',
                'users.nama AS users_nama',
                'users.username AS users_username'
            )
            ->whereMonth('transaksi.tgl', $month)
            ->whereYear('transaksi.tgl', $year)
            ->where('transaksi.tgl', '<=', $tanggalSekarang)
            ->whereNull('transaksi.deleted_at')
            ->orderBy('transaksi.tgl', 'desc');

        // Jika bukan admin → filter berdasarkan users_id
        if ($user->id != 1) {
            $query->where('transaksi.users_id', $user->id);
        }

        $transaksi = $query->get();

        // Inisialisasi array hasil
        $hasil       = [];
        $nomer_trans = 0;

        // Looping data transaksi
        foreach ($transaksi as $item) {
            $tgl = date('Y-m-d', strtotime($item->tgl));

            // Cek apakah tanggal sudah ada dalam hasil
            if (!array_key_exists($tgl, $hasil)) {
                $nomer_trans++;
                // Inisialisasi data tanggal pada hasil
                $hasil[$tgl] = [
                    'id'            => $nomer_trans,
                    'nama'          => $tgl,
                    'pemasukan'     => 0,
                    'pengeluaran'   => 0,
                    'saldo'         => 0,
                    'jml_transaksi' => 0,
                    'detail'        => [],
                ];
            }

            // Tambahkan flag milik_sendiri di item
            $item->milik_sendiri = ($item->users_id == $user->id);

            // Tambahkan transaksi ke detail
            $hasil[$tgl]['detail'][] = $item;

            // Update total pemasukan atau pengeluaran
            if ($item->jenis === 'Pemasukan') {
                $hasil[$tgl]['pemasukan'] += $item->nominal;
                $totalPemasukan           += $item->nominal;
            } else {
                $hasil[$tgl]['pengeluaran'] += $item->nominal;
                $totalPengeluaran           += $item->nominal;
            }

            // Update saldo per tanggal & jumlah transaksi
            $hasil[$tgl]['saldo'] = $hasil[$tgl]['pemasukan'] - $hasil[$tgl]['pengeluaran'];
            $hasil[$tgl]['jml_transaksi']++;
        }

        // Hitung saldo global
        $rekap = [
            'pemasukan'   => $totalPemasukan,
            'pengeluaran' => $totalPengeluaran,
            'saldo'       => $totalPemasukan - $totalPengeluaran,
        ];

        // Potong hasil (misal cuma ambil beberapa tanggal / transaksi terakhir)
        $hasil_shorted = $this->fn_potongHasil($hasil);

        // Ubah bentuk hasil menjadi array numerik
        $data = array_values($hasil_shorted);

        return response()->json([
            'success' => true,
            'data'    => $data,
            'rekap'   => $rekap,
            'filter'  => [
                'month' => $month,
                'year'  => $year,
            ],
        ], 200);
    }


    function fn_potongHasil($hasil)
    {
        $hasilBaru = [];

        // foreach ($hasil as $data) {
        //     $dataBaru = $data;
        //     $dataBaru['detail'] = array_slice($data['detail'], 0, 2); // Potong maksimal 2 data detail
        //     $hasilBaru[] = $dataBaru;
        // }

        return array_slice($hasil, 0, 5);;
    }
    public function transaksi_detail(Request $request)
    {
        $user = Auth::guard()->user();

        // request month, year (pakai input() + default ke tanggal sekarang)
        $month = $request->input('month', date('m'));
        $year  = $request->input('year', date('Y'));

        // Inisialisasi total pemasukan dan pengeluaran
        $totalPemasukan   = 0;
        $totalPengeluaran = 0;

        // Ambil tanggal sekarang
        $tanggalSekarang = date('Y-m-d');

        // Query dasar: join kategori + users
        $query = DB::table('transaksi')
            ->join('kategori', 'transaksi.kategori_id', '=', 'kategori.id')
            ->join('users', 'transaksi.users_id', '=', 'users.id')
            ->select(
                'transaksi.*',
                'kategori.nama AS kategori_nama',
                'users.nama AS users_nama',
                'users.username AS users_username'
            )
            ->whereMonth('tgl', $month)
            ->whereYear('tgl', $year)
            ->where('tgl', '<=', $tanggalSekarang)
            ->whereNull('transaksi.deleted_at')
            ->orderBy('tgl', 'desc');

        // Jika bukan admin → filter berdasarkan users_id
        if ($user->id != 1) {
            $query->where('transaksi.users_id', $user->id);
        }

        $transaksi = $query->get();

        // Inisialisasi array hasil
        $hasil = [];
        $nomer_trans = 0;

        // Looping data transaksi
        foreach ($transaksi as $item) {
            $tgl = date('Y-m-d', strtotime($item->tgl));

            // Kalau tanggal belum ada di hasil → inisialisasi
            if (!array_key_exists($tgl, $hasil)) {
                $nomer_trans++;
                $hasil[$tgl] = [
                    'id'            => $nomer_trans,
                    'nama'          => $tgl,
                    'pemasukan'     => 0,
                    'pengeluaran'   => 0,
                    'saldo'         => 0,
                    'jml_transaksi' => 0,
                    'detail'        => [],
                ];
            }

            // Tentukan apakah transaksi milik user yang sedang login
            $milikSendiri = ($item->users_id == $user->id);

            // Bentuk detail transaksi dengan properti tambahan
            $detail = (object)[
                'id'             => $item->id,
                'users_id'       => $item->users_id,
                'users_nama'     => $item->users_nama,
                'users_username' => $item->users_username,
                'milik_sendiri'  => $milikSendiri,
                'kategori_id'    => $item->kategori_id,
                'kategori_nama'  => $item->kategori_nama,
                'tgl'            => $item->tgl,
                'nama'           => $item->nama,
                'jenis'          => $item->jenis,
                'nominal'        => $item->nominal,
                'created_at'     => $item->created_at,
                'updated_at'     => $item->updated_at,
            ];

            // Tambahkan transaksi ke detail per tanggal
            $hasil[$tgl]['detail'][] = $detail;

            // Update total pemasukan/pengeluaran per tanggal & global
            if ($item->jenis === 'Pemasukan') {
                $hasil[$tgl]['pemasukan'] += $item->nominal;
                $totalPemasukan          += $item->nominal;
            } else {
                $hasil[$tgl]['pengeluaran'] += $item->nominal;
                $totalPengeluaran          += $item->nominal;
            }

            // Update jumlah transaksi per tanggal
            $hasil[$tgl]['jml_transaksi']++;
        }

        // Hitung saldo global
        $rekap = [
            'pemasukan'   => $totalPemasukan,
            'pengeluaran' => $totalPengeluaran,
            'saldo'       => $totalPemasukan - $totalPengeluaran,
        ];

        // Ubah bentuk hasil menjadi array numerik
        $data = array_values($hasil);

        return response()->json([
            'success' => true,
            'data'    => $data,
            'rekap'   => $rekap,
            'filter'  => [
                'month' => $month,
                'year'  => $year,
            ],
        ], 200);
    }
    public function transaksi_detail_bulanan(Request $request)
    {
        $user = Auth::guard()->user();

        // request year, default ke tahun sekarang
        $year = $request->input('year', date('Y'));

        $data = [];

        // Bulan sekarang (dalam bentuk integer)
        $bulanIni = (int) date('m');

        // Inisialisasi array bulan
        $bulan = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // Inisialisasi total pemasukan dan pengeluaran (setahun)
        $totalPemasukan   = 0;
        $totalPengeluaran = 0;

        // Looping 12 bulan
        foreach ($bulan as $id => $namaBulan) {
            // Kalau tahun yang diminta adalah tahun ini,
            // jangan proses bulan setelah bulan berjalan
            if ((int) $year === (int) date('Y') && $id > $bulanIni) {
                break;
            }

            // Query dasar per bulan
            $query = DB::table('transaksi')
                ->select('tgl', 'nama', 'jenis', 'nominal')
                ->whereMonth('tgl', $id)
                ->whereYear('tgl', $year)
                ->whereNull('deleted_at');

            // Kalau bukan admin → filter per user
            if ($user->id != 1) {
                $query->where('users_id', $user->id);
            }

            $transaksiBulan = $query->get();

            // Inisialisasi total pemasukan dan pengeluaran per bulan
            $totalPemasukanBulan   = 0;
            $totalPengeluaranBulan = 0;
            $jmlTransaksi          = $transaksiBulan->count();

            // Looping data transaksi per bulan
            foreach ($transaksiBulan as $item) {
                if ($item->jenis === 'Pemasukan') {
                    $totalPemasukanBulan += $item->nominal;
                } else {
                    $totalPengeluaranBulan += $item->nominal;
                }
            }

            // Hitung saldo per bulan
            $saldoBulan = $totalPemasukanBulan - $totalPengeluaranBulan;

            // Tambahkan data bulan ke hasil
            $data[] = [
                'id'             => $id,
                'nama'           => $namaBulan,
                'pemasukan'      => $totalPemasukanBulan,
                'pengeluaran'    => $totalPengeluaranBulan,
                'saldo'          => $saldoBulan,
                'jml_transaksi'  => $jmlTransaksi,
            ];

            // Update total tahunan
            $totalPemasukan   += $totalPemasukanBulan;
            $totalPengeluaran += $totalPengeluaranBulan;
        }

        // Hitung saldo tahunan
        $saldo = $totalPemasukan - $totalPengeluaran;

        // Buat array rekap tahunan
        $rekap = [
            'pemasukan'   => $totalPemasukan,
            'pengeluaran' => $totalPengeluaran,
            'saldo'       => $saldo,
        ];

        // Urutkan hasil secara descending berdasarkan ID bulan (Desember → Januari)
        usort($data, function ($a, $b) {
            return $b['id'] <=> $a['id'];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
            'rekap'   => $rekap,
            'filter'  => [
                'year' => $year,
            ],
        ], 200);
    }

    public function transaksi_detail_tahunan(Request $request)
    {
        $user = Auth::guard()->user();

        // Tahun maksimum yang direkap (default: tahun ini)
        $tahunMaks = (int) $request->input('max_year', date('Y'));

        // Ambil data transaksi per tahun, dikelompokkan per tahun & jenis
        $transaksiTahun = DB::table('transaksi')
            ->select(
                DB::raw('YEAR(tgl) as tahun'),
                'jenis',
                DB::raw('SUM(nominal) as total_nominal'),
                DB::raw('COUNT(*) as jml_transaksi')
            )
            ->whereYear('tgl', '<=', $tahunMaks)
            ->whereNull('deleted_at')
            ->when($user->id != 1, function ($q) use ($user) {
                // Jika bukan admin → filter berdasarkan users_id
                $q->where('users_id', $user->id);
            })
            ->groupBy('tahun', 'jenis')
            ->orderBy('tahun', 'desc')
            ->get();

        $hasil = [];

        // Inisialisasi total pemasukan & pengeluaran seluruh tahun
        $totalPemasukan   = 0;
        $totalPengeluaran = 0;

        // Looping data transaksi per tahun
        foreach ($transaksiTahun as $item) {
            $tahun = $item->tahun;

            // Kalau tahun belum ada di array hasil → inisialisasi
            if (!array_key_exists($tahun, $hasil)) {
                $hasil[$tahun] = [
                    'id'             => 0, // sementara, nanti diisi ulang
                    'nama'           => $tahun,
                    'pemasukan'      => 0,
                    'pengeluaran'    => 0,
                    'saldo'          => 0,
                    'jml_transaksi'  => 0,
                ];
            }

            // Update total pemasukan / pengeluaran per tahun & global
            if ($item->jenis === 'Pemasukan') {
                $hasil[$tahun]['pemasukan'] += $item->total_nominal;
                $totalPemasukan             += $item->total_nominal;
            } else {
                $hasil[$tahun]['pengeluaran'] += $item->total_nominal;
                $totalPengeluaran            += $item->total_nominal;
            }

            // Hitung saldo per tahun
            $hasil[$tahun]['saldo'] = $hasil[$tahun]['pemasukan'] - $hasil[$tahun]['pengeluaran'];

            // Tambah jumlah transaksi per tahun
            $hasil[$tahun]['jml_transaksi'] += $item->jml_transaksi;
        }

        // Konversi ke array numerik & assign id urut (descending by tahun)
        $data = [];
        $index = 1;
        foreach ($hasil as $tahun => $row) {
            $row['id'] = $index++;
            $data[]    = $row;
        }

        // Rekap global
        $rekap = [
            'pemasukan'   => $totalPemasukan,
            'pengeluaran' => $totalPengeluaran,
            'saldo'       => $totalPemasukan - $totalPengeluaran,
        ];

        return response()->json([
            'success' => true,
            'data'    => $data,
            'rekap'   => $rekap,
            'filter'  => [
                'max_year' => $tahunMaks,
            ],
        ], 200);
    }
}

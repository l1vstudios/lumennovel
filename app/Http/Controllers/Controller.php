<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;
class Controller extends BaseController
{
    public function getKategori()
    {
        try {
            $data = DB::table('mst_kategori')->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data kategori.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getCerita()
    {
        try {
            $data = DB::table('mst_cerita')
                ->select('mst_cerita.*', 'mst_kategori.default_title')
                ->join('mst_kategori', 'mst_cerita.id_kategori', '=', 'mst_kategori.id')
                ->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data cerita.',
                'error_detail' => $e->getMessage()
            ], 500)
            ->header('Access-Control-Allow-Origin', '*');
        }
    }
    public function getCeritaById($id)
    {
        try {
            $data = DB::table('mst_cerita')
                ->select('mst_cerita.*', 'mst_kategori.default_title')
                ->join('mst_kategori', 'mst_cerita.id_kategori', '=', 'mst_kategori.id')
                ->where('mst_cerita.id', $id)
                ->first();

            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data cerita tidak ditemukan.'
                ], 404)
                ->header('Access-Control-Allow-Origin', '*');
            }

            return response()->json([
                'status' => 'success',
                'results' => $data
            ], 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail cerita.',
                'error_detail' => $e->getMessage()
            ], 500)
            ->header('Access-Control-Allow-Origin', '*');
        }
    }
    public function getAction()
    {
        try {
            $data = DB::table('mst_action')->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data action.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getSliders()
    {
        try {
            $data = DB::table('mst_sliders')
                ->where('status', true)
                ->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data sliders.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
     public function getNotifikasi()
    {
        try {
            $data = DB::table('mst_notifikasi')->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data notifikasi.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getUser()
    {
        try {
            $data = DB::table('mst_user')->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data user.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getVersion()
    {
        try {
            $data = DB::table('mst_versions')->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data version.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function insertCeritaPanjang()
    {
       try {
        $dataCerita = [
            ['judul' => 'The Roses Of Night', 'kategori' => 4],
            ['judul' => 'Dear You', 'kategori' => 1],
            ['judul' => 'Senja', 'kategori' => 1],
            ['judul' => 'Kala Senja Menyapa', 'kategori' => 1],
            ['judul' => 'Sang Penerang', 'kategori' => 4],
            ['judul' => 'Di Taman Puisi', 'kategori' => 1],
            ['judul' => 'Buku Catatan', 'kategori' => 4],
            ['judul' => 'Deeper Love', 'kategori' => 1],
            ['judul' => 'Cinta Tanpa Jeda', 'kategori' => 1],
            ['judul' => 'Tujuh Kelana', 'kategori' => 2],
        ];
        $payload = [];
        foreach ($dataCerita as $item) {
            $isiCerita = [
                "chapter 1" => "Awal mula kisah " . $item['judul'] . " dimulai di sini. Narasi panjang mengalir menceritakan pengenalan karakter utama dan latar belakang dunia yang sedang dibangun untuk memikat pembaca sejak baris pertama.",
                "chapter 2" => "Konflik mulai muncul di bab kedua ini. Ketegangan meningkat saat rahasia mulai terungkap dan tantangan besar menghadang langkah sang tokoh utama dalam perjalanan " . $item['judul'] . ".",
                "chapter 3" => "Puncak emosional terjadi di bab ketiga. Semua taruhan dikerahkan, dan karakter harus memilih antara idealisme atau kenyataan pahit yang harus mereka hadapi demi mencapai tujuan akhir.",
                "chapter 4" => "Resolusi dan konklusi sementara. Bab ini menutup bagian pertama dari " . $item['judul'] . " dengan sebuah 'cliffhanger' atau penyelesaian yang manis, memberikan kepuasan bagi para pembaca setia."
            ];
            $payload[] = [
                'judul'         => $item['judul'],
                'parts'         => 4,
                'isi_cerita'    => json_encode($isiCerita),
                'status'        => true,
                'total_read'    => rand(100, 5000),
                'total_vote'    => rand(10, 1000),
                'total_share'   => rand(5, 500),
                'recomendation' => (bool)rand(0, 1),
                'wajib_dibaca'  => (bool)rand(0, 1),
                'id_kategori'   => $item['kategori'],
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ];
        }
        DB::table('mst_cerita')->insert($payload);
        return response()->json([
            'status' => 'success',
            'message' => '10 Judul cerita berhasil ditambahkan ke database novel_mora'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }}
    public function getTest()
    {
       dd(1);
    }
}

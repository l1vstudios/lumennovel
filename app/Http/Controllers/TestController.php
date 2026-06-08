<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Exception;
class TestController extends Controller
{
    public function checkConnection()
    {
        try {
            $dbName = DB::connection()->getDatabaseName();
            return response()->json([
                'status' => 'success',
                'message' => 'Koneksi Berhasil!',
                'database' => $dbName,
                'php_version' => phpversion()
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung ke database.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}

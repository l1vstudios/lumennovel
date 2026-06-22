<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;
class Controller extends BaseController
{
    // OAuth 2.0 "Web client" ID dari Firebase/GCP. WAJIB sama dengan webClientId
    // di aplikasi (data/googleAuth.ts). id_token Google hanya diterima jika
    // klaim "aud" cocok dengan salah satu ID di bawah ini.
    // Tambahkan iOS client ID juga bila aplikasi iOS memakai iosClientId.
    private const GOOGLE_ALLOWED_CLIENT_IDS = [
        '438568225178-pql53tjm7iul6ar54rf3fvct79p1sasf.apps.googleusercontent.com',
    ];

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
            $data = DB::table('mst_users')
                ->select('id', 'name', 'email', 'google_id', 'google_avatar', 'email_verified_at', 'last_login_at', 'auth_provider', 'created_at', 'updated_at')
                ->get();
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
    public function register(Request $request)
    {
        $name = trim((string) $request->input('name', ''));
        $email = strtolower(trim((string) $request->input('email', '')));
        $password = (string) $request->input('password', '');

        if ($name === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'name wajib diisi.'
            ], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status' => 'error',
                'message' => 'email tidak valid.'
            ], 422);
        }

        if (strlen($password) < 6) {
            return response()->json([
                'status' => 'error',
                'message' => 'password minimal 6 karakter.'
            ], 422);
        }

        try {
            $emailExists = DB::table('mst_users')
                ->whereRaw('LOWER(email) = ?', [$email])
                ->exists();

            if ($emailExists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email sudah terdaftar.'
                ], 409);
            }

            $now = date('Y-m-d H:i:s');
            $token = $this->generateApiToken();
            $userId = DB::table('mst_users')->insertGetId([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'api_token' => $token,
                'auth_provider' => 'local',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $user = DB::table('mst_users')->where('id', $userId)->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Register berhasil.',
                'token' => $token,
                'results' => $this->formatUser($user),
            ], 201);
        } catch (\Throwable $e) {
            if (strpos($e->getMessage(), 'mst_users_email_unique') !== false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email sudah terdaftar.'
                ], 409);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal register.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        $password = (string) $request->input('password', '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'email dan password wajib diisi.'
            ], 422);
        }

        try {
            $user = DB::table('mst_users')
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();

            if (!$user || $user->password === null || $user->password === '') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengguna tidak ditemukan.'
                ], 401);
            }

            $passwordInfo = password_get_info((string) $user->password);
            $isHashedPassword = $passwordInfo['algo'] !== 0;
            $passwordValid = $isHashedPassword
                ? password_verify($password, (string) $user->password)
                : hash_equals((string) $user->password, $password);

            if (!$passwordValid) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengguna tidak ditemukan.'
                ], 401);
            }

            $now = date('Y-m-d H:i:s');
            $token = $this->generateApiToken();
            $updateData = [
                'api_token' => $token,
                'last_login_at' => $now,
                'updated_at' => $now,
            ];

            if (!$isHashedPassword) {
                $updateData['password'] = password_hash($password, PASSWORD_BCRYPT);
            }

            DB::table('mst_users')->where('id', $user->id)->update($updateData);

            $user = DB::table('mst_users')->where('id', $user->id)->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil.',
                'token' => $token,
                'results' => $this->formatUser($user),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal login.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    public function googleLogin(Request $request)
    {
        $idToken = (string) $request->input('id_token', '');

        if ($idToken === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'id_token wajib diisi.'
            ], 422);
        }

        $payload = $this->verifyGoogleIdToken($idToken);
        if ($payload === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token Google tidak valid atau kedaluwarsa.'
            ], 401);
        }

        $googleId = (string) ($payload['sub'] ?? '');
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $name = trim((string) ($payload['name'] ?? ''));
        $avatar = (string) ($payload['picture'] ?? '');
        $emailVerified = ($payload['email_verified'] ?? 'false') === true
            || ($payload['email_verified'] ?? 'false') === 'true';

        if ($googleId === '' || $email === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Data akun Google tidak lengkap.'
            ], 422);
        }

        try {
            $now = date('Y-m-d H:i:s');
            $token = $this->generateApiToken();

            // 1. Cari berdasarkan google_id, lalu fallback ke email.
            $user = DB::table('mst_users')->where('google_id', $googleId)->first();
            if (!$user) {
                $user = DB::table('mst_users')
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->first();
            }

            if ($user) {
                // 2. User sudah ada -> tautkan google_id & perbarui sesi.
                $updateData = [
                    'google_id' => $googleId,
                    'api_token' => $token,
                    'last_login_at' => $now,
                    'updated_at' => $now,
                ];
                if ($avatar !== '') {
                    $updateData['google_avatar'] = $avatar;
                }
                if (($user->name ?? '') === '' && $name !== '') {
                    $updateData['name'] = $name;
                }
                if ($emailVerified && empty($user->email_verified_at)) {
                    $updateData['email_verified_at'] = $now;
                }

                DB::table('mst_users')->where('id', $user->id)->update($updateData);
                $user = DB::table('mst_users')->where('id', $user->id)->first();
                $statusCode = 200;
            } else {
                // 3. User baru -> buat akun via Google (tanpa password).
                $userId = DB::table('mst_users')->insertGetId([
                    'name' => $name !== '' ? $name : strstr($email, '@', true),
                    'email' => $email,
                    'password' => null,
                    'google_id' => $googleId,
                    'google_avatar' => $avatar !== '' ? $avatar : null,
                    'auth_provider' => 'google',
                    'email_verified_at' => $emailVerified ? $now : null,
                    'api_token' => $token,
                    'last_login_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $user = DB::table('mst_users')->where('id', $userId)->first();
                $statusCode = 201;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Login Google berhasil.',
                'token' => $token,
                'results' => $this->formatUser($user),
            ], $statusCode);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal login Google.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    // Verifikasi id_token Google lewat endpoint tokeninfo (memvalidasi tanda
    // tangan & masa berlaku di sisi Google). Mengembalikan payload klaim jika
    // valid & "aud" cocok dengan client ID kita, atau null jika tidak valid.
    private function verifyGoogleIdToken(string $idToken): ?array
    {
        $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        $payload = json_decode($response, true);
        if (!is_array($payload)) {
            return null;
        }

        // Pastikan token diterbitkan oleh Google.
        $iss = $payload['iss'] ?? '';
        if ($iss !== 'https://accounts.google.com' && $iss !== 'accounts.google.com') {
            return null;
        }

        // Pastikan token ditujukan untuk aplikasi kita (cegah token disusupkan).
        $aud = $payload['aud'] ?? '';
        if (!in_array($aud, self::GOOGLE_ALLOWED_CLIENT_IDS, true)) {
            return null;
        }

        // Pastikan belum kedaluwarsa.
        if (!isset($payload['exp']) || (int) $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    public function markNotifikasiRead(Request $request)
    {
        $userId = $request->input('user_id');
        $notifikasiId = $request->input('notifikasi_id', $request->input('notification_id'));

        if (!filter_var($userId, FILTER_VALIDATE_INT) || (int) $userId < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'user_id wajib diisi dan harus berupa angka lebih dari 0.'
            ], 422);
        }

        if (!filter_var($notifikasiId, FILTER_VALIDATE_INT) || (int) $notifikasiId < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'notifikasi_id wajib diisi dan harus berupa angka lebih dari 0.'
            ], 422);
        }

        $userId = (int) $userId;
        $notifikasiId = (int) $notifikasiId;

        try {
            if (!DB::table('mst_users')->where('id', $userId)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data user tidak ditemukan.'
                ], 404);
            }

            if (!DB::table('mst_notifikasi')->where('id', $notifikasiId)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data notifikasi tidak ditemukan.'
                ], 404);
            }

            $now = date('Y-m-d H:i:s');
            $rows = DB::select(
                "INSERT INTO mst_notifikasi_read (user_id, notifikasi_id, read_at, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?)
                 ON CONFLICT (user_id, notifikasi_id) DO UPDATE
                 SET read_at = EXCLUDED.read_at,
                     updated_at = EXCLUDED.updated_at
                 RETURNING id, user_id, notifikasi_id, read_at, created_at, updated_at",
                [$userId, $notifikasiId, $now, $now, $now]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Notifikasi berhasil ditandai sudah dibaca.',
                'results' => $rows[0],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan status read notifikasi.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    public function getNotifikasiByUser($user_id)
    {
        if (!filter_var($user_id, FILTER_VALIDATE_INT) || (int) $user_id < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'user_id harus berupa angka lebih dari 0.'
            ], 422);
        }

        $userId = (int) $user_id;

        try {
            if (!DB::table('mst_users')->where('id', $userId)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data user tidak ditemukan.'
                ], 404);
            }

            $data = DB::select(
                "SELECT
                    n.*,
                    CASE WHEN r.id IS NULL THEN false ELSE true END AS is_read,
                    r.read_at
                 FROM mst_notifikasi n
                 LEFT JOIN mst_notifikasi_read r
                   ON r.notifikasi_id = n.id
                  AND r.user_id = ?
                 ORDER BY n.id DESC",
                [$userId]
            );

            return response()->json([
                'status' => 'success',
                'count' => count($data),
                'results' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data notifikasi user.',
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
    public function trxRead(Request $request)
    {
        return $this->incrementNovelCounter($request, 'read');
    }

    public function trxVote(Request $request)
    {
        return $this->incrementNovelCounter($request, 'vote');
    }

    public function trxShare(Request $request)
    {
        return $this->incrementNovelCounter($request, 'share');
    }

    private function formatUser($user): array
    {
        $data = (array) $user;
        unset($data['password']);

        return $data;
    }

    private function generateApiToken(): string
    {
        return bin2hex(random_bytes(40));
    }

    private function incrementNovelCounter(Request $request, string $type)
    {
        $configs = [
            'read' => [
                'table' => 'trx_read_novel',
                'total_column' => 'total_read',
            ],
            'vote' => [
                'table' => 'trx_vote_novel',
                'total_column' => 'total_vote',
            ],
            'share' => [
                'table' => 'trx_share_novel',
                'total_column' => 'total_share',
            ],
        ];

        if (!isset($configs[$type])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tipe transaksi tidak valid.'
            ], 400);
        }

        $novelId = $request->input('novel_id', $request->input('id'));
        $increment = $request->input('increment', 1);

        if (!filter_var($novelId, FILTER_VALIDATE_INT) || (int)$novelId < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'novel_id wajib diisi dan harus berupa angka lebih dari 0.'
            ], 422);
        }

        if (!filter_var($increment, FILTER_VALIDATE_INT) || (int)$increment < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'increment harus berupa angka lebih dari 0.'
            ], 422);
        }

        $novelId = (int) $novelId;
        $increment = (int) $increment;
        $table = $configs[$type]['table'];
        $totalColumn = $configs[$type]['total_column'];
        $now = date('Y-m-d H:i:s');

        try {
            $result = DB::transaction(function () use ($novelId, $increment, $table, $totalColumn, $now) {
                $totalRows = DB::select(
                    "UPDATE mst_cerita
                     SET {$totalColumn} = (
                         CASE
                             WHEN {$totalColumn}::text ~ '^[0-9]+$' THEN {$totalColumn}::integer
                             ELSE 0
                         END
                     ) + ?, updated_at = ?
                     WHERE id = ?
                     RETURNING {$totalColumn} AS total_count",
                    [$increment, $now, $novelId]
                );

                if (count($totalRows) === 0) {
                    return [
                        'not_found' => true,
                    ];
                }

                $trxRows = DB::select(
                    "INSERT INTO {$table} (novel_id, \"count\", created_at, updated_at)
                     VALUES (?, ?, ?, ?)
                     ON CONFLICT (novel_id) DO UPDATE
                     SET \"count\" = (
                         (
                             CASE
                                 WHEN {$table}.\"count\"::text ~ '^[0-9]+$' THEN {$table}.\"count\"::integer
                                 ELSE 0
                             END
                         ) + EXCLUDED.\"count\"::integer
                     )::varchar,
                     updated_at = EXCLUDED.updated_at
                     RETURNING id, novel_id, \"count\", created_at, updated_at",
                    [$novelId, (string) $increment, $now, $now]
                );

                return [
                    'not_found' => false,
                    'trx' => $trxRows[0],
                    'total_count' => (int) $totalRows[0]->total_count,
                ];
            });

            if ($result['not_found']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data cerita tidak ditemukan.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Counter berhasil ditambahkan.',
                'type' => $type,
                'novel_id' => $novelId,
                'count' => (int) $result['trx']->count,
                'total_count' => $result['total_count'],
                'results' => $result['trx'],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan counter.',
                'error_detail' => $e->getMessage()
            ], 500);
        }

    }
}

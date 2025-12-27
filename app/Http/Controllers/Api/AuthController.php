<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZonaUser;
use App\Models\EWallet;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Models\LoginHistory;
use Jenssegers\Agent\Agent;


class AuthController extends Controller
{
    /**
     * DAFTAR VIA AKUN GOOGLE
     */
    public function googleLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID Token wajib dikirim'
            ], 422);
        }

        try {
            $googleClientId = config('services.google.client_id');

            $response = Http::timeout(5)->get(
                'https://oauth2.googleapis.com/tokeninfo',
                ['id_token' => $request->id_token]
            );

            if (!$response->ok()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token Google tidak valid'
                ], 401);
            }

            $googleUser = $response->json();

            // ✅ VALIDASI LENGKAP SESUAI GOOGLE
            if (
                empty($googleUser['sub']) ||
                empty($googleUser['email']) ||
                empty($googleUser['aud']) ||
                empty($googleUser['iss'])
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token Google tidak lengkap'
                ], 401);
            }

            // ✅ VALIDASI AUD (STRING / ARRAY)
            $aud = $googleUser['aud'];
            if (is_array($aud)) {
                if (!in_array($googleClientId, $aud)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Client ID tidak cocok'
                    ], 401);
                }
            } elseif ($aud !== $googleClientId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client ID tidak cocok'
                ], 401);
            }

            // ✅ VALIDASI ISSUER
            if (!in_array($googleUser['iss'], [
                'accounts.google.com',
                'https://accounts.google.com'
            ])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Issuer token tidak valid'
                ], 401);
            }

            DB::beginTransaction();

            $googleId = $googleUser['sub'];
            $email = $googleUser['email'];
            $name = $googleUser['name']
                ?? explode('@', $email)[0];

            $user = ZonaUser::where('google_id', $googleId)
                ->orWhere('email', $email)
                ->first();

            $isNewUser = false;

            if (!$user) {
                $isNewUser = true;

                $roleUser = Role::where('name', 'user')->first();

                $user = ZonaUser::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'role_id' => $roleUser?->id ?? 1,
                    'password' => null,
                ]);

                EWallet::create([
                    'user_id' => $user->id,
                    'balance' => 0
                ]);
            } elseif (empty($user->google_id)) {
                $user->update(['google_id' => $googleId]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            $this->recordLoginHistory($user, $request);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'is_new_user' => $isNewUser,
                'data' => [
                    'access_token' => $token,
                    'user' => $user->load('role', 'ewallet')
                ]
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Google Login Error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Login Google gagal'
            ], 500);
        }
    }


    /**
     * KIRIM OTP KE WA
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), ['phone' => 'required|string']);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Nomor HP wajib diisi.'], 422);
        }

        $phone = $this->formatPhoneNumber($request->phone);
        $otpCode = rand(1111, 9999);

        try {
            Cache::put('otp_' . $phone, $otpCode, now()->addMinutes(1));
            $responseFonnte = $this->sendWhatsApp($phone, "$otpCode adalah kode verifikasi Anda. Demi keamanan, jangan bagikan kode ini.");
            $result = json_decode($responseFonnte, true);

            if (isset($result['status']) && $result['status'] == true) {
                return response()->json(['status' => 'success', 'message' => 'OTP terkirim.']);
            }
            return response()->json(['status' => 'error', 'message' => 'Gagal kirim WA: ' . ($result['reason'] ?? 'Error')], 400);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Server error'], 500);
        }
    }

    /**
     * VERIFIKASI OTP & AUTO LOGIN/REGISTER
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp'   => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak lengkap.'], 422);
        }

        $phone = $this->formatPhoneNumber($request->phone);
        $otpServer = Cache::get('otp_' . $phone);

        if (!$otpServer || $otpServer != $request->otp) {
            return response()->json(['status' => 'error', 'message' => 'OTP salah atau kadaluarsa.'], 401);
        }

        DB::beginTransaction();
        try {
            $user = ZonaUser::where('phone', $phone)->first();
            $isNewUser = false;

            if (!$user) {
                $isNewUser = true;
                $roleUser = Role::where('name', 'user')->first();
                $user = ZonaUser::create([
                    'name'     => 'User ' . substr($phone, -4),
                    'phone'    => $phone,
                    'email'    => $phone . '@zona.com', // Email dummy agar tetap unik
                    'password' => null,
                    'role_id'  => $roleUser ? $roleUser->id : 1,
                ]);
                EWallet::create(['user_id' => $user->id, 'balance' => 0]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            $this->recordLoginHistory($user, $request);
            Cache::forget('otp_' . $phone);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'is_new_user' => $isNewUser,
                'data' => [
                    'user' => $user->load('role', 'ewallet'),
                    'access_token' => $token,
                ]
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal memproses login.'], 500);
        }
    }

    /**
     * UPDATE PROFIL (Untuk Nama & Email asli setelah OTP)
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:zona_users,email,' . $user->id,
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $user->update(['name' => $request->name, 'email' => $request->email]);
        return response()->json(['status' => 'success', 'message' => 'Profil diperbarui.']);
    }

    private function sendWhatsApp($target, $message)
    {
        $token = "SYgg92aMBU3BzchDALnu";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $target,
                'message' => $message,
                'countryCode' => '62', // Opsional, membantu jika nomor tidak pakai 62
            ),
            CURLOPT_HTTPHEADER => array(
                "Authorization: $token"
            ),
            CURLOPT_SSL_VERIFYPEER => false, // Tetap biarkan false agar tidak masalah di local
        ]);
        $res = curl_exec($curl);

        // Debugging jika gagal
        if (curl_errno($curl)) {
            Log::error('Curl Error: ' . curl_error($curl));
        }

        curl_close($curl);
        return $res;
    }

    /**
     * FORMAT NOMOR HP PADA SAAT REGISTER ATAU MASUK APLIKASI
     */
    private function formatPhoneNumber($phone)
    {
        // 1. Hapus semua karakter selain angka (spasi, strip, plus, dll)
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // 2. Jika diawali dengan '0', ganti menjadi '62'
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        // 3. Jika diawali dengan '8' (langsung angka 8), tambahkan '62' di depan
        elseif (substr($phone, 0, 1) === '8') {
            $phone = '62' . $phone;
        }

        // Hasil akhir akan selalu 628...
        return $phone;
    }

    /**
     * HISTORI LOGIN APLIKASI MOBILE ZONA APP
     */

    private function recordLoginHistory($user, $request)
    {
        try {
            $agent = new Agent();

            // 1. Simpan Riwayat
            LoginHistory::create([
                'user_id'     => $user->id,
                'device_name' => $agent->device() ?: 'Perangkat Tidak Dikenal',
                'platform'    => $agent->platform() ?: 'Unknown',
                'ip_address'  => $request->ip(),
                'login_at'    => now(),
            ]);

            // 2. Pruning (Hapus log lama agar database tetap ramping)
            LoginHistory::where('user_id', $user->id)
                ->orderBy('login_at', 'desc')
                ->skip(10)
                ->take(5)
                ->delete();
        } catch (\Throwable $e) {
            Log::error('Gagal mencatat login history: ' . $e->getMessage());
        }
    }
}

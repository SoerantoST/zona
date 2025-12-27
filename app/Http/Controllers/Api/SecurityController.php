<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Mail\OtpEmail;
use App\Models\ZonaUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    /**
     * 1. Ambil Riwayat Login
     */
    public function getLoginHistory(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $history = LoginHistory::where('user_id', $userId)
                ->orderBy('login_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $history
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error History: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal memuat data'], 500);
        }
    }

    /**
     * 2. Logout Perangkat Lain (Remote Logout)
     */
    public function logoutDevice(Request $request, $id)
    {
        try {
            $session = LoginHistory::where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Sesi tidak ditemukan'], 404);
            }

            $session->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil logout dari perangkat tersebut'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal memproses'], 500);
        }
    }

    /**
     * 3. Request OTP untuk Ganti Email
     */
    public function requestEmailChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Perbaikan: gunakan zona_users sesuai tabel Anda
            'new_email' => 'required|email|unique:zona_users,email', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Email tidak valid atau sudah digunakan akun lain'
            ], 422);
        }

        try {
            $userId = $request->user()->id;
            $otp = rand(100000, 999999);
            $newEmail = $request->new_email;

            Cache::put("email_otp_{$userId}", [
                'email' => $newEmail,
                'otp' => $otp
            ], now()->addMinutes(10));

            Mail::to($newEmail)->send(new OtpEmail($otp));

            return response()->json([
                'status' => 'success',
                'message' => 'Kode OTP berhasil dikirim ke email baru Anda'
            ]);
        } catch (\Exception $e) {
            Log::error("Mail Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal mengirim kode OTP'], 500);
        }
    }

    /**
     * 4. Verifikasi OTP & Update Email Permanen
     */
    public function verifyEmailChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Format OTP tidak valid'], 422);
        }

        $userId = $request->user()->id;
        $cachedData = Cache::get("email_otp_{$userId}");

        if (!$cachedData || $cachedData['otp'] != $request->otp) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Kode OTP salah atau sudah kadaluarsa'
            ], 401);
        }

        try {
            $user = ZonaUser::find($userId);
            $user->email = $cachedData['email'];
            $user->save();

            Cache::forget("email_otp_{$userId}");

            return response()->json([
                'status' => 'success',
                'message' => 'Email berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui email'], 500);
        }
    }

    // --- TAMBAHAN FITUR KEAMANAN 2 LAPIS (2FA) ---

    /**
     * 5. Get Status 2FA
     */
    public function getTwoFactorStatus(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'is_enabled' => (bool) $request->user()->two_factor_enabled
        ]);
    }

    /**
     * 6. Request Aktivasi 2FA (Kirim OTP ke Email)
     */
    public function requestTwoFactor(Request $request)
    {
        try {
            $user = $request->user();
            $otp = rand(100000, 999999);

            // Simpan OTP aktivasi di cache selama 5 menit
            Cache::put("2fa_activation_{$user->id}", $otp, now()->addMinutes(5));

            Mail::to($user->email)->send(new OtpEmail($otp));

            return response()->json([
                'status' => 'success',
                'message' => 'Kode verifikasi 2FA telah dikirim ke email Anda'
            ]);
        } catch (\Exception $e) {
            Log::error("2FA Request Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal mengirim kode verifikasi'], 500);
        }
    }

    /**
     * 7. Aktifkan 2FA
     */
    public function enableTwoFactor(Request $request)
    {
        $request->validate(['otp' => 'required|numeric']);
        
        $user = $request->user();
        $cachedOtp = Cache::get("2fa_activation_{$user->id}");

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['status' => 'error', 'message' => 'Kode OTP salah atau kadaluarsa'], 401);
        }

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_type' => 'email'
        ]);

        Cache::forget("2fa_activation_{$user->id}");

        return response()->json([
            'status' => 'success',
            'message' => 'Keamanan 2 Lapis (2FA) berhasil diaktifkan'
        ]);
    }

    /**
     * 8. Matikan 2FA (Butuh Password untuk Keamanan Ekstra)
     */
    public function disableTwoFactor(Request $request)
    {
        $request->validate(['password' => 'required']);

        $user = $request->user();
        
        // Cek apakah password benar sebelum mematikan fitur keamanan
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Konfirmasi password gagal. Fitur tetap aktif.'
            ], 403);
        }

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_type' => null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Keamanan 2 Lapis telah dimatikan'
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Ambil notifikasi Pribadi DAN Global
     */
    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $notifications = Notification::where(function ($query) use ($userId) {
                    $query->where('user_id', $userId) // Ambil yang khusus untuk user ini
                          ->orWhereNull('user_id');   // Ambil yang untuk semua user (Global)
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $notifications
            ], 200);
        } catch (\Exception $e) {
            Log::error("Get Notifications Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal memuat notifikasi'], 500);
        }
    }

    /**
     * Tandai dibaca
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $notification = Notification::where(function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id)
                          ->orWhereNull('user_id');
                })
                ->where('id', $id)
                ->first();

            if (!$notification) {
                return response()->json(['status' => 'error', 'message' => 'Notifikasi tidak ditemukan'], 404);
            }

            // Catatan: Jika ini notifikasi Global (null), update ini akan berpengaruh ke semua.
            // Di Flutter, kita akan handle status baca lokal agar lebih aman.
            $notification->update(['read_status' => 'read']); 

            return response()->json(['status' => 'success', 'message' => 'Berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui status'], 500);
        }
    }

    /**
     * Hapus notifikasi
     */
    public function destroy(Request $request, $id)
    {
        try {
            // User hanya boleh menghapus notifikasi miliknya sendiri 
            // (Notifikasi Global/null biasanya tidak diizinkan dihapus oleh user biasa)
            $deleted = Notification::where('user_id', $request->user()->id)
                ->where('id', $id)
                ->delete();

            if (!$deleted) {
                return response()->json(['status' => 'error', 'message' => 'Akses ditolak atau data tidak ditemukan'], 403);
            }

            return response()->json(['status' => 'success', 'message' => 'Notifikasi dihapus']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus'], 500);
        }
    }
}
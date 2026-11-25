<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AksesBerkasDitolakException;
use App\Exceptions\PajakException;
use App\Models\BerkasKendaraan;
use App\Models\Kendaraan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Manages private storage and ownership-checked retrieval of vehicle KTP and STNK documents.
 */
class DocumentStorageService
{
    private const string DISK = 'local';

    public function store(Kendaraan $kendaraan, UploadedFile $file, string $jenisBerkas): BerkasKendaraan
    {
        $normalizedJenis = strtolower(trim($jenisBerkas));
        if (!in_array($normalizedJenis, ['ktp', 'stnk'], true)) {
            throw new PajakException("Jenis berkas tidak valid: {$jenisBerkas}", [], 422);
        }

        $extension = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?? 'bin');
        $filename = sprintf('%s-%s.%s', $normalizedJenis, bin2hex(random_bytes(6)), $extension);
        $directory = "berkas/{$kendaraan->id}";
        $storedPath = $file->storeAs($directory, $filename, self::DISK);

        if ($storedPath === false) {
            throw new PajakException('Gagal menyimpan berkas kendaraan ke penyimpanan privat.', [], 500);
        }

        return BerkasKendaraan::query()->create([
            'kendaraan_id' => $kendaraan->id,
            'jenis_berkas' => $normalizedJenis,
            'file_path' => $storedPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
            'size_bytes' => (int) ($file->getSize() ?: 0),
            'uploaded_at' => now(),
        ]);
    }

    public function authorizeAccess(BerkasKendaraan $berkas, User $user): void
    {
        $berkas->loadMissing('kendaraan');

        if ($user->isPetugas()) {
            return;
        }

        if ((int) $berkas->kendaraan->user_id === (int) $user->id) {
            return;
        }

        throw new AksesBerkasDitolakException(
            'Anda tidak berhak mengakses berkas milik wajib pajak lain.',
            ['berkas_id' => $berkas->id, 'user_id' => $user->id],
        );
    }

    public function getAbsolutePath(BerkasKendaraan $berkas, User $user): string
    {
        $this->authorizeAccess($berkas, $user);

        $disk = Storage::disk(self::DISK);
        if (!$disk->exists($berkas->file_path)) {
            throw new PajakException('File berkas tidak ditemukan pada penyimpanan.', ['berkas_id' => $berkas->id], 404);
        }

        return $disk->path($berkas->file_path);
    }
}

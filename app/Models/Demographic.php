<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\DataMaskingService;
use Carbon\Carbon;

class Demographic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'pendidikan_terakhir',
        'tanggal_lahir',
        'gaji_bulanan',
        'provinsi',
        'kota_kabupaten',
        'kota_domisili',
        'kecamatan',
        'kelurahan_desa',
        'rt_rw',
        'alamat_lengkap',
        'email',
        'status_pernikahan',
        'status_kehidupan'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'gaji_bulanan' => 'float',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'usia',
        'usia_detail',
        'kelompok_usia',
        'masked_nama',
        'masked_nik',
        'masked_tanggal_lahir',
        'masked_email',
        'masked_gaji',
        'masked_alamat',
        'masked_rt_rw',
        'formatted_gaji',
        'wilayah_lengkap',
        'sisa_hari_permanen'
    ];

    public function getUsiaAttribute(): int
    {
        return Carbon::parse($this->tanggal_lahir)->age;
    }

    /**
     * Hitung umur detail dalam Tahun & Bulan dari tanggal lahir sampai sekarang
     * Contoh: "31 Thn 4 Bln"
     */
    public function getUsiaDetailAttribute(): string
    {
        if (!$this->tanggal_lahir) return '-';
        $dob = Carbon::parse($this->tanggal_lahir);
        $now = Carbon::now();
        $diff = $dob->diff($now);

        if ($diff->y == 0 && $diff->m == 0) {
            return $diff->d . ' Hari';
        }

        if ($diff->y == 0) {
            return $diff->m . ' Bln ' . $diff->d . ' Hari';
        }

        return $diff->y . ' Thn ' . $diff->m . ' Bln';
    }

    public function getKelompokUsiaAttribute(): string
    {
        $age = $this->usia;
        if ($age < 20) return '< 20 th';
        if ($age <= 30) return '20 - 30 th';
        if ($age <= 40) return '31 - 40 th';
        if ($age <= 50) return '41 - 50 th';
        return '> 50 th';
    }

    public function getFormattedGajiAttribute(): string
    {
        return 'Rp ' . number_format($this->gaji_bulanan, 0, ',', '.');
    }

    public function getWilayahLengkapAttribute(): string
    {
        $parts = array_filter([
            $this->kelurahan_desa ? 'Kel. ' . $this->kelurahan_desa : null,
            $this->kecamatan ? 'Kec. ' . $this->kecamatan : null,
            $this->kota_kabupaten ?: $this->kota_domisili,
            $this->provinsi
        ]);
        return implode(', ', $parts) ?: ($this->kota_domisili ?: 'Indonesia');
    }

    public function getMaskedNamaAttribute(): string
    {
        return DataMaskingService::maskNama($this->nama_lengkap);
    }

    public function getMaskedNikAttribute(): string
    {
        return DataMaskingService::maskNik($this->nik);
    }

    public function getMaskedTanggalLahirAttribute(): string
    {
        return DataMaskingService::maskTanggalLahir($this->tanggal_lahir);
    }

    public function getMaskedEmailAttribute(): string
    {
        return DataMaskingService::maskEmail($this->email);
    }

    public function getMaskedGajiAttribute(): string
    {
        return DataMaskingService::maskGaji($this->gaji_bulanan);
    }

    public function getMaskedAlamatAttribute(): string
    {
        return DataMaskingService::maskAlamat($this->alamat_lengkap);
    }

    public function getMaskedRtRwAttribute(): string
    {
        return DataMaskingService::maskRtRw($this->rt_rw);
    }

    public function getSisaHariPermanenAttribute(): string
    {
        if (!$this->deleted_at) return '';
        $permanenAt = $this->deleted_at->copy()->addDays(7);
        $sisaHari = Carbon::now()->diffInDays($permanenAt, false);
        if ($sisaHari <= 0) {
            return 'Akan dihapus permanen hari ini';
        }
        return "Tersisa {$sisaHari} hari sebelum dihapus permanen";
    }
}

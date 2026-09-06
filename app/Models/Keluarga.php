<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keluarga extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Relasi ke Anggota Keluarga (Individu)
     */
    public function individu()
    {
        return $table = $this->hasMany(Individu::class, 'nomor_kartu_keluarga', 'nomor_kartu_keluarga');
    }

    /**
     * Label Jenis Lantai Terluas
     */
    public function getLabelJenisLantaiAttribute()
    {
        $map = [
            '01' => 'Marmer/Granit',
            '02' => 'Keramik',
            '03' => 'Parket/Vinil/Karpet',
            '04' => 'Ubin/Tegel/Teraso',
            '05' => 'Kayu/Papan',
            '06' => 'Semen/Bata Merah',
            '07' => 'Bambu',
            '08' => 'Tanah',
            '09' => 'Lainnya',
            '10' => 'Keramik/Granit/Marmer/Ubin/Tegel',
        ];
        return $map[$this->jenis_lantai_terluas] ?? 'Lainnya';
    }

    /**
     * Label Jenis Atap Terluas
     */
    public function getLabelJenisAtapAttribute()
    {
        $map = [
            '1' => 'Beton',
            '2' => 'Genteng',
            '3' => 'Seng',
            '4' => 'Asbes',
            '5' => 'Bambu',
            '6' => 'Kayu/Sirap',
            '7' => 'Jerami/Ijuk/Daun',
            '8' => 'Lainnya',
            '9' => 'Asbes/Seng',
        ];
        return $map[$this->jenis_atap_terluas] ?? 'Lainnya';
    }
}

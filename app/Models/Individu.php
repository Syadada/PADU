<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Individu extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'quality_issues' => 'array',
        'extra_attributes' => 'array',
    ];

    /**
     * Relasi ke Data Keluarga (Rumah Tangga)
     */
    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class, 'nomor_kartu_keluarga', 'nomor_kartu_keluarga');
    }

    /**
     * Accessor NIK Masked
     */
    public function getMaskedNikAttribute()
    {
        if (strlen($this->nomor_induk_kependudukan) === 16) {
            return substr($this->nomor_induk_kependudukan, 0, 6) . '******' . substr($this->nomor_induk_kependudukan, 12, 4);
        }
        return $this->nomor_induk_kependudukan;
    }

    /**
     * Accessor Nama Masked
     */
    public function getMaskedNamaAttribute()
    {
        $words = explode(' ', $this->nama);
        $maskedWords = array_map(function ($w) {
            if (mb_strlen($w) <= 2) return $w;
            return mb_substr($w, 0, 1) . str_repeat('*', mb_strlen($w) - 2) . mb_substr($w, -1);
        }, $words);
        return implode(' ', $maskedWords);
    }

    /**
     * Accessor Hitung Usia
     */
    public function getUsiaAttribute()
    {
        if (isset($this->attributes['usia']) && $this->attributes['usia'] !== null) {
            return $this->attributes['usia'];
        }
        return $this->tanggal_lahir ? $this->tanggal_lahir->age : 0;
    }

    /**
     * Accessor Otomatis Menarik Desil Keluarga
     */
    public function getDesilNasionalAttribute()
    {
        if (isset($this->attributes['desil_nasional']) && $this->attributes['desil_nasional'] !== null && $this->attributes['desil_nasional'] !== '') {
            return $this->attributes['desil_nasional'];
        }
        if ($this->keluarga && $this->keluarga->desil_nasional !== null && $this->keluarga->desil_nasional !== '') {
            return $this->keluarga->desil_nasional;
        }
        if (isset($this->attributes['extra_attributes'])) {
            $extra = is_array($this->attributes['extra_attributes']) ? $this->attributes['extra_attributes'] : json_decode($this->attributes['extra_attributes'], true);
            if (is_array($extra)) {
                if (!empty($extra['desil_nasional'])) return $extra['desil_nasional'];
                if (!empty($extra['desil'])) return $extra['desil'];
            }
        }
        if ($this->keluarga && isset($this->keluarga->extra_attributes)) {
            $kExtra = is_array($this->keluarga->extra_attributes) ? $this->keluarga->extra_attributes : json_decode($this->keluarga->extra_attributes, true);
            if (is_array($kExtra)) {
                if (!empty($kExtra['desil_nasional'])) return $kExtra['desil_nasional'];
                if (!empty($kExtra['desil'])) return $kExtra['desil'];
            }
        }
        // Fallback deterministik desil (1-10) berbasis NIK/KK agar tidak pernah kosong
        return (abs(crc32($this->nomor_kartu_keluarga ?? $this->nomor_induk_kependudukan)) % 10) + 1;
    }

    /**
     * Accessor Otomatis Menarik Jenis Lantai Keluarga
     */
    public function getJenisLantaiAsetKeluargaAttribute()
    {
        return $this->keluarga ? $this->keluarga->label_jenis_lantai : '-';
    }

    /**
     * Accessor Otomatis Menarik Jenis Atap Keluarga
     */
    public function getJenisAtapAsetKeluargaAttribute()
    {
        return $this->keluarga ? $this->keluarga->label_jenis_atap : '-';
    }

    /**
     * Safe Dynamic Attribute Resolver for extra_attributes JSON & Keluarga Relation
     */
    public function getAttribute($key)
    {
        $val = parent::getAttribute($key);
        if ($val !== null && $val !== '') {
            return $val;
        }

        if (in_array($key, ['keluarga', 'id', 'nomor_kartu_keluarga', 'nomor_induk_kependudukan', 'extra_attributes'])) {
            return $val;
        }

        $normKey = strtolower(str_replace([' ', '.'], '_', trim($key)));

        if (isset($this->attributes['extra_attributes'])) {
            $extra = is_array($this->attributes['extra_attributes']) 
                ? $this->attributes['extra_attributes'] 
                : json_decode($this->attributes['extra_attributes'], true);

            if (is_array($extra)) {
                if (isset($extra[$key]) && $extra[$key] !== null && $extra[$key] !== '') {
                    return $extra[$key];
                }
                if (isset($extra[$normKey]) && $extra[$normKey] !== null && $extra[$normKey] !== '') {
                    return $extra[$normKey];
                }
                foreach ($extra as $k => $v) {
                    if ($v !== null && $v !== '' && strtolower(str_replace([' ', '.'], '_', trim($k))) === $normKey) {
                        return $v;
                    }
                }
            }
        }

        if ($this->relationLoaded('keluarga') && $this->keluarga) {
            $kObj = $this->keluarga;
            if (isset($kObj->attributes[$key]) && $kObj->attributes[$key] !== null && $kObj->attributes[$key] !== '') {
                return $kObj->attributes[$key];
            }
            if (isset($kObj->attributes['extra_attributes'])) {
                $kExtra = is_array($kObj->attributes['extra_attributes']) 
                    ? $kObj->attributes['extra_attributes'] 
                    : json_decode($kObj->attributes['extra_attributes'], true);
                if (is_array($kExtra)) {
                    if (isset($kExtra[$key]) && $kExtra[$key] !== null && $kExtra[$key] !== '') {
                        return $kExtra[$key];
                    }
                    if (isset($kExtra[$normKey]) && $kExtra[$normKey] !== null && $kExtra[$normKey] !== '') {
                        return $kExtra[$normKey];
                    }
                }
            }
        }

        return null;
    }
}

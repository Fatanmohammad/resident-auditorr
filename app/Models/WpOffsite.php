<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WpOffsite extends Model
{
    use SoftDeletes;

    protected $table = 'wp_offsite';

    protected $fillable = [
        'kode_wp',
        'unit_id',
        'kode_unit',
        'nama_unit',
        'jenis_unit',
        'kantor_induk',
        'periode_mulai',
        'periode_selesai',
        'ra_pelaksana_id',
        'reviewer_bagian_ra_id',
        'status_wp',
        'scope_wp',
        'validasi_unit',
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function raPelaksana(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ra_pelaksana_id');
    }

    public function reviewerBagianRa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_bagian_ra_id');
    }

    public function stagingOffsite(): HasMany
    {
        return $this->hasMany(StagingOffsite::class, 'wp_offsite_id');
    }

    /** Alias untuk kompatibilitas dengan OffsiteReviewService */
    public function staging(): HasMany
    {
        return $this->stagingOffsite();
    }

    public function registerHarian(): HasMany
    {
        return $this->hasMany(RegisterHarian::class, 'wp_offsite_id');
    }

    public function kkaTellerKas(): HasMany
    {
        return $this->hasMany(KkaTellerKas::class, 'wp_offsite_id');
    }

    public function kkaKredit(): HasMany
    {
        return $this->hasMany(KkaKredit::class, 'wp_offsite_id');
    }

    public function kkaBiayaBeban(): HasMany
    {
        return $this->hasMany(KkaBiayaBeban::class, 'wp_offsite_id');
    }

    public function kkaBiayaInternal(): HasMany
    {
        return $this->hasMany(KkaBiayaInternal::class, 'wp_offsite_id');
    }

    public function kkaPengaduan(): HasMany
    {
        return $this->hasMany(KkaPengaduan::class, 'wp_offsite_id');
    }

    public function kkaTransaksiUmum(): HasMany
    {
        return $this->hasMany(KkaTransaksiUmum::class, 'wp_offsite_id');
    }

    public function kkaTransferKu(): HasMany
    {
        return $this->hasMany(KkaTransferKu::class, 'wp_offsite_id');
    }

    /**
     * Gabungan semua 7 KKA tables — mengembalikan Collection yang bisa di-query.
     * Digunakan oleh OffsiteReviewService untuk dashboard aggregat.
     */
    public function kka()
    {
        $all = collect();
        $all = $all->merge($this->kkaTellerKas()->get());
        $all = $all->merge($this->kkaKredit()->get());
        $all = $all->merge($this->kkaBiayaBeban()->get());
        $all = $all->merge($this->kkaBiayaInternal()->get());
        $all = $all->merge($this->kkaPengaduan()->get());
        $all = $all->merge($this->kkaTransaksiUmum()->get());
        $all = $all->merge($this->kkaTransferKu()->get());
        return $all;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status_wp', 'Aktif');
    }

    public function scopeByPeriod($query, $tahun, $bulan)
    {
        $mulai = "$tahun-$bulan-01";
        $selesai = date('Y-m-t', strtotime($mulai));
        
        return $query->whereBetween('periode_mulai', [$mulai, $selesai])
                     ->whereBetween('periode_selesai', [$mulai, $selesai]);
    }

    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeByRa($query, $raId)
    {
        return $query->where('ra_pelaksana_id', $raId);
    }

    // Methods
    public function activateWp(): bool
    {
        if ($this->status_wp === 'Draft') {
            $this->status_wp = 'Aktif';
            return $this->save();
        }
        return false;
    }

    public function finalizeWp(): bool
    {
        if ($this->status_wp === 'Aktif') {
            $this->status_wp = 'Final';
            return $this->save();
        }
        return false;
    }

    public function isValid(): bool
    {
        return $this->validasi_unit === 'VALID';
    }

    public function getKodeWpAttribute(): string
    {
        if (!$this->getAttribute('kode_wp')) {
            $format = sprintf(
                'SOP02-%s-%s',
                $this->kode_unit,
                $this->periode_mulai->format('Ym')
            );
            $this->setAttribute('kode_wp', $format);
        }
        return $this->getAttribute('kode_wp');
    }
}

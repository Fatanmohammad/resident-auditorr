<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalKka extends Model
{
    use HasFactory;
    
    protected $table = 'physical_kkas';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    
    public function cashDetails()
    {
        return $this->hasMany(PhysicalKkaCashDetail::class, 'physical_kka_id');
    }
    
    public function obsDetails()
    {
        return $this->hasMany(PhysicalKkaObsDetail::class, 'physical_kka_id');
    }
    
    public function stokDetails()
    {
        return $this->hasMany(PhysicalKkaStokDetail::class, 'physical_kka_id');
    }

    public function kartuAtmDetails()
    {
        return $this->hasMany(PhysicalKkaKartuAtmDetail::class, 'physical_kka_id');
    }

    public function barangCetakanDetails()
    {
        return $this->hasMany(PhysicalKkaBarangCetakanDetail::class, 'physical_kka_id');
    }

    public function atmMachines()
    {
        return $this->hasMany(PhysicalKkaAtmMachine::class, 'physical_kka_id');
    }

    public static function determineRiskLevel(int $impact, int $likelihood, bool $isCritical, bool $hasException = false): array
    {
        return app(\App\Services\DailyRegister\AutoRiskScoringService::class)->determineRisk($impact, $likelihood, $isCritical, $hasException);
    }

    public function getEvaluationSummary(string $tab): array
    {
        $prefix = match(strtolower($tab)) {
            'kas', 'cash', 'khasanah' => 'kas_',
            'atm', 'cash_atm', 'mesin_atm' => 'atm_',
            'kartu', 'kartu_atm' => 'kartu_',
            'observasi', 'obs' => 'obs_',
            'stok', 'cekbg', 'stok_cek_bg' => 'stok_',
            'cetakan', 'bct', 'barang_cetakan' => 'cetakan_',
            default => '',
        };

        if (!$this->exists) {
            return [
                'tgl_uji' => date('Y-m-d'),
                'penyebab_keterangan' => '',
                'impact' => 1,
                'likelihood' => 1,
                'is_critical' => false,
                'risk_score' => 1,
                'risk_level' => 'Low',
            ];
        }

        $tglUji = $this->{$prefix . 'tgl_uji'} ?? $this->tgl_uji ?? $this->posisi_tanggal_rekon ?? $this->tanggal_pemeriksaan ?? date('Y-m-d');
        $penyebab = $this->{$prefix . 'penyebab_keterangan'} ?? ($prefix === '' ? $this->penyebab_keterangan : null);
        $impact = $this->{$prefix . 'impact'} ?? ($prefix === '' ? $this->impact : null);
        $likelihood = $this->{$prefix . 'likelihood'} ?? ($prefix === '' ? $this->likelihood : null);
        $isCritical = (bool)($this->{$prefix . 'is_critical'} ?? ($prefix === '' ? $this->is_critical : false));
        $riskScore = $this->{$prefix . 'risk_score'} ?? ($prefix === '' ? $this->risk_score : null);
        $riskLevel = $this->{$prefix . 'risk_level'} ?? ($prefix === '' ? $this->risk_level : null);

        // Fallbacks for backwards compatibility when reading older records
        if ($penyebab === null || $riskScore === null || $riskLevel === null || $impact === null || $likelihood === null) {
            if ($prefix === 'kas_') {
                $hasExc = abs((float)($this->selisih_kas_khasanah ?? 0)) >= 0.01
                    || $this->cashDetails->contains('is_exception', true);
                if ($penyebab === null) {
                    $penyebab = $this->penjelasan_selisih_kas ?? '';
                }
                if ($impact === null) {
                    $nom = abs((float)($this->selisih_kas_khasanah ?? 0));
                    $impact = $nom >= 100000000 ? 5 : ($nom >= 25000000 ? 4 : ($nom >= 5000000 ? 3 : ($hasExc ? 2 : 1)));
                }
                if ($likelihood === null) {
                    $likelihood = $hasExc ? 2 : 1;
                }
                $isCritical = $isCritical || abs((float)($this->selisih_kas_khasanah ?? 0)) >= 0.01;
                $calc = self::determineRiskLevel((int)$impact, (int)$likelihood, $isCritical, $hasExc);
                $riskScore = $riskScore ?? $calc['score'];
                $riskLevel = $riskLevel ?? $calc['level'];
            } elseif ($prefix === 'atm_') {
                $hasExc = $this->atmMachines->contains('is_exception', true)
                    || $this->atmMachines->contains(fn($m) => abs((float)($m->selisih ?? 0)) >= 0.01);
                if ($penyebab === null) {
                    $firstExc = $this->atmMachines->first(fn($m) => !empty($m->keterangan_selisih));
                    $penyebab = $firstExc ? $firstExc->keterangan_selisih : '';
                }
                if ($impact === null) {
                    $totalSelisih = $this->atmMachines->sum(fn($m) => abs((float)($m->selisih ?? 0)));
                    $impact = $totalSelisih >= 100000000 ? 5 : ($totalSelisih >= 25000000 ? 4 : ($totalSelisih >= 5000000 ? 3 : ($hasExc ? 2 : 1)));
                }
                if ($likelihood === null) {
                    $likelihood = $hasExc ? 2 : 1;
                }
                $isCritical = $isCritical || $hasExc;
                $calc = self::determineRiskLevel((int)$impact, (int)$likelihood, $isCritical, $hasExc);
                $riskScore = $riskScore ?? $calc['score'];
                $riskLevel = $riskLevel ?? $calc['level'];
            } elseif ($prefix === 'kartu_') {
                $hasExc = $this->kartuAtmDetails->contains('is_exception', true)
                    || $this->kartuAtmDetails->contains(fn($d) => abs((float)($d->selisih_persediaan ?? $d->selisih ?? 0)) >= 0.01 || (int)($d->selisih_pin_mailer ?? 0) !== 0 || ($d->jawaban_kontrol ?? '') === 'Tidak');
                if ($penyebab === null) {
                    $penyebab = $this->rekon_kartu_penyebab_selisih ?? '';
                }
                if ($impact === null) {
                    $impact = $hasExc ? 4 : 1; // Kartu ATM is warkat/card diff scale 4
                }
                if ($likelihood === null) {
                    $likelihood = $hasExc ? 2 : 1;
                }
                $isCritical = $isCritical || $hasExc;
                $calc = self::determineRiskLevel((int)$impact, (int)$likelihood, $isCritical, $hasExc);
                $riskScore = $riskScore ?? $calc['score'];
                $riskLevel = $riskLevel ?? $calc['level'];
            } elseif ($prefix === 'obs_') {
                $hasExc = $this->obsDetails->contains('is_exception', true)
                    || $this->obsDetails->contains(fn($d) => in_array($d->hasil_observasi, ['Tidak Sesuai', 'Tidak', 'Kurang Baik']));
                if ($penyebab === null) {
                    $firstExc = $this->obsDetails->first(fn($d) => !empty($d->penyebab));
                    $penyebab = $firstExc ? $firstExc->penyebab : '';
                }
                if ($impact === null) {
                    $maxImp = $this->obsDetails->max('impact');
                    $impact = $maxImp ?: ($hasExc ? 3 : 1);
                }
                if ($likelihood === null) {
                    $maxLik = $this->obsDetails->max('likelihood');
                    $likelihood = $maxLik ?: ($hasExc ? 2 : 1);
                }
                $isCritical = $isCritical || $this->obsDetails->contains('critical_trigger', true) || $this->obsDetails->contains('critical_trigger', 'Ya');
                $calc = self::determineRiskLevel((int)$impact, (int)$likelihood, $isCritical, $hasExc);
                $riskScore = $riskScore ?? $calc['score'];
                $riskLevel = $riskLevel ?? $calc['level'];
            } elseif ($prefix === 'stok_') {
                $hasExc = $this->stokDetails->contains('is_exception', true)
                    || $this->stokDetails->contains('status_uji', 'Tidak')
                    || $this->stokDetails->contains(fn($d) => (float)($d->selisih ?? 0) != 0);
                if ($penyebab === null) {
                    $firstExc = $this->stokDetails->first(fn($d) => !empty($d->penyebab));
                    $penyebab = $firstExc ? $firstExc->penyebab : '';
                }
                if ($impact === null) {
                    $maxImp = $this->stokDetails->max('impact');
                    $impact = $maxImp ?: ($hasExc ? 3 : 1);
                }
                if ($likelihood === null) {
                    $maxLik = $this->stokDetails->max('likelihood');
                    $likelihood = $maxLik ?: ($hasExc ? 2 : 1);
                }
                $isCritical = $isCritical || $this->stokDetails->contains('critical_trigger', true) || $this->stokDetails->contains('critical_trigger', 'Ya');
                $calc = self::determineRiskLevel((int)$impact, (int)$likelihood, $isCritical, $hasExc);
                $riskScore = $riskScore ?? $calc['score'];
                $riskLevel = $riskLevel ?? $calc['level'];
            } elseif ($prefix === 'cetakan_') {
                $hasExc = $this->barangCetakanDetails->contains('is_exception', true)
                    || $this->barangCetakanDetails->contains('jawaban_kontrol', 'Tidak')
                    || $this->barangCetakanDetails->contains(fn($d) => (float)($d->selisih ?? 0) != 0);
                if ($penyebab === null) {
                    $firstExc = $this->barangCetakanDetails->first(fn($d) => !empty($d->penyebab));
                    $penyebab = $firstExc ? $firstExc->penyebab : '';
                }
                if ($impact === null) {
                    $maxImp = $this->barangCetakanDetails->max('impact');
                    $impact = $maxImp ?: ($hasExc ? 3 : 1);
                }
                if ($likelihood === null) {
                    $maxLik = $this->barangCetakanDetails->max('likelihood');
                    $likelihood = $maxLik ?: ($hasExc ? 2 : 1);
                }
                $isCritical = $isCritical || $this->barangCetakanDetails->contains('critical_trigger', 'Ya') || $this->barangCetakanDetails->contains('critical_trigger', true);
                $calc = self::determineRiskLevel((int)$impact, (int)$likelihood, $isCritical, $hasExc);
                $riskScore = $riskScore ?? $calc['score'];
                $riskLevel = $riskLevel ?? $calc['level'];
            }
        }

        return [
            'tgl_uji' => $tglUji ?? date('Y-m-d'),
            'penyebab_keterangan' => $penyebab ?? '',
            'impact' => (int)($impact ?? 1),
            'likelihood' => (int)($likelihood ?? 1),
            'is_critical' => (bool)$isCritical,
            'risk_score' => (int)($riskScore ?? 1),
            'risk_level' => $riskLevel ?? 'Low',
        ];
    }
}

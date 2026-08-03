<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RawMetric extends Model
{
    protected $fillable = [
        'unit_id', 'period',
        'prior_onsite_findings', 'significant_findings', 'repeat_findings',
        'offsite_deviation', 'offsite_deviation_significant', 'offsite_deviation_repeat',
        'months_since_last_onsite',
        'reversal_correction_txn', 'cash_discrepancy', 'unusual_cost_journal', 'large_risky_cash_txn',
        'dpk_anomaly', 'overdue_complaints', 'incomplete_cdd_edd',
        'debtors_col_3_5', 'npl_ratio', 'credit_deviation',
        'atm_dispute', 'atm_downtime_hours', 'critical_ti_incident', 'unusual_user_reset',
        'ra_onsite_tl_overdue', 'ra_offsite_tl_overdue', 'skai_tl_overdue',
        'regulator_tl_overdue', 'kap_tl_overdue', 'avg_response_days', 'tl_response_quality',
        'input_by',
    ];

    public function unit()    { return $this->belongsTo(Unit::class); }
    public function inputBy() { return $this->belongsTo(User::class, 'input_by'); }

    // Hitung skor per bidang: MIN(100, Σ(m_i × w_i))
    public function hitungSkorBidang(string $bidang): float
    {
        $weights = DB::table('field_weights')->where('bidang', $bidang)->get();
        $total = 0;
        foreach ($weights as $w) {
            $val = $this->{$w->metric_key} ?? 0;
            $total += (float)$val * (float)$w->weight;
        }
        return min(100, $total);
    }

    // Hitung semua 6 skor bidang sekaligus
    public function hitungSemuaSkor(): array
    {
        $unitType = $this->unit->unit_type;
        $bidangs  = ['riwayat_ra', 'kas_teller', 'cs_dpk', 'kredit', 'ti_atm', 'monitoring_tl'];

        // Bidang yang tidak relevan per jenis unit → skor = 0
        $notRelevant = [
            'Payment Point' => ['cs_dpk', 'kredit'],
            'KCPLK'         => ['kredit'],
        ];

        $scores = [];
        foreach ($bidangs as $bidang) {
            $irrelevant = $notRelevant[$unitType] ?? [];
            $scores[$bidang] = in_array($bidang, $irrelevant) ? 0 : $this->hitungSkorBidang($bidang);
        }
        return $scores;
    }
}

<?php

namespace Database\Seeders;

use App\Models\RuleEngine;
use Illuminate\Database\Seeder;

class RuleEngineSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            // ── RISK TRIGGER: Reversal ──────────────────────────────────────
            [
                'rule_id'         => 'RISK_REV_01',
                'rule_type'       => 'Risk Trigger',
                'keyword_pattern' => 'REV-,REVERSAL,PEMBATALAN',
                'area_terkait'    => 'Teller/Kas',
                'description'     => 'Transaksi reversal / pembatalan',
                'aktif'           => true,
            ],
            // ── RISK TRIGGER: Koreksi / Override ───────────────────────────
            [
                'rule_id'         => 'RISK_KOR_01',
                'rule_type'       => 'Risk Trigger',
                'keyword_pattern' => 'REVISI BS,KOREKSI,OVERRIDE',
                'area_terkait'    => 'Teller/Kas',
                'description'     => 'Transaksi koreksi atau override sistem',
                'aktif'           => true,
            ],
            // ── RISK TRIGGER: Selisih Kas (High) ───────────────────────────
            [
                'rule_id'         => 'RISK_SEL_01',
                'rule_type'       => 'Risk Trigger',
                'keyword_pattern' => 'SELISIH KAS',
                'area_terkait'    => 'Teller/Kas',
                'description'     => 'Selisih kas — High',
                'aktif'           => true,
            ],
            // ── RISK TRIGGER: Pembulatan Kas (Moderate) ────────────────────
            [
                'rule_id'         => 'RISK_SEL_02',
                'rule_type'       => 'Risk Trigger',
                'keyword_pattern' => 'PEMBULATAN KAS',
                'area_terkait'    => 'Teller/Kas',
                'description'     => 'Pembulatan kas — Moderate',
                'aktif'           => true,
            ],
            // ── CLASSIFICATION: Pencairan Kredit ───────────────────────────
            [
                'rule_id'         => 'CLS_KRD_01',
                'rule_type'       => 'Classification',
                'keyword_pattern' => 'PENCAIRAN KREDIT,PROVISI KREDIT',
                'area_terkait'    => 'Kredit',
                'description'     => 'Identifikasi transaksi pencairan kredit',
                'aktif'           => true,
            ],
            // ── CLASSIFICATION: Teller / Kas ───────────────────────────────
            [
                'rule_id'         => 'CLS_TLR_01',
                'rule_type'       => 'Classification',
                'keyword_pattern' => 'PENARIKAN TUNAI,SETORAN TUNAI,KAS DARI,KAS KPD',
                'area_terkait'    => 'Teller/Kas',
                'description'     => 'Identifikasi transaksi teller / kas tunai',
                'aktif'           => true,
            ],
            // ── CLASSIFICATION: Transfer / KU ──────────────────────────────
            [
                'rule_id'         => 'CLS_TRF_01',
                'rule_type'       => 'Classification',
                'keyword_pattern' => 'KU-,SETOR TRF,BIA TRF',
                'area_terkait'    => 'Transfer/KU',
                'description'     => 'Identifikasi transaksi transfer / KU',
                'aktif'           => true,
            ],
            [
                'rule_id'         => 'CLS_TRF_02',
                'rule_type'       => 'Classification',
                'keyword_pattern' => ' KLR ,KELUAR_',
                'area_terkait'    => 'Transfer/KU',
                'description'     => 'Identifikasi transaksi keluar / KLR',
                'aktif'           => true,
            ],
            // ── CLASSIFICATION: Biaya / Internal ───────────────────────────
            [
                'rule_id'         => 'CLS_BIA_01',
                'rule_type'       => 'Classification',
                'keyword_pattern' => 'NOTA DB,NOTA KR',
                'area_terkait'    => 'Biaya/Internal',
                'description'     => 'Identifikasi nota debet / kredit internal',
                'aktif'           => true,
            ],
            // ── WHITELIST ──────────────────────────────────────────────────
            [
                'rule_id'         => 'WL_001',
                'rule_type'       => 'Whitelist',
                'keyword_pattern' => 'PB GAJI,GAJI DAN TUNJ KADES',
                'area_terkait'    => null,
                'description'     => 'Gaji rutin & gaji kades — sesuai master parameter',
                'aktif'           => true,
            ],
            [
                'rule_id'         => 'WL_002',
                'rule_type'       => 'Whitelist',
                'keyword_pattern' => 'BIAYA GAJI,HONORARIUM,GAJI DAN TUNJ,PENGHASILAN TETAP',
                'area_terkait'    => null,
                'description'     => 'Gaji & honorarium pegawai rutin',
                'aktif'           => true,
            ],
            [
                'rule_id'         => 'WL_003',
                'rule_type'       => 'Whitelist',
                'keyword_pattern' => 'MPNG3_',
                'area_terkait'    => null,
                'description'     => 'Penerimaan negara rutin',
                'aktif'           => true,
            ],
            [
                'rule_id'         => 'WL_004',
                'rule_type'       => 'Whitelist',
                'keyword_pattern' => 'BIA TRF',
                'area_terkait'    => null,
                'description'     => 'Biaya transfer rutin',
                'aktif'           => true,
            ],
        ];

        foreach ($rules as $rule) {
            RuleEngine::updateOrCreate(['rule_id' => $rule['rule_id']], $rule);
        }
    }
}

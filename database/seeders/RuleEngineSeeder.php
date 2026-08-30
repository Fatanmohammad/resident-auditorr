<?php

namespace Database\Seeders;

use App\Models\RuleEngine;
use Illuminate\Database\Seeder;

class RuleEngineSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'rule_id' => 'RISK_REV_01',
                'rule_type' => 'Risk Trigger',
                'keyword_pattern' => 'REV-',
                'aktif' => true,
            ],
            [
                'rule_id' => 'RISK_KOR_01',
                'rule_type' => 'Risk Trigger',
                'keyword_pattern' => 'REVISI BS,KOREKSI,OVERRIDE',
                'aktif' => true,
            ],
            [
                'rule_id' => 'RISK_SEL_01',
                'rule_type' => 'Risk Trigger',
                'keyword_pattern' => 'SELISIH KAS,PEMBULATAN KAS',
                'aktif' => true,
            ],
            [
                'rule_id' => 'CLS_KRD_01',
                'rule_type' => 'Classification',
                'keyword_pattern' => 'PENCAIRAN KREDIT,PROVISI KREDIT',
                'aktif' => true,
            ],
            [
                'rule_id' => 'WL_001',
                'rule_type' => 'Whitelist',
                'keyword_pattern' => 'BIAYA GAJI,HONORARIUM,GAJI DAN TUNJ,PENGHASILAN TETAP',
                'aktif' => true,
            ],
        ];

        foreach ($rules as $rule) {
            RuleEngine::updateOrCreate(['rule_id' => $rule['rule_id']], $rule);
        }
    }
}
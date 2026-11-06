<?php

namespace App\Http\Controllers\Onsite\Concerns;

use Illuminate\Http\Request;

trait HandlesKkaChecklistUpdate
{
    /**
     * Memproses update hasil checklist & penilaian risiko KKA secara standar
     */
    protected function processKkaUpdate(Request $request, $item, string $sheetName)
    {
        if ($item->status_review === 'Approved') {
            return response()->json([
                'status'  => 'error',
                'message' => "KKA {$sheetName} sudah Approved dan tidak dapat diubah."
            ], 403);
        }

        // Parsing checklist answers
        $rawChecklist = $request->input('checklist_answers');
        if (is_string($rawChecklist)) {
            $rawChecklist = json_decode($rawChecklist, true) ?: [];
        }

        $tidakCount = 0;
        if (is_array($rawChecklist)) {
            foreach ($rawChecklist as $ans) {
                $jwb = $ans['jawaban'] ?? '';
                if ($jwb === 'Tidak' || $jwb === '✗') {
                    $tidakCount++;
                }
            }
        }

        $statusUji = $request->input('status_uji', $tidakCount > 0 ? 'Ada Exception' : 'Sesuai');
        if ($tidakCount > 0) {
            $statusUji = 'Ada Exception';
        }

        // Skor risiko otomatis
        $skorDampak = (int) ($request->input('skor_dampak', 1));
        $skorKemungkinan = (int) ($request->input('skor_kemungkinan', 1));
        $skorRisiko = $skorDampak * $skorKemungkinan;

        $criticalTrigger = $request->input('critical_trigger', 'Tidak');
        $riskLevel = 'Low';
        if ($criticalTrigger === 'Ya' || $skorRisiko >= 20) {
            $riskLevel = 'Kritis';
        } elseif ($skorRisiko >= 12) {
            $riskLevel = 'High';
        } elseif ($skorRisiko >= 6) {
            $riskLevel = 'Moderate';
        }

        $rawSubAreas = $request->input('selected_sub_areas');
        if (is_string($rawSubAreas)) {
            $rawSubAreas = json_decode($rawSubAreas, true) ?: [];
        }

        $updateData = [
            'checklist_answers'  => $rawChecklist,
            'selected_sub_areas' => $rawSubAreas,
            'status_uji'         => $statusUji,
            'jenis_exception'    => $request->input('jenis_exception'),
            'kondisi'            => $request->input('kondisi'),
            'kriteria'           => $request->input('kriteria'),
            'sebab'              => $request->input('sebab'),
            'dampak'             => $request->input('dampak'),
            'hasil_uji'          => $request->input('hasil_uji', $request->input('simpulan_ra')),
            'klarifikasi_unit'   => $request->input('klarifikasi_unit'),
            'skor_dampak'        => $skorDampak,
            'skor_kemungkinan'   => $skorKemungkinan,
            'skor_risiko'        => $skorRisiko,
            'risk_level'         => $riskLevel,
            'critical_trigger'   => $criticalTrigger,
            'simpulan_ra'        => $request->input('simpulan_ra', $request->input('hasil_uji')),
            'status_klarifikasi' => $request->input('status_klarifikasi', 'Draft'),
        ];

        if ($request->hasFile('file_bukti')) {
            $path = $request->file('file_bukti')->store('onsite_bukti', 'public');
            $updateData['file_bukti'] = $path;
        }

        $userRole = strtolower(auth()->user()->role ?? '');
        $isReviewer = in_array($userRole, ['admin', 'kabag_ra', 'kadiv_skai']);

        if (!$isReviewer) {
            $updateData['status_review'] = 'Menunggu Review';
        } elseif ($request->filled('status_review')) {
            $updateData['status_review'] = $request->input('status_review');
            $updateData['reviewed_by']   = auth()->user()->name ?? 'Reviewer';
            $updateData['reviewed_at']   = now();
        }
        if ($request->has('catatan_reviewer')) {
            $updateData['catatan_reviewer'] = $request->input('catatan_reviewer');
        }

        $item->update($updateData);

        return response()->json([
            'status'  => 'success',
            'message' => "Pengujian KKA {$sheetName} berhasil disimpan.",
            'data'    => $item
        ]);
    }

    /**
     * Memproses review / approval atasan
     */
    protected function processKkaReview(Request $request, $item, string $sheetName)
    {
        $request->validate([
            'status_review'    => 'required|string',
            'catatan_reviewer' => 'nullable|string',
        ]);

        $item->update([
            'status_review'    => $request->status_review,
            'catatan_reviewer' => $request->catatan_reviewer,
            'reviewed_by'      => auth()->user()->name ?? 'Reviewer',
            'reviewed_at'      => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Review KKA {$sheetName} berhasil diperbarui.",
            'data'    => $item
        ]);
    }
}


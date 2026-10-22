<?php

namespace App\Http\Controllers\RiskTrigger;

use App\Http\Controllers\Controller;
use App\Models\RiskTrigger\RegisterTrigger;
use App\Models\RiskTrigger\AssignmentScope;
use App\Models\RiskTrigger\ProgramKerjaData;
use App\Models\RiskTrigger\MasterParameter;
use App\Models\RiskTrigger\Kka\RiskTriggerKkaInduk;
use App\Models\RiskTrigger\Kka\KkaTellerKas;
use App\Models\RiskTrigger\Kka\KkaDpkLayanan;
use App\Models\RiskTrigger\Kka\KkaKredit;
use App\Models\RiskTrigger\Kka\KkaBiayaBeban;
use App\Models\RiskTrigger\Kka\KkaAtm;
use App\Models\RiskTrigger\Kka\KkaPengaduan;
use App\Models\RiskTrigger\Kka\KkaFisik;
use App\Models\RiskTrigger\Kka\KkaObservasi;
use App\Models\RiskTrigger\SimpulanTl;
use App\Models\RiskTrigger\KlarifikasiBukti;
use App\Models\Unit;
use App\Models\Cabang;
use App\Models\User;
use App\Services\RiskTrigger\RiskTriggerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiskTriggerController extends Controller
{
    protected RiskTriggerService $service;

    public function __construct(RiskTriggerService $service)
    {
        $this->service = $service;
    }

    /**
     * Cek apakah user adalah role RA
     */
    protected function isRa(?User $user = null): bool
    {
        $u = $user ?: Auth::user();
        return $u && strtolower($u->role) === 'ra';
    }

    /**
     * Cek apakah user adalah Admin / Reviewer
     */
    protected function isAdmin(?User $user = null): bool
    {
        return !$this->isRa($user);
    }

    /**
     * Validasi isolasi cabang untuk akses detail trigger
     */
    protected function checkBranchAccess(RegisterTrigger $trigger, ?User $user = null): void
    {
        $u = $user ?: Auth::user();
        if ($this->isRa($u)) {
            $allowedCodes = RiskTriggerService::getAccessibleUnitCodes($u) ?? [];
            if (!in_array($trigger->kode_unit, $allowedCodes)) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengakses data trigger milik cabang lain.');
            }
        }
    }

    /**
     * Validasi isolasi cabang untuk akses Assignment Scope
     */
    protected function checkAssignmentBranchAccess(AssignmentScope $assignment, ?User $user = null): void
    {
        $u = $user ?: Auth::user();
        if ($this->isRa($u)) {
            $allowedCodes = RiskTriggerService::getAccessibleUnitCodes($u) ?? [];
            if (!in_array($assignment->kode_unit, $allowedCodes)) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengakses penugasan visit milik cabang lain.');
            }
        }
    }

    /**
     * Validasi isolasi cabang untuk akses Program Kerja & Data
     */
    protected function checkProgramKerjaBranchAccess(ProgramKerjaData $pk, ?User $user = null): void
    {
        $u = $user ?: Auth::user();
        if ($this->isRa($u)) {
            $allowedCodes = RiskTriggerService::getAccessibleUnitCodes($u) ?? [];
            if (!in_array($pk->kode_unit, $allowedCodes)) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengakses program kerja audit milik cabang lain.');
            }
        }
    }

    /**
     * Validasi isolasi cabang untuk akses KKA Induk & KKA Area
     */
    protected function checkKkaBranchAccess(RiskTriggerKkaInduk $kka, ?User $user = null): void
    {
        $u = $user ?: Auth::user();
        if ($this->isRa($u)) {
            $allowedCodes = RiskTriggerService::getAccessibleUnitCodes($u) ?? [];
            if (!in_array($kka->kode_unit, $allowedCodes)) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengakses KKA milik cabang lain.');
            }
        }
    }

    /**
     * Validasi isolasi cabang untuk akses Simpulan & Tindak Lanjut (Fase 4)
     */
    protected function checkSimpulanTlBranchAccess(SimpulanTl $simpulan, ?User $user = null): void
    {
        $u = $user ?: Auth::user();
        if ($this->isRa($u)) {
            $allowedCodes = RiskTriggerService::getAccessibleUnitCodes($u) ?? [];
            if (!in_array($simpulan->kode_unit, $allowedCodes)) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengakses Simpulan & TL milik cabang lain.');
            }
        }
    }

    /**
     * Validasi isolasi cabang untuk akses Klarifikasi & Bukti (Fase 4)
     */
    protected function checkKlarifikasiBranchAccess(KlarifikasiBukti $row, ?User $user = null): void
    {
        $u = $user ?: Auth::user();
        if ($this->isRa($u)) {
            $allowedCodes = RiskTriggerService::getAccessibleUnitCodes($u) ?? [];
            if (!in_array($row->kode_unit, $allowedCodes)) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengakses Klarifikasi & Bukti milik cabang lain.');
            }
        }
    }

    /**
     * Dashboard Ringkasan Control (Tab Utama SOP 06)
     */
    public function control(Request $request)
    {
        $user = Auth::user();
        $isRa = $this->isRa($user);
        $filterKodeUnit = $isRa ? null : $request->query('kode_unit');

        $stats = $this->service->getRingkasanControlStats($user, $filterKodeUnit);

        // Ambil daftar unit yang sesuai dengan hak akses
        if ($isRa) {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses')
                ? $user->cabangIdYangDapatDiakses()
                : [$user->cabang_id];
            $units = Unit::whereIn('cabang_id', $allowedCabangIds)->orderBy('unit_code', 'asc')->get();
            $userCabangName = $user->cabang?->nama_cabang ?? 'Cabang Anda';
        } else {
            $units = Unit::orderBy('unit_code', 'asc')->get();
            $userCabangName = 'Semua Cabang (Global)';
        }

        return view('risk_trigger.control', [
            'kpi' => $stats['kpi'],
            'keputusanSummary' => $stats['keputusan_summary'],
            'matrixArea' => $stats['matrix_area'],
            'alarms' => $stats['alarms'],
            'units' => $units,
            'isRa' => $isRa,
            'filterKodeUnit' => $filterKodeUnit,
            'userCabangName' => $userCabangName,
            'standarAreas' => RegisterTrigger::$STANDAR_AREAS,
            'standarKeputusan' => RegisterTrigger::$STANDAR_KEPUTUSAN,
        ]);
    }

    /**
     * Halaman Master Register Trigger (37 Kolom Excel)
     */
    public function register(Request $request)
    {
        $user = Auth::user();
        $isRa = $this->isRa($user);
        $query = RegisterTrigger::query();

        // Scope Cabang untuk role RA
        if ($isRa) {
            $query->byBranch($user);
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses')
                ? $user->cabangIdYangDapatDiakses()
                : [$user->cabang_id];
            $units = Unit::whereIn('cabang_id', $allowedCabangIds)->orderBy('unit_code', 'asc')->get();
            $userCabangName = $user->cabang?->nama_cabang ?? 'Cabang Anda';
        } else {
            // Filter Cabang / Kode Unit untuk Admin
            if ($request->filled('kode_unit')) {
                $expanded = RiskTriggerService::expandUnitCode($request->kode_unit);
                $query->whereIn('kode_unit', $expanded);
            }
            $units = Unit::orderBy('unit_code', 'asc')->get();
            $userCabangName = 'Semua Cabang (Global)';
        }

        // Filter Area
        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }

        // Filter Risk Level
        if ($request->filled('risk_level')) {
            $query->where('risk_level_reassessment', $request->risk_level);
        }

        // Filter Keputusan Final
        if ($request->filled('keputusan_final')) {
            $query->where('keputusan_final', $request->keputusan_final);
        }

        // Filter Status Workflow
        if ($request->filled('status_workflow')) {
            $query->where('status_workflow', $request->status_workflow);
        }

        // Filter Status Duplikat
        if ($request->filled('status_duplikat')) {
            $query->where('status_duplikat', $request->status_duplikat);
        }

        // Filter Search Keyword
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('trigger_id', 'like', "%{$q}%")
                    ->orWhere('source_wp_id', 'like', "%{$q}%")
                    ->orWhere('uraian_trigger', 'like', "%{$q}%")
                    ->orWhere('objek_key', 'like', "%{$q}%")
                    ->orWhere('nama_unit', 'like', "%{$q}%");
            });
        }

        $triggers = $query->latest('id')->paginate(25)->withQueryString();
        $alarms = $this->service->getActiveAlarms($user, $isRa ? null : $request->kode_unit);

        return view('risk_trigger.register', [
            'triggers' => $triggers,
            'alarms' => $alarms,
            'units' => $units,
            'isRa' => $isRa,
            'userCabangName' => $userCabangName,
            'standarAreas' => RegisterTrigger::$STANDAR_AREAS,
            'standarKeputusan' => RegisterTrigger::$STANDAR_KEPUTUSAN,
        ]);
    }

    /**
     * Halaman Input Trigger Manual (Dedicated Page) - KHUSUS ROLE ADMIN
     */
    public function create()
    {
        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Hanya Admin / Reviewer yang memiliki wewenang menginput Trigger Manual.');
        }

        $units = Unit::orderBy('unit_code', 'asc')->get();

        return view('risk_trigger.create', [
            'units' => $units,
            'standarAreas' => RegisterTrigger::$STANDAR_AREAS,
            'standarKeputusan' => RegisterTrigger::$STANDAR_KEPUTUSAN,
        ]);
    }

    /**
     * Halaman Detail Lengkap 37 Kolom Trigger (Dedicated Page)
     */
    public function show($id)
    {
        $trigger = RegisterTrigger::with('assignmentScope')->findOrFail($id);
        $this->checkBranchAccess($trigger);

        return view('risk_trigger.show', [
            'trigger' => $trigger,
            'isRa' => $this->isRa(),
            'standarKeputusan' => RegisterTrigger::$STANDAR_KEPUTUSAN,
        ]);
    }

    /**
     * Halaman Override Keputusan (Dedicated Page) - KHUSUS ROLE ADMIN
     */
    public function editOverride($id)
    {
        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Hanya Admin / Reviewer yang memiliki wewenang melakukan Override Keputusan.');
        }

        $trigger = RegisterTrigger::findOrFail($id);

        return view('risk_trigger.override', [
            'trigger' => $trigger,
            'standarKeputusan' => RegisterTrigger::$STANDAR_KEPUTUSAN,
        ]);
    }

    /**
     * 1-Click Sync Trigger dari Daily Register & Offsite
     */
    public function sync(Request $request)
    {
        $user = Auth::user();
        $isRa = $this->isRa($user);
        $filterKodeUnit = $isRa ? null : $request->input('kode_unit');

        try {
            $stats = $this->service->syncAll($user, $filterKodeUnit);
            $targetLabel = $isRa ? "cabang penempatan Anda" : ($filterKodeUnit ? "cabang {$filterKodeUnit}" : "seluruh cabang");
            $msg = "Sinkronisasi untuk {$targetLabel} berhasil! {$stats['total_new']} trigger baru ditarik (Daily Register: {$stats['daily_register']}, KKA Fisik: {$stats['physical_kka']}, Offsite: {$stats['offsite']}).";
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyinkronkan data trigger: ' . $e->getMessage());
        }
    }

    /**
     * Input / Import Trigger Manual - KHUSUS ROLE ADMIN
     */
    public function storeManual(Request $request)
    {
        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Hanya Admin / Reviewer yang memiliki wewenang menginput Trigger Manual.');
        }

        $validated = $request->validate([
            'sumber' => 'required|in:Surprise,Monitoring TL,Pengaduan,Informasi Manajemen,Daily Review,Offsite,Onsite',
            'source_wp_id' => 'nullable|string|max:100',
            'tanggal_trigger' => 'required|date',
            'kode_unit' => 'required|string|max:20',
            'nama_unit' => 'nullable|string|max:150',
            'area' => 'required|string|max:50',
            'objek_key' => 'required|string|max:255',
            'periode_mulai' => 'nullable|date',
            'periode_selesai' => 'nullable|date',
            'uraian_trigger' => 'required|string',
            'bukti_awal' => 'required|string',
            'risk_score_sumber' => 'required|numeric|min:0|max:25',
            'jumlah_kejadian' => 'required|integer|min:1',
            'eksposur_nominal' => 'nullable|numeric|min:0',
            'perlu_verifikasi_lapangan' => 'required|in:Ya,Tidak',
            'indikasi_fraud' => 'required|in:Ya,Tidak',
            'objek_surprise_fisik' => 'required|in:Ya,Tidak',
        ]);

        // Resolusi nama_unit otomatis jika belum terisi
        if (empty($validated['nama_unit'])) {
            $unit = Unit::where('unit_code', $validated['kode_unit'])->first();
            $validated['nama_unit'] = $unit ? $unit->unit_name : "Kantor Cabang {$validated['kode_unit']}";
        }

        try {
            $trigger = $this->service->createManualTrigger($validated, Auth::user());
            return redirect()->route('risk-trigger.show', $trigger->id)->with('success', "Trigger manual {$trigger->trigger_id} untuk unit {$trigger->kode_unit} - {$trigger->nama_unit} berhasil dibuat dan dihitung secara otomatis.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan trigger manual: ' . $e->getMessage());
        }
    }

    /**
     * Override Keputusan oleh Reviewer / Admin (Kolom AB, AC, AK SOP 06) - KHUSUS ROLE ADMIN
     */
    public function override(Request $request, $id)
    {
        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Hanya Admin / Reviewer yang memiliki wewenang melakukan Override Keputusan.');
        }

        $trigger = RegisterTrigger::findOrFail($id);

        $validated = $request->validate([
            'override_keputusan' => 'nullable|string|max:50',
            'alasan_override' => 'nullable|string|required_with:override_keputusan',
            'catatan_review' => 'nullable|string',
        ]);

        // Aturan Proteksi Fraud: Tidak boleh meng-override turun jika indikasi fraud == "Ya"
        if (strtoupper((string)$trigger->indikasi_fraud) === 'YA' && !empty($validated['override_keputusan']) && $validated['override_keputusan'] !== 'SOP 09 - PEMERIKSAAN AWAL FRAUD') {
            return redirect()->back()->with('error', 'Akses Ditolak: Temuan dengan Indikasi Fraud TIDAK BOLEH di-override ke keputusan lain selain SOP 09 - PEMERIKSAAN AWAL FRAUD.');
        }

        $trigger->override_keputusan = $validated['override_keputusan'] ?: null;
        $trigger->alasan_override = $validated['alasan_override'] ?: null;
        $trigger->catatan_review = $validated['catatan_review'] ?: null;
        $trigger->reviewer = Auth::user()->name;
        $trigger->calculateDerivedAttributes();
        $trigger->save();

        return redirect()->route('risk-trigger.show', $trigger->id)->with('success', "Keputusan dan catatan review trigger {$trigger->trigger_id} berhasil diperbarui oleh Reviewer.");
    }

    /**
     * Hapus trigger manual - KHUSUS ROLE ADMIN
     */
    public function destroy($id)
    {
        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Hanya Admin yang memiliki wewenang menghapus trigger manual.');
        }

        $trigger = RegisterTrigger::findOrFail($id);
        $idStr = $trigger->trigger_id;
        $trigger->delete();

        $this->service->recalculateDuplicatesAndAlarms();

        return redirect()->route('risk-trigger.register')->with('success', "Trigger {$idStr} berhasil dihapus.");
    }

    /**
     * Sub-Menu Assignment & Scope (22 Kolom Excel)
     */
    public function assignment(Request $request)
    {
        $user = Auth::user();
        $isRa = $this->isRa($user);
        $query = AssignmentScope::with(['registerTrigger', 'programKerjaData']);

        if ($isRa) {
            $query->byBranch($user);
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses')
                ? $user->cabangIdYangDapatDiakses()
                : [$user->cabang_id];
            $units = Unit::whereIn('cabang_id', $allowedCabangIds)->orderBy('unit_code', 'asc')->get();
            $userCabangName = $user->cabang?->nama_cabang ?? 'Cabang Anda';
            $filterKodeUnit = null;
        } else {
            $filterKodeUnit = $request->query('kode_unit');
            if (!empty($filterKodeUnit)) {
                $expanded = RiskTriggerService::expandUnitCode($filterKodeUnit);
                $query->whereIn('kode_unit', $expanded);
            }
            $units = Unit::orderBy('unit_code', 'asc')->get();
            $userCabangName = 'Semua Cabang (Global)';
        }

        if ($request->filled('status_assignment')) {
            $query->where('status_assignment', $request->query('status_assignment'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('visit_id', 'like', "%{$search}%")
                    ->orWhere('trigger_id', 'like', "%{$search}%")
                    ->orWhere('nama_unit', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%")
                    ->orWhere('ra_pelaksana', 'like', "%{$search}%")
                    ->orWhere('referensi_otorisasi', 'like', "%{$search}%");
            });
        }

        $assignments = (clone $query)->orderBy('id', 'desc')->paginate(25)->withQueryString();

        $stats = [
            'total' => (clone $query)->count(),
            'siap' => (clone $query)->where('status_assignment', 'SIAP DILAKSANAKAN')->count(),
            'belum_lengkap' => (clone $query)->where('status_assignment', 'BELUM LENGKAP')->count(),
            'bukan_visit' => (clone $query)->where('status_assignment', 'BUKAN KEPUTUSAN VISIT')->count(),
        ];

        return view('risk_trigger.assignment.index', [
            'assignments' => $assignments,
            'stats' => $stats,
            'units' => $units,
            'isRa' => $isRa,
            'filterKodeUnit' => $filterKodeUnit,
            'userCabangName' => $userCabangName,
        ]);
    }

    /**
     * Form Buat Assignment Visit Baru - KHUSUS ROLE ADMIN
     */
    public function createAssignment(Request $request)
    {
        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Hanya Admin / Reviewer yang memiliki wewenang membuat Assignment Visit.');
        }

        // Ambil trigger berstatus RISK TRIGGER VISIT yang belum dibuatkan assignment
        $eligibleTriggers = RegisterTrigger::where('keputusan_final', RegisterTrigger::KEPUTUSAN_RISK_TRIGGER_VISIT)
            ->whereDoesntHave('assignmentScope')
            ->orderBy('id', 'desc')
            ->get();

        // Hitung jumlah trigger siap visit per kode_unit
        $triggerCountByUnit = [];
        foreach ($eligibleTriggers as $t) {
            $code = $t->kode_unit;
            $triggerCountByUnit[$code] = ($triggerCountByUnit[$code] ?? 0) + 1;
            $clean = ltrim($code, '0');
            if ($clean !== $code && $clean !== '') {
                $triggerCountByUnit[$clean] = ($triggerCountByUnit[$clean] ?? 0) + 1;
            }
            $pad = str_pad($code, 3, '0', STR_PAD_LEFT);
            if ($pad !== $code) {
                $triggerCountByUnit[$pad] = ($triggerCountByUnit[$pad] ?? 0) + 1;
            }
        }

        $selectedTriggerId = $request->query('trigger_id');
        $selectedTrigger = null;
        if ($selectedTriggerId) {
            $selectedTrigger = RegisterTrigger::where('id', $selectedTriggerId)
                ->orWhere('trigger_id', $selectedTriggerId)
                ->first();
        }

        $selectedKodeUnit = $request->query('kode_unit', $selectedTrigger?->kode_unit);

        $units = Unit::with('cabang')->orderBy('unit_code', 'asc')->get();
        $raUsers = User::with('cabang')->whereRaw('LOWER(role) = ?', ['ra'])->orderBy('name', 'asc')->get();

        // Map RA per unit/cabang
        $branchRaMap = [];
        foreach ($units as $u) {
            $ra = $raUsers->first(function ($user) use ($u) {
                return $user->cabang_id && $user->cabang_id == $u->cabang_id;
            });
            if ($ra) {
                $branchRaMap[$u->unit_code] = $ra->name;
                $clean = ltrim($u->unit_code, '0');
                if ($clean !== '') {
                    $branchRaMap[$clean] = $ra->name;
                }
                $branchRaMap[str_pad($u->unit_code, 3, '0', STR_PAD_LEFT)] = $ra->name;
            }
        }

        return view('risk_trigger.assignment.create', [
            'eligibleTriggers' => $eligibleTriggers,
            'selectedTrigger' => $selectedTrigger,
            'selectedKodeUnit' => $selectedKodeUnit,
            'triggerCountByUnit' => $triggerCountByUnit,
            'units' => $units,
            'raUsers' => $raUsers,
            'branchRaMap' => $branchRaMap,
        ]);
    }

    /**
     * Simpan Assignment Visit & Generate Program Kerja - KHUSUS ROLE ADMIN
     */
    public function storeAssignment(Request $request)
    {
        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Hanya Admin / Reviewer yang memiliki wewenang membuat Assignment Visit.');
        }

        $validated = $request->validate([
            'trigger_id' => 'required',
            'tanggal_visit' => 'required|date',
            'ra_pelaksana' => 'required|string|max:150',
            'reviewer' => 'required|string|max:150',
            'referensi_otorisasi' => 'required|string|max:150',
            'klasifikasi' => 'required|string|max:100',
            'batas_scope' => 'nullable|string',
            'kriteria_selesai' => 'nullable|string',
            'langkah_tambahan_ra' => 'nullable|string',
        ]);

        $trigger = RegisterTrigger::where('id', $validated['trigger_id'])
            ->orWhere('trigger_id', $validated['trigger_id'])
            ->firstOrFail();

        try {
            $assignment = $this->service->createAssignmentFromTrigger($trigger, $validated, Auth::user());
            return redirect()->route('risk-trigger.assignment')
                ->with('success', "Penugasan Visit {$assignment->visit_id} berhasil diterbitkan untuk Trigger {$trigger->trigger_id} dan Program Kerja Otomatis telah dibuat.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat assignment visit: ' . $e->getMessage());
        }
    }

    /**
     * Detail Assignment Scope
     */
    public function showAssignment($id)
    {
        $assignment = AssignmentScope::with(['registerTrigger', 'programKerjaData'])->findOrFail($id);
        $this->checkAssignmentBranchAccess($assignment);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $assignment,
            ]);
        }

        return $this->editActualDates($id);
    }

    /**
     * Halaman Dedicated: Update Tanggal Mulai dan Selesai Aktual Lapangan (Role RA / Admin)
     */
    public function editActualDates($id)
    {
        $assignment = AssignmentScope::with(['registerTrigger', 'programKerjaData'])->findOrFail($id);
        $this->checkAssignmentBranchAccess($assignment);

        return view('risk_trigger.assignment.edit_dates', [
            'assignment' => $assignment,
            'isRa' => $this->isRa(),
        ]);
    }

    /**
     * Update Tanggal Mulai dan Selesai Aktual Lapangan (Role RA / Admin)
     */
    public function updateActualDates(Request $request, $id)
    {
        $assignment = AssignmentScope::findOrFail($id);
        $this->checkAssignmentBranchAccess($assignment);

        $validated = $request->validate([
            'mulai_aktual' => 'nullable|date',
            'selesai_aktual' => 'nullable|date|after_or_equal:mulai_aktual',
        ]);

        try {
            $this->service->updateAssignmentActualDates($assignment, $validated, Auth::user());
            return redirect()->route('risk-trigger.assignment')->with('success', "Tanggal pelaksanaan aktual untuk penugasan {$assignment->visit_id} berhasil disimpan.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui tanggal pelaksanaan: ' . $e->getMessage());
        }
    }

    /**
     * Sub-Menu Program Kerja Terarah & Daftar Data (18 Kolom Excel)
     */
    public function programKerja(Request $request)
    {
        $user = Auth::user();
        $isRa = $this->isRa($user);
        $query = ProgramKerjaData::with(['assignmentScope', 'registerTrigger']);

        if ($isRa) {
            $query->byBranch($user);
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses')
                ? $user->cabangIdYangDapatDiakses()
                : [$user->cabang_id];
            $units = Unit::whereIn('cabang_id', $allowedCabangIds)->orderBy('unit_code', 'asc')->get();
            $userCabangName = $user->cabang?->nama_cabang ?? 'Cabang Anda';
            $filterKodeUnit = null;
        } else {
            $filterKodeUnit = $request->query('kode_unit');
            if (!empty($filterKodeUnit)) {
                $expanded = RiskTriggerService::expandUnitCode($filterKodeUnit);
                $query->whereIn('kode_unit', $expanded);
            }
            $units = Unit::orderBy('unit_code', 'asc')->get();
            $userCabangName = 'Semua Cabang (Global)';
        }

        if ($request->filled('area')) {
            $query->where('area', $request->query('area'));
        }

        if ($request->filled('status_kesiapan')) {
            $query->where('status_kesiapan', $request->query('status_kesiapan'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('visit_id', 'like', "%{$search}%")
                    ->orWhere('trigger_id', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%")
                    ->orWhere('kka_tujuan', 'like', "%{$search}%")
                    ->orWhere('tujuan_uji', 'like', "%{$search}%")
                    ->orWhere('data_bukti_minimum', 'like', "%{$search}%");
            });
        }

        $programKerja = (clone $query)->orderBy('id', 'desc')->paginate(25)->withQueryString();

        $stats = [
            'total' => (clone $query)->count(),
            'siap_uji' => (clone $query)->where('status_kesiapan', 'SIAP UJI')->count(),
            'menunggu_data' => (clone $query)->where('status_kesiapan', 'MENUNGGU DATA')->count(),
            'belum_diminta' => (clone $query)->where('status_kesiapan', 'BELUM DIMINTA')->count(),
            'belum_siap' => (clone $query)->where('status_kesiapan', 'BELUM SIAP')->count(),
        ];

        return view('risk_trigger.program_kerja.index', [
            'programKerja' => $programKerja,
            'stats' => $stats,
            'units' => $units,
            'isRa' => $isRa,
            'filterKodeUnit' => $filterKodeUnit,
            'userCabangName' => $userCabangName,
            'standarAreas' => RegisterTrigger::$STANDAR_AREAS,
        ]);
    }

    /**
     * Halaman Dedicated: Input / Edit Pelaksanaan Kerja Lapangan & Kesiapan Data RA (Role RA / Admin)
     */
    public function editProgramKerja($id)
    {
        $pk = ProgramKerjaData::with(['assignmentScope', 'registerTrigger'])->findOrFail($id);
        $this->checkProgramKerjaBranchAccess($pk);

        return view('risk_trigger.program_kerja.edit', [
            'pk' => $pk,
            'isRa' => $this->isRa(),
        ]);
    }

    /**
     * Update Program Kerja Lapangan (Kolom N, O, R oleh RA Pelaksana)
     */
    public function updateProgramKerja(Request $request, $id)
    {
        $pk = ProgramKerjaData::with('assignmentScope')->findOrFail($id);
        $this->checkProgramKerjaBranchAccess($pk);

        // Hanya role RA yang diizinkan menginput data pelaksanaan kerja lapangan
        if (!$this->isRa()) {
            return redirect()->route('risk-trigger.program-kerja')
                ->with('error', 'Akses Ditolak: Halaman pelaksanaan kerja hanya dapat diisi oleh Resident Auditor (RA) pelaksana di unit kerja. Akun Admin / Reviewer memiliki hak baca (read-only).');
        }

        $validated = $request->validate([
            'tanggal_permintaan' => 'nullable|date',
            'tanggal_diterima' => 'nullable|date|after_or_equal:tanggal_permintaan',
            'catatan_akses_data' => 'nullable|string',
        ]);

        // Langkah tambahan RA ditetapkan oleh Admin saat Assignment Visit dan bersifat read-only bagi RA
        $validated['langkah_tambahan_ra'] = $pk->langkah_tambahan_ra;

        try {
            $this->service->updateProgramKerjaData($pk, $validated, Auth::user());
            return redirect()->route('risk-trigger.program-kerja')->with('success', "Program Kerja & status kesiapan pengujian untuk {$pk->visit_id} berhasil diperbarui.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui program kerja: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // FASE 3: KKA RISK TRIGGER (KKA INDUK - 20 KOLOM EXCEL) & 8 KKA AREA
    // =========================================================================

    /**
     * Sub-Menu KKA Induk (KKA_Risk_Trigger - 20 Kolom Excel A s.d. T)
     */
    public function kkaIndex(Request $request)
    {
        $user = Auth::user();
        $isRa = $this->isRa($user);

        // Pastikan sinkronisasi jika ada penugasan baru belum memiliki KKA Induk
        $this->service->syncAllKkaInduk();

        $query = RiskTriggerKkaInduk::with(['assignment', 'trigger', 'programKerja']);

        if ($isRa) {
            $allowedCodes = RiskTriggerService::getAccessibleUnitCodes($user) ?? [];
            $query->whereIn('kode_unit', $allowedCodes);
            $units = Unit::whereIn('unit_code', $allowedCodes)->orderBy('unit_code', 'asc')->get();
            $userCabangName = $user->cabang?->nama_cabang ?? 'Cabang Anda';
            $filterKodeUnit = null;
        } else {
            $filterKodeUnit = $request->query('kode_unit');
            if (!empty($filterKodeUnit)) {
                $expanded = RiskTriggerService::expandUnitCode($filterKodeUnit);
                $query->whereIn('kode_unit', $expanded);
            }
            $units = Unit::orderBy('unit_code', 'asc')->get();
            $userCabangName = 'Semua Cabang (Global)';
        }

        if ($request->filled('area')) {
            $query->where('area', $request->query('area'));
        }

        if ($request->filled('status_review')) {
            $query->where('status_review', $request->query('status_review'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('visit_id', 'like', "%{$search}%")
                    ->orWhere('trigger_id', 'like', "%{$search}%")
                    ->orWhere('unit_kantor', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%")
                    ->orWhere('kka_area', 'like', "%{$search}%")
                    ->orWhere('pertanyaan_audit', 'like', "%{$search}%")
                    ->orWhere('simpulan_ra', 'like', "%{$search}%");
            });
        }

        $kkas = (clone $query)->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $stats = [
            'total' => (clone $query)->count(),
            'draft' => (clone $query)->where('status_review', 'DRAFT / BELUM DIREVIEW')->count(),
            'revisi' => (clone $query)->where('status_review', 'REVISI')->count(),
            'final' => (clone $query)->where('status_review', 'FINAL')->count(),
            'total_exception' => (clone $query)->sum('exception_area'),
        ];

        return view('risk_trigger.kka.index', [
            'kkas' => $kkas,
            'stats' => $stats,
            'units' => $units,
            'isRa' => $isRa,
            'filterKodeUnit' => $filterKodeUnit,
            'userCabangName' => $userCabangName,
            'standarAreas' => RegisterTrigger::$STANDAR_AREAS,
        ]);
    }

    /**
     * Tampilan Detail & Input Pengujian KKA Induk + Tab 8 KKA Area
     */
    public function kkaEdit(Request $request, $id)
    {
        $kka = RiskTriggerKkaInduk::with(['assignment', 'trigger', 'programKerja'])->findOrFail($id);
        $this->checkKkaBranchAccess($kka);

        // Hitung ulang exception area real-time
        $kka->exception_area = $this->service->recalculateExceptionArea($kka->visit_id);

        $isRa = $this->isRa();
        $isFinal = ($kka->status_review === 'FINAL');
        $canModify = $this->service->canModifyKka($kka, Auth::user());

        // Ambil baris sampel untuk seluruh 8 KKA Area
        $tellerKasRows = KkaTellerKas::where('visit_id', $kka->visit_id)->orderBy('id', 'asc')->get();
        $dpkLayananRows = KkaDpkLayanan::where('visit_id', $kka->visit_id)->orderBy('id', 'asc')->get();
        $kreditRows = KkaKredit::where('visit_id', $kka->visit_id)->orderBy('id', 'asc')->get();
        $biayaBebanRows = KkaBiayaBeban::where('visit_id', $kka->visit_id)->orderBy('id', 'asc')->get();
        $atmRows = KkaAtm::where('visit_id', $kka->visit_id)->orderBy('id', 'asc')->get();
        $pengaduanRows = KkaPengaduan::where('visit_id', $kka->visit_id)->orderBy('id', 'asc')->get();
        $fisikRows = KkaFisik::where('visit_id', $kka->visit_id)->orderBy('id', 'asc')->get();
        $observasiRows = KkaObservasi::where('visit_id', $kka->visit_id)->orderBy('id', 'asc')->get();

        // Hitungan per Area untuk Badge Tab
        $areaCounts = [
            'KKA_Teller_Kas' => $tellerKasRows->count(),
            'KKA_DPK_Layanan' => $dpkLayananRows->count(),
            'KKA_Kredit' => $kreditRows->count(),
            'KKA_Biaya_Beban' => $biayaBebanRows->count(),
            'KKA_ATM' => $atmRows->count(),
            'KKA_Pengaduan' => $pengaduanRows->count(),
            'KKA_Fisik' => $fisikRows->count(),
            'KKA_Observasi' => $observasiRows->count(),
        ];

        // Area aktif default dari KKA Tujuan
        $activeTab = $request->query('tab', $kka->kka_area ?: 'KKA_Teller_Kas');

        return view('risk_trigger.kka.edit', [
            'kka' => $kka,
            'isRa' => $isRa,
            'isFinal' => $isFinal,
            'canModify' => $canModify,
            'tellerKasRows' => $tellerKasRows,
            'dpkLayananRows' => $dpkLayananRows,
            'kreditRows' => $kreditRows,
            'biayaBebanRows' => $biayaBebanRows,
            'atmRows' => $atmRows,
            'pengaduanRows' => $pengaduanRows,
            'fisikRows' => $fisikRows,
            'observasiRows' => $observasiRows,
            'areaCounts' => $areaCounts,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Alias kkaShow ke kkaEdit
     */
    public function kkaShow(Request $request, $id)
    {
        return $this->kkaEdit($request, $id);
    }

    /**
     * Update Data KKA Induk (Input Profesional RA & Status Review Admin)
     */
    public function kkaUpdate(Request $request, $id)
    {
        $kka = RiskTriggerKkaInduk::findOrFail($id);
        $this->checkKkaBranchAccess($kka);

        $user = Auth::user();
        $isRa = $this->isRa($user);

        if ($isRa) {
            if ($kka->status_review === 'FINAL') {
                return redirect()->back()->with('error', 'Akses Ditolak: KKA Induk ini telah berstatus FINAL dan dikunci oleh Reviewer.');
            }

            $validated = $request->validate([
                'ringkasan_bukti' => 'nullable|string',
                'hasil_analisis' => 'nullable|string',
                'trigger_terbukti' => 'nullable|in:Ya,Tidak,Sebagian',
                'perlu_perluasan' => 'required|in:Ya,Tidak',
                'alasan_perluasan' => 'nullable|string|required_if:perlu_perluasan,Ya',
                'simpulan_ra' => 'nullable|string',
            ], [
                'alasan_perluasan.required_if' => 'Alasan Perluasan wajib diisi jika Perlu Perluasan bernilai "Ya".',
            ]);

            // RA tidak berhak mengubah Status Review
            unset($validated['status_review']);
        } else {
            // Admin / Reviewer
            $validated = $request->validate([
                'status_review' => 'required|in:DRAFT / BELUM DIREVIEW,REVISI,FINAL',
                'ringkasan_bukti' => 'nullable|string',
                'hasil_analisis' => 'nullable|string',
                'trigger_terbukti' => 'nullable|in:Ya,Tidak,Sebagian',
                'perlu_perluasan' => 'nullable|in:Ya,Tidak',
                'alasan_perluasan' => 'nullable|string',
                'simpulan_ra' => 'nullable|string',
            ]);
        }

        try {
            $this->service->updateKkaInduk($kka, $validated, $user);
            return redirect()->route('risk-trigger.kka.edit', ['id' => $kka->id, 'tab' => $request->query('tab', $kka->kka_area)])
                ->with('success', "KKA Induk untuk {$kka->visit_id} berhasil diperbarui.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui KKA Induk: ' . $e->getMessage());
        }
    }

    /**
     * Tambah Baris Sampel Baru pada Salah Satu dari 8 KKA Area
     */
    public function storeAreaRow(Request $request, $id)
    {
        $kka = RiskTriggerKkaInduk::findOrFail($id);
        $this->checkKkaBranchAccess($kka);

        $user = Auth::user();
        if ($this->isRa($user) && $kka->status_review === 'FINAL') {
            return redirect()->back()->with('error', 'Akses Ditolak: KKA telah FINAL dan dikunci.');
        }

        $area = $request->input('kka_area', $kka->kka_area);
        $data = $request->except(['_token', 'kka_area']);

        try {
            $this->service->storeAreaRow($area, $kka->visit_id, $data, $user);
            return redirect()->route('risk-trigger.kka.edit', ['id' => $kka->id, 'tab' => $area])
                ->with('success', "Baris sampel pengujian berhasil ditambahkan ke {$area}.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan baris sampel: ' . $e->getMessage());
        }
    }

    /**
     * Update Baris Sampel pada KKA Area
     */
    public function updateAreaRow(Request $request, $area, $rowId)
    {
        $modelClass = RiskTriggerService::getAreaModelClass($area);
        if (!$modelClass) {
            return redirect()->back()->with('error', "Area {$area} tidak valid.");
        }

        $row = $modelClass::findOrFail($rowId);
        $kka = RiskTriggerKkaInduk::where('visit_id', $row->visit_id)->firstOrFail();
        $this->checkKkaBranchAccess($kka);

        $user = Auth::user();
        if ($this->isRa($user) && $kka->status_review === 'FINAL') {
            return redirect()->back()->with('error', 'Akses Ditolak: KKA telah FINAL dan dikunci.');
        }

        $data = $request->except(['_token', '_method']);

        try {
            $this->service->updateAreaRow($area, $rowId, $data, $user);
            return redirect()->route('risk-trigger.kka.edit', ['id' => $kka->id, 'tab' => $area])
                ->with('success', "Baris sampel pada {$area} berhasil diperbarui.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui baris sampel: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Baris Sampel pada KKA Area
     */
    public function destroyAreaRow(Request $request, $area, $rowId)
    {
        $modelClass = RiskTriggerService::getAreaModelClass($area);
        if (!$modelClass) {
            return redirect()->back()->with('error', "Area {$area} tidak valid.");
        }

        $row = $modelClass::findOrFail($rowId);
        $kka = RiskTriggerKkaInduk::where('visit_id', $row->visit_id)->firstOrFail();
        $this->checkKkaBranchAccess($kka);

        $user = Auth::user();
        if ($this->isRa($user) && $kka->status_review === 'FINAL') {
            return redirect()->back()->with('error', 'Akses Ditolak: KKA telah FINAL dan dikunci.');
        }

        try {
            $this->service->deleteAreaRow($area, $rowId, $user);
            return redirect()->route('risk-trigger.kka.edit', ['id' => $kka->id, 'tab' => $area])
                ->with('success', "Baris sampel pada {$area} berhasil dihapus.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus baris sampel: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     * FASE 4: SIMPULAN, TINDAK LANJUT & CLOSURE (Simpulan_TL) & KLARIFIKASI BUKTI
     * ========================================================================= */

    /**
     * Halaman Utama Simpulan & Tindak Lanjut (23 Kolom Excel: A s.d. W)
     */
    public function simpulanTlIndex(Request $request)
    {
        $user = Auth::user();
        $isRa = $this->isRa($user);

        // Pastikan seluruh assignment yang ada memiliki baris Simpulan TL
        $this->service->syncAllSimpulanTl();

        $query = SimpulanTl::with(['assignment', 'trigger', 'kkaInduk']);

        if ($isRa) {
            $query->byBranch($user);
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses')
                ? $user->cabangIdYangDapatDiakses()
                : [$user->cabang_id];
            $units = Unit::whereIn('cabang_id', $allowedCabangIds)->orderBy('unit_code', 'asc')->get();
            $userCabangName = $user->cabang?->nama_cabang ?? 'Cabang Anda';
        } else {
            if ($request->filled('kode_unit')) {
                $expanded = RiskTriggerService::expandUnitCode($request->kode_unit);
                $query->whereIn('kode_unit', $expanded);
            }
            $units = Unit::orderBy('unit_code', 'asc')->get();
            $userCabangName = 'Semua Cabang (Global)';
        }

        if ($request->filled('status_closure')) {
            $query->where('status_closure', $request->status_closure);
        }

        if ($request->filled('status_review')) {
            $query->where('status_review', $request->status_review);
        }

        if ($request->filled('status_klarifikasi')) {
            $query->where('status_klarifikasi', $request->status_klarifikasi);
        }

        if ($request->filled('klasifikasi_hasil')) {
            $query->where('klasifikasi_hasil', $request->klasifikasi_hasil);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('visit_id', 'like', "%{$q}%")
                  ->orWhere('trigger_id', 'like', "%{$q}%")
                  ->orWhere('unit_kantor', 'like', "%{$q}%")
                  ->orWhere('area', 'like', "%{$q}%")
                  ->orWhere('simpulan_akhir', 'like', "%{$q}%")
                  ->orWhere('pic_unit', 'like', "%{$q}%");
            });
        }

        $simpulanList = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Rekapitulasi status untuk KPI card di atas tabel
        $summaryQuery = SimpulanTl::query();
        if ($isRa) {
            $summaryQuery->byBranch($user);
        } elseif ($request->filled('kode_unit')) {
            $summaryQuery->whereIn('kode_unit', RiskTriggerService::expandUnitCode($request->kode_unit));
        }
        $allRecords = $summaryQuery->get();

        $stats = [
            'total' => $allRecords->count(),
            'closed' => $allRecords->where('status_closure', 'CLOSED/DITERUSKAN')->count(),
            'belum_ditutup' => $allRecords->where('status_closure', 'BELUM DITUTUP')->count(),
            'tl_belum_lengkap' => $allRecords->where('status_closure', 'TL BELUM LENGKAP')->count(),
            'klarifikasi_open' => $allRecords->where('status_closure', 'KLARIFIKASI BELUM SELESAI')->count(),
            'final_review' => $allRecords->where('status_review', 'FINAL')->count(),
        ];

        return view('risk_trigger.simpulan_tl.index', [
            'simpulanList' => $simpulanList,
            'stats' => $stats,
            'units' => $units,
            'isRa' => $isRa,
            'userCabangName' => $userCabangName,
            'filterKodeUnit' => $request->query('kode_unit'),
            'filterStatusClosure' => $request->query('status_closure'),
            'filterStatusReview' => $request->query('status_review'),
        ]);
    }

    /**
     * Lembar Kerja Simpulan, Tindak Lanjut & Register Klarifikasi (Form Interaktif)
     */
    public function simpulanTlEdit(Request $request, $id)
    {
        $simpulan = SimpulanTl::with(['assignment', 'trigger', 'kkaInduk'])->findOrFail($id);
        $this->checkSimpulanTlBranchAccess($simpulan);

        $user = Auth::user();
        $isRa = $this->isRa($user);

        // Kunci jika RA dan status sudah FINAL & CLOSED
        $isLockedForRa = $isRa && ($simpulan->status_review === 'FINAL' && $simpulan->status_closure === 'CLOSED/DITERUSKAN');

        // Daftar klarifikasi bukti untuk Visit ID ini
        $klarifikasiList = $this->service->getKlarifikasiList($simpulan->visit_id, $user);

        // KKA Induk terkait
        $kkaInduk = $simpulan->kkaInduk ?: RiskTriggerKkaInduk::where('visit_id', $simpulan->visit_id)->first();

        // Exception count dari 8 KKA Area
        $exceptionSummary = [
            'teller_kas' => KkaTellerKas::where('visit_id', $simpulan->visit_id)->where('exception', 'Ya')->count(),
            'dpk_layanan' => KkaDpkLayanan::where('visit_id', $simpulan->visit_id)->where('exception', 'Ya')->count(),
            'kredit' => KkaKredit::where('visit_id', $simpulan->visit_id)->where('exception', 'Ya')->count(),
            'biaya_beban' => KkaBiayaBeban::where('visit_id', $simpulan->visit_id)->where('exception', 'Ya')->count(),
            'atm' => KkaAtm::where('visit_id', $simpulan->visit_id)->where('exception', 'Ya')->count(),
            'pengaduan' => KkaPengaduan::where('visit_id', $simpulan->visit_id)->where('exception', 'Ya')->count(),
            'fisik' => KkaFisik::where('visit_id', $simpulan->visit_id)->where('exception', 'Ya')->count(),
            'observasi' => KkaObservasi::where('visit_id', $simpulan->visit_id)->where('exception', 'Ya')->count(),
        ];

        return view('risk_trigger.simpulan_tl.edit', [
            'simpulan' => $simpulan,
            'kkaInduk' => $kkaInduk,
            'klarifikasiList' => $klarifikasiList,
            'exceptionSummary' => $exceptionSummary,
            'isRa' => $isRa,
            'isLockedForRa' => $isLockedForRa,
        ]);
    }

    /**
     * Simpan Perubahan Simpulan & Tindak Lanjut
     */
    public function simpulanTlUpdate(Request $request, $id)
    {
        $simpulan = SimpulanTl::findOrFail($id);
        $this->checkSimpulanTlBranchAccess($simpulan);

        $user = Auth::user();
        if ($this->isRa($user) && $simpulan->status_review === 'FINAL' && $simpulan->status_closure === 'CLOSED/DITERUSKAN') {
            return redirect()->back()->with('error', 'Akses Ditolak: Simpulan TL sudah FINAL & CLOSED, data tidak dapat diedit.');
        }

        $data = $request->all();

        // RA tidak berhak mengubah status review (Kolom V)
        if ($this->isRa($user)) {
            unset($data['status_review']);
        }

        try {
            $this->service->updateSimpulanTl($simpulan, $data, $user);
            return redirect()->route('risk-trigger.simpulan-tl.edit', $simpulan->id)
                ->with('success', 'Simpulan, Tindak Lanjut & Closure berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui Simpulan TL: ' . $e->getMessage());
        }
    }

    /**
     * Tambah Baris Klarifikasi & Bukti Baru
     */
    public function storeKlarifikasi(Request $request, $id)
    {
        $simpulan = SimpulanTl::findOrFail($id);
        $this->checkSimpulanTlBranchAccess($simpulan);

        $user = Auth::user();
        if ($this->isRa($user) && $simpulan->status_review === 'FINAL' && $simpulan->status_closure === 'CLOSED/DITERUSKAN') {
            return redirect()->back()->with('error', 'Akses Ditolak: Simpulan TL sudah FINAL & CLOSED, penambahan klarifikasi dikunci.');
        }

        $request->validate([
            'isu_exception' => 'required|string',
            'status_klarifikasi' => 'required|in:Open,Dalam Proses,Closed',
            'kecukupan_bukti' => 'nullable|in:Memadai,Kurang Memadai,Tidak Ada Bukti',
            'lampiran_file' => 'nullable|file|max:10240',
        ]);

        $data = $request->except(['_token', 'lampiran_file']);
        $data['visit_id'] = $simpulan->visit_id;

        // RA tidak bisa mengisi catatan_review (Kolom P)
        if ($this->isRa($user)) {
            unset($data['catatan_review']);
        }

        try {
            $this->service->storeKlarifikasi($data, $user, $request->file('lampiran_file'));
            return redirect()->route('risk-trigger.simpulan-tl.edit', $simpulan->id)
                ->with('success', 'Baris Klarifikasi & Bukti berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan klarifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Update Baris Klarifikasi & Bukti
     */
    public function updateKlarifikasi(Request $request, $id)
    {
        $row = KlarifikasiBukti::findOrFail($id);
        $this->checkKlarifikasiBranchAccess($row);

        $simpulan = SimpulanTl::where('visit_id', $row->visit_id)->firstOrFail();

        $user = Auth::user();
        if ($this->isRa($user) && $simpulan->status_review === 'FINAL' && $simpulan->status_closure === 'CLOSED/DITERUSKAN') {
            return redirect()->back()->with('error', 'Akses Ditolak: Simpulan TL sudah FINAL & CLOSED.');
        }

        $data = $request->except(['_token', '_method', 'lampiran_file']);

        // RA tidak bisa mengisi catatan_review (Kolom P)
        if ($this->isRa($user)) {
            unset($data['catatan_review']);
        }

        try {
            $this->service->updateKlarifikasi($row, $data, $user, $request->file('lampiran_file'));
            return redirect()->route('risk-trigger.simpulan-tl.edit', $simpulan->id)
                ->with('success', 'Baris Klarifikasi & Bukti berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui klarifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Baris Klarifikasi & Bukti
     */
    public function destroyKlarifikasi(Request $request, $id)
    {
        $row = KlarifikasiBukti::findOrFail($id);
        $this->checkKlarifikasiBranchAccess($row);

        $simpulan = SimpulanTl::where('visit_id', $row->visit_id)->firstOrFail();

        $user = Auth::user();
        if ($this->isRa($user) && $simpulan->status_review === 'FINAL' && $simpulan->status_closure === 'CLOSED/DITERUSKAN') {
            return redirect()->back()->with('error', 'Akses Ditolak: Simpulan TL sudah FINAL & CLOSED.');
        }

        try {
            $this->service->deleteKlarifikasi($row, $user);
            return redirect()->route('risk-trigger.simpulan-tl.edit', $simpulan->id)
                ->with('success', 'Baris Klarifikasi & Bukti berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus klarifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Tarik Cepat Seluruh Exception dari 8 KKA Area ke Register Klarifikasi
     */
    public function pullExceptions(Request $request, $id)
    {
        $simpulan = SimpulanTl::findOrFail($id);
        $this->checkSimpulanTlBranchAccess($simpulan);

        $user = Auth::user();
        if ($this->isRa($user) && $simpulan->status_review === 'FINAL' && $simpulan->status_closure === 'CLOSED/DITERUSKAN') {
            return redirect()->back()->with('error', 'Akses Ditolak: Simpulan TL sudah FINAL & CLOSED.');
        }

        try {
            $pulled = $this->service->pullExceptionsToKlarifikasi($simpulan->visit_id, $user);
            return redirect()->route('risk-trigger.simpulan-tl.edit', $simpulan->id)
                ->with('success', "Berhasil menarik {$pulled} isu exception baru dari 8 KKA Area ke Register Klarifikasi.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menarik exception: ' . $e->getMessage());
        }
    }
}


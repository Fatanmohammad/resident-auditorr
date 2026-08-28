namespace App\Http\Controllers;

use App\Models\StagingOffsite;
use App\Models\WpOffsite;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminOffsiteController extends Controller
{
    // Properti $kkaModels dan $kkaLabels diasumsikan sudah ada di class

    public function unitDetail(WpOffsite $wp)
    {
        // Ambil data staging sekali saja
        $stagingData = $wp->stagingOffsite()->get();

        // Grouping berdasarkan tanggal
        $rows = $stagingData->groupBy(function ($item) {
            return Carbon::parse($item->tanggal_data)->format('Y-m-d');
        });

        // Agregasi statistik langsung dari Collection yang sudah di-load (tanpa query DB ulang)
        $ringkasan = [
            'populasi'   => $stagingData->count(),
            'sampel_low' => $stagingData->where('sampel_low', 1)->count(),
            'kka_final'  => $stagingData->where('masuk_kka_final', 1)->count(),
            'exception'  => $stagingData->where('exception_awal', 1)->count(),
        ];

        return view('admin-offsite.unit-detail', compact('wp', 'rows', 'ringkasan'));
    }

    public function kkaIndex(WpOffsite $wp, string $area)
    {
        if (!isset($this->kkaModels[$area]) || !isset($this->kkaLabels[$area])) {
            abort(404);
        }

        $modelClass = $this->kkaModels[$area];
        $areaLabel  = $this->kkaLabels[$area];

        $rows = $modelClass::where('wp_offsite_id', $wp->id)->get();

        return view('admin-offsite.kka-index', compact('wp', 'area', 'areaLabel', 'rows'));
    }

    public function kkaShow(WpOffsite $wp, string $area, $kkaId)
    {
        if (!isset($this->kkaModels[$area])) {
            abort(404);
        }

        $modelClass = $this->kkaModels[$area];

        // Cari data Staging terlebih dahulu sebagai Single Source of Truth
        $staging = StagingOffsite::where('wp_offsite_id', $wp->id)
            ->where('id', $kkaId)
            ->firstOrFail();

        // Sinkronisasi otomatis (Auto-Create/Fetch) ke tabel KKA Area spesifik
        $kkaData = $modelClass::firstOrCreate(
            [
                'wp_offsite_id' => $wp->id,
                'staging_id'    => $staging->id, // Menggunakan FK staging_id secara konsisten
            ],
            [
                'user_maker'       => $staging->user_maker ?? auth()->id(),
                'risk_awal'        => $staging->risk_awal,
                'status_review'    => $staging->status_review ?? 'Draft',
                'deskripsi_narasi' => $staging->deskripsi_narasi ?? null,
                'catatan_reviewer' => $staging->catatan_reviewer ?? null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]
        );

        return view('admin-offsite.kka-show', compact('wp', 'area', 'kkaData', 'staging'));
    }

    public function kkaUpdate(Request $request, WpOffsite $wp, string $area, $kkaId)
    {
        if (!isset($this->kkaModels[$area])) {
            abort(404);
        }

        $validated = $request->validate([
            'catatan_reviewer' => 'nullable|string|max:2000',
        ]);

        $modelClass = $this->kkaModels[$area];
        $userId     = auth()->id();
        $now        = now();

        // Gunakan Transaction untuk memastikan kedua tabel ter-update bersamaan
        DB::transaction(function () use ($wp, $kkaId, $modelClass, $validated, $userId, $now) {
            
            // 1. Update tabel StagingOffsite
            StagingOffsite::where('wp_offsite_id', $wp->id)
                ->where('id', $kkaId)
                ->update([
                    'catatan_reviewer' => $validated['catatan_reviewer'] ?? null,
                    'user_reviewer'    => $userId,
                    'updated_at'       => $now,
                ]);

            // 2. Update tabel KKA Area (Presisi via staging_id atau primary key)
            $modelClass::where('wp_offsite_id', $wp->id)
                ->where(function ($query) use ($kkaId) {
                    $query->where('staging_id', $kkaId)
                          ->orWhere('id', $kkaId);
                })
                ->update([
                    'catatan_reviewer' => $validated['catatan_reviewer'] ?? null,
                    'user_reviewer'    => $userId,
                    'updated_at'       => $now,
                ]);
        });

        return redirect()
            ->back()
            ->with('updated_success', 'Catatan reviewer berhasil disimpan.');
    }
}
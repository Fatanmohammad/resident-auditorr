<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyRegister\DailyTarget;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DailyTargetAutoLock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily-target:auto-lock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically locks daily targets (KKA Harian) if grace period (H+2) or extended due date is passed without completion.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now()->startOfDay();
        
        $this->info("Running Daily Target Auto-Lock at " . $now->toDateString());
        
        // Find all active targets that are NOT Final and NOT Locked
        $activeTargets = DailyTarget::whereNotIn('status', ['Final', 'LOCKED_UNFINISHED'])
            ->get();
            
        $lockedCount = 0;
            
        foreach ($activeTargets as $target) {
            $shouldLock = false;
            
            if ($target->extended_due_date) {
                // If it has been extended, check if we passed the extended date
                if ($now->gt($target->extended_due_date)) {
                    $shouldLock = true;
                    $this->info("Target #{$target->id} passed extended_due_date ({$target->extended_due_date->toDateString()})");
                }
            } else {
                // Check Grace Period H+2
                $gracePeriodEnd = $target->tanggal_tugas->copy()->addDays(2);
                
                if ($now->gt($gracePeriodEnd)) {
                    // Passed H+2. Check if there's a pending approval letter.
                    // If it is 'PENDING_APPROVAL_PUSAT', it means they applied, so we don't lock it yet until Pusat rejects/approves.
                    // But if it's 'Pending' or 'In Progress' and no letter was uploaded, LOCK IT.
                    if ($target->status !== 'PENDING_APPROVAL_PUSAT') {
                        $shouldLock = true;
                        $this->info("Target #{$target->id} passed Grace Period H+2 ({$gracePeriodEnd->toDateString()}) without approval request");
                    }
                }
            }
            
            if ($shouldLock) {
                $target->update(['status' => 'LOCKED_UNFINISHED']);
                $lockedCount++;
                Log::info("DailyTargetAutoLock: Locked Target #{$target->id} for unit {$target->kode_unit}");
            }
        }
        
        $this->info("Process completed. Locked {$lockedCount} targets.");
    }
}

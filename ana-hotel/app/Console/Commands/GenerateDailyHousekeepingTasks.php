<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Room;
use App\Models\HousekeepingTask;

class GenerateDailyHousekeepingTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'housekeeping:generate-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily housekeeping cleaning tasks for occupied rooms assigned to each housekeeper';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->toDateString();
        $countCreated = 0;

        $housekeepers = User::where('role', 'housekeeping')
            ->with(['assignedRooms' => function($q) {
                $q->with('roomType');
            }])
            ->orderBy('name')
            ->get();

        foreach ($housekeepers as $hk) {
            foreach ($hk->assignedRooms as $room) {
                $occupied = $room->status === 'occupied' || $room->getCurrentBooking();
                if (!$occupied) {
                    continue;
                }

                $existing = HousekeepingTask::where('room_id', $room->id)
                    ->where('assigned_to', $hk->id)
                    ->whereDate('due_date', $today)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->first();

                if (!$existing) {
                    HousekeepingTask::create([
                        'room_id' => $room->id,
                        'assigned_to' => $hk->id,
                        'assigned_by' => $hk->id, // system
                        'task_type' => 'cleaning',
                        'status' => 'pending',
                        'priority' => 'high',
                        'description' => 'Daily cleaning for occupied room',
                        'due_date' => now(),
                    ]);
                    $countCreated++;
                }
            }
        }

        $this->info("Daily housekeeping task generation complete. Created: {$countCreated}");
        return self::SUCCESS;
    }
}

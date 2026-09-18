<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\JobWorkEvent;
use App\Models\ServiceJob;
use App\Models\ServiceJobStatusEvent;
use App\Models\Tenant;
use App\Models\TimeEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JobWorkController extends Controller
{
    public function start(Request $request, ServiceJob $job): RedirectResponse
    {
        [$tenant, $events] = $this->authorizeJobWork($request, $job);
        abort_if($events->contains('event_type', JobWorkEvent::TYPE_START), 422, 'This job has already been started.');

        $this->recordEvent($tenant, $job, $request, JobWorkEvent::TYPE_START);
        $oldStatus = $job->status;
        $job->update(['status' => ServiceJob::STATUS_IN_PROGRESS]);
        $this->recordStatusChange($tenant, $job, $oldStatus, ServiceJob::STATUS_IN_PROGRESS, $request->user()->id, 'Job timer started.');

        return back()->with('status', 'Job started.');
    }

    public function startBreak(Request $request, ServiceJob $job): RedirectResponse
    {
        [$tenant, $events] = $this->authorizeJobWork($request, $job);
        abort_unless($this->currentState($events) === 'working', 422, 'The job must be active before starting a break.');

        $this->recordEvent($tenant, $job, $request, JobWorkEvent::TYPE_BREAK_START);

        return back()->with('status', 'Break started.');
    }

    public function endBreak(Request $request, ServiceJob $job): RedirectResponse
    {
        [$tenant, $events] = $this->authorizeJobWork($request, $job);
        abort_unless($this->currentState($events) === 'on_break', 422, 'No active break found for this job.');

        $this->recordEvent($tenant, $job, $request, JobWorkEvent::TYPE_BREAK_END);

        return back()->with('status', 'Break ended.');
    }

    public function end(Request $request, ServiceJob $job): RedirectResponse
    {
        [$tenant, $events] = $this->authorizeJobWork($request, $job);
        abort_unless(in_array($this->currentState($events), ['working', 'on_break'], true), 422, 'The job must be started before it can be ended.');

        DB::transaction(function () use ($tenant, $job, $request): void {
            $this->recordEvent($tenant, $job, $request, JobWorkEvent::TYPE_END);

            $events = $job->workEvents()->oldest('occurred_at')->get();
            $summary = $this->summary($events);

            TimeEntry::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'service_job_id' => $job->id,
                    'user_id' => $request->user()->id,
                    'notes' => 'Auto-calculated from job timer.',
                ],
                [
                    'started_at' => $summary['started_at'],
                    'ended_at' => $summary['ended_at'],
                    'minutes' => $summary['working_minutes'],
                ]
            );

            $oldStatus = $job->status;
            $job->update(['status' => ServiceJob::STATUS_COMPLETED]);
            $this->recordStatusChange($tenant, $job, $oldStatus, ServiceJob::STATUS_COMPLETED, $request->user()->id, 'Job timer ended.');
        });

        return back()->with('status', 'Job ended and time calculated.');
    }

    private function authorizeJobWork(Request $request, ServiceJob $job): array
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');
        abort_unless((int) $job->tenant_id === (int) $tenant->id, 404);
        abort_unless($request->user()->hasPermission('jobs.work', $tenant) || $request->user()->hasPermission('jobs.manage', $tenant), 403);

        $job->loadMissing('team.users');
        $isAssigned = (int) $job->assigned_user_id === (int) $request->user()->id
            || $job->team?->users->contains('id', $request->user()->id)
            || $request->user()->hasPermission('jobs.manage', $tenant);

        abort_unless($isAssigned, 404);

        return [$tenant, $job->workEvents()->oldest('occurred_at')->get()];
    }

    private function recordEvent(Tenant $tenant, ServiceJob $job, Request $request, string $type): JobWorkEvent
    {
        return JobWorkEvent::create([
            'tenant_id' => $tenant->id,
            'service_job_id' => $job->id,
            'user_id' => $request->user()->id,
            'event_type' => $type,
            'occurred_at' => now(),
            'notes' => $request->input('notes'),
        ]);
    }

    private function recordStatusChange(Tenant $tenant, ServiceJob $job, ?string $oldStatus, string $newStatus, ?int $userId, ?string $notes = null): void
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        ServiceJobStatusEvent::create([
            'tenant_id' => $tenant->id,
            'service_job_id' => $job->id,
            'user_id' => $userId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_at' => now(),
            'notes' => $notes,
        ]);
    }

    private function currentState(Collection $events): string
    {
        $last = $events->last();

        return match ($last?->event_type) {
            JobWorkEvent::TYPE_START, JobWorkEvent::TYPE_BREAK_END => 'working',
            JobWorkEvent::TYPE_BREAK_START => 'on_break',
            JobWorkEvent::TYPE_END => 'ended',
            default => 'not_started',
        };
    }

    private function summary(Collection $events): array
    {
        $startedAt = $events->firstWhere('event_type', JobWorkEvent::TYPE_START)?->occurred_at;
        $endedAt = $events->reverse()->firstWhere('event_type', JobWorkEvent::TYPE_END)?->occurred_at ?? now();
        $breakMinutes = 0;
        $openBreak = null;

        foreach ($events as $event) {
            if ($event->event_type === JobWorkEvent::TYPE_BREAK_START) {
                $openBreak = $event->occurred_at;
            }

            if ($event->event_type === JobWorkEvent::TYPE_BREAK_END && $openBreak) {
                $breakMinutes += $openBreak->diffInMinutes($event->occurred_at);
                $openBreak = null;
            }
        }

        if ($openBreak) {
            $breakMinutes += $openBreak->diffInMinutes($endedAt);
        }

        $totalMinutes = $startedAt ? $startedAt->diffInMinutes($endedAt) : 0;

        return [
            'started_at' => $startedAt ?? now(),
            'ended_at' => $endedAt,
            'total_minutes' => $totalMinutes,
            'break_minutes' => $breakMinutes,
            'working_minutes' => max(0, $totalMinutes - $breakMinutes),
        ];
    }
}

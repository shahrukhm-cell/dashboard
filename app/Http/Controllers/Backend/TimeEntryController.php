<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ServiceJob;
use App\Models\Tenant;
use App\Models\TimeEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TimeEntryController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $this->currentTenant($request, 'jobs.view');

        $entries = TimeEntry::query()
            ->where('tenant_id', $tenant->id)
            ->with(['job.customer', 'user'])
            ->latest('started_at')
            ->paginate(15);

        return view('backend.time-entries.index', [
            'tenant' => $tenant,
            'entries' => $entries,
            'jobs' => ServiceJob::where('tenant_id', $tenant->id)->with('customer')->latest()->get(),
            'users' => $tenant->users()->orderBy('name')->get(),
            'totalMinutes' => TimeEntry::where('tenant_id', $tenant->id)->sum('minutes'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'jobs.manage');

        $data = $request->validate([
            'service_job_id' => ['required', Rule::exists('service_jobs', 'id')->where('tenant_id', $tenant->id)],
            'user_id' => ['required', Rule::exists('tenant_user', 'user_id')->where('tenant_id', $tenant->id)],
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'hours' => ['required', 'numeric', 'min:0.01', 'max:999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        TimeEntry::create([
            'tenant_id' => $tenant->id,
            'service_job_id' => $data['service_job_id'],
            'user_id' => $data['user_id'],
            'started_at' => $data['started_at'],
            'ended_at' => $data['ended_at'] ?? null,
            'minutes' => (int) round(((float) $data['hours']) * 60),
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('status', 'Time entry logged.');
    }

    private function currentTenant(Request $request, string $permission): Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');
        abort_unless($request->user()->hasPermission($permission, $tenant), 403);

        return $tenant;
    }
}
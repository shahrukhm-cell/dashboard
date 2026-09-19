<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ServiceJob;
use App\Models\Team;
use App\Models\TeamLeave;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $this->currentTenant($request, 'jobs.assign');
        $search = trim((string) $request->query('search'));

        $teams = Team::where('tenant_id', $tenant->id)
            ->with('users')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('users', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->get();

        return view('backend.teams.index', [
            'tenant' => $tenant,
            'teams' => $teams,
            'users' => $tenant->users()->where('users.id', '!=', 1)->where('users.is_super_admin', false)->with(['roles' => fn ($query) => $query->wherePivot('tenant_id', $tenant->id)])->orderBy('name')->get(),
            'search' => $search,
        ]);
    }

    public function show(Request $request, Team $team): View
    {
        $tenant = $this->currentTenant($request, 'jobs.assign');
        $this->ensureTenantTeam($tenant, $team);

        $team->load([
            'users.roles' => fn ($query) => $query->wherePivot('tenant_id', $tenant->id),
            'users.assignedJobs' => fn ($query) => $query->where('tenant_id', $tenant->id)->latest()->limit(5),
            'jobs.customer',
        ]);

        return view('backend.teams.show', [
            'tenant' => $tenant,
            'team' => $team,
            'jobs' => $team->jobs()->with('customer')->latest()->limit(10)->get(),
        ]);
    }

    public function member(Request $request, Team $team, User $user): View
    {
        $tenant = $this->currentTenant($request, 'jobs.assign');
        $this->ensureTenantTeam($tenant, $team);
        abort_unless($team->users()->whereKey($user->id)->exists(), 404);

        $assignedJobs = ServiceJob::where('tenant_id', $tenant->id)
            ->where(function ($query) use ($team, $user): void {
                $query->where('assigned_user_id', $user->id)
                    ->orWhere('team_id', $team->id);
            })
            ->with('customer')
            ->latest()
            ->get();

        $timeEntries = $user->timeEntries()
            ->where('tenant_id', $tenant->id)
            ->with('job')
            ->latest('started_at')
            ->limit(12)
            ->get();

        $leaves = $user->leaves()
            ->where('tenant_id', $tenant->id)
            ->latest('starts_at')
            ->limit(8)
            ->get();

        return view('backend.teams.member', [
            'tenant' => $tenant,
            'team' => $team,
            'member' => $user->load(['roles' => fn ($query) => $query->wherePivot('tenant_id', $tenant->id)]),
            'assignedJobs' => $assignedJobs,
            'timeEntries' => $timeEntries,
            'leaves' => $leaves,
            'leaveStatuses' => TeamLeave::statuses(),
            'completedJobs' => $assignedJobs->where('status', ServiceJob::STATUS_COMPLETED)->count(),
            'totalMinutes' => $timeEntries->sum('minutes'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'jobs.assign');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('teams')->where('tenant_id', $tenant->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'users' => ['array'],
            'users.*' => [Rule::exists('tenant_user', 'user_id')->where('tenant_id', $tenant->id)],
            'team_lead_user_id' => ['nullable', Rule::exists('tenant_user', 'user_id')->where('tenant_id', $tenant->id)],
        ]);

        $team = Team::create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->syncUsers($team, $data['users'] ?? [], $data['team_lead_user_id'] ?? null);

        return back()->with('status', 'Team created.');
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'jobs.assign');
        $this->ensureTenantTeam($tenant, $team);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('teams')->where('tenant_id', $tenant->id)->ignore($team->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'users' => ['array'],
            'users.*' => [Rule::exists('tenant_user', 'user_id')->where('tenant_id', $tenant->id)],
            'team_lead_user_id' => ['nullable', Rule::exists('tenant_user', 'user_id')->where('tenant_id', $tenant->id)],
        ]);

        $team->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncUsers($team, $data['users'] ?? [], $data['team_lead_user_id'] ?? null);

        return back()->with('status', 'Team updated.');
    }

    public function storeAttendance(Request $request, Team $team, User $user): RedirectResponse
    {
        $tenant = $this->currentTenant($request, 'jobs.assign');
        $this->ensureTenantTeam($tenant, $team);
        abort_unless($team->users()->whereKey($user->id)->exists(), 404);

        $data = $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::in(TeamLeave::statuses())],
            'reason' => ['nullable', 'string', 'max:180'],
        ]);

        TeamLeave::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'status' => $data['status'],
            'reason' => $data['reason'] ?? null,
        ]);

        return back()->with('status', 'Attendance record saved.');
    }

    private function currentTenant(Request $request, string $permission): Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');
        abort_unless($request->user()->hasPermission($permission, $tenant), 403);

        return $tenant;
    }

    private function ensureTenantTeam(Tenant $tenant, Team $team): void
    {
        abort_unless((int) $team->tenant_id === (int) $tenant->id, 404);
    }

    private function syncUsers(Team $team, array $userIds, ?int $leadUserId = null): void
    {
        $userIds = collect($userIds)
            ->filter()
            ->map(fn ($userId) => (int) $userId);

        if ($leadUserId) {
            $userIds->push((int) $leadUserId);
        }

        $sync = $userIds
            ->unique()
            ->mapWithKeys(fn ($userId) => [$userId => ['role' => $leadUserId && (int) $userId === (int) $leadUserId ? 'lead' : 'member']])
            ->all();

        $team->users()->sync($sync);
    }
}



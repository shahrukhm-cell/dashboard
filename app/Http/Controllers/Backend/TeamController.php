<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $this->currentTenant($request, 'jobs.assign');

        return view('backend.teams.index', [
            'tenant' => $tenant,
            'teams' => Team::where('tenant_id', $tenant->id)->with('users')->orderBy('name')->get(),
            'users' => $tenant->users()->with(['roles' => fn ($query) => $query->wherePivot('tenant_id', $tenant->id)])->orderBy('name')->get(),
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



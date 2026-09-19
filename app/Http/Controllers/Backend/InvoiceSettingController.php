<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceSettingController extends Controller
{
    public function edit(Request $request): View
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');
        abort_unless($request->user()->hasPermission('tenant.settings.update', $tenant), 403);

        return view('backend.invoice-settings.edit', [
            'tenant' => $tenant,
            'settings' => $tenant->invoiceSettings(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');
        abort_unless($request->user()->hasPermission('tenant.settings.update', $tenant), 403);

        $data = $request->validate([
            'accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'heading' => ['nullable', 'string', 'max:120'],
            'footer' => ['nullable', 'string', 'max:500'],
            'terms' => ['nullable', 'string', 'max:1000'],
            'show_payments' => ['nullable', 'boolean'],
        ]);

        $settings = $tenant->settings ?? [];
        $settings['invoice'] = [
            'accent_color' => $data['accent_color'] ?? '#111827',
            'heading' => $data['heading'] ?? 'Customer invoice',
            'footer' => $data['footer'] ?? 'Thank you.',
            'terms' => $data['terms'] ?? null,
            'show_payments' => $request->boolean('show_payments'),
        ];

        $tenant->update(['settings' => $settings]);

        return back()->with('status', 'Invoice design settings saved.');
    }
}

<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ServiceJob;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class JobInvoiceController extends Controller
{
    public function __invoke(Request $request, ServiceJob $job): Response
    {
        $tenant = $this->authorizedTenantJob($request, $job);
        abort_unless($job->status === ServiceJob::STATUS_COMPLETED, 422, 'Invoice can be downloaded after job completion.');

        return $this->downloadDocument(
            $tenant,
            $job,
            (string) $tenant->invoiceSetting('heading', 'Customer invoice'),
            true,
            'invoice'
        );
    }

    public function quote(Request $request, ServiceJob $job): Response
    {
        $tenant = $this->authorizedTenantJob($request, $job);

        return $this->downloadDocument($tenant, $job, 'Job quote', false, 'quote');
    }

    private function authorizedTenantJob(Request $request, ServiceJob $job): Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        abort_unless($tenant, 403, 'Select a workspace first.');
        abort_unless((int) $job->tenant_id === (int) $tenant->id, 404);
        abort_unless($request->user()->hasPermission('jobs.view', $tenant), 403);

        if ($request->user()->isFieldStaff($tenant)) {
            $job->loadMissing('team.users');
            abort_unless(
                (int) $job->assigned_user_id === (int) $request->user()->id
                    || $job->team?->users->contains('id', $request->user()->id),
                404
            );
        }

        return $tenant;
    }

    private function downloadDocument(Tenant $tenant, ServiceJob $job, string $title, bool $includePayments, string $type): Response
    {
        $job->load(['customer', 'items.service', 'customerPayments']);

        $html = view('backend.jobs.invoice', [
            'tenant' => $tenant,
            'job' => $job,
            'title' => $title,
            'type' => $type,
            'includePayments' => $includePayments,
            'paid' => (float) $job->customerPayments->where('status', 'paid')->sum('amount'),
            'showPayments' => (bool) $tenant->invoiceSetting('show_payments', true),
            'accent' => (string) $tenant->invoiceSetting('accent_color', $tenant->themeColor()),
            'terms' => $tenant->invoiceSetting('terms'),
            'footer' => (string) $tenant->invoiceSetting('footer', 'Thank you.'),
            'logoPath' => $this->logoPath($tenant),
        ])->render();

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', [public_path(), storage_path('app/public')]);

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html);
        $pdf->setPaper('a4', 'portrait');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->filename($type, $job).'"',
        ]);
    }

    private function filename(string $type, ServiceJob $job): string
    {
        return Str::slug($type.'-'.$job->job_number).'.pdf';
    }

    private function logoPath(Tenant $tenant): ?string
    {
        if (! $tenant->logoPath()) {
            return null;
        }

        $path = public_path('storage/'.$tenant->logoPath());

        return is_file($path) ? $path : null;
    }
}

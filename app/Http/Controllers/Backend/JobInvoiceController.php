<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ServiceJob;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JobInvoiceController extends Controller
{
    public function __invoke(Request $request, ServiceJob $job): Response
    {
        $tenant = $this->authorizedTenantJob($request, $job);
        abort_unless($job->status === ServiceJob::STATUS_COMPLETED, 422, 'Invoice can be downloaded after job completion.');

        $job->load(['customer', 'items.service', 'customerPayments']);

        return $this->pdfResponse(
            $this->makePdf($tenant, $job, (string) $tenant->invoiceSetting('heading', 'Customer invoice'), true),
            'invoice-'.$job->job_number.'.pdf'
        );
    }

    public function quote(Request $request, ServiceJob $job): Response
    {
        $tenant = $this->authorizedTenantJob($request, $job);
        $job->load(['customer', 'items.service', 'customerPayments']);

        return $this->pdfResponse(
            $this->makePdf($tenant, $job, 'Job quote', false),
            'quote-'.$job->job_number.'.pdf'
        );
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

    private function pdfResponse(string $pdf, string $filename): Response
    {
        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function makePdf(Tenant $tenant, ServiceJob $job, string $title, bool $includePayments): string
    {
        $paid = (float) $job->customerPayments->where('status', 'paid')->sum('amount');
        $balance = max(0, (float) $job->total - $paid);
        $lines = [
            $tenant->name,
            $title,
            'Job: '.$job->job_number,
            'Quote status: '.str_replace('_', ' ', (string) ($job->quote_status ?? ServiceJob::QUOTE_DRAFT)),
            'Date: '.now()->format('M j, Y'),
            'Customer: '.$job->customer->name,
            'Phone: '.($job->customer->phone ?: 'Not added'),
            'Address: '.($job->service_address ?: $job->customer->addressSummary() ?: 'Not added'),
            '',
            'Services',
        ];

        foreach ($job->items as $item) {
            $lines[] = $item->name.' - '.$item->quantity.' '.$item->unit_type.' x $'.number_format((float) $item->unit_price, 2).' = $'.number_format((float) $item->line_total, 2);
        }

        $lines = array_merge($lines, [
            '',
            'Subtotal: $'.number_format((float) $job->subtotal, 2),
            'Discount: $'.number_format((float) $job->discount, 2),
            'Total: $'.number_format((float) $job->total, 2),
        ]);

        if ($includePayments && $tenant->invoiceSetting('show_payments', true)) {
            $lines[] = 'Paid: $'.number_format($paid, 2);
            $lines[] = 'Balance due: $'.number_format($balance, 2);
        }

        if ($terms = $tenant->invoiceSetting('terms')) {
            $lines[] = '';
            $lines[] = (string) $terms;
        }

        $lines[] = '';
        $lines[] = (string) $tenant->invoiceSetting('footer', 'Thank you.');

        return $this->renderSimplePdf($lines);
    }

    private function renderSimplePdf(array $lines): string
    {
        $content = "BT\n/F1 18 Tf\n50 780 Td\n";
        $first = true;

        foreach ($lines as $line) {
            $fontSize = $first ? 18 : 10;
            $leading = $first ? 26 : 15;
            $content .= '/F1 '.$fontSize." Tf\n";
            $content .= '('.$this->pdfText($line).') Tj' . "\n";
            $content .= '0 -'.$leading." Td\n";
            $first = false;
        }

        $content .= "ET\n";

        $objects = [];
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>";
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[] = "<< /Length ".strlen($content)." >>\nstream\n".$content."endstream";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $number = $index + 1;
            $pdf .= $number." 0 obj\n".$object."\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF";

        return $pdf;
    }

    private function pdfText(string $text): string
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);

        return preg_replace('/[^\x20-\x7E]/', '-', $text) ?? '';
    }
}

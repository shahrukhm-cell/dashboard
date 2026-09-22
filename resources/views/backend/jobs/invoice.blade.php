<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - {{ $job->job_number }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            background: #ffffff;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 13px;
            line-height: 1.45;
        }

        .sheet {
            width: 100%;
            /* padding: 22px; */
            background: #fff;
        }

        .top {
            width: 100%;
            padding-bottom: 20px;
            border-bottom: 4px solid {{ $accent }};
        }

        .brand {
            float: left;
            width: 58%;
        }

        .doc-title {
            float: right;
            width: 38%;
            text-align: right;
        }

        .clear {
            clear: both;
        }

        .logo,
        .logo-mark {
            width: 58px;
            height: 58px;
            border-radius: 12px;
            object-fit: contain;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .logo-mark {
            display: inline-block;
            color: #fff;
            background: {{ $accent }};
            font-size: 24px;
            font-weight: 800;
            text-align: center;
            line-height: 58px;
        }

        .brand-copy {
            display: inline-block;
            vertical-align: middle;
            margin-left: 12px;
            max-width: 360px;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
        }

        h1 {
            font-size: 27px;
            line-height: 1.1;
        }

        h2 {
            color: {{ $accent }};
            font-size: 26px;
            text-transform: uppercase;
        }

        .muted,
        .brand p {
            color: #6b7280;
        }

        .pill {
            display: inline-block;
            margin-top: 9px;
            padding: 6px 10px;
            color: {{ $accent }};
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .grid {
            width: 100%;
            margin-top: 24px;
        }

        .box {
            width: 44%;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #f8fafc;
            vertical-align: top;
        }

        .box-left {
            float: left;
        }

        .box-right {
            float: right;
        }

        .box h3 {
            margin-bottom: 9px;
            color: {{ $accent }};
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .meta-row {
            width: 100%;
            margin-bottom: 6px;
        }

        .meta-row span {
            color: #6b7280;
        }

        .meta-row strong {
            float: right;
            max-width: 65%;
            text-align: right;
        }

        table {
            width: 100%;
            margin-top: 28px;
            border-collapse: collapse;
        }

        th {
            padding: 11px 9px;
            color: #fff;
            background: {{ $accent }};
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }

        th:last-child,
        td:last-child {
            text-align: right;
        }

        td {
            padding: 12px 9px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        td small {
            display: block;
            color: #6b7280;
            margin-top: 2px;
        }

        .totals {
            width: 330px;
            margin-top: 22px;
            margin-left: auto;
        }

        .totals table {
            width: 100%;
            margin: 0;
        }

        .totals td {
            padding: 8px 0 8px 16px;
            border: 0;
        }

        .totals tr.total td {
            padding-top: 13px;
            border-top: 2px solid #e5e7eb;
            color: {{ $accent }};
            font-size: 19px;
            font-weight: 800;
        }

        .note {
            margin-top: 26px;
            padding: 15px;
            border-left: 4px solid {{ $accent }};
            background: #f8fafc;
            border-radius: 10px;
            white-space: pre-line;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
        }

        .footer-left {
            float: left;
            width: 60%;
        }

        .footer-right {
            float: right;
            width: 35%;
            text-align: right;
        }
    </style>
</head>

<body>
    <main class="sheet">
        <section class="top">
            <div class="brand">
                @if ($logoPath)
                    <img class="logo" src="{{ $logoPath }}" alt="{{ $tenant->brandName() }} logo">
                @else
                    <div class="logo-mark">{{ Str::of($tenant->brandName())->substr(0, 1)->upper() }}</div>
                @endif
                <div class="brand-copy">
                    <h1>{{ $tenant->brandName() }}</h1>
                    <p>{{ $tenant->name }}</p>
                </div>
            </div>
            <div class="doc-title">
                <h2>{{ $title }}</h2>
                <p class="muted">{{ now()->format('F j, Y') }}</p>
                <span class="pill">{{ $job->job_number }}</span>
            </div>
            <div class="clear"></div>
        </section>

        <section class="grid">
            <div class="box box-left">
                <h3>Bill to</h3>
                <strong>{{ $job->customer->name }}</strong>
                <p>{{ $job->customer->company ?: 'Individual customer' }}</p>
                <p>{{ $job->customer->email ?: 'No email added' }}</p>
                <p>{{ $job->customer->phone ?: 'No phone added' }}</p>
            </div>
            <div class="box box-right meta">
                <h3>Job details</h3>
                <div class="meta-row"><span>Status</span><strong>{{ Str::headline($job->status) }}</strong>
                    <div class="clear"></div>
                </div>
                <div class="meta-row">
                    <span>Quote</span><strong>{{ Str::headline($job->quote_status ?? 'draft') }}</strong>
                    <div class="clear"></div>
                </div>
                <div class="meta-row">
                    <span>Scheduled</span><strong>{{ $job->scheduled_at?->format('M j, Y') ?? 'Not scheduled' }}</strong>
                    <div class="clear"></div>
                </div>
                <div class="meta-row">
                    <span>Address</span><strong>{{ $job->service_address ?: $job->customer->addressSummary() ?: 'Not added' }}</strong>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="clear"></div>
        </section>

        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($job->items as $item)
                    <tr>
                        <td><strong>{{ $item->name }}</strong><small>{{ Str::headline($item->unit_type) }}</small>
                        </td>
                        <td>{{ number_format((float) $item->quantity, 0) }}</td>
                        <td>${{ number_format((float) $item->unit_price, 2) }}</td>
                        <td>${{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php($balance = max(0, (float) $job->total - (float) $paid))
        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td>${{ number_format((float) $job->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Discount</td>
                    <td>-${{ number_format((float) $job->discount, 2) }}</td>
                </tr>
                @if ($includePayments && $showPayments)
                    <tr>
                        <td>Paid</td>
                        <td>-${{ number_format((float) $paid, 2) }}</td>
                    </tr>
                    <tr class="total">
                        <td>Balance due</td>
                        <td>${{ number_format($balance, 2) }}</td>
                    </tr>
                @else
                    <tr class="total">
                        <td>Total</td>
                        <td>${{ number_format((float) $job->total, 2) }}</td>
                    </tr>
                @endif
            </table>
        </div>

        @if ($terms)
            <section class="note"><strong>Terms</strong><br>{{ $terms }}</section>
        @endif

        <footer class="footer">
            <div class="footer-left">{{ $footer }}</div>
            <div class="footer-right">{{ $tenant->brandName() }} / {{ $job->job_number }}</div>
            <div class="clear"></div>
        </footer>
    </main>
</body>

</html>

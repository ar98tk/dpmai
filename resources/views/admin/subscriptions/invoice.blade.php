<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Invoice #{{ $subscription->id }}</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            color: #1f2937;
            background: #f8fafc;
            padding: 26px;
        }

        .invoice {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 22px 24px;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-wrap img {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }

        .title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }

        .meta {
            text-align: right;
            font-size: 13px;
            color: #4b5563;
            line-height: 1.55;
        }

        .content {
            padding: 20px 24px 26px;
        }

        .section {
            margin-bottom: 18px;
        }

        .section-title {
            margin: 0 0 8px;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            font-weight: 700;
            color: #111827;
        }

        .footer {
            padding: 14px 24px 18px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="invoice">
    <div class="header">
        <div class="logo-wrap">
            @if ($logoDataUri)
                <img src="{{ $logoDataUri }}" alt="DPM Logo">
            @endif
            <div>
                <h1 class="title">Subscription Invoice</h1>
            </div>
        </div>
        <div class="meta">
            <div><strong>Invoice #</strong> SUB-{{ $subscription->id }}</div>
            <div><strong>Generated At:</strong> {{ optional($generatedAt)->format('Y-m-d H:i') }}</div>
        </div>
    </div>

    <div class="content">
        <div class="section">
            <h2 class="section-title">Business Information</h2>
            <table>
                <tr>
                    <th style="width: 30%;">Business Name</th>
                    <td>{{ $business?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $business?->email ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ $business?->phone ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ ucfirst((string) ($business?->status ?? '-')) }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">Subscription Details</h2>
            <table>
                <tr>
                    <th style="width: 30%;">Plan</th>
                    <td>{{ $plan?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Price</th>
                    <td>${{ number_format((float) ($plan?->price ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <th>Subscription Status</th>
                    <td>{{ ucfirst((string) $subscription->status) }}</td>
                </tr>
                <tr>
                    <th>Start Date</th>
                    <td>{{ optional($subscription->start_date)->format('Y-m-d H:i') ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Expiry Date</th>
                    <td>{{ optional($subscription->end_date)->format('Y-m-d H:i') ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Max Instances</th>
                    <td>{{ (int) ($plan?->max_instances ?? 0) }}</td>
                </tr>
                <tr>
                    <th>Daily Token Limit</th>
                    <td>{{ number_format((int) ($plan?->daily_token_limit ?? 0)) }}</td>
                </tr>
                <tr>
                    <th>Monthly Token Limit</th>
                    <td>{{ number_format((int) ($plan?->monthly_token_limit ?? 0)) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        This invoice is generated automatically from the subscriptions system.
    </div>
</div>
</body>
</html>

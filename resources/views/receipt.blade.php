{{--
    Donation receipt.

    Deliberately a standalone document rather than a site page: it is reached
    through an expiring signed URL, carries donor detail, and is printed or saved
    to PDF by the browser. It is served with no-store and noindex headers by the
    controller, and it loads no analytics and no external assets.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Donation receipt {{ $donation->reference }}</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 2.5rem 1.5rem;
            background: #faf8f4;
            color: #1c1b18;
            font: 16px/1.6 ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
        }
        .sheet {
            max-width: 44rem;
            margin: 0 auto;
            padding: 2.5rem;
            background: #fff;
            border: 1px solid #e4e0d7;
            border-radius: 0.75rem;
        }
        h1 { margin: 0 0 0.25rem; font-size: 1.5rem; font-family: Georgia, 'Times New Roman', serif; }
        .muted { color: #57534a; }
        .eyebrow { font-size: 0.7rem; letter-spacing: 0.14em; text-transform: uppercase; color: #c0221b; font-weight: 600; }
        table { width: 100%; margin-top: 2rem; border-collapse: collapse; }
        th, td { padding: 0.7rem 0; text-align: left; vertical-align: top; border-bottom: 1px solid #e4e0d7; }
        th { width: 40%; font-weight: 600; }
        td { color: #57534a; }
        .total td, .total th { border-bottom: none; padding-top: 1.25rem; font-size: 1.25rem; color: #0f5539; font-weight: 700; }
        .reference { font-family: ui-monospace, 'Cascadia Code', Menlo, monospace; font-size: 0.8rem; word-break: break-all; }
        footer { margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #e4e0d7; font-size: 0.8rem; color: #57534a; }
        .actions { max-width: 44rem; margin: 1.5rem auto 0; }
        button {
            padding: 0.6rem 1.2rem; font: inherit; font-size: 0.875rem; font-weight: 600;
            color: #fff; background: #0f5539; border: 0; border-radius: 0.375rem; cursor: pointer;
        }
        @media print {
            body { padding: 0; background: #fff; }
            .sheet { border: 0; padding: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <main class="sheet">
        <p class="eyebrow">Donation receipt</p>
        <h1>{{ $settings['org.legal_name'] ?? 'Global Support Foundation' }}</h1>

        @php
            $address = collect([
                $settings['contact.address'] ?? null,
                $settings['contact.city'] ?? null,
                $settings['contact.state'] ?? null,
                $settings['contact.country'] ?? null,
            ])->filter()->implode(', ');
        @endphp

        @if ($address)
            <p class="muted" style="margin:0.25rem 0 0;font-size:0.875rem">{{ $address }}</p>
        @endif

        @if ($settings['contact.email'] ?? null)
            <p class="muted" style="margin:0.15rem 0 0;font-size:0.875rem">{{ $settings['contact.email'] }}</p>
        @endif

        <table>
            <tbody>
                <tr>
                    <th scope="row">Receipt reference</th>
                    <td class="reference">{{ $donation->reference }}</td>
                </tr>
                <tr>
                    <th scope="row">Received from</th>
                    <td>{{ $donation->name }}</td>
                </tr>
                <tr>
                    <th scope="row">Email</th>
                    <td>{{ $donation->email }}</td>
                </tr>
                <tr>
                    <th scope="row">Date received</th>
                    <td>{{ $donation->paid_at?->format('j F Y, H:i') }}</td>
                </tr>
                @if ($donation->campaign)
                    <tr>
                        <th scope="row">Designated for</th>
                        <td>{{ $donation->campaign->title }}</td>
                    </tr>
                @endif
                <tr class="total">
                    <th scope="row">Amount</th>
                    <td>{{ $donation->currency }} {{ $donation->amount }}</td>
                </tr>
            </tbody>
        </table>

        <footer>
            <p>
                Thank you for supporting grassroots enterprise. This receipt confirms a donation received
                and settled through our payment provider against the reference shown above.
            </p>
            @if (($settings['registration.number'] ?? '') !== '')
                <p>Registered with {{ $settings['registration.authority'] }} — {{ $settings['registration.number'] }}.</p>
            @endif
            <p>Issued {{ now()->format('j F Y') }}.</p>
        </footer>
    </main>

    <div class="actions">
        <button type="button" onclick="window.print()">Print or save as PDF</button>
    </div>
</body>
</html>

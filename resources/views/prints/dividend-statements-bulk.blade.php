<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dividend Statements FY {{ $distribution->financial_year }} — {{ $orgName }}</title>
    @include('partials.print.styles')
</head>
<body>
    @forelse($pages as $page)
        <div class="page">
            @include('partials.print.watermark', ['print' => $page['print'], 'inline' => true])
            @include('partials.print.header', ['print' => $page['print'], 'documentTitle' => 'Dividend & Interest Statement'])
            @include('prints.partials.dividend-body', $page)
            @include('partials.print.footer', ['print' => $page['print']])
        </div>
    @empty
        <div class="page">
            <p class="muted center" style="margin-top:120px;">No dividend records exist for this financial year.</p>
        </div>
    @endforelse
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Statements — {{ $orgName }}</title>
    @include('partials.print.styles')
</head>
<body>
    @forelse($pages as $page)
        <div class="page">
            @include('partials.print.watermark', ['print' => $page['print'], 'inline' => true])
            @include('partials.print.header', ['print' => $page['print'], 'documentTitle' => 'Member Account Statement'])
            @include('prints.partials.statement-body', $page)
            @include('partials.print.footer', ['print' => $page['print']])
        </div>
    @empty
        <div class="page">
            <p class="muted center" style="margin-top:120px;">No active members were found, so there are no statements to print.</p>
        </div>
    @endforelse
</body>
</html>

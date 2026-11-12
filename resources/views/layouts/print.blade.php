<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('document_title', 'Document') — {{ $orgName }}</title>
    @include('partials.print.styles')
</head>
<body>
    @include('partials.print.watermark', ['print' => $print])

    <div class="sheet">
        @include('partials.print.header', ['print' => $print])

        @yield('content')

        @include('partials.print.footer', ['print' => $print])
    </div>
</body>
</html>

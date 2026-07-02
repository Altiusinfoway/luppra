<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $previewData['invoice']['number'] ?? 'Invoice Template Preview' }}</title>
    @include('invoice_templates.render.partials.styles', [
        'previewTheme' => $previewTheme,
    ])
</head>
<body>
    @include('invoice_templates.render.partials.template', [
        'template' => $template,
        'sectionMap' => $sectionMap,
        'previewData' => $previewData,
        'previewTheme' => $previewTheme,
    ])
</body>
</html>

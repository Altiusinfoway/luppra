@include('invoice_templates.render.partials.styles', [
    'previewTheme' => $previewTheme,
])

@include('invoice_templates.render.partials.template', [
    'template' => $template,
    'sectionMap' => $sectionMap,
    'previewData' => $previewData,
    'previewTheme' => $previewTheme,
])

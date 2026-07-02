param(
    [string]$Tenant = "",
    [switch]$Force,
    [switch]$SkipSync
)

$root = Resolve-Path (Join-Path $PSScriptRoot '..')
Set-Location $root

Write-Host "Running tenant audit from: $root" -ForegroundColor Cyan

$provisionArgs = @('php', 'artisan', 'tenancy:audit')
if ($Tenant -ne '') {
    $provisionArgs += $Tenant
}

if ($Force) {
    $provisionArgs += '--force'
}
if ($SkipSync) {
    $provisionArgs += '--skip-sync'
}

& $provisionArgs[0] $provisionArgs[1..($provisionArgs.Length - 1)]
$provisionExitCode = $LASTEXITCODE

if ($provisionExitCode -ne 0) {
    Write-Host "Provision/audit command failed with exit code $provisionExitCode" -ForegroundColor Red
    exit $provisionExitCode
}

exit 0

# Install Laravel Scheduler as a Windows Scheduled Task
#
# Laravel's `schedule:run` needs to be invoked every minute. On Windows
# (where Herd ships PHP), the cleanest way is a Scheduled Task that runs
# every minute and calls `php artisan schedule:run`. This script registers
# that task. Run as Administrator.
#
# Usage (PowerShell, admin):
#   cd C:\Users\NanoChip\Herd\My-lawyer
#   powershell -ExecutionPolicy Bypass -File scripts\install-windows-scheduler.ps1
#
# To uninstall:
#   Unregister-ScheduledTask -TaskName "MyLawyerScheduler" -Confirm:$false

$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$phpExe = "C:\Users\$env:USERNAME\.config\herd\bin\php84\php.exe"

if (!(Test-Path $phpExe)) {
    Write-Error "PHP not found at $phpExe — adjust path in this script."
    exit 1
}
if (!(Test-Path "$projectRoot\artisan")) {
    Write-Error "artisan not found in $projectRoot — run from project root."
    exit 1
}

$taskName = "MyLawyerScheduler"

# Existing? Unregister first so we always reinstall a clean copy.
$existing = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
if ($existing) {
    Write-Host "Removing existing $taskName task..."
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
}

$action = New-ScheduledTaskAction -Execute $phpExe `
    -Argument "artisan schedule:run" `
    -WorkingDirectory $projectRoot

# Run every minute, indefinitely
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) `
    -RepetitionInterval (New-TimeSpan -Minutes 1)

$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -ExecutionTimeLimit (New-TimeSpan -Minutes 10)

# Run as the current user; if you need it to run when no one is logged in,
# pass -User and -Password (or use SYSTEM) at registration time.
Register-ScheduledTask `
    -TaskName $taskName `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Description "My-lawyer Laravel scheduler — runs schedule:run every minute" `
    -RunLevel Highest

Write-Host ""
Write-Host "✓ $taskName registered. It will fire every minute starting at $(Get-Date)."
Write-Host ""
Write-Host "Verify with:"
Write-Host "  Get-ScheduledTask -TaskName $taskName"
Write-Host ""
Write-Host "Logs go to: $projectRoot\storage\logs\laravel.log"
Write-Host "Test once now:"
Write-Host "  Start-ScheduledTask -TaskName $taskName"

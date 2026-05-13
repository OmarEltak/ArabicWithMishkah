# Install Laravel queue worker as a Windows Scheduled Task
#
# Background jobs (VerifyDocumentFreshnessJob, IngestEastlawsQueryJob)
# need a queue worker to process them. This task starts `queue:work` at
# system boot and restarts it if it dies. Run as Administrator.
#
# Usage:
#   powershell -ExecutionPolicy Bypass -File scripts\install-windows-queue-worker.ps1
#
# To uninstall:
#   Unregister-ScheduledTask -TaskName "MyLawyerQueueWorker" -Confirm:$false

$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$phpExe = "C:\Users\$env:USERNAME\.config\herd\bin\php84\php.exe"
$taskName = "MyLawyerQueueWorker"

if (!(Test-Path $phpExe)) { Write-Error "PHP not found at $phpExe"; exit 1 }
if (!(Test-Path "$projectRoot\artisan")) { Write-Error "artisan not found"; exit 1 }

$existing = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
if ($existing) {
    Write-Host "Removing existing $taskName task..."
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
}

# `queue:work --tries=3 --timeout=300 --max-time=3600` —
# auto-restart hourly so memory leaks don't accumulate.
$action = New-ScheduledTaskAction -Execute $phpExe `
    -Argument "artisan queue:work --tries=3 --timeout=300 --max-time=3600" `
    -WorkingDirectory $projectRoot

# Trigger at system startup
$trigger = New-ScheduledTaskTrigger -AtStartup

$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -RestartInterval (New-TimeSpan -Minutes 1) `
    -RestartCount 999 `
    -ExecutionTimeLimit (New-TimeSpan -Hours 2)

Register-ScheduledTask `
    -TaskName $taskName `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Description "My-lawyer queue worker — processes background jobs" `
    -RunLevel Highest

Write-Host ""
Write-Host "✓ $taskName registered. It will auto-start at next boot."
Write-Host "  To start it now without rebooting:"
Write-Host "    Start-ScheduledTask -TaskName $taskName"

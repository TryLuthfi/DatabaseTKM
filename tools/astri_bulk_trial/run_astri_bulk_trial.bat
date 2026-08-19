@echo off
setlocal

cd /d "%~dp0..\.."

echo Running ASTRI bulk trial...
echo Limit: 3 clusters
echo Delay: 3 seconds per request
echo Retry: 3 attempts per request
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "tools\astri_bulk_trial\astri_bulk_trial.ps1" -LimitClusters 3 -DelaySeconds 3 -MaxRetries 3

echo.
echo Done. Check folder: tmp_astri_bulk_trial
pause

@echo off
setlocal

cd /d "%~dp0.."

echo Running ASTRI full scrap trial...
echo LimitClusters: 3
echo PageStart: 1
echo PageLimit: 1
echo DelaySeconds: 3
echo MaxRetries: 3
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "automation - scrap\astri_full_scrap.ps1" -LimitClusters 0 -PageStart 1 -PageLimit 999 -DelaySeconds 5 -MaxRetries 5

echo.
echo Done. Check folder: automation - scrap\output
pause

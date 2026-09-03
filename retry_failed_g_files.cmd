@echo off
setlocal

set "BASE_DST=F:\LUTHFI\Expansion_G_Recovery"
set "LOG=F:\LUTHFI\retry_failed_g_files.log"

echo Retrying failed files only...
echo Log: %LOG%
echo.

robocopy "G:\1. TKM\1. FIBERISASI IFORTE\4. INDOSAT\22. BIT-PSN-FiberImprovement-DF137 - 4100035472\DOC ATP ELEKTRIK" "%BASE_DST%\1. TKM\1. FIBERISASI IFORTE\4. INDOSAT\22. BIT-PSN-FiberImprovement-DF137 - 4100035472\DOC ATP ELEKTRIK" "1. ATP - BAKALANPASRN_PL - NGEMPLAKREJO_TB.xlsx" /ZB /B /R:1 /W:5 /COPY:DAT /DCOPY:DAT /TEE /LOG+:"%LOG%"
echo.
echo First retry exit code: %ERRORLEVEL%
echo.

robocopy "G:\FILM\GAME" "%BASE_DST%\FILM\GAME" "THE WITCHER 3.rar" /ZB /B /R:1 /W:5 /COPY:DAT /DCOPY:DAT /TEE /LOG+:"%LOG%"
echo.
echo Second retry exit code: %ERRORLEVEL%
echo.

echo Done. Check: %LOG%
pause

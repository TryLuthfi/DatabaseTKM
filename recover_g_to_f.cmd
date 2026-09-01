@echo off
setlocal

set "SRC=G:\."
set "DST=F:\LUTHFI\Expansion_G_Recovery"
set "LOG=F:\LUTHFI\copy_expansion_g_admin.log"

echo Starting recovery copy...
echo Source: %SRC%
echo Target: %DST%
echo Log:    %LOG%
echo.

robocopy "%SRC%" "%DST%" /E /ZB /B /R:0 /W:0 /XJ /XA:SH /XD "G:\$RECYCLE.BIN" "G:\System Volume Information" "G:\Seagate" /COPY:DAT /DCOPY:DAT /TEE /LOG+:"%LOG%"
set "RC=%ERRORLEVEL%"

echo.
echo Robocopy exit code: %RC%
echo Log file: %LOG%
pause
exit /b %RC%

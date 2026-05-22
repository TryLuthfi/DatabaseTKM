@echo off
setlocal

set "MYSQLD_EXE=D:\XAMPP\mysql\bin\mysqld.exe"
set "MY_INI=D:\XAMPP\mysql\bin\my.ini"

if not exist "%MYSQLD_EXE%" (
    echo [ERROR] File tidak ditemukan: %MYSQLD_EXE%
    exit /b 1
)

netstat -ano | findstr /R /C:":3306 .*LISTENING" >nul
if "%errorlevel%"=="0" (
    echo [INFO] MySQL sudah berjalan di port 3306.
    exit /b 0
)

echo [INFO] Menjalankan MySQL manual (non-service)...
start "" /min "%MYSQLD_EXE%" --defaults-file="%MY_INI%"
ping -n 4 127.0.0.1 >nul

netstat -ano | findstr ":3306" >nul
if not "%errorlevel%"=="0" (
    echo [ERROR] MySQL belum listen di port 3306.
    echo [INFO] Cek log: D:\XAMPP\mysql\data\mysql_error.log
    exit /b 1
)

echo [OK] MySQL berjalan di port 3306.
exit /b 0

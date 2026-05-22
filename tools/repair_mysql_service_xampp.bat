@echo off
setlocal

set "MYSQL_BIN=D:\XAMPP\mysql\bin"
set "MYSQLD_EXE=%MYSQL_BIN%\mysqld.exe"
set "MY_INI=%MYSQL_BIN%\my.ini"

echo ==========================================
echo XAMPP MySQL Service Repair
echo ==========================================

if not exist "%MYSQLD_EXE%" (
    echo [ERROR] File tidak ditemukan: %MYSQLD_EXE%
    exit /b 1
)

net session >nul 2>&1
if not "%errorlevel%"=="0" (
    echo [ERROR] Jalankan file ini dengan Run as Administrator.
    exit /b 1
)

echo [1/6] Stop process mysqld yang masih berjalan...
taskkill /F /IM mysqld.exe >nul 2>&1

echo [2/6] Remove service mysql lama (jika ada)...
"%MYSQLD_EXE%" --remove mysql >nul 2>&1

echo [3/6] Install service mysql baru...
"%MYSQLD_EXE%" --install mysql --defaults-file="%MY_INI%"
if errorlevel 1 (
    echo [ERROR] Install service mysql gagal.
    exit /b 1
)

echo [4/6] Set startup service ke Automatic...
sc.exe config mysql start= auto >nul

echo [5/6] Start service mysql...
net start mysql
if errorlevel 1 (
    echo [ERROR] Service mysql gagal start.
    echo [INFO] Cek log: D:\XAMPP\mysql\data\mysql_error.log
    exit /b 1
)

echo [6/6] Verifikasi service dan port...
sc.exe query mysql | findstr /I "RUNNING" >nul
if errorlevel 1 (
    echo [ERROR] Service mysql belum RUNNING.
    exit /b 1
)

netstat -ano | findstr ":3306" >nul
if errorlevel 1 (
    echo [WARNING] Port 3306 belum LISTEN, cek konfigurasi my.ini.
    exit /b 1
)

echo [OK] Repair selesai. MySQL service aktif dan port 3306 siap.
exit /b 0

param(
    [string] $RemoteHost = "212.85.27.154",
    [string] $RemoteUser = "root",
    [string] $RemoteAppPath = "/www/wwwroot/databasetkm.com",
    [string] $KeyPath = "tmp_codex_vps_ed25519",
    [string] $KnownHostsPath = "tmp_vps_known_hosts",
    [string] $LocalHost = "",
    [int] $LocalPort = 0,
    [string] $LocalUser = "",
    [string] $LocalPassword = "",
    [string] $LocalDatabase = "",
    [switch] $SkipLocalBackup,
    [switch] $NoDropLocalDatabase
)

$ErrorActionPreference = "Stop"

function Read-DotEnv {
    param([string] $Path)
    $result = @{}
    if (!(Test-Path -LiteralPath $Path)) {
        return $result
    }

    foreach ($line in Get-Content -LiteralPath $Path) {
        $trimmed = $line.Trim()
        if ($trimmed -eq "" -or $trimmed.StartsWith("#") -or $trimmed.StartsWith(";") -or $trimmed -notmatch "=") {
            continue
        }

        $parts = $trimmed.Split("=", 2)
        $key = $parts[0].Trim()
        $value = $parts[1].Trim()
        if (($value.StartsWith('"') -and $value.EndsWith('"')) -or ($value.StartsWith("'") -and $value.EndsWith("'"))) {
            $value = $value.Substring(1, $value.Length - 2)
        }
        $result[$key] = $value
    }

    return $result
}

function Get-MysqlPasswordArgs {
    param([string] $Password)
    if ($Password -ne "") {
        return @("-p$Password")
    }
    return @()
}

function Get-MysqlConnectionArgs {
    param(
        [string] $HostName,
        [int] $Port,
        [string] $User,
        [string] $Password
    )

    $args = @("-h", $HostName, "-u", $User)
    if ($Port -gt 0) {
        $args += @("-P", [string] $Port)
    }
    $args += Get-MysqlPasswordArgs $Password
    return $args
}

function Assert-SafeDatabaseName {
    param([string] $Name)
    if ($Name -notmatch "^[A-Za-z0-9_]+$") {
        throw "Nama database lokal tidak aman: $Name"
    }
}

function New-ProtectedSshPrivateKeyCopy {
    param([string] $Path)

    $currentIdentity = [System.Security.Principal.WindowsIdentity]::GetCurrent()
    $sourceFile = Get-Item -LiteralPath $Path
    $tempKeyPath = Join-Path ([System.IO.Path]::GetTempPath()) ("databasetkm_ssh_key_{0}_{1}.tmp" -f $PID, ([guid]::NewGuid().ToString("N")))

    Copy-Item -LiteralPath $sourceFile.FullName -Destination $tempKeyPath -Force
    & icacls $tempKeyPath /inheritance:r 2>$null | Out-Null

    $acl = Get-Acl -LiteralPath $tempKeyPath
    foreach ($rule in @($acl.Access)) {
        $identityName = $rule.IdentityReference.Value
        if ($identityName -ne $currentIdentity.Name) {
            try {
                & icacls $tempKeyPath /remove:g $identityName 2>$null | Out-Null
                & icacls $tempKeyPath /remove:d $identityName 2>$null | Out-Null
            }
            catch {
                # Best effort only; temp file is still safer than the shared source key.
            }
        }
    }

    & icacls $tempKeyPath /remove:g "Everyone" "BUILTIN\Users" "Users" "Authenticated Users" "NT AUTHORITY\Authenticated Users" "BUILTIN\Administrators" "NT AUTHORITY\SYSTEM" 2>$null | Out-Null
    & icacls $tempKeyPath /grant:r "$($currentIdentity.Name)`:(F)" 2>$null | Out-Null

    return $tempKeyPath
}

function Quote-ProcessArgument {
    param([string] $Value)
    if ($Value -notmatch '[\s"]') {
        return $Value
    }
    return '"' + ($Value -replace '\\(?=\\*")', '$0$0' -replace '"', '\"') + '"'
}

function Quote-BashSingleQuoted {
    param([string] $Value)
    return "'" + ($Value -replace "'", "'\''") + "'"
}

function Invoke-ProcessOutputToFile {
    param(
        [string] $FilePath,
        [string[]] $Arguments,
        [string] $OutputFile
    )

    $startInfo = [System.Diagnostics.ProcessStartInfo]::new()
    $startInfo.FileName = $FilePath
    $startInfo.Arguments = ($Arguments | ForEach-Object { Quote-ProcessArgument $_ }) -join " "
    $startInfo.UseShellExecute = $false
    $startInfo.RedirectStandardOutput = $true
    $startInfo.RedirectStandardError = $true

    $process = [System.Diagnostics.Process]::new()
    $process.StartInfo = $startInfo
    $stderrBuilder = [System.Text.StringBuilder]::new()
    $errorHandler = [System.Diagnostics.DataReceivedEventHandler] {
        param($sender, $eventArgs)
        if ($null -ne $eventArgs.Data -and $eventArgs.Data.Trim() -ne "") {
            [void] $stderrBuilder.AppendLine($eventArgs.Data)
        }
    }
    $process.add_ErrorDataReceived($errorHandler)
    [void] $process.Start()
    $process.BeginErrorReadLine()

    $outputStream = [System.IO.File]::Create($OutputFile)
    try {
        $process.StandardOutput.BaseStream.CopyTo($outputStream)
    }
    finally {
        $outputStream.Close()
    }

    $process.WaitForExit()
    $process.CancelErrorRead()
    $process.remove_ErrorDataReceived($errorHandler)

    $stderr = $stderrBuilder.ToString()
    if ($stderr.Trim() -ne "") {
        Write-Host $stderr.Trim()
    }

    return @{
        ExitCode = $process.ExitCode
        Error = $stderr.Trim()
    }
}

function Test-MysqlDumpLooksComplete {
    param([string] $Path)
    if (!(Test-Path -LiteralPath $Path) -or ((Get-Item -LiteralPath $Path).Length -le 0)) {
        return $false
    }

    $tail = Get-Content -LiteralPath $Path -Tail 20 -ErrorAction SilentlyContinue
    return (($tail | Where-Object { $_ -like "-- Dump completed on *" }) | Measure-Object).Count -gt 0
}

$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$envPath = Join-Path $repoRoot ".env"
$env = Read-DotEnv $envPath
if ($LocalHost -eq "") {
    if ($env.ContainsKey("HOSTNAME")) {
        $LocalHost = [string] $env["HOSTNAME"]
    }
}
if ($LocalHost -eq "") {
    $LocalHost = "127.0.0.1"
}
if ($LocalHost -match "^\[?([^\]]+)\]?:(\d+)$") {
    $LocalHost = $Matches[1]
    if ($LocalPort -le 0) {
        $LocalPort = [int] $Matches[2]
    }
}
if ($LocalPort -le 0 -and $env.ContainsKey("DB_PORT")) {
    $parsedPort = 0
    if ([int]::TryParse([string] $env["DB_PORT"], [ref] $parsedPort)) {
        $LocalPort = $parsedPort
    }
}
if ($LocalUser -eq "") {
    if ($env.ContainsKey("USERNAME")) {
        $LocalUser = [string] $env["USERNAME"]
    }
}
if ($LocalUser -eq "") {
    $LocalUser = "root"
}
if ($LocalPassword -eq "") {
    if ($env.ContainsKey("PASSWORD")) {
        $LocalPassword = [string] $env["PASSWORD"]
    }
}
if ($LocalDatabase -eq "") {
    if ($env.ContainsKey("DATABASE")) {
        $LocalDatabase = [string] $env["DATABASE"]
    }
}
if ($LocalDatabase -eq "") {
    throw "LocalDatabase kosong. Isi DATABASE di .env atau pass -LocalDatabase."
}
Assert-SafeDatabaseName $LocalDatabase

$xamppRoot = Split-Path (Split-Path $repoRoot -Parent) -Parent
$mysql = Join-Path $xamppRoot "mysql\bin\mysql.exe"
$mysqldump = Join-Path $xamppRoot "mysql\bin\mysqldump.exe"
if (!(Test-Path -LiteralPath $mysql)) {
    throw "mysql.exe XAMPP tidak ditemukan: $mysql"
}
if (!(Test-Path -LiteralPath $mysqldump)) {
    throw "mysqldump.exe XAMPP tidak ditemukan: $mysqldump"
}

$keyFullPath = (Resolve-Path (Join-Path $repoRoot $KeyPath)).Path
$knownHostsFullPath = (Resolve-Path (Join-Path $repoRoot $KnownHostsPath)).Path
$sshKeyPath = New-ProtectedSshPrivateKeyCopy $keyFullPath
Write-Host "[SSH Key] Menggunakan key sementara untuk $([System.Security.Principal.WindowsIdentity]::GetCurrent().Name)"

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupDir = Join-Path $repoRoot "backups\VPS"
New-Item -ItemType Directory -Force -Path $backupDir | Out-Null
$localDumpPath = Join-Path $backupDir ("databasetkm_com_{0}_mysql_data.sql" -f $timestamp)
$localXamppDumpPath = Join-Path $backupDir ("databasetkm_com_{0}_mysql_data_xampp.sql" -f $timestamp)
$localBeforePath = Join-Path $backupDir ("local_before_vps_import_{0}_{1}.sql" -f $LocalDatabase, $timestamp)

$sshArgs = @(
    "-i", $sshKeyPath,
    "-o", "UserKnownHostsFile=$knownHostsFullPath",
    "-o", "StrictHostKeyChecking=yes",
    "-o", "BatchMode=yes",
    "-o", "ServerAliveInterval=30",
    "-o", "ServerAliveCountMax=6",
    "-o", "ConnectTimeout=30",
    "-C",
    "$RemoteUser@$RemoteHost"
)

$remoteScript = @"
set -e
ENV_FILE="$RemoteAppPath/.env"
read_env() {
    grep -E "^`$1=" "`$ENV_FILE" | tail -n 1 | cut -d= -f2-
}
DB_HOST="`$(read_env HOSTNAME)"
DB_USER="`$(read_env USERNAME)"
DB_PASS="`$(read_env PASSWORD)"
DB_NAME="`$(read_env DATABASE)"
STAMP="`$(date +%Y%m%d_%H%M%S)"
DUMP="/tmp/databasetkm_com_`${STAMP}_mysql_data.sql"
MYSQL_PWD="`$DB_PASS" mysqldump --single-transaction --routines --triggers --events --default-character-set=utf8mb4 -h "`$DB_HOST" -u "`$DB_USER" "`$DB_NAME" > "`$DUMP"
echo "`$DUMP"
"@
$remoteScript = $remoteScript -replace "`r`n", "`n" -replace "`r", "`n"

Write-Host "[Target lokal] $LocalUser@$LocalHost$(if ($LocalPort -gt 0) { ":$LocalPort" })/$LocalDatabase"
Write-Host "[1/6] Dump database VPS..."
$remoteScriptBase64 = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($remoteScript))
$remoteOutput = & ssh @sshArgs "printf '%s' '$remoteScriptBase64' | base64 -d | bash"
if ($LASTEXITCODE -ne 0) {
    throw "Gagal dump database dari VPS."
}
$remoteDumpPath = (($remoteOutput | Select-Object -Last 1) -as [string]).Trim()
if ($remoteDumpPath -eq "") {
    throw "Path dump remote kosong."
}

Write-Host "[2/6] Download dump ke $localDumpPath..."
$downloadCommand = "cat " + (Quote-BashSingleQuoted $remoteDumpPath)
$downloadPartPath = "$localDumpPath.part"
$downloadResult = $null
$downloadMaxAttempts = 3
for ($downloadAttempt = 1; $downloadAttempt -le $downloadMaxAttempts; $downloadAttempt++) {
    if (Test-Path -LiteralPath $downloadPartPath) {
        Remove-Item -LiteralPath $downloadPartPath -Force
    }

    if ($downloadMaxAttempts -gt 1) {
        Write-Host "[Download] Percobaan $downloadAttempt/$downloadMaxAttempts..."
    }

    $downloadResult = Invoke-ProcessOutputToFile "ssh" ($sshArgs + @($downloadCommand)) $downloadPartPath
    $dumpLooksComplete = Test-MysqlDumpLooksComplete $downloadPartPath
    if ([int] $downloadResult.ExitCode -eq 0 -and $dumpLooksComplete) {
        Move-Item -LiteralPath $downloadPartPath -Destination $localDumpPath -Force
        break
    }

    if ([int] $downloadResult.ExitCode -ne 0 -and $dumpLooksComplete) {
        Write-Host "[Download] SSH keluar dengan ExitCode=$($downloadResult.ExitCode), tapi dump memiliki footer lengkap. File tetap dipakai."
        Move-Item -LiteralPath $downloadPartPath -Destination $localDumpPath -Force
        break
    }

    $partialSize = 0
    if (Test-Path -LiteralPath $downloadPartPath) {
        $partialSize = (Get-Item -LiteralPath $downloadPartPath).Length
    }
    Write-Host "[Download] Gagal pada percobaan $downloadAttempt. ExitCode=$($downloadResult.ExitCode), bytes=$partialSize"

    if ($downloadAttempt -lt $downloadMaxAttempts) {
        Start-Sleep -Seconds (5 * $downloadAttempt)
    }
}

if (!(Test-MysqlDumpLooksComplete $localDumpPath)) {
    $lastError = ""
    if ($downloadResult -and $downloadResult.Error) {
        $lastError = " Detail SSH: " + $downloadResult.Error
    }
    throw "Gagal download dump dari VPS via SSH stream setelah $downloadMaxAttempts percobaan.$lastError"
}

Write-Host "[3/6] Normalize dump untuk MariaDB XAMPP..."
$reader = [System.IO.StreamReader]::new($localDumpPath)
$writer = [System.IO.StreamWriter]::new($localXamppDumpPath, $false, [System.Text.UTF8Encoding]::new($false))
try {
    while (($line = $reader.ReadLine()) -ne $null) {
        if ($line -like "/*M!999999\-*") {
            continue
        }
        $line = $line.Replace("utf8mb4_uca1400_ai_ci", "utf8mb4_unicode_ci")
        $line = $line.Replace("utf8mb3_uca1400_ai_ci", "utf8_general_ci")
        $writer.WriteLine($line)
    }
}
finally {
    $reader.Close()
    $writer.Close()
}

$localMysqlArgs = Get-MysqlConnectionArgs $LocalHost $LocalPort $LocalUser $LocalPassword
$dbExists = & $mysql @localMysqlArgs --batch --skip-column-names -e "SHOW DATABASES LIKE '$LocalDatabase';"
if ($LASTEXITCODE -ne 0) {
    throw "Tidak bisa konek ke MySQL lokal XAMPP."
}

if (($dbExists | Out-String).Trim() -ne "" -and !$SkipLocalBackup) {
    Write-Host "[4/6] Backup database lokal ke $localBeforePath..."
    & $mysqldump @localMysqlArgs --single-transaction --routines --triggers --events --default-character-set=utf8mb4 --result-file=$localBeforePath $LocalDatabase
    if ($LASTEXITCODE -ne 0) {
        throw "Backup database lokal gagal. Import dibatalkan."
    }
} else {
    Write-Host "[4/6] Backup lokal dilewati."
}

Write-Host "[5/6] Create/update database lokal $LocalDatabase..."
if ($NoDropLocalDatabase) {
    & $mysql @localMysqlArgs -e "CREATE DATABASE IF NOT EXISTS ``$LocalDatabase`` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
} else {
    & $mysql @localMysqlArgs -e "DROP DATABASE IF EXISTS ``$LocalDatabase``; CREATE DATABASE ``$LocalDatabase`` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
}
if ($LASTEXITCODE -ne 0) {
    throw "Gagal create database lokal."
}

$sourcePath = $localXamppDumpPath.Replace("\", "/")
Write-Host "[6/6] Import dump VPS ke database lokal..."
& $mysql @localMysqlArgs $LocalDatabase -e "source $sourcePath"
if ($LASTEXITCODE -ne 0) {
    throw "Import dump ke database lokal gagal."
}

& ssh @sshArgs "rm -f '$remoteDumpPath'" | Out-Null
if ($sshKeyPath -and (Test-Path -LiteralPath $sshKeyPath)) {
    Remove-Item -LiteralPath $sshKeyPath -Force
}

Write-Host "Selesai."
Write-Host "Dump VPS: $localDumpPath"
Write-Host "Dump XAMPP: $localXamppDumpPath"
if (Test-Path -LiteralPath $localBeforePath) {
    Write-Host "Backup lokal sebelum import: $localBeforePath"
}

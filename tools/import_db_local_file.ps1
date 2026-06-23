param(
    [Parameter(Mandatory = $true)]
    [string] $DumpPath,
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

function Quote-ProcessArgument {
    param([string] $Value)
    if ($Value -notmatch '[\s"]') {
        return $Value
    }
    return '"' + ($Value -replace '\\(?=\\*")', '$0$0' -replace '"', '\"') + '"'
}

function Invoke-ProcessWithInputFile {
    param(
        [string] $FilePath,
        [string[]] $Arguments,
        [string] $InputFile
    )

    $startInfo = [System.Diagnostics.ProcessStartInfo]::new()
    $startInfo.FileName = $FilePath
    $startInfo.Arguments = ($Arguments | ForEach-Object { Quote-ProcessArgument $_ }) -join " "
    $startInfo.UseShellExecute = $false
    $startInfo.RedirectStandardInput = $true
    $startInfo.RedirectStandardOutput = $true
    $startInfo.RedirectStandardError = $true

    $process = [System.Diagnostics.Process]::new()
    $process.StartInfo = $startInfo
    [void] $process.Start()

    $inputStream = [System.IO.File]::OpenRead($InputFile)
    try {
        $inputStream.CopyTo($process.StandardInput.BaseStream)
        $process.StandardInput.Close()
    }
    finally {
        $inputStream.Close()
    }

    $stdout = $process.StandardOutput.ReadToEnd()
    $stderr = $process.StandardError.ReadToEnd()
    $process.WaitForExit()

    if ($stdout.Trim() -ne "") {
        Write-Host $stdout.Trim()
    }
    if ($stderr.Trim() -ne "") {
        Write-Host $stderr.Trim()
    }

    return $process.ExitCode
}

$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$envPath = Join-Path $repoRoot ".env"
$env = Read-DotEnv $envPath

if (!(Test-Path -LiteralPath $DumpPath)) {
    throw "File dump tidak ditemukan: $DumpPath"
}
$dumpFullPath = (Resolve-Path -LiteralPath $DumpPath).Path

if ($LocalHost -eq "" -and $env.ContainsKey("HOSTNAME")) {
    $LocalHost = [string] $env["HOSTNAME"]
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
if ($LocalUser -eq "" -and $env.ContainsKey("USERNAME")) {
    $LocalUser = [string] $env["USERNAME"]
}
if ($LocalUser -eq "") {
    $LocalUser = "root"
}
if ($LocalPassword -eq "" -and $env.ContainsKey("PASSWORD")) {
    $LocalPassword = [string] $env["PASSWORD"]
}
if ($LocalDatabase -eq "" -and $env.ContainsKey("DATABASE")) {
    $LocalDatabase = [string] $env["DATABASE"]
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

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupDir = Join-Path $repoRoot "backups\LOCAL_IMPORT"
New-Item -ItemType Directory -Force -Path $backupDir | Out-Null
$localBeforePath = Join-Path $backupDir ("local_before_file_import_{0}_{1}.sql" -f $LocalDatabase, $timestamp)
$normalizedDumpPath = Join-Path $backupDir ("normalized_import_{0}_{1}.sql" -f $LocalDatabase, $timestamp)

Write-Host "[Target lokal] $LocalUser@$LocalHost$(if ($LocalPort -gt 0) { ":$LocalPort" })/$LocalDatabase"
Write-Host "[1/5] Normalize dump untuk MariaDB XAMPP..."
$reader = [System.IO.StreamReader]::new($dumpFullPath)
$writer = [System.IO.StreamWriter]::new($normalizedDumpPath, $false, [System.Text.UTF8Encoding]::new($false))
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
    Write-Host "[2/5] Backup database lokal ke $localBeforePath..."
    & $mysqldump @localMysqlArgs --single-transaction --routines --triggers --events --default-character-set=utf8mb4 --result-file=$localBeforePath $LocalDatabase
    if ($LASTEXITCODE -ne 0) {
        throw "Backup database lokal gagal. Import dibatalkan."
    }
} else {
    Write-Host "[2/5] Backup lokal dilewati."
}

Write-Host "[3/5] Create/update database lokal $LocalDatabase..."
if ($NoDropLocalDatabase) {
    & $mysql @localMysqlArgs -e "CREATE DATABASE IF NOT EXISTS ``$LocalDatabase`` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
} else {
    & $mysql @localMysqlArgs -e "DROP DATABASE IF EXISTS ``$LocalDatabase``; CREATE DATABASE ``$LocalDatabase`` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
}
if ($LASTEXITCODE -ne 0) {
    throw "Gagal create/update database lokal."
}

Write-Host "[4/5] Import dump lokal..."
$importExitCode = Invoke-ProcessWithInputFile $mysql ($localMysqlArgs + @("--default-character-set=utf8mb4", "--binary-mode=1", $LocalDatabase)) $normalizedDumpPath
if ($importExitCode -ne 0) {
    throw "Import dump ke MySQL lokal gagal."
}

Write-Host "[5/5] Selesai."
Write-Host "Dump sumber: $dumpFullPath"
Write-Host "Backup sebelum import: $localBeforePath"

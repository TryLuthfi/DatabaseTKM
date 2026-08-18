$ErrorActionPreference = 'Stop'

$repoRoot = Resolve-Path -LiteralPath (Join-Path $PSScriptRoot '..\..')
$modulePath = Join-Path $PSScriptRoot 'AstriSync.bas'
$outputPath = Join-Path $repoRoot 'AstriSyncPrototype.xlsm'

function Get-DotEnvValue([string]$key) {
    $envPath = Join-Path $repoRoot '.env'
    if (-not (Test-Path -LiteralPath $envPath)) {
        return ''
    }

    $line = Get-Content -LiteralPath $envPath | Where-Object {
        $_ -match "^\s*$([regex]::Escape($key))\s*="
    } | Select-Object -First 1

    if (-not $line) {
        return ''
    }

    return (($line -split '=', 2)[1]).Trim().Trim('"').Trim("'")
}

$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false

try {
    $workbook = $excel.Workbooks.Add()

    while ($workbook.Worksheets.Count -lt 3) {
        $workbook.Worksheets.Add() | Out-Null
    }

    $config = $workbook.Worksheets.Item(1)
    $input = $workbook.Worksheets.Item(2)
    $result = $workbook.Worksheets.Item(3)

    $config.Name = 'Config'
    $input.Name = 'Input'
    $result.Name = 'Result'

    $config.Range('A1').Value2 = 'ASTRI Sync Config'
    $config.Range('A1').Font.Bold = $true
    $config.Range('A2').Value2 = 'ASTRI_BASE_URL'
    $config.Range('A3').Value2 = 'USERNAME'
    $config.Range('A4').Value2 = 'PASSWORD'
    $config.Range('A6').Value2 = 'MAX_PAGES_PER_ROUTE'
    $config.Range('A7').Value2 = 'REQUEST_DELAY_MS'
    $config.Range('A9').Value2 = 'Catatan'
    $config.Range('B2').Value2 = Get-DotEnvValue 'ASTRI_BASE_URL'
    $config.Range('B3').Value2 = ''
    $config.Range('B4').Value2 = ''
    $config.Range('B6').Value2 = 3
    $config.Range('B7').Value2 = 1000
    $config.Range('B9').Value2 = 'Credential sengaja tidak diisi dari .env. Isi manual saat testing, jangan share file berisi password.'
    $config.Columns('A:B').AutoFit()

    $input.Range('A1').Value2 = 'ASTRI Sync Preview'
    $input.Range('A1').Font.Bold = $true
    $input.Range('A2').Value2 = 'Cluster Code'
    $input.Range('A3').Value2 = 'Cluster Name'
    $input.Range('A6').Value2 = 'Status'
    $input.Range('A7').Value2 = 'Last Run'
    $input.Range('A9').Value2 = 'Result'
    $input.Range('A9').Font.Bold = $true
    $input.Range('B2').Value2 = ''
    $input.Range('B3').Value2 = ''
    $input.Range('B6').Value2 = 'Ready'
    $input.Range('B7').Value2 = ''
    $input.Columns('A:B').AutoFit()

    $button = $input.Buttons().Add(260, 36, 130, 32)
    $button.Caption = 'Sync Astri'
    $button.OnAction = 'SyncAstriDocuments'

    $headers = @(
        'Route', 'Scope', 'Phase', 'Astri Type', 'Astri Label', 'Derived Status',
        'File Count', 'Upload Date', 'Verified By', 'Verified At', 'Revision By',
        'Revision At', 'Revision Remark', 'Filename', 'Scraped At', 'Detail URL'
    )
    for ($i = 0; $i -lt $headers.Count; $i++) {
        $input.Cells.Item(10, $i + 1).Value2 = $headers[$i]
        $result.Cells.Item(1, $i + 1).Value2 = $headers[$i]
    }
    $input.Rows.Item(10).Font.Bold = $true
    $result.Rows.Item(1).Font.Bold = $true
    $input.Columns.AutoFit()
    $result.Columns.AutoFit()

    $workbook.VBProject.VBComponents.Import($modulePath) | Out-Null

    if (Test-Path -LiteralPath $outputPath) {
        Remove-Item -LiteralPath $outputPath -Force
    }

    $workbook.SaveAs($outputPath, 52)
    $workbook.Close($true)

    Write-Output $outputPath
}
finally {
    $excel.Quit()
    [System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel) | Out-Null
}

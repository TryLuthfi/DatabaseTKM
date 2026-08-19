param(
    [int]$LimitClusters = 3,
    [int]$DelaySeconds = 3,
    [int]$MaxRetries = 3,
    [string]$OutputName = ''
)

$ErrorActionPreference = 'Stop'

$RepoRoot = Resolve-Path -LiteralPath (Join-Path $PSScriptRoot '..\..')
$EnvPath = Join-Path $RepoRoot '.env'
$RunDir = Join-Path $RepoRoot 'tmp_astri_bulk_trial'

New-Item -ItemType Directory -Force -Path $RunDir | Out-Null

if ([string]::IsNullOrWhiteSpace($OutputName)) {
    $OutputName = 'astri_bulk_trial_' + (Get-Date -Format 'yyyyMMdd_HHmmss')
}

$CookiePath = Join-Path $RunDir 'cookies.txt'
$CsvPath = Join-Path $RunDir ($OutputName + '.csv')
$XlsxPath = Join-Path $RunDir ($OutputName + '.xlsx')
$LogPath = Join-Path $RunDir ($OutputName + '.log.txt')

function Write-Log([string]$Message) {
    $line = '[' + (Get-Date -Format 'yyyy-MM-dd HH:mm:ss') + '] ' + $Message
    Write-Host $line
    Add-Content -LiteralPath $LogPath -Value $line
}

function Get-DotEnvValue([string]$Key) {
    $line = Get-Content -LiteralPath $EnvPath | Where-Object {
        $_ -match "^\s*$([regex]::Escape($Key))\s*="
    } | Select-Object -First 1

    if (-not $line) {
        return ''
    }

    return (($line -split '=', 2)[1]).Trim().Trim('"').Trim("'")
}

function UrlEncode([string]$Value) {
    return [System.Uri]::EscapeDataString($Value)
}

function HtmlDecode([string]$Value) {
    return [System.Net.WebUtility]::HtmlDecode($Value)
}

function Get-Title([string]$Html) {
    $match = [regex]::Match($Html, '<title[^>]*>(.*?)</title>', 'IgnoreCase,Singleline')
    if ($match.Success) {
        return (($match.Groups[1].Value -replace '\s+', ' ').Trim())
    }
    return ''
}

function Get-InfoCardValue([string]$Html, [string]$Label) {
    $pattern = '<small[^>]*>\s*' + [regex]::Escape($Label) + '\s*</small>\s*<p[^>]*(?:title=["'']([^"'']*)["''])?[^>]*>([\s\S]*?)</p>'
    $match = [regex]::Match($Html, $pattern, 'IgnoreCase')
    if (-not $match.Success) {
        return ''
    }

    if (-not [string]::IsNullOrWhiteSpace($match.Groups[1].Value)) {
        return (HtmlDecode $match.Groups[1].Value).Trim()
    }

    $text = [regex]::Replace($match.Groups[2].Value, '<[^>]+>', ' ')
    return ((HtmlDecode $text) -replace '\s+', ' ').Trim()
}

function Invoke-AstriGet([string]$Url, [string]$OutFile) {
    for ($attempt = 1; $attempt -le $MaxRetries; $attempt++) {
        & curl.exe -k -sS -L --http1.1 --compressed `
            --connect-timeout 30 `
            --max-time 180 `
            -A 'ZEYN-Astri-Bulk-Trial/1.0' `
            -c $CookiePath -b $CookiePath `
            -o $OutFile `
            --url $Url

        if ($LASTEXITCODE -eq 0 -and (Test-Path -LiteralPath $OutFile) -and (Get-Item -LiteralPath $OutFile).Length -gt 0) {
            Start-Sleep -Seconds $DelaySeconds
            return
        }

        Write-Log ("GET failed attempt $attempt/$MaxRetries. curl_exit=$LASTEXITCODE")
        if ($attempt -lt $MaxRetries) {
            Start-Sleep -Seconds ([Math]::Max($DelaySeconds, 5) * $attempt)
        }
    }

    throw "curl GET failed after $MaxRetries attempts for $Url"
}

function Invoke-AstriPost([string]$Url, [string]$Body, [string]$OutFile) {
    for ($attempt = 1; $attempt -le $MaxRetries; $attempt++) {
        & curl.exe -k -sS -L --http1.1 --compressed `
            --connect-timeout 30 `
            --max-time 180 `
            -A 'ZEYN-Astri-Bulk-Trial/1.0' `
            -c $CookiePath -b $CookiePath `
            -H 'Content-Type: application/x-www-form-urlencoded' `
            --data $Body `
            -o $OutFile `
            --url $Url

        if ($LASTEXITCODE -eq 0 -and (Test-Path -LiteralPath $OutFile) -and (Get-Item -LiteralPath $OutFile).Length -gt 0) {
            Start-Sleep -Seconds $DelaySeconds
            return
        }

        Write-Log ("POST failed attempt $attempt/$MaxRetries. curl_exit=$LASTEXITCODE")
        if ($attempt -lt $MaxRetries) {
            Start-Sleep -Seconds ([Math]::Max($DelaySeconds, 5) * $attempt)
        }
    }

    throw "curl POST failed after $MaxRetries attempts for $Url"
}

function Login-Astri([string]$BaseUrl, [string]$Username, [string]$Password) {
    Write-Log 'Login Astri...'

    $loginFile = Join-Path $RunDir 'login.html'
    $afterLoginFile = Join-Path $RunDir 'after_login.html'

    Invoke-AstriGet $BaseUrl $loginFile

    $postData = 'csrf_name=&csrf_value=&user-identity=' + (UrlEncode $Username) + '&password=' + (UrlEncode $Password)

    Invoke-AstriPost ($BaseUrl + 'login-process') $postData $afterLoginFile

    $html = Get-Content -LiteralPath $afterLoginFile -Raw
    $loggedIn = ($html -match 'Logout|logout') -and ($html -notmatch 'user-identity|type=["'']password["'']')
    if (-not $loggedIn) {
        throw ('Login failed. Title: ' + (Get-Title $html))
    }

    Write-Log 'Login OK.'
}

function Get-InitialClusters([string]$BaseUrl) {
    Write-Log "Ambil $LimitClusters cluster awal dari CW ATP list..."

    $listFile = Join-Path $RunDir 'initial_cw_atp_list.html'
    Invoke-AstriGet ($BaseUrl + 'rfs-document/cw-atp/') $listFile

    $html = Get-Content -LiteralPath $listFile -Raw
    $matches = [regex]::Matches($html, 'href=["'']([^"'']*rfs-document/list/by-invoice-phase/cw-atp[^"'']*)["'']', 'IgnoreCase')

    $items = New-Object System.Collections.Generic.List[object]
    $seen = @{}

    foreach ($match in $matches) {
        $href = HtmlDecode $match.Groups[1].Value
        if ($href -notmatch 'cluster_code=([^&]+)') {
            continue
        }

        $clusterCode = [System.Uri]::UnescapeDataString($matches[1])
        if ($seen.ContainsKey($clusterCode)) {
            continue
        }

        $workOrder = ''
        $vendorLabel = ''
        if ($href -match 'work_order_number=([^&]+)') {
            $workOrder = [System.Uri]::UnescapeDataString($matches[1])
        }
        if ($href -match 'vendor_label=([^&]+)') {
            $vendorLabel = [System.Uri]::UnescapeDataString($matches[1])
        }

        $items.Add([pscustomobject]@{
            cluster_code = $clusterCode
            work_order_number = $workOrder
            vendor_label = $vendorLabel
        })
        $seen[$clusterCode] = $true

        if ($items.Count -ge $LimitClusters) {
            break
        }
    }

    Write-Log ('Cluster ditemukan: ' + $items.Count)
    return $items
}

function Get-DetailUrl([string]$BaseUrl, [string]$RoutePath, [string]$DetailPart, [object]$Cluster) {
    if ($RoutePath -eq 'project-opname/cluster/') {
        $filterUrl = $BaseUrl + $RoutePath + '?filter%5Bglobal%5D=' + (UrlEncode $Cluster.cluster_code)
        $listFile = Join-Path $RunDir ('project_opname_' + $Cluster.cluster_code + '.html')
        Invoke-AstriGet $filterUrl $listFile
        $html = Get-Content -LiteralPath $listFile -Raw
        $matches = [regex]::Matches($html, 'href=["'']([^"'']*material/cluster/project-opname/review[^"'']*)["'']', 'IgnoreCase')
        foreach ($match in $matches) {
            $href = HtmlDecode $match.Groups[1].Value
            if ($href -match ('cluster_code=' + [regex]::Escape($Cluster.cluster_code))) {
                return ConvertTo-AbsoluteUrl $BaseUrl $href
            }
        }
        return ''
    }

    $query = 'cluster_code=' + (UrlEncode $Cluster.cluster_code) +
        '&work_order_number=' + (UrlEncode $Cluster.work_order_number) +
        '&vendor_label=' + (UrlEncode $Cluster.vendor_label)

    return $BaseUrl + $DetailPart + '?' + $query
}

function ConvertTo-AbsoluteUrl([string]$BaseUrl, [string]$Href) {
    $Href = $Href -replace ' ', '%20'
    if ($Href -match '^https?://') {
        return $Href
    }
    if ($Href.StartsWith('/')) {
        return $BaseUrl.TrimEnd('/') + $Href
    }
    return $BaseUrl + $Href
}

function Get-DocumentRowsFromDetail([string]$BaseUrl, [object]$Cluster, [object]$Route) {
    $detailUrl = Get-DetailUrl $BaseUrl $Route.path $Route.detail $Cluster
    if ([string]::IsNullOrWhiteSpace($detailUrl)) {
        $status = if ($Route.name -match 'PROJECT OPNAME') { 'PROJECT_OPNAME_NOT_FOUND' } else { 'DETAIL_NOT_FOUND' }
        return @([pscustomobject]@{
            scraped_at = Get-Date
            route = $Route.name
            scope = $Route.scope
            phase = $Route.phase
            cluster_code = $Cluster.cluster_code
            clean_list_name = ''
            work_order_number = $Cluster.work_order_number
            vendor_label = $Cluster.vendor_label
            astri_type = ''
            astri_label = ''
            derived_status = $status
            file_count = ''
            upload_date = ''
            verified_by = ''
            verified_at = ''
            revision_by = ''
            revision_at = ''
            revision_remark = ''
            filename = ''
            detail_url = ''
        })
    }

    $safeName = ($Route.name + '_' + $Cluster.cluster_code -replace '[^A-Za-z0-9]+', '_').Trim('_')
    $detailFile = Join-Path $RunDir ($safeName + '_detail.html')
    Invoke-AstriGet $detailUrl $detailFile

    $html = Get-Content -LiteralPath $detailFile -Raw
    $jsonMatch = [regex]::Match($html, 'let\s+documentListJsonRaw\s*=\s*`([\s\S]*?)`;', 'IgnoreCase')

    if (-not $jsonMatch.Success) {
        return @([pscustomobject]@{
            scraped_at = Get-Date
            route = $Route.name
            scope = $Route.scope
            phase = $Route.phase
            cluster_code = $Cluster.cluster_code
            clean_list_name = Get-InfoCardValue $html 'Name (Clean List)'
            work_order_number = $Cluster.work_order_number
            vendor_label = $Cluster.vendor_label
            astri_type = ''
            astri_label = ''
            derived_status = 'PARSE_FAILED'
            file_count = ''
            upload_date = ''
            verified_by = ''
            verified_at = ''
            revision_by = ''
            revision_at = ''
            revision_remark = ''
            filename = ''
            detail_url = $detailUrl
        })
    }

    $cleanName = Get-InfoCardValue $html 'Name (Clean List)'
    $docObject = $jsonMatch.Groups[1].Value | ConvertFrom-Json

    $rows = New-Object System.Collections.Generic.List[object]

    foreach ($property in $docObject.PSObject.Properties) {
        $doc = $property.Value
        $data = @($doc.data)

        $verifiedItems = @($data | Where-Object { $_.verified_by_username -or $_.verified_by_fullname -or $_.verified_at })
        $revisionItems = @($data | Where-Object { $_.requested_revision_at -or $_.requested_revision_remarks })

        $selected = $null
        $status = 'NOT UPLOADED'

        if ($verifiedItems.Count -gt 0) {
            $status = 'APPROVED'
            $selected = $verifiedItems[-1]
        }
        elseif ($revisionItems.Count -gt 0) {
            $status = 'REVISION'
            $selected = $revisionItems[-1]
        }
        elseif ($data.Count -gt 0) {
            $status = 'ON REVIEW'
            $selected = $data[-1]
        }

        $rows.Add([pscustomobject]@{
            scraped_at = Get-Date
            route = $Route.name
            scope = $Route.scope
            phase = $Route.phase
            cluster_code = $Cluster.cluster_code
            clean_list_name = $cleanName
            work_order_number = $Cluster.work_order_number
            vendor_label = $Cluster.vendor_label
            astri_type = $doc.name
            astri_label = $doc.label
            derived_status = $status
            file_count = $data.Count
            upload_date = if ($selected) { $selected.created_at } else { '' }
            verified_by = if ($selected) { if ($selected.verified_by_fullname) { $selected.verified_by_fullname } else { $selected.verified_by_username } } else { '' }
            verified_at = if ($selected) { $selected.verified_at } else { '' }
            revision_by = if ($selected) { $selected.requested_revision_by_fullname } else { '' }
            revision_at = if ($selected) { $selected.requested_revision_at } else { '' }
            revision_remark = if ($selected) { $selected.requested_revision_remarks } else { '' }
            filename = if ($selected) { $selected.filename } else { '' }
            detail_url = $detailUrl
        })
    }

    return $rows
}

function Export-ToExcel([string]$CsvFile, [string]$ExcelFile) {
    $excelType = [type]::GetTypeFromProgID('Excel.Application')
    if (-not $excelType) {
        Write-Log 'Excel COM tidak tersedia, output hanya CSV.'
        return
    }

    $excel = New-Object -ComObject Excel.Application
    $excel.Visible = $false
    $excel.DisplayAlerts = $false

    try {
        $workbook = $excel.Workbooks.Open($CsvFile)
        $worksheet = $workbook.Worksheets.Item(1)
        $worksheet.Name = 'Astri_Status'
        $worksheet.Rows.Item(1).Font.Bold = $true
        $worksheet.Columns.AutoFit() | Out-Null
        if (Test-Path -LiteralPath $ExcelFile) {
            Remove-Item -LiteralPath $ExcelFile -Force
        }
        $workbook.SaveAs($ExcelFile, 51)
        $workbook.Close($true)
    }
    finally {
        $excel.Quit()
        [System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel) | Out-Null
    }
}

$baseUrl = (Get-DotEnvValue 'ASTRI_BASE_URL').TrimEnd('/') + '/'
$username = Get-DotEnvValue 'ASTRI_USERNAME'
$password = Get-DotEnvValue 'ASTRI_PASSWORD'

if ([string]::IsNullOrWhiteSpace($baseUrl) -or [string]::IsNullOrWhiteSpace($username) -or [string]::IsNullOrWhiteSpace($password)) {
    throw 'ASTRI_BASE_URL / ASTRI_USERNAME / ASTRI_PASSWORD belum lengkap di .env'
}

$routes = @(
    [pscustomobject]@{ name = 'ATP CLUSTER - CW ATP'; path = 'rfs-document/cw-atp/'; phase = 'CW ATP'; scope = 'CLUSTER'; detail = 'rfs-document/list/by-invoice-phase/cw-atp' },
    [pscustomobject]@{ name = 'ATP CLUSTER - FULL OPM'; path = 'rfs-document/full-opm/'; phase = 'FULL OPM'; scope = 'CLUSTER'; detail = 'rfs-document/list/by-invoice-phase/full-opm' },
    [pscustomobject]@{ name = 'ATP CLUSTER - RFS'; path = 'rfs-document/rfs/'; phase = 'RFS'; scope = 'CLUSTER'; detail = 'rfs-document/list/by-invoice-phase/rfs' },
    [pscustomobject]@{ name = 'PROJECT OPNAME - CLUSTER'; path = 'project-opname/cluster/'; phase = 'PROJECT OPNAME'; scope = 'CLUSTER'; detail = 'material/cluster/project-opname/review' },
    [pscustomobject]@{ name = 'ATP SUBFEEDER - CW ATP'; path = 'rfs-document/subfeeder/cw-atp'; phase = 'CW ATP'; scope = 'SUBFEEDER'; detail = 'rfs-document/subfeeder/list/by-invoice-phase/cw-atp' },
    [pscustomobject]@{ name = 'ATP SUBFEEDER - FULL OPM'; path = 'rfs-document/subfeeder/full-opm'; phase = 'FULL OPM'; scope = 'SUBFEEDER'; detail = 'rfs-document/subfeeder/list/by-invoice-phase/full-opm' },
    [pscustomobject]@{ name = 'ATP SUBFEEDER - RFS'; path = 'rfs-document/subfeeder/rfs'; phase = 'RFS'; scope = 'SUBFEEDER'; detail = 'rfs-document/subfeeder/list/by-invoice-phase/rfs' }
)

Write-Log '=== ASTRI bulk trial start ==='
Write-Log "LimitClusters=$LimitClusters; DelaySeconds=$DelaySeconds; MaxRetries=$MaxRetries"

Login-Astri $baseUrl $username $password

$clusters = @(Get-InitialClusters $baseUrl)
if ($clusters.Count -eq 0) {
    throw 'Tidak ada cluster awal yang ditemukan.'
}

$allRows = New-Object System.Collections.Generic.List[object]

foreach ($cluster in $clusters) {
    Write-Log ('Proses cluster ' + $cluster.cluster_code + ' / ' + $cluster.work_order_number)
    foreach ($route in $routes) {
        try {
            Write-Log ('  Route: ' + $route.name)
            $rows = @(Get-DocumentRowsFromDetail $baseUrl $cluster $route)
            foreach ($row in $rows) {
                $allRows.Add($row)
            }
        }
        catch {
            Write-Log ('  ERROR route ' + $route.name + ': ' + $_.Exception.Message)
            $allRows.Add([pscustomobject]@{
                scraped_at = Get-Date
                route = $route.name
                scope = $route.scope
                phase = $route.phase
                cluster_code = $cluster.cluster_code
                clean_list_name = ''
                work_order_number = $cluster.work_order_number
                vendor_label = $cluster.vendor_label
                astri_type = ''
                astri_label = ''
                derived_status = 'ROUTE_ERROR'
                file_count = ''
                upload_date = ''
                verified_by = ''
                verified_at = ''
                revision_by = ''
                revision_at = ''
                revision_remark = $_.Exception.Message
                filename = ''
                detail_url = ''
            })
        }
    }
}

$allRows | Export-Csv -LiteralPath $CsvPath -NoTypeInformation -Encoding UTF8
Write-Log ('CSV dibuat: ' + $CsvPath)

Export-ToExcel $CsvPath $XlsxPath
if (Test-Path -LiteralPath $XlsxPath) {
    Write-Log ('Excel dibuat: ' + $XlsxPath)
}

Write-Log '=== ASTRI bulk trial done ==='
Write-Host ''
Write-Host 'Output:'
Write-Host $CsvPath
if (Test-Path -LiteralPath $XlsxPath) {
    Write-Host $XlsxPath
}

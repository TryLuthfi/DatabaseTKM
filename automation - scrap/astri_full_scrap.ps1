param(
    [int]$LimitClusters = 3,
    [int]$PageStart = 1,
    [int]$PageLimit = 1,
    [int]$DelaySeconds = 3,
    [int]$MaxRetries = 3,
    [switch]$IncludeCertificateSentDetail,
    [string]$OutputName = ''
)

$ErrorActionPreference = 'Stop'

$AutomationDir = $PSScriptRoot
$RepoRoot = Resolve-Path -LiteralPath (Join-Path $AutomationDir '..')
$EnvPath = Join-Path $RepoRoot '.env'
$RunDir = Join-Path $AutomationDir 'output'

New-Item -ItemType Directory -Force -Path $RunDir | Out-Null

if ([string]::IsNullOrWhiteSpace($OutputName)) {
    $OutputName = 'astri_scrap_' + (Get-Date -Format 'yyyyMMdd_HHmmss')
}

$CookiePath = Join-Path $RunDir 'cookies.txt'
$ClusterCsvPath = Join-Path $RunDir ($OutputName + '_cluster_status.csv')
$DocumentCsvPath = Join-Path $RunDir ($OutputName + '_document_detail.csv')
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

function Strip-Html([string]$Html) {
    $text = [regex]::Replace($Html, '<script[\s\S]*?</script>|<style[\s\S]*?</style>', ' ', 'IgnoreCase')
    $text = [regex]::Replace($text, '<[^>]+>', ' ')
    return ((HtmlDecode $text) -replace '\s+', ' ').Trim()
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

    return Strip-Html $match.Groups[2].Value
}

function Invoke-AstriGet([string]$Url, [string]$OutFile) {
    for ($attempt = 1; $attempt -le $MaxRetries; $attempt++) {
        & curl.exe -k -sS -L --http1.1 --compressed `
            --connect-timeout 30 `
            --max-time 180 `
            -A 'ZEYN-Astri-Full-Scrap/1.0' `
            -c $CookiePath -b $CookiePath `
            -o $OutFile `
            --url $Url

        if ($LASTEXITCODE -eq 0 -and (Test-Path -LiteralPath $OutFile) -and (Get-Item -LiteralPath $OutFile).Length -gt 0) {
            Start-Sleep -Seconds $DelaySeconds
            return
        }

        Write-Log "GET failed attempt $attempt/$MaxRetries. curl_exit=$LASTEXITCODE"
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
            -A 'ZEYN-Astri-Full-Scrap/1.0' `
            -c $CookiePath -b $CookiePath `
            -H 'Content-Type: application/x-www-form-urlencoded' `
            --data $Body `
            -o $OutFile `
            --url $Url

        if ($LASTEXITCODE -eq 0 -and (Test-Path -LiteralPath $OutFile) -and (Get-Item -LiteralPath $OutFile).Length -gt 0) {
            Start-Sleep -Seconds $DelaySeconds
            return
        }

        Write-Log "POST failed attempt $attempt/$MaxRetries. curl_exit=$LASTEXITCODE"
        if ($attempt -lt $MaxRetries) {
            Start-Sleep -Seconds ([Math]::Max($DelaySeconds, 5) * $attempt)
        }
    }

    throw "curl POST failed after $MaxRetries attempts for $Url"
}

function Login-Astri([string]$BaseUrl, [string]$Username, [string]$Password) {
    Write-Log 'Login Astri...'

    $loginFile = Join-Path $RunDir '_login.html'
    $afterLoginFile = Join-Path $RunDir '_after_login.html'

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

function ConvertTo-AbsoluteUrl([string]$BaseUrl, [string]$Href) {
    $Href = (HtmlDecode $Href) -replace ' ', '%20'
    if ($Href -match '^https?://') {
        return $Href
    }
    if ($Href.StartsWith('/')) {
        return $BaseUrl.TrimEnd('/') + $Href
    }
    return $BaseUrl + $Href
}

function Get-ClusterSeedFromList([string]$BaseUrl) {
    $clusters = New-Object System.Collections.Generic.List[object]
    $seen = @{}

    for ($pageNo = $PageStart; $pageNo -lt ($PageStart + $PageLimit); $pageNo++) {
        if ($pageNo -le 1) {
            $url = $BaseUrl + 'rfs-document/cw-atp/'
        }
        else {
            $url = $BaseUrl + 'rfs-document/cw-atp/page-' + $pageNo
        }

        Write-Log "Scan seed page CW ATP page $pageNo"
        $listFile = Join-Path $RunDir ("_seed_cw_atp_page_$pageNo.html")
        Invoke-AstriGet $url $listFile

        $html = Get-Content -LiteralPath $listFile -Raw
        $matches = [regex]::Matches($html, 'href=["'']([^"'']*rfs-document/list/by-invoice-phase/cw-atp[^"'']*)["'']', 'IgnoreCase')

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

            $clusters.Add([pscustomobject]@{
                cluster_code = $clusterCode
                work_order_number = $workOrder
                vendor_label = $vendorLabel
            })
            $seen[$clusterCode] = $true

            if ($LimitClusters -gt 0 -and $clusters.Count -ge $LimitClusters) {
                Write-Log ('Seed cluster limit reached: ' + $clusters.Count)
                return $clusters
            }
        }
    }

    Write-Log ('Seed cluster found: ' + $clusters.Count)
    return $clusters
}

function Get-DetailUrl([string]$BaseUrl, [object]$Cluster, [object]$Route) {
    if ($Route.name -match 'PROJECT OPNAME') {
        $filterUrl = $BaseUrl + $Route.path + '?filter%5Bglobal%5D=' + (UrlEncode $Cluster.cluster_code)
        $listFile = Join-Path $RunDir ('_project_opname_' + $Cluster.cluster_code + '.html')
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

    return $BaseUrl + $Route.detail + '?' + $query
}

function Get-ClusterSummary([string]$Html, [object]$Cluster, [string]$SummarySourceUrl) {
    $cwStatus = Get-InfoCardValue $Html 'Document CW ATP Status'
    $fullOpmStatus = Get-InfoCardValue $Html 'Document Full OPM Status'
    $rfsStatus = Get-InfoCardValue $Html 'Document RFS Status'
    $facStatus = Get-InfoCardValue $Html 'Document FAC Status'

    $statusValues = @($cwStatus, $fullOpmStatus, $rfsStatus, $facStatus) | Where-Object { -not [string]::IsNullOrWhiteSpace($_) }
    $worstStatus = 'UNKNOWN'

    if ($statusValues | Where-Object { $_ -match 'REVISION|REJECT' }) {
        $worstStatus = 'REVISION'
    }
    elseif ($statusValues | Where-Object { $_ -match 'ON REVIEW|UPLOAD|WAITING|UNVERIFIED|DOCUMENT UPLOADED' }) {
        $worstStatus = 'ON REVIEW'
    }
    elseif ($statusValues.Count -gt 0 -and -not ($statusValues | Where-Object { $_ -notmatch 'CERTIFICATE SENT|APPROVED|VERIFIED|DONE' })) {
        $worstStatus = 'APPROVED'
    }

    return [pscustomobject]@{
        scraped_at = Get-Date
        cluster_code = $Cluster.cluster_code
        clean_list_name = Get-InfoCardValue $Html 'Name (Clean List)'
        work_order_number = $Cluster.work_order_number
        vendor_label = $Cluster.vendor_label
        area = Get-InfoCardValue $Html 'Area'
        status_rfs = Get-InfoCardValue $Html 'Status RFS'
        document_cw_atp_status = $cwStatus
        document_full_opm_status = $fullOpmStatus
        document_rfs_status = $rfsStatus
        document_fac_status = $facStatus
        worst_document_status = $worstStatus
        summary_source_url = $SummarySourceUrl
    }
}

function Should-SkipDocumentDetail([string]$StatusValue) {
    if ($IncludeCertificateSentDetail) {
        return $false
    }
    return (([string]$StatusValue).Trim().ToUpperInvariant() -eq 'CERTIFICATE SENT')
}

function Get-RouteSummaryStatus([object]$Summary, [object]$Route) {
    switch ($Route.summary_key) {
        'cw_atp' { return $Summary.document_cw_atp_status }
        'full_opm' { return $Summary.document_full_opm_status }
        'rfs' { return $Summary.document_rfs_status }
        'fac' { return $Summary.document_fac_status }
        default { return '' }
    }
}

function Get-DocumentRowsFromDetail([string]$BaseUrl, [object]$Cluster, [object]$Route, [object]$Summary) {
    $summaryStatus = Get-RouteSummaryStatus $Summary $Route
    if (Should-SkipDocumentDetail $summaryStatus) {
        return @([pscustomobject]@{
            scraped_at = Get-Date
            cluster_code = $Cluster.cluster_code
            clean_list_name = $Summary.clean_list_name
            work_order_number = $Cluster.work_order_number
            route = $Route.name
            scope = $Route.scope
            phase = $Route.phase
            route_summary_status = $summaryStatus
            astri_type = ''
            astri_label = ''
            derived_status = 'SKIPPED_CERTIFICATE_SENT'
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

    $detailUrl = Get-DetailUrl $BaseUrl $Cluster $Route
    if ([string]::IsNullOrWhiteSpace($detailUrl)) {
        $status = if ($Route.name -match 'PROJECT OPNAME') { 'PROJECT_OPNAME_NOT_FOUND' } else { 'DETAIL_NOT_FOUND' }
        return @([pscustomobject]@{
            scraped_at = Get-Date
            cluster_code = $Cluster.cluster_code
            clean_list_name = $Summary.clean_list_name
            work_order_number = $Cluster.work_order_number
            route = $Route.name
            scope = $Route.scope
            phase = $Route.phase
            route_summary_status = $summaryStatus
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
            cluster_code = $Cluster.cluster_code
            clean_list_name = $Summary.clean_list_name
            work_order_number = $Cluster.work_order_number
            route = $Route.name
            scope = $Route.scope
            phase = $Route.phase
            route_summary_status = $summaryStatus
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
            cluster_code = $Cluster.cluster_code
            clean_list_name = $Summary.clean_list_name
            work_order_number = $Cluster.work_order_number
            route = $Route.name
            scope = $Route.scope
            phase = $Route.phase
            route_summary_status = $summaryStatus
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

function Add-WorksheetFromCsv($Workbook, [string]$CsvPath, [string]$SheetName, [int]$TabColor, [bool]$IsClusterSheet) {
    $worksheet = $Workbook.Worksheets.Add()
    $worksheet.Name = $SheetName
    $worksheet.Tab.Color = $TabColor

    $rows = @(Import-Csv -LiteralPath $CsvPath)
    if ($rows.Count -eq 0) {
        return $worksheet
    }

    $headers = @($rows[0].PSObject.Properties.Name)
    for ($col = 0; $col -lt $headers.Count; $col++) {
        $worksheet.Cells.Item(1, $col + 1).Value2 = $headers[$col]
    }

    for ($row = 0; $row -lt $rows.Count; $row++) {
        for ($col = 0; $col -lt $headers.Count; $col++) {
            $worksheet.Cells.Item($row + 2, $col + 1).Value2 = [string]$rows[$row].PSObject.Properties[$headers[$col]].Value
        }
    }

    $worksheet.Rows.Item(1).Font.Bold = $true
    $worksheet.Rows.Item(1).Interior.Color = $TabColor
    $worksheet.Columns.AutoFit() | Out-Null

    $lastRow = $rows.Count + 1
    $lastCol = $headers.Count
    $rangeAddress = 'A2:' + (ConvertTo-ExcelColumnName $lastCol) + $lastRow
    $formatRange = $worksheet.Range($rangeAddress)

    if ($IsClusterSheet) {
        $statusColumn = 12
    }
    else {
        $statusColumn = 11
    }

    Add-StatusFormat $formatRange $statusColumn 'APPROVED' 13434828
    Add-StatusFormat $formatRange $statusColumn 'ON REVIEW' 13431551
    Add-StatusFormat $formatRange $statusColumn 'REVISION' 8696052
    Add-StatusFormat $formatRange $statusColumn 'NOT UPLOADED' 16777215
    Add-StatusFormat $formatRange $statusColumn 'SKIPPED_CERTIFICATE_SENT' 14277081
    Add-StatusFormat $formatRange $statusColumn 'PROJECT_OPNAME_NOT_FOUND' 14277081
    Add-StatusFormat $formatRange $statusColumn 'ROUTE_ERROR' 13421823

    return $worksheet
}

function Add-StatusFormat($Range, [int]$StatusColumn, [string]$StatusText, [int]$Color) {
    $columnName = ConvertTo-ExcelColumnName $StatusColumn
    $condition = $Range.FormatConditions.Add(2, 0, "=`$$columnName`2=""$StatusText""")
    $condition.Interior.Color = $Color
    $condition.Font.Color = 0
}

function ConvertTo-ExcelColumnName([int]$ColumnNumber) {
    $name = ''
    while ($ColumnNumber -gt 0) {
        $mod = ($ColumnNumber - 1) % 26
        $name = [char](65 + $mod) + $name
        $ColumnNumber = [math]::Floor(($ColumnNumber - $mod) / 26)
    }
    return $name
}

function Export-ToExcelWorkbook([string]$ClusterCsv, [string]$DocumentCsv, [string]$ExcelFile) {
    $excelType = [type]::GetTypeFromProgID('Excel.Application')
    if (-not $excelType) {
        Write-Log 'Excel COM tidak tersedia, output hanya CSV.'
        return
    }

    $excel = New-Object -ComObject Excel.Application
    $excel.Visible = $false
    $excel.DisplayAlerts = $false

    try {
        $workbook = $excel.Workbooks.Add()
        while ($workbook.Worksheets.Count -gt 1) {
            $workbook.Worksheets.Item($workbook.Worksheets.Count).Delete()
        }

        Add-WorksheetFromCsv $workbook $ClusterCsv 'Cluster_Status' 15773696 $true | Out-Null
        Add-WorksheetFromCsv $workbook $DocumentCsv 'Document_Detail' 5296274 $false | Out-Null
        $workbook.Worksheets.Item($workbook.Worksheets.Count).Delete()

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
    [pscustomobject]@{ name = 'ATP CLUSTER - CW ATP'; path = 'rfs-document/cw-atp/'; phase = 'CW ATP'; scope = 'CLUSTER'; detail = 'rfs-document/list/by-invoice-phase/cw-atp'; summary_key = 'cw_atp' },
    [pscustomobject]@{ name = 'ATP CLUSTER - FULL OPM'; path = 'rfs-document/full-opm/'; phase = 'FULL OPM'; scope = 'CLUSTER'; detail = 'rfs-document/list/by-invoice-phase/full-opm'; summary_key = 'full_opm' },
    [pscustomobject]@{ name = 'ATP CLUSTER - RFS'; path = 'rfs-document/rfs/'; phase = 'RFS'; scope = 'CLUSTER'; detail = 'rfs-document/list/by-invoice-phase/rfs'; summary_key = 'rfs' },
    [pscustomobject]@{ name = 'PROJECT OPNAME - CLUSTER'; path = 'project-opname/cluster/'; phase = 'PROJECT OPNAME'; scope = 'CLUSTER'; detail = 'material/cluster/project-opname/review'; summary_key = 'project_opname' },
    [pscustomobject]@{ name = 'ATP SUBFEEDER - CW ATP'; path = 'rfs-document/subfeeder/cw-atp'; phase = 'CW ATP'; scope = 'SUBFEEDER'; detail = 'rfs-document/subfeeder/list/by-invoice-phase/cw-atp'; summary_key = 'cw_atp' },
    [pscustomobject]@{ name = 'ATP SUBFEEDER - FULL OPM'; path = 'rfs-document/subfeeder/full-opm'; phase = 'FULL OPM'; scope = 'SUBFEEDER'; detail = 'rfs-document/subfeeder/list/by-invoice-phase/full-opm'; summary_key = 'full_opm' },
    [pscustomobject]@{ name = 'ATP SUBFEEDER - RFS'; path = 'rfs-document/subfeeder/rfs'; phase = 'RFS'; scope = 'SUBFEEDER'; detail = 'rfs-document/subfeeder/list/by-invoice-phase/rfs'; summary_key = 'rfs' }
)

Write-Log '=== ASTRI full scrap start ==='
Write-Log "LimitClusters=$LimitClusters; PageStart=$PageStart; PageLimit=$PageLimit; DelaySeconds=$DelaySeconds; MaxRetries=$MaxRetries; IncludeCertificateSentDetail=$IncludeCertificateSentDetail"

Login-Astri $baseUrl $username $password

$clusters = @(Get-ClusterSeedFromList $baseUrl)
if ($clusters.Count -eq 0) {
    throw 'Tidak ada cluster yang ditemukan.'
}

$clusterRows = New-Object System.Collections.Generic.List[object]
$documentRows = New-Object System.Collections.Generic.List[object]
$summaryRoute = $routes[0]

foreach ($cluster in $clusters) {
    Write-Log ('Proses cluster summary ' + $cluster.cluster_code + ' / ' + $cluster.work_order_number)

    try {
        $summaryUrl = Get-DetailUrl $baseUrl $cluster $summaryRoute
        $summaryFile = Join-Path $RunDir ('_summary_' + $cluster.cluster_code + '.html')
        Invoke-AstriGet $summaryUrl $summaryFile
        $summaryHtml = Get-Content -LiteralPath $summaryFile -Raw
        $summary = Get-ClusterSummary $summaryHtml $cluster $summaryUrl
        $clusterRows.Add($summary)
    }
    catch {
        Write-Log ('  ERROR summary: ' + $_.Exception.Message)
        $summary = [pscustomobject]@{
            scraped_at = Get-Date
            cluster_code = $cluster.cluster_code
            clean_list_name = ''
            work_order_number = $cluster.work_order_number
            vendor_label = $cluster.vendor_label
            area = ''
            status_rfs = ''
            document_cw_atp_status = ''
            document_full_opm_status = ''
            document_rfs_status = ''
            document_fac_status = ''
            worst_document_status = 'ROUTE_ERROR'
            summary_source_url = ''
        }
        $clusterRows.Add($summary)
    }

    foreach ($route in $routes) {
        try {
            Write-Log ('  Detail route: ' + $route.name)
            $rows = @(Get-DocumentRowsFromDetail $baseUrl $cluster $route $summary)
            foreach ($row in $rows) {
                $documentRows.Add($row)
            }
        }
        catch {
            Write-Log ('  ERROR detail ' + $route.name + ': ' + $_.Exception.Message)
            $documentRows.Add([pscustomobject]@{
                scraped_at = Get-Date
                cluster_code = $cluster.cluster_code
                clean_list_name = $summary.clean_list_name
                work_order_number = $cluster.work_order_number
                route = $route.name
                scope = $route.scope
                phase = $route.phase
                route_summary_status = Get-RouteSummaryStatus $summary $route
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

$clusterRows | Export-Csv -LiteralPath $ClusterCsvPath -NoTypeInformation -Encoding UTF8
$documentRows | Export-Csv -LiteralPath $DocumentCsvPath -NoTypeInformation -Encoding UTF8

Write-Log ('Cluster CSV dibuat: ' + $ClusterCsvPath)
Write-Log ('Document CSV dibuat: ' + $DocumentCsvPath)

Export-ToExcelWorkbook $ClusterCsvPath $DocumentCsvPath $XlsxPath
if (Test-Path -LiteralPath $XlsxPath) {
    Write-Log ('Excel dibuat: ' + $XlsxPath)
}

Set-Content -LiteralPath $CookiePath -Value '' -Encoding ASCII
Write-Log 'Cookie dikosongkan.'
Write-Log '=== ASTRI full scrap done ==='

Write-Host ''
Write-Host 'Output:'
Write-Host $ClusterCsvPath
Write-Host $DocumentCsvPath
if (Test-Path -LiteralPath $XlsxPath) {
    Write-Host $XlsxPath
}

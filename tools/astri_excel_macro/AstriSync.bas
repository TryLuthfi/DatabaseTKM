Attribute VB_Name = "AstriSync"
Option Explicit

Private Const ROUTE_COUNT As Long = 7
Private Const AUTO_COOKIE As String = "__AUTO_COOKIE__"

Public Sub SyncAstriDocuments()
    On Error GoTo HandleError

    Dim wsInput As Worksheet, wsConfig As Worksheet, wsResult As Worksheet
    Set wsInput = ThisWorkbook.Worksheets("Input")
    Set wsConfig = ThisWorkbook.Worksheets("Config")
    Set wsResult = wsInput

    Dim baseUrl As String, username As String, password As String
    baseUrl = Trim(CStr(wsConfig.Range("B2").Value))
    username = Trim(CStr(wsConfig.Range("B3").Value))
    password = Trim(CStr(wsConfig.Range("B4").Value))

    If baseUrl = "" Or username = "" Or password = "" Then
        MsgBox "Isi ASTRI_BASE_URL, USERNAME, dan PASSWORD di sheet Config dulu.", vbExclamation
        Exit Sub
    End If

    If Right$(baseUrl, 1) <> "/" Then baseUrl = baseUrl & "/"

    Dim clusterCode As String, clusterName As String
    clusterCode = Trim(CStr(wsInput.Range("B2").Value))
    clusterName = Trim(CStr(wsInput.Range("B3").Value))

    If clusterCode = "" And clusterName = "" Then
        MsgBox "Isi Cluster Code atau Cluster Name di sheet Input dulu.", vbExclamation
        Exit Sub
    End If

    Dim maxPages As Long, delayMs As Long
    maxPages = CLng(Val(wsConfig.Range("B6").Value))
    delayMs = CLng(Val(wsConfig.Range("B7").Value))
    If maxPages < 1 Then maxPages = 3
    If delayMs < 500 Then delayMs = 800

    ClearResult wsResult
    wsInput.Range("B6").Value = "Logging in..."
    wsInput.Range("B8").Value = ""

    Dim cookieJar As String
    cookieJar = LoginAstri(baseUrl, username, password, wsInput)
    If cookieJar = "" Then Err.Raise vbObjectError + 100, , "Login gagal. Detail: " & CStr(wsInput.Range("B8").Value)

    Dim routes(1 To ROUTE_COUNT, 1 To 4) As String
    FillRoutes routes

    Dim resultRow As Long
    resultRow = 11

    Dim i As Long
    For i = 1 To ROUTE_COUNT
        On Error GoTo RouteError
        wsInput.Range("B6").Value = "Searching " & routes(i, 1)
        DoEvents

        Dim detailUrl As String
        detailUrl = FindDetailUrl(baseUrl, cookieJar, routes(i, 2), routes(i, 3), clusterCode, clusterName, maxPages, delayMs)

        If detailUrl <> "" Then
            wsInput.Range("B6").Value = "Reading " & routes(i, 1)
            DoEvents
            resultRow = AppendDetailDocuments(wsResult, resultRow, baseUrl, cookieJar, detailUrl, routes(i, 1), routes(i, 3), routes(i, 4))
            SleepMs delayMs
        Else
            resultRow = AppendNotFound(wsResult, resultRow, routes(i, 1), routes(i, 3), routes(i, 4), clusterCode, clusterName)
        End If

        On Error GoTo HandleError
        GoTo ContinueRoute

RouteError:
        wsInput.Range("B8").Value = "Route error on " & routes(i, 1) & ": " & Err.Description
        resultRow = AppendRouteError(wsResult, resultRow, routes(i, 1), routes(i, 3), routes(i, 4), Err.Description)
        Err.Clear
        On Error GoTo HandleError

ContinueRoute:
    Next i

    wsInput.Range("B6").Value = "Done"
    wsInput.Range("B7").Value = Now
    wsResult.Columns.AutoFit
    MsgBox "Sync preview selesai. Cek sheet Result.", vbInformation
    Exit Sub

HandleError:
    wsInput.Range("B6").Value = "Error"
    MsgBox "Error: " & Err.Description, vbCritical
End Sub

Private Sub FillRoutes(ByRef routes() As String)
    routes(1, 1) = "ATP CLUSTER - CW ATP": routes(1, 2) = "rfs-document/cw-atp/": routes(1, 3) = "CW ATP": routes(1, 4) = "CLUSTER"
    routes(2, 1) = "ATP CLUSTER - FULL OPM": routes(2, 2) = "rfs-document/full-opm/": routes(2, 3) = "FULL OPM": routes(2, 4) = "CLUSTER"
    routes(3, 1) = "ATP CLUSTER - RFS": routes(3, 2) = "rfs-document/rfs/": routes(3, 3) = "RFS": routes(3, 4) = "CLUSTER"
    routes(4, 1) = "PROJECT OPNAME - CLUSTER": routes(4, 2) = "project-opname/cluster/": routes(4, 3) = "PROJECT OPNAME": routes(4, 4) = "CLUSTER"
    routes(5, 1) = "ATP SUBFEEDER - CW ATP": routes(5, 2) = "rfs-document/subfeeder/cw-atp": routes(5, 3) = "CW ATP": routes(5, 4) = "SUBFEEDER"
    routes(6, 1) = "ATP SUBFEEDER - FULL OPM": routes(6, 2) = "rfs-document/subfeeder/full-opm": routes(6, 3) = "FULL OPM": routes(6, 4) = "SUBFEEDER"
    routes(7, 1) = "ATP SUBFEEDER - RFS": routes(7, 2) = "rfs-document/subfeeder/rfs": routes(7, 3) = "RFS": routes(7, 4) = "SUBFEEDER"
End Sub

Private Function LoginAstri(ByVal baseUrl As String, ByVal username As String, ByVal password As String, ByVal statusSheet As Worksheet) As String
    Dim attemptNo As Long
    For attemptNo = 1 To 3
        statusSheet.Range("B6").Value = "Logging in... attempt " & CStr(attemptNo) & "/3"
        DoEvents

        Dim loginPage As Object
        Set loginPage = HttpRequest("GET", baseUrl, "", "")

        Dim csrfName As String, csrfValue As String
        csrfName = ExtractInputValue(loginPage.responseText, "csrf_name")
        csrfValue = ExtractInputValue(loginPage.responseText, "csrf_value")

        Dim body As String
        body = "csrf_name=" & UrlEncode(csrfName) & _
               "&csrf_value=" & UrlEncode(csrfValue) & _
               "&user-identity=" & UrlEncode(username) & _
               "&password=" & UrlEncode(password)

        Dim cookieJar As String
        cookieJar = CollectCookies(loginPage)

        Dim loginResponse As Object
        Set loginResponse = HttpRequest("POST", baseUrl & "login-process", body, cookieJar)
        cookieJar = MergeCookies(cookieJar, CollectCookies(loginResponse))

        SleepMs 2000

        Dim probeResponse As Object
        Set probeResponse = HttpRequest("GET", baseUrl & "setting/user/update", "", cookieJar)

        If IsLoggedInResponse(loginResponse.responseText) Or IsLoggedInResponse(probeResponse.responseText) Then
            If cookieJar = "" Then
                LoginAstri = AUTO_COOKIE
                statusSheet.Range("B8").Value = "Login OK on attempt " & CStr(attemptNo) & " using Excel internal cookie"
            Else
                LoginAstri = cookieJar
                statusSheet.Range("B8").Value = "Login OK on attempt " & CStr(attemptNo) & " with cookie"
            End If
            Exit Function
        End If

        statusSheet.Range("B8").Value = "Attempt " & CStr(attemptNo) & " failed. Login title: " & ExtractTitle(loginResponse.responseText) & "; probe title: " & ExtractTitle(probeResponse.responseText)
        SleepMs 3000
    Next attemptNo

    LoginAstri = ""
End Function

Private Function AppendRouteError(ByVal ws As Worksheet, ByVal startRow As Long, ByVal routeName As String, ByVal phaseName As String, ByVal scopeName As String, ByVal errorMessage As String) As Long
    ws.Cells(startRow, 1).Resize(1, 17).Value = Array("", routeName, scopeName, phaseName, "", "", "ROUTE_ERROR", "", "", "", "", "", "", errorMessage, "", Now, "")
    AppendRouteError = startRow + 1
End Function

Private Function IsLoggedInResponse(ByVal html As String) As Boolean
    IsLoggedInResponse = (InStr(1, html, "Logout", vbTextCompare) > 0 Or InStr(1, html, "/logout", vbTextCompare) > 0) _
        And InStr(1, html, "user-identity", vbTextCompare) = 0 _
        And InStr(1, html, "type=""password""", vbTextCompare) = 0
End Function

Private Function ExtractTitle(ByVal html As String) As String
    Dim re As Object, matches As Object
    Set re = CreateObject("VBScript.RegExp")
    re.Global = False
    re.IgnoreCase = True
    re.MultiLine = True
    re.Pattern = "<title[^>]*>([\s\S]*?)</title>"
    Set matches = re.Execute(html)
    If matches.Count > 0 Then
        ExtractTitle = Trim$(StripHtml(matches(0).SubMatches(0)))
    Else
        ExtractTitle = "(no title)"
    End If
End Function

Private Function FindDetailUrl(ByVal baseUrl As String, ByVal cookieJar As String, ByVal routePath As String, ByVal phaseName As String, ByVal clusterCode As String, ByVal clusterName As String, ByVal maxPages As Long, ByVal delayMs As Long) As String
    Dim pageNo As Long
    For pageNo = 1 To maxPages
        Dim listUrl As String
        If pageNo = 1 Then
            listUrl = baseUrl & routePath
        Else
            If Right$(routePath, 1) <> "/" Then
                listUrl = baseUrl & routePath & "/page-" & CStr(pageNo)
            Else
                listUrl = baseUrl & routePath & "page-" & CStr(pageNo)
            End If
        End If

        Dim filterValue As String
        If clusterCode <> "" Then
            filterValue = clusterCode
        Else
            filterValue = clusterName
        End If
        If filterValue <> "" Then listUrl = AddQueryParam(listUrl, "filter%5Bglobal%5D", UrlEncode(filterValue))

        Dim response As Object
        Set response = HttpRequest("GET", listUrl, "", cookieJar)

        Dim html As String
        html = response.responseText
        If clusterCode <> "" Then
            If InStr(1, html, clusterCode, vbTextCompare) = 0 Then GoTo ContinuePage
        ElseIf clusterName <> "" Then
            If InStr(1, html, clusterName, vbTextCompare) = 0 Then GoTo ContinuePage
        End If

        Dim href As String
        If InStr(1, routePath, "project-opname/cluster", vbTextCompare) > 0 Then
            href = ExtractDetailHref(html, "material/cluster/project-opname/review", clusterCode, clusterName)
        ElseIf InStr(1, routePath, "/subfeeder/", vbTextCompare) > 0 Then
            href = ExtractDetailHref(html, "rfs-document/subfeeder/list/by-invoice-phase/", clusterCode, clusterName)
        Else
            href = ExtractDetailHref(html, "rfs-document/list/by-invoice-phase/", clusterCode, clusterName)
        End If

        If href <> "" Then
            FindDetailUrl = AbsoluteUrl(baseUrl, HtmlDecode(href))
            Exit Function
        End If

ContinuePage:
        SleepMs delayMs
    Next pageNo
    FindDetailUrl = ""
End Function

Private Function ExtractDetailHref(ByVal html As String, ByVal urlPart As String, ByVal clusterCode As String, ByVal clusterName As String) As String
    Dim re As Object, matches As Object
    Set re = CreateObject("VBScript.RegExp")
    re.Global = True
    re.IgnoreCase = True
    re.Pattern = "href=[""']([^""']*" & EscapeRegex(urlPart) & "[^""']*)[""']"
    Set matches = re.Execute(html)
    If matches.Count = 0 Then Exit Function

    Dim i As Long
    If clusterCode <> "" Then
        For i = 0 To matches.Count - 1
            If InStr(1, HtmlDecode(matches(i).SubMatches(0)), "cluster_code=" & clusterCode, vbTextCompare) > 0 Then
                ExtractDetailHref = matches(i).SubMatches(0)
                Exit Function
            End If
        Next i
    End If

    If clusterName <> "" Then
        For i = 0 To matches.Count - 1
            Dim hrefStart As Long, rowStart As Long, rowEnd As Long
            hrefStart = InStr(1, html, matches(i).Value, vbTextCompare)
            rowStart = InStrRev(html, "<tr", hrefStart, vbTextCompare)
            rowEnd = InStr(hrefStart, html, "</tr>", vbTextCompare)
            If rowStart > 0 And rowEnd > rowStart Then
                Dim rowHtml As String
                rowHtml = Mid$(html, rowStart, rowEnd - rowStart + 5)
                If InStr(1, rowHtml, clusterName, vbTextCompare) > 0 Then
                    ExtractDetailHref = matches(i).SubMatches(0)
                    Exit Function
                End If
            End If
        Next i
    End If

    ExtractDetailHref = matches(0).SubMatches(0)
End Function

Private Function AppendDetailDocuments(ByVal ws As Worksheet, ByVal startRow As Long, ByVal baseUrl As String, ByVal cookieJar As String, ByVal detailUrl As String, ByVal routeName As String, ByVal phaseName As String, ByVal scopeName As String) As Long
    Dim response As Object
    Set response = HttpRequest("GET", detailUrl, "", cookieJar)

    Dim rawJson As String
    rawJson = ExtractDocumentListJson(response.responseText)
    If rawJson = "" Then
        ws.Cells(startRow, 1).Resize(1, 17).Value = Array("", routeName, scopeName, phaseName, "", "", "PARSE_FAILED", "", "", "", "", "", "", "", "", "", detailUrl)
        AppendDetailDocuments = startRow + 1
        Exit Function
    End If

    Dim clusterCleanListName As String
    clusterCleanListName = ExtractInfoCardValue(response.responseText, "Name (Clean List)")

    Dim docBlocks As Collection
    Set docBlocks = ExtractDocumentBlocks(rawJson)

    Dim rowNo As Long
    rowNo = startRow

    Dim block As Variant
    For Each block In docBlocks
        Dim typeName As String, labelName As String, fileCount As Long
        typeName = ExtractJsonString(CStr(block), "name")
        labelName = ExtractJsonString(CStr(block), "label")
        fileCount = CountOccurrences(CStr(block), """work_order_number""")

        Dim selectedData As String

        Dim statusName As String, uploadDate As String, verifiedBy As String, verifiedAt As String
        Dim revisionBy As String, revisionAt As String, revisionRemark As String, filename As String

        selectedData = SelectRelevantDataObject(CStr(block), statusName)

        If selectedData = "" Then
            If statusName = "" Then statusName = "NOT UPLOADED"
        Else
            uploadDate = ExtractJsonString(selectedData, "created_at")
            verifiedBy = ExtractJsonString(selectedData, "verified_by_fullname")
            If verifiedBy = "" Then verifiedBy = ExtractJsonString(selectedData, "verified_by_username")
            verifiedAt = ExtractJsonString(selectedData, "verified_at")
            revisionBy = ExtractJsonString(selectedData, "requested_revision_by_fullname")
            revisionAt = ExtractJsonString(selectedData, "requested_revision_at")
            revisionRemark = ExtractJsonString(selectedData, "requested_revision_remarks")
            filename = ExtractJsonString(selectedData, "filename")
        End If

        ws.Cells(rowNo, 1).Resize(1, 17).Value = Array(clusterCleanListName, routeName, scopeName, phaseName, typeName, labelName, statusName, fileCount, uploadDate, verifiedBy, verifiedAt, revisionBy, revisionAt, revisionRemark, filename, Now, detailUrl)
        rowNo = rowNo + 1
    Next block

    AppendDetailDocuments = rowNo
End Function

Private Function AppendNotFound(ByVal ws As Worksheet, ByVal startRow As Long, ByVal routeName As String, ByVal phaseName As String, ByVal scopeName As String, ByVal clusterCode As String, ByVal clusterName As String) As Long
    Dim statusName As String
    If InStr(1, routeName, "PROJECT OPNAME", vbTextCompare) > 0 Then
        statusName = "PROJECT_OPNAME_NOT_FOUND"
    Else
        statusName = "CLUSTER_NOT_FOUND"
    End If

    ws.Cells(startRow, 1).Resize(1, 17).Value = Array("", routeName, scopeName, phaseName, "", "", statusName, "", "", "", "", "", "", "Input: " & clusterCode & " " & clusterName, "", Now, "")
    AppendNotFound = startRow + 1
End Function

Private Function ExtractDocumentListJson(ByVal html As String) As String
    Dim re As Object, matches As Object
    Set re = CreateObject("VBScript.RegExp")
    re.Global = False
    re.IgnoreCase = True
    re.MultiLine = True
    re.Pattern = "let\s+documentListJsonRaw\s*=\s*`([\s\S]*?)`;"
    Set matches = re.Execute(html)
    If matches.Count > 0 Then ExtractDocumentListJson = matches(0).SubMatches(0)
End Function

Private Function ExtractDocumentBlocks(ByVal rawJson As String) As Collection
    Dim result As New Collection
    Dim pos As Long
    pos = 1
    Do
        Dim uuidPos As Long
        uuidPos = InStr(pos, rawJson, """uuid"":", vbTextCompare)
        If uuidPos = 0 Then Exit Do

        Dim keyStart As Long
        keyStart = InStrRev(rawJson, """:{", uuidPos, vbTextCompare)
        If keyStart = 0 Then Exit Do

        Dim objStart As Long
        objStart = keyStart + 2

        Dim objEnd As Long
        objEnd = FindMatchingBrace(rawJson, objStart)
        If objEnd = 0 Then Exit Do

        result.Add Mid$(rawJson, objStart, objEnd - objStart + 1)
        pos = objEnd + 1
    Loop
    Set ExtractDocumentBlocks = result
End Function

Private Function ExtractLastDataObject(ByVal docBlock As String) As String
    Dim dataPos As Long
    dataPos = InStr(1, docBlock, """data"":[", vbTextCompare)
    If dataPos = 0 Then Exit Function

    Dim arrayStart As Long
    arrayStart = InStr(dataPos, docBlock, "[")
    If arrayStart = 0 Then Exit Function
    If Mid$(docBlock, arrayStart + 1, 1) = "]" Then Exit Function

    Dim pos As Long, lastStart As Long, lastEnd As Long
    pos = arrayStart + 1
    Do
        Dim objStart As Long
        objStart = InStr(pos, docBlock, "{")
        If objStart = 0 Then Exit Do
        If InStr(pos, docBlock, "]") > 0 And InStr(pos, docBlock, "]") < objStart Then Exit Do
        Dim objEnd As Long
        objEnd = FindMatchingBrace(docBlock, objStart)
        If objEnd = 0 Then Exit Do
        lastStart = objStart
        lastEnd = objEnd
        pos = objEnd + 1
    Loop

    If lastStart > 0 And lastEnd >= lastStart Then ExtractLastDataObject = Mid$(docBlock, lastStart, lastEnd - lastStart + 1)
End Function

Private Function SelectRelevantDataObject(ByVal docBlock As String, ByRef statusName As String) As String
    Dim dataObjects As Collection
    Set dataObjects = ExtractDataObjects(docBlock)

    If dataObjects.Count = 0 Then
        statusName = "NOT UPLOADED"
        Exit Function
    End If

    Dim i As Long, dataObject As String
    Dim approvedObject As String, revisionObject As String, onReviewObject As String

    For i = 1 To dataObjects.Count
        dataObject = CStr(dataObjects(i))

        Dim verifiedBy As String, verifiedAt As String
        verifiedBy = ExtractJsonString(dataObject, "verified_by_fullname")
        If verifiedBy = "" Then verifiedBy = ExtractJsonString(dataObject, "verified_by_username")
        verifiedAt = ExtractJsonString(dataObject, "verified_at")

        If verifiedBy <> "" Or verifiedAt <> "" Then
            approvedObject = dataObject
        End If

        If ExtractJsonString(dataObject, "requested_revision_at") <> "" Or ExtractJsonString(dataObject, "requested_revision_remarks") <> "" Then
            revisionObject = dataObject
        End If

        onReviewObject = dataObject
    Next i

    If approvedObject <> "" Then
        statusName = "APPROVED"
        SelectRelevantDataObject = approvedObject
    ElseIf revisionObject <> "" Then
        statusName = "REVISION"
        SelectRelevantDataObject = revisionObject
    Else
        statusName = "ON REVIEW"
        SelectRelevantDataObject = onReviewObject
    End If
End Function

Private Function ExtractDataObjects(ByVal docBlock As String) As Collection
    Dim result As New Collection
    Dim dataPos As Long
    dataPos = InStr(1, docBlock, """data"":[", vbTextCompare)
    If dataPos = 0 Then
        Set ExtractDataObjects = result
        Exit Function
    End If

    Dim arrayStart As Long
    arrayStart = InStr(dataPos, docBlock, "[")
    If arrayStart = 0 Then
        Set ExtractDataObjects = result
        Exit Function
    End If

    Dim pos As Long
    pos = arrayStart + 1
    Do
        Dim objStart As Long
        objStart = InStr(pos, docBlock, "{")
        If objStart = 0 Then Exit Do
        If InStr(pos, docBlock, "]") > 0 And InStr(pos, docBlock, "]") < objStart Then Exit Do

        Dim objEnd As Long
        objEnd = FindMatchingBrace(docBlock, objStart)
        If objEnd = 0 Then Exit Do

        result.Add Mid$(docBlock, objStart, objEnd - objStart + 1)
        pos = objEnd + 1
    Loop

    Set ExtractDataObjects = result
End Function

Private Function ExtractInfoCardValue(ByVal html As String, ByVal labelText As String) As String
    Dim re As Object, matches As Object
    Set re = CreateObject("VBScript.RegExp")
    re.Global = False
    re.IgnoreCase = True
    re.MultiLine = True
    re.Pattern = "<small[^>]*>\s*" & EscapeRegex(labelText) & "\s*</small>\s*<p[^>]*(?:title=[""']([^""']*)[""'])?[^>]*>([\s\S]*?)</p>"
    Set matches = re.Execute(html)

    If matches.Count = 0 Then Exit Function

    If matches(0).SubMatches(0) <> "" Then
        ExtractInfoCardValue = HtmlDecode(matches(0).SubMatches(0))
    Else
        ExtractInfoCardValue = StripHtml(matches(0).SubMatches(1))
    End If
End Function

Private Function FindMatchingBrace(ByVal text As String, ByVal startPos As Long) As Long
    Dim depth As Long, i As Long, inString As Boolean, escaped As Boolean, ch As String
    For i = startPos To Len(text)
        ch = Mid$(text, i, 1)
        If inString Then
            If escaped Then
                escaped = False
            ElseIf ch = "\" Then
                escaped = True
            ElseIf ch = """" Then
                inString = False
            End If
        Else
            If ch = """" Then
                inString = True
            ElseIf ch = "{" Then
                depth = depth + 1
            ElseIf ch = "}" Then
                depth = depth - 1
                If depth = 0 Then
                    FindMatchingBrace = i
                    Exit Function
                End If
            End If
        End If
    Next i
End Function

Private Function ExtractJsonString(ByVal jsonText As String, ByVal keyName As String) As String
    Dim re As Object, matches As Object
    Set re = CreateObject("VBScript.RegExp")
    re.Global = False
    re.IgnoreCase = True
    re.Pattern = """" & EscapeRegex(keyName) & """\s*:\s*(null|""((?:\\""|[^""])*)"")"
    Set matches = re.Execute(jsonText)
    If matches.Count = 0 Then Exit Function
    If LCase$(matches(0).SubMatches(0)) = "null" Then Exit Function
    ExtractJsonString = JsonUnescape(matches(0).SubMatches(1))
End Function

Private Function ExtractInputValue(ByVal html As String, ByVal inputName As String) As String
    Dim re As Object, matches As Object
    Set re = CreateObject("VBScript.RegExp")
    re.Global = False
    re.IgnoreCase = True
    re.Pattern = "name=[""']" & EscapeRegex(inputName) & "[""'][^>]*value=[""']([^""']*)[""']|value=[""']([^""']*)[""'][^>]*name=[""']" & EscapeRegex(inputName) & "[""']"
    Set matches = re.Execute(html)
    If matches.Count > 0 Then
        If matches(0).SubMatches(0) <> "" Then
            ExtractInputValue = HtmlDecode(matches(0).SubMatches(0))
        Else
            ExtractInputValue = HtmlDecode(matches(0).SubMatches(1))
        End If
    End If
End Function

Private Function HttpRequest(ByVal methodName As String, ByVal url As String, ByVal body As String, ByVal cookieJar As String) As Object
    Dim http As Object
    Set http = CreateObject("MSXML2.XMLHTTP.6.0")
    http.Open methodName, url, False
    http.setRequestHeader "User-Agent", "Mozilla/5.0"
    http.setRequestHeader "Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8"
    If cookieJar <> "" And cookieJar <> AUTO_COOKIE Then http.setRequestHeader "Cookie", cookieJar
    If methodName = "POST" Then http.setRequestHeader "Content-Type", "application/x-www-form-urlencoded"
    http.Send body
    Set HttpRequest = http
End Function

Private Function CollectCookies(ByVal http As Object) As String
    On Error Resume Next
    Dim rawHeaders As String
    rawHeaders = http.GetAllResponseHeaders
    On Error GoTo 0

    Dim lines() As String
    lines = Split(rawHeaders, vbCrLf)

    Dim jar As String, i As Long
    For i = LBound(lines) To UBound(lines)
        If LCase$(Left$(lines(i), 11)) = "set-cookie:" Then
            Dim cookiePart As String
            cookiePart = Trim$(Mid$(lines(i), 12))
            cookiePart = Split(cookiePart, ";")(0)
            If cookiePart <> "" Then
                If jar <> "" Then jar = jar & "; "
                jar = jar & cookiePart
            End If
        End If
    Next i
    CollectCookies = jar
End Function

Private Function MergeCookies(ByVal oldJar As String, ByVal newJar As String) As String
    If oldJar = "" Then
        MergeCookies = newJar
    ElseIf newJar = "" Then
        MergeCookies = oldJar
    Else
        MergeCookies = oldJar & "; " & newJar
    End If
End Function

Private Function UrlEncode(ByVal text As String) As String
    Dim i As Long, ch As Integer, result As String
    For i = 1 To Len(text)
        ch = AscW(Mid$(text, i, 1))
        Select Case ch
            Case 48 To 57, 65 To 90, 97 To 122, 45, 46, 95, 126
                result = result & ChrW$(ch)
            Case 32
                result = result & "%20"
            Case Else
                result = result & "%" & Right$("0" & Hex$(ch), 2)
        End Select
    Next i
    UrlEncode = result
End Function

Private Function HtmlDecode(ByVal text As String) As String
    HtmlDecode = Replace(Replace(Replace(Replace(Replace(text, "&amp;", "&"), "&quot;", """"), "&#039;", "'"), "&lt;", "<"), "&gt;", ">")
End Function

Private Function StripHtml(ByVal html As String) As String
    Dim re As Object
    Set re = CreateObject("VBScript.RegExp")
    re.Global = True
    re.IgnoreCase = True
    re.Pattern = "<[^>]+>"
    StripHtml = Trim$(HtmlDecode(re.Replace(html, " ")))
End Function

Private Function JsonUnescape(ByVal text As String) As String
    Dim result As String
    result = Replace(text, "\/", "/")
    result = Replace(result, "\""", """")
    result = Replace(result, "\n", vbLf)
    result = Replace(result, "\r", vbCr)
    result = Replace(result, "\t", vbTab)
    result = Replace(result, "\\", "\")
    JsonUnescape = result
End Function

Private Function EscapeRegex(ByVal text As String) As String
    Dim chars As Variant, i As Long
    chars = Array("\", ".", "+", "*", "?", "^", "$", "(", ")", "[", "]", "{", "}", "|")
    EscapeRegex = text
    For i = LBound(chars) To UBound(chars)
        EscapeRegex = Replace(EscapeRegex, CStr(chars(i)), "\" & CStr(chars(i)))
    Next i
End Function

Private Function AbsoluteUrl(ByVal baseUrl As String, ByVal href As String) As String
    href = Replace(href, " ", "%20")
    If LCase$(Left$(href, 4)) = "http" Then
        AbsoluteUrl = href
    ElseIf Left$(href, 1) = "/" Then
        AbsoluteUrl = Left$(baseUrl, Len(baseUrl) - 1) & href
    Else
        AbsoluteUrl = baseUrl & href
    End If
End Function

Private Function CountOccurrences(ByVal text As String, ByVal needle As String) As Long
    Dim pos As Long, count As Long
    pos = InStr(1, text, needle, vbTextCompare)
    Do While pos > 0
        count = count + 1
        pos = InStr(pos + Len(needle), text, needle, vbTextCompare)
    Loop
    CountOccurrences = count
End Function

Private Sub SleepMs(ByVal milliseconds As Long)
    Dim untilTime As Date
    untilTime = DateAdd("s", milliseconds / 1000#, Now)
    Do While Now < untilTime
        DoEvents
    Loop
End Sub

Private Function AddQueryParam(ByVal url As String, ByVal keyName As String, ByVal valueText As String) As String
    If InStr(1, url, "?", vbTextCompare) > 0 Then
        AddQueryParam = url & "&" & keyName & "=" & valueText
    Else
        AddQueryParam = url & "?" & keyName & "=" & valueText
    End If
End Function

Private Sub ClearResult(ByVal ws As Worksheet)
    ws.Range("A10:Q10000").Clear
    ws.Range("A10:Q10").Value = Array("Name (Clean List)", "Route", "Scope", "Phase", "Astri Type", "Astri Label", "Derived Status", "File Count", "Upload Date", "Verified By", "Verified At", "Revision By", "Revision At", "Revision Remark", "Filename", "Scraped At", "Detail URL")
    ws.Rows(10).Font.Bold = True
    ApplyStatusRowFormatting ws
End Sub

Private Sub ApplyStatusRowFormatting(ByVal ws As Worksheet)
    Dim resultRange As Range
    Set resultRange = ws.Range("A11:Q10000")
    resultRange.FormatConditions.Delete

    With resultRange.FormatConditions.Add(Type:=xlExpression, Formula1:="=$G11=""APPROVED""")
        .Interior.Color = RGB(198, 239, 206)
        .Font.Color = RGB(0, 0, 0)
    End With

    With resultRange.FormatConditions.Add(Type:=xlExpression, Formula1:="=$G11=""ON REVIEW""")
        .Interior.Color = RGB(255, 242, 204)
        .Font.Color = RGB(0, 0, 0)
    End With

    With resultRange.FormatConditions.Add(Type:=xlExpression, Formula1:="=$G11=""REVISION""")
        .Interior.Color = RGB(244, 176, 132)
        .Font.Color = RGB(0, 0, 0)
    End With

    With resultRange.FormatConditions.Add(Type:=xlExpression, Formula1:="=$G11=""NOT UPLOADED""")
        .Interior.Color = RGB(255, 255, 255)
        .Font.Color = RGB(0, 0, 0)
    End With

    With resultRange.FormatConditions.Add(Type:=xlExpression, Formula1:="=$G11=""PROJECT_OPNAME_NOT_FOUND""")
        .Interior.Color = RGB(217, 217, 217)
        .Font.Color = RGB(0, 0, 0)
    End With
End Sub

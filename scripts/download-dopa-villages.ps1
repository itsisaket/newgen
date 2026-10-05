param(
    [string]$MetadataPath = "storage/app/village-dataset-metadata.json",
    [string]$OutputDirectory = "database/data/thailand/villages"
)

$ErrorActionPreference = "Stop"
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$metadataFile = (Resolve-Path (Join-Path $projectRoot $MetadataPath)).Path
$outputRoot = Join-Path $projectRoot $OutputDirectory
$temporaryFile = Join-Path $projectRoot "storage/app/dopa-village-download.tmp.json"
$utf8WithoutBom = New-Object System.Text.UTF8Encoding($false)

New-Item -ItemType Directory -Path $outputRoot -Force | Out-Null
$metadata = Get-Content -Raw -LiteralPath $metadataFile | ConvertFrom-Json
$resources = @($metadata.result.resources | Where-Object {
    $_.format -eq "JSON" -and $_.name -notlike "*data_dictionary*"
})

$completed = 0
foreach ($resource in $resources) {
    & curl.exe -L --fail --silent --show-error --max-time 180 $resource.url -o $temporaryFile
    if ($LASTEXITCODE -ne 0) {
        throw "ดาวน์โหลดไม่สำเร็จ: $($resource.name)"
    }

    $rows = Get-Content -Raw -LiteralPath $temporaryFile | ConvertFrom-Json
    if ($rows.Count -eq 0) {
        continue
    }

    $provinceCode = [string]$rows[0].pcode
    if ($provinceCode.Length -gt 2) {
        throw "รหัสจังหวัดผิดรูปแบบ ($provinceCode) จาก $($resource.name)"
    }
    $minimalRows = $rows | Select-Object pcode, pname, acode, aname, tcode, tname, mcode, mname,
        oct_side15_lat, oct_side15_lon
    $destination = Join-Path $outputRoot "$provinceCode.json"
    $json = $minimalRows | ConvertTo-Json -Compress -Depth 3
    [System.IO.File]::WriteAllText($destination, [string]$json, $utf8WithoutBom)

    $completed++
    Write-Output "[$completed/$($resources.Count)] $($resource.name) -> $provinceCode.json ($($rows.Count) รายการ)"
}

if (Test-Path -LiteralPath $temporaryFile) {
    Remove-Item -LiteralPath $temporaryFile -Force
}

Write-Output "สร้างชุดข้อมูลหมู่บ้านแบบย่อแล้ว $completed จังหวัด ที่ $outputRoot"

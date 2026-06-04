Get-ChildItem "public\uploads\products" -File |
ForEach-Object {

    $tempName = "tmp_" + [guid]::NewGuid().ToString() + $_.Extension

    Rename-Item $_.FullName $tempName
}
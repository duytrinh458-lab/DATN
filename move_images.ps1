$target = Resolve-Path "public\uploads\products"

Get-ChildItem "public\uploads\products" -Recurse -File |
Where-Object {
    $_.DirectoryName -ne $target.Path
} |
ForEach-Object {

    $ext = $_.Extension

    $newName = [guid]::NewGuid().ToString() + $ext

    Move-Item $_.FullName "$($target.Path)\$newName"
}
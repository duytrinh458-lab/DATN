$target = (Resolve-Path "public\uploads\products").Path

Get-ChildItem "public\uploads\products" -Recurse -File |
Where-Object { $_.DirectoryName -ne $target } |
ForEach-Object {

    $ext = $_.Extension
    $newName = [guid]::NewGuid().ToString() + $ext

    Move-Item $_.FullName "$target\$newName"
}
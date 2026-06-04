$i = 1

Get-ChildItem "public\uploads\products" -File |
Sort-Object Name |
ForEach-Object {

    $newName = "{0:D4}.jpg" -f $i

    Rename-Item $_.FullName $newName

    $i++
}
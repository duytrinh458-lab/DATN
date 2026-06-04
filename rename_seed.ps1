$i = 1

Get-ChildItem "public\uploads\products" -File |
Where-Object {
    $_.Name -match '^[0-9]{6}\.'
} |
Sort-Object Name |
ForEach-Object {

    $ext = $_.Extension

    $newName = "uav_seed_{0:D3}{1}" -f $i,$ext

    Rename-Item $_.FullName $newName

    $i++
}
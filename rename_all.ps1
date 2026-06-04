$i = 1

Get-ChildItem "public\storage\news" -File |
Sort-Object Name |
ForEach-Object {

    $ext = $_.Extension.ToLower()

    $newName = "news{0:D2}{1}" -f $i,$ext

    Rename-Item $_.FullName $newName

    $i++
}
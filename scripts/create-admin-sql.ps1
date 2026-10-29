$ErrorActionPreference = 'Stop'
$nakamaName = Read-Host 'Nombre del administrador'
$nakamaEmail = Read-Host 'Correo de acceso'
$nakamaSecurePassword = Read-Host 'Contraseña (mínimo 12 caracteres, letras y números)' -AsSecureString
$nakamaSecureConfirmation = Read-Host 'Repetí la contraseña' -AsSecureString
try {
    $nakamaPassword = [System.Net.NetworkCredential]::new('', $nakamaSecurePassword).Password
    $nakamaConfirmation = [System.Net.NetworkCredential]::new('', $nakamaSecureConfirmation).Password
    if ($nakamaPassword -cne $nakamaConfirmation) { throw 'Las contraseñas no coinciden.' }
    $nakamaPayload = @{ name = $nakamaName; email = $nakamaEmail; password = $nakamaPassword } | ConvertTo-Json -Compress
    $nakamaPayload | php (Join-Path $PSScriptRoot 'admin-sql.php')
    if ($LASTEXITCODE -ne 0) { throw 'No se generó el archivo.' }
} finally {
    $nakamaPassword = $null
    $nakamaConfirmation = $null
    $nakamaPayload = $null
    $nakamaSecurePassword.Dispose()
    $nakamaSecureConfirmation.Dispose()
}

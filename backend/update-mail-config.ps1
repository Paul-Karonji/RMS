$envPath = ".env"
$content = Get-Content $envPath

# Update mail settings
$content = $content -replace '^MAIL_MAILER=.*', 'MAIL_MAILER=smtp'
$content = $content -replace '^MAIL_HOST=.*', 'MAIL_HOST=sandbox.smtp.mailtrap.io'
$content = $content -replace '^MAIL_PORT=.*', 'MAIL_PORT=2525'
$content = $content -replace '^MAIL_USERNAME=.*', 'MAIL_USERNAME=79bfb2afca64ce'
$content = $content -replace '^MAIL_PASSWORD=.*', 'MAIL_PASSWORD=d0bc03d1c6c1e0'
$content = $content -replace '^MAIL_FROM_ADDRESS=.*', 'MAIL_FROM_ADDRESS=noreply@rms.test'
$content = $content -replace '^MAIL_FROM_NAME=.*', 'MAIL_FROM_NAME="${APP_NAME}"'

# Save the file
$content | Set-Content $envPath -Encoding UTF8

Write-Host "Mail configuration updated successfully!"

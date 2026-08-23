# ============================================================
#  KABA - Build de production (Windows PowerShell)
#  Usage :  .\build-prod.ps1
#  Compile les assets front-end et retire le fichier "hot".
# ============================================================

$ErrorActionPreference = "Stop"
Set-Location -Path $PSScriptRoot

Write-Host "==> Installation des dependances front-end..." -ForegroundColor Cyan
npm install

Write-Host "==> Compilation des assets (production)..." -ForegroundColor Cyan
npm run build

Write-Host "==> Suppression du fichier 'hot' (residu du dev Vite)..." -ForegroundColor Cyan
if (Test-Path "public\hot") {
    Remove-Item "public\hot" -Force
    Write-Host "    public\hot supprime." -ForegroundColor Green
} else {
    Write-Host "    (aucun fichier 'hot' - OK)" -ForegroundColor DarkGray
}

Write-Host ""
Write-Host "Build termine. Le dossier public\build est pret a etre deploye." -ForegroundColor Green
Write-Host "Voir DEPLOIEMENT.md pour la suite." -ForegroundColor Green

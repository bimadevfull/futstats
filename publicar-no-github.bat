@echo off
REM Envia o FutStats para https://github.com/bimadevfull/futstats
cd /d "%~dp0"
if not exist .git git init
git add .
git commit -m "FutStats: sistema de gerenciamento de times de futebol em PHP"
git branch -M main
git remote remove origin 2>nul
git remote add origin https://github.com/bimadevfull/futstats.git
git push -u origin main
echo.
echo Pronto! Abra https://github.com/bimadevfull/futstats
pause

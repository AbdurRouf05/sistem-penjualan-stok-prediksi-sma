@echo off
REM Menghapus semua data lalu mengisi ulang data demo toko
cd /d "%~dp0"
set PHP=D:\setup\php\php.exe
if not exist "%PHP%" set PHP=php
echo PERINGATAN: semua data akan dihapus dan diganti data demo.
pause
"%PHP%" artisan migrate:fresh --seed --force
"%PHP%" artisan optimize:clear
echo Selesai.
pause

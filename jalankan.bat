@echo off
REM Menjalankan aplikasi di http://127.0.0.1:8000
cd /d "%~dp0"
set PHP=D:\setup\php\php.exe
if not exist "%PHP%" set PHP=php
echo Membuka http://127.0.0.1:8000 ... (tutup jendela ini untuk menghentikan server)
start "" http://127.0.0.1:8000
"%PHP%" artisan serve --host=127.0.0.1 --port=8000

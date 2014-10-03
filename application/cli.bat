@echo off
set APPLICATION_ENV=production
set APPLICATION_CACHE_BACKEND=file
PUSHD %~dp0
php cli.php %*
POPD
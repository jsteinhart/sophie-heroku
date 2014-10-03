@echo off
set APPLICATION_ENV=development
set APPLICATION_CACHE_BACKEND=noop
PUSHD %~dp0
php cli.php %*
POPD
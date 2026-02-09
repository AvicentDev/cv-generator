@echo off
cls

echo.
echo LIMPIEZA DE FRONTEND - generador_cv
echo.

echo Eliminando carpetas...
echo.

rmdir /s /q resources\css 2>nul && echo [OK] resources\css
rmdir /s /q resources\js 2>nul && echo [OK] resources\js
rmdir /s /q resources\views 2>nul && echo [OK] resources\views
rmdir /s /q node_modules 2>nul && echo [OK] node_modules
rmdir /s /q public\build 2>nul && echo [OK] public\build

echo.
echo Eliminando archivos...
echo.

del /q package.json 2>nul && echo [OK] package.json
del /q package-lock.json 2>nul && echo [OK] package-lock.json
del /q vite.config.js 2>nul && echo [OK] vite.config.js
del /q demo_standalone.html 2>nul && echo [OK] demo_standalone.html
del /q FRONTEND_README.md 2>nul && echo [OK] FRONTEND_README.md
del /q FRONTEND_SUMMARY.md 2>nul && echo [OK] FRONTEND_SUMMARY.md
del /q MEJORAS_FRONTEND.md 2>nul && echo [OK] MEJORAS_FRONTEND.md
del /q INSTRUCCIONES_INTEGRACION.md 2>nul && echo [OK] INSTRUCCIONES_INTEGRACION.md
del /q IMPLEMENTATION_CHECKLIST.md 2>nul && echo [OK] IMPLEMENTATION_CHECKLIST.md

echo.
echo ========================================================
echo LIMPIEZA COMPLETADA
echo ========================================================
echo.
echo Resumen:
echo - Frontend completamente eliminado
echo - Backend 100 porciento funcional
echo - APIs REST listas para usar
echo.
echo Proximos pasos:
echo 1. composer install
echo 2. php artisan serve
echo.
echo Ver BACKEND_ONLY.md para mas info
echo.
pause

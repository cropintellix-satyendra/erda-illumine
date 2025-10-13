@echo off
echo ================================================
echo KML Reader Setup Script
echo ================================================
echo.

echo [1/3] Creating KML storage folder...
if not exist "storage\app\public\kml" (
    mkdir storage\app\public\kml
    echo KML folder created successfully!
) else (
    echo KML folder already exists!
)
echo.

echo [2/3] Creating storage symlink...
php artisan storage:link
echo.

echo [3/3] Setting up permissions...
echo Note: On Windows, no additional permissions needed
echo.

echo ================================================
echo Setup Complete!
echo ================================================
echo.
echo You can now access:
echo - KML Viewer: http://localhost/admin/kml/viewer
echo - Upload KML: http://localhost/admin/kml/upload
echo - KML List: http://localhost/admin/kml/list
echo.
echo For more details, check KML_READER_SETUP.md
echo.
pause


# KML Reader Setup Guide

## विशेषताएं (Features)

KML Reader module आपको निम्नलिखित सुविधाएं प्रदान करता है:

1. **KML File Upload** - KML files को आसानी से upload करें
2. **Interactive Map Viewer** - Google Maps पर KML data को visualize करें
3. **File Management** - uploaded files को manage करें (view, download, delete)
4. **Drag & Drop Support** - आसान file upload के लिए drag & drop
5. **Responsive Design** - सभी devices पर काम करता है

## Installation Steps

### 1. Storage Setup

पहले KML files के लिए storage folder create करें:

```bash
# Windows PowerShell में
mkdir storage\app\public\kml

# Linux/Mac में
mkdir -p storage/app/public/kml
```

### 2. Storage Link

Public storage link बनाएं:

```bash
php artisan storage:link
```

यह command `public/storage` से `storage/app/public` को link कर देगा।

### 3. Permissions (Linux/Mac only)

Storage folder को writable बनाएं:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 4. Routes Verification

Routes already added हैं `routes/web.php` में:

- `/admin/kml/viewer` - KML Viewer
- `/admin/kml/upload` - Upload Page
- `/admin/kml/list` - Files List
- `/admin/kml/store` - Store uploaded file
- `/admin/kml/delete/{filename}` - Delete file
- `/admin/kml/content/{filename}` - Get KML content

### 5. Google Maps API Key

KML Viewer में Google Maps API key already configured है। अगर आपको अपनी key use करनी है, तो `resources/views/admin/kml/viewer.blade.php` में update करें:

```javascript
// Line में Google Maps API URL
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
```

## Usage Instructions

### 1. Access Menu

Sidebar में "KML Reader" menu Settings के नीचे मिलेगा (केवल SuperAdmin के लिए)।

### 2. Upload KML File

**Method 1: Upload Page से**
1. Menu से "Upload KML" पर click करें
2. File browse करें या drag & drop करें
3. Upload button पर click करें

**Method 2: Viewer से Direct**
1. Menu से "KML Viewer" पर click करें
2. Left panel में "Quick Upload" section में file drop करें

### 3. View KML on Map

1. "KML Viewer" page खोलें
2. Left sidebar में uploaded KML files की list दिखेगी
3. किसी file पर click करें
4. Google Map में KML data automatically load हो जाएगा

### 4. File Management

"KML Files" page से आप:
- सभी files की list देख सकते हैं
- Files को download कर सकते हैं
- Files को delete कर सकते हैं
- Map पर view कर सकते हैं

## File Structure

```
app/
└── Http/
    └── Controllers/
        └── Admin/
            └── KmlController.php        # Main controller

resources/
└── views/
    └── admin/
        └── kml/
            ├── viewer.blade.php         # Map viewer
            ├── upload.blade.php         # Upload page
            └── list.blade.php           # Files list

storage/
└── app/
    └── public/
        └── kml/                         # KML files storage

routes/
└── web.php                              # Routes defined here
```

## Troubleshooting

### Issue 1: Files not uploading
**Solution:** 
- Check storage folder permissions
- Verify `storage/app/public/kml` folder exists
- Run `php artisan storage:link`

### Issue 2: KML not loading on map
**Possible Causes:**
- File URL must be publicly accessible
- Google Maps API might need the KML file to be hosted on a public URL
- Check browser console for errors

**Solution:**
- Ensure storage link is created
- File should be accessible via `/storage/kml/filename.kml`

### Issue 3: Upload size limit
**Solution:**
- Update `php.ini`:
  ```
  upload_max_filesize = 10M
  post_max_size = 10M
  ```
- Restart Apache/Nginx

### Issue 4: Menu not showing
**Solution:**
- Verify you're logged in as SuperAdmin
- Clear cache: `php artisan cache:clear`
- Check `resources/views/elements/sidebar.blade.php`

## Configuration

### Change Upload Size Limit

In `app/Http/Controllers/Admin/KmlController.php`:

```php
$request->validate([
    'kml_file' => 'required|file|mimes:kml,xml|max:10240', // Change 10240 (KB)
]);
```

### Change Storage Location

Default: `storage/app/public/kml`

To change, update `KmlController.php`:

```php
$path = $file->storeAs('your-folder', $filename, 'public');
```

## Google Maps API Notes

- Current API key में restriction हो सकती है
- Production में अपनी API key use करें
- API key को environment variable में store करें
- Maps JavaScript API enable होनी चाहिए

## Security Notes

1. **File Validation:** केवल `.kml` extension वाली files accept होती हैं
2. **File Size:** Maximum 10MB limit
3. **Authentication:** Routes `auth` middleware से protected हैं
4. **Authorization:** केवल SuperAdmin access कर सकता है

## Features Roadmap

Future में add किए जा सकते हैं:

- [ ] KML file editing capability
- [ ] Multiple files को एक साथ display करना
- [ ] KML to GeoJSON conversion
- [ ] Export functionality
- [ ] File versioning
- [ ] Sharing functionality
- [ ] Categories/Tags for files

## Support

किसी भी issue के लिए, check करें:
1. Laravel logs: `storage/logs/laravel.log`
2. Browser console for JavaScript errors
3. Network tab for API requests

## Credits

- Google Maps JavaScript API
- Laravel Framework
- Bootstrap for UI


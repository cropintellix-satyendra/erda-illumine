# Google Maps API Key Setup Guide

## 🗺️ Google Maps API Key की जरूरत क्यों है?

KML Viewer में Google Maps को use करने के लिए एक valid API key चाहिए।

## 📝 Step-by-Step Setup

### Step 1: Google Cloud Console में जाएं

1. Visit: https://console.cloud.google.com/
2. अपने Google account से login करें

### Step 2: New Project बनाएं (या existing use करें)

1. Top bar में Project selector पर click करें
2. "New Project" पर click करें
3. Project name दें (जैसे: "KML Viewer")
4. "Create" पर click करें

### Step 3: Maps JavaScript API Enable करें

1. Left sidebar में "APIs & Services" > "Library" पर जाएं
2. Search bar में "Maps JavaScript API" search करें
3. "Maps JavaScript API" पर click करें
4. "Enable" button पर click करें

### Step 4: API Key बनाएं

1. Left sidebar में "APIs & Services" > "Credentials" पर जाएं
2. Top में "+ CREATE CREDENTIALS" पर click करें
3. "API Key" select करें
4. आपकी API key बन जाएगी - इसे copy कर लें

### Step 5: API Key को Restrict करें (Optional but Recommended)

**Application Restrictions:**
1. "HTTP referrers (web sites)" select करें
2. Add referrers:
   ```
   http://localhost/*
   http://ei.test/*
   http://your-domain.com/*
   ```

**API Restrictions:**
1. "Restrict key" select करें
2. Select APIs: "Maps JavaScript API"
3. "Save" पर click करें

### Step 6: .env File में Add करें

अपनी `.env` file खोलें और नीचे add करें:

```env
# Google Maps API Key
GOOGLE_MAPS_API_KEY=your_api_key_here
```

**Example:**
```env
GOOGLE_MAPS_API_KEY=AIzaSyD1234567890abcdefghijklmnopqrstuv
```

### Step 7: Cache Clear करें

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 8: Test करें

1. Browser में KML Viewer खोलें
2. Console में कोई error नहीं होनी चाहिए
3. Map properly load होना चाहिए

## 🆓 Free Tier Details

Google Maps Platform free tier में:
- **$200 free credit हर महीने**
- Maps JavaScript API: **28,500 map loads free/month**
- Static Maps API: **28,000 map loads free/month**

ज्यादातर small projects के लिए यह काफी है।

## ⚠️ Important Notes

1. **Billing Account**: API key use करने के लिए billing account setup करना पड़ता है (लेकिन free tier में charge नहीं होता)

2. **API Key Security**: 
   - API key को public repositories में commit न करें
   - `.env` file को `.gitignore` में रखें
   - Production में API restrictions जरूर use करें

3. **Rate Limits**: Free tier के limits cross करने पर charges apply होंगे

## 🔧 Alternative: Temporary Testing (Development Only)

अगर आप सिर्फ test करना चाहते हैं, तो आप temporarily KML viewer को बिना API key के test कर सकते हैं:

**Option 1: Static KML Display**
- Google Maps की जगह OpenStreetMap/Leaflet.js use करें
- Free और unlimited

**Option 2: Mock Data**
- Development के लिए demo KML data use करें

## 🐛 Troubleshooting

### Error: "InvalidKeyMapError"
**Solution:** API key invalid या expired है। New key बनाएं।

### Error: "RefererNotAllowedMapError"
**Solution:** API key restrictions में current domain add करें।

### Error: "This API project is not authorized to use this API"
**Solution:** Maps JavaScript API enable करें project में।

### Map loading but KML not showing
**Possible Issues:**
1. KML file का URL publicly accessible नहीं है
2. KML file में errors हैं
3. Storage link properly configured नहीं है

## 📞 Support

Google Maps Platform Support:
- Documentation: https://developers.google.com/maps/documentation
- Support: https://developers.google.com/maps/support

## 💡 Quick Reference

**Console URL:** https://console.cloud.google.com/

**APIs to Enable:**
- Maps JavaScript API (Required)
- Maps Static API (Optional - for static map images)
- Geocoding API (Optional - for address lookup)

**Important URLs:**
- Pricing: https://cloud.google.com/maps-platform/pricing
- API Key Best Practices: https://developers.google.com/maps/api-key-best-practices


# Erda Illumine - Agricultural Management System

## 📋 Project Overview

Erda Illumine is a comprehensive agricultural management system built with Laravel 8/9, designed for managing farmer data, polygon mapping, validation workflows, and agricultural operations. The system provides role-based access control with L1 and L2 validator workflows.

## 🚀 Key Features

### **Core Functionality**
- **Farmer Management**: Complete farmer onboarding and data management
- **Polygon Mapping**: Interactive polygon visualization with Google Maps/Leaflet.js
- **Validation Workflows**: L1 and L2 validator approval processes
- **File Management**: Image uploads, downloads, and S3 integration
- **Reporting**: Excel exports and comprehensive reporting
- **KML Processing**: KML file upload, analysis, and comparison

### **Recent Updates (January 2025)**
- ✅ **Move to Pending Functionality**: L2 validators can move approved polygons back to pending
- ✅ **Enhanced Pagination**: Server-side pagination for better performance
- ✅ **Route Parameter Fixes**: Fixed parameter handling issues
- ✅ **Status Check Logic**: Enhanced status validation for polygon operations
- ✅ **Comprehensive Logging**: Detailed logging for debugging and monitoring

## 🏗️ Technical Stack

### **Backend**
- **Framework**: Laravel 8/9
- **Database**: MySQL/MariaDB
- **Authentication**: Laravel Sanctum
- **File Storage**: AWS S3
- **Caching**: Redis/Memcached

### **Frontend**
- **Maps**: Google Maps API / Leaflet.js
- **UI Framework**: Bootstrap 4
- **JavaScript**: Vue.js 2, jQuery
- **Charts**: ECharts, Chart.js
- **Data Tables**: DataTables with server-side processing

### **Key Dependencies**
- `laravel/sanctum` - API authentication
- `maatwebsite/excel` - Excel import/export
- `yajra/laravel-datatables-oracle` - DataTables integration
- `aws/aws-sdk-php` - AWS S3 integration
- `predis/predis` - Redis client
- `intervention/image` - Image processing

## 🎯 User Roles & Permissions

### **L1 Validator**
- First-level validation of farmer data
- Polygon validation and approval
- Pipe installation validation
- Aeration validation
- Crop data validation
- Benefit validation

### **L2 Validator**
- Second-level validation and final approval
- Move approved polygons back to pending
- Comprehensive polygon management
- Advanced reporting and analytics
- System administration

### **Admin**
- Full system access
- User management
- System configuration
- KML file management
- API logs monitoring

### **Viewer**
- Read-only access to all data
- Report viewing
- Data export capabilities

## 🗺️ Polygon Management

### **Polygon Operations**
- **Create**: Draw polygons on interactive maps
- **Edit**: Modify existing polygon boundaries
- **Validate**: L1 and L2 validation workflows
- **Move to Pending**: Move approved polygons back to pending status
- **Export**: GeoJSON and KML export functionality

### **Map Integration**
- **Google Maps**: Primary mapping solution
- **Leaflet.js**: Alternative mapping solution
- **Interactive Features**: Zoom, pan, polygon drawing
- **Info Windows**: Detailed polygon information display

## 📊 Validation Workflows

### **L1 Validation Process**
1. **Data Submission**: Farmers submit data and images
2. **L1 Review**: L1 validators review and validate
3. **Approval/Rejection**: L1 validators approve or reject
4. **L2 Forward**: Approved items move to L2 queue

### **L2 Validation Process**
1. **L2 Review**: L2 validators perform final review
2. **Final Approval**: L2 validators give final approval
3. **Move to Pending**: Option to move approved items back to pending
4. **Status Updates**: Real-time status updates

## 🔧 API Endpoints

### **Authentication**
- `POST /api/login` - User authentication
- `POST /api/logout` - User logout
- `GET /api/user` - Get current user

### **Polygon Management**
- `GET /api/polygons` - Get all polygons
- `POST /api/polygons` - Create new polygon
- `PUT /api/polygons/{id}` - Update polygon
- `DELETE /api/polygons/{id}` - Delete polygon
- `POST /api/polygons/{id}/move-to-pending` - Move to pending

### **File Management**
- `POST /api/upload` - Upload files
- `GET /api/download/{id}` - Download files
- `DELETE /api/files/{id}` - Delete files

## 🚀 Installation & Setup

### **Prerequisites**
- PHP 7.4+ or PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Node.js & NPM
- XAMPP/WAMP (for local development)

### **Installation Steps**

1. **Clone Repository**
```bash
git clone <repository-url>
cd erda-illumine
```

2. **Install Dependencies**
```bash
composer install
npm install
```

3. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database Configuration**
```bash
# Update .env file with database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erda_illumine
DB_USERNAME=root
DB_PASSWORD=
```

5. **Database Migration**
```bash
php artisan migrate
php artisan db:seed
```

6. **Storage Setup**
```bash
php artisan storage:link
```

7. **Start Development Server**
```bash
php artisan serve
```

## 🔧 Configuration

### **Environment Variables**
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erda_illumine
DB_USERNAME=root
DB_PASSWORD=

# AWS S3
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket_name

# Google Maps
GOOGLE_MAPS_API_KEY=your_api_key

# Cache
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### **File Permissions**
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

## 📁 Project Structure

```
erda-illumine/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/
│   │   │   ├── Account/
│   │   │   │   ├── l1validator/
│   │   │   │   └── l2validator/
│   │   │   └── KmlController.php
│   │   └── Api/V1/
│   ├── Models/
│   └── Exports/
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── l1validator/
│       │   └── l2validator/
│       └── layout/
├── routes/
│   ├── web.php
│   └── api.php
├── public/
│   ├── css/
│   ├── js/
│   └── images/
└── storage/
    ├── app/
    └── logs/
```

## 🐛 Troubleshooting

### **Common Issues**

#### **1. Route Parameter Issues**
- **Problem**: Parameters not passed correctly
- **Solution**: Check method signatures match route parameters
- **Reference**: `ROUTE_FIX_SUMMARY.md`

#### **2. Status Check Failures**
- **Problem**: Polygon status validation fails
- **Solution**: Enhanced status check logic implemented
- **Reference**: `STATUS_CHECK_FIX_ANALYSIS.md`

#### **3. Database Connection Issues**
- **Problem**: Database connection fails
- **Solution**: Check database credentials and connection
- **Reference**: Check `storage/logs/laravel.log`

### **Debug Tools**
- **Debug Routes**: `/admin/view/debug/plot/{plotid}`
- **Logs**: `storage/logs/laravel.log`
- **API Logs**: Admin panel → API Logs

## 📚 Documentation

### **Available Documentation**
- `WEB_ROUTES_DOCUMENTATION.md` - Complete web routes documentation
- `MOVE_TO_PENDING_IMPLEMENTATION.md` - Move to pending functionality
- `PAGINATION_IMPLEMENTATION_GUIDE.md` - Pagination implementation
- `STATUS_CHECK_FIX_ANALYSIS.md` - Status validation fixes
- `LOG_ANALYSIS_AND_FIX.md` - Logging and debugging guide

### **API Documentation**
- API endpoints documented in `API_DOCUMENTATION.md`
- Postman collection available
- Swagger documentation (if implemented)

## 🤝 Contributing

### **Development Guidelines**
1. Follow PSR-12 coding standards
2. Write comprehensive tests
3. Update documentation
4. Use meaningful commit messages
5. Test thoroughly before submitting

### **Code Review Process**
1. Create feature branch
2. Implement changes
3. Write tests
4. Update documentation
5. Submit pull request

## 📞 Support

### **Technical Support**
- **Documentation**: Check project documentation files
- **Logs**: Review `storage/logs/laravel.log`
- **Issues**: Create GitHub issue with detailed description

### **Contact Information**
- **Project Lead**: [Contact Information]
- **Technical Lead**: [Contact Information]
- **Documentation**: [Contact Information]

## 📄 License

This project is proprietary software. All rights reserved.

## 🔄 Version History

### **Version 2.0 (January 2025)**
- ✅ Move to Pending functionality
- ✅ Enhanced pagination
- ✅ Route parameter fixes
- ✅ Status check improvements
- ✅ Comprehensive logging

### **Version 1.0 (Initial Release)**
- ✅ Basic polygon management
- ✅ L1/L2 validation workflows
- ✅ File management system
- ✅ Reporting capabilities

---

**Last Updated**: January 10, 2025  
**Version**: 2.0  
**Status**: Active Development
# Erda Illumine - API Documentation

## 📋 Overview

This document provides comprehensive API documentation for the Erda Illumine Agricultural Management System. The API is built with Laravel 8/9 and uses Laravel Sanctum for authentication.

## 🔐 Authentication

### **Base URL**
```
http://ei.test/api
```

### **Authentication Method**
- **Type**: Bearer Token (Laravel Sanctum)
- **Header**: `Authorization: Bearer {token}`
- **Content-Type**: `application/json`

## 🚀 Core Endpoints

### **Authentication Endpoints**

#### **1. User Login**
```http
POST /api/login
```

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "roles": ["L-2-Validator"]
        },
        "token": "1|abcdef123456..."
    }
}
```

#### **2. User Logout**
```http
POST /api/logout
```

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Logout successful"
}
```

#### **3. Get Current User**
```http
GET /api/user
```

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "roles": ["L-2-Validator"],
        "permissions": ["polygon.view", "polygon.edit"]
    }
}
```

## 🗺️ Polygon Management APIs

### **1. Get All Polygons**
```http
GET /api/polygons
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Number of items per page (default: 50)
- `status` (optional): Filter by status (Approved, Pending, Rejected)
- `search` (optional): Search by plot ID or farmer name

**Response:**
```json
{
    "success": true,
    "data": {
        "polygons": [
            {
                "id": 1,
                "farmer_plot_uniqueid": "891263912P1",
                "farmer_name": "John Doe",
                "farmer_uniqueId": "F123456",
                "final_status": "Approved",
                "l2_status": "Approved",
                "plot_area": 2.5,
                "ranges": [
                    {"lat": 20.5937, "lng": 78.9629},
                    {"lat": 20.5947, "lng": 78.9639}
                ],
                "created_at": "2025-01-10T10:00:00Z",
                "updated_at": "2025-01-10T12:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 10,
            "per_page": 50,
            "total": 500,
            "from": 1,
            "to": 50
        }
    }
}
```

### **2. Get Polygon Details**
```http
GET /api/polygons/{id}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "farmer_plot_uniqueid": "891263912P1",
        "farmer_name": "John Doe",
        "farmer_uniqueId": "F123456",
        "final_status": "Approved",
        "l2_status": "Approved",
        "plot_area": 2.5,
        "ranges": [
            {"lat": 20.5937, "lng": 78.9629},
            {"lat": 20.5947, "lng": 78.9639}
        ],
        "farmer_details": {
            "mobile": "9876543210",
            "state": "Maharashtra",
            "district": "Pune",
            "taluka": "Pune",
            "village": "Pune"
        },
        "pipe_installations": [...],
        "crop_data": [...],
        "benefits": [...]
    }
}
```

### **3. Create Polygon**
```http
POST /api/polygons
```

**Request Body:**
```json
{
    "farmer_plot_uniqueid": "891263912P1",
    "farmer_name": "John Doe",
    "farmer_uniqueId": "F123456",
    "plot_area": 2.5,
    "ranges": [
        {"lat": 20.5937, "lng": 78.9629},
        {"lat": 20.5947, "lng": 78.9639}
    ]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Polygon created successfully",
    "data": {
        "id": 1,
        "farmer_plot_uniqueid": "891263912P1",
        "created_at": "2025-01-10T10:00:00Z"
    }
}
```

### **4. Update Polygon**
```http
PUT /api/polygons/{id}
```

**Request Body:**
```json
{
    "plot_area": 3.0,
    "ranges": [
        {"lat": 20.5937, "lng": 78.9629},
        {"lat": 20.5947, "lng": 78.9639},
        {"lat": 20.5957, "lng": 78.9649}
    ]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Polygon updated successfully",
    "data": {
        "id": 1,
        "updated_at": "2025-01-10T12:00:00Z"
    }
}
```

### **5. Move Polygon to Pending** ⭐ **NEW**
```http
POST /api/polygons/{id}/move-to-pending
```

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Polygon successfully moved to Pending status",
    "data": {
        "id": 1,
        "previous_status": "Approved",
        "new_status": "Pending",
        "updated_at": "2025-01-10T12:00:00Z"
    }
}
```

### **6. Delete Polygon**
```http
DELETE /api/polygons/{id}
```

**Response:**
```json
{
    "success": true,
    "message": "Polygon deleted successfully"
}
```

## 📊 Validation APIs

### **1. L1 Validation**
```http
POST /api/validation/l1/{polygon_id}
```

**Request Body:**
```json
{
    "status": "Approved",
    "comments": "Validation comments",
    "validation_data": {
        "area_verified": true,
        "boundary_verified": true,
        "farmer_verified": true
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "L1 validation completed",
    "data": {
        "polygon_id": 1,
        "l1_status": "Approved",
        "l1_validator_id": 2,
        "validated_at": "2025-01-10T12:00:00Z"
    }
}
```

### **2. L2 Validation**
```http
POST /api/validation/l2/{polygon_id}
```

**Request Body:**
```json
{
    "status": "Approved",
    "comments": "L2 validation comments",
    "final_approval": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "L2 validation completed",
    "data": {
        "polygon_id": 1,
        "l2_status": "Approved",
        "final_status": "Approved",
        "l2_validator_id": 3,
        "validated_at": "2025-01-10T12:00:00Z"
    }
}
```

## 📁 File Management APIs

### **1. Upload File**
```http
POST /api/upload
```

**Request Body:**
```
Content-Type: multipart/form-data

file: [binary data]
type: "polygon_image" | "farmer_document" | "crop_image"
polygon_id: 1
```

**Response:**
```json
{
    "success": true,
    "message": "File uploaded successfully",
    "data": {
        "file_id": 1,
        "filename": "polygon_image_123.jpg",
        "url": "https://s3.amazonaws.com/bucket/polygon_image_123.jpg",
        "size": 1024000,
        "type": "image/jpeg"
    }
}
```

### **2. Download File**
```http
GET /api/download/{file_id}
```

**Response:**
```
Content-Type: application/octet-stream
Content-Disposition: attachment; filename="file.jpg"

[binary data]
```

### **3. Delete File**
```http
DELETE /api/files/{file_id}
```

**Response:**
```json
{
    "success": true,
    "message": "File deleted successfully"
}
```

## 📈 Reporting APIs

### **1. Generate Report**
```http
POST /api/reports/generate
```

**Request Body:**
```json
{
    "report_type": "polygon_summary",
    "date_from": "2025-01-01",
    "date_to": "2025-01-31",
    "filters": {
        "status": "Approved",
        "state": "Maharashtra"
    },
    "format": "excel"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Report generated successfully",
    "data": {
        "report_id": 1,
        "download_url": "/api/reports/download/1",
        "expires_at": "2025-01-11T12:00:00Z"
    }
}
```

### **2. Download Report**
```http
GET /api/reports/download/{report_id}
```

**Response:**
```
Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
Content-Disposition: attachment; filename="report.xlsx"

[binary data]
```

## 🗺️ Map APIs

### **1. Get Map Data**
```http
GET /api/map/polygons
```

**Query Parameters:**
- `bounds` (optional): Map bounds (north,south,east,west)
- `zoom` (optional): Map zoom level
- `status` (optional): Filter by status

**Response:**
```json
{
    "success": true,
    "data": {
        "polygons": [
            {
                "id": 1,
                "farmer_plot_uniqueid": "891263912P1",
                "ranges": [
                    {"lat": 20.5937, "lng": 78.9629},
                    {"lat": 20.5947, "lng": 78.9639}
                ],
                "status": "Approved",
                "area": 2.5
            }
        ],
        "bounds": {
            "north": 20.6,
            "south": 20.5,
            "east": 79.0,
            "west": 78.9
        }
    }
}
```

### **2. Generate GeoJSON**
```http
GET /api/map/geojson
```

**Query Parameters:**
- `polygon_ids` (optional): Comma-separated polygon IDs
- `status` (optional): Filter by status

**Response:**
```json
{
    "success": true,
    "data": {
        "type": "FeatureCollection",
        "features": [
            {
                "type": "Feature",
                "properties": {
                    "id": 1,
                    "farmer_plot_uniqueid": "891263912P1",
                    "status": "Approved",
                    "area": 2.5
                },
                "geometry": {
                    "type": "Polygon",
                    "coordinates": [[
                        [78.9629, 20.5937],
                        [78.9639, 20.5947],
                        [78.9629, 20.5937]
                    ]]
                }
            }
        ]
    }
}
```

## 🔧 System APIs

### **1. Get System Status**
```http
GET /api/system/status
```

**Response:**
```json
{
    "success": true,
    "data": {
        "status": "operational",
        "version": "2.0",
        "database": "connected",
        "cache": "connected",
        "storage": "connected",
        "uptime": "7 days, 12 hours"
    }
}
```

### **2. Get API Logs**
```http
GET /api/system/logs
```

**Query Parameters:**
- `page` (optional): Page number
- `per_page` (optional): Items per page
- `level` (optional): Log level (info, warning, error)
- `date_from` (optional): Start date
- `date_to` (optional): End date

**Response:**
```json
{
    "success": true,
    "data": {
        "logs": [
            {
                "id": 1,
                "level": "info",
                "message": "Polygon created successfully",
                "context": {
                    "polygon_id": 1,
                    "user_id": 2
                },
                "created_at": "2025-01-10T12:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 10,
            "per_page": 50,
            "total": 500
        }
    }
}
```

## 📊 Statistics APIs

### **1. Get Dashboard Statistics**
```http
GET /api/statistics/dashboard
```

**Response:**
```json
{
    "success": true,
    "data": {
        "total_polygons": 1000,
        "approved_polygons": 800,
        "pending_polygons": 150,
        "rejected_polygons": 50,
        "total_farmers": 500,
        "total_area": 2500.5,
        "recent_activity": [
            {
                "type": "polygon_approved",
                "description": "Polygon 891263912P1 approved by L2 Validator",
                "timestamp": "2025-01-10T12:00:00Z"
            }
        ]
    }
}
```

### **2. Get Validation Statistics**
```http
GET /api/statistics/validation
```

**Query Parameters:**
- `period` (optional): Time period (daily, weekly, monthly)
- `date_from` (optional): Start date
- `date_to` (optional): End date

**Response:**
```json
{
    "success": true,
    "data": {
        "l1_validations": {
            "total": 100,
            "approved": 80,
            "rejected": 20,
            "pending": 0
        },
        "l2_validations": {
            "total": 80,
            "approved": 70,
            "rejected": 10,
            "pending": 0
        },
        "move_to_pending": {
            "total": 5,
            "last_week": 2,
            "last_month": 5
        }
    }
}
```

## 🚨 Error Handling

### **Error Response Format**
```json
{
    "success": false,
    "message": "Error description",
    "error_code": "VALIDATION_ERROR",
    "details": {
        "field": "plot_area",
        "message": "Plot area must be greater than 0"
    },
    "debug_info": {
        "request_id": "req_123456",
        "timestamp": "2025-01-10T12:00:00Z"
    }
}
```

### **Common Error Codes**
- `VALIDATION_ERROR`: Input validation failed
- `AUTHENTICATION_ERROR`: Authentication failed
- `AUTHORIZATION_ERROR`: Insufficient permissions
- `NOT_FOUND`: Resource not found
- `RATE_LIMIT_EXCEEDED`: Too many requests
- `SERVER_ERROR`: Internal server error

## 🔒 Rate Limiting

### **Rate Limits**
- **Authentication**: 5 requests per minute
- **API Calls**: 100 requests per minute
- **File Upload**: 10 requests per minute
- **Report Generation**: 5 requests per minute

### **Rate Limit Headers**
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1641234567
```

## 📝 API Versioning

### **Current Version**
- **Version**: v1
- **Base URL**: `/api`
- **Deprecation Policy**: 6 months notice

### **Version Headers**
```
API-Version: v1
Accept: application/vnd.erda.v1+json
```

## 🧪 Testing

### **Test Environment**
- **Base URL**: `http://ei.test/api`
- **Test User**: `test@example.com`
- **Test Password**: `password`

### **Postman Collection**
- Collection available in `/docs/postman/`
- Environment variables configured
- Test scripts included

## 📚 SDKs & Libraries

### **JavaScript SDK**
```javascript
import { ErdaAPI } from 'erda-illumine-sdk';

const api = new ErdaAPI({
    baseURL: 'http://ei.test/api',
    token: 'your-token'
});

// Get polygons
const polygons = await api.polygons.getAll();

// Move to pending
await api.polygons.moveToPending(1);
```

### **PHP SDK**
```php
use ErdaIllumine\API\Client;

$client = new Client('http://ei.test/api', 'your-token');

// Get polygons
$polygons = $client->polygons()->all();

// Move to pending
$client->polygons()->moveToPending(1);
```

---

**Last Updated**: January 10, 2025  
**API Version**: v1  
**Status**: Active Development

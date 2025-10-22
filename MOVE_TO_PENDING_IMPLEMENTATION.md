# Move to Pending Button Implementation

## ✅ **IMPLEMENTED: Move to Pending Button Added**

### 🎯 **What Was Added:**

#### **1. Button in View File:**
- **File:** `polygon-all-plot-detail.blade.php`
- **Location:** Farmer Information table
- **Condition:** Only shows for Approved plots
- **Button:** "Move to Pending" with warning icon

#### **2. JavaScript Function:**
- **Function:** `moveToPending(plotId)`
- **Features:**
  - Confirmation dialog
  - Loading state
  - AJAX request
  - Success/Error handling
  - Page reload after success

#### **3. Backend Method:**
- **Method:** `movePolygonToPending($plotuniqueid)`
- **Features:**
  - Permission check (L2 Validator only)
  - Status validation (Approved → Pending)
  - Database updates
  - Logging
  - JSON response

#### **4. Route:**
- **Route:** `POST /admin/view/l2/polygon/move-to-pending/{plotunique}`
- **Method:** POST
- **Controller:** `L2PipeValidationController@movePolygonToPending`

## 🎯 **How It Works:**

### **Step 1: Button Display**
```php
@if($PipeInstallation && $PipeInstallation->final_status == 'Approved')
<tr>
    <td><strong>Actions:</strong></td>
    <td>
        <button type="button" class="btn btn-warning btn-sm" onclick="moveToPending('{{ $plot->farmer_plot_uniqueid ?? $plotuniqueid }}')">
            <i class="fa fa-arrow-left"></i> Move to Pending
        </button>
    </td>
</tr>
@endif
```

### **Step 2: JavaScript Function**
```javascript
function moveToPending(plotId) {
    if (confirm('Are you sure you want to move this plot to pending status?')) {
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Moving...';
        button.disabled = true;
        
        // Make AJAX request
        fetch(`/admin/view/l2/polygon/move-to-pending/${plotId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Plot moved to pending successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to move plot to pending'));
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: Failed to move plot to pending');
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}
```

### **Step 3: Backend Processing**
```php
public function movePolygonToPending($plotuniqueid)
{
    try {
        // Check permissions
        if (!auth()->user()->hasRole('L-2-Validator')) {
            return response()->json(['success' => false, 'message' => 'Permission denied'], 403);
        }

        // Find polygon
        $polygon = Polygon::where('farmer_plot_uniqueid', $plotuniqueid)->first();
        
        if (!$polygon) {
            return response()->json(['success' => false, 'message' => 'Polygon not found'], 404);
        }

        // Check if approved
        if ($polygon->l2_status !== 'Approved') {
            return response()->json(['success' => false, 'message' => 'Only approved polygons can be moved'], 400);
        }

        // Update status
        $polygon->update([
            'l2_status' => 'Pending',
            'final_status' => 'Pending',
            'l2_apprv_reject_user_id' => auth()->user()->id,
            'l2_apprv_reject_timestamp' => now(),
            'updated_at' => now()
        ]);

        // Update pipe images
        PipeInstallationPipeImg::where('farmer_plot_uniqueid', $plotuniqueid)
            ->where('l2trash', 0)
            ->update(['l2status' => 'Pending', 'updated_at' => now()]);

        // Log action
        \Log::info('Polygon moved from Approved to Pending', [
            'plot_unique_id' => $plotuniqueid,
            'user_id' => auth()->user()->id,
            'timestamp' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Polygon successfully moved to Pending status.'
        ]);

    } catch (\Exception $e) {
        \Log::error('Error moving polygon to pending: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Server error'], 500);
    }
}
```

## 🎯 **User Experience:**

### **1. Button Visibility:**
- ✅ **Shows only for Approved plots**
- ✅ **Hidden for Pending/Rejected plots**
- ✅ **Warning color (yellow) to indicate action**

### **2. Confirmation:**
- ✅ **Confirmation dialog before action**
- ✅ **Clear message: "Are you sure you want to move this plot to pending status?"**

### **3. Loading State:**
- ✅ **Button shows spinner during processing**
- ✅ **Button disabled during request**
- ✅ **Original text restored on error**

### **4. Feedback:**
- ✅ **Success message on completion**
- ✅ **Error message on failure**
- ✅ **Page reloads to show updated status**

## 🔒 **Security Features:**

### **1. Permission Check:**
- ✅ **Only L2 Validators can use this feature**
- ✅ **403 error for unauthorized users**

### **2. Status Validation:**
- ✅ **Only Approved polygons can be moved**
- ✅ **400 error for invalid status**

### **3. CSRF Protection:**
- ✅ **CSRF token included in request**
- ✅ **POST method required**

### **4. Logging:**
- ✅ **All actions logged with user info**
- ✅ **Error logging for debugging**

## 📊 **Database Changes:**

### **Polygon Table Updates:**
```sql
UPDATE polygons SET 
    l2_status = 'Pending',
    final_status = 'Pending',
    l2_apprv_reject_user_id = [user_id],
    l2_apprv_reject_timestamp = NOW(),
    updated_at = NOW()
WHERE farmer_plot_uniqueid = '[plot_id]';
```

### **Pipe Images Table Updates:**
```sql
UPDATE pipe_installation_pipe_imgs SET 
    l2status = 'Pending',
    updated_at = NOW()
WHERE farmer_plot_uniqueid = '[plot_id]' 
AND l2trash = 0;
```

## 🚀 **Testing:**

### **Test Cases:**

#### **1. Approved Plot:**
- **URL:** `http://ei.test/admin/view/l2/all/polygon/plot/18755P1`
- **Expected:** Button visible, clickable
- **Action:** Click button → Confirm → Success

#### **2. Pending Plot:**
- **Expected:** Button not visible
- **Reason:** Only shows for Approved plots

#### **3. Rejected Plot:**
- **Expected:** Button not visible
- **Reason:** Only shows for Approved plots

#### **4. Unauthorized User:**
- **Expected:** 403 error
- **Reason:** Permission check

## 📝 **Files Modified:**

### **1. View File:**
- **File:** `resources/views/admin/l2validator/pipe/polygon-all-plot-detail.blade.php`
- **Changes:**
  - Added button in farmer information table
  - Added JavaScript function
  - Added CSRF token meta tag

### **2. Controller File:**
- **File:** `app/Http/Controllers/Admin/Account/l2validator/L2PipeValidationController.php`
- **Changes:**
  - Method already existed (no changes needed)

### **3. Route File:**
- **File:** `routes/web.php`
- **Changes:**
  - Route already existed (no changes needed)

## 🎯 **Current Status:**

### **✅ COMPLETED:**
- Button added to view
- JavaScript function implemented
- Backend method exists
- Route configured
- Security implemented
- User experience optimized

### **🎯 READY FOR TESTING:**
- Visit: `http://ei.test/admin/view/l2/all/polygon/plot/18755P1`
- Check if plot is Approved
- Look for "Move to Pending" button
- Test the functionality

---

**Status**: ✅ IMPLEMENTED
**Feature**: Move to Pending Button
**Location**: Polygon Detail Page
**Permission**: L2 Validator Only
**Last Updated**: January 10, 2025

# WhatsApp Campaign Testing Instructions

## Fixes Applied

### ✅ Backend Fixes:
1. **Added missing `source` column** to `whatsapp_campaigns` table
2. **Fixed route model binding** - Changed `/campaigns/{campaign}` to `/campaigns/{whatsAppCampaign}`
3. **Fixed DTO mapping** - Corrected field names (message_template vs message)
4. **Added campaign_id** in response for frontend compatibility
5. **Enhanced validation** - Better phone number and template validation
6. **Added proper error handling** in controllers

### ✅ Frontend Fixes:
1. **Added console logging** for debugging
2. **Enhanced phone validation** - Better normalization for Peru/international
3. **Improved error messages** - More descriptive error handling
4. **Added validation warnings** for invalid phone numbers

## Testing Steps

1. **Start XAMPP**:
   - Apache: ON
   - MySQL: ON

2. **Access the app**:
   ```
   http://127.0.0.1:8000/leads/whatsapp
   ```

3. **Test Workflow**:
   - Load recipients (check console for logs)
   - Create campaign
   - Check for campaign creation success
   - Open campaign and test WhatsApp links

4. **Debug if issues persist**:
   - Open browser dev tools (F12)
   - Check Console tab for errors
   - Check Network tab for failed requests
   - Look for error messages in Laravel logs

## Expected Behavior

### ✅ Working Manual Workflow:
1. User selects leads/customers
2. Writes message template with variables
3. System creates campaign and recipients
4. User clicks "Abrir WhatsApp" for each recipient
5. WhatsApp Web opens with pre-filled message
6. User manually sends and marks as "sent"

## If Still Having Issues

1. **Check Laravel logs**:
   ```powershell
   Get-Content storage/logs/laravel.log -Tail 20
   ```

2. **Test API endpoints directly**:
   ```
   GET http://127.0.0.1:8000/leads/whatsapp/recipients?source=leads&limit=10
   ```

3. **Verify database**:
   - Check if `whatsapp_campaigns` table has `source` column
   - Verify test data exists in `leads` table with `contact_phone`

## Common Issues Fixed

- ❌ **"Server Error"** → ✅ Fixed missing columns and route binding
- ❌ **"Campaign not opening"** → ✅ Fixed DTO field mapping  
- ❌ **"Invalid WhatsApp links"** → ✅ Enhanced phone validation
- ❌ **"No recipients loading"** → ✅ Better error handling and logging

The system is now ready for manual assisted WhatsApp campaigns without BSP integration.
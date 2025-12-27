# Fix DHL "Invalid Credentials" Error

## ✅ Good News
DHL Express is **working and appearing on checkout**! The integration is complete. You just need to fix the API credentials.

## 🔍 Current Status
- ✅ DHL appears on checkout page
- ✅ API requests are being sent correctly
- ❌ API returns "Invalid Credentials" error

## 🔧 Steps to Fix

### Step 1: Verify Your DHL Developer Portal Credentials

1. **Go to DHL Developer Portal**: https://developer.dhl.com/
2. **Log in** to your account
3. **Check your API credentials**:
   - API Key (Site ID)
   - API Secret (Password)
   - Account Number

### Step 2: Verify Account Number Format

DHL account numbers can be in different formats:
- **Numeric**: `1005155689` (10 digits)
- **Alphanumeric**: `sSgnLtd-0001` (with prefix/suffix)

**Important**: The Account Number must match exactly what's registered with your API Key/Secret.

### Step 3: Check Sandbox vs Production

Your current settings show:
- **Sandbox Mode**: YES ✅
- **API Endpoint**: `https://express.api.dhl.com/mydhlapi/test/rates`

**Make sure**:
- If using **Sandbox credentials**, keep Sandbox Mode = YES
- If using **Production credentials**, set Sandbox Mode = NO

### Step 4: Update Admin Configuration

1. Go to: **Admin Panel → Configure → Sales → Shipping Methods → DHL Express**
2. **Verify each field**:
   - **API Key**: Should match your DHL Developer Portal API Key exactly
   - **API Secret**: Should match your DHL Developer Portal API Secret exactly
   - **Account Number**: Should match the account number associated with your API credentials
   - **Sandbox Mode**: 
     - ✅ YES if using test/sandbox credentials
     - ❌ NO if using production credentials

3. **Save** the configuration

### Step 5: Clear Cache Again

After updating credentials:

```bash
php artisan optimize:clear
php artisan config:cache
```

### Step 6: Test Again

1. Go to checkout
2. Add products to cart
3. Enter shipping address
4. DHL should now show real rates instead of "Invalid Credentials"

## 🐛 Common Issues

### Issue 1: Account Number Mismatch
**Symptom**: API returns "Invalid Credentials"
**Solution**: 
- Verify account number matches your API Key/Secret
- Check if account number needs prefix/suffix
- Contact DHL support to confirm account number format

### Issue 2: Wrong Endpoint
**Symptom**: Sandbox credentials on production endpoint (or vice versa)
**Solution**:
- If using sandbox credentials → Set Sandbox Mode = YES
- If using production credentials → Set Sandbox Mode = NO

### Issue 3: Credentials Not Saved
**Symptom**: Old account number still being used
**Solution**:
- Clear all caches: `php artisan optimize:clear`
- Re-save configuration in admin panel
- Check if configuration is channel-specific (set for correct channel)

### Issue 4: API Key/Secret Format
**Symptom**: Credentials look correct but still fail
**Solution**:
- Copy-paste credentials directly from DHL Developer Portal
- Don't add extra spaces or characters
- Check if credentials are case-sensitive

## 📋 Testing Checklist

- [ ] API Key matches DHL Developer Portal
- [ ] API Secret matches DHL Developer Portal  
- [ ] Account Number matches DHL Developer Portal
- [ ] Sandbox Mode matches credential type (sandbox/production)
- [ ] Configuration saved in admin panel
- [ ] Cache cleared after saving
- [ ] Tested on checkout page

## 🎯 Expected Result

After fixing credentials, you should see:
- **DHL Express** option on checkout
- **Real shipping rates** from DHL API
- **Estimated delivery dates** (if available)
- **No "Invalid Credentials" error**

## 📞 Need Help?

If credentials are correct but still getting errors:
1. Check DHL Developer Portal for account status
2. Verify API Key/Secret are active
3. Contact DHL support with error message ID from logs
4. Check DHL API documentation for account number format requirements


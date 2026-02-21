# DHL Express - Checkout Troubleshooting

## ✅ What Was Fixed

I've made the following changes to ensure DHL Express appears on the checkout page:

### 1. Added `isAvailable()` Method Override
- DHL will now appear on checkout even before admin configuration
- It will show as active by default until you configure it in admin panel

### 2. Added Test Rate Support
- When API credentials are not configured, DHL will show a test rate
- This allows you to see DHL on checkout before configuring API credentials
- Test rate shows: "DHL Express (Configure in Admin)" with $0.00 cost

### 3. Better Error Handling
- DHL won't fail silently if API credentials are missing
- It will show a helpful message indicating configuration is needed

## 🔍 Why DHL Might Not Be Showing

### Issue 1: Cart Has No Products
**Solution:** Add at least one product to the cart

### Issue 2: Products Have No Weight
**Solution:** Ensure all products have weight set (in kg)

### Issue 3: No Shipping Address Entered
**Solution:** Complete the shipping address in checkout

## 📋 Testing Steps

1. **Add a Product to Cart**
   - Go to your store
   - Add a product with weight set
   - Proceed to checkout

2. **Enter Shipping Address**
   - Complete the shipping address form
   - Click "Continue" to shipping

3. **DHL Should Now Appear**
   - You should see "DHL Express" as a shipping option
   - If not configured: Shows "DHL Express (Configure in Admin)" with $0.00
   - If configured: Shows real rates from DHL API

## ⚙️ Configure DHL Properly

Once DHL appears on checkout, you need to configure it:

1. **Go to Admin Panel**
   - Navigate to: `Configure > Sales > Shipping Methods > DHL Express`

2. **Enter Your DHL API Credentials**
   - **API Key**: Your DHL API Key (from https://developer.dhl.com/)
   - **API Secret**: Your DHL API Secret
   - **Account Number**: Your DHL Account Number
   - **Sandbox Mode**: ✅ Enable for testing

3. **Set Origin Address**
   - **Origin Postcode**: Your warehouse/shipping postcode
   - **Origin City**: Your shipping city
   - **Origin Country Code**: Your country code (e.g., US, UK)
   - **Origin Address**: Your shipping address

4. **Set Default Dimensions** (if products don't have dimensions)
   - **Default Length**: 10 cm
   - **Default Width**: 10 cm
   - **Default Height**: 5 cm

5. **Configure Fallback Rate** (optional)
   - **Fallback Enabled**: ✅ Enable
   - **Fallback Rate**: Set a default rate (e.g., 50.00)

6. **Save Configuration**
   - Click "Save" button
   - Clear cache: `php artisan optimize:clear`

## 🧪 Test Again

After configuration:

1. Add a product with weight to cart
2. Go to checkout
3. Enter shipping address
4. DHL should show real rates from API

## 🔍 Check Logs

If DHL still doesn't work, check:
```bash
tail -f storage/logs/laravel.log
```

Look for:
- "DHL API Request" - API is being called
- "DHL API Response" - API response received
- "DHL API Error" - API call failed
- Any other error messages

## ❓ Common Issues

### DHL Not Appearing on Checkout
**Cause:** Cart has no weight or products
**Fix:** Ensure products have weight > 0

### Showing $0.00 Price
**Cause:** API credentials not configured
**Fix:** Configure API credentials in admin panel

### API Error in Logs
**Cause:** Invalid API credentials or API endpoint issue
**Fix:** 
- Check API credentials are correct
- Verify account number is correct
- Check if sandbox mode matches your credentials (test vs production)

### No Rates Returned
**Cause:** DHL API not returning rates for that route
**Fix:**
- Verify origin and destination are valid
- Check if DHL serves that route
- Try different addresses


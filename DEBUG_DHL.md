# Debug DHL Shipping

## Changes Made

1. ✅ Made `isAvailable()` return `true` always
2. ✅ Added logging throughout the calculate() method
3. ✅ Made DHL work with 0 weight (uses default 1kg)
4. ✅ Cleared all caches

## Next Steps

### 1. Try Checkout Again
- Add product to cart
- Go to checkout
- Enter shipping address
- Click continue
- Check if DHL appears

### 2. Check the Logs

Run this command to see what's happening:

```bash
tail -f storage/logs/laravel.log
```

Then try checkout and watch for DHL-related log messages.

### 3. What to Look For in Logs

Look for these messages:
- `DHL: Not available (isAvailable returned false)` - Carrier not available
- `DHL: No cart found` - Cart doesn't exist
- `DHL: No shipping address` - No shipping address entered
- `DHL: Weight: X` - Weight being calculated
- `DHL: Not configured. Returning test rate.` - Showing test rate
- `DHL: Weight was 0, using default 1kg` - Using default weight

### 4. Verify Carrier is Loaded

Test if DHL carrier is being instantiated:

```bash
php artisan tinker --execute="
\$carrier = new Webkul\Shipping\Carriers\DHL();
echo 'Code: ' . \$carrier->getCode() . PHP_EOL;
echo 'Available: ' . (\$carrier->isAvailable() ? 'Yes' : 'No') . PHP_EOL;
"
```

Should output:
```
Code: dhl
Available: Yes
```

### 5. Verify All Carriers are Registered

```bash
php artisan tinker --execute="print_r(config('carriers'));"
```

Should show: flatrate, free, and dhl

### 6. Common Issues

**Issue: No products in cart**
- Solution: Add products with weight set

**Issue: No shipping address**
- Solution: Complete shipping address in checkout

**Issue: Carrier not in config**
- Solution: Run `php artisan config:cache`

**Issue: Autoload error**
- Solution: Run `composer dump-autoload`


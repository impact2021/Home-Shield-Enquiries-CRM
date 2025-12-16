# Quick Start Guide

Get your Home Shield Enquiries CRM up and running in 5 minutes!

## ⚡ 5-Minute Setup

### Step 1: Install Plugin (1 minute)

1. Download the plugin or clone this repository
2. Upload to `/wp-content/plugins/home-shield-crm/`
3. In WordPress admin, go to **Plugins** → Activate "Home Shield Enquiries CRM"

✅ Done! Database table is automatically created.

### Step 2: Get Google API Key (2 minutes)

**Quick Route:**
1. Visit: https://console.cloud.google.com/
2. Create/select a project
3. Enable: **Places API** and **Maps JavaScript API**
4. Create API Key (Credentials section)
5. Restrict to your domain (Application restrictions)
6. Restrict to Places API & Maps JavaScript API (API restrictions)

### Step 3: Configure Plugin (1 minute)

1. In WordPress admin: **HS Enquiries** → **Settings**
2. Paste your Google API key
3. Click **Save Settings**

### Step 4: Add Form to Page (1 minute)

1. Go to **Pages** → **Add New**
2. Title: "Get a Quote" (or any name)
3. Add this shortcode in the content:
   ```
   [hs_contact_form]
   ```
4. Click **Publish**

### Step 5: Test! (<1 minute)

1. Visit your new page
2. Fill out the form (try the NZ address autocomplete!)
3. Submit
4. Check **HS Enquiries** in admin menu to see your submission

🎉 **You're done!**

## 📋 Daily Workflow

### For Admins

1. **Check New Enquiries**
   - Go to **HS Enquiries** in admin menu
   - Click **Not Actioned** tab

2. **Send a Quote**
   - Change status dropdown to "Quoted"
   - Email modal opens automatically
   - Add work items and costs (GST auto-calculated!)
   - Click "Send Email"

3. **Track Progress**
   - Use tabs to filter enquiries
   - Update status as job progresses
   - Mark as "Completed" when done

### Status Flow

```
Not Actioned → Emailed → Quoted → Completed
              ↓
             Dead (if customer not interested)
```

## 🎯 Common Tasks

### Adding the Form to Multiple Pages

Just add `[hs_contact_form]` to any page or post!

### Changing Job Types

Edit `includes/class-hs-crm-form.php`, find `get_job_types()` function:

```php
return array(
    'interior_painting' => 'Interior Painting',
    'exterior_painting' => 'Exterior Painting',
    'roof_painting' => 'Roof Painting',
    'fence_painting' => 'Fence Painting',
    'commercial_painting' => 'Commercial Painting',
    'your_new_type' => 'Your New Type Name'  // Add here
);
```

### Customizing Email Subject

In the email modal, just edit the "Subject" field before sending!

### Changing GST Rate

If GST rate changes, edit `assets/js/scripts.js`:

```javascript
var gst = cost * 0.15;  // Change 0.15 to new rate (e.g., 0.13 for 13%)
```

And `includes/class-hs-crm-email.php`:

```php
$gst = $cost * 0.15;  // Change here too
```

## 🔧 Troubleshooting Quick Fixes

### Address Autocomplete Not Working?

✓ Check API key is saved in Settings  
✓ Verify Places API is enabled in Google Console  
✓ Check browser console for errors  
✓ Test on another browser  

### Can't Send Emails?

✓ Install WP Mail SMTP plugin  
✓ Configure SMTP settings  
✓ Test with a simple WordPress email first  

### Don't See Menu Item?

✓ Make sure you're logged in as Administrator  
✓ Plugin is activated (check Plugins page)  

## 💡 Pro Tips

1. **Respond Quickly**: Use the "Not Actioned" filter daily to catch new enquiries
2. **Save Quote Templates**: Keep common work items in a text file to copy/paste
3. **Mobile Friendly**: Check enquiries on your phone - it works great!
4. **Backup Regularly**: Export your database regularly (wp_hs_enquiries table)
5. **Use SMTP**: For reliable email delivery, use an SMTP plugin

## 📱 Access on Mobile

The admin dashboard works perfectly on mobile:
- Visit your WordPress admin on phone
- Tap **HS Enquiries**
- View and manage enquiries on the go!

## 🚀 Next Steps

- Read [FEATURES.md](FEATURES.md) for complete feature list
- See [INSTALLATION.md](INSTALLATION.md) for detailed setup
- Check [PLUGIN_README.md](PLUGIN_README.md) for full documentation

## 📞 Support Checklist

Before asking for help, check:

- [ ] Plugin is activated
- [ ] Google API key is configured
- [ ] Places API and Maps JavaScript API are enabled
- [ ] WordPress and PHP meet minimum requirements
- [ ] Checked WordPress debug log (set WP_DEBUG to true)
- [ ] Tested in different browser
- [ ] Cleared browser cache

## ⚠️ Important Notes

- **Backup First**: Before updates, backup your database
- **Test Emails**: Send a test quote to yourself first
- **Secure API Key**: Always restrict your Google API key
- **HTTPS Recommended**: Use SSL/TLS for secure communication
- **Admin Only**: Only administrators can access the dashboard

---

Need more help? Check the full documentation files:
- [INSTALLATION.md](INSTALLATION.md) - Detailed installation steps
- [FEATURES.md](FEATURES.md) - Complete feature overview
- [PLUGIN_README.md](PLUGIN_README.md) - Full documentation

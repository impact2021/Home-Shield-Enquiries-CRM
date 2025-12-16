# Installation Guide - Home Shield Enquiries CRM

## Prerequisites

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher
- Administrator access to WordPress

## Step-by-Step Installation

### 1. Upload Plugin Files

**Option A: Via WordPress Admin (Recommended)**
1. Download the plugin as a ZIP file
2. Log into your WordPress admin panel
3. Go to `Plugins > Add New`
4. Click `Upload Plugin`
5. Choose the ZIP file and click `Install Now`
6. Click `Activate Plugin`

**Option B: Via FTP**
1. Upload the `home-shield-crm` folder to `/wp-content/plugins/`
2. Log into your WordPress admin panel
3. Go to `Plugins`
4. Find "Home Shield Enquiries CRM" and click `Activate`

### 2. Configure Google Maps API

The plugin uses Google Places API for New Zealand address autocomplete.

#### Get Your API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Navigate to `APIs & Services > Library`
4. Enable the following APIs:
   - **Places API**
   - **Maps JavaScript API**
5. Go to `APIs & Services > Credentials`
6. Click `Create Credentials > API Key`
7. Your API key will be generated

#### Secure Your API Key (Important!)

1. Click on your newly created API key
2. Under "Application restrictions":
   - Select "HTTP referrers (web sites)"
   - Add your website URL(s), e.g., `yourdomain.com/*`
3. Under "API restrictions":
   - Select "Restrict key"
   - Check only:
     - Places API
     - Maps JavaScript API
4. Click `Save`

#### Add API Key to Plugin

1. In WordPress admin, go to `HS Enquiries > Settings`
2. Paste your API key in the "Google Maps API Key" field
3. Click `Save Settings`

### 3. Create Contact Form Page

1. Go to `Pages > Add New`
2. Give your page a title (e.g., "Get a Quote" or "Contact Us")
3. In the content editor, add the shortcode:
   ```
   [hs_contact_form]
   ```
4. Publish the page
5. Visit the page to see your contact form

### 4. Access Admin Dashboard

1. In WordPress admin, look for the new menu item `HS Enquiries`
2. Click on it to view the enquiries dashboard
3. Only administrators can access this page

## Testing the Installation

### Test Contact Form

1. Visit your contact form page
2. Fill in all fields:
   - Name
   - Email
   - Phone
   - Address (start typing and select from NZ addresses)
   - Job Type
3. Submit the form
4. You should see a success message

### Test Admin Dashboard

1. Go to `HS Enquiries` in admin menu
2. You should see your test submission
3. Try changing the status using the dropdown
4. When changing to "Emailed", "Quoted", or "Completed", an email modal should appear

### Test Email System

1. Change an enquiry status to "Quoted"
2. In the email modal:
   - Verify customer email is correct
   - Add quote items with descriptions and costs
   - Watch GST calculate automatically (15%)
   - Review totals
3. Click "Send Email"
4. Check if email was sent (check spam folder too)

## Troubleshooting

### Address Autocomplete Not Working

**Problem**: Address field doesn't show suggestions
**Solutions**:
- Verify Google Maps API key is entered in Settings
- Check that Places API and Maps JavaScript API are enabled in Google Cloud Console
- Check browser console for API errors
- Verify API key restrictions aren't blocking your domain

### Email Not Sending

**Problem**: Quote emails not being sent
**Solutions**:
- Check WordPress email configuration
- Install an SMTP plugin (e.g., WP Mail SMTP)
- Test with WordPress default email first
- Check spam folders

### Database Table Not Created

**Problem**: Errors about missing database table
**Solutions**:
- Deactivate and reactivate the plugin
- Check database permissions
- Check WordPress debug log for errors

### Permission Denied in Admin

**Problem**: Cannot access HS Enquiries menu
**Solutions**:
- Ensure you're logged in as Administrator
- Check user capabilities in WordPress

## Uninstallation

To remove the plugin:

1. Go to `Plugins` in WordPress admin
2. Deactivate "Home Shield Enquiries CRM"
3. Click `Delete`

**Note**: Deleting the plugin will not automatically remove the database table. If you want to completely remove all data, you'll need to manually delete the `wp_hs_enquiries` table from your database using phpMyAdmin or a similar tool.

## Support

For issues or questions:
- Check this installation guide first
- Review the [PLUGIN_README.md](PLUGIN_README.md) for feature documentation
- Check WordPress debug logs: Enable `WP_DEBUG` in `wp-config.php`
- Contact your system administrator

## Security Notes

- Only administrators can access the enquiries dashboard
- All form inputs are sanitized and validated
- SQL queries use prepared statements
- AJAX requests are nonce-protected
- Output is properly escaped to prevent XSS
- Secure your Google Maps API key with restrictions

## Next Steps

After installation:
1. Customize email templates in `includes/class-hs-crm-email.php`
2. Adjust job types in `includes/class-hs-crm-form.php`
3. Style the form to match your theme (optional)
4. Set up SMTP for reliable email delivery
5. Test thoroughly before going live

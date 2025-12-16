# Home Shield Enquiries CRM - WordPress Plugin

A comprehensive CRM system for managing painter enquiries with contact form, admin dashboard, and automated quote generation with GST calculation.

## Features

- **Contact Form**: Simple form with name, phone, address (NZ geocoded), and job requirement fields
- **Job Types**: Pre-defined options for house painting services:
  - Interior Painting
  - Exterior Painting
  - Roof Painting
  - Fence Painting
  - Commercial Painting
- **Admin Dashboard**: Secure admin-only page to manage enquiries
- **Status Management**: Track enquiries through different stages:
  - Not Actioned
  - Emailed
  - Quoted
  - Completed
  - Dead
- **Filtered Tabs**: Quick filtering by status with counts
- **Email System**: Send professional quote emails to customers
- **Quote Table**: Editable table with automatic GST (15%) calculation
- **NZ Address Autocomplete**: Google Places API integration restricted to New Zealand addresses only

## Installation

1. Upload the `home-shield-crm` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **HS Enquiries > Settings** in the WordPress admin
4. Enter your Google Maps API key (required for address autocomplete)
5. Add the contact form to any page using the shortcode: `[hs_contact_form]`

## Google Maps API Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the following APIs:
   - Places API
   - Maps JavaScript API
4. Create an API key
5. Restrict the API key:
   - Add website restrictions (your WordPress domain)
   - Add API restrictions (Places API and Maps JavaScript API)
6. Enter the API key in **HS Enquiries > Settings**

## Usage

### Adding the Contact Form

Use the shortcode in any page or post:
```
[hs_contact_form]
```

### Managing Enquiries

1. Navigate to **HS Enquiries** in the WordPress admin menu
2. View all enquiries in a table format
3. Use tabs to filter by status
4. Change enquiry status using the dropdown in the Actions column
5. When changing status to "Emailed", "Quoted", or "Completed", an email modal will appear

### Sending Quote Emails

1. Change an enquiry status to trigger the email modal
2. Review the pre-filled customer information
3. Edit the subject and message as needed
4. Add quote items:
   - Enter description of work
   - Enter cost (excluding GST)
   - GST (15%) is automatically calculated
   - Add multiple items as needed
5. Review the total calculations:
   - Subtotal (ex GST)
   - Total GST
   - Total (inc GST)
6. Click "Send Email" to send the quote

## Database Structure

The plugin creates a table `wp_hs_enquiries` with the following structure:
- id: Unique identifier
- name: Customer name
- phone: Contact phone number
- address: Customer address (NZ only)
- job_type: Type of painting job
- status: Current status of the enquiry
- created_at: Timestamp of submission
- updated_at: Last modification timestamp

## File Structure

```
home-shield-crm/
├── home-shield-crm.php          # Main plugin file
├── includes/
│   ├── class-hs-crm-database.php   # Database operations
│   ├── class-hs-crm-form.php       # Contact form handler
│   ├── class-hs-crm-admin.php      # Admin interface
│   ├── class-hs-crm-email.php      # Email functionality
│   └── class-hs-crm-settings.php   # Settings page
├── assets/
│   ├── css/
│   │   └── styles.css              # Plugin styles
│   └── js/
│       └── scripts.js              # JavaScript functionality
└── PLUGIN_README.md                # This file
```

## Security Features

- Nonce verification for all form submissions
- AJAX request validation
- Admin-only access to dashboard
- Input sanitization and validation
- SQL injection prevention through prepared statements
- XSS protection through proper escaping

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher
- Google Maps API key (for address autocomplete)

## Support

For issues or questions, please contact the development team or create an issue on the GitHub repository.

## License

GPL v2 or later

## Changelog

### Version 1.0.0
- Initial release
- Contact form with NZ address autocomplete
- Admin dashboard with filtering
- Status management system
- Email system with quote table
- Automatic GST calculation (15%)

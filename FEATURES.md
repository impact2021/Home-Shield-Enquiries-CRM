# Feature Overview - Home Shield Enquiries CRM

This document provides a detailed overview of all features included in the Home Shield Enquiries CRM WordPress plugin.

## 1. Contact Form (Front-End)

### Form Fields

The contact form includes the following fields:

- **Name** (Required) - Text input for customer's full name
- **Email** (Required) - Email input with validation
- **Phone Number** (Required) - Telephone input
- **Address** (Required) - Auto-complete text input restricted to New Zealand addresses only
- **Job Requirement** (Required) - Dropdown select with 5 pre-defined options

### Job Types Available

1. **Interior Painting** - For painting inside of buildings
2. **Exterior Painting** - For painting outside surfaces
3. **Roof Painting** - Specialized roof painting services
4. **Fence Painting** - Fence and boundary painting
5. **Commercial Painting** - Large-scale commercial projects

### Form Features

- **Real-time Validation** - Client-side validation before submission
- **AJAX Submission** - No page reload required
- **Success/Error Messages** - Clear feedback to users
- **NZ Address Autocomplete** - Google Places API integration
  - Only shows addresses within New Zealand
  - Suggests addresses as user types
  - Ensures accurate location data
- **Responsive Design** - Works on mobile, tablet, and desktop
- **Accessible** - Proper labels and ARIA attributes

### Usage

Add to any page or post using the shortcode:
```
[hs_contact_form]
```

## 2. Admin Dashboard

### Overview Table

The admin dashboard displays all enquiries in a sortable table with the following columns:

- **ID** - Unique identifier
- **Name** - Customer name
- **Email** - Customer email address
- **Phone** - Contact number
- **Address** - Full address
- **Job Type** - Type of work requested
- **Status** - Current status with color-coded badge
- **Created** - Date and time of submission
- **Actions** - Status change dropdown

### Status Management

Each enquiry can be assigned one of five statuses:

1. **Not Actioned** (Gray badge)
   - Default status for new enquiries
   - Indicates no action has been taken yet

2. **Emailed** (Blue badge)
   - Customer has been contacted via email
   - Initial communication sent

3. **Quoted** (Orange badge)
   - Quote has been sent to customer
   - Awaiting customer decision

4. **Completed** (Green badge)
   - Job has been completed
   - Final status for successful projects

5. **Dead** (Red badge)
   - Enquiry is no longer active
   - Customer not interested or unresponsive

### Filtering Tabs

Quick filter tabs at the top of the dashboard:

- **All** - Shows all enquiries with total count
- **Not Actioned** - Only new enquiries needing attention
- **Emailed** - Enquiries that have been contacted
- **Quoted** - Enquiries with pending quotes
- **Completed** - Finished jobs
- **Dead** - Inactive enquiries

Each tab shows the count of enquiries in that status.

### Features

- **Real-time Updates** - Status changes update immediately
- **Confirmation Prompts** - Prevents accidental status changes
- **Color-Coded Badges** - Easy visual status identification
- **Chronological Sorting** - Newest enquiries appear first
- **Admin-Only Access** - Secure access for administrators only

## 3. Email Quote System

### Email Modal

When changing an enquiry status to "Emailed", "Quoted", or "Completed", a modal window appears with:

#### Basic Information

- **To:** Customer's email address (read-only)
- **Customer:** Customer name and phone (read-only)
- **Subject:** Editable subject line (default: "Quote for Painting Services")
- **Message:** Editable message body with greeting

#### Quote Table

An editable table for building detailed quotes:

**Columns:**
1. **Description of Work** - Text input for work item description
2. **Cost (ex GST)** - Number input for base cost
3. **GST (15%)** - Auto-calculated, read-only
4. **Remove** - Button to delete row

**Features:**
- Add unlimited quote items with "+ Add Item" button
- Automatic GST calculation at 15% for each line item
- Remove individual items (minimum 1 item required)
- Real-time total calculations

**Totals Section:**
- **Subtotal (ex GST)** - Sum of all costs before GST
- **Total GST** - Sum of all GST amounts
- **Total (inc GST)** - Final amount including GST

### Email Template

The sent email includes:

1. **Professional Header** - Home Shield Painters branding
2. **Custom Message** - Admin's personalized message
3. **Quote Table** - HTML formatted table with all items and totals
4. **Job Details Section** - Summary of enquiry information:
   - Customer name
   - Customer email
   - Address
   - Phone number
   - Job type
5. **Footer** - Professional closing message

### Email Features

- **HTML Formatted** - Professional appearance
- **Responsive Design** - Looks good on all devices
- **Automatic Calculations** - GST computed in real-time
- **Preview Before Send** - Review all details before sending
- **WordPress Mail System** - Uses WordPress email functions
- **SMTP Compatible** - Works with SMTP plugins for reliable delivery

## 4. Settings Page

Located at: `HS Enquiries > Settings`

### Configuration Options

**Google Maps API Key**
- Input field for API key
- Instructions for obtaining key
- Links to Google Maps Platform documentation
- Required APIs listed (Places API, Maps JavaScript API)

### Shortcode Reference

The settings page includes shortcode usage instructions for easy reference.

## 5. Security Features

### Form Security

- **Nonce Verification** - CSRF protection on all submissions
- **Input Sanitization** - All inputs cleaned before processing
- **Email Validation** - Validates email format
- **SQL Injection Prevention** - Prepared statements for all queries
- **XSS Protection** - Output escaping throughout

### Admin Security

- **Capability Checks** - Only administrators can access dashboard
- **AJAX Nonce Verification** - All AJAX requests verified
- **Read-only Fields** - Critical information cannot be edited
- **Confirmation Dialogs** - Prevents accidental actions

### Data Security

- **Encrypted Connection** - Works with HTTPS
- **WordPress Standards** - Follows WP coding standards
- **Database Prefixing** - Uses WordPress table prefix
- **No Direct Access** - PHP files protected from direct access

## 6. Responsive Design

### Mobile Optimization

- **Touch-Friendly** - Large buttons and inputs
- **Stacked Layouts** - Forms and tables adapt to small screens
- **Readable Text** - Appropriate font sizes
- **Easy Navigation** - Accessible tabs and menus

### Desktop Experience

- **Multi-Column Layouts** - Efficient use of screen space
- **Hover Effects** - Visual feedback on interactions
- **Keyboard Navigation** - Supports keyboard users
- **Wide Tables** - Full data visibility

## 7. Extensibility

### Customization Points

- **Job Types** - Easily add or modify in `class-hs-crm-form.php`
- **Email Templates** - Customize HTML in `class-hs-crm-email.php`
- **Styling** - Override CSS in your theme
- **Status Options** - Add new statuses in database class
- **GST Rate** - Adjustable in JavaScript calculations

### Developer-Friendly

- **Clean Code Structure** - Object-oriented PHP
- **Commented Code** - Inline documentation
- **WordPress Standards** - Follows WordPress coding standards
- **Action Hooks** - Can be extended with custom hooks
- **Filter Hooks** - Potential for custom filters

## Technical Specifications

### Database Schema

Table: `wp_hs_enquiries`

| Column | Type | Description |
|--------|------|-------------|
| id | mediumint(9) | Primary key |
| name | varchar(255) | Customer name |
| email | varchar(255) | Customer email |
| phone | varchar(50) | Phone number |
| address | text | Full address |
| job_type | varchar(100) | Job type selection |
| status | varchar(50) | Current status |
| created_at | datetime | Creation timestamp |
| updated_at | datetime | Last update timestamp |

### GST Calculation

- **Rate:** 15% (New Zealand standard rate)
- **Formula:** Cost × 0.15 = GST amount
- **Total:** Cost + GST = Total including GST
- **Precision:** 2 decimal places for currency

### API Integration

- **Google Places API** - Address autocomplete
- **Country Restriction:** New Zealand (NZ) only
- **Address Types:** Full street addresses
- **Fields Retrieved:** formatted_address, geometry, name

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- **Optimized Queries** - Indexed database columns
- **Lazy Loading** - Scripts loaded as needed
- **Minimal Dependencies** - Only jQuery required
- **Cached Assets** - Versioned for browser caching
- **AJAX Requests** - Asynchronous for better UX

## Accessibility

- **ARIA Labels** - Screen reader support
- **Keyboard Navigation** - All functions keyboard accessible
- **Focus Indicators** - Clear focus states
- **Semantic HTML** - Proper HTML structure
- **Form Labels** - All inputs properly labeled

## Workflow Example

1. **Customer visits website** → Fills out contact form
2. **Form submitted** → Data saved to database with "Not Actioned" status
3. **Admin reviews** → Views enquiry in dashboard
4. **Admin sends quote** → Changes status to "Quoted"
5. **Email modal opens** → Admin adds quote items
6. **GST auto-calculated** → Shows totals
7. **Email sent** → Customer receives professional quote
8. **Job completed** → Admin marks as "Completed"

This complete workflow ensures no enquiry is missed and all communications are tracked.

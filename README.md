# Home-Shield-Enquiries-CRM
CRM for Home Shield Painters

## Quote Generator

This CRM system includes a quote generator that allows administrators to create professional quotes for painting work with automated GST calculation.

### Features

- **Editable Quote Table**: Add multiple line items with work descriptions and costs
- **Automated GST Calculation**: GST is automatically calculated based on the configurable GST rate (default 10%)
- **Dynamic Totals**: All totals (subtotal, GST, grand total) are calculated automatically
- **Email Template Generation**: Generate a professional HTML email template ready to send to clients
- **Client Information**: Capture client name, email, and quote date

### How to Use

1. Open `index.html` in a web browser
2. Enter client information (name, email, quote date)
3. Adjust GST rate if needed (default is 10%)
4. Click "Add Row" to add line items
5. Enter work description and cost for each item
6. GST and totals are calculated automatically
7. Click "Generate Email Preview" to see the formatted quote email
8. Click "Copy Email HTML" to copy the HTML for pasting into an email client

### Files

- `index.html` - Main application page
- `styles.css` - Styling for the quote generator
- `script.js` - JavaScript logic for table management and calculations

### Technical Details

- Pure HTML/CSS/JavaScript implementation (no frameworks required)
- GST calculation: `GST = Cost × (GST Rate / 100)`
- Total calculation: `Total = Cost + GST`
- All monetary values are displayed with 2 decimal places

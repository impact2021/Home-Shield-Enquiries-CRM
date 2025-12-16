# Workflow Guide - Home Shield Enquiries CRM

## Visual Workflow Overview

This document illustrates how the Home Shield Enquiries CRM works from customer submission to job completion.

## 🔄 Complete Workflow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                         CUSTOMER SIDE                            │
└─────────────────────────────────────────────────────────────────┘

    👤 Customer visits website
          ↓
    📝 Fills out contact form:
       • Name
       • Email
       • Phone
       • Address (NZ autocomplete) 🗺️
       • Job Type (dropdown)
          ↓
    ✉️ Submits form (AJAX)
          ↓
    ✅ Sees success message
          ↓
    ⏳ Waits for quote email

┌─────────────────────────────────────────────────────────────────┐
│                          ADMIN SIDE                              │
└─────────────────────────────────────────────────────────────────┘

    🔔 New enquiry appears in dashboard
       Status: "Not Actioned" (Gray badge)
          ↓
    👀 Admin reviews enquiry details
          ↓
    ┌──────────────────────────────────────┐
    │  DECISION POINT: What to do?         │
    └──────────────────────────────────────┘
          ↓
    ┌─────┴─────┬─────────────┬─────────────┐
    │           │             │             │
    ↓           ↓             ↓             ↓
NOT VIABLE   CONTACT      SEND QUOTE    COMPLETE
   ↓            ↓             ↓             ↓
  ❌ Dead      📧 Email      💰 Quote      ✅ Done
             (Blue)        (Orange)      (Green)

┌─────────────────────────────────────────────────────────────────┐
│                      QUOTE PROCESS (Detailed)                    │
└─────────────────────────────────────────────────────────────────┘

    Admin changes status to "Quoted"
          ↓
    🖥️ Email modal opens automatically
          ↓
    Admin sees customer details:
       • Email address (read-only)
       • Name & phone (read-only)
          ↓
    📋 Admin builds quote table:
       ┌──────────────────────────────────────────┐
       │ Description          | Cost | GST | Total│
       ├──────────────────────────────────────────┤
       │ Interior walls       | $800 | $120| $920 │
       │ Exterior trim        | $500 | $75 | $575 │
       │ Roof coating         | $600 | $90 | $690 │
       └──────────────────────────────────────────┘
          ↓
    💡 GST auto-calculates (15% per line)
          ↓
    📊 Totals update in real-time:
       • Subtotal: $1,900 (ex GST)
       • Total GST: $285
       • Grand Total: $2,185 (inc GST)
          ↓
    📧 Admin reviews and clicks "Send Email"
          ↓
    ✅ Email sent to customer
          ↓
    📬 Customer receives professional quote email

┌─────────────────────────────────────────────────────────────────┐
│                         STATUS FLOW                              │
└─────────────────────────────────────────────────────────────────┘

  START
    ↓
┌────────────────┐
│ Not Actioned   │ ← New enquiry lands here
│  (Gray Badge)  │
└────────┬───────┘
         │
    ┌────┴──────┐
    ↓           ↓
┌─────────┐  ┌──────┐
│ Emailed │  │ Dead │ ← Not viable, customer unresponsive
│ (Blue)  │  │(Red) │
└────┬────┘  └──────┘
     │          ↓
     ↓        END
┌─────────┐
│ Quoted  │ ← Quote sent, waiting for acceptance
│(Orange) │
└────┬────┘
     │
     ↓
┌───────────┐
│ Completed │ ← Job finished successfully
│ (Green)   │
└─────┬─────┘
      ↓
     END

┌─────────────────────────────────────────────────────────────────┐
│                      FILTERING & VIEWS                           │
└─────────────────────────────────────────────────────────────────┘

  Admin Dashboard has filtering tabs:

  ┌────┬────────────┬─────────┬────────┬───────────┬──────┐
  │All │Not Actioned│ Emailed │ Quoted │ Completed │ Dead │
  │(15)│    (5)     │   (3)   │  (4)   │    (2)    │ (1)  │
  └────┴────────────┴─────────┴────────┴───────────┴──────┘
   ↓       ↓           ↓         ↓          ↓         ↓
  Shows  Shows     Shows     Shows      Shows     Shows
  ALL    New       Contact   Pending    Finished  Inactive
  items  items     made      quotes     jobs      leads

┌─────────────────────────────────────────────────────────────────┐
│                      DAILY WORKFLOW                              │
└─────────────────────────────────────────────────────────────────┘

  MORNING ROUTINE:
    1. Open "HS Enquiries" in WordPress admin
    2. Click "Not Actioned" tab
    3. Review new enquiries
    4. For each enquiry, decide:
       - Send quote? → Change to "Quoted" → Fill quote table → Send
       - Need more info? → Change to "Emailed" → Contact customer
       - Not suitable? → Change to "Dead"

  DURING DAY:
    5. Check "Quoted" tab for quotes sent
    6. Follow up if needed (phone calls)
    7. When job accepted → Start work
    8. When job done → Change to "Completed"

  REPORTING:
    9. Use "Completed" tab to see finished jobs
    10. Use counts for quick overview

┌─────────────────────────────────────────────────────────────────┐
│                       EMAIL ANATOMY                              │
└─────────────────────────────────────────────────────────────────┘

  📧 Quote Email Structure:

  ┌──────────────────────────────────────────────┐
  │         HOME SHIELD PAINTERS                  │ ← Header
  ├───────────────────────────────────────────────┤
  │ Dear Customer,                                │
  │                                               │
  │ Thank you for your enquiry.                   │ ← Message
  │ Please find our quote below:                  │
  ├───────────────────────────────────────────────┤
  │            QUOTE DETAILS                      │
  │                                               │
  │ ┌─────────────────────────────────────────┐  │
  │ │ Description  │Cost ex│GST  │Total inc│  │
  │ ├─────────────────────────────────────────┤  │
  │ │ Interior     │ $800  │$120 │  $920   │  │ ← Quote Table
  │ │ Exterior     │ $500  │$75  │  $575   │  │
  │ ├─────────────────────────────────────────┤  │
  │ │ Subtotal (ex GST):        $1,300       │  │
  │ │ Total GST:                $195         │  │
  │ │ Total (inc GST):          $1,495       │  │ ← Totals
  │ └─────────────────────────────────────────┘  │
  ├───────────────────────────────────────────────┤
  │            JOB DETAILS                        │
  │                                               │
  │ Name: John Smith                              │
  │ Email: john@example.com                       │ ← Customer Info
  │ Address: 123 Main St, Auckland, NZ           │
  │ Phone: 021 123 4567                           │
  │ Job Type: Interior Painting                   │
  ├───────────────────────────────────────────────┤
  │ Thank you for choosing Home Shield Painters   │ ← Footer
  └───────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                    TECHNICAL WORKFLOW                            │
└─────────────────────────────────────────────────────────────────┘

  Form Submission → AJAX Request → PHP Handler
                                        ↓
                                   Validate Input
                                        ↓
                                   Sanitize Data
                                        ↓
                              Insert into Database
                                        ↓
                                   Return Success
                                        ↓
                              Update UI (no reload)

  Status Change → AJAX Request → PHP Handler
                                      ↓
                                Check Permissions
                                      ↓
                                Update Database
                                      ↓
                        Check if Email Trigger
                                      ↓
                           Yes → Return enquiry data
                                      ↓
                              Show Email Modal
                                      ↓
                          Admin fills quote table
                                      ↓
                              JavaScript calculates GST
                                      ↓
                              Submit Email Form
                                      ↓
                           PHP builds email HTML
                                      ↓
                              Send via wp_mail()
                                      ↓
                                Return Success

┌─────────────────────────────────────────────────────────────────┐
│                     DATA FLOW                                    │
└─────────────────────────────────────────────────────────────────┘

  Customer Input → Form Fields → JavaScript Validation
                                        ↓
                                  AJAX Submit
                                        ↓
                              PHP Validation
                                        ↓
                              Database Insert
                                        ↓
                         wp_hs_enquiries Table
                                        ↓
                              Admin Dashboard Query
                                        ↓
                              Display in Table
                                        ↓
                         Admin Action (Status Change)
                                        ↓
                              Database Update
                                        ↓
                         Email Generation (if needed)
                                        ↓
                              Customer Email Inbox

┌─────────────────────────────────────────────────────────────────┐
│                    INTEGRATION POINTS                            │
└─────────────────────────────────────────────────────────────────┘

  WordPress Core
       ↓
  ┌──────────────────────┐
  │ Home Shield CRM      │
  │ Plugin               │
  └──────────────────────┘
       ↓
  ┌────────┴────────┐
  ↓                 ↓
Google Places API   WordPress Mail
(Address lookup)    (Email sending)
       ↓                 ↓
  Returns NZ        Sends to
  addresses only    Customer email

┌─────────────────────────────────────────────────────────────────┐
│                    SECURITY LAYERS                               │
└─────────────────────────────────────────────────────────────────┘

  Layer 1: WordPress Nonce Verification
  Layer 2: User Capability Check (is_admin)
  Layer 3: Input Sanitization
  Layer 4: SQL Prepared Statements
  Layer 5: Output Escaping
  Layer 6: AJAX Security Checks
  Layer 7: Direct File Access Prevention

  All layers active on every request! 🔒

┌─────────────────────────────────────────────────────────────────┐
│                   QUICK REFERENCE                                │
└─────────────────────────────────────────────────────────────────┘

  Status Colors:
  • Gray   = Not Actioned (new)
  • Blue   = Emailed (contacted)
  • Orange = Quoted (pending)
  • Green  = Completed (done)
  • Red    = Dead (inactive)

  GST Rate: 15% (New Zealand standard)
  
  Formula: Cost × 1.15 = Total inc GST
  
  Shortcode: [hs_contact_form]
  
  Menu Location: HS Enquiries (WordPress admin)
  
  Settings: HS Enquiries → Settings

┌─────────────────────────────────────────────────────────────────┐
│                    SUCCESS METRICS                               │
└─────────────────────────────────────────────────────────────────┘

  ✅ Customer submits form
  ✅ Enquiry appears in dashboard immediately
  ✅ Admin can filter and find enquiry
  ✅ Status change works smoothly
  ✅ Email modal opens when needed
  ✅ Quote table calculates correctly
  ✅ Email sends successfully
  ✅ Customer receives professional quote
  ✅ Job tracked through to completion

  = Happy customer! Happy business! 🎉

```

## 💡 Pro Tips for Admins

### Morning Checklist
1. ✅ Check "Not Actioned" tab first thing
2. ✅ Respond to all new enquiries within 24 hours
3. ✅ Move processed items out of "Not Actioned"

### Quote Building Tips
1. ✅ Be descriptive in work items
2. ✅ Double-check costs before sending
3. ✅ GST calculates automatically - no mental math!
4. ✅ Include prep work and materials in separate lines

### Status Management
1. ✅ Update status as soon as action taken
2. ✅ Use "Emailed" for initial contact without quote
3. ✅ Use "Quoted" when sending price
4. ✅ Move to "Completed" only when job 100% done
5. ✅ Use "Dead" for unresponsive/declined enquiries

### Keeping Organized
1. ✅ Review "Quoted" tab daily for follow-ups
2. ✅ Keep "Not Actioned" tab empty by end of day
3. ✅ Use tab counts for quick status overview
4. ✅ Regular check-ins on older "Quoted" items

## 🎯 Common Scenarios

### Scenario 1: New Customer Enquiry
```
Customer submits → "Not Actioned" → Review → Send quote → "Quoted" 
→ Customer accepts → Do work → "Completed"
```

### Scenario 2: Need More Information
```
Customer submits → "Not Actioned" → Need details → "Emailed" 
→ Get info → Send quote → "Quoted" → Continue workflow
```

### Scenario 3: Not Suitable
```
Customer submits → "Not Actioned" → Review → Not our type of work 
→ "Dead"
```

### Scenario 4: Customer Unresponsive
```
Customer submits → "Not Actioned" → Send quote → "Quoted" 
→ Follow up → No response → Wait → Still no response → "Dead"
```

## 📊 Workflow Efficiency

**Before This Plugin:**
- Manual email tracking
- Spreadsheets for enquiries
- Lost enquiries
- Manual GST calculations
- Inconsistent quotes
- No central tracking

**After This Plugin:**
- Automatic tracking
- Centralized dashboard
- Nothing gets lost
- Auto GST calculation
- Professional consistent quotes
- Complete history

**Time Savings:**
- ~15 minutes per quote (auto calculations)
- ~30 minutes per day (centralized tracking)
- ~2 hours per week (no manual spreadsheets)

= More time for actual painting! 🎨

---

**For complete details, see:**
- [QUICKSTART.md](QUICKSTART.md) - Get started in 5 minutes
- [FEATURES.md](FEATURES.md) - Full feature documentation
- [INSTALLATION.md](INSTALLATION.md) - Detailed setup guide

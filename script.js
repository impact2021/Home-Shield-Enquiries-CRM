// Quote Table Management
class QuoteTable {
    constructor() {
        this.tableBody = document.getElementById('quoteTableBody');
        this.gstRateInput = document.getElementById('gstRate');
        this.addRowBtn = document.getElementById('addRowBtn');
        this.generateEmailBtn = document.getElementById('generateEmailBtn');
        this.copyEmailBtn = document.getElementById('copyEmailBtn');
        
        this.init();
    }

    init() {
        // Add event listeners
        this.addRowBtn.addEventListener('click', () => this.addRow());
        this.generateEmailBtn.addEventListener('click', () => this.generateEmail());
        this.copyEmailBtn.addEventListener('click', () => this.copyEmailHTML());
        this.gstRateInput.addEventListener('change', () => this.updateAllRows());
        
        // Set today's date
        const today = new Date();
        document.getElementById('quoteDate').value = today.toISOString().split('T')[0];
        
        // Add initial row
        this.addRow();
    }

    addRow() {
        const row = document.createElement('tr');
        row.className = 'quote-row';
        
        row.innerHTML = `
            <td>
                <input type="text" class="description-input" placeholder="Enter work description">
            </td>
            <td>
                <input type="number" class="cost-input" placeholder="0.00" step="0.01" min="0">
            </td>
            <td class="gst-cell">$0.00</td>
            <td class="total-cell">$0.00</td>
            <td>
                <button class="btn btn-danger delete-btn">Delete</button>
            </td>
        `;
        
        this.tableBody.appendChild(row);
        
        // Add event listeners to inputs
        const costInput = row.querySelector('.cost-input');
        costInput.addEventListener('input', () => this.updateRow(row));
        
        const deleteBtn = row.querySelector('.delete-btn');
        deleteBtn.addEventListener('click', () => this.deleteRow(row));
    }

    deleteRow(row) {
        row.remove();
        this.calculateTotals();
    }

    updateRow(row) {
        const costInput = row.querySelector('.cost-input');
        const gstCell = row.querySelector('.gst-cell');
        const totalCell = row.querySelector('.total-cell');
        
        const cost = parseFloat(costInput.value) || 0;
        const gstRate = parseFloat(this.gstRateInput.value) || 0;
        const gst = cost * (gstRate / 100);
        const total = cost + gst;
        
        gstCell.textContent = `$${gst.toFixed(2)}`;
        totalCell.textContent = `$${total.toFixed(2)}`;
        
        this.calculateTotals();
    }

    updateAllRows() {
        const rows = this.tableBody.querySelectorAll('.quote-row');
        rows.forEach(row => this.updateRow(row));
    }

    calculateTotals() {
        const rows = this.tableBody.querySelectorAll('.quote-row');
        let subtotal = 0;
        let totalGst = 0;
        let grandTotal = 0;
        
        rows.forEach(row => {
            const costInput = row.querySelector('.cost-input');
            const cost = parseFloat(costInput.value) || 0;
            const gstRate = parseFloat(this.gstRateInput.value) || 0;
            const gst = cost * (gstRate / 100);
            
            subtotal += cost;
            totalGst += gst;
            grandTotal += (cost + gst);
        });
        
        document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('totalGst').textContent = `$${totalGst.toFixed(2)}`;
        document.getElementById('grandTotal').textContent = `$${grandTotal.toFixed(2)}`;
    }

    getQuoteData() {
        const rows = this.tableBody.querySelectorAll('.quote-row');
        const items = [];
        const gstRate = parseFloat(this.gstRateInput.value) || 0;
        
        rows.forEach(row => {
            const description = row.querySelector('.description-input').value;
            const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
            const gst = cost * (gstRate / 100);
            const total = cost + gst;
            
            if (description || cost > 0) {
                items.push({
                    description: description || 'N/A',
                    cost: cost,
                    gst: gst,
                    total: total
                });
            }
        });
        
        return {
            clientName: document.getElementById('clientName').value || 'N/A',
            clientEmail: document.getElementById('clientEmail').value || 'N/A',
            quoteDate: document.getElementById('quoteDate').value || new Date().toISOString().split('T')[0],
            gstRate: gstRate,
            items: items,
            subtotal: items.reduce((sum, item) => sum + item.cost, 0),
            totalGst: items.reduce((sum, item) => sum + item.gst, 0),
            grandTotal: items.reduce((sum, item) => sum + item.total, 0)
        };
    }

    generateEmail() {
        const data = this.getQuoteData();
        
        if (data.items.length === 0) {
            alert('Please add at least one item to the quote.');
            return;
        }
        
        const emailHTML = this.createEmailHTML(data);
        
        const emailPreview = document.getElementById('emailPreview');
        const emailContent = document.getElementById('emailContent');
        
        emailContent.innerHTML = emailHTML;
        emailPreview.style.display = 'block';
        
        // Scroll to preview
        emailPreview.scrollIntoView({ behavior: 'smooth' });
    }

    createEmailHTML(data) {
        let itemsHTML = '';
        data.items.forEach(item => {
            itemsHTML += `
                <tr>
                    <td>${item.description}</td>
                    <td style="text-align: right;">$${item.cost.toFixed(2)}</td>
                    <td style="text-align: right;">$${item.gst.toFixed(2)}</td>
                    <td style="text-align: right;">$${item.total.toFixed(2)}</td>
                </tr>
            `;
        });
        
        return `
            <div class="email-template" style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto;">
                <div style="text-align: center; padding: 20px; background-color: #2c3e50; color: white;">
                    <h1 style="margin: 0;">Home Shield Painters</h1>
                    <h2 style="margin: 10px 0 0 0; font-weight: normal;">Quote</h2>
                </div>
                
                <div style="padding: 20px;">
                    <p>Dear ${data.clientName},</p>
                    
                    <p>Thank you for your enquiry. Please find below our quote for the painting work:</p>
                    
                    <div style="margin: 20px 0;">
                        <p><strong>Quote Date:</strong> ${data.quoteDate}</p>
                        <p><strong>GST Rate:</strong> ${data.gstRate}%</p>
                    </div>
                    
                    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                        <thead>
                            <tr style="background-color: #34495e; color: white;">
                                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Description of Work</th>
                                <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Cost</th>
                                <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">GST</th>
                                <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHTML}
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #ecf0f1; font-weight: bold;">
                                <td style="padding: 10px; border: 1px solid #ddd;">Total</td>
                                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">$${data.subtotal.toFixed(2)}</td>
                                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">$${data.totalGst.toFixed(2)}</td>
                                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">$${data.grandTotal.toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <p>This quote is valid for 30 days from the date above.</p>
                    
                    <p>If you have any questions or would like to proceed with this quote, please don't hesitate to contact us.</p>
                    
                    <p>Best regards,<br>
                    <strong>Home Shield Painters</strong></p>
                </div>
            </div>
        `;
    }

    async copyEmailHTML() {
        const data = this.getQuoteData();
        
        if (data.items.length === 0) {
            alert('Please add at least one item to the quote.');
            return;
        }
        
        const emailHTML = this.createEmailHTML(data);
        
        // Try modern Clipboard API first
        if (navigator.clipboard && navigator.clipboard.writeText) {
            try {
                await navigator.clipboard.writeText(emailHTML);
                alert('Email HTML copied to clipboard! You can now paste it into your email client.');
                return;
            } catch (err) {
                console.error('Clipboard API failed:', err);
            }
        }
        
        // Fallback to older method for browsers that don't support Clipboard API
        const tempTextarea = document.createElement('textarea');
        tempTextarea.value = emailHTML;
        tempTextarea.style.position = 'fixed';
        tempTextarea.style.left = '-9999px';
        document.body.appendChild(tempTextarea);
        tempTextarea.select();
        
        try {
            document.execCommand('copy');
            alert('Email HTML copied to clipboard! You can now paste it into your email client.');
        } catch (err) {
            alert('Failed to copy. Please use the Generate Email Preview button and manually copy the content.');
        }
        
        document.body.removeChild(tempTextarea);
    }
}

// Initialize the quote table when the page loads
document.addEventListener('DOMContentLoaded', () => {
    new QuoteTable();
});

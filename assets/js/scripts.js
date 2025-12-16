jQuery(document).ready(function($) {
    
    // Initialize Google Places Autocomplete for address field (NZ only)
    function initAddressAutocomplete() {
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            var addressInput = document.getElementById('hs_address');
            if (addressInput) {
                var autocomplete = new google.maps.places.Autocomplete(addressInput, {
                    componentRestrictions: { country: 'nz' }, // Restrict to New Zealand only
                    fields: ['formatted_address', 'geometry', 'name'],
                    types: ['address']
                });
                
                autocomplete.addListener('place_changed', function() {
                    var place = autocomplete.getPlace();
                    if (place.formatted_address) {
                        $(addressInput).val(place.formatted_address);
                    }
                });
            }
        }
    }
    
    // Try to initialize immediately
    initAddressAutocomplete();
    
    // Also try after a short delay in case Google Maps API is still loading
    setTimeout(initAddressAutocomplete, 1000);
    
    // Handle contact form submission
    $('#hs-crm-contact-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $messages = $('.hs-crm-form-messages');
        var $submitBtn = $form.find('.hs-crm-submit-btn');
        
        $submitBtn.prop('disabled', true).text('Submitting...');
        $messages.html('');
        
        $.ajax({
            url: hsCrmAjax.ajaxurl,
            type: 'POST',
            data: $form.serialize() + '&action=hs_crm_submit_form',
            success: function(response) {
                if (response.success) {
                    $messages.html('<div class="hs-crm-success">' + response.data.message + '</div>');
                    $form[0].reset();
                } else {
                    $messages.html('<div class="hs-crm-error">' + response.data.message + '</div>');
                }
            },
            error: function() {
                $messages.html('<div class="hs-crm-error">An error occurred. Please try again.</div>');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text('Submit Enquiry');
            }
        });
    });
    
    // Admin page functionality
    if ($('.hs-crm-admin-wrap').length > 0) {
        
        // Handle status change
        $('.hs-crm-status-select').on('change', function() {
            var $select = $(this);
            var enquiryId = $select.data('enquiry-id');
            var newStatus = $select.val();
            var oldStatus = $select.data('current-status');
            
            if (!newStatus) {
                return;
            }
            
            if (!confirm('Are you sure you want to change the status to "' + newStatus + '"?')) {
                $select.val('');
                return;
            }
            
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hs_crm_update_status',
                    nonce: hsCrmAjax.nonce,
                    enquiry_id: enquiryId,
                    status: newStatus,
                    old_status: oldStatus
                },
                success: function(response) {
                    if (response.success) {
                        // Update status badge
                        var $row = $select.closest('tr');
                        var statusClass = newStatus.toLowerCase().replace(/\s+/g, '-');
                        $row.find('.hs-crm-status-badge')
                            .removeClass()
                            .addClass('hs-crm-status-badge status-' + statusClass)
                            .text(newStatus);
                        
                        // Update current status
                        $select.data('current-status', newStatus);
                        $select.val('');
                        
                        // Show email modal if needed
                        if (response.data.trigger_email) {
                            showEmailModal(response.data.enquiry);
                        } else {
                            alert(response.data.message);
                        }
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred while updating the status.');
                }
            });
        });
        
        // Email modal functionality
        var $modal = $('#hs-crm-email-modal');
        
        function showEmailModal(enquiry) {
            $('#email-enquiry-id').val(enquiry.id);
            $('#email-to').val(enquiry.email);
            $('#email-customer').val(enquiry.name + ' - ' + enquiry.phone);
            
            // Reset quote table to one row
            $('#quote-items-body').html(getQuoteItemRowHtml());
            calculateQuoteTotals();
            
            $modal.fadeIn();
        }
        
        $('.hs-crm-modal-close').on('click', function() {
            $modal.fadeOut();
        });
        
        $(window).on('click', function(e) {
            if ($(e.target).is('#hs-crm-email-modal')) {
                $modal.fadeOut();
            }
        });
        
        // Quote table functionality
        function getQuoteItemRowHtml() {
            return '<tr class="quote-item-row">' +
                '<td><input type="text" class="quote-description" placeholder="e.g., Interior wall painting"></td>' +
                '<td><input type="number" class="quote-cost" placeholder="0.00" step="0.01" min="0"></td>' +
                '<td class="quote-gst">$0.00</td>' +
                '<td><button type="button" class="remove-quote-item button">×</button></td>' +
                '</tr>';
        }
        
        $('#add-quote-item').on('click', function() {
            $('#quote-items-body').append(getQuoteItemRowHtml());
        });
        
        $(document).on('click', '.remove-quote-item', function() {
            var $tbody = $('#quote-items-body');
            if ($tbody.find('.quote-item-row').length > 1) {
                $(this).closest('.quote-item-row').remove();
                calculateQuoteTotals();
            } else {
                alert('You must have at least one quote item.');
            }
        });
        
        $(document).on('input', '.quote-cost', function() {
            var cost = parseFloat($(this).val()) || 0;
            var gst = cost * 0.15;
            $(this).closest('tr').find('.quote-gst').text('$' + gst.toFixed(2));
            calculateQuoteTotals();
        });
        
        function calculateQuoteTotals() {
            var subtotal = 0;
            var totalGst = 0;
            
            $('.quote-item-row').each(function() {
                var cost = parseFloat($(this).find('.quote-cost').val()) || 0;
                var gst = cost * 0.15;
                subtotal += cost;
                totalGst += gst;
            });
            
            var grandTotal = subtotal + totalGst;
            
            $('#quote-subtotal').text('$' + subtotal.toFixed(2));
            $('#quote-total-gst').text('$' + totalGst.toFixed(2));
            $('#quote-total').html('<strong>$' + grandTotal.toFixed(2) + '</strong>');
        }
        
        // Handle email form submission
        $('#hs-crm-email-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var quoteItems = [];
            
            $('.quote-item-row').each(function() {
                var description = $(this).find('.quote-description').val();
                var cost = $(this).find('.quote-cost').val();
                
                if (description && cost) {
                    quoteItems.push({
                        description: description,
                        cost: cost
                    });
                }
            });
            
            var formData = {
                action: 'hs_crm_send_email',
                nonce: hsCrmAjax.nonce,
                enquiry_id: $('#email-enquiry-id').val(),
                subject: $('#email-subject').val(),
                message: $('#email-message').val(),
                quote_items: quoteItems
            };
            
            $.ajax({
                url: hsCrmAjax.ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        $modal.fadeOut();
                        location.reload(); // Reload to update the table
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred while sending the email.');
                }
            });
        });
    }
});

/* ============================================
   FITZONE GYM - MAIN JAVASCRIPT
   ============================================ */

// ============================================
// Theme Toggle (Dark / Light Mode)
// ============================================
function toggleTheme() {
    var current = document.documentElement.getAttribute('data-theme') || 'dark';
    var next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    // Persist in cookie so PHP can read it on next page load (no flash)
    document.cookie = 'fitzone_theme=' + next + '; path=/; max-age=31536000; SameSite=Lax';
}

// ============================================
// Sidebar Toggle (Mobile)
// ============================================
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');
    if (sidebar) sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('show');
}

// Close sidebar on overlay click
document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'sidebar-overlay') {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('show');
    }
});

// Close sidebar on clicking outside (legacy)
document.addEventListener('click', function(e) {
    var sidebar = document.querySelector('.sidebar');
    var hamburger = document.querySelector('.hamburger');
    if (sidebar && sidebar.classList.contains('open') && !sidebar.contains(e.target) && hamburger && !hamburger.contains(e.target)) {
        sidebar.classList.remove('open');
        var overlay = document.getElementById('sidebar-overlay');
        if (overlay) overlay.classList.remove('show');
    }
});

// ============================================
// Modal Management
// ============================================
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Close modal on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
        document.body.style.overflow = '';
    }
});

// ============================================
// Alert Auto-dismiss
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(function() { alert.remove(); }, 300);
        }, 5000);
    });
});

// ============================================
// Table Search
// ============================================
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(function(row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

// ============================================
// Delete Confirmation
// ============================================
function confirmDelete(url, name) {
    if (confirm('Are you sure you want to delete "' + name + '"? This action cannot be undone.')) {
        window.location.href = url;
    }
}

// ============================================
// Form Validation
// ============================================
function validateForm(formId) {
    const form = document.getElementById(formId);
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(function(field) {
        if (!field.value.trim()) {
            field.style.borderColor = '#EF476F';
            isValid = false;
        } else {
            field.style.borderColor = '';
        }
    });

    return isValid;
}

// ============================================
// Image Preview
// ============================================
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ============================================
// Date Formatting
// ============================================
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

// ============================================
// Currency Formatting
// ============================================
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toLocaleString('en-IN');
}

// ============================================
// Print Invoice
// ============================================
function printInvoice(invoiceId) {
    const invoice = document.getElementById(invoiceId);
    const printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>Invoice</title>');
    printWindow.document.write('<style>body{font-family:Arial,sans-serif;padding:40px;color:#333;}table{width:100%;border-collapse:collapse;margin:20px 0;}th,td{padding:10px;border:1px solid #ddd;text-align:left;}th{background:#f5f5f5;}.text-right{text-align:right;}.brand{font-size:24px;font-weight:bold;color:#E63946;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(invoice.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// ============================================
// Export Table to CSV
// ============================================
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    let csv = [];

    rows.forEach(function(row) {
        const cols = row.querySelectorAll('td, th');
        let rowData = [];
        cols.forEach(function(col) {
            let text = col.innerText.replace(/,/g, '');
            rowData.push('"' + text + '"');
        });
        csv.push(rowData.join(','));
    });

    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '.csv';
    link.click();
}

// ============================================
// Login Tab Switching
// ============================================
function switchLoginTab(tab) {
    document.querySelectorAll('.login-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.login-form').forEach(f => f.style.display = 'none');

    document.querySelector('[data-tab="' + tab + '"]').classList.add('active');
    document.getElementById(tab + '-form').style.display = 'block';
}

// ============================================
// AJAX Helper
// ============================================
function ajaxRequest(url, method, data, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                callback(response);
            } catch (e) {
                callback({ success: false, message: 'Server error' });
            }
        }
    };
    xhr.send(data);
}

// ============================================
// Attendance Check-in/out via AJAX
// ============================================
function checkIn(memberId) {
    ajaxRequest('api/attendance.php', 'POST', 'action=checkin&member_id=' + memberId, function(res) {
        if (res.success) {
            location.reload();
        } else {
            alert(res.message);
        }
    });
}

function checkOut(attendanceId) {
    ajaxRequest('api/attendance.php', 'POST', 'action=checkout&attendance_id=' + attendanceId, function(res) {
        if (res.success) {
            location.reload();
        } else {
            alert(res.message);
        }
    });
}

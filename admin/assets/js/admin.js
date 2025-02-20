// Operasyonel verimlilik için event yönetimi
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    setupAjaxInterceptors();
});

// Event listener initialization
function initializeEventListeners() {
    const uploadForm = document.getElementById('menuUploadForm');
    if(uploadForm) {
        uploadForm.addEventListener('submit', handleMenuUpload);
    }
}

// AJAX interceptors for global error handling
function setupAjaxInterceptors() {
    document.addEventListener('ajaxStart', showLoader);
    document.addEventListener('ajaxComplete', hideLoader);
}

// Menu operations
async function handleMenuUpload(e) {
    e.preventDefault();
    
    try {
        const formData = new FormData(e.target);
        const response = await fetch('ajax/upload_menu.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if(result.success) {
            showNotification('success', 'Menü başarıyla yüklendi');
            location.reload();
        } else {
            showNotification('error', result.message);
        }
    } catch(error) {
        console.error('Upload error:', error);
        showNotification('error', 'Bir hata oluştu');
    }
}

async function toggleMenuStatus(menuId) {
    try {
        const response = await fetch('ajax/toggle_menu.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: menuId })
        });
        
        const result = await response.json();
        
        if(!result.success) {
            showNotification('error', result.message);
            location.reload();
        }
    } catch(error) {
        console.error('Toggle error:', error);
        showNotification('error', 'Bir hata oluştu');
    }
}

async function deleteMenu(menuId) {
    if(!confirm('Bu menüyü silmek istediğinizden emin misiniz?')) {
        return;
    }
    
    try {
        const response = await fetch('ajax/delete_menu.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: menuId })
        });
        
        const result = await response.json();
        
        if(result.success) {
            showNotification('success', 'Menü başarıyla silindi');
            location.reload();
        } else {
            showNotification('error', result.message);
        }
    } catch(error) {
        console.error('Delete error:', error);
        showNotification('error', 'Bir hata oluştu');
    }
}

// UI utilities
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function showLoader() {
    const loader = document.createElement('div');
    loader.className = 'loader';
    document.body.appendChild(loader);
}

function hideLoader() {
    const loader = document.querySelector('.loader');
    if(loader) {
        loader.remove();
    }
}
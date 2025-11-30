/**
 * Category Management JavaScript
 */

// Load categories
async function loadCategories() {
    try {
        const response = await fetch('../actions/fetch_category_action.php');
        const result = await response.json();
        
        if (result.success) {
            displayCategories(result.data);
        } else {
            showAlert('Failed to load categories', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('An error occurred while loading categories', 'error');
    }
}

// Display categories in table
function displayCategories(categories) {
    const tbody = document.getElementById('categoriesTableBody');
    
    if (categories.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-12 text-center text-muted-foreground">
                    <div class="flex flex-col items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-inbox">
                            <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/>
                            <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
                        </svg>
                        <span>No categories found</span>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = categories.map(cat => `
        <tr class="hover:bg-secondary/50 transition-colors">
            <td class="px-4 py-3 text-sm font-medium text-foreground">${escapeHtml(cat.cat_name)}</td>
            <td class="px-4 py-3 text-sm text-muted-foreground">${escapeHtml(cat.cat_description || '-')}</td>
            <td class="px-4 py-3 text-sm text-muted-foreground">${escapeHtml(cat.cat_icon || '-')}</td>
            <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                <button onclick="editCategory(${cat.cat_id})" 
                            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-8 px-3 border border-border bg-background hover:bg-secondary hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit-2 mr-1.5">
                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                        </svg>
                        Edit
                </button>
                <button onclick="deleteCategory(${cat.cat_id})" 
                            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-8 px-3 border border-destructive/50 bg-destructive/10 text-destructive hover:bg-destructive hover:text-destructive-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2 mr-1.5">
                            <path d="M3 6h18"/>
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                        </svg>
                        Delete
                </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Show add category modal
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Category';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryModal').classList.remove('hidden');
    document.getElementById('categoryModal').classList.add('flex');
}

// Edit category
async function editCategory(catId) {
    try {
        const response = await fetch('../actions/fetch_category_action.php');
        const result = await response.json();
        
        if (result.success) {
            const category = result.data.find(c => c.cat_id == catId);
            if (category) {
                document.getElementById('modalTitle').textContent = 'Edit Category';
                document.getElementById('categoryId').value = category.cat_id;
                document.getElementById('name').value = category.cat_name;
                document.getElementById('description').value = category.cat_description || '';
                document.getElementById('icon').value = category.cat_icon || '';
                document.getElementById('categoryModal').classList.remove('hidden');
                document.getElementById('categoryModal').classList.add('flex');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Failed to load category details', 'error');
    }
}

// Delete category
async function deleteCategory(catId) {
    if (!confirm('Are you sure you want to delete this category? This will also delete all associated venues.')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('cat_id', catId);
        
        const response = await fetch('../actions/delete_category_action.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            loadCategories();
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('An error occurred while deleting category', 'error');
    }
}

// Save category (add or update)
document.getElementById('categoryForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const categoryId = document.getElementById('categoryId').value;
    const isUpdate = categoryId !== '';
    const action = isUpdate ? '../actions/update_category_action.php' : '../actions/add_category_action.php';
    
    const formData = new FormData(this);
    if (isUpdate) {
        formData.append('cat_id', categoryId);
    }
    
    try {
        const response = await fetch(action, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            closeModal();
            loadCategories();
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('An error occurred while saving category', 'error');
    }
});

// Close modal
function closeModal() {
    document.getElementById('categoryModal').classList.add('hidden');
    document.getElementById('categoryModal').classList.remove('flex');
    document.getElementById('categoryForm').reset();
}

// Show alert
function showAlert(message, type) {
    const alertDiv = document.getElementById('alertMessage');
    const isSuccess = type === 'success';
    alertDiv.className = `mb-4 rounded-md p-3 text-sm ${isSuccess ? 'bg-green-950/30 border border-green-900/50 text-green-400' : 'bg-red-950/30 border border-red-900/50 text-red-400'}`;
    alertDiv.innerHTML = `
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-${isSuccess ? 'check-circle' : 'alert-circle'}">
                ${isSuccess ? '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>' : '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>'}
            </svg>
            <span>${escapeHtml(message)}</span>
        </div>
    `;
    alertDiv.classList.remove('hidden');
    
    setTimeout(() => {
        alertDiv.classList.add('hidden');
    }, 5000);
}

// HTML escape function
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

// Load categories on page load
document.addEventListener('DOMContentLoaded', loadCategories);


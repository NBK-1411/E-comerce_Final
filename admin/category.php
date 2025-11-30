<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/category_controller.php');

// Check if admin
require_admin();

// Get user info for header
$user = get_user();
if (!$user || !is_array($user)) {
    $user = ['name' => 'Admin', 'email' => ''];
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Category Management - Admin - Go Outside</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
* { font-family: 'Inter', 'Segoe UI', sans-serif; }
:root {
    --background: #0a0a0a;
    --foreground: #ffffff;
    --muted: #9b9ba1;
    --muted-foreground: #9b9ba1;
    --border: rgba(39, 39, 42, 0.7);
    --accent: #FF6B35;
    --accent-hover: #ff8c66;
    --card: #1a1a1a;
    --secondary: #1e1e1e;
    --secondary-foreground: #d4d4d8;
    --destructive: #ef4444;
    --destructive-foreground: #ffffff;
    }
body { background: var(--background); color: var(--foreground); }
    </style>
</head>

<body>
<!-- Admin Header -->
<header class="sticky top-0 z-50 border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
    <div class="container mx-auto flex h-16 items-center justify-between px-4">
        <div class="flex items-center gap-4">
            <a href="../index.php" class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin text-white">
                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <span class="font-bold text-foreground">Go Outside</span>
            </a>
            <span class="hidden rounded-md bg-destructive px-2.5 py-0.5 text-xs font-semibold text-destructive-foreground sm:inline-flex">
                Admin
            </span>
        </div>

        <div class="flex items-center gap-2">
            <a href="dashboard.php" class="inline-flex h-10 items-center justify-center rounded-md border border-border bg-background px-4 text-sm font-medium transition-colors hover:bg-secondary hover:text-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left mr-2">
                    <path d="m12 19-7-7 7-7"/>
                    <path d="M19 12H5"/>
                </svg>
                Back to Dashboard
            </a>
            <div class="relative">
                <button class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-border bg-background text-sm font-medium transition-colors hover:bg-secondary hover:text-foreground">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-secondary text-sm font-medium text-secondary-foreground">
                        <?php echo strtoupper(substr($user['name'] ?? 'AD', 0, 2)); ?>
                    </div>
                </button>
            </div>
        </div>
    </div>
</header>

<main class="container mx-auto px-4 py-6">
    <!-- Welcome Section -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-foreground">Category Management</h1>
        <p class="text-muted-foreground">Manage venue categories</p>
                </div>

                    <!-- Alert Message -->
    <div id="alertMessage" class="hidden mb-4 rounded-md p-3 text-sm" role="alert"></div>

                    <!-- Add Category Button -->
    <div class="mb-6">
        <button onclick="showAddModal()" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 px-4 py-2 bg-accent text-white hover:bg-accent-hover">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus mr-2">
                <path d="M5 12h14"/>
                <path d="M12 5v14"/>
            </svg>
            Add New Category
                        </button>
                    </div>

                    <!-- Categories Table -->
    <div class="rounded-lg border border-border bg-card">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                                <thead>
                        <tr class="border-b border-border">
                            <th class="px-4 py-3 text-left text-sm font-semibold text-foreground">Name</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-foreground">Description</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-foreground">Icon</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-foreground">Actions</th>
                                    </tr>
                                </thead>
                    <tbody id="categoriesTableBody" class="divide-y divide-border">
                                    <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-muted-foreground">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-loader-2 animate-spin">
                                        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                                    </svg>
                                    <span>Loading categories...</span>
                                </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
</main>

    <!-- Category Modal -->
<div id="categoryModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(4px);" onclick="if(event.target === this) closeModal();">
    <div class="relative w-full max-w-lg rounded-lg border border-border bg-card p-6 shadow-lg" onclick="event.stopPropagation();">
        <div class="mb-6 flex items-center justify-between">
            <h3 id="modalTitle" class="text-xl font-semibold text-foreground">Add New Category</h3>
            <button onclick="closeModal()" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:text-foreground hover:bg-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                    <path d="M18 6 6 18"/>
                    <path d="M6 6l12 12"/>
                </svg>
                </button>
            </div>

        <form id="categoryForm" class="space-y-4">
                <input type="hidden" id="categoryId">
                
            <div>
                <label for="name" class="block text-sm font-medium text-foreground mb-2">Category Name *</label>
                    <input type="text" id="name" name="name" required
                       class="flex h-10 w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:ring-offset-background"
                           placeholder="e.g., Event Spaces">
                </div>

            <div>
                <label for="description" class="block text-sm font-medium text-foreground mb-2">Description</label>
                    <textarea id="description" name="description" rows="3"
                          class="flex min-h-[80px] w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:ring-offset-background"
                              placeholder="Brief description of the category"></textarea>
                </div>

            <div>
                <label for="icon" class="block text-sm font-medium text-foreground mb-2">Icon Class</label>
                    <input type="text" id="icon" name="icon"
                       class="flex h-10 w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:ring-offset-background"
                           placeholder="e.g., icon-home">
                <p class="mt-1 text-xs text-muted-foreground">Optional: Icon class name</p>
                </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 px-4 py-2 bg-accent text-white hover:bg-accent-hover">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-save mr-2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Save Category
                    </button>
                <button type="button" onclick="closeModal()" class="inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 px-4 py-2 border border-border bg-background hover:bg-secondary hover:text-foreground">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../public/js/category.js"></script>
</body>
</html>

<?php
require_once(__DIR__ . '/../settings/core.php');

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ../index.php');
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - Go Outside</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
* {
    font-family: 'Inter', 'Segoe UI', sans-serif;
}
:root {
    /* Dark theme (default) */
    --bg-primary: #0a0a0a;
    --bg-secondary: #1a1a1a;
    --bg-card: #1a1a1a;
    --text-primary: #ffffff;
    --text-secondary: #9b9ba1;
    --text-muted: #9b9ba1;
    --border-color: rgba(39, 39, 42, 0.7);
    --accent: #FF6B35;
    --accent-hover: #ff8c66;
}

[data-theme="light"] {
    /* Light theme */
    --bg-primary: #ffffff;
    --bg-secondary: #f5f5f5;
    --bg-card: #ffffff;
    --text-primary: #0a0a0a;
    --text-secondary: #525252;
    --text-muted: #737373;
    --border-color: rgba(229, 229, 229, 0.8);
}

body {
    background-color: var(--bg-primary);
    color: var(--text-primary);
    transition: background-color 0.3s ease, color 0.3s ease;
}
</style>
</head>

<body>
<div class="min-h-screen flex">
    <!-- Left Side - Image with Quote (Hidden on mobile, visible on desktop) -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-[#FF6B35] via-[#ff8c66] to-[#ff5518]">
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&q=80')] bg-cover bg-center opacity-20"></div>
        <div class="relative z-10 flex flex-col justify-center items-center p-12 text-white">
            <div class="max-w-md">
                <div class="mb-8">
                    <h2 class="text-4xl font-bold mb-4">Welcome Back!</h2>
                    <p class="text-xl text-white/90 leading-relaxed">
                        "Every great journey begins with a single step. Your next adventure in Ghana awaits."
                    </p>
                </div>
                <div class="mt-12 pt-8 border-t border-white/20">
                    <p class="text-sm text-white/80 italic">— Discover amazing venues and experiences</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="border rounded-xl p-8 shadow-2xl"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold mb-2" style="color: var(--text-primary);">Welcome Back</h1>
                    <p class="text-sm" style="color: var(--text-secondary);">Login to your account</p>
                </div>

            <div id="alertMessage" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

            <form id="loginForm" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--text-primary);">Email Address</label>
                    <input type="email" id="email" name="email" 
                           class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                           style="background-color: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary);"
                           placeholder-style="color: var(--text-secondary);"
                           onfocus="this.style.borderColor='var(--accent)'; this.style.boxShadow='0 0 0 2px rgba(255, 107, 53, 0.2)'"
                           onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'" 
                           placeholder="Enter your email" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--text-primary);">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" 
                               class="w-full px-4 py-3 pr-12 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                               style="background-color: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary);"
                               placeholder-style="color: var(--text-secondary);"
                               onfocus="this.style.borderColor='var(--accent)'; this.style.boxShadow='0 0 0 2px rgba(255, 107, 53, 0.2)'"
                               onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'"
                               placeholder="Enter your password" required>
                        <button type="button" id="togglePassword" class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors"
                            style="color: var(--text-secondary);" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" id="loginBtn" class="w-full font-semibold py-3 px-4 rounded-lg transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed"
                    style="background-color: var(--accent); color: #ffffff;"
                    onmouseover="this.style.backgroundColor='var(--accent-hover)'" onmouseout="this.style.backgroundColor='var(--accent)'">
                    <span id="btnText">Login</span>
                    <span id="btnLoader" class="hidden">
                        <svg class="inline-block w-4 h-4 animate-spin mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Logging in...
                    </span>
                </button>
            </form>

            <div class="mt-6 text-center space-y-2">
                <p class="text-sm" style="color: var(--text-secondary);">
                    Don't have an account? 
                    <a href="register.php" class="font-medium transition-colors" style="color: var(--accent);" onmouseover="this.style.color='var(--accent-hover)'" onmouseout="this.style.color='var(--accent)'">Register here</a>
                </p>
                <p class="text-sm" style="color: var(--text-secondary);">
                    <a href="../index.php" class="transition-colors inline-flex items-center gap-1"
                        style="color: var(--text-secondary);" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Back to Home
                    </a>
                </p>
            </div>
        </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script>
// Toggle password visibility
$('#togglePassword').click(function() {
    const passwordField = $('#password');
    const eyeIcon = $('#eyeIcon');
    const eyeOffIcon = $('#eyeOffIcon');
    
    if (passwordField.attr('type') === 'password') {
        passwordField.attr('type', 'text');
        eyeIcon.addClass('hidden');
        eyeOffIcon.removeClass('hidden');
    } else {
        passwordField.attr('type', 'password');
        eyeIcon.removeClass('hidden');
        eyeOffIcon.addClass('hidden');
    }
});

// Show alert message
function showAlert(message, type) {
    const alertDiv = $('#alertMessage');
    alertDiv.removeClass('hidden alert-success alert-danger');
    alertDiv.addClass('alert-' + type);
    
    if (type === 'success') {
        alertDiv.css({
            'background': 'rgba(34, 197, 94, 0.1)',
            'border': '1px solid rgba(34, 197, 94, 0.3)',
            'color': '#4ade80'
        });
    } else {
        alertDiv.css({
            'background': 'rgba(239, 68, 68, 0.1)',
            'border': '1px solid rgba(239, 68, 68, 0.3)',
            'color': '#f87171'
        });
    }
    
    alertDiv.html(message);
    alertDiv.removeClass('hidden');
}

// Login form submission
$('#loginForm').submit(function(e) {
    e.preventDefault();
    
    const btnText = $('#btnText');
    const btnLoader = $('#btnLoader');
    const loginBtn = $('#loginBtn');
    
    // Disable button and show loader
    loginBtn.prop('disabled', true);
    btnText.hide();
    btnLoader.removeClass('hidden');
    
    const formData = $(this).serialize();
    
    $.ajax({
        url: '../actions/login_action.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(result) {
            if (result.success) {
                showAlert(result.message, 'success');
                setTimeout(function() {
                    // Redirect based on user role
                    // Role 1 = Admin, Role 2 = Customer, Role 3 = Venue Owner
                    if (result.user && result.user.role == 1) {
                        window.location.href = '../admin/dashboard.php';
                    } else {
                        window.location.href = '../index.php';
                    }
                }, 1000);
            } else {
                showAlert(result.message, 'danger');
                loginBtn.prop('disabled', false);
                btnText.show();
                btnLoader.addClass('hidden');
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'danger');
            loginBtn.prop('disabled', false);
            btnText.show();
            btnLoader.addClass('hidden');
        }
    });
});
</script>
</body>
</html>

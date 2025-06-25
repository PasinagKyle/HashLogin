// Function to switch between login and register forms, and show forgot password
function showForm(formType) {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const forgotForm = document.getElementById('forgotForm');
    const loginTab = document.querySelector('.tab-btn:nth-child(1)');
    const registerTab = document.querySelector('.tab-btn:nth-child(2)');
    
    // Hide all forms
    loginForm.classList.remove('active');
    registerForm.classList.remove('active');
    forgotForm.classList.remove('active');
    
    if (formType === 'login') {
        loginForm.classList.add('active');
        loginTab.classList.add('active');
        registerTab.classList.remove('active');
    } else if (formType === 'register') {
        registerForm.classList.add('active');
        registerTab.classList.add('active');
        loginTab.classList.remove('active');
    } else if (formType === 'forgot') {
        forgotForm.classList.add('active');
        // Hide tabs when showing forgot password form
        loginTab.classList.remove('active');
        registerTab.classList.remove('active');
    }
}

window.addEventListener('DOMContentLoaded', function() {
    // No need to call moveTabIndicator here
});

// Handle login form submission
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('username', document.getElementById('loginUsername').value);
    formData.append('password', document.getElementById('loginPassword').value);
    
    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('loginMessage');
        messageDiv.textContent = data.message;
        messageDiv.className = 'message ' + (data.success ? 'success' : 'error') + ' fade-in-initial';
        
        if (data.success) {
            // Redirect to dashboard after successful login
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('loginMessage').textContent = 'An error occurred. Please try again.';
        document.getElementById('loginMessage').className = 'message error';
    });
});

// Handle register form submission
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('username', document.getElementById('registerUsername').value);
    formData.append('email', document.getElementById('registerEmail').value);
    formData.append('password', document.getElementById('registerPassword').value);
    formData.append('confirm_password', document.getElementById('confirmPassword').value);
    
    // Show loading state
    const submitBtn = document.querySelector('#registerForm button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Sending...';
    submitBtn.disabled = true;
    
    fetch('register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('registerMessage');
        
        if (data.success) {
            // Show success message with email icon and better styling
            messageDiv.innerHTML = `
                <div class="success-message">
                    <div class="success-icon">📧</div>
                    <div class="success-content">
                        <h4>Registration Successful!</h4>
                        <p>We've sent a verification email to <strong>${document.getElementById('registerEmail').value}</strong></p>
                        <div class="email-steps">
                            <div class="step">1. Check your email inbox</div>
                            <div class="step">2. Click the verification link</div>
                            <div class="step">3. Come back here to login</div>
                        </div>
                        <div class="email-tips">
                            <small>💡 Don't see the email? Check your spam folder</small>
                        </div>
                    </div>
                </div>
            `;
            messageDiv.className = 'message success';
            
            // Clear form
            document.getElementById('registerForm').reset();
            
            // Switch to login after 5 seconds
            setTimeout(() => {
                showForm('login');
                document.getElementById('registerMessage').innerHTML = '';
                document.getElementById('registerMessage').className = 'message';
            }, 5000);
        } else {
            messageDiv.textContent = data.message;
            messageDiv.className = 'message error';
        }
        
        // Reset button
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('registerMessage').textContent = 'An error occurred. Please try again.';
        document.getElementById('registerMessage').className = 'message error';
        
        // Reset button
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

// Handle forgot password form submission
document.getElementById('forgotForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('email', document.getElementById('forgotEmail').value);
    
    // Show loading state
    const submitBtn = document.querySelector('#forgotForm button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Sending...';
    submitBtn.disabled = true;
    
    fetch('forgot_password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('forgotMessage');
        messageDiv.textContent = data.message;
        messageDiv.className = 'message ' + (data.success ? 'success' : 'error');
        
        if (data.success) {
            // Clear form
            document.getElementById('forgotForm').reset();
            
            // Switch to login after 3 seconds
            setTimeout(() => {
                showForm('login');
                document.getElementById('forgotMessage').textContent = '';
                document.getElementById('forgotMessage').className = 'message';
            }, 3000);
        }
        
        // Reset button
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('forgotMessage').textContent = 'An error occurred. Please try again.';
        document.getElementById('forgotMessage').className = 'message error';
        
        // Reset button
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

// Show/hide password for login
const loginPassword = document.getElementById('loginPassword');
const toggleLoginPassword = document.getElementById('toggleLoginPassword');
const loginEye = document.getElementById('loginEye');

if (toggleLoginPassword) {
    toggleLoginPassword.addEventListener('click', function() {
        if (loginPassword.type === 'password') {
            loginPassword.type = 'text';
            loginEye.textContent = '🙈';
            toggleLoginPassword.setAttribute('aria-label', 'Hide password');
        } else {
            loginPassword.type = 'password';
            loginEye.textContent = '👁️';
            toggleLoginPassword.setAttribute('aria-label', 'Show password');
        }
    });
} 
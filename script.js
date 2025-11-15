// 1. The Toggle Function (Switches between Login and Register)
function toggleMode() {
    const title = document.getElementById('formTitle');
    const submitBtn = document.getElementById('submitBtn');
    const toggleBtn = document.getElementById('toggleBtn');
    const toggleText = document.getElementById('toggleText');
    const confirmGroup = document.getElementById('confirmPassGroup');
    const actionInput = document.getElementById('actionType');
    const messageBox = document.getElementById('message');

    messageBox.style.display = 'none';

    if (actionInput.value === 'login') {
        actionInput.value = 'register';
        title.innerText = 'Register';
        submitBtn.innerText = 'Register Now';
        toggleText.innerText = 'Already have an account?';
        toggleBtn.innerText = 'Login here';
        confirmGroup.style.display = 'block';
    } else {
        actionInput.value = 'login';
        title.innerText = 'Login';
        submitBtn.innerText = 'Login';
        toggleText.innerText = 'Don\'t have an account?';
        toggleBtn.innerText = 'Register here';
        confirmGroup.style.display = 'none';
    }
}

// 2. The AJAX Listener (Intercepts the form submit)
document.getElementById('authForm').addEventListener('submit', function(e) {
    e.preventDefault(); // STOP the page from reloading!

    const formData = new FormData(this);
    const messageBox = document.getElementById('message');

    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // Expect a JSON response (the "memo")
    .then(data => {
        // Show the message box
        messageBox.style.display = 'block';
        messageBox.innerText = data.message;

        // Style it based on success or error
        if (data.status === 'success') {
            messageBox.className = 'success';
            if (data.redirect) {
                setTimeout(() => { window.location.href = data.redirect; }, 1000);
            }
        } else {
            messageBox.className = 'error';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        messageBox.style.display = 'block';
        messageBox.className = 'error';
        messageBox.innerText = "An unexpected error occurred.";
    });
});


// --- 3. NEW: Show/Hide Password Logic (Simplified) ---

// This is our helper function
function setupPasswordToggle(toggleId, inputId) {
    const toggleElement = document.getElementById(toggleId);
    const inputElement = document.getElementById(inputId);

    // This "if" check is key: it won't run if it can't find the elements
    if (toggleElement && inputElement) {
        
        // Event: On mouse click and HOLD
        toggleElement.addEventListener('mousedown', () => {
            inputElement.type = 'text';
        });
        
        // Event: On mouse RELEASE
        toggleElement.addEventListener('mouseup', () => {
            inputElement.type = 'password';
        });
        
        // Event: Safety - if mouse slides off while holding
        toggleElement.addEventListener('mouseleave', () => {
            inputElement.type = 'password';
        });
    }
}

// Since our script tag is at the end of the HTML, the elements
// already exist. We can call the function directly.
setupPasswordToggle('togglePassword', 'password');
setupPasswordToggle('toggleConfirmPassword', 'confirm_password');
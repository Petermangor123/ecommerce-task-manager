// Regex patterns used to validate the registration form fields
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const phoneRegex = /^[0-9+\-\s]{7,15}$/;
const passwordRegex = /^(?=.*\d).{8,}$/; // at least 8 characters and one digit
const form = document.getElementById('register-form');

if (form) {
    form.addEventListener('submit', function (e) {
        // Grab the input elements
        const name = document.getElementById('name');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const contact = document.getElementById('contact');
        // Grab the inline error spans
        const nameError = document.getElementById('name-error');
        const emailError = document.getElementById('email-error');
        const passwordError = document.getElementById('password-error');
        const contactError = document.getElementById('contact-error');
        let valid = true;
        // Clear any previous error messages before re-validating
        nameError.textContent = '';
        emailError.textContent = '';
        passwordError.textContent = '';
        contactError.textContent = '';
        // Name must be at least 2 characters
        if (name.value.trim().length < 2) {
            nameError.textContent = 'Name must contain at least 2 characters.';
            valid = false;
        }
        // Validate email format
        if (!emailRegex.test(email.value)) {
            emailError.textContent = 'Invalid email address.';
            valid = false;
        }
        // Password must be at least 8 characters and contain at least one digit
        if (!passwordRegex.test(password.value)) {
            passwordError.textContent =
                'Password must be at least 8 characters and contain at least one digit.';
            valid = false;
        }
        // Phone number: 7–15 characters, digits, +, - or spaces only
        if (!phoneRegex.test(contact.value)) {
            contactError.textContent =
                'Phone number must be 7 to 15 characters and may contain +, - or spaces.';
            valid = false;
        }
        // Prevent the form from submitting when any field is invalid
        if (!valid) {
            e.preventDefault();
        }
    });
}

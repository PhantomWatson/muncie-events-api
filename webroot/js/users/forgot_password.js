const form = document.getElementById('forgot-password');
const loadingIndicator = document.querySelector('#forgot-password-button i');
const handleSubmit = async function (event) {
    event.preventDefault();
    const email = document.getElementById('forgot-password-field').value;
    const url = 'https://api.muncieevents.com/v1/users/forgot-password';
    loadingIndicator.style.display = 'inline-block';
    const alert = document.getElementById('forgot-password-alert');
    alert.style.visibility = 'hidden';
    const response = await fetch(url, {
        method: 'POST',
        mode: 'cors',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'email=' + encodeURIComponent(email)
    });
    loadingIndicator.style.display = 'none';
    alert.setAttribute('class', response.ok ? 'alert alert-success' : 'alert alert-danger');
    if (response.ok) {
        alert.textContent = 'Email sent to ' + email;
    } else {
        if (response.status === 404) {
            alert.textContent = 'We couldn\'t find an account associated with ' + email;
        } else {
            alert.textContent = 'There was an error sending a message to the email address ' + email;
        }
    }
    console.log(response);
    alert.style.visibility = 'visible';
};
form.addEventListener('submit', handleSubmit);

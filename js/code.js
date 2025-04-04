async function populateUniversities() {
    const universitySelect = document.getElementById('university');
    
    try {
        const response = await fetch('getUniversities.php');
        const data = await response.json();
        
        if (data.success) {
            // Clear existing options
            universitySelect.innerHTML = '';
            
            // Add default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select a university';
            universitySelect.appendChild(defaultOption);
            
            // Add universities from database
            data.universities.forEach(university => {
                const option = document.createElement('option');
                option.value = university.id;
                option.textContent = university.name;
                universitySelect.appendChild(option);
            });
        } else {
            universitySelect.innerHTML = '<option value="">Error loading universities</option>';
            console.error(data.message);
        }
    } catch (error) {
        universitySelect.innerHTML = '<option value="">Failed to load universities</option>';
        console.error('Fetch error:', error);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('.form-box.login');
    const registerForm = document.querySelector('.form-box.register');
    const createForm = document.querySelector('.form-box.create');
    const toggleBox = document.querySelector('.toggle-box');
    
    //initially hide register and create forms, show login form
    registerForm.style.visibility = 'hidden';
    createForm.style.visibility = 'hidden';
    loginForm.style.visibility = 'visible';

    //toggle between login and register forms
    document.querySelector('.form-box.register .formText a').addEventListener('click', function(e) {
        e.preventDefault();
        registerForm.style.visibility = 'hidden';
        createForm.style.visibility = 'hidden';
        loginForm.style.visibility = 'visible';
    });

    document.querySelector('.form-box.login .formText a').addEventListener('click', function(e) {
        e.preventDefault();
        registerForm.style.visibility = 'visible';
        createForm.style.visibility = 'hidden';
        loginForm.style.visibility = 'hidden';
        populateUniversities();

    });

    //show create university form
    document.querySelector('.button2').addEventListener('click', function(e) {
        e.preventDefault();
        registerForm.style.visibility = 'hidden';
        createForm.style.visibility = 'visible';
        loginForm.style.visibility = 'hidden';

    });

    //return to login from create university form
    document.querySelector('.form-box.create .formText a').addEventListener('click', function(e) {
        e.preventDefault();
        registerForm.style.visibility = 'hidden';
        createForm.style.visibility = 'hidden';
        loginForm.style.visibility = 'visible';
    });
});
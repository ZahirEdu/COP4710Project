const urlBase = 'https://zahirgutierrez.com/LAMPAPI'
const extension = 'php';

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('.form-box.login');
    const registerForm = document.querySelector('.form-box.register');
    const createForm = document.querySelector('.form-box.create');
    
    
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
        fetchUniversities();

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

let UID = 0;
let universityID = 0;;
let name = "";
let role = "";
function doLogin() {
    UID = 0;
    universityID = 0;
    name = "";
    role = "";

    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;

    document.getElementById("loginResult").innerHTML = "";

    let tmp = { email: email, password: password };
    let jsonPayload = JSON.stringify(tmp);
    
    let url = urlBase + '/Login.' + extension;

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                let jsonObject = JSON.parse(xhr.responseText);
    
                if (jsonObject.error) {
                    document.getElementById("loginResult").innerHTML = jsonObject.error; // Display the error message from the server
                    return;
                }
    
                UID = jsonObject.UID;
    
                if (UID < 1) {
                    document.getElementById("loginResult").innerHTML = "Login failed. Please try again."; // Fallback for unexpected UID
                    return;
                }
    
                universityID = jsonObject.universityID;
                name = jsonObject.name;
                role = jsonObject.role;
    
                saveCookie();
                document.getElementById("loginResult").innerHTML = "Login successful!";
                window.location.href = "dashboard.html";
            } else {
                document.getElementById("loginResult").innerHTML = "Login failed. Please check your connection.";
            }
        }
    };
    
    try {
        xhr.send(jsonPayload);
    } catch(err) {
        document.getElementById("loginResult").innerHTML = err.message;
    }
}

function doLogout()
{
	UID = 0;
    universityID = 0;
	name = "";
	role = "";
	document.cookie = "name= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
	window.location.href = "index.html";
}

function saveCookie()
{
    let minutes = 20;
    let date = new Date();
    date.setTime(date.getTime()+(minutes*60*1000));

    document.cookie = "name=" + name + "; expires=" + date.toGMTString() + "; path=/";
    document.cookie = document.cookie + "; email=" + email + "; expires=" + date.toGMTString() + "; path=/";
    document.cookie = document.cookie + "; UID=" + UID + "; expires=" + date.toGMTString() + "; path=/";
    document.cookie = document.cookie + "; universityID=" + universityID + "; expires=" + date.toGMTString() + "; path=/";
    document.cookie = document.cookie + "; role=" + role + "; expires=" + date.toGMTString() + "; path=/";
}

function readCookie()
{
	UID = -1;
	let data = document.cookie;
	let splits = data.substring(data.indexOf(';') + 1).split(",");
	for(var i = 0; i < splits.length; i++) 
	{
		let thisOne = splits[i].trim();
		let tokens = thisOne.split("=");
		if( tokens[0] == "name" )
		{
			name = tokens[1];
		}
		else if( tokens[0] == "email" )
		{
			email = tokens[1];
		}
        else if( tokens[0] == "role" )
        {
                role = tokens[1];
        }  
		else if( tokens[0] == "UID" )
		{
			UID = parseInt( tokens[1].trim() );
		}
        else if( tokens[0] == "universityID" )
        {
            universityID = parseInt( tokens[1].trim() );
        }
	}
	
	if( UID < 0 )
	{
		window.location.href = "index.html";
	}
	else
	{
		console.log("name: ", name, "UID: ", UID,"email: ", email,"role: ", role, "universityID: ", universityID);
	}
}


async function fetchUniversities() {
    const universitySelect = document.getElementById('Runiversity');
    const url = urlBase + '/UniversitiesFetch.' + extension; // Corrected URL

    let xhr = new XMLHttpRequest();
    xhr.open("GET", url, true); // Using GET as no data is being sent in the body

    try {
        xhr.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                let jsonObject = JSON.parse(xhr.responseText);

                if (jsonObject.success) {
                    universitySelect.innerHTML = '';
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = 'Select a university';
                    universitySelect.appendChild(defaultOption);

                    jsonObject.universities.forEach(university => {
                        const option = document.createElement('option');
                        option.value = university.universityID;
                        option.textContent = university.name;
                        universitySelect.appendChild(option);
                    });
                } else {
                    universitySelect.innerHTML = '<option value="">Error loading universities</option>';
                    console.error("Error loading universities:", jsonObject.error);
                }
            } else if (this.readyState === 4) {
                // Handle non-200 status codes (errors)
                universitySelect.innerHTML = '<option value="">Error loading universities</option>';
                console.error("Error loading universities (status " + this.status + ")");
            }
        };
        xhr.send(); // No data to send for a GET request
    } catch (err) {
        universitySelect.innerHTML = '<option value="">Failed to load</option>';
        console.error("Error fetching universities:", err.message);
    }
}

function doRegister() {
    let email = document.getElementById("Remail").value;
    let password = document.getElementById("Rpassword").value;
    let name = document.getElementById("Rname").value;
    let universitySelect = document.getElementById("Runiversity");
    let universityID = universitySelect.value;
    let role = 'student'; // Automatically set role to 'student'

    document.getElementById("registrationResult").innerHTML = ""; // Assuming you have a span with this ID for feedback

    let tmp = { email: email, password: password, name: name, role: role, universityID: universityID };
    let jsonPayload = JSON.stringify(tmp);

    let url = urlBase + '/Register.' + extension; // Assuming urlBase and extension are defined

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                let jsonObject = JSON.parse(xhr.responseText);

                if (jsonObject.error) {
                    document.getElementById("registrationResult").innerHTML = jsonObject.error;
                } else if (jsonObject.message === "User created successfully") {
                    document.getElementById("registrationResult").innerHTML = "Registration successful! Redirecting to login...";
                    
                    setTimeout(function() {
                        window.location.reload(); // Or window.location.href = "your_login_page.html";
                    }, 3000);
                } else {
                    document.getElementById("registrationResult").innerHTML = "Registration failed. Please try again.";
                    console.error("Registration error:", jsonObject); // Log unexpected responses
                }
            } else {
                document.getElementById("registrationResult").innerHTML = "Registration failed due to network error.";
                console.error("Registration request failed:", this.status);
            }
        }
    };

    try {
        xhr.send(jsonPayload);
    } catch (err) {
        document.getElementById("registrationResult").innerHTML = "An error occurred during registration.";
        console.error("Error sending registration request:", err.message);
    }
}

function doRegisterUniversity() {
    let name = document.getElementById("Dname").value; // Assuming you have a name field for the admin creating the university
    let email = document.getElementById("Demail").value; // Assuming you have an email field for the admin
    let password = document.getElementById("Dpassword").value; // Assuming you have a password field for the admin
    let uniName = document.getElementById("university").value;
    let description = document.getElementById("description").value;
    let studentCount = document.getElementById("studentCount").value;

    document.getElementById("universityResult").innerHTML = ""; // Assuming you have a span with this ID for feedback

    // Basic validation (you might want more robust validation)
    if (!name || !email || !password || !uniName || !description || !studentCount) {
        document.getElementById("universityResult").innerHTML = "Please fill in all fields.";
        return;
    }

    if (isNaN(studentCount)) {
        document.getElementById("universityResult").innerHTML = "Student Count must be a number.";
        return;
    }

    let tmp = {
        name: name,
        email: email,
        password: password,
        uniName: uniName,
        description: description,
        studentCount: parseInt(studentCount) // Ensure studentCount is an integer
    };

    let jsonPayload = JSON.stringify(tmp);

    let url = urlBase + '/UniversityCreate.' + extension; // Assuming urlBase and extension are defined

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                let jsonObject = JSON.parse(xhr.responseText);

                if (jsonObject.error) {
                    document.getElementById("universityResult").innerHTML = jsonObject.error;
                } else if (jsonObject.message === "University and super admin created successfully") {
                    document.getElementById("universityResult").innerHTML = "University created successfully! Redirecting to login...";
                    setTimeout(function() {
                        window.location.reload(); // Or window.location.href = "your_login_page.html";
                    }, 3000);
                } else {
                    document.getElementById("universityResult").innerHTML = "University creation failed.";
                    console.error("University creation error:", jsonObject); // Log unexpected responses
                }
            } else {
                document.getElementById("universityResult").innerHTML = "University creation failed due to network error.";
                console.error("University creation request failed:", this.status);
            }
        }
    };

    try {
        xhr.send(jsonPayload);
    } catch (err) {
        document.getElementById("universityResult").innerHTML = "An error occurred during university creation.";
        console.error("Error sending university creation request:", err.message);
    }
}
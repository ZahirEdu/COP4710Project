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
                UID = jsonObject.UID;
                
                if (UID < 1) {        
                    document.getElementById("loginResult").innerHTML = "Email or password is incorrect";
                    return;
                }
                
                universityID = jsonObject.universityID;
                name = jsonObject.name;
                role = jsonObject.role;

                saveCookie();
                window.location.href = "dashboard.html";
            } else {
                document.getElementById("loginResult").innerHTML = "Login failed. Please try again.";
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
	document.cookie = "name=" + name + 
    ",email=" + email + 
    ",UID=" + UID + 
    ",universityID=" + 
    universityID + ",role=" + 
    role + ";expires=" + 
    date.toGMTString();
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
                option.value = university.universityID;
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


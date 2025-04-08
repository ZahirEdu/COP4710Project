const urlBase = 'https://zahirgutierrez.com/LAMPAPI'
const extension = 'php';

document.addEventListener('DOMContentLoaded', readCookie);

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
let universityID = 0;
let name = "";
let role = "";
let email = "";


function doLogin() {
    UID = 0;
    universityID = 0;
    name = "";
    role = "";

    email = document.getElementById("email").value; 
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


function saveCookie()
{
    let minutes = 20;
    let date = new Date();
    date.setTime(date.getTime()+(minutes*60*1000));
    
    // Set each cookie separately
    document.cookie = "name=" + encodeURIComponent(name) + "; expires=" + date.toGMTString() + "; path=/";
    document.cookie = "email=" + encodeURIComponent(email) + "; expires=" + date.toGMTString() + "; path=/";
    document.cookie = "UID=" + UID + "; expires=" + date.toGMTString() + "; path=/";
    document.cookie = "universityID=" + universityID + "; expires=" + date.toGMTString() + "; path=/";
    document.cookie = "role=" + encodeURIComponent(role) + "; expires=" + date.toGMTString() + "; path=/";
}

let nameFromCookie;
let emailFromCookie;
let UIDFromCookie;
let universityIDFromCookie;
let roleFromCookie;

function readCookie() {
    const cookies = document.cookie.split(';');
    const userData = {};

    for (let i = 0; i < cookies.length; i++) {
        let cookie = cookies[i].trim();
        if (cookie.includes('=')) {
            let [name, value] = cookie.split('=');
            userData[name] = decodeURIComponent(value); // Decode here
        }
    }

    nameFromCookie = userData.name;
    emailFromCookie = userData.email;
    UIDFromCookie = parseInt(userData.UID); // Convert to number
    universityIDFromCookie = parseInt(userData.universityID); // Convert to number
    roleFromCookie = userData.role;
}

function doLogout() {
    // Clear the cookies by setting their expiration date to the past
    document.cookie = "name=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;";
    document.cookie = "email=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;";
    document.cookie = "UID=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;";
    document.cookie = "universityID=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;";
    document.cookie = "role=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;";

    window.location.href = "index.html";
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

async function fetchEvents() {;
    const UID = UIDFromCookie
    const universityID = universityIDFromCookie

    if (!UID) {
        console.error("User ID (UID) not found in cookie. Cannot fetch events.");
        return null;
    }

    try {
        const url = urlBase + '/EventsFetch.' + extension; // The URL of your PHP script

        const requestOptions = {
            method: 'POST', // Your PHP script expects a POST request
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ UID: UID, universityID: universityID }) // Send UID and universityID in the request body as JSON
        };

        const response = await fetch(url, requestOptions);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return data; // The JSON data fetched from EventsFetch.php
    } catch (error) {
        console.error("Error fetching events:", error);
        return null; // Or handle the error as needed in your application
    }
}

// Example of how to use the fetchEvents function:
async function loadAndDisplayEvents() {
    const eventsData = await fetchEvents();
    if (eventsData && eventsData.approved_events) {
        console.log("Fetched events:", eventsData.approved_events);
        // Now you can process and display the eventsData in your HTML
        console.log("printing events");
        displayEvents(eventsData.approved_events);
    } else {
        console.log("Failed to load events.");
        // Handle the case where fetching events failed
    }
}

function displayEvents(events) {
    const dashboardContainer = document.getElementById('dashboard-container');
    if (dashboardContainer) {
        dashboardContainer.innerHTML = ''; // Clear existing content
        events.forEach(event => {
            const eventCard = document.createElement('div');
            eventCard.dataset.eventId = event.eventID;
            eventCard.classList.add('card');
            eventCard.innerHTML = `
                <div class="card-info">
                    <div class="card-title">
                        <span>${event.name}</span>
                    </div>
                    <div class="card-location">
                        <span style="font-weight: 600;">Location: </span> <span style="font-weight: 500;">${event.locationName}</span>
                    </div>
                    <div class="card-time">
                        <span style="font-weight: 600;">Time: </span> 
                        <span style="font-weight: 500;">${formatDateTime(event.start_time)} - ${formatDateTime(event.end_time)}</span>
                    </div>
                    <div class="card-desc">
                        <span>${event.description}</span>
                    </div>

                    <div class="card-details">
                        <div class="card-cat">
                        </div>
                        <div class="card-contact-email">
                        </div>
                        <div class="card-contact-phone">
                        </div>
                    </div>

                </div>
            `;
            dashboardContainer.appendChild(eventCard);

            eventCard.addEventListener('click', function() {
                const eventId = this.dataset.eventId;
                showEventPopup(event);
            });
        });
    }
}

async function showEventPopup(event) {
    const popup = document.getElementById('eventPopUp');
    
    // Set all the event information
    document.getElementById('event-popup-header').querySelector('h1').textContent = event.name;
    document.getElementById('location').textContent = event.locationName;
    document.getElementById('time').textContent = `${formatDateTime(event.start_time)} - ${formatDateTime(event.end_time)}`;
    document.getElementById('desc').textContent = event.description;
    
    // Set optional fields (check if they exist first)
    if (event.rsoName) {
        document.getElementById('rso').textContent = event.rsoName;
    } else {
        document.getElementById('rso').textContent = 'N/A';
    }
    
    if (event.category) {
        document.getElementById('cat').textContent = event.category;
    } else {
        document.getElementById('cat').textContent = 'N/A';
    }
    
    if (event.contactPhone) {
        document.getElementById('phone').textContent = event.contactPhone;
    } else {
        document.getElementById('phone').textContent = 'N/A';
    }
    
    if (event.contactEmail) {
        document.getElementById('email').textContent = event.contactEmail;
    } else {
        document.getElementById('email').textContent = 'N/A';
    }

    const ratingsResult = await fetchAndCalculateAverageRating(event.eventID);

    const ratingElement = document.querySelector('.event-ratig span:first-child');
    if (ratingElement) {
        ratingElement.textContent = ratingsResult.average;
    }

    const ratingInput = document.getElementById('rating');
    const addRatingBtn = document.getElementById('add-rating');

    const commentInput = document.getElementById('comment-input');
    const addCommentBtn = document.getElementById('add-comment');
    const commentsContainer = document.getElementById('comments-container');

    try {
        const comments = await fetchEventComments(event.eventID);
        displayComments(comments, commentsContainer);
    } catch (error) {
        console.error('Error loading comments:', error);
        commentsContainer.innerHTML = '<p>Error loading comments. Please try again.</p>';
    }
    
    addRatingBtn.addEventListener('click', async () => {
        const ratingValue = parseInt(ratingInput.value);
        const UID = UIDFromCookie; 
        
        if (isNaN(ratingValue) || ratingValue < 1 || ratingValue > 5) {
            alert('Please enter a rating between 1 and 5');
            return;
        }
        
        const result = await submitEventRating(event.eventID, UID, ratingValue);
        
        if (result.success) {
            alert('Rating submitted successfully!');
            // Refresh ratings display
            const ratingsResult = await fetchAndCalculateAverageRating(event.eventID);
            updateRatingDisplay(ratingsResult);
            ratingInput.value = ''; // Clear input
        } else {
            alert('Error: ' + result.message);
        }
    });
    
    addCommentBtn.addEventListener('click', async () => {
        const commentText = commentInput.value.trim();
        const UID = getCurrentUserID(); // Implement this based on your auth system
        
        if (!commentText) {
            alert('Please enter a comment');
            return;
        }
        
        try {
            const result = await submitEventComment(event.eventID, UID, commentText);
            
            if (result.error) {
                alert('Error: ' + result.error);
            } else {
                // Refresh comments after successful submission
                const comments = await fetchEventComments(event.eventID);
                displayComments(comments, commentsContainer);
                commentInput.value = ''; // Clear input
            }
        } catch (error) {
            alert('Failed to submit comment: ' + error.message);
        }
    });
    
    
    // Show the popup
    popup.style.display = 'block';
    
    // Add close button functionality
    document.getElementById('close-popup').onclick = function() {
        popup.style.display = 'none';
    };
    
    // Close when clicking outside the popup
    popup.addEventListener('click', function(e) {
        if (e.target === popup) {
            popup.style.display = 'none';
        }
    });
}

function displayComments(comments, container) {
    // Clear existing comments
    container.innerHTML = '';

    if (comments.length === 0) {
        container.innerHTML = '<p>No comments yet. Be the first to comment!</p>';
        return;
    }

    comments.forEach(comment => {
        const commentElement = document.createElement('div');
        commentElement.classList.add('comment');
        commentElement.innerHTML = `
            <div class="comment-header">
                <div class="comment-header-item">
                    <span>Comment ID: </span><span>${comment.commentID}</span>
                </div>
                <div class="comment-header-item">
                    <span>User ID: </span><span>${formatDateTime(comment.UID)}</span>
                </div>
            </div>
            <div class="comment-body">
                <p>${comment.commentText}</p>
            </div>
            ${comment.updatedAtt !== comment.createdAt ? 
                `<div class="comment-footer">
                    <span>Edited: </span><span>${formatDateTime(comment.updatedAtt)}</span>
                </div>` : ''
            }
        `;
        container.appendChild(commentElement);
    });
}

async function fetchEventComments(eventID) {
    try {
        const response = await fetch('https://zahirgutierrez.com/LAMPAPI/CommentsFetch.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ eventID: eventID })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }

        return data.comments || [];
    } catch (error) {
        console.error('Error fetching comments:', error);
        return []; // Return empty array on error
    }
}


async function fetchAndCalculateAverageRating(eventID) {
    try {
        // Fetch ratings from the API
        const response = await fetch(`https://zahirgutierrez.com/LAMPAPI/RatingsFetch.php?eventID=${eventID}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        // Check if ratings exist
        if (data.status !== 'success' || !data.ratings || data.ratings.length === 0) {
            return { average: 0, count: 0 }; // Return 0 if no ratings
        }
        
        // Calculate average and round up
        const ratings = data.ratings;
        const sum = ratings.reduce((total, rating) => total + rating.rating, 0);
        const average = Math.ceil(sum / ratings.length); // Round UP to nearest whole number
        
        return {
            average: average,
            count: ratings.length,
            ratings: ratings // Optional: include all ratings if needed
        };
        
    } catch (error) {
        console.error('Error fetching ratings:', error);
        return { average: 0, count: 0, error: error.message };
    }
}

async function submitEventRating(eventID, UID, ratingValue) {
    try {
        const response = await fetch('https://zahirgutierrez.com/LAMPAPI/RatingCreate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                eventID: eventID,
                UID: UID,
                rating: ratingValue
            })
        });

        const data = await response.json();
        
        if (data.status === 'success') {
            return { success: true, message: data.message };
        } else {
            return { success: false, message: data.message };
        }
    } catch (error) {
        console.error('Error submitting rating:', error);
        return { success: false, message: 'Failed to submit rating' };
    }
}


async function submitEventComment(eventID, UID, commentText) {
    try {
        const response = await fetch('https://zahirgutierrez.com/LAMPAPI/CommentCreate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                eventID: eventID,
                UID: UID,
                commentText: commentText
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('Error submitting comment:', error);
        return { error: error.message };
    }
}


// Helper function to format datetime (you might want a more robust one)
function formatDateTime(dateTimeString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateTimeString).toLocaleDateString(undefined, options);
}

// Call loadAndDisplayEvents when the page loads (or at the appropriate time)
document.addEventListener('DOMContentLoaded', loadAndDisplayEvents);
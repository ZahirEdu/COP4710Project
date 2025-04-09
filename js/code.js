const urlBase = 'https://zahirgutierrez.com/LAMPAPI'
const extension = 'php';

document.addEventListener('DOMContentLoaded', readCookie);
document.addEventListener('DOMContentLoaded', pendingEventsLoad);


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

function updateDashboardUID() {
    const dashboardHeader = document.querySelector('.dashboard-header');
    if (UIDFromCookie) {
        dashboardHeader.innerHTML = `Dashboard | User ID: ${UIDFromCookie}`;
    } else {
        dashboardHeader.innerHTML = `Dashboard | User ID not available`;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    updateDashboardUID();  
}, false);

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

async function showCreateRsoPopup() {
    const popup = document.getElementById('rso-create-popup');

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

    const ratingElement = document.getElementById('avg-rating');
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
        displayComments(comments, commentsContainer,event.eventID);
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
        const UID = UIDFromCookie;
        
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
                displayComments(comments, commentsContainer,event.eventID);
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



function updateRatingDisplay(ratingsResult) {
    const ratingElement = document.getElementById('avg-rating');    
    if (ratingElement) {
        ratingElement.textContent = ratingsResult.average;
    }
}

function displayComments(comments, container, eventID) {
    // Clear existing comments
    container.innerHTML = '';

    if (comments.length === 0) {
        container.innerHTML = '<p>No comments yet. Be the first to comment!</p>';
        return;
    }

    comments.forEach(comment => {
        const commentElement = document.createElement('div');
        commentElement.classList.add('comment');
        commentElement.dataset.commentId = comment.commentID;

        const showDeleteButton = comment.UID == UIDFromCookie;

        commentElement.innerHTML = `
            <div class="comment-header">
                <div class="comment-header-item">
                    <span>Comment ID: </span><span>${comment.commentID}</span>
                </div>
                <div class="comment-header-item">
                    <span>User ID: </span><span>${comment.UID}</span>
                </div>
            </div>
            <div class="comment-body">
                <p>${comment.commentText}</p>
            </div>
            <div class="comment-footer">
                ${showDeleteButton ? 
                    `<button class="delete-comment" data-comment-id="${comment.commentID}">
                        <i class='bx bx-trash'></i>
                    </button>` : ''
                }
                <span>Updated: </span><span>${formatDateTime(comment.updatedAtt)}</span>
            </div>
        `;
        container.appendChild(commentElement);

        if (showDeleteButton) {
            const deleteBtn = commentElement.querySelector('.delete-comment');
            deleteBtn.addEventListener('click', async () => {
                if (confirm('Are you sure you want to delete this comment?')) {
                    const result = await deleteComment(comment.commentID, UIDFromCookie);
                    
                    if (result.error) {
                        alert('Error: ' + result.error);
                    } else {
                        // Refresh comments after successful deletion
                        const comments = await fetchEventComments(eventID);
                        displayComments(comments, container, eventID);
                        alert('Comment deleted successfully!');
                    }
                }
            });
        }
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

async function deleteComment(commentID, UID) {
    try {
        const response = await fetch('https://zahirgutierrez.com/LAMPAPI/CommentDelete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                commentID: commentID,
                UID: UID
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('Error deleting comment:', error);
        return { error: error.message };
    }
}

async function createRSO(event) {
    event.preventDefault(); // Prevent form submission
    console.log('Creating RSO...');
    // Get form values
    const rsoData = {
        name: document.getElementById('rso-create-name').value,
        description: document.getElementById('rso-create-desc').value,
        universityID: document.getElementById('rso-create-uni-id').value,
        adminID: document.getElementById('rso-create-uid').value,
        status: 'active'
    };

    // Get member emails (filter out empty ones)
    const memberEmails = [
        document.getElementById('rso-create-mem1').value,
        document.getElementById('rso-create-mem2').value,
        document.getElementById('rso-create-mem3').value,
        document.getElementById('rso-create-mem4').value
    ].filter(email => email.trim() !== '');

    try {
        // Step 1: Create the RSO
        const rsoResponse = await fetch('https://zahirgutierrez.com/LAMPAPI/RSOCreate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(rsoData)
        });

        const rsoResult = await rsoResponse.json();
        
        if (rsoResult.status !== 'success') {
            throw new Error(rsoResult.message || 'Failed to create RSO');
        }

        const rsoID = rsoResult.rsoID;
        
        // Step 2: Add members to the RSO
        for (const email of memberEmails) {
            try {
                // Fetch UID for each member email
                const uidResponse = await fetch(`https://zahirgutierrez.com/LAMPAPI/EmailFetch.php?email=${encodeURIComponent(email)}`);
                const uidResult = await uidResponse.json();
                
                if (uidResult.status !== 'success') {
                    console.error(`Failed to find user with email ${email}:`, uidResult.message);
                    continue; // Skip to next member if this one fails
                }

                // Add member to RSO
                const joinData = {
                    rsoID: rsoID,
                    UID: uidResult.UID
                };

                const joinResponse = await fetch('https://zahirgutierrez.com/LAMPAPI/RSOJoin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(joinData)
                });

                const joinResult = await joinResponse.json();
                
                if (joinResult.status !== 'success') {
                    console.error(`Failed to add user ${email} to RSO:`, joinResult.message);
                }
            } catch (error) {
                console.error(`Error processing member ${email}:`, error);
            }
        }

        // Success message
        alert('RSO created successfully with members added!');
        // Optionally close the popup or reset the form
        document.getElementById('close-popup').click();
        
    } catch (error) {
        console.error('Error creating RSO:', error);
        alert('Error creating RSO: ' + error.message);
    }
}


async function editFormSubmit(event) {
    event.preventDefault();

    const commentID = document.getElementById('comment-id').value;
    const commentText = document.getElementById('new-comment-text').value;

    const UID = UIDFromCookie;

    if (!commentID || !commentText || !UID) {
        alert('Please fill in all fields');
        return;
    }

    const data = {
        commentID: parseInt(commentID),
        UID: parseInt(UID),
        commentText: commentText
    };

    try {
        const response = await fetch('https://zahirgutierrez.com/LAMPAPI/CommentEdit.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.error) {
            throw new Error(result.error);
        }

        alert(result.message || 'Comment updated successfully');

        event.target.reset();

    } catch (error) {
        console.error('Error updating comment:', error);
        alert('Failed to update comment: ' + error.message);
    }
}

async function joinRSOSubmit(event) {
    event.preventDefault();
    console.log('Joining RSO...');
    const rsoID = document.getElementById('rso-join-id').value;
    const UID = UIDFromCookie;

    if (!rsoID) {
        alert('Please fill in all fields');
        return;
    }

    const data = {
        rsoID: parseInt(rsoID),
        UID: parseInt(UID)
    };

    try {
        const response = await fetch('https://zahirgutierrez.com/LAMPAPI/RSOJoin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.error) {
            throw new Error(result.error);
        }

        alert(result.message || 'RSO joined successfully');

        event.target.reset();
           
    } catch (error) {
        console.error('Error joining RSO:', error);
        alert('Failed to join RSO: ' + error.message);
    }
}

 async function leaveRSOSubmit() {
    console.log('Leaving RSO...');
    const rsoID = document.getElementById('rso-leave-id').value;
    const UID = UIDFromCookie;

    if (!rsoID) {
        alert('Please fill in all fields');
        return;
    }

    const data = {
        rsoID: parseInt(rsoID),
        UID: parseInt(UID)
    };

    try {
        const response = await fetch('https://zahirgutierrez.com/LAMPAPI/RSOLeave.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.error) {
            throw new Error(result.error);
        }

        alert(result.message || 'RSO Left successfully');

        event.target.reset();
           
    } catch (error) {
        console.error('Error leaving RSO:', error);
        alert('Failed to leave RSO: ' + error.message);
    }
}


document.getElementById('edit-comment-form')?.addEventListener('submit', editFormSubmit);
document.getElementById('join-rso-form')?.addEventListener('submit', joinRSOSubmit);
document.getElementById('create-rso-form')?.addEventListener('submit', createRSO);
document.getElementById('create-event-form')?.addEventListener('submit', createEvent);


function toggleRSOForm() {
    const form = document.getElementById('rso-create-popup');
    const li = document.getElementById('li-rso-create');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        li.style.backgroundColor= '#e09010';
      } else {
        form.style.display = 'none';
        li.style.backgroundColor= '#fca311';
      }
}

function toggleCreateEventForm() {
    const form = document.getElementById('event-create-popup');
    const li = document.getElementById('li-create-event');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        li.style.backgroundColor= '#e09010';
      } else {
        form.style.display = 'none';
        li.style.backgroundColor= '#fca311';
      }
}

function toggleRSOJoinForm() {
    const form = document.getElementById('rso-join-popup');
    const li = document.getElementById('li-rso-join');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        li.style.backgroundColor= '#e09010';
      } else {
        form.style.display = 'none';
        li.style.backgroundColor= '#fca311';
      }
}
function toggleRSOLeaveForm() {
    const form = document.getElementById('rso-leave-popup');
    const li = document.getElementById('li-rso-leave');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        li.style.backgroundColor= '#e09010';
      } else {
        form.style.display = 'none';
        li.style.backgroundColor= '#fca311';
      }
}
function toggleCommentForm() {
    const form = document.getElementById('edit-comment-popup');
    const li = document.getElementById('li-comment');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        li.style.backgroundColor= '#e09010';
      } else {
        form.style.display = 'none';
        li.style.backgroundColor= '#fca311';
      }
}

async function pendingEventsLoad() {
    const element = document.getElementById('superadmin-only');
    const role = roleFromCookie;

    if (role === 'superadmin') {
        element.style.display = 'block'; 
    } else {
        element.style.display = 'none';
    }
}

document.getElementById('rso-create-popup').addEventListener('click', toggleRSOForm);



// Helper function to format datetime (you might want a more robust one)
function formatDateTime(dateTimeString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateTimeString).toLocaleDateString(undefined, options);
}

// Call loadAndDisplayEvents when the page loads (or at the appropriate time)
document.addEventListener('DOMContentLoaded', loadAndDisplayEvents);



async function createEvent(event) {
    event.preventDefault();
    console.log('Creating Event...');
    // Get form values
    const form = event.target;
    const formData = {
        name: document.getElementById('event-create-name').value,
        description: document.getElementById('event-create-desc').value,
        start_time: document.getElementById('event-create-start').value,
        end_time: document.getElementById('event-create-end').value,
        address: document.getElementById('event-create-loc').value,
        contactPhone: document.getElementById('event-create-phone').value,
        contactEmail: document.getElementById('event-create-email').value,
        eventType: document.getElementById('event-create-type').value,
        rsoID: document.getElementById('event-create-rso').value || null,
        UID: UIDFromCookie,
        universityID: universityIDFromCookie
    };

    // Validate required fields
    if (!formData.name || !formData.description || !formData.start_time || 
        !formData.end_time || !formData.address || !formData.contactPhone || 
        !formData.contactEmail || !formData.eventType || !formData.UID || 
        !formData.universityID) {
        alert('Please fill in all required fields');
        return;
    }

    try {
        // Step 1: Check if location exists
        let locationID;
        const locationSearchResponse = await fetch('https://zahirgutierrez.com/LAMPAPI/LocationSearch.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ address: formData.address })
        });

        const locationSearchResult = await locationSearchResponse.json();

        if (locationSearchResult.status === 'success') {
            // Location exists, use its ID
            locationID = locationSearchResult.locationID;
        } else {
            // Location doesn't exist, create it
            // Note: You'll need to implement geocoding to get lat/lon in a real app
            // For now using placeholder coordinates
            const locationCreateResponse = await fetch('https://zahirgutierrez.com/LAMPAPI/LocationCreate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: formData.address,
                    lat: 0.0, // Replace with actual geocoding in production
                    lon: 0.0, // Replace with actual geocoding in production
                    address: formData.address
                })
            });

            const locationCreateResult = await locationCreateResponse.json();
            
            if (locationCreateResult.status !== 'success') {
                throw new Error('Failed to create location: ' + (locationCreateResult.message || 'Unknown error'));
            }

            locationID = locationCreateResult.locationID;
        }

        // Step 2: Create the event
        const eventData = {
            name: formData.name,
            description: formData.description,
            catID: 1, // Always set to 1 as per requirements
            start_time: formData.start_time,
            end_time: formData.end_time,
            locationID: locationID,
            contactPhone: formData.contactPhone,
            contactEmail: formData.contactEmail,
            eventType: formData.eventType,
            UID: formData.UID,
            universityID: formData.universityID,
            rsoID: formData.rsoID
        };

        const eventCreateResponse = await fetch('https://zahirgutierrez.com/LAMPAPI/EventCreate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(eventData)
        });

        const eventCreateResult = await eventCreateResponse.json();

        if (eventCreateResult.status === 'success') {
            alert('Event created successfully!');
            // Optional: Reset form or redirect
            event.target.reset();
        } else {
            throw new Error(eventCreateResult.message || 'Failed to create event');
        }

    } catch (error) {
        console.error('Error creating event:', error);
        alert('Error: ' + error.message);
    }
}


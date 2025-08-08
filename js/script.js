// Dropdown menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const dropdownMenu = document.getElementById('dropdown-menu');
    let backdrop = document.querySelector('.dropdown-backdrop');

    // Create backdrop if it doesn't exist
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.className = 'dropdown-backdrop';
        document.body.appendChild(backdrop);
    }

    // Hamburger menu click
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            dropdownMenu.classList.toggle('active');
            backdrop.classList.toggle('show');
        });
    }

    // Backdrop click to close menu
    backdrop.addEventListener('click', function() {
        dropdownMenu.classList.remove('show');
        backdrop.classList.remove('show');
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!hamburger.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.classList.remove('show');
            backdrop.classList.remove('show');
        }
    });

    // Close menu when pressing Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            dropdownMenu.classList.remove('show');
            backdrop.classList.remove('show');
        }
    });

    // Counter animation on scroll
    const counters = document.querySelectorAll('.counter');
    const options = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000; // 2 seconds
                const increment = target / (duration / 16); // 60fps
                let current = 0;

                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        counter.textContent = Math.floor(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };

                updateCounter();
                observer.unobserve(counter); // Only animate once
            }
        });
    }, options);

    counters.forEach(counter => {
        observer.observe(counter);
    });
}); 

document.addEventListener('DOMContentLoaded', function() {
    const submenuParents = document.querySelectorAll('.has-submenu > a');

    submenuParents.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault(); // stop the link from navigating immediately

            document.querySelectorAll('.has-submenu').forEach(item => {
                if (item !== link.parentElement) {
                    item.classList.remove('open');
                }
            });

            link.parentElement.classList.toggle('open');
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const dropdownMenu = document.getElementById('dropdown-menu');

    if (hamburger) {
        hamburger.addEventListener('click', () => {
            dropdownMenu.style.display = 
                dropdownMenu.style.display === 'flex' ? 'none' : 'flex';
        });
    }

    // ✅ Submenu toggle
    const submenuParents = document.querySelectorAll('.has-submenu > a');
    submenuParents.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            link.parentElement.classList.toggle('open');
        });
    });
});



const backendURL = 'https://zidalco.infinityfree.me/get_feedback.php'; // 🔁 Change this to your live domain when online

// Character counter
document.querySelector('textarea[name="message"]').addEventListener('input', function() {
    document.getElementById('charCount').innerText = this.value.length;
});

const backendURL = 'https://zidalco.infinityfree.me'; // ✅ Base domain only

// Submit feedback
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(`${backendURL}/submit_feedback.php`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if (data.includes("success")) {
            alert("✅ Thank you for your feedback!");
            this.reset();
            loadFeedback();
            document.getElementById('charCount').innerText = "0";
        } else {
            alert("❌ Something went wrong: " + data);
            console.error(data);
        }
    })
    .catch(error => {
        console.error('Error submitting feedback:', error);
        alert("❌ Network error. Please try again later.");
    });
});

// Load feedback comments
function loadFeedback() {
    fetch(`${backendURL}/get_feedback.php`)
        .then(res => res.json())
        .then(data => {
            const section = document.getElementById('feedbackSection');
            const count = document.getElementById('commentCount');
            section.innerHTML = '';

            if (!data.length) {
                section.innerHTML = '<p>No feedback yet.</p>';
                count.innerText = '0 Comments';
                return;
            }

            count.innerText = `${data.length} Comment${data.length > 1 ? 's' : ''}`;

            data.forEach(comment => {
                const box = document.createElement('div');
                box.classList.add('comment-box');
                box.innerHTML = `
                    <strong>${comment.name}</strong><br>
                    <p>${comment.message}</p>
                    <small>${new Date(comment.created_at).toLocaleString()}</small>
                    <hr>
                `;
                section.appendChild(box);
            });
        })
        .catch(error => {
            console.error('Error loading feedback:', error);
        });
}

// Initial load
loadFeedback();



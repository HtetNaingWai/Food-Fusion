// Mobile menu toggle (simple)
function toggleMenu(){
    const nav = document.querySelector('nav ul');
    if(!nav) return;
    const isMobile = window.matchMedia("(max-width: 760px)").matches;
    if (isMobile) {
        nav.style.display = (nav.style.display === 'flex') ? 'none' : 'flex';
        nav.style.flexDirection = 'column';
        nav.style.gap = '12px';
        nav.style.marginTop = '12px';
        nav.style.position = 'absolute';
        nav.style.top = '100%';
        nav.style.left = '0';
        nav.style.width = '100%';
        nav.style.background = 'rgba(15,15,18,.9)';
        nav.style.padding = '10px 20px';
    } else {
        // Reset for desktop view if resized
        nav.style.display = 'flex';
        nav.style.flexDirection = 'row';
        nav.style.position = 'static';
    }
}

// Year in footer
document.addEventListener('DOMContentLoaded', () => {
    const yearSpan = document.getElementById('year');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }
    checkCookieConsent();
});

// Simple client-side filter for recipes/menu
function filterMenu(tag){
    const cards = document.querySelectorAll('#menuGrid .card');
    const buttons = document.querySelectorAll('.filter');
    
    // Deactivate all buttons
    buttons.forEach(b => b.classList.remove('active'));

    // Activate the relevant button
    const activeBtn = [...buttons].find(b => b.getAttribute('onclick').includes(`'${tag}'`));
    if (activeBtn) {
        activeBtn.classList.add('active');
    }

    // Filter cards
    cards.forEach(c => {
        const tags = c.getAttribute('data-tags') || '';
        c.style.display = (tag === 'all' || tags.includes(tag)) ? 'flex' : 'none';
    });
}

// Modal/Pop-up Logic ( "Sign up Now" pop-up)
const modal = document.getElementById('signup-modal');

function openModal() {
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    }
}

function closeModal() {
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Close modal if user clicks outside the content
if (modal) {
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
}

// Cookie Acceptance Logic (Task 4)
const cookieBanner = document.getElementById('cookie-banner');

function checkCookieConsent() {
    if (localStorage.getItem('cookieAccepted') !== 'true') {
        if (cookieBanner) {
            cookieBanner.style.display = 'flex';
        }
    }
}

function acceptCookies() {
    localStorage.setItem('cookieAccepted', 'true');
    if (cookieBanner) {
        cookieBanner.style.display = 'none';
    }
}

function showCookieBanner() {
    // Used for the "Cookie Policy" link in the footer to show the banner again
    if (cookieBanner) {
         cookieBanner.style.display = 'flex';
    }
}
// Smooth scrolling for policy page navigation
document.addEventListener('DOMContentLoaded', function() {
    // Policy page smooth scrolling
    const policyLinks = document.querySelectorAll('.policy-nav a[href^="#"]');
    
    policyLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 100;
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
                
                // Update active state
                policyLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });
    
    // Update active state on scroll
    if (window.location.pathname.includes('privacy-policy')) {
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('.policy-section');
            const navLinks = document.querySelectorAll('.policy-nav a');
            
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                
                if (pageYOffset >= sectionTop - 150) {
                    current = section.getAttribute('id');
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });
    }
});
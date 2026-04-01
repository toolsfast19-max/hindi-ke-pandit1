// ========== LOAD PAGES DYNAMICALLY ==========
async function loadPage(pageName) {
    try {
        const response = await fetch(`pages/${pageName}.html`);
        const html = await response.text();
        document.getElementById(`${pageName}-page`).innerHTML = html;
        
        // Re-initialize page-specific JS
        if(pageName === 'registration') {
            // Wait a bit for DOM to update
            setTimeout(() => {
                if(typeof initRegistrationForm === 'function') initRegistrationForm();
            }, 100);
        }
    } catch(error) {
        console.error('Error loading page:', error);
    }
}

// Load all pages
async function loadAllPages() {
    const pages = ['home', 'about', 'courses', 'dashboard', 'registration', 'contact'];
    for(let page of pages) {
        await loadPage(page);
    }
}

// Page navigation
document.querySelectorAll('[data-page]').forEach(btn => {
    btn.addEventListener('click', function() {
        const pageId = this.getAttribute('data-page');
        document.querySelectorAll('.page').forEach(page => page.classList.remove('active-page'));
        document.getElementById(`${pageId}-page`).classList.add('active-page');
        window.scrollTo({top: 0, behavior: 'smooth'});
        
        // Close mobile menu
        const navLinks = document.getElementById('navLinks');
        if(navLinks.classList.contains('active')) {
            navLinks.classList.remove('active');
        }
    });
});

// Load pages on start
loadAllPages();
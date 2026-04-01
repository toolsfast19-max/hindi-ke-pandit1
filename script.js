// ========== PAGE NAVIGATION ==========
const pages = ['home', 'about', 'courses', 'dashboard', 'registration', 'contact'];
let currentPage = 'home';

function showPage(pageId) {
    pages.forEach(p => {
        const pageElem = document.getElementById(`${p}-page`);
        if (pageElem) pageElem.classList.remove('active-page');
    });
    const activePage = document.getElementById(`${pageId}-page`);
    if (activePage) activePage.classList.add('active-page');
    currentPage = pageId;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Add click event to all data-page elements
document.querySelectorAll('[data-page]').forEach(el => {
    el.addEventListener('click', (e) => {
        const page = el.getAttribute('data-page');
        if (page) showPage(page);
    });
});

// ========== MOBILE MENU TOGGLE ==========
const menuToggle = document.getElementById('menuToggle');
const navLinks = document.getElementById('navLinks');

if (menuToggle) {
    menuToggle.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });
}

// ========== STICKY ENROLL BUTTON ==========
const stickyEnrollBtn = document.getElementById('stickyEnrollBtn');
if (stickyEnrollBtn) {
    stickyEnrollBtn.addEventListener('click', (e) => {
        e.preventDefault();
        showPage('registration');
    });
}

// ========== DASHBOARD TABS ==========
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        document.getElementById(`${btn.dataset.tab}-pane`).classList.add('active');
    });
});

// ========== REGISTRATION FORM ==========
const registrationForm = document.getElementById('registrationForm');
if (registrationForm) {
    registrationForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        // Get form data
        const name = document.getElementById('regName').value;
        const phone = document.getElementById('regPhone').value;
        const studentClass = document.getElementById('regClass').value;
        const medium = document.getElementById('regMedium').value;
        
        // Show success message
        alert(`✅ Registration Successful!\n\nName: ${name}\nMobile: ${phone}\nClass: ${studentClass}\nMedium: ${medium}\n\nWe will contact you within 24 hours. Thank you!`);
        
        // Reset form
        e.target.reset();
    });
}

// ========== LOGIN SYSTEM ==========
let loggedIn = false;
const loginForm = document.getElementById('loginForm');
const dashboardInfo = document.getElementById('dashboardInfo');
const feeDetails = document.getElementById('feeDetails');
const marksList = document.getElementById('marksList');
let marksChart = null;

if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const id = document.getElementById('loginId').value;
        const password = document.getElementById('loginPassword').value;
        
        if (id && password) {
            loggedIn = true;
            
            // Show dashboard info
            document.getElementById('displayName').innerText = id;
            document.getElementById('displayId').innerText = "STU" + Math.floor(1000 + Math.random() * 9000);
            dashboardInfo.style.display = 'block';
            loginForm.style.display = 'none';
            
            // Update fee details
            feeDetails.innerHTML = "<p>✅ Fee Paid: ₹0 pending. Next installment: 15th April 2025</p>";
            
            // Update marks list
            marksList.innerHTML = "<p>📊 Math: 85/100 | Science: 78/100 | English: 92/100</p>";
            
            // Create or update chart
            const ctx = document.getElementById('marksChart').getContext('2d');
            if (marksChart) {
                marksChart.destroy();
            }
            marksChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Math', 'Science', 'English'],
                    datasets: [{
                        label: 'Marks',
                        data: [85, 78, 92],
                        backgroundColor: '#2563eb',
                        borderRadius: 10
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        } else {
            alert('Please enter Student ID and Password');
        }
    });
}

// Logout functionality
const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
        loggedIn = false;
        dashboardInfo.style.display = 'none';
        loginForm.style.display = 'block';
        loginForm.reset();
        feeDetails.innerHTML = "Please login to view fee status";
        marksList.innerHTML = "";
        if (marksChart) {
            marksChart.destroy();
            marksChart = null;
        }
    });
}

// ========== LANGUAGE SWITCHER (English & Marathi) ==========
const translations = {
    en: {
        hero_title: "Step Towards Success",
        hero_sub: "Sasane Sir (9881296727) | Shinde Sir (9075238958)",
        hero_medium: "CBSE | English | Semi | Marathi | 5th to 10th All Mediums",
        btn_register: "Online Registration",
        btn_dashboard: "Student Dashboard",
        features_title: "Our Features",
        feat1_title: "10+ Years Experience",
        feat1_desc: "Decades of experience in education",
        feat2_title: "Excellent Results",
        feat2_desc: "90%+ students in merit every year",
        feat3_title: "Personal Attention",
        feat3_desc: "Limited batches, focus on each student",
        feat4_title: "All Mediums",
        feat4_desc: "CBSE, English, Semi, Marathi",
        about_title: "About Us",
        mission_title: "Our Mission",
        mission_desc: "To help every student reach their maximum potential. Providing quality education that lasts a lifetime.",
        vision_title: "Our Vision",
        vision_desc: "To make education simple, effective and result-oriented. Helping every student achieve success.",
        values_title: "Our Values",
        values_desc: "Dedication, Integrity, Excellence. We prioritize students' future.",
        teachers_title: "Our Expert Teachers",
        courses_title: "Our Courses",
        course1: "5th - 8th Foundation",
        course1_desc: "Strong foundation, concept clarity, regular tests",
        course2: "9th - 10th Board Prep",
        course2_desc: "Complete syllabus, revision, mock tests, previous papers",
        course3: "Competitive Exams",
        course3_desc: "JEE/NEET Foundation, Olympiad, MHT-CET",
        course4: "Online Classes",
        course4_desc: "Live Zoom classes, recorded videos, doubt support",
        enroll_now: "Enroll Now",
        dashboard_title: "Student Dashboard",
        tab_login: "Login",
        tab_fees: "Fee Status",
        tab_results: "Test Results",
        tab_material: "Study Material",
        fee_title: "Fee Details",
        results_title: "Test Results",
        material_title: "Study Material",
        reg_title: "Online Registration",
        contact_title: "Contact Us"
    },
    mr: {
        hero_title: "सफलता की ओर सुनहरा कदम",
        hero_sub: "ससाणे सर (9881296727) | शिंदे सर (9075238958)",
        hero_medium: "CBSE | English | Semi | Marathi | 5वी ते 10वी सर्व माध्यम",
        btn_register: "ऑनलाइन रजिस्ट्रेशन",
        btn_dashboard: "विद्यार्थी डॅशबोर्ड",
        features_title: "आमची वैशिष्ट्ये",
        feat1_title: "१०+ वर्षे अनुभव",
        feat1_desc: "शिक्षण क्षेत्रात दशकांचा अनुभव",
        feat2_title: "उत्कृष्ट परिणाम",
        feat2_desc: "दरवर्षी ९०%+ विद्यार्थी मेरिटमध्ये",
        feat3_title: "वैयक्तिक लक्ष",
        feat3_desc: "मर्यादित बॅच, प्रत्येक विद्यार्थ्यावर लक्ष",
        feat4_title: "सर्व माध्यम",
        feat4_desc: "CBSE, English, Semi, Marathi",
        about_title: "आमच्याबद्दल",
        mission_title: "आमचे ध्येय",
        mission_desc: "प्रत्येक विद्यार्थ्याला त्याच्या कमाल क्षमतेपर्यंत पोहोचवणे. आयुष्यभर उपयोगी पडेल असे दर्जेदार शिक्षण देणे.",
        vision_title: "आमची दृष्टी",
        vision_desc: "शिक्षण सोपे, प्रभावी आणि परिणाम-केंद्रित बनवणे. प्रत्येक विद्यार्थ्याला यशाच्या शिखरावर पोहोचवणे.",
        values_title: "आमची मूल्ये",
        values_desc: "समर्पण, प्रामाणिकपणा, उत्कृष्टता. आम्ही विद्यार्थ्यांच्या भविष्याला प्राधान्य देतो.",
        teachers_title: "आमचे तज्ज्ञ शिक्षक",
        courses_title: "आमचे अभ्यासक्रम",
        course1: "५वी - ८वी फाउंडेशन",
        course1_desc: "मजबूत पाया, संकल्पना स्पष्टता, नियमित चाचण्या",
        course2: "९वी - १०वी बोर्ड तयारी",
        course2_desc: "संपूर्ण अभ्यासक्रम, पुनरावृत्ती, मॉक टेस्ट, मागील पेपर",
        course3: "स्पर्धा परीक्षा",
        course3_desc: "JEE/NEET फाउंडेशन, ऑलिम्पियाड, MHT-CET",
        course4: "ऑनलाइन वर्ग",
        course4_desc: "लाइव्ह Zoom वर्ग, रेकॉर्डेड व्हिडिओ, शंका समाधान",
        enroll_now: "आता प्रवेश घ्या",
        dashboard_title: "विद्यार्थी डॅशबोर्ड",
        tab_login: "लॉगिन",
        tab_fees: "फी स्टेटस",
        tab_results: "चाचणी निकाल",
        tab_material: "अभ्यास साहित्य",
        fee_title: "फी तपशील",
        results_title: "चाचणी निकाल",
        material_title: "अभ्यास साहित्य",
        reg_title: "ऑनलाइन रजिस्ट्रेशन",
        contact_title: "संपर्क करा"
    }
};

let currentLang = 'en';

function updateLanguage(lang) {
    document.querySelectorAll('[data-key]').forEach(el => {
        const key = el.getAttribute('data-key');
        if (translations[lang][key]) {
            el.innerText = translations[lang][key];
        }
    });
    
    document.querySelectorAll('.lang-btn').forEach(btn => {
        if (btn.getAttribute('data-lang') === lang) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    currentLang = lang;
}

// Add language switcher event listeners
document.querySelectorAll('.lang-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const lang = btn.getAttribute('data-lang');
        updateLanguage(lang);
    });
});

// Initialize English language
updateLanguage('en');

// ========== CLOSE MOBILE MENU ON LINK CLICK ==========
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
        navLinks.classList.remove('active');
    });
});

// ========== SMOOTH SCROLL FOR ANCHOR LINKS ==========
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href === "#" || href === "") return;
        const target = document.querySelector(href);
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// ========== WELCOME MESSAGE ON PAGE LOAD ==========
window.addEventListener('load', () => {
    console.log('Website Loaded Successfully!');
});
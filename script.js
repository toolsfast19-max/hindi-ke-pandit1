// ========== LOAD PAGES DYNAMICALLY ==========
async function loadPage(pageName) {
    try {
        const response = await fetch(`pages/${pageName}.html`);
        const html = await response.text();
        document.getElementById(`${pageName}-page`).innerHTML = html;
        
        if(pageName === 'registration') {
            setTimeout(() => {
                if(typeof window.showStep === 'function') window.showStep(1);
            }, 100);
        }
        
        if(pageName === 'home') {
            setTimeout(() => {
                startCounters();
            }, 200);
        }
    } catch(error) {
        console.error('Error loading page:', error);
    }
}

async function loadAllPages() {
    const pages = ['home', 'about', 'courses', 'dashboard', 'registration', 'contact'];
    for(let page of pages) {
        await loadPage(page);
    }
    
    setTimeout(() => {
        if(document.getElementById('home-page').classList.contains('active-page')) {
            startCounters();
        }
    }, 500);
}

// ========== MOBILE NUMBER VALIDATION ==========
function isValidMobile(mobile) {
    let cleanMobile = mobile.toString().trim();
    if(cleanMobile.startsWith('+91')) cleanMobile = cleanMobile.substring(3);
    if(cleanMobile.startsWith('0')) cleanMobile = cleanMobile.substring(1);
    cleanMobile = cleanMobile.replace(/[\s-]/g, '');
    return /^[0-9]{10}$/.test(cleanMobile);
}

function getCleanMobile(mobile) {
    let cleanMobile = mobile.toString().trim();
    if(cleanMobile.startsWith('+91')) cleanMobile = cleanMobile.substring(3);
    if(cleanMobile.startsWith('0')) cleanMobile = cleanMobile.substring(1);
    cleanMobile = cleanMobile.replace(/[\s-]/g, '');
    return cleanMobile;
}

// ========== PAGE NAVIGATION ==========
document.querySelectorAll('[data-page]').forEach(btn => {
    btn.addEventListener('click', function() {
        const pageId = this.getAttribute('data-page');
        document.querySelectorAll('.page').forEach(page => page.classList.remove('active-page'));
        document.getElementById(`${pageId}-page`).classList.add('active-page');
        window.scrollTo({top: 0, behavior: 'smooth'});
        
        const navLinks = document.getElementById('navLinks');
        if(navLinks) navLinks.classList.remove('active');
        
        if(pageId === 'home') {
            setTimeout(() => startCounters(), 300);
        }
    });
});

// ========== MOBILE MENU ==========
const menuToggle = document.getElementById('menuToggle');
const navLinks = document.getElementById('navLinks');
if(menuToggle) {
    menuToggle.addEventListener('click', () => navLinks.classList.toggle('active'));
}

// ========== STICKY ENROLL BUTTON ==========
const stickyBtn = document.getElementById('stickyEnrollBtn');
if(stickyBtn) {
    stickyBtn.addEventListener('click', (e) => {
        e.preventDefault();
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active-page'));
        document.getElementById('registration-page').classList.add('active-page');
        window.scrollTo({top: 0, behavior: 'smooth'});
    });
}

// ========== LANGUAGE SWITCHER (EN + MR) ==========
const langBtns = document.querySelectorAll('.lang-btn');
langBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        const lang = this.getAttribute('data-lang');
        langBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        if(lang === 'mr') {
            document.querySelectorAll('.hero h1').forEach(el => {
                el.innerHTML = 'यशाच्या दिशेने <span style="color: #fbbf24;">सुवर्ण पाऊल</span>';
            });
        } else {
            document.querySelectorAll('.hero h1').forEach(el => {
                el.innerHTML = 'Golden Step <span style="color: #fbbf24;">Towards Success</span>';
            });
        }
    });
});

// ========== COUNTER ANIMATION ==========
function startCounters() {
    const counters = document.querySelectorAll('.counter-number');
    if(counters.length === 0) return;
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-count'));
        let count = 0;
        const increment = target / 50;
        
        const updateCounter = () => {
            count += increment;
            if(count < target) {
                counter.innerText = Math.floor(count);
                requestAnimationFrame(updateCounter);
            } else {
                counter.innerText = target;
            }
        };
        updateCounter();
    });
}

// ========== REGISTRATION FORM FUNCTIONS ==========
window.currentStep = 1;

window.showStep = function(step) {
    const step1 = document.getElementById('step1Section');
    const step2 = document.getElementById('step2Section');
    const step3 = document.getElementById('step3Section');
    
    if(step1) step1.style.display = 'none';
    if(step2) step2.style.display = 'none';
    if(step3) step3.style.display = 'none';
    
    if(step === 1 && step1) step1.style.display = 'block';
    if(step === 2 && step2) step2.style.display = 'block';
    if(step === 3 && step3) step3.style.display = 'block';
    
    // Update progress steps styling
    document.querySelectorAll('.step').forEach((el, idx) => {
        el.classList.remove('active', 'completed');
        if(idx + 1 === step) {
            el.classList.add('active');
        } else if(idx + 1 < step) {
            el.classList.add('completed');
        }
    });
    
    window.currentStep = step;
    window.scrollTo({top: 0, behavior: 'smooth'});
};

window.nextStep = function(step) {
    if(step === 1 && window.validateStep1()) {
        window.showStep(2);
    } 
    else if(step === 2 && window.validateStep2()) {
        window.showStep(3);
    }
    else if(step === 1 && !window.validateStep1()) {
        alert('कृपया सभी आवश्यक जानकारी भरें!');
    }
    else if(step === 2 && !window.validateStep2()) {
        alert('कृपया सभी शैक्षणिक जानकारी भरें!');
    }
};

window.prevStep = function(step) {
    if(step === 2) window.showStep(1);
    if(step === 3) window.showStep(2);
};

window.validateStep1 = function() {
    let valid = true;
    let name = document.getElementById('fullName')?.value.trim() || '';
    let mobile = document.getElementById('mobileNumber')?.value.trim() || '';
    let gender = document.getElementById('gender')?.value || '';
    let parent = document.getElementById('parentName')?.value.trim() || '';
    
    if(name.length < 3) {
        if(document.getElementById('nameError')) document.getElementById('nameError').innerText = 'नाम कम से कम 3 अक्षर का होना चाहिए';
        if(document.getElementById('fullName')) document.getElementById('fullName').style.borderColor = '#ef4444';
        valid = false;
    } else {
        if(document.getElementById('nameError')) document.getElementById('nameError').innerText = '';
        if(document.getElementById('fullName')) document.getElementById('fullName').style.borderColor = '#e2e8f0';
    }
    
    if(!mobile) {
        if(document.getElementById('mobileError')) document.getElementById('mobileError').innerText = 'मोबाइल नंबर आवश्यक है';
        if(document.getElementById('mobileNumber')) document.getElementById('mobileNumber').style.borderColor = '#ef4444';
        valid = false;
    } else if(!isValidMobile(mobile)) {
        if(document.getElementById('mobileError')) document.getElementById('mobileError').innerText = 'कृपया सही 10 अंकों का मोबाइल नंबर डालें';
        if(document.getElementById('mobileNumber')) document.getElementById('mobileNumber').style.borderColor = '#ef4444';
        valid = false;
    } else {
        if(document.getElementById('mobileError')) document.getElementById('mobileError').innerText = '';
        if(document.getElementById('mobileNumber')) document.getElementById('mobileNumber').style.borderColor = '#e2e8f0';
        let cleanMobile = getCleanMobile(mobile);
        document.getElementById('mobileNumber').value = cleanMobile;
    }
    
    if(!gender) {
        if(document.getElementById('genderError')) document.getElementById('genderError').innerText = 'लिंग का चयन करें';
        if(document.getElementById('gender')) document.getElementById('gender').style.borderColor = '#ef4444';
        valid = false;
    } else {
        if(document.getElementById('genderError')) document.getElementById('genderError').innerText = '';
        if(document.getElementById('gender')) document.getElementById('gender').style.borderColor = '#e2e8f0';
    }
    
    if(parent.length < 2) {
        if(document.getElementById('parentError')) document.getElementById('parentError').innerText = 'अभिभावक का नाम आवश्यक है';
        if(document.getElementById('parentName')) document.getElementById('parentName').style.borderColor = '#ef4444';
        valid = false;
    } else {
        if(document.getElementById('parentError')) document.getElementById('parentError').innerText = '';
        if(document.getElementById('parentName')) document.getElementById('parentName').style.borderColor = '#e2e8f0';
    }
    
    return valid;
};

window.validateStep2 = function() {
    let valid = true;
    let classVal = document.getElementById('currentClass')?.value || '';
    let medium = document.getElementById('medium')?.value || '';
    let subjects = document.querySelectorAll('.subject:checked');
    
    if(!classVal) {
        if(document.getElementById('classError')) document.getElementById('classError').innerText = 'कक्षा का चयन करें';
        if(document.getElementById('currentClass')) document.getElementById('currentClass').style.borderColor = '#ef4444';
        valid = false;
    } else {
        if(document.getElementById('classError')) document.getElementById('classError').innerText = '';
        if(document.getElementById('currentClass')) document.getElementById('currentClass').style.borderColor = '#e2e8f0';
    }
    
    if(!medium) {
        if(document.getElementById('mediumError')) document.getElementById('mediumError').innerText = 'माध्यम का चयन करें';
        if(document.getElementById('medium')) document.getElementById('medium').style.borderColor = '#ef4444';
        valid = false;
    } else {
        if(document.getElementById('mediumError')) document.getElementById('mediumError').innerText = '';
        if(document.getElementById('medium')) document.getElementById('medium').style.borderColor = '#e2e8f0';
    }
    
    if(subjects.length === 0) {
        if(document.getElementById('subjectsError')) document.getElementById('subjectsError').innerText = 'कम से कम एक विषय का चयन करें';
        valid = false;
    } else {
        if(document.getElementById('subjectsError')) document.getElementById('subjectsError').innerText = '';
    }
    
    return valid;
};

window.validateStep3 = function() {
    let valid = true;
    let batch = document.getElementById('batch')?.value || '';
    
    if(!batch) {
        if(document.getElementById('batchError')) document.getElementById('batchError').innerText = 'बैच का चयन करें';
        if(document.getElementById('batch')) document.getElementById('batch').style.borderColor = '#ef4444';
        valid = false;
    } else {
        if(document.getElementById('batchError')) document.getElementById('batchError').innerText = '';
        if(document.getElementById('batch')) document.getElementById('batch').style.borderColor = '#e2e8f0';
    }
    
    return valid;
};

window.submitRegistration = function() {
    if(!window.validateStep3()) {
        alert('कृपया बैच का चयन करें!');
        return;
    }
    
    let name = document.getElementById('fullName')?.value || '';
    let mobile = document.getElementById('mobileNumber')?.value || '';
    let classVal = document.getElementById('currentClass')?.value || '';
    let batch = document.getElementById('batch')?.value || '';
    let regId = 'SNC' + new Date().getFullYear() + Math.floor(Math.random() * 10000);
    
    let selectedSubjects = [];
    document.querySelectorAll('.subject:checked').forEach(sub => {
        selectedSubjects.push(sub.value);
    });
    
    let message = `✅ Registration Successful!\n\nRegistration ID: ${regId}\nName: ${name}\nMobile: ${mobile}\nClass: ${classVal}\nBatch: ${batch}\nSubjects: ${selectedSubjects.join(', ')}\n\nWe will contact you soon.`;
    alert(message);
    
    let whatsappMsg = `Hi ${name},\n\nThank you for registering!\n\nReg ID: ${regId}\nClass: ${classVal}\nBatch: ${batch}\n\nTeam श्रीनाथ चाणक्य\n📞 9881296727`;
    window.open(`https://wa.me/${mobile}?text=${encodeURIComponent(whatsappMsg)}`, '_blank');
    
    // Reset form
    const fields = ['fullName', 'mobileNumber', 'whatsapp', 'gender', 'dob', 'email', 'parentName', 'address', 'currentClass', 'schoolName', 'medium', 'percentage', 'batch', 'mode', 'heardFrom', 'notes'];
    fields.forEach(id => {
        if(document.getElementById(id)) document.getElementById(id).value = '';
    });
    document.querySelectorAll('.subject').forEach(cb => cb.checked = false);
    if(document.getElementById('competitiveExam')) document.getElementById('competitiveExam').value = 'Not Interested';
    if(document.getElementById('mode')) document.getElementById('mode').value = 'Offline (Classroom)';
    if(document.getElementById('heardFrom')) document.getElementById('heardFrom').value = 'Select';
    
    document.querySelectorAll('input, select, textarea').forEach(el => {
        el.style.borderColor = '#e2e8f0';
    });
    
    window.showStep(1);
    window.scrollTo({top: 0, behavior: 'smooth'});
};

// ========== INITIALIZE ==========
loadAllPages();
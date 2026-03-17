/**
 * Updevix Quiz Platform - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initThemeToggle();
    initMobileMenu();
    initFormTabs();
    initPasswordToggles();
    initAlertDismiss();
});

/* ============================================
   THEME TOGGLE (Dark Mode)
   ============================================ */
function initThemeToggle() {
    var toggle = document.getElementById('themeToggle');
    if (!toggle) return;

    var saved = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
    updateThemeIcon(saved);

    toggle.addEventListener('click', function() {
        var current = document.documentElement.getAttribute('data-theme');
        var next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        updateThemeIcon(next);
    });
}

function updateThemeIcon(theme) {
    var toggle = document.getElementById('themeToggle');
    if (!toggle) return;
    var icon = toggle.querySelector('i');
    if (icon) {
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

/* ============================================
   MOBILE MENU
   ============================================ */
function initMobileMenu() {
    var btn = document.getElementById('mobileToggle');
    var menu = document.getElementById('mobileMenu');
    if (!btn || !menu) return;

    btn.addEventListener('click', function() {
        menu.classList.toggle('active');
        var icon = btn.querySelector('i');
        if (icon) {
            icon.className = menu.classList.contains('active') ? 'fas fa-times' : 'fas fa-bars';
        }
    });
}

/* ============================================
   FORM TABS (Login/Register Toggle)
   ============================================ */
function initFormTabs() {
    var tabs = document.querySelectorAll('.form-tab');
    if (tabs.length === 0) return;

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var target = this.getAttribute('data-tab');

            tabs.forEach(function(t) { t.classList.remove('active'); });
            this.classList.add('active');

            document.querySelectorAll('.auth-form').forEach(function(form) {
                form.classList.remove('active');
            });

            var targetForm = document.getElementById(target);
            if (targetForm) {
                targetForm.classList.add('active');
            }
        });
    });

    // Check URL hash for direct tab navigation
    if (window.location.hash === '#register') {
        var registerTab = document.querySelector('[data-tab="registerForm"]');
        if (registerTab) registerTab.click();
    }
}

/* ============================================
   PASSWORD VISIBILITY TOGGLE
   ============================================ */
function initPasswordToggles() {
    document.querySelectorAll('.password-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = this.parentElement.querySelector('input');
            var icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
    });
}

/* ============================================
   AUTO-DISMISS ALERTS
   ============================================ */
function initAlertDismiss() {
    document.querySelectorAll('.alert').forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() { alert.remove(); }, 300);
        }, 5000);
    });
}

/* ============================================
   QUIZ TIMER
   ============================================ */
var quizTimerInterval = null;

function startQuizTimer(durationMinutes, onExpire) {
    var totalSeconds = durationMinutes * 60;
    var timerDisplay = document.getElementById('quizTimer');
    var startTime = Date.now();
    var savedRemaining = sessionStorage.getItem('quiz_remaining_time');

    if (savedRemaining) {
        totalSeconds = parseInt(savedRemaining);
    }

    function updateTimer() {
        var elapsed = Math.floor((Date.now() - startTime) / 1000);
        var remaining = totalSeconds - elapsed;

        if (remaining <= 0) {
            clearInterval(quizTimerInterval);
            sessionStorage.removeItem('quiz_remaining_time');
            if (timerDisplay) timerDisplay.textContent = '00:00';
            if (typeof onExpire === 'function') onExpire();
            return;
        }

        sessionStorage.setItem('quiz_remaining_time', remaining);

        var minutes = Math.floor(remaining / 60);
        var seconds = remaining % 60;
        var timeStr = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

        if (timerDisplay) {
            timerDisplay.textContent = timeStr;
            var timerContainer = timerDisplay.closest('.quiz-timer');
            if (timerContainer) {
                if (remaining <= 60) {
                    timerContainer.classList.add('warning');
                } else {
                    timerContainer.classList.remove('warning');
                }
            }
        }
    }

    updateTimer();
    quizTimerInterval = setInterval(updateTimer, 1000);
}

/* ============================================
   QUIZ NAVIGATION
   ============================================ */
function navigateQuestion(index) {
    var cards = document.querySelectorAll('.question-card');
    var dots = document.querySelectorAll('.question-dot');

    cards.forEach(function(card, i) {
        card.style.display = i === index ? 'block' : 'none';
    });

    dots.forEach(function(dot, i) {
        dot.classList.toggle('current', i === index);
    });

    // Update progress
    var progressBar = document.getElementById('progressBar');
    if (progressBar && cards.length > 0) {
        progressBar.style.width = ((index + 1) / cards.length * 100) + '%';
    }

    var progressText = document.getElementById('progressText');
    if (progressText) {
        progressText.textContent = 'Question ' + (index + 1) + ' of ' + cards.length;
    }

    // Store current question
    sessionStorage.setItem('current_question', index);
}

function selectOption(element, questionId) {
    var parent = element.closest('.options-grid');
    parent.querySelectorAll('.option-item').forEach(function(opt) {
        opt.classList.remove('selected');
    });
    element.classList.add('selected');

    var radio = element.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;

    // Mark dot as answered
    var dots = document.querySelectorAll('.question-dot');
    var cards = document.querySelectorAll('.question-card');
    cards.forEach(function(card, i) {
        if (card.style.display !== 'none') {
            dots[i].classList.add('answered');
        }
    });
}

/* ============================================
   OTP INPUT HANDLING
   ============================================ */
function initOTPInputs() {
    var inputs = document.querySelectorAll('.otp-input');
    inputs.forEach(function(input, index) {
        input.addEventListener('input', function() {
            if (this.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value === '' && index > 0) {
                inputs[index - 1].focus();
            }
        });
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            var paste = (e.clipboardData || window.clipboardData).getData('text').trim();
            for (var i = 0; i < paste.length && i < inputs.length; i++) {
                inputs[i].value = paste[i];
            }
            if (paste.length >= inputs.length) {
                inputs[inputs.length - 1].focus();
            }
        });
    });
}

/* ============================================
   FORM VALIDATION
   ============================================ */
function validateEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePassword(password) {
    return password.length >= 6;
}

function showFormError(inputId, message) {
    var input = document.getElementById(inputId);
    if (!input) return;
    var existing = input.parentElement.querySelector('.form-error');
    if (existing) existing.remove();

    var error = document.createElement('div');
    error.className = 'form-error';
    error.textContent = message;
    input.parentElement.appendChild(error);
    input.style.borderColor = '#ef4444';
}

function clearFormErrors() {
    document.querySelectorAll('.form-error').forEach(function(e) { e.remove(); });
    document.querySelectorAll('.form-input').forEach(function(i) { i.style.borderColor = ''; });
}

/* ============================================
   LOADING SPINNER
   ============================================ */
function showSpinner() {
    var overlay = document.getElementById('spinnerOverlay');
    if (overlay) overlay.classList.add('active');
}

function hideSpinner() {
    var overlay = document.getElementById('spinnerOverlay');
    if (overlay) overlay.classList.remove('active');
}

/* ============================================
   CONFIRM DIALOG
   ============================================ */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

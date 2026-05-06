/* =============================================
   EMCEE WEBSITE — MAIN JAVASCRIPT
   ============================================= */

/* ---- Navbar: scroll effect + mobile toggle ---- */
(function () {
  const navbar = document.getElementById('navbar');
  const navToggle = document.getElementById('navToggle');
  const navLinks = document.getElementById('navLinks');

  if (navbar) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 40) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    }, { passive: true });
  }

  if (navToggle && navLinks) {
    navToggle.addEventListener('click', function () {
      const isOpen = navLinks.classList.toggle('open');
      navToggle.classList.toggle('open', isOpen);
      navToggle.setAttribute('aria-expanded', isOpen);
    });

    // Close menu when a link is clicked
    navLinks.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        navLinks.classList.remove('open');
        navToggle.classList.remove('open');
        navToggle.setAttribute('aria-expanded', false);
      });
    });
  }
})();

/* ---- Scroll animations (Intersection Observer) ---- */
(function () {
  const targets = document.querySelectorAll('.fade-in');
  if (!targets.length) return;

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

  targets.forEach(function (el) {
    observer.observe(el);
  });
})();

/* ---- Package pre-selection from URL param ---- */
(function () {
  const params = new URLSearchParams(window.location.search);
  const pkg = params.get('package');
  if (!pkg) return;

  const radio = document.querySelector('input[name="package"][value="' + pkg + '"]');
  if (radio) {
    radio.checked = true;
    const label = radio.closest('.radio-option');
    if (label) label.classList.add('selected');
  }
})();

/* ---- Radio option highlight ---- */
(function () {
  const radios = document.querySelectorAll('.radio-option input[type="radio"]');
  radios.forEach(function (radio) {
    radio.addEventListener('change', function () {
      // Remove selected from all
      document.querySelectorAll('.radio-option').forEach(function (opt) {
        opt.classList.remove('selected');
      });
      // Add to current
      const label = radio.closest('.radio-option');
      if (label) label.classList.add('selected');
    });
  });
})();

/* ---- Set min date on date input (today) ---- */
(function () {
  const dateInput = document.getElementById('eventDate');
  if (!dateInput) return;
  const today = new Date().toISOString().split('T')[0];
  dateInput.setAttribute('min', today);
})();

/* ---- Booking form validation & submission ---- */
(function () {
  const form = document.getElementById('bookingForm');
  const formCard = document.getElementById('formCard');
  const success = document.getElementById('successMessage');
  if (!form) return;

  function showError(fieldId, errorId, show) {
    const field = document.getElementById(fieldId);
    const err = document.getElementById(errorId);
    if (!field || !err) return;
    if (show) {
      field.classList.add('error');
      err.classList.add('visible');
    } else {
      field.classList.remove('error');
      err.classList.remove('visible');
    }
  }

  function isValidEmail(val) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim());
  }

  function validateForm() {
    let valid = true;

    // Full Name
    const fullName = document.getElementById('fullName');
    if (!fullName || !fullName.value.trim()) {
      showError('fullName', 'fullNameError', true);
      valid = false;
    } else {
      showError('fullName', 'fullNameError', false);
    }

    // Email
    const email = document.getElementById('email');
    if (!email || !isValidEmail(email.value)) {
      showError('email', 'emailError', true);
      valid = false;
    } else {
      showError('email', 'emailError', false);
    }

    // Phone
    const phone = document.getElementById('phone');
    if (!phone || !phone.value.trim()) {
      showError('phone', 'phoneError', true);
      valid = false;
    } else {
      showError('phone', 'phoneError', false);
    }

    // Event Type
    const eventType = document.getElementById('eventType');
    if (!eventType || !eventType.value) {
      showError('eventType', 'eventTypeError', true);
      valid = false;
    } else {
      showError('eventType', 'eventTypeError', false);
    }

    // Venue
    const venue = document.getElementById('venue');
    if (!venue || !venue.value.trim()) {
      showError('venue', 'venueError', true);
      valid = false;
    } else {
      showError('venue', 'venueError', false);
    }

    // Event Date
    const eventDate = document.getElementById('eventDate');
    if (!eventDate || !eventDate.value) {
      showError('eventDate', 'eventDateError', true);
      valid = false;
    } else {
      showError('eventDate', 'eventDateError', false);
    }

    // Event Time
    const eventTime = document.getElementById('eventTime');
    if (!eventTime || !eventTime.value) {
      showError('eventTime', 'eventTimeError', true);
      valid = false;
    } else {
      showError('eventTime', 'eventTimeError', false);
    }

    // Duration
    const duration = document.getElementById('duration');
    if (!duration || !duration.value) {
      showError('duration', 'durationError', true);
      valid = false;
    } else {
      showError('duration', 'durationError', false);
    }

    // Package
    const packageSelected = document.querySelector('input[name="package"]:checked');
    const packageError = document.getElementById('packageError');
    if (!packageSelected) {
      if (packageError) packageError.classList.add('visible');
      valid = false;
    } else {
      if (packageError) packageError.classList.remove('visible');
    }

    return valid;
  }

  // Live validation: clear errors on input/change
  ['fullName', 'email', 'phone', 'eventType', 'venue', 'eventDate', 'eventTime', 'duration'].forEach(function (id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', function () {
      var errorId = id + 'Error';
      // Re-validate only this field on blur/input
      el.classList.remove('error');
      var err = document.getElementById(errorId);
      if (err) err.classList.remove('visible');
    });
    el.addEventListener('change', function () {
      el.classList.remove('error');
      var err = document.getElementById(id + 'Error');
      if (err) err.classList.remove('visible');
    });
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    if (!validateForm()) {
      // Scroll to first error
      var firstError = form.querySelector('.error, input[name="package"]');
      if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      return;
    }

    var submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
      submitBtn.textContent = 'Submitting...';
      submitBtn.disabled = true;
    }

    // Gather booking details
    var fullName = document.getElementById('fullName').value.trim();
    var email = document.getElementById('email').value.trim();
    var phone = document.getElementById('phone').value.trim();

    var companyInput = document.getElementById('company');
    var company = companyInput ? companyInput.value.trim() : '';

    function getSelectedText(selectId) {
      var el = document.getElementById(selectId);
      return el && el.selectedIndex > 0 ? el.options[el.selectedIndex].text : '';
    }

    function getSelectedValue(selectId) {
      var el = document.getElementById(selectId);
      return el ? el.value : '';
    }

    var eventType = getSelectedText('eventType');
    var venue = document.getElementById('venue').value.trim();
    var eventDate = document.getElementById('eventDate').value;
    var eventTime = document.getElementById('eventTime').value;
    var durationText = getSelectedText('duration');
    var durationValue = getSelectedValue('duration');

    var packageEl = document.querySelector('input[name="package"]:checked');
    var pkg = packageEl ? packageEl.value : '';
    var packageName = pkg;
    if (pkg === 'basic') packageName = 'Package Kahwin A (RM550)';
    else if (pkg === 'standard') packageName = 'Package Kahwin B (RM750)';
    else if (pkg === 'premium') packageName = 'Open Event (RM150/hr)';

    var addonsElements = document.querySelectorAll('input[name="addons"]:checked');
    var addons = [];
    var addonsValues = [];
    addonsElements.forEach(function (el) {
      addonsValues.push(el.value);
      if (el.value === 'coordinator') addons.push('Event Coordinator / Floor Manager');
      if (el.value === 'djcrew') addons.push('DJ Crew');
    });
    var addonsText = addons.length > 0 ? addons.join(', ') : 'None';

    var notes = document.getElementById('notes').value.trim();

    // Build WhatsApp message
    var text = '*Ameerul Iskandar - Booking Enquiry*\n\n';
    text += '*Personal Details*\n';
    text += 'Name: ' + fullName + '\n';
    text += 'Email: ' + email + '\n';
    text += 'Phone: ' + phone + '\n';
    if (company) text += 'Company: ' + company + '\n';

    text += '\n*Event Details*\n';
    text += 'Type: ' + eventType + '\n';
    text += 'Venue: ' + venue + '\n';
    text += 'Date: ' + eventDate + '\n';
    text += 'Time: ' + eventTime + '\n';
    text += 'Duration: ' + durationText + '\n';

    text += '\n*Package & Add-ons*\n';
    text += 'Package: ' + packageName + '\n';
    text += 'Add-ons: ' + addonsText + '\n';

    if (notes) {
      text += '\n*Additional Notes*\n' + notes;
    }

    var waNumber = '60162275267';
    var encodedText = encodeURIComponent(text);
    var whatsappUrl = 'https://wa.me/' + waNumber + '?text=' + encodedText;

    // Save booking to database via API, then redirect to WhatsApp
    var apiData = {
      fullName: fullName,
      email: email,
      phone: phone,
      company: company,
      eventType: eventType,
      venue: venue,
      eventDate: eventDate,
      eventTime: eventTime,
      duration: durationValue,
      package: pkg,
      addons: addonsValues.join(', '),
      notes: notes
    };

    fetch('api/booking.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(apiData)
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      // Redirect to WhatsApp after saving
      window.location.href = whatsappUrl;
    })
    .catch(function(err) {
      // Even if API fails, still redirect to WhatsApp
      console.error('Booking API error:', err);
      window.location.href = whatsappUrl;
    });
  });
})();

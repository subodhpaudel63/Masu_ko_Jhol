AOS.init({
  offset: '140', // 50% viewport height ka offset
});
document.addEventListener("DOMContentLoaded", function() {
  const loader = document.querySelector('.loader');
  setTimeout(() => {
    loader.style.opacity = '0';
    loader.style.display = 'none';
  }, 3000);
});

// Header functionality
document.addEventListener("DOMContentLoaded", function() {
  var getHamburgerIcon = document.getElementById("hamburger");
  var getHamburgerCrossIcon = document.getElementById("hamburger-cross");
  var getMobileMenu = document.getElementById("mobile-menu");

  // Search bar functionality
  const searchBtn = document.getElementById("searchBtn");
  const searchBtnMobile = document.getElementById("searchBtnMobile");
  const closeBtn = document.getElementById("search-close-btn");
  const searchCon = document.getElementById("search-container");

  // Shopping cart functionality
  var shoppingbtn = document.getElementById('shoppingbutton');
  var shoppingbtnMobile = document.getElementById('shoppingbuttonMobile');
  var shoppingCart = document.querySelector('.shopping-cart');
  var cartClose = document.querySelectorAll('.shopping-cart-header > i');

  // Check if elements exist before attaching event listeners
  if (getHamburgerIcon && getHamburgerCrossIcon && getMobileMenu) {
    // Open the mobile menu
    getHamburgerIcon.addEventListener("click", function () {
        getMobileMenu.classList.add("show");
    });

    // Close the mobile menu
    function closeMenu() {
        getMobileMenu.classList.remove("show");
    }

    // Close the mobile menu when the close icon is clicked
    getHamburgerCrossIcon.addEventListener("click", closeMenu);

    // Close the mobile menu if clicking outside of it
    document.addEventListener("click", function(event) {
        // Check if mobile menu and hamburger icon exist
        if (getMobileMenu && getHamburgerIcon) {
            var isClickInsideMenu = getMobileMenu.contains(event.target);
            var isClickOnIcon = getHamburgerIcon.contains(event.target);

            if (!isClickInsideMenu && !isClickOnIcon) {
                closeMenu();
            }
        }
    });
  }

  if (searchBtn) {
    // Show search container when search button is clicked
    searchBtn.addEventListener("click", (event) => {
      event.preventDefault();
      searchCon.classList.remove("d-none");
      requestAnimationFrame(() => {
        searchCon.classList.add("show");
      });
    });
  }

  if (searchBtnMobile) {
    // Show search container when mobile search button is clicked
    searchBtnMobile.addEventListener("click", (event) => {
      event.preventDefault();
      searchCon.classList.remove("d-none");
      requestAnimationFrame(() => {
        searchCon.classList.add("show");
      });
    });
  }

  if (closeBtn) {
    // Hide search container when close button is clicked
    closeBtn.addEventListener("click", () => {
      searchCon.classList.remove("show");
      setTimeout(() => {
        searchCon.classList.add("d-none");
      }, 500); // Delay hiding the search container to allow animation to complete
    });
  }

  if (shoppingbtn) {
    shoppingbtn.addEventListener('click', function(event) {
      event.preventDefault();
      console.log('chl');
      if (shoppingCart) {
        shoppingCart.style.right = "0";
      }
    });
  }

  if (shoppingbtnMobile) {
    shoppingbtnMobile.addEventListener('click', function(event) {
      event.preventDefault();
      console.log('chl');
      if (shoppingCart) {
        shoppingCart.style.right = "0";
      }
    });
  }

  if (cartClose && cartClose.length > 0) {
    cartClose.forEach(function(closeBtn) {
      closeBtn.addEventListener('click', function(event) {
        event.preventDefault();
        if (shoppingCart) {
          shoppingCart.style.right = "-100vw";
        }
      });
    });
  }
});

// Header scroll behavior
const header = document.querySelector('header');
const headerClass = document.querySelector('.header');

const checkScroll = () => {
  if (!header || !headerClass) return; //new line
  if (window.scrollY > 10) {
    header.classList.add('scrolled');
    headerClass.classList.remove('my-3');
    headerClass.classList.add('my-2');
    sessionStorage.setItem('scrolled', 'true');
    
  } else {
    header.classList.remove('scrolled');
    headerClass.classList.add('my-3');
    headerClass.classList.remove('my-2');
    sessionStorage.removeItem('scrolled');
  }
};

// Check scroll position on page load
if (sessionStorage.getItem('scrolled') === 'true') {
  header.classList.add('scrolled');
}
window.addEventListener('scroll', checkScroll);  
checkScroll(); // Initial check

// Update copyright year
// document.getElementById('copyrightCurrentYear').textContent = new Date().getFullYear();

const copyright = document.getElementById('copyrightCurrentYear');

if(copyright){
  copyright.textContent = new Date().getFullYear();
}


$('.testimonials .slider-content').slick({
  slidesToShow: 1,
  slidesToScroll: 1,
  arrows: false,
  fade: false,
  speed: 300,
  asNavFor: '.testimonials .slider-nav',
  draggable: true,
  swipe: true,
});

alert("JS FILE CONNECTED");


// Navigation Slider for Testimonials
$('.testimonials .slider-nav').slick({
  slidesToShow: 3,
  slidesToScroll: 1,
  asNavFor: '.testimonials .slider-content',
  dots: false,
  focusOnSelect: true,
  centerMode: true, // Center the active slide
  centerPadding: '0px',
  draggable: true,
  swipe: true,
  arrows: false, // Disable navigation arrows
  infinite: true,
});

$('.slider-nav').slick({
    slidesToShow: 3,
    slidesToScroll: 1,
    asNavFor: '.slider-content',
    dots: false,
    focusOnSelect: true,
    centerMode: true,
    centerPadding: '0px', // Prevents side images from overlapping the center
    arrows: false,
    infinite: true
});

$('.our-chefs .our-chef-slider-wrapper').slick({
  slidesToShow: 3,
  slidesToScroll: 1,
  arrows: true,
  focusOnSelect: true,
  centerMode: true, // Center the active slide
  centerPadding: '0px',
  fade: false,
  speed: 300,
  draggable: false,
  swipe: false,
  prevArrow: '<button class="slide-arrow prev-arrow"><i class="fas fa-chevron-left"></i></button>',
  nextArrow: '<button class="slide-arrow next-arrow"><i class="fas fa-chevron-right"></i></button>', // <-- comma added here
  responsive: [
    {
      breakpoint: 990,
      settings: {
        slidesToShow: 1,
      }
    }
  ]
});

/**
 * CONTACT FEEDBACK FORM — contact-form.js
 * ─────────────────────────────────────────
 * 
 *
 * No dependencies beyond vanilla JS. Self-contained IIFE so
 * it never pollutes the global scope.
 */

(function () {
  'use strict';

  /* ── Emoji labels for each star value ── */
  var RATING_LABELS = ['', 'Terrible 😞', 'Poor 😕', 'Okay 😐', 'Good 😊', 'Amazing! 🤩'];


  /* ════════════════════════════════════════════
     1. STAR RATING — emoji hint on selection
  ════════════════════════════════════════════ */
  var srHint = document.getElementById('srHint');

  document.querySelectorAll('.star-rating input').forEach(function (input) {
    input.addEventListener('change', function () {
      srHint.textContent = RATING_LABELS[this.value] || '';
      srHint.classList.add('visible');
    });
  });


  /* ════════════════════════════════════════════
     2. CATEGORY CHIPS — single-select toggle
  ════════════════════════════════════════════ */
  document.querySelectorAll('.feedback-chip').forEach(function (chip) {
    chip.addEventListener('click', function () {
      // Deactivate all, then activate this one
      document.querySelectorAll('.feedback-chip').forEach(function (c) {
        c.classList.remove('active');
      });
      chip.classList.add('active');
      document.getElementById('selectedCategory').value = chip.dataset.val;
    });
  });


  /* ════════════════════════════════════════════
     3. CHARACTER COUNTER (message textarea)
  ════════════════════════════════════════════ */
  var msgArea   = document.getElementById('ff-msg');
var charCount = document.getElementById('charCount');

var charDiv = null;

if(msgArea){
  charDiv = msgArea.closest('.ff-group').querySelector('.ff-char-count');
}


  msgArea.addEventListener('input', function () {
    var len = msgArea.value.length;
    charCount.textContent = len;

    // Colour cues: normal → amber at 400 → red at 500
    charDiv.className =
      'ff-char-count' +
      (len >= 500 ? ' limit' : len >= 400 ? ' warn' : '');
  });


  /* ════════════════════════════════════════════
     4. VALIDATION HELPERS
  ════════════════════════════════════════════ */

  /** Basic email format check */
  function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  /**
   * Apply or clear valid / invalid state on a field + its group.
   * @param {HTMLElement} field    — the input / textarea
   * @param {string}      groupId  — id of the wrapping .ff-group
   * @param {boolean}     ok       — whether the value passes validation
   * @param {boolean}     dirty    — false = just clear state (field untouched)
   */
  function setFieldState(field, groupId, ok, dirty) {
    var grp = document.getElementById(groupId);
    if (!dirty) {
      field.classList.remove('is-valid', 'is-invalid');
      if (grp) grp.classList.remove('has-error');
      return;
    }
    field.classList.toggle('is-valid',   ok);
    field.classList.toggle('is-invalid', !ok);
    if (grp) grp.classList.toggle('has-error', !ok);
  }

  /**
   * Attach blur + live-input validation to a field.
   * Validation only fires on input AFTER the field has been blurred once
   * (avoids showing errors while the user is still typing for the first time).
   *
   * @param {string}   fieldId  — id of the input
   * @param {string}   groupId  — id of the .ff-group wrapper
   * @param {Function} checkFn  — returns true if value is valid
   */
  function attachValidation(fieldId, groupId, checkFn) {
    var field   = document.getElementById(fieldId);
    var touched = false;

    field.addEventListener('blur', function () {
      touched = true;
      setFieldState(field, groupId, checkFn(field.value.trim()), true);
    });

    field.addEventListener('input', function () {
      if (touched) {
        setFieldState(
          field, groupId,
          checkFn(field.value.trim()),
          field.value.trim() !== ''
        );
      }
    });
  }

  /* Wire up the three required fields */
  attachValidation('ff-name',  'grp-name',  function (v) { return v.length >= 2; });
  attachValidation('ff-email', 'grp-email', isValidEmail);
  attachValidation('ff-msg',   'grp-msg',   function (v) { return v.length >= 5; });

  /* Phone is optional — just show the green tick if something is entered */
  var phoneField = document.getElementById('ff-phone');
  phoneField.addEventListener('blur', function () {
    if (phoneField.value.trim()) {
      phoneField.classList.add('is-valid');
    } else {
      phoneField.classList.remove('is-valid', 'is-invalid');
    }
  });


  /* ════════════════════════════════════════════
     5. SUBMIT
  ════════════════════════════════════════════ */
  var btn      = document.getElementById('sendFeedbackBtn');
  var nameF    = document.getElementById('ff-name');
  var emailF   = document.getElementById('ff-email');
  var msgF     = document.getElementById('ff-msg');
  var formNote = document.getElementById('formNote');

  btn.addEventListener('click', function () {

    /* Validate all required fields */
    var nameOk  = nameF.value.trim().length >= 2;
    var emailOk = isValidEmail(emailF.value.trim());
    var msgOk   = msgF.value.trim().length >= 5;

    setFieldState(nameF,  'grp-name',  nameOk,  true);
    setFieldState(emailF, 'grp-email', emailOk, true);
    setFieldState(msgF,   'grp-msg',   msgOk,   true);

    if (!nameOk || !emailOk || !msgOk) {
      formNote.textContent = '⚠ Please fix the highlighted fields.';
      /* Smooth-scroll to the first invalid field */
      var firstBad = document.querySelector('.ff-field.is-invalid');
      if (firstBad) firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    formNote.textContent = '';

    /* ── Loading state ── */
    btn.classList.add('loading');

    /*
      Replace the setTimeout below with a real fetch() / XMLHttpRequest
      to your server endpoint, then call showSuccess() in the callback.
    */
    setTimeout(function () {

      /* ── Green "sent" flash ── */
      btn.classList.remove('loading');
      btn.classList.add('sent');
      btn.querySelector('.btn-label').innerHTML = '✓ Sent!';

      setTimeout(function () {
        showSuccess();
      }, 600);

    }, 1800); /* ← simulated network delay; remove when using real fetch */
  });


  /* ════════════════════════════════════════════
     6. SUCCESS TRANSITION
  ════════════════════════════════════════════ */
  function showSuccess() {
    var wrap    = document.getElementById('feedbackFormWrap');
    var overlay = document.getElementById('formSuccessOverlay');

    /* Fade + slide the form out */
    wrap.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
    wrap.style.opacity    = '0';
    wrap.style.transform  = 'translateY(-10px)';

    setTimeout(function () {
      wrap.style.display = 'none';
      /* Show success overlay — CSS transitions handle the icon + text entrance */
      overlay.classList.add('visible');
    }, 350);
  }


  /* ════════════════════════════════════════════
     7. RESET ("Send Another" button)
  ════════════════════════════════════════════ */
  document.getElementById('sendAnotherBtn').addEventListener('click', function () {
    var overlay = document.getElementById('formSuccessOverlay');

    /* Fade the overlay out first */
    overlay.style.transition = 'opacity 0.3s ease';
    overlay.style.opacity    = '0';

    setTimeout(function () {
      overlay.classList.remove('visible');
      overlay.style.opacity = '';

      /* ── Clear all field values and states ── */
      [nameF, emailF, msgF, phoneField].forEach(function (f) {
        f.value = '';
        f.classList.remove('is-valid', 'is-invalid');
      });

      charCount.textContent = '0';
      charDiv.className = 'ff-char-count';

      ['grp-name', 'grp-email', 'grp-msg'].forEach(function (id) {
        var g = document.getElementById(id);
        if (g) g.classList.remove('has-error');
      });

      /* Clear chips */
      document.querySelectorAll('.feedback-chip').forEach(function (c) {
        c.classList.remove('active');
      });
      document.getElementById('selectedCategory').value = '';

      /* Clear stars */
      document.querySelectorAll('input[name="rating"]').forEach(function (r) {
        r.checked = false;
      });
      srHint.textContent = '';
      srHint.classList.remove('visible');

      formNote.textContent = '';

      /* ── Fade + slide the form back in ── */
      var wrap = document.getElementById('feedbackFormWrap');
      wrap.style.display   = '';
      wrap.style.opacity   = '0';
      wrap.style.transform = 'translateY(12px)';

      /* Double rAF ensures the display:'' is painted before we start the transition */
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          wrap.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
          wrap.style.opacity    = '1';
          wrap.style.transform  = 'translateY(0)';
        });
      });

      /* Reset the button back to its default state */
      btn.classList.remove('sent', 'loading');
      btn.querySelector('.btn-label').innerHTML =
        'Send Feedback &nbsp;<i class="fa fa-paper-plane"></i>';

    }, 300);
  });

})();


// index reservation forms starts ....



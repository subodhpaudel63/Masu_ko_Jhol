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
document.getElementById('copyrightCurrentYear').textContent = new Date().getFullYear();

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

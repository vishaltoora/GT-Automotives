// GT Automotives Frontend JavaScript

document.addEventListener("DOMContentLoaded", function () {
  // Mobile navigation toggle
  const mobileNavToggle = document.getElementById("mobile-nav-toggle");
  const navLinks = document.getElementById("nav-links");

  if (mobileNavToggle && navLinks) {
    mobileNavToggle.addEventListener("click", function () {
      navLinks.classList.toggle("active");
      mobileNavToggle.classList.toggle("active");
    });
  }

  // Close mobile menu when clicking outside
  document.addEventListener("click", function (event) {
    if (!event.target.closest(".navbar")) {
      if (navLinks) {
        navLinks.classList.remove("active");
      }
      if (mobileNavToggle) {
        mobileNavToggle.classList.remove("active");
      }
    }
  });

  // Smooth scrolling for anchor links
  const anchorLinks = document.querySelectorAll('a[href^="#"]');
  anchorLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href");
      const targetElement = document.querySelector(targetId);

      if (targetElement) {
        targetElement.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });

  // Form validation enhancement
  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const requiredFields = form.querySelectorAll("[required]");
      let isValid = true;

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          isValid = false;
          field.classList.add("error");
        } else {
          field.classList.remove("error");
        }
      });

      if (!isValid) {
        e.preventDefault();
        alert("Please fill in all required fields.");
      }
    });
  });

  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.style.opacity = "0";
      setTimeout(() => {
        alert.remove();
      }, 300);
    }, 5000);
  });

  // Lazy loading for images
  const images = document.querySelectorAll("img[data-src]");
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.removeAttribute("data-src");
        observer.unobserve(img);
      }
    });
  });

  images.forEach((img) => imageObserver.observe(img));
});

// Utility functions
window.GTUtils = {
  // Format price
  formatPrice: function (price) {
    return "$" + parseFloat(price).toFixed(2);
  },

  // Debounce function
  debounce: function (func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  },

  // Show loading state
  showLoading: function (element) {
    if (element) {
      element.innerHTML = '<div class="spinner"></div><p>Loading...</p>';
      element.classList.add("loading");
    }
  },

  // Hide loading state
  hideLoading: function (element) {
    if (element) {
      element.classList.remove("loading");
    }
  },
};

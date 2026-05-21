// ============================================
// AGARES CMS - Main JavaScript
// ============================================

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
  
  // ============================================
  // Mobile Navigation
  // ============================================
  const navbarToggle = document.querySelector('.navbar-toggle');
  const navbarMenu = document.querySelector('.navbar-menu');
  
  if (navbarToggle) {
    navbarToggle.addEventListener('click', function() {
      this.classList.toggle('active');
      navbarMenu.classList.toggle('active');
    });
  }
  
  // Close mobile menu when clicking outside
  document.addEventListener('click', function(e) {
    if (navbarMenu && navbarMenu.classList.contains('active')) {
      if (!e.target.closest('.navbar-menu') && !e.target.closest('.navbar-toggle')) {
        navbarToggle.classList.remove('active');
        navbarMenu.classList.remove('active');
      }
    }
  });
  
  // Mobile dropdown toggle
  const mobileDropdowns = document.querySelectorAll('.navbar-dropdown');
  mobileDropdowns.forEach(dropdown => {
    dropdown.addEventListener('click', function(e) {
      if (window.innerWidth <= 768) {
        e.preventDefault();
        this.classList.toggle('active');
      }
    });
  });
  
  // ============================================
  // Tabs
  // ============================================
  const tabButtons = document.querySelectorAll('.tab-button');
  
  tabButtons.forEach(button => {
    button.addEventListener('click', function() {
      const tabGroup = this.closest('.tabs');
      const targetId = this.getAttribute('data-tab');
      
      // Remove active class from all tabs in this group
      tabGroup.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
      });
      tabGroup.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
      });
      
      // Add active class to clicked tab and corresponding content
      this.classList.add('active');
      const targetContent = tabGroup.querySelector(`#${targetId}`);
      if (targetContent) {
        targetContent.classList.add('active');
      }
    });
  });
  
  // ============================================
  // Accordion
  // ============================================
  const accordionHeaders = document.querySelectorAll('.accordion-header');
  
  accordionHeaders.forEach(header => {
    header.addEventListener('click', function() {
      const item = this.closest('.accordion-item');
      const wasActive = item.classList.contains('active');
      
      // Optional: close other accordions (remove these lines for multi-open)
      const accordion = item.closest('.accordion');
      if (accordion) {
        accordion.querySelectorAll('.accordion-item').forEach(otherItem => {
          if (otherItem !== item) {
            otherItem.classList.remove('active');
          }
        });
      }
      
      // Toggle current accordion
      item.classList.toggle('active');
    });
  });
  
  // ============================================
  // Modals
  // ============================================
  const modalTriggers = document.querySelectorAll('[data-modal]');
  const modalCloses = document.querySelectorAll('.modal-close, [data-modal-close]');
  const modalBackdrops = document.querySelectorAll('.modal-backdrop');
  
  modalTriggers.forEach(trigger => {
    trigger.addEventListener('click', function(e) {
      e.preventDefault();
      const modalId = this.getAttribute('data-modal');
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    });
  });
  
  modalCloses.forEach(close => {
    close.addEventListener('click', function() {
      const modal = this.closest('.modal-backdrop');
      if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  });
  
  modalBackdrops.forEach(backdrop => {
    backdrop.addEventListener('click', function(e) {
      if (e.target === backdrop) {
        backdrop.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  });
  
  // ============================================
  // Toast Notifications
  // ============================================
  window.showToast = function(options) {
    const {
      type = 'info',
      title = 'Notification',
      message = '',
      duration = 5000
    } = options;
    
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    
    const icons = {
      success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>',
      error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>',
      warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 22h20L12 2z"/><path d="M12 9v4M12 17h.01"/></svg>',
      info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
      <div class="toast-icon">${icons[type]}</div>
      <div class="toast-content">
        <div class="toast-title">${title}</div>
        <p class="toast-message">${message}</p>
      </div>
      <button class="toast-close" aria-label="Close">&times;</button>
    `;
    
    container.appendChild(toast);
    
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => {
      toast.style.animation = 'slideInRight 0.3s ease reverse';
      setTimeout(() => toast.remove(), 300);
    });
    
    if (duration > 0) {
      setTimeout(() => {
        toast.style.animation = 'slideInRight 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
      }, duration);
    }
  };
  
  // ============================================
  // Code Copy Buttons
  // ============================================
  const copyButtons = document.querySelectorAll('.code-copy');
  
  copyButtons.forEach(button => {
    button.addEventListener('click', function() {
      const codeBlock = this.closest('.code-block');
      const code = codeBlock.querySelector('code').textContent;
      
      navigator.clipboard.writeText(code).then(() => {
        const originalText = this.textContent;
        this.textContent = 'Copied!';
        this.classList.add('copied');
        
        setTimeout(() => {
          this.textContent = originalText;
          this.classList.remove('copied');
        }, 2000);
      });
    });
  });
  
  // ============================================
  // Pricing Toggle (Monthly/Yearly)
  // ============================================
  const pricingToggle = document.getElementById('pricing-toggle');
  const pricingPrices = document.querySelectorAll('[data-monthly-price]');
  
  if (pricingToggle) {
    pricingToggle.addEventListener('change', function() {
      const isYearly = this.checked;
      
      pricingPrices.forEach(priceElement => {
        const monthlyPrice = priceElement.getAttribute('data-monthly-price');
        const yearlyPrice = priceElement.getAttribute('data-yearly-price');
        
        priceElement.textContent = isYearly ? yearlyPrice : monthlyPrice;
      });
    });
  }
  
  // ============================================
  // Blog Category Filter
  // ============================================
  const categoryFilters = document.querySelectorAll('[data-filter]');
  const blogCards = document.querySelectorAll('[data-category]');
  
  categoryFilters.forEach(filter => {
    filter.addEventListener('click', function() {
      const category = this.getAttribute('data-filter');
      
      // Update active state
      categoryFilters.forEach(f => f.classList.remove('active'));
      this.classList.add('active');
      
      // Filter blog cards
      blogCards.forEach(card => {
        if (category === 'all' || card.getAttribute('data-category') === category) {
          card.style.display = '';
          card.style.animation = 'fadeIn 0.3s ease';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
  
  // ============================================
  // Smooth Scroll
  // ============================================
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      if (href !== '#' && href !== '#!') {
        const target = document.querySelector(href);
        if (target) {
          e.preventDefault();
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      }
    });
  });
  
  // ============================================
  // Scroll Spy (for docs/features pages)
  // ============================================
  const sections = document.querySelectorAll('[data-section]');
  const navLinks = document.querySelectorAll('[data-scroll-spy]');
  
  if (sections.length > 0 && navLinks.length > 0) {
    const observerOptions = {
      root: null,
      rootMargin: '-20% 0px -80% 0px',
      threshold: 0
    };
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const id = entry.target.getAttribute('id');
          navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${id}`) {
              link.classList.add('active');
            }
          });
        }
      });
    }, observerOptions);
    
    sections.forEach(section => observer.observe(section));
  }
  
  // ============================================
  // Form Validation (Contact Form)
  // ============================================
  const contactForm = document.getElementById('contact-form');
  
  if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Get form data
      const formData = new FormData(this);
      const data = Object.fromEntries(formData);
      
      // Simple validation
      let isValid = true;
      let errorMessage = '';
      
      if (!data.name || data.name.trim().length < 2) {
        isValid = false;
        errorMessage = 'Please enter a valid name';
      } else if (!data.email || !data.email.includes('@')) {
        isValid = false;
        errorMessage = 'Please enter a valid email';
      } else if (!data.message || data.message.trim().length < 10) {
        isValid = false;
        errorMessage = 'Please enter a message (at least 10 characters)';
      }
      
      if (!isValid) {
        showToast({
          type: 'error',
          title: 'Validation Error',
          message: errorMessage
        });
        return;
      }
      
      // Show success state
      showToast({
        type: 'success',
        title: 'Message Sent!',
        message: 'Thank you for contacting us. We\'ll get back to you soon.'
      });
      
      // Reset form
      this.reset();
    });
  }
  
  // ============================================
  // Stats Counter Animation
  // ============================================
  const statValues = document.querySelectorAll('.stat-value[data-count]');
  
  const animateCounter = (element) => {
    const target = parseInt(element.getAttribute('data-count'));
    const duration = 2000;
    const increment = target / (duration / 16);
    let current = 0;
    
    const updateCounter = () => {
      current += increment;
      if (current < target) {
        element.textContent = Math.floor(current).toLocaleString();
        requestAnimationFrame(updateCounter);
      } else {
        element.textContent = target.toLocaleString() + (element.getAttribute('data-suffix') || '');
      }
    };
    
    updateCounter();
  };
  
  if (statValues.length > 0) {
    const statsObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          statsObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    
    statValues.forEach(stat => statsObserver.observe(stat));
  }
  
  // ============================================
  // Simple Chart Rendering (for demo page)
  // ============================================
  window.renderSimpleChart = function(canvasId, data) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    const padding = 40;
    const chartWidth = width - padding * 2;
    const chartHeight = height - padding * 2;
    
    // Clear canvas
    ctx.clearRect(0, 0, width, height);
    
    // Find max value
    const maxValue = Math.max(...data.map(d => d.value));
    
    // Draw grid lines
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
    ctx.lineWidth = 1;
    for (let i = 0; i <= 4; i++) {
      const y = padding + (chartHeight / 4) * i;
      ctx.beginPath();
      ctx.moveTo(padding, y);
      ctx.lineTo(width - padding, y);
      ctx.stroke();
    }
    
    // Draw bars
    const barWidth = chartWidth / data.length - 10;
    const gradient = ctx.createLinearGradient(0, padding, 0, height - padding);
    gradient.addColorStop(0, '#7c3aed');
    gradient.addColorStop(1, '#06b6d4');
    
    data.forEach((item, index) => {
      const barHeight = (item.value / maxValue) * chartHeight;
      const x = padding + (chartWidth / data.length) * index + 5;
      const y = height - padding - barHeight;
      
      // Draw bar
      ctx.fillStyle = gradient;
      ctx.fillRect(x, y, barWidth, barHeight);
      
      // Draw label
      ctx.fillStyle = '#9ca3bc';
      ctx.font = '12px sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText(item.label, x + barWidth / 2, height - padding + 20);
      
      // Draw value
      ctx.fillStyle = '#e8eaf0';
      ctx.fillText(item.value, x + barWidth / 2, y - 5);
    });
  };
  
  // Render demo charts if on demo page
  if (document.getElementById('visitors-chart')) {
    renderSimpleChart('visitors-chart', [
      { label: 'Mon', value: 1200 },
      { label: 'Tue', value: 1900 },
      { label: 'Wed', value: 1500 },
      { label: 'Thu', value: 2100 },
      { label: 'Fri', value: 1800 },
      { label: 'Sat', value: 900 },
      { label: 'Sun', value: 1100 }
    ]);
  }
  
  // ============================================
  // Demo Page Interactions
  // ============================================
  
  // Simulate dashboard data updates
  const updateDashboardStats = () => {
    const stats = document.querySelectorAll('.demo-stat-value');
    stats.forEach(stat => {
      const currentValue = parseInt(stat.textContent.replace(/[^0-9]/g, ''));
      const change = Math.floor(Math.random() * 10) - 5;
      const newValue = Math.max(0, currentValue + change);
      stat.textContent = newValue.toLocaleString();
    });
  };
  
  // Update stats every 3 seconds on demo page
  if (document.querySelector('.demo-stat-value')) {
    setInterval(updateDashboardStats, 3000);
  }
  
  // ============================================
  // Tooltips (simple implementation)
  // ============================================
  const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
  
  tooltipTriggers.forEach(trigger => {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.style.cssText = `
      position: absolute;
      background: var(--color-bg-elevated);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-md);
      padding: var(--space-sm) var(--space-md);
      font-size: var(--text-sm);
      color: var(--color-text-primary);
      pointer-events: none;
      opacity: 0;
      transition: opacity 0.2s;
      z-index: 1000;
      white-space: nowrap;
    `;
    tooltip.textContent = trigger.getAttribute('data-tooltip');
    document.body.appendChild(tooltip);
    
    trigger.addEventListener('mouseenter', function(e) {
      const rect = this.getBoundingClientRect();
      tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + 'px';
      tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
      tooltip.style.opacity = '1';
    });
    
    trigger.addEventListener('mouseleave', function() {
      tooltip.style.opacity = '0';
    });
  });
  
  // ============================================
  // Initialize demo notifications
  // ============================================
  const demoNotificationsBtn = document.getElementById('demo-notifications');
  if (demoNotificationsBtn) {
    demoNotificationsBtn.addEventListener('click', function() {
      const notifications = [
        { type: 'success', title: 'Page Published', message: 'Your page "About Us" is now live.' },
        { type: 'info', title: 'New Comment', message: 'John Doe commented on your post.' },
        { type: 'warning', title: 'Storage Alert', message: 'You\'re using 80% of your storage.' }
      ];
      
      const random = notifications[Math.floor(Math.random() * notifications.length)];
      showToast(random);
    });
  }
  
});

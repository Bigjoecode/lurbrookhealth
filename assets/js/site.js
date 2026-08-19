(() => {
  const toast = document.querySelector('#toast');
  const showToast = message => { if (!toast) return; toast.textContent = message; toast.classList.add('show'); setTimeout(() => toast.classList.remove('show'), 2600); };
  document.querySelector('.menu-toggle')?.addEventListener('click', e => {
    const nav = document.querySelector('.main-nav'); nav.classList.toggle('open'); e.currentTarget.setAttribute('aria-expanded', nav.classList.contains('open'));
  });
  document.querySelector('.category-menu > button')?.addEventListener('click', e => e.currentTarget.parentElement.classList.toggle('open'));
  document.addEventListener('click', async e => {
    const button = e.target.closest('[data-add-cart]');
    if (!button) return;
    e.preventDefault(); button.disabled = true;
    const quantity = document.querySelector('#product-quantity')?.value || button.dataset.quantity || 1;
    const body = new URLSearchParams({action:'add', product_id:button.dataset.addCart, quantity, csrf:window.LURBROOK.csrf});
    try {
      const response = await fetch(window.LURBROOK.base + '/cart-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body});
      const data = await response.json();
      if (!response.ok) throw new Error(data.message || 'Could not add item');
      document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = data.count);
      const prompt = document.querySelector('#cart-prompt');
      if (prompt) {
        prompt.querySelector('#cart-prompt-product').textContent = data.product_name;
        prompt.hidden = false;
        document.body.classList.add('prompt-open');
        prompt.querySelector('.cart-prompt-close')?.focus();
      } else { showToast(data.message); }
    } catch (error) { showToast(error.message); }
    finally { button.disabled = false; }
  });
  document.querySelectorAll('[data-qty]').forEach(button => button.addEventListener('click', () => {
    const input = document.querySelector('#product-quantity'); if (!input) return;
    input.value = Math.max(1, Math.min(Number(input.max || 99), Number(input.value) + Number(button.dataset.qty)));
  }));
  document.querySelectorAll('[data-gallery-src]').forEach(button => button.addEventListener('click', () => {
    const main = document.querySelector('#product-main-photo');
    if (main) main.src = button.dataset.gallerySrc;
    document.querySelectorAll('[data-gallery-src]').forEach(item => item.classList.remove('active'));
    button.classList.add('active');
  }));
  document.querySelectorAll('[data-close-cart-prompt]').forEach(button => button.addEventListener('click', () => {
    const prompt = document.querySelector('#cart-prompt');
    if (prompt) prompt.hidden = true;
    document.body.classList.remove('prompt-open');
  }));
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') document.querySelector('[data-close-cart-prompt]')?.click();
  });
  const slider = document.querySelector('[data-hero-slider]');
  if (slider) {
    const slides = [...slider.querySelectorAll('[data-hero-slide]')];
    const dots = [...slider.querySelectorAll('[data-hero-dot]')];
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const interval = 6500;
    let current = 0;
    let timer;
    let touchStart = 0;
    const resetProgress = () => {
      slider.classList.add('reset-progress');
      void slider.offsetWidth;
      slider.classList.remove('reset-progress');
    };
    const showSlide = index => {
      current = (index + slides.length) % slides.length;
      slides.forEach((slide, i) => {
        const active = i === current;
        slide.classList.toggle('active', active);
        slide.setAttribute('aria-hidden', String(!active));
        slide.querySelectorAll('a,button,input,select,textarea').forEach(control => {
          if (active) control.removeAttribute('tabindex');
          else control.setAttribute('tabindex', '-1');
        });
      });
      dots.forEach((dot, i) => {
        const active = i === current;
        dot.classList.toggle('active', active);
        dot.setAttribute('aria-selected', String(active));
      });
      resetProgress();
    };
    const stop = () => { window.clearInterval(timer); timer = undefined; slider.classList.add('is-paused'); };
    const start = () => {
      window.clearInterval(timer);
      slider.classList.remove('is-paused');
      if (!reducedMotion.matches) timer = window.setInterval(() => showSlide(current + 1), interval);
    };
    slider.querySelector('[data-hero-prev]')?.addEventListener('click', () => { showSlide(current - 1); start(); });
    slider.querySelector('[data-hero-next]')?.addEventListener('click', () => { showSlide(current + 1); start(); });
    dots.forEach((dot, i) => dot.addEventListener('click', () => { showSlide(i); start(); }));
    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('focusin', stop);
    slider.addEventListener('focusout', event => { if (!slider.contains(event.relatedTarget)) start(); });
    slider.addEventListener('touchstart', event => { touchStart = event.changedTouches[0].clientX; }, {passive:true});
    slider.addEventListener('touchend', event => {
      const distance = event.changedTouches[0].clientX - touchStart;
      if (Math.abs(distance) > 50) { showSlide(current + (distance < 0 ? 1 : -1)); start(); }
    }, {passive:true});
    document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
    reducedMotion.addEventListener?.('change', () => reducedMotion.matches ? stop() : start());
    showSlide(0);
    start();
  }
})();

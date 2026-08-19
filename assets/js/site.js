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
      document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = data.count); showToast(data.message);
    } catch (error) { showToast(error.message); }
    finally { button.disabled = false; }
  });
  document.querySelectorAll('[data-qty]').forEach(button => button.addEventListener('click', () => {
    const input = document.querySelector('#product-quantity'); if (!input) return;
    input.value = Math.max(1, Math.min(Number(input.max || 99), Number(input.value) + Number(button.dataset.qty)));
  }));
})();

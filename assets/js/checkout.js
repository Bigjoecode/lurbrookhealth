document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#checkout-form');
  const errorBox = document.querySelector('#checkout-error');
  const showError = message => { errorBox.textContent = message; errorBox.hidden = false; errorBox.scrollIntoView({behavior:'smooth',block:'center'}); };
  paypal.Buttons({
    style:{layout:'vertical',shape:'rect',label:'paypal'},
    createOrder: async () => {
      if (!form.reportValidity()) throw new Error('Please complete your delivery details.');
      errorBox.hidden = true;
      const response = await fetch(window.LURBROOK.base + '/paypal.php?action=create',{method:'POST',body:new FormData(form)});
      const data = await response.json();
      if (!response.ok || !data.id) throw new Error(data.message || 'PayPal could not start the payment.');
      return data.id;
    },
    onApprove: async data => {
      const body = new URLSearchParams({csrf:window.LURBROOK.csrf,order_id:data.orderID});
      const response = await fetch(window.LURBROOK.base + '/paypal.php?action=capture',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body});
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message || 'The payment could not be confirmed.');
      window.location.href = window.LURBROOK.base + '/order-confirmed?order=' + encodeURIComponent(result.order_number);
    },
    onCancel: () => showError('Payment was cancelled. Your shopping bag has not been changed.'),
    onError: error => showError(error.message || 'PayPal could not process the payment. Please try again.')
  }).render('#paypal-button-container');
});

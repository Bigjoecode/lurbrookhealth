(() => {
  const assistant = document.querySelector('[data-sales-assistant]');
  if (!assistant) return;
  const panel = assistant.querySelector('.assistant-panel');
  const launcher = assistant.querySelector('[data-assistant-open]');
  const messages = assistant.querySelector('[data-assistant-messages]');
  const chatForm = assistant.querySelector('[data-assistant-form]');
  const input = chatForm.querySelector('textarea');
  const enquiryForm = assistant.querySelector('[data-assistant-enquiry-form]');
  const history = [];

  const setOpen = open => {
    panel.hidden = !open;
    launcher.setAttribute('aria-expanded', String(open));
    assistant.classList.toggle('open', open);
    if (open) window.setTimeout(() => input.focus(), 80);
  };
  const scrollToLatest = () => { messages.scrollTop = messages.scrollHeight; };
  const addMessage = (text, role = 'bot', loading = false) => {
    const bubble = document.createElement('div');
    bubble.className = `assistant-message assistant-message-${role}${loading ? ' assistant-loading' : ''}`;
    bubble.textContent = text;
    messages.appendChild(bubble);
    scrollToLatest();
    return bubble;
  };
  const addActions = data => {
    if (!data.show_checkout && !data.policy_url && !data.show_enquiry) return;
    const actions = document.createElement('div');
    actions.className = 'assistant-actions';
    if (data.show_checkout) {
      const checkout = document.createElement('a'); checkout.href = data.checkout_url; checkout.textContent = 'Go to checkout →'; checkout.className = 'assistant-action-primary'; actions.appendChild(checkout);
      const bag = document.createElement('a'); bag.href = data.cart_url; bag.textContent = 'View bag'; actions.appendChild(bag);
    }
    if (data.policy_url) { const policy = document.createElement('a'); policy.href = data.policy_url; policy.textContent = 'Read full policy →'; actions.appendChild(policy); }
    if (data.show_enquiry) { const enquiry = document.createElement('button'); enquiry.type = 'button'; enquiry.textContent = 'Send an enquiry →'; enquiry.addEventListener('click', () => { enquiryForm.hidden = false; enquiryForm.querySelector('input').focus(); }); actions.appendChild(enquiry); }
    messages.appendChild(actions);
  };
  const addProducts = products => {
    if (!products?.length) return;
    const list = document.createElement('div'); list.className = 'assistant-products';
    products.forEach(product => {
      const card = document.createElement('article'); card.className = 'assistant-product';
      const imageLink = document.createElement('a'); imageLink.href = product.url;
      const image = document.createElement('img'); image.src = product.image; image.alt = ''; imageLink.appendChild(image);
      const copy = document.createElement('div');
      const category = document.createElement('small'); category.textContent = product.category;
      const name = document.createElement('a'); name.href = product.url; name.className = 'assistant-product-name'; name.textContent = product.name;
      const row = document.createElement('div'); const price = document.createElement('strong'); price.textContent = `£${Number(product.price).toFixed(2)}`;
      const add = document.createElement('button'); add.type = 'button'; add.dataset.addCart = product.id; add.dataset.quantity = '1'; add.textContent = 'Add';
      row.append(price, add); copy.append(category, name, row); card.append(imageLink, copy); list.appendChild(card);
    });
    messages.appendChild(list); scrollToLatest();
  };
  const post = async body => {
    const response = await fetch(`${window.LURBROOK.base}/assistant-api.php`, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({...body, csrf:window.LURBROOK.csrf})});
    const data = await response.json().catch(() => ({message:'The assistant could not respond just now.'}));
    if (!response.ok) throw new Error(data.message || 'The assistant could not respond just now.');
    return data;
  };
  const send = async text => {
    text = text.trim(); if (!text) return;
    setOpen(true); addMessage(text, 'user');
    const sentHistory = [...history]; history.push({role:'user',content:text});
    const loading = addMessage('Thinking…', 'bot', true);
    input.disabled = true;
    try {
      const data = await post({action:'chat', message:text, history:JSON.stringify(sentHistory)});
      loading.remove(); addMessage(data.reply, 'bot'); history.push({role:'assistant',content:data.reply});
      addProducts(data.products); addActions(data); scrollToLatest();
    } catch (error) { loading.remove(); addMessage(error.message, 'bot'); }
    finally { input.disabled = false; input.focus(); }
  };

  launcher.addEventListener('click', () => setOpen(panel.hidden));
  assistant.querySelector('[data-assistant-close]').addEventListener('click', () => setOpen(false));
  assistant.querySelector('[data-assistant-enquiry-close]').addEventListener('click', () => { enquiryForm.hidden = true; });
  assistant.querySelectorAll('[data-assistant-prompt]').forEach(button => button.addEventListener('click', () => send(button.dataset.assistantPrompt)));
  chatForm.addEventListener('submit', event => { event.preventDefault(); const value = input.value; input.value = ''; send(value); });
  input.addEventListener('keydown', event => { if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); chatForm.requestSubmit(); } });
  enquiryForm.addEventListener('submit', async event => {
    event.preventDefault(); const submit = enquiryForm.querySelector('[type="submit"]'); submit.disabled = true;
    try {
      const values = Object.fromEntries(new FormData(enquiryForm));
      const data = await post({action:'enquiry', ...values});
      enquiryForm.reset(); enquiryForm.hidden = true; addMessage(data.reply, 'bot'); scrollToLatest();
    } catch (error) { addMessage(error.message, 'bot'); }
    finally { submit.disabled = false; }
  });
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && !panel.hidden) setOpen(false); });
  if (window.location.hash === '#assistant') setOpen(true);
})();

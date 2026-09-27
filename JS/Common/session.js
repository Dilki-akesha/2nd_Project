'use strict';
window.HARVESTLY_BASE = document.querySelector('meta[name="app-base"]').content;
(() => {
  const token = document.querySelector('meta[name="csrf-token"]').content;
  const originalFetch = window.fetch.bind(window);
  window.fetch = (input, options = {}) => {
    const address = new URL(input instanceof Request ? input.url : input, location.href);
    const method = (options.method || (input instanceof Request ? input.method : 'GET')).toUpperCase();
    if (address.origin === location.origin && !['GET','HEAD'].includes(method)) {
      const headers = new Headers(options.headers || (input instanceof Request ? input.headers : undefined));
      headers.set('X-CSRF-Token', token);
      options = {...options, headers};
    }
    return originalFetch(input, options);
  };
  document.addEventListener('click', event => {
    const link = event.target.closest('a[href]');
    if (!link) return;
    const target = new URL(link.href, location.href);
    if (target.origin !== location.origin || target.searchParams.get('action') !== 'add_to_cart') return;
    event.preventDefault();
    const form = document.createElement('form');
    form.method = 'post'; form.action = target.pathname;
    target.searchParams.set('csrf_token',token);
    for (const [name,value] of target.searchParams) {
      const field = document.createElement('input'); field.type='hidden'; field.name=name; field.value=value; form.append(field);
    }
    document.body.append(form); form.submit();
  });
})();

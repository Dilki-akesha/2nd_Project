/* Harvestly local icon adapter — zero external icon/font libraries. */
(function () {
  'use strict';
  const icons = {
    account_balance:'▦', add:'＋', add_a_photo:'▣', admin_panel_settings:'◆', agriculture:'♧',
    arrow_back:'←', arrow_forward:'→', assignment:'✉', assignment_turned_in:'✓', bolt:'ϟ',
    calendar_month:'▣', call:'☎', cancel:'×', check:'✓', check_circle:'✓', chevron_right:'›',
    credit_card:'▤', dashboard:'▦', delete:'⌫', done_all:'✓', eco:'♧', error:'!', favorite:'♥',
    favorite_border:'♡', gpp_bad:'!', grass:'♧', handshake:'↔', healing:'✚', history:'↶', inbox:'▣',
    info:'i', inventory_2:'□', local_fire_department:'♨', local_shipping:'▰', location_on:'⌖',
    lock:'●', logout:'↪', mail:'✉', mark_email_unread:'✉', menu:'☰', monitor_weight:'⚖',
    notifications:'●', nutrition:'♧', open_in_new:'↗', password:'•••', payments:'Rs', person:'●',
    person_add:'＋', phonelink_lock:'●', photo_camera:'▣', rate_review:'☆', receipt_long:'▤',
    remove:'−', report_problem:'!', route:'↝', save:'✓', search:'⌕', search_off:'×', settings:'⚙',
    shopping_bag:'▱', shopping_cart:'▱', speed:'◷', star:'★', star_half:'☆', storefront:'▣',
    tune:'≡', verified:'✓', verified_user:'✓', visibility:'◉', visibility_off:'○'
  };
  function convert(el) {
    if (!el || el.dataset.localIconReady === '1') return;
    const key = (el.textContent || '').trim();
    if (!key) return;
    el.setAttribute('aria-label', el.getAttribute('aria-label') || key.replace(/_/g, ' '));
    el.setAttribute('title', el.getAttribute('title') || key.replace(/_/g, ' '));
    el.dataset.iconName = key;
    el.textContent = icons[key] || '•';
    el.dataset.localIconReady = '1';
  }
  function scan(root) {
    (root || document).querySelectorAll('.material-symbols-outlined').forEach(convert);
  }
  document.addEventListener('DOMContentLoaded', function () {
    scan(document);
    const observer = new MutationObserver(function (mutations) {
      for (const mutation of mutations) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType !== 1) return;
          if (node.matches && node.matches('.material-symbols-outlined')) convert(node);
          scan(node);
        });
      }
    });
    observer.observe(document.body, { childList:true, subtree:true });
  });
})();

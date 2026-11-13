/*
 * Mock-up helper: switches between the page's states.
 * - The "State" select in the mock-up bar picks a state; the choice is kept in the URL hash.
 * - Elements with data-show="a b" are visible only in states a and b.
 * - Buttons with data-go="state" switch to that state when clicked.
 * Not part of the design; the real pages decide their state from props.
 */
(function () {
  var select = document.getElementById('mockup-state');
  if (!select) { return; }

  function apply(state) {
    document.documentElement.setAttribute('data-state', state);
    select.value = state;
    document.querySelectorAll('[data-show]').forEach(function (el) {
      el.hidden = el.getAttribute('data-show').split(/\s+/).indexOf(state) === -1;
    });
    document.dispatchEvent(new CustomEvent('mockup:state', { detail: state }));
  }

  function fromHash() {
    var wanted = location.hash.replace('#', '');
    var exists = Array.prototype.some.call(select.options, function (o) { return o.value === wanted; });
    return exists ? wanted : select.options[0].value;
  }

  select.addEventListener('change', function () {
    history.replaceState(null, '', '#' + select.value);
    apply(select.value);
  });
  document.addEventListener('click', function (event) {
    var target = event.target.closest('[data-go]');
    if (!target) { return; }
    var state = target.getAttribute('data-go');
    history.replaceState(null, '', '#' + state);
    apply(state);
  });
  window.addEventListener('hashchange', function () { apply(fromHash()); });

  window.mockup = { go: function (state) { history.replaceState(null, '', '#' + state); apply(state); } };
  apply(fromHash());
})();

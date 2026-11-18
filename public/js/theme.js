(function () {
  const storageKey = 'benben-theme';
  const root = document.documentElement;

  function readTheme() {
    try {
      return localStorage.getItem(storageKey) === 'dark' ? 'dark' : 'light';
    } catch (error) {
      console.warn('Preferensi tema tidak dapat dibaca dari localStorage.', error);
      return 'light';
    }
  }

  function updateToggleButtons(theme) {
    const isDark = theme === 'dark';
    const label = isDark
      ? 'Tema gelap. Klik untuk mode terang'
      : 'Tema terang. Klik untuk mode gelap';
    const title = isDark
      ? 'Tema gelap — klik untuk mode terang'
      : 'Tema terang — klik untuk mode gelap';

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
      const icon = button.querySelector('[data-theme-icon]');
      const status = button.querySelector('[data-theme-status]');

      button.setAttribute('aria-label', label);
      button.setAttribute('aria-pressed', String(isDark));
      button.title = title;
      if (icon) icon.className = `bi ${isDark ? 'bi-moon-stars-fill' : 'bi-sun-fill'}`;
      if (status) status.textContent = isDark ? 'Mode gelap' : 'Mode terang';
    });
  }

  function applyTheme(theme) {
    const normalizedTheme = theme === 'dark' ? 'dark' : 'light';
    root.dataset.theme = normalizedTheme;
    root.style.colorScheme = normalizedTheme;
    updateToggleButtons(normalizedTheme);
    document.dispatchEvent(new CustomEvent('benben:themechange', {
      detail: { theme: normalizedTheme },
    }));
  }

  function bindToggleButtons() {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
      button.addEventListener('click', () => {
        const theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
        applyTheme(theme);

        try {
          localStorage.setItem(storageKey, theme);
        } catch (error) {
          console.warn('Preferensi tema tidak dapat disimpan ke localStorage.', error);
        }
      });
    });

    updateToggleButtons(root.dataset.theme || 'light');
  }

  applyTheme(readTheme());

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindToggleButtons, { once: true });
  } else {
    bindToggleButtons();
  }

  window.addEventListener('storage', (event) => {
    if (event.key === storageKey && (event.newValue === 'dark' || event.newValue === 'light')) {
      applyTheme(event.newValue);
    }
  });
})();

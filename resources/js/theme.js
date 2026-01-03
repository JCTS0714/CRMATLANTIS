const STORAGE_KEY = 'crmatlantis-theme';

export const getStoredTheme = () => {
  try {
    return localStorage.getItem(STORAGE_KEY);
  } catch {
    return null;
  }
};

export const getSystemTheme = () => {
  if (typeof window === 'undefined') return 'light';
  return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

export const getPreferredTheme = () => {
  return getStoredTheme() || getSystemTheme();
};

export const applyTheme = (theme) => {
  const resolved = theme === 'dark' ? 'dark' : 'light';
  const root = document.documentElement;
  root.classList.toggle('dark', resolved === 'dark');

  try {
    localStorage.setItem(STORAGE_KEY, resolved);
  } catch {
    // ignore
  }

  window.dispatchEvent(new CustomEvent('theme:changed', { detail: { theme: resolved } }));
  return resolved;
};

export const toggleTheme = () => {
  const isDark = document.documentElement.classList.contains('dark');
  return applyTheme(isDark ? 'light' : 'dark');
};

export const initTheme = () => {
  applyTheme(getPreferredTheme());
};

/**
 * CRM Atlantis Design System
 * Unified design tokens and utility classes built on Tailwind CSS
 */

export const designTokens = {
  // Color palette
  colors: {
    primary: {
      50: 'rgb(239 246 255)',  // blue-50
      100: 'rgb(219 234 254)', // blue-100
      500: 'rgb(59 130 246)',  // blue-500
      600: 'rgb(37 99 235)',   // blue-600
      700: 'rgb(29 78 216)',   // blue-700
      900: 'rgb(30 58 138)',   // blue-900
    },
    secondary: {
      50: 'rgb(248 250 252)',  // slate-50
      100: 'rgb(241 245 249)', // slate-100
      200: 'rgb(226 232 240)', // slate-200
      300: 'rgb(203 213 225)', // slate-300
      400: 'rgb(148 163 184)', // slate-400
      500: 'rgb(100 116 139)', // slate-500
      600: 'rgb(71 85 105)',   // slate-600
      700: 'rgb(51 65 85)',    // slate-700
      800: 'rgb(30 41 59)',    // slate-800
      900: 'rgb(15 23 42)',    // slate-900
    },
    success: {
      50: 'rgb(240 253 244)',  // green-50
      100: 'rgb(220 252 231)', // green-100
      500: 'rgb(34 197 94)',   // green-500
      600: 'rgb(22 163 74)',   // green-600
      700: 'rgb(21 128 61)',   // green-700
    },
    warning: {
      50: 'rgb(255 251 235)',  // amber-50
      100: 'rgb(254 243 199)', // amber-100
      500: 'rgb(245 158 11)',  // amber-500
      600: 'rgb(217 119 6)',   // amber-600
      700: 'rgb(180 83 9)',    // amber-700
    },
    danger: {
      50: 'rgb(254 242 242)',  // red-50
      100: 'rgb(254 226 226)', // red-100
      500: 'rgb(239 68 68)',   // red-500
      600: 'rgb(220 38 38)',   // red-600
      700: 'rgb(185 28 28)',   // red-700
    }
  },

  // Typography scale
  typography: {
    fontFamily: {
      sans: ['Nunito', 'ui-sans-serif', 'system-ui'],
    },
    fontSize: {
      xs: ['0.75rem', { lineHeight: '1rem' }],      // 12px
      sm: ['0.875rem', { lineHeight: '1.25rem' }],  // 14px
      base: ['1rem', { lineHeight: '1.5rem' }],     // 16px
      lg: ['1.125rem', { lineHeight: '1.75rem' }],  // 18px
      xl: ['1.25rem', { lineHeight: '1.75rem' }],   // 20px
      '2xl': ['1.5rem', { lineHeight: '2rem' }],    // 24px
      '3xl': ['1.875rem', { lineHeight: '2.25rem' }] // 30px
    },
    fontWeight: {
      normal: '400',
      medium: '500',
      semibold: '600',
      bold: '700'
    }
  },

  // Spacing scale
  spacing: {
    xs: '0.25rem',  // 4px
    sm: '0.5rem',   // 8px
    md: '1rem',     // 16px
    lg: '1.5rem',   // 24px
    xl: '2rem',     // 32px
    '2xl': '3rem',  // 48px
    '3xl': '4rem',  // 64px
  },

  // Border radius
  borderRadius: {
    sm: '0.125rem',   // 2px
    md: '0.375rem',   // 6px
    lg: '0.5rem',     // 8px
    xl: '0.75rem',    // 12px
  },

  // Shadows
  boxShadow: {
    sm: '0 1px 2px 0 rgb(0 0 0 / 0.05)',
    DEFAULT: '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
    md: '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
    lg: '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
  }
};

// Component variants
export const componentVariants = {
  // Button variants
  button: {
    primary: 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-4 focus:ring-blue-100 border-transparent',
    secondary: 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50 focus:ring-4 focus:ring-gray-100',
    success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-4 focus:ring-green-100 border-transparent',
    warning: 'bg-amber-500 text-white hover:bg-amber-600 focus:ring-4 focus:ring-amber-100 border-transparent',
    danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-4 focus:ring-red-100 border-transparent',
    ghost: 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:ring-4 focus:ring-gray-100',
  },

  // Button sizes
  buttonSize: {
    xs: 'px-2 py-1 text-xs',
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-sm',
    lg: 'px-6 py-3 text-base',
    xl: 'px-8 py-4 text-lg'
  },

  // Badge variants
  badge: {
    primary: 'bg-blue-100 text-blue-800',
    secondary: 'bg-gray-100 text-gray-800',
    success: 'bg-green-100 text-green-800',
    warning: 'bg-amber-100 text-amber-800',
    danger: 'bg-red-100 text-red-800',
    info: 'bg-sky-100 text-sky-800'
  },

  // Input states
  input: {
    base: 'w-full rounded-lg border px-3 py-2 text-sm shadow-sm transition-colors focus:outline-none focus:ring-4',
    default: 'border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-100',
    error: 'border-red-300 bg-red-50 text-red-900 placeholder:text-red-300 focus:border-red-500 focus:ring-red-100',
    success: 'border-green-300 bg-green-50 text-green-900 placeholder:text-green-300 focus:border-green-500 focus:ring-green-100',
    disabled: 'border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed'
  }
};

// Animation presets
export const animations = {
  transition: {
    fast: 'transition-all duration-150 ease-in-out',
    normal: 'transition-all duration-200 ease-in-out',
    slow: 'transition-all duration-300 ease-in-out'
  },
  
  entrance: {
    fadeIn: 'animate-in fade-in duration-200',
    slideUp: 'animate-in slide-in-from-bottom-4 duration-300',
    slideDown: 'animate-in slide-in-from-top-4 duration-300',
    scaleIn: 'animate-in zoom-in-95 duration-200'
  },
  
  exit: {
    fadeOut: 'animate-out fade-out duration-150',
    slideDown: 'animate-out slide-out-to-bottom-4 duration-200',
    slideUp: 'animate-out slide-out-to-top-4 duration-200',
    scaleOut: 'animate-out zoom-out-95 duration-150'
  }
};

// Layout patterns
export const layout = {
  container: {
    sm: 'max-w-lg mx-auto px-4',
    md: 'max-w-4xl mx-auto px-6',
    lg: 'max-w-6xl mx-auto px-8',
    xl: 'max-w-7xl mx-auto px-8',
    full: 'w-full px-4 sm:px-6 lg:px-8'
  },
  
  card: {
    base: 'bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden',
    padded: 'bg-white rounded-lg shadow-sm border border-gray-200 p-6',
    elevated: 'bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden'
  },
  
  header: {
    page: 'bg-gray-50 border-b border-gray-200 px-6 py-4',
    section: 'border-b border-gray-200 px-6 py-3 bg-gray-50'
  },

  table: {
    wrapper: 'bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden',
    header: 'text-xs uppercase bg-gray-50 text-gray-700',
    row: 'border-b border-gray-100 bg-white hover:bg-blue-50/40 transition-colors',
    cell: 'px-4 py-3'
  }
};

/**
 * Utility function to combine classes
 */
export const clsx = (...classes) => {
  return classes.filter(Boolean).join(' ');
};

/**
 * Get component variant classes
 */
export const getVariant = (component, variant, size = null) => {
  const variants = componentVariants[component];
  if (!variants) return '';
  
  let classes = variants[variant] || variants.default || '';
  
  if (size && componentVariants[`${component}Size`]) {
    classes += ' ' + componentVariants[`${component}Size`][size];
  }
  
  return classes;
};

export default {
  designTokens,
  componentVariants,
  animations,
  layout,
  clsx,
  getVariant
};
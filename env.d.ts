/// <reference types="vite/client" />

declare module '*.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<{}, {}, any>
  export default component
}

// Global window properties
declare global {
  interface Window {
    __AUTH_USER__?: {
      id: number;
      name: string;
      email: string;
      roles: Array<{ id: number; name: string; }>;
    };
    __APP_LOGO__?: string;
    __APP_NAME__?: string;
  }
}

export {};
import type {
  Ailment,
  ElementalType,
  Generation,
  PokemonGender,
  User,
} from '@/shared/types';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
  interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;
    [key: string]: string | boolean | undefined;
  }

  interface ImportMeta {
    readonly env: ImportMetaEnv;
    readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
  }
}

declare module '@inertiajs/core' {
  export interface InertiaConfig {
    sharedPageProps: {
      name: string;
      auth: {
        user: Pick<User, 'id' | 'name' | 'email'> | null;
      };
      labels: {
        elementalType: Record<ElementalType, string>;
        pokemonGender: Record<PokemonGender, string>;
        ailment: Record<Ailment, string>;
        generation: Record<Generation, string>;
      };
      authLayout: {
        sprites: string[];
      };
      sidebarOpen: boolean;
      [key: string]: unknown;
    };
  }
}

declare module 'vue' {
  interface ComponentCustomProperties {
    $inertia: typeof Router;
    $page: Page;
    $headManager: ReturnType<typeof createHeadManager>;
  }
}

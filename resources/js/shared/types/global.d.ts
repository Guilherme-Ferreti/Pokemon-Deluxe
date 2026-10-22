import type {
  Ailment,
  ElementalType,
  Generation,
  PokemonGender,
} from '@/shared/types/enums';
import type { User } from '@/shared/types/response';

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
      config: {
        game: {
          maxIncubatorsPerUser: number;
        };
      };
      authLayout: {
        sprites: string[];
      };
      [key: string]: unknown;
    };
    flashDataType: {
      toast?: {
        type: 'success' | 'error' | 'info' | 'warning';
        title: string;
        message: string;
      };
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

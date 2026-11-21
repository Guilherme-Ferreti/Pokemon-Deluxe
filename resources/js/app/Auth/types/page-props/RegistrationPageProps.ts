import type { Species } from '@/shared/types/response';

export type Starter = {
  slug: Species['slug'];
  name: Species['name'];
  portrait: Species['portraits']['regular'];
};

export type StarterRegion = {
  name: string;
  starters: Starter[];
};

export type RegistrationPageProps = {
  starterRegions: StarterRegion[];
};

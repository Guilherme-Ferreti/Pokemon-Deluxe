type RouteParameters = {
  'auth.web.login.create': never;
  'auth.web.login.store': never;
  'auth.web.registration.create': never;
  'auth.web.registration.store': never;
  'auth.web.logout': never;
  'dashboard.web.home': never;
  'dex.web.pokedex.index': never;
  'dex.web.pokedex.show': {
    species: string | number;
  };
  'dex.web.attackdex.index': never;
  'dex.api.species.autocomplete': never;
  'pokemon.web.party': never;
  'pokemon.web.box.index': never;
  'pokemon.web.nursery': never;
  'pokemon.web.incubators.purchase': {
    incubatorIndex: string | number;
  };
  'pokemon.web.incubators.place-egg': {
    incubatorIndex: string | number;
  };
  'pokemon.web.incubators.hatch-egg': {
    incubatorIndex: string | number;
  };
  'pokemon.web.incubators.catch-pokemon': {
    incubatorIndex: string | number;
  };
  'public.web.home': never;
  'storage.local.upload': {
    path: string | number;
  };
};
export function hasRoute(name: string): name is keyof RouteParameters {
  return Object.prototype.hasOwnProperty.call(routes, name);
}
export function route<T extends keyof RouteParameters>(
  name: T,
  parameters?: [RouteParameters[T]] extends [never]
    ? Record<string, never>
    : RouteParameters[T],
  absolute: boolean = false,
): string {
  if (!Object.prototype.hasOwnProperty.call(routes, name)) {
    throw new Error(`Route "${name}" not found.`);
  }

  let url: string = '/' + routes[name];

  if (parameters) {
    for (const [key, value] of Object.entries(parameters)) {
      url = url.replace(`{${key}}`, String(value));
    }
  }

  if (absolute) {
    url = window.location.origin + url;
  }

  return url;
}
const routes = {
  'auth.web.login.create': 'login',
  'auth.web.login.store': 'login',
  'auth.web.registration.create': 'registration',
  'auth.web.registration.store': 'registration',
  'auth.web.logout': 'logout',
  'dashboard.web.home': 'dashboard',
  'dex.web.pokedex.index': 'pokedex',
  'dex.web.pokedex.show': 'pokedex/{species}',
  'dex.web.attackdex.index': 'attackdex',
  'dex.api.species.autocomplete': 'dex/api/species/autocomplete',
  'pokemon.web.party': 'your-pokemon/party',
  'pokemon.web.box.index': 'your-pokemon/box',
  'pokemon.web.nursery': 'your-pokemon/nursery',
  'pokemon.web.incubators.purchase':
    'your-pokemon/incubator/{incubatorIndex}/purchase',
  'pokemon.web.incubators.place-egg':
    'your-pokemon/incubator/{incubatorIndex}/place-egg',
  'pokemon.web.incubators.hatch-egg':
    'your-pokemon/incubator/{incubatorIndex}/hatch-egg',
  'pokemon.web.incubators.catch-pokemon':
    'your-pokemon/incubator/{incubatorIndex}/catch-pokemon',
  'public.web.home': '',
  'storage.local.upload': 'storage/{path}',
};

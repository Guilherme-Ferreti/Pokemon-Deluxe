type RouteParameters = {
  'auth.web.login.create': never;
  'auth.web.login.store': never;
  'auth.web.register.create': never;
  'auth.web.register.store': never;
  'auth.web.logout': never;
  'dashboard.web.home': never;
  'dex.web.pokedex.index': never;
  'dex.web.pokedex.show': {
    species: string | number;
  };
  'pokemon.web.party': never;
  'pokemon.web.box.index': never;
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
  'auth.web.register.create': 'register',
  'auth.web.register.store': 'register',
  'auth.web.logout': 'logout',
  'dashboard.web.home': 'dashboard',
  'dex.web.pokedex.index': 'pokedex',
  'dex.web.pokedex.show': 'pokedex/{species}',
  'pokemon.web.party': 'your-pokemon/party',
  'pokemon.web.box.index': 'your-pokemon/box',
  'public.web.home': '',
  'storage.local.upload': 'storage/{path}',
};

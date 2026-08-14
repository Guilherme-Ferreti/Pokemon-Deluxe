type RouteParameters = {
  'web.public.home': never;
  'web.auth.login.create': never;
  'web.auth.login.store': never;
  'web.auth.register.create': never;
  'web.auth.register.store': never;
  'web.auth.logout': never;
  'web.dashboard.home': never;
  'web.dex.pokedex.index': never;
  'web.pokemon.party': never;
  'web.pokemon.box.index': never;
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
  'web.public.home': '',
  'web.auth.login.create': 'login',
  'web.auth.login.store': 'login',
  'web.auth.register.create': 'register',
  'web.auth.register.store': 'register',
  'web.auth.logout': 'logout',
  'web.dashboard.home': 'dashboard',
  'web.dex.pokedex.index': 'pokedex',
  'web.pokemon.party': 'your-pokemon/party',
  'web.pokemon.box.index': 'your-pokemon/box',
  'storage.local.upload': 'storage/{path}',
};

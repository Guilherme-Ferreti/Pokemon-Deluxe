export interface FilterOption<T extends string | number = string | number> {
  label: string;
  value: T;
}

export enum ElementalType {
  BUG = 'bug',
  DARK = 'dark',
  DRAGON = 'dragon',
  ELECTRIC = 'electric',
  FAIRY = 'fairy',
  FIGHTING = 'fighting',
  FIRE = 'fire',
  FLYING = 'flying',
  GHOST = 'ghost',
  GRASS = 'grass',
  GROUND = 'ground',
  ICE = 'ice',
  NORMAL = 'normal',
  POISON = 'poison',
  PSYCHIC = 'psychic',
  ROCK = 'rock',
  STEEL = 'steel',
  WATER = 'water',
}

export enum Generation {
  KANTO = 1,
  JOHTO = 2,
  HOENN = 3,
  SINNOH = 4,
  UNOVA = 5,
  KALOS = 6,
  ALOLA = 7,
  GALAR = 8,
  PALDEA = 9,
}

export enum PokeballType {
  STANDARD = 'standard',
  SPECIAL = 'special',
  APRICORN = 'apricorn',
}

export enum PokemonTrait {
  SHINY = 'shiny',
  BABY = 'baby',
  LEGENDARY = 'legendary',
  MYTHICAL = 'mythical',
  MEGA = 'mega',
  GMAX = 'gmax',
}

export enum SearchBoxSortBy {
  CAPTURE_DATE = 'capture-date',
  EXPERIENCE = 'experience',
  LEVEL = 'level',
}

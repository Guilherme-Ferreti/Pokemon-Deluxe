export interface PokemonPartyPageProps {
  party: {
    id: string;
    name: string;
    frontSprite: string | null;
    level: number;
  }[];
}

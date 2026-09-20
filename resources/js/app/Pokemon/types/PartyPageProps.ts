export interface PartyPageProps {
  party: {
    id: string;
    name: string;
    frontSprite: string | null;
    level: number;
  }[];
}

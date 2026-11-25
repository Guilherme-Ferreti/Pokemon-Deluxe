export type OwnedItem = {
  id: string;
  name: string;
  sprite: string | null;
  description: string;
  quantity: number;
};

export type InventoryIndexPageProps = {
  pokeballs: OwnedItem[];
  evolutionItems: OwnedItem[];
  tms: OwnedItem[];
  boostStones: OwnedItem[];
  keyItems: OwnedItem[];
};

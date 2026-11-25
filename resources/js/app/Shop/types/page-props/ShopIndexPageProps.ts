import type { Move } from '@/shared/types/response';

export type ShopItemType = 'pokeball' | 'evolution-item' | 'tm';

export type BuyableItem = {
  id: string;
  itemType: ShopItemType;
  name: string;
  pluralName: string;
  sprite: string | null;
  price: number;
  ownedQuantity: number;
};

export type ShopItem = BuyableItem & {
  effect: string;
};

export type ShopTm = BuyableItem & {
  sprite: string;
  type: Move['type'];
  power: Move['power'];
};

export type ShopIndexPageProps = {
  pokeballs: ShopItem[];
  evolutionItems: ShopItem[];
  tms: ShopTm[];
  maxStack: number;
  confirmPurchaseOver: number;
};

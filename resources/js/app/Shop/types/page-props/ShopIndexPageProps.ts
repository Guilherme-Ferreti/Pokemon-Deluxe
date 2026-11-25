import type { Move } from '@/shared/types/response';

export type ShopItem = {
  id: string;
  name: string;
  sprite: string | null;
  effect: string;
  price: number;
  ownedQuantity: number;
};

export type ShopTm = {
  id: Move['id'];
  name: string;
  sprite: string;
  type: Move['type'];
  power: Move['power'];
  price: number;
  ownedQuantity: number;
};

export type TmRotation = {
  startsAt: string;
  endsAt: string;
  tms: ShopTm[];
};

export type ShopIndexPageProps = {
  pokeballs: ShopItem[];
  evolutionItems: ShopItem[];
  tmRotation: TmRotation;
};

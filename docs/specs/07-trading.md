# Spec 7 — Trading

Status: agreed.

Players list a Pokémon on a trade market, other players make offers of Pokémon and items, and the seller accepts one.

## How it works

- A trade market: a player lists a Pokémon, other players make offers, the seller accepts one.
- Offers can mix Pokémon and items (boost stones, Mega Stones, TMs…). No money in offers.
- Direct trades and selling for money may come later (see Future ideas in the README).

## Listings

- The seller can write what they are looking for (free text, up to 200 characters), so players don't waste offers.
- Listings last 7 days.
- Listing fee: ₽ 500. Refunded if the listing expires with no accepted offer; kept if the trade happens or the seller cancels.

## Pokémon and items on hold

- A Pokémon in a listing or offer leaves the party and cannot battle, be released or evolve until the trade ends.
- A Pokémon can be in only one listing or offer at a time.
- Items in an offer are set aside from the inventory and returned if the offer is turned down or withdrawn.
- Locked Pokémon (Spec 4) cannot be listed or offered.

## Accepting and withdrawing

- Accepting an offer automatically turns down every other offer; their Pokémon and items go back to their owners.
- An offer can be withdrawn at any time before it is accepted.

## What a traded Pokémon keeps

- Its full pool of known moves, active moves, level, boost, shiny and original trainer.
- There are no trade evolutions: species that evolve by trade in the main games evolve by level or item in this game's seed data (Alakazam at level 45, Gengar at 36, Scizor with a Metal Coat).

## Notifications and history

- A notification bell in the top navigation bar lists new offers, accepted trades and expired listings. It is reusable later for missions and other features.
- Each player sees their own trade history.
- A public list of recent trades appears on the Trade market page and as a Dashboard widget (Spec 8).

## Trade market page

```
TRADE MARKET      [Search species] [Type ▾] [Shiny] [Min boost] [Min level]
┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────┐
│ (portrait)│ │ (portrait)│ │ (portrait)│ │ (portrait)│
│ Shiny Gible│ │ Dragonite │ │ Magikarp  │ │ Mewtwo    │
│ Lv 48 +30 │ │ Lv 100 +5 │ │ Lv 100+90 │ │ Lv 67 +0  │
│ 3 offers  │ │ 0 offers  │ │ 12 offers │ │ 5 offers  │
│ 6d left   │ │ 2d left   │ │ 1d left   │ │ 4d left   │
│ [Details] │ │ [Details] │ │ [Details] │ │ [Details] │
└───────────┘ └───────────┘ └───────────┘ └───────────┘
[ My listings ]   [ My offers ]   [ + New listing ]
RECENT TRADES (full list)
```

- A grid of listing cards like the Box, with similar filters: portrait, shiny, level, boost, offer count, time left.
- Below the grid, the full list of recent public trades.

## Listing page

```
┌──────────────────────────────────────────────────────────┐
│ (portrait) Shiny Gible ♂ Lv 48 · Boost +30 · by Ash     │
│ Moves: Dragon Claw · Earthquake · Crunch · Fire Fang      │
│ Looking for: Shiny Dragon types or boost stones           │
│ Expires in 6 days                              [Details]  │
├──────────────────────────────────────────────────────────┤
│ OFFERS (3)                                                │
│ Misty: Shiny Dratini Lv 30 +10 + 5 Boost Stones           │
│                                      [Details] [Accept]   │
│ Brock: Onix Lv 100 +50               [Details] [Accept]   │
│                                     [ Make an offer ]    │
└──────────────────────────────────────────────────────────┘
```

- Accept buttons are visible only to the seller; others see **Make an offer**, which opens a picker for their Pokémon and items.
- Listing cards and offer cards have a **Details** button that opens a modal with everything about the Pokémon: stats, active moves, the full known-moves pool, boost and shiny.

## Details to confirm (proposal)

Gaps found while splitting this spec into tasks. Each is a first proposal.

- **Declining:** the seller can decline a single offer; its Pokémon and items go back to the offerer.
- **One offer per player per listing:** to change it, withdraw it and make a new one.
- **Offer size:** at least one Pokémon or item; at most 6 Pokémon and 10 different items.
- **No self-trading:** a player can't make an offer on their own listing.
- **Listings can't be edited:** cancel (the fee is kept) and list again.
- **Box space:** an offer can only be accepted when both players have room for what they receive; otherwise accepting shows why.
- **Party:** the last party Pokémon can't be listed or offered, since battles need a party.
- **Holds:** a Pokémon on hold can't battle, join the party, evolve, be released, use items or enter the Battle Tower.
- **Expiry:** checked every few minutes; when a listing expires, its fee is refunded and every pending offer is declined.

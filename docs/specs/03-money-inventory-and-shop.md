# Spec 3 — Money, inventory and shop

Status: agreed.

Players earn Pokédollars (₽) from trainers, gyms, missions and selling items, and spend them in the shop, on incubators and on trade listings.

## Money

- One currency for now: Pokédollars (₽). A second currency, Battle Points, comes with the Battle Tower (Spec 6).
- **Earned from:** beating NPC trainers and gyms, missions, and selling items back to the shop.
- **Not earned from:** wild battles, or selling or releasing Pokémon (this would let players farm money by catching and selling).
- **Spent on:** the shop, unlocking incubators, and a trade listing fee (Spec 7). The fee comes back if the listing expires.
- New players start with ₽ 2,000, enough for 10 Poké Balls.
- The player's money shows in the top navigation bar on every page.

## Shop catalog

| Item | In the shop | Price |
| --- | --- | --- |
| Poké Ball | always | ₽ 200 |
| Great Ball | always | ₽ 600 |
| Ultra Ball | always | ₽ 1,200 |
| Special balls (Net, Dusk, Quick, Timer…) | always | ₽ 1,000 |
| Evolution stones | always | ₽ 3,000 |
| Normal TMs | 10 per day, rotating | ₽ 2,000 (power under 60), ₽ 5,000 (60–89), ₽ 10,000 (90+), ₽ 3,000 (status moves) |
| Collectible and event balls, Master Ball | never; earned only | — |
| Mega Stones, Z-Move and Dynamax TMs | never; earned only | — |
| Boost stones | never; earned only | — |

- TMs rotate every day at midnight server time. All players see the same 10.
- Players can buy any amount at once, up to the 9,999 stack limit.
- Shop items sell back for 50% of their price. Earned-only items have no price and cannot be sold to the shop, only traded.
- The first incubator is free; each next one costs ₽ 5,000 more than the last (2nd ₽ 5,000, 10th ₽ 45,000).

## Inventory page

```
INVENTORY                                       ₽ 12,450
[Balls] [Evolution items] [TMs] [Boost stones] [Key items]
┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐
│ (icon) │ │ (icon) │ │ (icon) │ │ (icon) │
│ Ultra  │ │ Net    │ │ Quick  │ │ Master │
│ Ball   │ │ Ball   │ │ Ball   │ │ Ball   │
│ × 42   │ │ × 7    │ │ × 3    │ │ × 1    │
└────────┘ └────────┘ └────────┘ └────────┘
```

- Tabs: Balls, Evolution items, TMs, Boost stones, Key items (such as the XP Share).
- A grid of item icons with quantities. Clicking one opens its details with **Use** and **Sell** buttons.
- **Use** opens a "choose a Pokémon" dialog. Items can also be used from the Pokémon page with a "choose an item" dialog.
- Empty tabs say what the tab holds and where to get it ("No TMs yet. Buy them in the Shop or earn them from missions.").

## Shop page

```
SHOP                                            ₽ 12,450
[Balls] [Evolution items] [TMs (today's 10)]
┌──────────────────────────────────────────────┐
│ (icon) Ultra Ball   2× catch rate   ₽ 1,200  │
│        You own: 42      [ − 1 + ]  [ Buy ]   │
└──────────────────────────────────────────────┘
```

- A list, one row per item: icon, name, short effect, price, "You own: X", a quantity picker and Buy.
- The TMs tab shows a countdown to the next rotation and each TM's type and power.
- Buy is disabled with a short reason when the player can't afford it or the stack is full.
- Purchases over ₽ 10,000 ask for confirmation.
- Selling happens from the inventory page, not the shop.

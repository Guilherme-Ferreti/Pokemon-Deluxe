# Spec 4 — Pokémon page

Status: agreed.

Each owned Pokémon gets its own page, opened from the Party and the Box, where the player manages moves, uses items, evolves, locks and releases it. Only the owner can see it for now.

```
Box › Shiny Charmeleon
┌──────────────────────────────────────────────────────────────┐
│ (big sprite)     Shiny Charmeleon  ♂                         │
│                  [FIRE] [Lv 36] [Boost +14] [Shiny]          │
│                  XP ███████░░░  720 / 1,000 XP to Lv 37      │
│                  Caught in a (ball) Ultra Ball · 3 Nov 2026  │
│ [Evolve] [Boost] [Stats] [Trade] [Remove from party] [Lock]  │
│ [Release]                                                    │
├──────────────────────────────────────────────────────────────┤
│ ACTIVE MOVES                                     [Teach TM]  │
│ 1 Flamethrower FIRE 90                            ↑↓ Swap    │
│ 2 Dragon Claw  DRGN 80                            ↑↓ Swap    │
│ 3 Slash        NORM 70                            ↑↓ Swap    │
│ 4 Will-O-Wisp  FIRE  —                            ↑↓ Swap    │
│ KNOWN MOVES (9)                                              │
│ Ember · Scratch · Growl · ...                                │
└──────────────────────────────────────────────────────────────┘
```

## Header

- Sprite, display name ("Shiny Charmeleon": no star or icon in the name), gender, types, and badges for level, boost and shiny (the "Shiny" badge may carry a star icon). An XP bar to the next level ("Max level" at level 100).
- Status badges when they apply: "Locked", "On trade" (in a listing or offer, Spec 7) and "In a Battle Tower run" (Spec 6), each with a tooltip saying what it blocks.
- Ball it was caught in and catch date. The original trainer isn't shown on this page.
- No nicknames.

## Actions

- **Evolve:** always shown, highlighted (primary) only when the Pokémon can evolve now. It opens a dialog listing every possible evolution as a radio choice with its requirement ("Evolves at level 36 using a Water Stone"); ones not yet possible are greyed out and can't be picked. Evolving asks for confirmation because it is permanent. When none is possible yet, the dialog says so above the greyed-out rows. A species with no evolutions gets a disabled Evolve button with the tooltip "This Pokémon cannot evolve any further" instead of a dialog.
- **Boost:** opens the boost stones dialog. TMs are taught with **Teach TM** (Moves) and evolution items and Mega Stones are used through **Evolve**, so there is no general "Use item" button. Items can also be used from the inventory, which opens this page's matching dialog.
- **Party:** "Remove from party" (with a confirmation) when it is in the party; "Add to party" when it is not. With a full party, "Add to party" opens a dialog to choose which party Pokémon goes to the Box in exchange. The same dialog is used from the Box card menu.
- **Trade:** opens the new-listing dialog at its details step (Spec 7). Disabled with the reason when the Pokémon is locked, on trade hold, in a Battle Tower run or the last party Pokémon; "View listing" instead when it is already listed.
- **Lock:** a locked Pokémon cannot be released or traded until unlocked. Locking asks for confirmation and says exactly that; unlocking does not ask.
- **Release:** permanent and gives nothing back. The player confirms by typing the Pokémon's display name ("Shiny Charmeleon" for a shiny one); boosted or shiny Pokémon get a stronger warning. Locked Pokémon cannot be released.

## Stats

- A "Stats" button in the header opens a dialog with HP, Attack and Defense and their current values. Under each one, the dialog lists what makes it up: level, boost, shiny.

## Moves

- Four active slots. **Swap** on a slot opens the known-moves pool to pick a replacement.
- The active moves can be **reordered** (drag and drop, or up and down arrows). Their order is the order of the move buttons in battle.
- The known-moves pool lists every move learned, with type and power. **Teach TM** opens the TMs the player owns.
- The known-moves pool and the Swap picker can be sorted by name or by power (strongest first) and filtered by type. Moves already in a slot are dimmed and say which slot in a tooltip.

## Boost stones

- Before using, the dialog shows three numbers: the boost now, the stones owned, and the chance the next stone fails ("0%" at +14, "41%" at +60). At the max boost the Boost button is disabled with the tooltip "This Pokémon is at maximum boost level" and no dialog opens.
- Players can use several at once: "use until +30" or "use up to 20 stones". The result shows stones used, successes and failures, and the new boost.

## Layout and access

- Header across the top, then the moves card at full width. Phone: everything stacks, header first.
- Only the owner can open the page. Other players see a Pokémon through the read-only details dialog of trading (Spec 7), not this page.
- Actions a hold blocks (trade hold: everything that changes the Pokémon; Battle Tower run: release, evolve and trade) are disabled with the reason.

## Party page

The existing Party page lists the party's up to 6 Pokémon. Their order is the **sending-out order**: the first one starts every battle that uses the party (wild, trainers, Elite Four). Gyms use the order picked for that challenge, and the Battle Tower its own team order (Spec 6).

- Reorder by dragging a Pokémon onto another place (desktop) or with the move-earlier and move-later arrow buttons (everywhere). Each change is saved right away. The first slot is marked "Leads".
- Each slot links to the Pokémon page. Empty slots link to the Box, where Pokémon are added to the party.
- A Pokémon added to the party joins at the end; one swapped in takes the place of the one it replaced; removing one closes the gap.
- The order can't change while a battle is in progress (the battle uses a snapshot of the party).

## Later

- A **history** section will be added in the future (for example times traded, battles won, date it reached level 100). It is left out to keep the first version simple.

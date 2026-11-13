# Spec 4 — Pokémon page

Status: agreed.

Each owned Pokémon gets its own page, opened from the Party and the Box, where the player manages moves, uses items, evolves, locks and releases it. Only the owner can see it for now.

```
← Box
┌──────────────────────────────────────────────────────────────┐
│ (big portrait)   ★ Shiny Charmeleon  ♂                        │
│                  [FIRE]    Lv 36   Boost +14                  │
│                  XP ███████░░░  7,200 / 10,000 → Lv 37        │
│                  Caught in Ultra Ball · 3 Nov 2026            │
│                  Original trainer: Ash                        │
│   [ Evolve ]  [ Use item ]  [ In party ✓ ]  [ Locked ]       │
├────────────────────────────┬─────────────────────────────────┤
│ STATS                      │ ACTIVE MOVES (4)                │
│ HP       520               │ [Flamethrower FIRE 90]  [swap]  │
│ Attack   +39%              │ [Slash        NORM 70]  [swap]  │
│ Defense  −25% dmg taken    │ [Dragon Claw  DRGN 80]  [swap]  │
│                            │ [Will-O-Wisp  FIRE  —]  [swap]  │
│                            │ KNOWN MOVES (9)    [Teach TM]   │
│                            │ Ember · Scratch · Growl · ...   │
└────────────────────────────┴─────────────────────────────────┘
```

## Header

- Portrait, shiny star, species name, gender, types, level, boost, and an XP bar to the next level.
- Ball it was caught in, catch date and original trainer.
- No nicknames.

## Actions

- **Evolve:** shown only when the Pokémon can evolve, with a small highlight. It opens a dialog listing every possible evolution with its requirement; ones not yet possible are greyed out with the reason ("Needs a Water Stone"). Evolving asks for confirmation because it is permanent.
- **Use item:** opens a "choose an item" dialog (TMs, boost stones, evolution items, Mega Stones).
- **Party:** add to or remove from the party here or on the Party page. A full party shows "Party full (6)".
- **Lock:** a locked Pokémon cannot be released or traded until unlocked. Locking asks for confirmation and says exactly that; unlocking does not ask.
- **Release:** permanent and gives nothing back. The player confirms by typing the Pokémon's display name ("Shiny Charmeleon" for a shiny one); boosted or shiny Pokémon get a stronger warning. Locked Pokémon cannot be released.

## Stats

- HP, Attack and Defense with their current values. Hovering a value shows what makes it up: level, boost, shiny.

## Moves

- Four active slots. **Swap** on a slot opens the known-moves pool to pick a replacement.
- The known-moves pool lists every move learned, with type and power. **Teach TM** opens the TMs the player owns.

## Boost stones

- Before using, the dialog shows the current failure chance ("+14 → +15: always succeeds", "+60 → +61: 40% chance to fail").
- Players can use several at once: "use until +30" or "use up to 20 stones". The result shows stones used, successes and failures, and the new boost.

## Layout and access

- Desktop: header across the top, stats on the left, moves on the right. Phone: everything stacks, header first.
- Only the owner can open the page. Whether trading and profiles reuse it or get their own page is decided once this page is built.

## Later

- A **history** section will be added in the future (for example times traded, battles won, date it reached level 100). It is left out to keep the first version simple.

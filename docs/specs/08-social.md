# Spec 8 — Social

Status: agreed.

Players can look each other up through public profiles, see notable catches and recent trades on the Dashboard, and find other players by name. There are no leaderboards.

## Rules

- **No leaderboards.**
- **Unique player names**, enforced at registration.
- **Player search** opens a profile. Profiles also open from trades and the latest-captures feed.
- **Latest captures:** notable catches and hatches only (shiny, legendary or mythical, level 60+). Shows the 10–20 most recent.
- **Recent trades:** a widget on the Dashboard and a fuller list on the Trade market page (Spec 7).

## Player profile

- Name, join date, Pokédex count and %, shiny count, best Battle Tower floor.
- Badges per region, with Elite Four and Champion marks.
- The player's active trade listings.
- Visible to every logged-in player. Does not show the Box, inventory or a showcase.

```
┌──────────────────────────────────────────────────────────┐
│ Ash                                Joined Nov 2026        │
│ Pokédex 702 / 1025 (68%) · Shinies 41 · Best Tower 137    │
├──────────────────────────────────────────────────────────┤
│ BADGES                                                    │
│ Kanto   ● ● ● ● ● ● ● ●   Elite Four ✓  Champion ✓        │
│ Johto   ● ● ● ● ● ○ ○ ○                                   │
├──────────────────────────────────────────────────────────┤
│ ACTIVE TRADE LISTINGS                                     │
│ [listing card] [listing card] [listing card]              │
└──────────────────────────────────────────────────────────┘
```

## Dashboard (redesign)

Today the Dashboard only shows a welcome header. It becomes the home page:

```
DASHBOARD                               Welcome back, Ash!
┌───────────────────────────────┬──────────────────────────┐
│ WAITING FOR YOU               │ DAILY MISSIONS  07:42:10 │
│ • 2 eggs ready to hatch  [Go] │ Catch 5 Water   3/5      │
│ • 1 mission to claim     [Go] │ Win 3 battles   3/3 ✓    │
│ • 3 new trade offers     [Go] │ Lv 60+ catch    0/1      │
│ • Battle in progress     [Go] │ Streak: 4 days           │
├───────────────────────────────┴──────────────────────────┤
│ LATEST CAPTURES                                          │
│ (portrait) Misty caught Shiny Gyarados Lv 34 · 2m ago  │
├──────────────────────────────────────────────────────────┤
│ RECENT TRADES                                            │
│ Misty traded Shiny Gible Lv 48 +30 to Brock for Onix +50     │
├──────────────────────────────────────────────────────────┤
│ SHORTCUTS  [Explore] [Party] [Box] [Shop] [Missions] …   │
└──────────────────────────────────────────────────────────┘
```

- **Waiting for you** comes first: eggs ready, missions to claim, trade offers, an unfinished battle; each line links straight there. Empty state: "All caught up."
- **Daily missions** is a compact version of the Missions page.
- Player names in the feeds open their profiles.
- Phone order: waiting, missions, captures, trades, shortcuts.

## Details to confirm (proposal)

Gaps found while splitting this spec into tasks. Each is a first proposal.

- **Profile link:** `/players/{id}`, so a name never has to be URL-safe.
- **Names:** 3–20 characters (letters, numbers, spaces, `-` and `_`), unique ignoring upper and lower case ("Ash" and "ash" can't both exist).
- **Pokédex count:** the species the player owns right now, out of every species in the Pokédex, matching the Pokédex page's own catch statistics. A traded or released Pokémon stops counting.
- **Shiny count:** shiny Pokémon the player owns right now.
- **Latest captures:** recorded at the moment of the catch or hatch (level as caught), so later level-ups, trades or releases don't change the feed. 20 most recent.
- **Player search:** by name, results as a list with join date and Pokédex %, 20 per page.
- **"Waiting for you"** on the Dashboard: eggs ready to hatch or catch, rewards to claim, new offers on the player's listings, an ongoing battle, an active Battle Tower run.

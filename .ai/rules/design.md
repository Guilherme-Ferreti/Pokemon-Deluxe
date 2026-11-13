---
paths:
  - 'resources/js/**'
---

# Design

How pages look and behave. Follow it for every new page or component; tasks in `docs/specs/tasks.md` add **UI suggestions** on top of it.

## Build from what exists

- Use, in this order: game components (`shared/components/game/`), app wrappers (`shared/components/{layout,content,form,list}/`), then shadcn-vue primitives (`shared/components/ui/`). Write a new component only when none of them fits.
- A missing shadcn-vue primitive (tabs, alert, alert-dialog, toggle-group, scroll-area…) is added with the shadcn-vue CLI, never written by hand.
- Icons come from `@tabler/icons-vue` only. Sizes: `size-4` inline with text, `size-5` in buttons and badges, `size-12` for empty states.
- Pokémon are shown with `SpeciesSprite` (full body, battles and encounters) or `SpeciesPortrait` (lists, grids, dialogs); names with `PokemonDisplayName`; types with `PokemonTypeBadges`; items with `ItemSprite`.

## Page structure

- Every page starts with `AppPageHeader` (title + one-sentence description). Detail pages add `AppBreadcrumbs` above it.
- Group content in `AppSection`s, each with an `AppSectionHeading` when the page has more than one section.
- Put related information in a `Card`; do not nest cards inside cards.
- Grids of cards use auto-fill columns (`grid-cols-[repeat(auto-fill,minmax(13rem,1fr))] gap-4`) or container queries on `@container/main-content`, never fixed column counts that break on phones.
- Spacing comes from the Tailwind scale: `gap-4` between cards and sections' children, `gap-2` inside a card, `gap-1` between badges.

## Buttons

- **One primary button per screen or dialog**: the main thing the player came to do (Battle, Buy, Claim, Send out). It uses the default `Button` variant.
- Secondary actions use `variant="outline"`; minor or repeated row actions use `variant="ghost"`.
- Anything permanent or that loses something (Release, Give up, Cancel listing) uses `variant="destructive"` and opens a confirmation dialog first. The dialog's confirm button is the destructive one; the other says what the player keeps ("Keep fighting", not "Cancel").
- Button labels are verbs: "Explore", "Throw ball", "Claim", not "OK" or "Submit".
- While a request runs, disable the button and the other actions it competes with (`form.processing`), so a double click never sends twice.

## Colour

- The theme is neutral (black, white, greys) and supports dark mode. Every colour added must have a `dark:` variant, as `PokemonTypeBadges` does.
- Colour carries meaning, never decoration:
  - Type colours: only from `PokemonTypeBadges`' palette.
  - Gold (`amber`/`yellow` ring or badge): shiny and rare things (shiny, legendary, mythical).
  - Green: success and "good" (HP above 50%, boosted ball, mission complete).
  - Yellow: warning (HP 20–50%, high boost-stone failure chance).
  - Red (`destructive`): danger and loss (HP below 20%, destructive buttons, errors).
- Never use colour alone to say something: pair it with text or an icon.

## Text

- Sentence case everywhere: "Keep exploring", not "Keep Exploring".
- Numbers players compare use thousands separators (₽ 12,450) and units (Lv 36, +14, ×3.5).
- Secondary information uses `text-sm text-muted-foreground`.

## States every screen must handle

- **Loading:** `Skeleton`s shaped like the content (deferred and optional props).
- **Empty:** `AppEmptyState` saying what goes here and how to get it, with a button when there is a next step ("No TMs yet. Buy them in the Shop.").
- **Error:** server errors as a toast (`ToastHelper` / the existing toaster); form errors under their field with `FieldError`.
- **Success:** a toast for actions that leave the page as it was (Egg placed); a dialog only for big moments (a catch, a level-up, an evolution).

## Dialogs

- `Dialog` for focused tasks and confirmations; `Popover` for small pickers next to their button; `Sheet` for long side panels.
- A dialog that must be answered (sending out after a faint) cannot be closed by clicking outside or pressing Escape, and has no close button.
- Long dialog content scrolls inside the dialog (`DialogScrollContent`); on phones, long dialogs may be full screen.

## Phones

- Design for 360px wide first. Columns stack in reading order; button pairs stack full width.
- Touch targets are at least 40px tall.
- No horizontal page scrolling; wide tables scroll inside their own container.

## Motion

- Keep it rare and short (150–500ms): HP and XP bars slide, new log lines fade in. No bouncing, shaking or looping animations unless a spec asks for one.
- Respect reduced motion (`motion-reduce:transition-none`).

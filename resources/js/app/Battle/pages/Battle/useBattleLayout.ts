import type { InjectionKey, Ref } from 'vue';
import { computed, inject, provide } from 'vue';

const isPhoneLayoutKey: InjectionKey<Readonly<Ref<boolean>>> =
  Symbol('isPhoneLayout');

/**
 * The battle page measures its grid and shares whether it is below the three-column layout,
 * so the Pokémon details open as a hover card on wide screens and as a sheet on phones.
 */
export function provideBattleLayout(
  isPhoneLayout: Readonly<Ref<boolean>>,
): void {
  provide(isPhoneLayoutKey, isPhoneLayout);
}

export function useBattleLayout(): { isPhoneLayout: Readonly<Ref<boolean>> } {
  return {
    isPhoneLayout: inject(
      isPhoneLayoutKey,
      computed(() => false),
    ),
  };
}

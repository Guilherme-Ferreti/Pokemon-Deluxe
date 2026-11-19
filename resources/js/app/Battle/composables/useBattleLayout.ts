import type { InjectionKey, Ref } from 'vue';
import { computed, inject, provide } from 'vue';

const isPhoneLayoutKey: InjectionKey<Readonly<Ref<boolean>>> =
  Symbol('isPhoneLayout');

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

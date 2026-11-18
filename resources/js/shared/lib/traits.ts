import { SpeciesTrait } from '@/shared/types/enums';

const mutuallyExclusiveTraits: Partial<Record<string, string[]>> = {
  [SpeciesTrait.BABY]: [
    SpeciesTrait.MEGA,
    SpeciesTrait.GMAX,
    SpeciesTrait.LEGENDARY,
    SpeciesTrait.MYTHICAL,
    SpeciesTrait.ULTRA_BEAST,
  ],
  [SpeciesTrait.LEGENDARY]: [
    SpeciesTrait.MYTHICAL,
    SpeciesTrait.BABY,
    SpeciesTrait.ULTRA_BEAST,
  ],
  [SpeciesTrait.MYTHICAL]: [
    SpeciesTrait.LEGENDARY,
    SpeciesTrait.BABY,
    SpeciesTrait.ULTRA_BEAST,
  ],
  [SpeciesTrait.MEGA]: [
    SpeciesTrait.GMAX,
    SpeciesTrait.BABY,
    SpeciesTrait.ULTRA_BEAST,
  ],
  [SpeciesTrait.GMAX]: [
    SpeciesTrait.MEGA,
    SpeciesTrait.BABY,
    SpeciesTrait.ULTRA_BEAST,
  ],
  [SpeciesTrait.ULTRA_BEAST]: [
    SpeciesTrait.BABY,
    SpeciesTrait.LEGENDARY,
    SpeciesTrait.MYTHICAL,
    SpeciesTrait.MEGA,
    SpeciesTrait.GMAX,
  ],
};

export function isTraitExcludedBySelection(
  trait: string,
  selectedTraits: string[],
): boolean {
  return (mutuallyExclusiveTraits[trait] ?? []).some((excludingTrait) =>
    selectedTraits.includes(excludingTrait),
  );
}

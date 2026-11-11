import { SpeciesTrait } from '@/shared/types/enums';

const mutuallyExclusiveTraits: Partial<Record<string, string[]>> = {
  [SpeciesTrait.BABY]: [
    SpeciesTrait.MEGA,
    SpeciesTrait.GMAX,
    SpeciesTrait.LEGENDARY,
    SpeciesTrait.MYTHICAL,
  ],
  [SpeciesTrait.LEGENDARY]: [SpeciesTrait.MYTHICAL, SpeciesTrait.BABY],
  [SpeciesTrait.MYTHICAL]: [SpeciesTrait.LEGENDARY, SpeciesTrait.BABY],
  [SpeciesTrait.MEGA]: [SpeciesTrait.GMAX, SpeciesTrait.BABY],
  [SpeciesTrait.GMAX]: [SpeciesTrait.MEGA, SpeciesTrait.BABY],
};

export function isTraitExcludedBySelection(
  trait: string,
  selectedTraits: string[],
): boolean {
  return (mutuallyExclusiveTraits[trait] ?? []).some((excludingTrait) =>
    selectedTraits.includes(excludingTrait),
  );
}

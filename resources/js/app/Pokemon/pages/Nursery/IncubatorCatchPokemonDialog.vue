<template>
  <Dialog
    v-if="incubator.pokemon"
    :open="isOpen"
    @close="isOpen = false"
  >
    <DialogContent data-test="incubator-catch-pokemon-dialog">
      <DialogHeader>
        <DialogTitle>
          Catch
          <PokemonDisplayName
            :name="incubator.pokemon.name"
            :gender="incubator.pokemon.gender"
          />
        </DialogTitle>
        <DialogDescription>
          Select the Poké Ball you want to throw at the hatched Pokémon, then
          click Catch to catch it.
        </DialogDescription>
      </DialogHeader>
      <div class="flex flex-col gap-6">
        <SpeciesSprite
          class="mx-auto"
          :url="incubator.pokemon.frontSprite"
          :name="incubator.pokemon.name"
        />
        <FieldSet>
          <FieldLegend variant="label">Poké Ball</FieldLegend>
          <RadioGroup
            v-model="form.pokeballId"
            class="max-h-80 scrollbar-thin grid-cols-2 gap-2 overflow-y-auto"
          >
            <Field
              v-for="pokeball in page.props.pokeballs"
              :key="pokeball.id"
              orientation="horizontal"
              class="cursor-pointer rounded-md border p-3 has-data-[state=checked]:border-primary"
              :data-test="`incubator-catch-pokemon-dialog__pokeball-${pokeball.id}-radio`"
              :data-invalid="!!form.errors.pokeballId"
              @click="form.pokeballId = pokeball.id"
            >
              <RadioGroupItem
                :id="`pokeball-${pokeball.id}`"
                :value="pokeball.id"
              />
              <FieldLabel
                class="flex cursor-pointer items-center gap-2 font-normal"
                :for="`pokeball-${pokeball.id}`"
              >
                <ItemSprite
                  v-if="pokeball.sprite"
                  :name="pokeball.name"
                  :url="pokeball.sprite"
                />
                <span>{{ pokeball.name }}</span>
                <span class="text-sm text-muted-foreground">
                  x{{ pokeball.quantity }}
                </span>
              </FieldLabel>
            </Field>
          </RadioGroup>
          <FieldError v-if="form.errors.pokeballId">
            {{ form.errors.pokeballId }}
          </FieldError>
        </FieldSet>
      </div>
      <DialogFooter>
        <DialogClose as-child>
          <Button
            type="button"
            variant="outline"
          >
            Cancel
          </Button>
        </DialogClose>
        <Button
          type="button"
          data-test="incubator-catch-pokemon-dialog__submit-button"
          :disabled="form.processing"
          @click="handleCatch"
        >
          Catch
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import ItemSprite from '@/shared/components/game/ItemSprite.vue';
import PokemonDisplayName from '@/shared/components/game/PokemonDisplayName.vue';
import SpeciesSprite from '@/shared/components/game/SpeciesSprite.vue';
import { Button } from '@/shared/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/shared/components/ui/dialog';
import {
  Field,
  FieldError,
  FieldLabel,
  FieldLegend,
  FieldSet,
} from '@/shared/components/ui/field';
import { RadioGroup, RadioGroupItem } from '@/shared/components/ui/radio-group';
import { route } from '@/shared/types/helpers/route';
import type {
  Incubator,
  NurseryPageProps,
} from '../../types/page-props/NurseryPageProps';

const props = defineProps<{
  incubator: Incubator;
}>();

const isOpen = defineModel<boolean>('isOpen');

const page = usePage<NurseryPageProps>();

const form = useForm({
  pokeballId: '',
});

function handleCatch() {
  form.post(
    route('pokemon.web.incubators.catch-pokemon', {
      incubatorIndex: props.incubator.index,
    }),
    {
      onSuccess: () => {
        isOpen.value = false;
      },
    },
  );
}
</script>

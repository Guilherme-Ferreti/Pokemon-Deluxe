<template>
  <div class="grid min-h-screen lg:grid-cols-2">
    <div class="flex flex-col gap-4 p-6 md:p-10">
      <div class="flex justify-center gap-2 md:justify-start">
        <Link
          :href="route('web.public.home')"
          class="flex items-center gap-2 font-medium"
        >
          <AppLogo class="w-35" />
        </Link>
      </div>
      <main class="flex flex-1 items-center justify-center">
        <div class="w-full max-w-xs">
          <slot />
        </div>
      </main>
    </div>
    <div class="relative hidden overflow-hidden lg:block">
      <div
        class="absolute inset-0 -z-10 grid grid-cols-7 place-items-center bg-muted p-6"
      >
        <img
          v-for="image in page.props.auth.layoutSprites"
          :key="image"
          :src="image"
          alt="Pokemon Deluxe"
          loading="lazy"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import { route } from '@/types/helpers/route';

const page = usePage();
</script>

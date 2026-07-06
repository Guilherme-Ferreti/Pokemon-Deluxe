<template>
  <Form
    v-slot="{ errors }"
    :action="route('web.auth.login.store')"
    method="post"
    disable-while-processing
    :reset-on-error="['password']"
    class="flex flex-col gap-6"
  >
    <FieldGroup>
      <AppAuthFormHeader
        title="Login to your account"
        description="Enter your email below to login to your account"
      />
      <AppInput
        label="Email"
        type="email"
        name="email"
        placeholder="test@example.com"
        required
        :error-message="errors.email"
      />
      <AppInput
        label="Password"
        type="password"
        name="password"
        required
        :error-message="errors.password"
      />
      <div class="flex justify-between gap-2">
        <AppCheckbox
          label="Remember me"
          id="remember"
          name="remember"
          class="w-fit"
        />
        <FieldDescription>
          <a href="#">Forgot password?</a>
        </FieldDescription>
      </div>
      <Button type="submit">Login</Button>
      <FieldDescription class="text-center">
        Don't have an account?
        <a href="#">Sign up</a>
      </FieldDescription>
    </FieldGroup>
  </Form>
</template>

<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import AppAuthFormHeader from '@/components/AppAuthFormHeader.vue';
import AppCheckbox from '@/components/AppCheckbox.vue';
import AppInput from '@/components/AppInput.vue';
import { Button } from '@/components/ui/button';
import { FieldDescription, FieldGroup } from '@/components/ui/field';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { route } from '@/types/helpers/route';

defineOptions({
  layout: AuthLayout,
});
</script>

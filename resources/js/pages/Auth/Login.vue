<template>
  <Head title="Login" />
  <Form
    v-slot="{ errors }"
    method="post"
    class="flex flex-col gap-6"
    :action="route('web.auth.login.store')"
    disable-while-processing
    :reset-on-error="['password']"
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
        value="test@example.com"
        :error-message="errors.email"
      />
      <AppInput
        label="Password"
        type="password"
        name="password"
        required
        value="password"
        :error-message="errors.password"
      />
      <div class="flex justify-between gap-2">
        <AppCheckbox
          id="remember"
          label="Remember me"
          name="remember"
          class="w-fit"
        />
        <FieldDescription>
          <a href="#">Forgot password?</a>
        </FieldDescription>
      </div>
      <Button
        type="submit"
        data-test="login-button"
      >
        Login
      </Button>
      <FieldDescription class="text-center">
        Don't have an account?
        <Link :href="route('web.auth.register.create')">Sign up</Link>
      </FieldDescription>
    </FieldGroup>
  </Form>
</template>

<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
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

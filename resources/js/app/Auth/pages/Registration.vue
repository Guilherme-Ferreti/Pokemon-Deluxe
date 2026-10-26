<template>
  <Head title="Registration" />
  <Form
    v-slot="{ errors, validate }"
    method="post"
    class="flex flex-col gap-6"
    :action="route('auth.web.registration.store')"
    disable-while-processing
    :reset-on-error="['password', 'passwordConfirmation']"
  >
    <FieldGroup>
      <AuthFormHeader
        title="Create an account"
        description="Enter your details below to create your account"
      />
      <AppInput
        label="Name"
        type="text"
        name="name"
        placeholder="John Doe"
        required
        :error-message="errors.name"
      />
      <AppInput
        label="Email"
        type="email"
        name="email"
        placeholder="test@example.com"
        required
        :error-message="errors.email"
        @blur="validate('email')"
      />
      <AppInput
        label="Password"
        type="password"
        name="password"
        required
        :error-message="errors.password"
      />
      <AppInput
        label="Confirm Password"
        type="password"
        name="passwordConfirmation"
        required
        :error-message="errors.passwordConfirmation"
      />
      <Button
        type="submit"
        data-test="auth-form__register-button"
      >
        Create account
      </Button>
      <FieldDescription class="text-center">
        Already have an account?
        <Link :href="route('auth.web.login.create')">Log in</Link>
      </FieldDescription>
    </FieldGroup>
  </Form>
</template>

<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AuthFormHeader from '@/app/Auth/components/AuthFormHeader.vue';
import AuthLayout from '@/app/Auth/layouts/AuthLayout.vue';
import AppInput from '@/shared/components/AppInput.vue';
import { Button } from '@/shared/components/ui/button';
import { FieldDescription, FieldGroup } from '@/shared/components/ui/field';
import { route } from '@/shared/types/helpers/route';

defineOptions({
  layout: AuthLayout,
});
</script>

import { Form, Head } from '@inertiajs/react';
// import InputError from '@/components/input-error';
// import PasswordInput from '@/components/password-input';
// import { Button } from '@/components/ui/button';
// import { Checkbox } from '@/components/ui/checkbox';
// import { Input } from '@/components/ui/input';
// import { Label } from '@/components/ui/label';
// import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { Form as FormAnt, Input } from 'antd';

type Props = {
  status?: string;
  canResetPassword: boolean;
};

export default function Login({ status, canResetPassword }: Props) {
  return (
    <>
      <Head title="Вход" />

      <Form
        {...store.form()}
        resetOnSuccess={['password']}
        className="flex flex-col gap-6"
      >
        {({ processing, errors }) => (
          <>
            <FormAnt.Item label="Имя пользователя" layout="vertical">
              <Input
              />
            </FormAnt.Item>
            {/* <div className="grid gap-6">
              <div className="grid gap-2">
                <Label htmlFor="username">Имя пользователя</Label>
                <Input
                  id="username"
                  type="text"
                  name="username"
                  required
                  autoFocus
                  tabIndex={1}
                  autoComplete="username"
                  placeholder="Имя пользователя"
                />
                <InputError message={errors.username} />
              </div>

              <div className="grid gap-2">
                <Label htmlFor="password">Пароль</Label>
                <PasswordInput
                  id="password"
                  name="password"
                  required
                  tabIndex={2}
                  autoComplete="current-password"
                  placeholder="Пароль"
                />
                <InputError message={errors.password} />
              </div>

              <div className="flex items-center space-x-3">
                <Checkbox
                  id="remember"
                  name="remember"
                  tabIndex={3}
                />
                <Label htmlFor="remember">Запомнить меня</Label>
              </div>

              <Button
                type="submit"
                className="mt-4 w-full"
                tabIndex={4}
                disabled={processing}
                data-test="login-button"
              >
                {processing && <Spinner />}
                Вход
              </Button>
            </div> */}
          </>
        )}
      </Form>

      {status && (
        <div className="mb-4 text-center text-sm font-medium text-green-600">
          {status}
        </div>
      )}
    </>
  );
}

Login.layout = {
  title: 'Войдите в свой аккаунт',
  description: 'Введите свой адрес электронной почты и пароль ниже, чтобы войти в систему',
};

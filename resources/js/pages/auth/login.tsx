import { Head, useForm } from '@inertiajs/react';
import { Button, Checkbox, Form, Input, message, Typography } from 'antd';
import { UserOutlined, LockOutlined, TeamOutlined } from '@ant-design/icons';
import { store } from '@/routes/login';
import type { FormProps } from 'antd';

const { Title, Paragraph } = Typography;

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
};

type Props = {
    status?: string;
    canResetPassword: boolean;
};

export default function Login({ status, canResetPassword }: Props) {
    const { data, setData, post, processing, errors } = useForm<LoginForm>({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit: FormProps<LoginForm>['onFinish'] = () => {
        post(store().url, {
            preserveScroll: true,
            onSuccess: () => {
                message.success('Вы успешно вошли в систему');
            },
            onError: () => {},
        });
    };

    return (
        <>
            <Head title="Вход" />

            <div style={{
                position: 'fixed',
                top: 0,
                left: 0,
                right: 0,
                bottom: 0,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                background: 'linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%)',
                fontFamily: 'system-ui, -apple-system, sans-serif',
                padding: '16px',
                boxSizing: 'border-box',
                zIndex: 9999,
            }}>
                <div style={{
                    width: '100%',
                    maxWidth: '400px',
                    background: '#ffffff',
                    borderRadius: '16px',
                    boxShadow: '0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1)',
                    padding: '32px',
                    boxSizing: 'border-box',
                }}>
                    {/* Logo & Header */}
                    <div style={{ textAlign: 'center', marginBottom: '32px' }}>
                        <div style={{
                            margin: '0 auto 16px',
                            width: '64px',
                            height: '64px',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            borderRadius: '50%',
                            background: '#eff6ff',
                        }}>
                            <TeamOutlined style={{ fontSize: '28px', color: '#2563eb' }} />
                        </div>
                        <Title level={2} style={{
                            marginBottom: '4px',
                            fontSize: '24px',
                            fontWeight: 600,
                            color: '#111827',
                        }}>
                            Вход в систему
                        </Title>
                        <Paragraph type="secondary" style={{ marginBottom: 0 }}>
                            Введите свои данные для входа
                        </Paragraph>
                    </div>

                    {/* Status message */}
                    {status && (
                        <div style={{
                            marginBottom: '16px',
                            borderRadius: '8px',
                            background: '#f0fdf4',
                            padding: '12px',
                            textAlign: 'center',
                            fontSize: '14px',
                            color: '#15803d',
                        }}>
                            {status}
                        </div>
                    )}

                    {/* Form */}
                    <Form
                        onFinish={handleSubmit}
                        layout="vertical"
                        size="large"
                        requiredMark={false}
                    >
                        <Form.Item
                            label="Учетная запись"
                            validateStatus={errors.email ? 'error' : ''}
                            help={errors.email}
                        >
                            <Input
                                prefix={<UserOutlined />}
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="samaccountname"
                                autoComplete="username"
                            />
                        </Form.Item>

                        <Form.Item
                            label="Пароль"
                            validateStatus={errors.password ? 'error' : ''}
                            help={errors.password}
                        >
                            <Input.Password
                                prefix={<LockOutlined />}
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Введите пароль"
                                autoComplete="current-password"
                            />
                        </Form.Item>

                        <Form.Item>
                            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                                <Form.Item valuePropName="checked" noStyle>
                                    <Checkbox
                                        checked={data.remember}
                                        onChange={(e) => setData('remember', e.target.checked)}
                                    >
                                        Запомнить меня
                                    </Checkbox>
                                </Form.Item>

                                {canResetPassword && (
                                    <a href="/password/request" style={{ fontSize: '14px', color: '#2563eb', textDecoration: 'none' }}>
                                        Забыли пароль?
                                    </a>
                                )}
                            </div>
                        </Form.Item>

                        <Form.Item style={{ marginBottom: 0 }}>
                            <Button
                                type="primary"
                                htmlType="submit"
                                block
                                loading={processing}
                                size="large"
                                style={{ height: '48px', fontSize: '16px' }}
                            >
                                Войти
                            </Button>
                        </Form.Item>
                    </Form>
                </div>
            </div>
        </>
    );
}

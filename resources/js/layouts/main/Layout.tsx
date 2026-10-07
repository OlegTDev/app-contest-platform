import { Button, Layout, Menu, theme } from 'antd';
import {
  MenuFoldOutlined,
  MenuUnfoldOutlined,
  HomeOutlined,
  TeamOutlined,
  TrophyOutlined,
} from '@ant-design/icons';
import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';

const { Header, Sider, Content } = Layout;

interface LayoutProps {
  children: React.ReactNode;
}

export default function MainLayout({ children }: LayoutProps): React.JSX.Element {
  const [collapsed, setCollapsed] = useState(false);
  const { url } = usePage();
  const { token: { colorBgContainer, borderRadiusLG } } = theme.useToken();

  const menuItems = [
    {
      key: '/',
      icon: <HomeOutlined />,
      label: <Link href="/">Главная</Link>,
    },
    {
      key: '/contest',
      icon: <TeamOutlined />,
      label: <Link href="/contest">Конкурсы</Link>,
    },
    {
      key: '/contest/create',
      icon: <TrophyOutlined />,
      label: <Link href="/contest/create">Создать конкурс</Link>,
    },
  ];

  return (
    <Layout style={{ height: '100vh' }}>
      <Sider
        trigger={null}
        collapsible
        collapsed={collapsed}
        style={{
          overflow: 'auto',
          height: '100vh',
          position: 'fixed',
          left: 0,
          top: 0,
          bottom: 0,
        }}
      >
        <div style={{
          height: 32,
          margin: 16,
          background: 'rgba(255,255,255,0.2)',
          borderRadius: 6,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          color: '#fff',
          fontWeight: 'bold',
          fontSize: 16,
        }}>
          {collapsed ? '🏆' : 'Конкурсы'}
        </div>
        <Menu
          theme="dark"
          mode="inline"
          defaultSelectedKeys={[url.startsWith('/contest') ? '/contest' : '/']}
          items={menuItems}
        />
      </Sider>
      <Layout
        style={{
          marginLeft: collapsed ? 80 : 200,
          transition: 'margin-left 0.2s',
        }}
      >
        <Header style={{
          padding: '0 24px',
          background: colorBgContainer,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
        }}>
          <Button
            type="text"
            icon={collapsed ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />}
            onClick={() => setCollapsed(!collapsed)}
            style={{
              fontSize: '16px',
              width: 64,
              height: 64,
            }}
          />
          <div style={{ color: '#666', fontSize: 14 }}>
            {url === '/' ? 'Главная' :
             url === '/contest' ? 'Конкурсы' :
             url === '/contest/create' ? 'Создать конкурс' :
             url.replace(/^\//, '').replace(/\//g, ' / ')}
          </div>
        </Header>
        <Content
          style={{
            margin: '24px 16px',
            padding: 24,
            minHeight: 280,
            background: colorBgContainer,
            borderRadius: borderRadiusLG,
          }}
        >
          {children}
        </Content>
      </Layout>
    </Layout>
  );
}

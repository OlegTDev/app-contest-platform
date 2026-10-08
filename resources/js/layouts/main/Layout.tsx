import { Button, Layout, Menu, theme } from 'antd';
import {
  MenuFoldOutlined,
  MenuUnfoldOutlined,
  HomeOutlined,
  TeamOutlined,
  TrophyOutlined,
} from '@ant-design/icons';
import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';

const { Header, Sider, Content } = Layout;

const SIDEBAR_STORAGE_KEY = 'sidebar_collapsed';

function getInitialCollapsed(): boolean {
  if (typeof window === 'undefined') return false;
  return window.localStorage.getItem(SIDEBAR_STORAGE_KEY) === 'true';
}

interface LayoutProps {
  children: React.ReactNode;
}

export default function MainLayout({ children }: LayoutProps): React.JSX.Element {
  const [collapsed, setCollapsed] = useState<boolean>(getInitialCollapsed);

  // Persist the collapsed state so the sidebar keeps its state across reloads.
  useEffect(() => {
    window.localStorage.setItem(SIDEBAR_STORAGE_KEY, String(collapsed));
  }, [collapsed]);
  const { url } = usePage();
  const { token: { colorBgContainer, borderRadiusLG } } = theme.useToken();

  const menuItems = [
    {
      key: '/',
      icon: <HomeOutlined />,
      label: <Link href="/">Главная</Link>,
    },
    {
      key: '/admin/contests',
      icon: <TrophyOutlined />,
      label: <Link href="/admin/contests">Управление</Link>,
    },
  ];

  return (
    <Layout style={{ minHeight: '100vh' }}>
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
          defaultSelectedKeys={[url.startsWith('/admin/contests') ? '/admin/contests' : '/']}
          items={menuItems}
        />
      </Sider>
      <Layout
        style={{
          marginLeft: collapsed ? 80 : 200,
          transition: 'margin-left 0.2s',
          display: 'flex',
          flexDirection: 'column',
          minHeight: '100vh',
        }}
      >
        <Header style={{
          padding: '0 24px',
          background: colorBgContainer,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          flexShrink: 0,
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
            {url === '/' ? 'Конкурсы' :
             url === '/admin/contests' ? 'Управление' :
             url === '/admin/contests/create' ? 'Создать конкурс' :
             url.replace(/^\//, '').replace(/\//g, ' / ')}
          </div>
        </Header>
        <Content
          style={{
            flex: 1,
            margin: '24px 16px',
            padding: 24,
            background: colorBgContainer,
            borderRadius: borderRadiusLG,
            minHeight: 0,
          }}
        >
          {children}
        </Content>
      </Layout>
    </Layout>
  );
}

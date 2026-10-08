import { Head, Link, router } from "@inertiajs/react";
import { Button, Card, Input, Select, Space, Table, Tag, Typography } from "antd";
import { PlusOutlined, SearchOutlined } from "@ant-design/icons";
import dayjs from "dayjs";
import { destroy, show, edit, status } from "@/routes/admin/contests";

const { Title, Text } = Typography;

type ContestItem = {
  id: number;
  title: string;
  type: string;
  status: string;
  description: string | null;
  start_at: string | null;
  end_at: string | null;
  is_active: boolean;
  quiz_entry_count: number;
  entry_count: number;
  author: {
    id: number;
    name: string;
  };
  is_owner: boolean;
  created_at: string;
};

type ContestIndexProps = {
  contests: {
    data: ContestItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    next_page_url: string | null;
    path: string;
    per_page: number;
    to: number;
    total: number;
    current_page: number;
  };
  filters: {
    status: string;
    type: string;
    search: string;
  };
};

const statusColors: Record<string, string> = {
  draft: "default",
  published: "green",
  paused: "orange",
  closed: "red",
};

const statusLabels: Record<string, string> = {
  draft: "Черновик",
  published: "Активен",
  paused: "На паузе",
  closed: "Закрыт",
};

export default function ContestIndex({ contests, filters }: ContestIndexProps): React.JSX.Element {
  const handleDelete = (id: number) => {
    if (confirm("Вы уверены, что хотите удалить этот конкурс?")) {
      router.delete(destroy({ contest: id }).url, {
        preserveScroll: true,
      });
    }
  };

  const handleStatusChange = (id: number, newStatus: string) => {
    router.patch(status({ contest: id }).url, { status: newStatus }, {
      preserveScroll: true,
    });
  };

  const handleFilterChange = (key: string, value: string | null) => {
    const params = new URLSearchParams(window.location.search);
    if (value) {
      params.set(key, value);
    } else {
      params.delete(key);
    }
    router.get('/admin/contests' + (params.toString() ? '?' + params.toString() : ''), {}, { preserveState: true });
  };

  const getTypeLabel = (type: string) => {
    return type === "quiz" ? "Викторина" : "Голосование";
  };

  const columns = [
    {
      title: "Название",
      dataIndex: "title",
      key: "title",
      width: 250,
      render: (title: string, record: ContestItem) => (
        <Link href={show({ contest: record.id }).url}>
          <Text strong>{title}</Text>
        </Link>
      ),
    },
    {
      title: "Автор",
      dataIndex: ["author", "name"],
      key: "author",
      width: 180,
      render: (name: string) => name || "—",
    },
    {
      title: "Тип",
      dataIndex: "type",
      key: "type",
      width: 120,
      render: (type: string) => (
        <Tag color={type === "quiz" ? "blue" : "green"}>
          {getTypeLabel(type)}
        </Tag>
      ),
    },
    {
      title: "Статус",
      dataIndex: "status",
      key: "status",
      width: 120,
      render: (status: string, record: ContestItem) => (
        <Tag color={statusColors[status]}>{statusLabels[status]}</Tag>
      ),
    },
    {
      title: "Вопросов/Работ",
      key: "entry_count",
      width: 100,
      render: (_: unknown, record: ContestItem) => (
        <span>{record.type === "quiz" ? record.quiz_entry_count : record.entry_count}</span>
      ),
    },
    {
      title: "Создан",
      dataIndex: "created_at",
      key: "created_at",
      width: 150,
      render: (date: string) => dayjs(date).format("DD.MM.YYYY"),
    },
    {
      title: "Действия",
      key: "actions",
      width: 220,
      render: (_: unknown, record: ContestItem) => (
        <Space size="small">
          <Link href={show({ contest: record.id }).url}>
            <Button size="small">Открыть</Button>
          </Link>
          {record.is_owner && (
            <>
              <Link href={edit({ contest: record.id }).url}>
                <Button size="small" type="primary">
                  Изменить
                </Button>
              </Link>
              {record.status === "draft" && (
                <Button
                  size="small"
                  onClick={() => handleStatusChange(record.id, "published")}
                >
                  Опубликовать
                </Button>
              )}
              {record.status === "published" && (
                <Button
                  size="small"
                  danger
                  onClick={() => handleStatusChange(record.id, "paused")}
                >
                  Пауза
                </Button>
              )}
              <Button
                size="small"
                danger
                onClick={() => handleDelete(record.id)}
              >
                Удалить
              </Button>
            </>
          )}
        </Space>
      ),
    },
  ];

  return (
    <>
      <Head title="Конкурсы" />

      <div style={{ maxWidth: "100%", margin: "0 auto", padding: "24px 0" }}>
        <Space direction="vertical" size="large" style={{ width: "100%" }}>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
            <Title level={2} style={{ margin: 0 }}>
              Конкурсы
            </Title>
            <Link href="/admin/contests/create">
              <Button type="primary" icon={<PlusOutlined />}>
                Создать конкурс
              </Button>
            </Link>
          </div>

          {/* Filters */}
          <Card style={{ marginBottom: 0 }}>
            <Space size="middle">
              <Input
                placeholder="Поиск по названию..."
                prefix={<SearchOutlined />}
                style={{ width: 250 }}
                defaultValue={filters.search}
                onPressEnter={(e) => handleFilterChange("search", e.currentTarget.value)}
              />
              <Select
                placeholder="Статус"
                style={{ width: 150 }}
                allowClear
                defaultValue={filters.status || undefined}
                onChange={(value) => handleFilterChange("status", value)}
                options={[
                  { label: "Черновик", value: "draft" },
                  { label: "Активен", value: "published" },
                  { label: "На паузе", value: "paused" },
                  { label: "Закрыт", value: "closed" },
                ]}
              />
              <Select
                placeholder="Тип"
                style={{ width: 150 }}
                allowClear
                defaultValue={filters.type || undefined}
                onChange={(value) => handleFilterChange("type", value)}
                options={[
                  { label: "Викторина", value: "quiz" },
                  { label: "Голосование", value: "voting" },
                ]}
              />
            </Space>
          </Card>

          <Card>
            <Table
              columns={columns}
              dataSource={contests.data}
              rowKey="id"
              pagination={{
                total: contests.total,
                pageSize: contests.per_page,
                current: contests.current_page,
                showSizeChanger: true,
                showTotal: (total) => `Всего: ${total}`,
                onChange: (page, pageSize) => {
                  const params = new URLSearchParams(window.location.search);
                  params.set('page', page.toString());
                  if (pageSize !== contests.per_page) {
                    params.set('per_page', pageSize.toString());
                  } else {
                    params.delete('per_page');
                  }
                  router.get('/admin/contests' + (params.toString() ? '?' + params.toString() : ''), {}, { preserveState: true });
                },
              }}
              scroll={{ x: 1400 }}
            />
          </Card>
        </Space>
      </div>
    </>
  );
}

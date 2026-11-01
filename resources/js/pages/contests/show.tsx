import { Head, Link, router } from "@inertiajs/react";
import { Button, Card, Descriptions, Popconfirm, Space, Table, Tag, Typography } from "antd";
import { PlusOutlined, EditOutlined, DeleteOutlined, PaperClipOutlined } from "@ant-design/icons";
import dayjs from "dayjs";
import { destroy, edit, status } from "@/routes/admin/contests";

const { Title, Text, Paragraph } = Typography;

type MediaItem = {
  id: number;
  file_name: string;
  file_url: string;
  thumbnail_url: string | null;
  file_type: string;
  file_extension: string;
  file_size: number | null;
  is_main: boolean;
  created_at: string | null;
};

type QuizActivity = {
  id: number;
  type: "quiz";
  title: string;
  description: string | null;
  question: string | null;
  options: { id: string; text: string; is_correct: boolean }[] | null;
  quiz_type: string | null;
  is_scheduled: boolean;
  is_visible: boolean;
  media: MediaItem[];
  created_at: string;
};

type VotingActivity = {
  id: number;
  type: "voting";
  title: string;
  description: string | null;
  author_name: string | null;
  author_department: string | null;
  votes_count: number;
  media: MediaItem[];
  created_at: string;
};

type ActivityItem = QuizActivity | VotingActivity;

type ContestItem = {
  id: number;
  title: string;
  type: string;
  status: string;
  description: string | null;
  start_at: string | null;
  end_at: string | null;
  is_active: boolean;
  is_time_active?: boolean;
  is_archived?: boolean;
  can_manage?: boolean;
  activities?: ActivityItem[] | null;
  leaderboard?: unknown[];
  created_at: string;
  slug?: string | null;
};

type ContestShowProps = {
  contest: ContestItem;
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

export default function ContestShow({ contest }: ContestShowProps): React.JSX.Element {
  const activities = contest.activities ?? [];
  const quizActivities = activities.filter((a): a is QuizActivity => a.type === "quiz");
  const votingActivities = activities.filter((a): a is VotingActivity => a.type === "voting");

  const handleDelete = () => {
    if (confirm("Вы уверены, что хотите удалить этот конкурс?")) {
      router.delete(destroy({ contest: contest.id }).url, {
        preserveScroll: true,
      });
    }
  };

  const handleStatusChange = (newStatus: string) => {
    router.patch(status({ contest: contest.id }).url, { status: newStatus }, {
      preserveScroll: true,
    });
  };

  const handleDeleteEntry = (id: number, title: string) => {
    if (confirm(`Вы уверены, что хотите удалить "${title}"?`)) {
      router.delete(`/admin/contests/${contest.id}/entries/${id}`, {
        preserveScroll: true,
      });
    }
  };

  const getTypeLabel = (type: string) => {
    return type === "quiz" ? "Викторина" : "Голосование";
  };

  const renderActions = (record: ActivityItem) => (
    <Space size="small">
      <Link href={`/admin/contests/${contest.id}/entries/${record.id}/media`}>
        <Button size="small" icon={<PaperClipOutlined />}>
          Медиа
        </Button>
      </Link>
      <Link href={`/admin/contests/${contest.id}/entries/${record.id}/edit`}>
        <Button size="small" icon={<EditOutlined />}>
          Изменить
        </Button>
      </Link>
      <Popconfirm
        title="Удалить"
        description="Это действие нельзя отменить"
        onConfirm={() => handleDeleteEntry(record.id, record.title)}
        okText="Удалить"
        cancelText="Отмена"
        okButtonProps={{ danger: true }}
      >
        <Button size="small" danger icon={<DeleteOutlined />} />
      </Popconfirm>
    </Space>
  );

  const quizColumns = [
    {
      title: "Название",
      dataIndex: "title",
      key: "title",
      render: (title: string, record: QuizActivity) => (
        <Space>
          <Text strong>{title}</Text>
          {record.is_scheduled && <Tag color="blue">Таймер</Tag>}
          {!record.is_visible && <Tag color="orange">Скоро</Tag>}
          {(record.media?.length ?? 0) > 0 && (
            <Tag color="geekblue">
              <PaperClipOutlined /> {record.media.length}
            </Tag>
          )}
        </Space>
      ),
    },
    {
      title: "Вопрос",
      dataIndex: "question",
      key: "question",
      ellipsis: true,
      render: (question: string | null) => question || "—",
    },
    {
      title: "Описание",
      dataIndex: "description",
      key: "description",
      ellipsis: true,
      render: (description: string | null) => description || "—",
    },
    {
      title: "Вариантов",
      dataIndex: "options",
      key: "options",
      width: 120,
      render: (options: QuizActivity["options"]) => options?.length ?? 0,
    },
    {
      title: "Создана",
      dataIndex: "created_at",
      key: "created_at",
      width: 160,
      render: (date: string) => dayjs(date).format("DD.MM.YYYY HH:mm"),
    },
    {
      title: "Действия",
      key: "actions",
      width: 280,
      render: (_: unknown, record: QuizActivity) => renderActions(record),
    },
  ];

  const votingColumns = [
    {
      title: "Название",
      dataIndex: "title",
      key: "title",
      render: (title: string, record: VotingActivity) => (
        <Space>
          <Text strong>{title}</Text>
          {(record.media?.length ?? 0) > 0 && (
            <Tag color="geekblue">
              <PaperClipOutlined /> {record.media.length}
            </Tag>
          )}
        </Space>
      ),
    },
    {
      title: "Автор",
      dataIndex: "author_name",
      key: "author_name",
      width: 200,
      render: (author: string | null) => author || "—",
    },
    {
      title: "Описание",
      dataIndex: "description",
      key: "description",
      ellipsis: true,
      render: (description: string | null) => description || "—",
    },
    {
      title: "Голосов",
      dataIndex: "votes_count",
      key: "votes_count",
      width: 100,
      render: (count: number) => count,
    },
    {
      title: "Создана",
      dataIndex: "created_at",
      key: "created_at",
      width: 160,
      render: (date: string) => dayjs(date).format("DD.MM.YYYY HH:mm"),
    },
    {
      title: "Действия",
      key: "actions",
      width: 280,
      render: (_: unknown, record: VotingActivity) => renderActions(record),
    },
  ];

  return (
    <>
      <Head title={contest.title} />

      <div style={{ maxWidth: "100%", margin: "0 auto", padding: "24px 0" }}>
        <Space direction="vertical" size="large" style={{ width: "100%" }}>
          {/* Header */}
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
            <Space>
              <Link href="/admin/contests">
                <Button type="text">← Назад</Button>
              </Link>
              <Space>
                <Title level={2} style={{ margin: 0 }}>{contest.title}</Title>
                <Tag color={statusColors[contest.status] || "default"}>
                  {statusLabels[contest.status] || contest.status}
                </Tag>
                {contest.is_active && (
                  <Tag color="green">● Активен</Tag>
                )}
              </Space>
              <Paragraph type="secondary" style={{ marginBottom: 0 }}>
                Тип: {getTypeLabel(contest.type)} | Создан: {dayjs(contest.created_at).format("DD.MM.YYYY HH:mm")}
              </Paragraph>
            </Space>
            {contest.can_manage && (
              <Space>
                {contest.type !== "quiz" && (
                  <Link href={`/admin/contests/${contest.id}/media`}>
                    <Button icon={<PaperClipOutlined />}>Медиа</Button>
                  </Link>
                )}
                <Link href={edit({ contest: contest.id }).url}>
                  <Button icon={<EditOutlined />}>Редактировать</Button>
                </Link>
                <Button danger icon={<DeleteOutlined />} onClick={handleDelete}>
                  Удалить
                </Button>
              </Space>
            )}
          </div>

          {/* Description */}
          {contest.description && (
            <Card>
              <Paragraph>{contest.description}</Paragraph>
            </Card>
          )}

          {/* Period */}
          {(contest.start_at || contest.end_at) && (
            <Card title="Период проведения">
              <Descriptions bordered column={1} size="small">
                <Descriptions.Item label="Начало">
                  {contest.start_at ? dayjs(contest.start_at).format("DD.MM.YYYY HH:mm") : "Не указано"}
                </Descriptions.Item>
                <Descriptions.Item label="Окончание">
                  {contest.end_at ? dayjs(contest.end_at).format("DD.MM.YYYY HH:mm") : "Не указано"}
                </Descriptions.Item>
              </Descriptions>
            </Card>
          )}

          {/* Quiz Activities Section (for quiz contests) */}
          {contest.type === "quiz" && (
            <Card
              title={
                <Space>
                  <span>Вопросы</span>
                  <Tag>{quizActivities.length}</Tag>
                </Space>
              }
              extra={
                contest.can_manage ? (
                  <Link href={`/admin/contests/${contest.id}/entries/create`}>
                    <Button type="primary" icon={<PlusOutlined />}>
                      Добавить вопрос
                    </Button>
                  </Link>
                ) : null
              }
            >
              <Table
                columns={quizColumns}
                dataSource={quizActivities}
                rowKey="id"
                pagination={false}
                locale={{ emptyText: "Вопросов пока нет" }}
              />
            </Card>
          )}

          {/* Voting Activities Section (for voting contests) */}
          {contest.type === "voting" && (
            <Card
              title={
                <Space>
                  <span>Работы</span>
                  <Tag>{votingActivities.length}</Tag>
                </Space>
              }
              extra={
                contest.can_manage ? (
                  <Link href={`/admin/contests/${contest.id}/entries/create`}>
                    <Button type="primary" icon={<PlusOutlined />}>
                      Добавить работу
                    </Button>
                  </Link>
                ) : null
              }
            >
              <Table
                columns={votingColumns}
                dataSource={votingActivities}
                rowKey="id"
                pagination={false}
                locale={{ emptyText: "Работ пока нет" }}
              />
            </Card>
          )}

          {/* Back button */}
          <Link href="/admin/contests">
            <Button>← Назад к конкурсам</Button>
          </Link>
        </Space>
      </div>
    </>
  );
}

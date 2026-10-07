import { Head, Link, router } from "@inertiajs/react";
import { Button, Card, List, Tag, Typography, Space, Empty, Descriptions } from "antd";
import { PlusOutlined, EditOutlined, DeleteOutlined, PlayCircleOutlined } from "@ant-design/icons";
import dayjs from "dayjs";
import { destroy } from "@/routes/contest";

const { Title, Text, Paragraph } = Typography;

type QuizEntryItem = {
  id: number;
  title: string;
  description: string | null;
  is_scheduled: boolean;
  created_at: string;
};

type EntryItem = {
  id: number;
  title: string;
  description: string | null;
  author_name: string | null;
  author_department: string | null;
  votes_count: number;
  created_at: string;
};

type ContestItem = {
  id: number;
  title: string;
  type: string;
  status: string;
  description: string | null;
  start_at: string | null;
  end_at: string | null;
  is_active: boolean;
  is_owner: boolean;
  quizEntries: QuizEntryItem[];
  entries: EntryItem[];
  created_at: string;
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
  const handleDelete = () => {
    if (confirm("Вы уверены, что хотите удалить этот конкурс?")) {
      router.delete(destroy({ contest: contest.id }).url, {
        preserveScroll: true,
      });
    }
  };

  const handleStatusChange = (newStatus: string) => {
    router.patch(`/contest/${contest.id}/status`, { status: newStatus }, {
      preserveScroll: true,
    });
  };

  const getTypeLabel = (type: string) => {
    return type === "quiz" ? "Викторина" : "Голосование";
  };

  return (
    <>
      <Head title={contest.title} />

      <div style={{ maxWidth: 1000, margin: "0 auto", padding: "24px 0" }}>
        <Space direction="vertical" size="large" style={{ width: "100%" }}>
          {/* Header */}
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start" }}>
            <div>
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
            </div>
            {contest.is_owner && (
              <Space>
                <Link href={`/contest/${contest.id}/edit`}>
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

          {/* Quiz Entries Section (for quiz contests) */}
          {contest.type === "quiz" && (
            <Card
              title={
                <Space>
                  <span>Вопросы</span>
                  <Tag>{contest.quizEntries.length}</Tag>
                </Space>
              }
              extra={
                contest.is_owner ? (
                  <Link href={`/contest/${contest.id}/entries/create`}>
                    <Button type="primary" icon={<PlusOutlined />}>
                      Добавить вопрос
                    </Button>
                  </Link>
                ) : contest.is_active ? (
                  <Link href={`/contest/${contest.id}/entries/public`}>
                    <Button type="primary" icon={<PlayCircleOutlined />}>
                      Пройти
                    </Button>
                  </Link>
                ) : null
              }
            >
              {contest.quizEntries.length === 0 ? (
                <Empty description="Вопросов пока нет" />
              ) : (
                <List
                  dataSource={contest.quizEntries}
                  renderItem={(entry) => (
                    <List.Item>
                      <Space style={{ width: "100%", justifyContent: "space-between" }}>
                        <Space>
                          <Text strong>{entry.title}</Text>
                          {entry.is_scheduled && (
                            <Tag color="blue">Таймер</Tag>
                          )}
                        </Space>
                        {!contest.is_owner && contest.is_active && (
                          <Text type="secondary" style={{ fontSize: 12 }}>
                            {dayjs(entry.created_at).format("DD.MM.YYYY")}
                          </Text>
                        )}
                      </Space>
                    </List.Item>
                  )}
                />
              )}
            </Card>
          )}

          {/* Entries Section (for voting contests) */}
          {contest.type === "voting" && (
            <Card
              title={
                <Space>
                  <span>Работы</span>
                  <Tag>{contest.entries.length}</Tag>
                </Space>
              }
              extra={
                contest.is_owner ? (
                  <Link href={`/contest/${contest.id}/entries/create`}>
                    <Button type="primary" icon={<PlusOutlined />}>
                      Добавить работу
                    </Button>
                  </Link>
                ) : contest.is_active ? (
                  <Link href={`/contest/${contest.id}/entries/public`}>
                    <Button type="primary" icon={<PlayCircleOutlined />}>
                      Голосовать
                    </Button>
                  </Link>
                ) : null
              }
            >
              {contest.entries.length === 0 ? (
                <Empty description="Работ пока нет" />
              ) : (
                <List
                  dataSource={contest.entries}
                  renderItem={(entry) => (
                    <List.Item>
                      <Space style={{ width: "100%", justifyContent: "space-between" }}>
                        <Space>
                          <Text strong>{entry.title}</Text>
                          {entry.author_name && (
                            <Tag>{entry.author_name}</Tag>
                          )}
                          <Tag>{entry.votes_count} голосов</Tag>
                        </Space>
                        {!contest.is_owner && contest.is_active && (
                          <Text type="secondary" style={{ fontSize: 12 }}>
                            {dayjs(entry.created_at).format("DD.MM.YYYY")}
                          </Text>
                        )}
                      </Space>
                    </List.Item>
                  )}
                />
              )}
            </Card>
          )}

          {/* Back button */}
          <Link href="/contest">
            <Button>← Назад к конкурсам</Button>
          </Link>
        </Space>
      </div>
    </>
  );
}

import { Head, Link } from "@inertiajs/react";
import { Button, Card, Empty, Table, Typography } from "antd";
import { PlusOutlined } from "@ant-design/icons";
import dayjs from "dayjs";

const { Title, Text } = Typography;

type QuizItem = {
  id: number;
  title: string;
  description: string | null;
  question_count: number;
  created_at: string;
};

type ContestItem = {
  id: number;
  title: string;
  type: string;
  status: string;
  quizzes: QuizItem[];
};

type QuizIndexProps = {
  contests: ContestItem[];
};

export default function QuizIndex({ contests }: QuizIndexProps): React.JSX.Element {
  const quizColumns = [
    {
      title: "Название",
      dataIndex: "title",
      key: "title",
      render: (title: string, record: QuizItem) => (
        <Link href={`/contest/${record.id}/quizzes/${record.id}/edit`}>{title}</Link>
      ),
    },
    {
      title: "Вопросов",
      dataIndex: "question_count",
      key: "question_count",
      width: 120,
    },
    {
      title: "Создана",
      dataIndex: "created_at",
      key: "created_at",
      width: 180,
      render: (date: string) => dayjs(date).format("DD.MM.YYYY HH:mm"),
    },
  ];

  const contestColumns = [
    {
      title: "Название конкурса",
      dataIndex: "title",
      key: "title",
      render: (title: string) => <Text strong>{title}</Text>,
    },
    {
      title: "Тип",
      dataIndex: "type",
      key: "type",
      width: 150,
      render: (type: string) => (
        <span style={{ textTransform: "capitalize" }}>{type}</span>
      ),
    },
    {
      title: "Викторины",
      dataIndex: "quizzes",
      key: "quizzes",
      render: (quizzes: QuizItem[]) => (
        <Table
          columns={quizColumns}
          dataSource={quizzes}
          pagination={false}
          size="small"
          rowKey="id"
        />
      ),
    },
    {
      title: "Действия",
      key: "actions",
      width: 150,
      render: (_: unknown, record: ContestItem) => (
        <Link href={`/contest/${record.id}/quizzes/create`}>
          <Button type="primary" size="small" icon={<PlusOutlined />}>
            Добавить викторину
          </Button>
        </Link>
      ),
    },
  ];

  return (
    <>
      <Head title="Викторины" />

      <div style={{ maxWidth: 1200, margin: "0 auto", padding: "24px 0" }}>
        <Title level={2}>Викторины</Title>

        {contests.length === 0 ? (
          <Card>
            <Empty description="У вас пока нет конкурсов" />
          </Card>
        ) : (
          <Table
            columns={contestColumns}
            dataSource={contests}
            pagination={false}
            rowKey="id"
          />
        )}
      </div>
    </>
  );
}

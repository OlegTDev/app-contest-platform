import { Head, Link } from "@inertiajs/react";
import { Button, Card, List, Typography, Avatar, Space, Result } from "antd";
import { TrophyOutlined, UserOutlined } from "@ant-design/icons";
import dayjs from "dayjs";

const { Title, Text, Paragraph } = Typography;

type LeaderboardItem = {
  user_id: number;
  user_name: string;
  user_email: string;
  score: number;
  total_questions: number;
  correct_answers: number;
  percentage: number;
  completed_at: string;
};

type QuizLeaderboardProps = {
  quiz: {
    id: number;
    title: string;
  };
  contest: {
    id: number;
    title: string;
  };
  leaderboard: LeaderboardItem[];
};

const getMedal = (index: number) => {
  switch (index) {
    case 0:
      return <TrophyOutlined style={{ color: "#faad14", fontSize: 20 }} />;
    case 1:
      return <TrophyOutlined style={{ color: "#c0c0c0", fontSize: 18 }} />;
    case 2:
      return <TrophyOutlined style={{ color: "#cd7f32", fontSize: 16 }} />;
    default:
      return <span style={{ color: "#999" }}>{index + 1}</span>;
  }
};

export default function QuizLeaderboard({ quiz, contest, leaderboard }: QuizLeaderboardProps): React.JSX.Element {
  const sorted = [...leaderboard].sort((a, b) => b.percentage - a.percentage);

  return (
    <>
      <Head title="Таблица лидеров" />

      <div style={{ maxWidth: 800, margin: "0 auto", padding: "24px 0" }}>
        <Space direction="vertical" size="large" style={{ width: "100%" }}>
          <div>
            <Link href={`/contest/${contest.id}`}>
              <Button type="text" style={{ float: "left", marginRight: 12 }}>
                ← Назад
              </Button>
            </Link>
            <Title level={2}>Таблица лидеров</Title>
            <Paragraph type="secondary">
              {quiz.title} | {contest.title}
            </Paragraph>
          </div>

          {sorted.length === 0 ? (
            <Card>
              <Result
                status="info"
                title="Пока нет участников"
                subTitle="Станьте первым, кто пройдёт эту викторину!"
                extra={[
                  <Link key="take" href={`/contest/${contest.id}/quizzes/${quiz.id}/take`}>
                    <Button type="primary">Пройти викторину</Button>
                  </Link>,
                ]}
              />
            </Card>
          ) : (
            <Card title={`Участники (${sorted.length})`}>
              <List
                dataSource={sorted}
                renderItem={(item, index) => (
                  <List.Item>
                    <Space style={{ width: "100%", justifyContent: "space-between" }}>
                      <Space>
                        {getMedal(index)}
                        <Avatar
                          size="default"
                          icon={index >= 3 ? <UserOutlined /> : undefined}
                        />
                        <div>
                          <Text strong>{item.user_name}</Text>
                          <br />
                          <Text type="secondary" style={{ fontSize: 12 }}>
                            {item.correct_answers}/{item.total_questions} правильных
                          </Text>
                        </div>
                      </Space>
                      <Space direction="vertical" align="end">
                        <Text strong style={{ fontSize: 16, color: "#1890ff" }}>
                          {item.percentage}%
                        </Text>
                        <Text type="secondary" style={{ fontSize: 12 }}>
                          {dayjs(item.completed_at).format("DD.MM.YYYY HH:mm")}
                        </Text>
                      </Space>
                    </Space>
                  </List.Item>
                )}
              />
            </Card>
          )}

          <Link href={`/contest/${contest.id}`}>
            <Button>К конкурсу</Button>
          </Link>
        </Space>
      </div>
    </>
  );
}

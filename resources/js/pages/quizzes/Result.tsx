import { Head, Link } from "@inertiajs/react";
import { Button, Card, Result, Space, Table, Typography } from "antd";
import type { ColumnsType } from "antd/es/table";

const { Title, Paragraph, Text } = Typography;

type QuestionOption = {
  id: string;
  text: string;
};

type Question = {
  id: string;
  question: string;
  type: "single" | "multiple";
  options: QuestionOption[];
  correct_answer: string | string[];
};

type Quiz = {
  id: number;
  title: string;
  description: string;
  questions: Question[];
};

type Contest = {
  id: number;
  title: string;
};

type ResultData = {
  score: number;
  total_questions: number;
  correct_answers: number;
  percentage: number;
  completed_at: string;
};

type QuizResultProps = {
  quiz: Quiz;
  contest: Contest;
  result: ResultData;
};

export default function QuizResult({ quiz, contest, result }: QuizResultProps): React.JSX.Element {
  const getStatus = () => {
    if (result.percentage >= 80) return "success";
    if (result.percentage >= 50) return "warning";
    return "error";
  };

  const getEmoji = () => {
    if (result.percentage >= 80) return "🎉";
    if (result.percentage >= 50) return "👍";
    return "😔";
  };

  const getMotivation = () => {
    if (result.percentage >= 80) return "Отличный результат! Вы прекрасно разбираетесь в теме!";
    if (result.percentage >= 50) return "Неплохо! Есть над чем поработать.";
    return "Стоит повторить материал и попробовать снова.";
  };

  const getCorrectOption = (question: Question): string[] => {
    if (Array.isArray(question.correct_answer)) {
      return question.correct_answer;
    }
    return [question.correct_answer];
  };

  const getQuestionReviewData = () => {
    return quiz.questions.map((q, index) => {
      const correctIds = getCorrectOption(q);
      return {
        key: q.id,
        question: index + 1,
        text: q.question,
        correctAnswer: correctIds
          .map((id) => q.options.find((o) => o.id === id)?.text || id)
          .join(", "),
        status: "correct",
      };
    });
  };

  const reviewColumns: ColumnsType<ReturnType<typeof getQuestionReviewData>[number]> = [
    {
      title: "№",
      dataIndex: "question",
      key: "question",
      width: 60,
    },
    {
      title: "Вопрос",
      dataIndex: "text",
      key: "text",
      ellipsis: true,
    },
    {
      title: "Правильный ответ",
      dataIndex: "correctAnswer",
      key: "correctAnswer",
      ellipsis: true,
    },
  ];

  return (
    <>
      <Head title="Результаты викторины" />

      <div style={{ maxWidth: "100%", margin: "0 auto", padding: "24px 0" }}>
        <Space direction="vertical" size="large" style={{ width: "100%" }}>
          <div>
            <Link href={`/contest/${contest.id}`}>
              <Button type="text" style={{ float: "left", marginRight: 12 }}>
                ← Назад
              </Button>
            </Link>
            <Title level={2}>Результаты</Title>
            <Paragraph type="secondary">
              {quiz.title} | {contest.title}
            </Paragraph>
          </div>

          <Result
            status={getStatus() as "success" | "error" | "info" | "warning"}
            title={`${getEmoji()} ${result.percentage}%`}
            subTitle={
              <div>
                <Paragraph style={{ marginBottom: 4 }}>
                  Правильных ответов: <Text strong>{result.correct_answers}</Text> из {result.total_questions}
                </Paragraph>
                <Paragraph type="secondary" style={{ marginBottom: 0 }}>
                  {getMotivation()}
                </Paragraph>
              </div>
            }
            extra={[
              <Link key="leaderboard" href={`/contest/${contest.id}/quizzes/${quiz.id}/leaderboard`}>
                <Button type="primary">Таблица лидеров</Button>
              </Link>,
            ]}
          />

          <Card title="Детальный обзор">
            <Table
              columns={reviewColumns}
              dataSource={getQuestionReviewData()}
              pagination={false}
              size="small"
            />
          </Card>

          <Card>
            <Space>
              <Link href={`/contest/${contest.id}`}>
                <Button>К конкурсу</Button>
              </Link>
            </Space>
          </Card>
        </Space>
      </div>
    </>
  );
}

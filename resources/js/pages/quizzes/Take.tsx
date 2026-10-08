import { Head, Link, useForm } from "@inertiajs/react";
import { Button, Card, Checkbox, Radio, Result, Space, Typography } from "antd";
import { PlayCircleOutlined } from "@ant-design/icons";
import { router } from "@inertiajs/react";

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

type QuizTakeProps = {
  quiz: Quiz;
  contest: Contest;
  alreadyCompleted: boolean;
  existingAnswer?: Record<string, unknown> | null;
};

export default function QuizTake({ quiz, contest, alreadyCompleted, existingAnswer }: QuizTakeProps): React.JSX.Element {
  const defaultAnswers: Record<string, unknown> = {};

  quiz.questions.forEach((q) => {
    defaultAnswers[q.id] = q.type === "multiple" ? [] : "";
  });

  const initialAnswers: Record<string, unknown> = {};

  if (existingAnswer && Object.keys(existingAnswer).length > 0) {
    Object.assign(initialAnswers, existingAnswer);
  } else {
    Object.assign(initialAnswers, defaultAnswers);
  }

  const { data, setData, post, processing, errors } = useForm<Record<string, unknown>>({
    ...initialAnswers,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    post(`/admin/contests/${contest.id}/quizzes/${quiz.id}/submit`, {
      onError: (error) => {
        console.error("Quiz submit error:", error);
      },
    });
  };

  const handleSingleChange = (questionId: string, value: string) => {
    setData(questionId, value);
  };

  const handleMultipleChange = (questionId: string, values: string[]) => {
    setData(questionId, values);
  };

  const answeredCount = quiz.questions.filter((q) => {
    const answer = data[q.id];
    if (q.type === "multiple") {
      return Array.isArray(answer) && answer.length > 0;
    }
    return answer !== "" && answer !== undefined;
  }).length;

  if (alreadyCompleted) {
    return (
      <>
        <Head title="Викторина пройдена" />
        <div style={{ maxWidth: 600, margin: "100px auto", textAlign: "center" }}>
          <Card>
            <Result
              status="info"
              title="Вы уже прошли эту викторину"
              subTitle="Пройти заново нельзя. Результаты уже сохранены."
              extra={[
                <Link key="result" href={`/admin/contests/${contest.id}/quizzes/${quiz.id}/result`}>
                  <Button type="primary">Посмотреть результаты</Button>
                </Link>,
              ]}
            />
          </Card>
        </div>
      </>
    );
  }

  return (
    <>
      <Head title={quiz.title} />

      <div style={{ maxWidth: 800, margin: "0 auto", padding: "24px 0" }}>
        <Space direction="vertical" size="large" style={{ width: "100%" }}>
          <div>
            <Link href={`/admin/contests/${contest.id}`}>
              <Button type="text" style={{ float: "left", marginRight: 12 }}>
                ← Назад
              </Button>
            </Link>
            <Title level={2}>{quiz.title}</Title>
            {quiz.description && <Paragraph type="secondary">{quiz.description}</Paragraph>}
            <Text type="secondary">
              Вопросов: {quiz.questions.length} | Пройдено: {answeredCount}/{quiz.questions.length}
            </Text>
          </div>

          <form onSubmit={handleSubmit}>
            {quiz.questions.map((question, qIndex) => (
              <Card
                key={question.id}
                style={{ marginBottom: 16 }}
                title={`Вопрос ${qIndex + 1}`}
              >
                <Paragraph strong style={{ marginBottom: 12 }}>
                  {question.question}
                </Paragraph>

                {question.type === "single" ? (
                  <Radio.Group
                    value={data[question.id] as string}
                    onChange={(e) => handleSingleChange(question.id, e.target.value)}
                  >
                    <Space direction="vertical" style={{ width: "100%" }}>
                      {question.options.map((option) => (
                        <Radio key={option.id} value={option.id}>
                          {option.text}
                        </Radio>
                      ))}
                    </Space>
                  </Radio.Group>
                ) : (
                  <Checkbox.Group
                    value={(data[question.id] as string[]) || []}
                    onChange={(values) => handleMultipleChange(question.id, values as string[])}
                  >
                    <Space direction="vertical" style={{ width: "100%" }}>
                      {question.options.map((option) => (
                        <Checkbox key={option.id} value={option.id}>
                          {option.text}
                        </Checkbox>
                      ))}
                    </Space>
                  </Checkbox.Group>
                )}
              </Card>
            ))}

            {errors.answers && (
              <div style={{ color: "red", marginBottom: 16 }}>
                {errors.answers}
              </div>
            )}

            <Card>
              <Button
                type="primary"
                htmlType="submit"
                loading={processing}
                size="large"
                disabled={answeredCount < quiz.questions.length}
              >
                {answeredCount < quiz.questions.length
                  ? `Ответить на все вопросы (${answeredCount}/${quiz.questions.length})`
                  : "Отправить ответы"}
              </Button>
            </Card>
          </form>
        </Space>
      </div>
    </>
  );
}

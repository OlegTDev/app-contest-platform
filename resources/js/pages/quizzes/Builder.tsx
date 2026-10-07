import { Head, Link, useForm } from "@inertiajs/react";
import { Button, Card, Checkbox, Form, Input, Radio, Space, Typography, message } from "antd";
import { PlusOutlined, MinusCircleOutlined } from "@ant-design/icons";
import { store, edit, update } from "@/routes/quizzes";

const { Title, Text, Paragraph } = Typography;

type QuestionType = "single" | "multiple";

type QuestionOption = {
  id: string;
  text: string;
};

type Question = {
  id: string;
  question: string;
  type: QuestionType;
  options: QuestionOption[];
  correct_answer: string | string[];
};

type QuizBuilderProps = {
  contest: {
    id: number;
    title: string;
    type: string;
  };
  quiz?: {
    id: number;
    title: string;
    description: string;
    questions: Question[];
  };
  initialQuestions?: Question[];
};

export default function QuizBuilder({ contest, quiz, initialQuestions }: QuizBuilderProps): React.JSX.Element {
  const isEditing = !!quiz?.id;

  const defaultQuestions: Question[] = initialQuestions && initialQuestions.length > 0
    ? initialQuestions
    : [{
        id: `q_${Date.now()}`,
        question: "",
        type: "single",
        options: [
          { id: `opt_${Date.now()}_1`, text: "" },
          { id: `opt_${Date.now()}_2`, text: "" },
        ],
        correct_answer: "",
      }];

  const { data, setData, post, put, processing, errors, reset } = useForm({
    title: quiz?.title || "",
    description: quiz?.description || "",
    questions: quiz?.questions || defaultQuestions,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    const url = isEditing
      ? update({ contest: contest.id, quiz: quiz!.id }).url
      : store({ contest: contest.id }).url;

    const method = isEditing ? put : post;

    method(url, {
      onSuccess: () => {
        message.success(isEditing ? "Викторина успешно обновлена" : "Викторина успешно создана");
      },
      onError: (error) => {
        console.error("Quiz save error:", error);
      },
    });
  };

  const addQuestion = () => {
    const newQuestion: Question = {
      id: `q_${Date.now()}`,
      question: "",
      type: "single",
      options: [
        { id: `opt_${Date.now()}_1`, text: "" },
        { id: `opt_${Date.now()}_2`, text: "" },
      ],
      correct_answer: "",
    };

    setData("questions", [...data.questions, newQuestion]);
  };

  const removeQuestion = (questionId: string) => {
    setData(
      "questions",
      data.questions.filter((q) => q.id !== questionId)
    );
  };

  const updateQuestion = (questionId: string, property: keyof Question, value: unknown) => {
    const updatedQuestions = data.questions.map((q) => {
      if (q.id === questionId) {
        return { ...q, [property]: value };
      }
      return q;
    });
    setData("questions", updatedQuestions);
  };

  const addOption = (questionId: string) => {
    const updatedQuestions = data.questions.map((q) => {
      if (q.id === questionId) {
        const newOption: QuestionOption = {
          id: `opt_${Date.now()}_${q.options.length}`,
          text: "",
        };
        return { ...q, options: [...q.options, newOption] };
      }
      return q;
    });
    setData("questions", updatedQuestions);
  };

  const removeOption = (questionId: string, optionId: string) => {
    const updatedQuestions = data.questions.map((q) => {
      if (q.id === questionId) {
        return {
          ...q,
          options: q.options.filter((o) => o.id !== optionId),
          correct_answer: q.correct_answer === optionId ? "" : q.correct_answer,
        };
      }
      return q;
    });
    setData("questions", updatedQuestions);
  };

  const updateOptionText = (questionId: string, optionId: string, text: string) => {
    const updatedQuestions = data.questions.map((q) => {
      if (q.id === questionId) {
        return {
          ...q,
          options: q.options.map((o) => (o.id === optionId ? { ...o, text } : o)),
        };
      }
      return q;
    });
    setData("questions", updatedQuestions);
  };

  const setCorrectAnswer = (questionId: string, optionId: string, type: QuestionType) => {
    const updatedQuestions = data.questions.map((q) => {
      if (q.id === questionId) {
        if (type === "multiple") {
          const currentAnswers = Array.isArray(q.correct_answer) ? q.correct_answer : [];
          const newAnswers = currentAnswers.includes(optionId)
            ? currentAnswers.filter((a) => a !== optionId)
            : [...currentAnswers, optionId];
          return { ...q, correct_answer: newAnswers };
        }
        return { ...q, correct_answer: optionId };
      }
      return q;
    });
    setData("questions", updatedQuestions);
  };

  const changeQuestionType = (questionId: string, newType: QuestionType) => {
    const updatedQuestions = data.questions.map((q) => {
      if (q.id === questionId) {
        if (newType === "multiple" && (!Array.isArray(q.correct_answer) || q.correct_answer === "")) {
          return { ...q, type: newType, correct_answer: [] };
        }
        if (newType === "single" && Array.isArray(q.correct_answer)) {
          return { ...q, type: newType, correct_answer: q.correct_answer[0] || "" };
        }
        return { ...q, type: newType };
      }
      return q;
    });
    setData("questions", updatedQuestions);
  };

  return (
    <>
      <Head title={isEditing ? `Редактировать викторину` : "Создать викторину"} />

      <div style={{ maxWidth: 900, margin: "0 auto", padding: "24px 0" }}>
        <Space direction="vertical" size="large" style={{ width: "100%" }}>
          <div>
            <Link href={`/contest/${contest.id}`}>
              <Button type="text" style={{ float: "left", marginRight: 12 }}>
                ← Назад
              </Button>
            </Link>
            <Title level={2}>
              {isEditing ? "Редактировать викторину" : "Создать викторину"}
            </Title>
            <Paragraph type="secondary">
              Конкурс: <Text strong>{contest.title}</Text>
            </Paragraph>
          </div>

          <Card>
            <Form layout="vertical">
              <Form.Item
                label="Название викторины"
                validateStatus={errors.title ? "error" : ""}
                help={errors.title}
              >
                <Input
                  value={data.title}
                  onChange={(e) => setData("title", e.target.value)}
                  placeholder="Например: Викторина по JavaScript"
                  size="large"
                />
              </Form.Item>

              <Form.Item
                label="Описание"
                validateStatus={errors.description ? "error" : ""}
                help={errors.description}
              >
                <Input.TextArea
                  value={data.description}
                  onChange={(e) => setData("description", e.target.value)}
                  placeholder="Краткое описание викторины"
                  rows={3}
                />
              </Form.Item>
            </Form>
          </Card>

          <Card
            title={`Вопросы (${data.questions.length})`}
            extra={
              <Button type="dashed" icon={<PlusOutlined />} onClick={addQuestion}>
                Добавить вопрос
              </Button>
            }
          >
            {data.questions.map((question, qIndex) => (
              <Card
                key={question.id}
                size="small"
                style={{ marginBottom: 16 }}
                title={`Вопрос ${qIndex + 1}`}
                extra={
                  <MinusCircleOutlined
                    style={{ color: "red" }}
                    onClick={() => removeQuestion(question.id)}
                  />
                }
              >
                <Form layout="vertical" style={{ marginTop: 12 }}>
                  <Form.Item label="Текст вопроса">
                    <Input
                      value={question.question}
                      onChange={(e) =>
                        updateQuestion(question.id, "question", e.target.value)
                      }
                      placeholder="Введите текст вопроса"
                    />
                  </Form.Item>

                  <Form.Item label="Тип вопроса">
                    <Radio.Group
                      value={question.type}
                      onChange={(e) =>
                        changeQuestionType(question.id, e.target.value)
                      }
                    >
                      <Radio value="single">Один правильный ответ</Radio>
                      <Radio value="multiple">Несколько правильных ответов</Radio>
                    </Radio.Group>
                  </Form.Item>

                  <Form.Item label="Варианты ответов">
                    <Space direction="vertical" style={{ width: "100%" }}>
                      {question.options.map((option, oIndex) => (
                        <Space key={option.id} style={{ width: "100%" }}>
                          {question.type === "single" ? (
                            <Radio
                              checked={question.correct_answer === option.id}
                              onClick={() => setCorrectAnswer(question.id, option.id, "single")}
                            />
                          ) : (
                            <Checkbox
                              checked={
                                Array.isArray(question.correct_answer) &&
                                question.correct_answer.includes(option.id)
                              }
                              onClick={() =>
                                setCorrectAnswer(question.id, option.id, "multiple")
                              }
                            />
                          )}
                          <Input
                            value={option.text}
                            onChange={(e) =>
                              updateOptionText(question.id, option.id, e.target.value)
                            }
                            placeholder={`Вариант ${oIndex + 1}`}
                            style={{ flex: 1 }}
                          />
                          {question.options.length > 2 && (
                            <MinusCircleOutlined
                              style={{ color: "red" }}
                              onClick={() => removeOption(question.id, option.id)}
                            />
                          )}
                        </Space>
                      ))}
                      <Button
                        type="dashed"
                        onClick={() => addOption(question.id)}
                        icon={<PlusOutlined />}
                        style={{ width: "100%" }}
                      >
                        Добавить вариант
                      </Button>
                    </Space>
                  </Form.Item>
                </Form>
              </Card>
            ))}

            {data.questions.length === 0 && (
              <div style={{ textAlign: "center", padding: "32px 0" }}>
                <Text type="secondary">
                  Добавьте хотя бы один вопрос, чтобы создать викторину
                </Text>
              </div>
            )}
          </Card>

          <Card>
            <Space>
              <Button
                type="primary"
                onClick={handleSubmit}
                loading={processing}
                disabled={data.questions.length === 0}
              >
                {isEditing ? "Сохранить изменения" : "Создать викторину"}
              </Button>
              <Button onClick={() => reset()}>Очистить</Button>
            </Space>
          </Card>
        </Space>
      </div>
    </>
  );
}

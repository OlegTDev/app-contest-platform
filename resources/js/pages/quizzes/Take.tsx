import { Head, Link, router } from "@inertiajs/react";
import { Button, Card, Radio, Space, Typography, Result, message } from "antd";
import { useState } from "react";

const { Title, Paragraph, Text } = Typography;

type QuizOption = {
    id: string;
    text: string;
    is_correct: boolean;
};

type Quiz = {
    id: number;
    title: string;
    description: string | null;
    question: string | null;
    options: QuizOption[] | null;
    type: string | null;
};

type Contest = {
    id: number;
    title: string;
};

type QuizTakeProps = {
    quiz: Quiz;
    contest: Contest;
    alreadyCompleted: boolean;
    existingAnswer: string[] | null;
};

export default function QuizTake({ quiz, contest, alreadyCompleted, existingAnswer }: QuizTakeProps): React.JSX.Element {
    const [selectedOptions, setSelectedOptions] = useState<string[]>(
        existingAnswer || []
    );

    const handleOptionSelect = (optionId: string) => {
        if (quiz.type === "single") {
            setSelectedOptions([optionId]);
        } else {
            setSelectedOptions((prev) => {
                if (prev.includes(optionId)) {
                    return prev.filter((id) => id !== optionId);
                }
                return [...prev, optionId];
            });
        }
    };

    const handleSubmit = () => {
        if (selectedOptions.length === 0) {
            message.warning("Выберите ответ");
            return;
        }

        router.post(`/contests/${contest.id}/quizzes/${quiz.id}/submit`, {
            selected_options: selectedOptions,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Redirect to result page
            },
            onError: () => {
                message.error("Не удалось отправить ответ");
            },
        });
    };

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
                                <Link key="result" href={`/contests/${contest.id}/quizzes/${quiz.id}/result`}>
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
                        <Link href={`/contests/${contest.id}`}>
                            <Button type="text" style={{ float: "left", marginRight: 12 }}>
                                ← Назад
                            </Button>
                        </Link>
                        <Title level={2}>{quiz.title}</Title>
                        {quiz.description && <Paragraph type="secondary">{quiz.description}</Paragraph>}
                    </div>

                    <Card title="Вопрос">
                        <Paragraph strong style={{ fontSize: 16, marginBottom: 20 }}>
                            {quiz.question || "Выберите правильный ответ"}
                        </Paragraph>

                        <Space direction="vertical" style={{ width: "100%" }}>
                            {quiz.options?.map((option) => {
                                const isSelected = selectedOptions.includes(option.id);
                                return (
                                    <Card
                                        key={option.id}
                                        hoverable
                                        style={{
                                            border: isSelected ? '2px solid #1677ff' : '1px solid #d9d9d9',
                                            background: isSelected ? '#e6f0ff' : '#fff',
                                            cursor: 'pointer',
                                        }}
                                        onClick={() => handleOptionSelect(option.id)}
                                    >
                                        <Space>
                                            {quiz.type === "single" ? (
                                                <Radio checked={isSelected} />
                                            ) : (
                                                <input
                                                    type="checkbox"
                                                    checked={isSelected}
                                                    readOnly
                                                    style={{ margin: 0 }}
                                                />
                                            )}
                                            <Text>{option.text}</Text>
                                        </Space>
                                    </Card>
                                );
                            })}
                        </Space>
                    </Card>

                    <Card>
                        <Button
                            type="primary"
                            size="large"
                            onClick={handleSubmit}
                            disabled={selectedOptions.length === 0}
                            block
                        >
                            Ответить
                        </Button>
                    </Card>
                </Space>
            </div>
        </>
    );
}

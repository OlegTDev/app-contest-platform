import { Head, Link } from "@inertiajs/react";
import { Button, Card, Result, Space, Tag, Typography } from "antd";
import { CheckCircleOutlined, CloseCircleOutlined } from "@ant-design/icons";

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

type AnswerData = {
    is_correct: boolean;
    selected_options: string[] | null;
    answered_at: string | null;
};

type QuizResultProps = {
    quiz: Quiz;
    contest: Contest;
    answer: AnswerData;
};

export default function QuizResult({ quiz, contest, answer }: QuizResultProps): React.JSX.Element {
    const getStatus = () => answer.is_correct ? "success" : "error";
    const getTitle = () => answer.is_correct ? "Правильно!" : "Неправильно";
    const getSubtitle = () =>
        answer.is_correct
            ? "Отличный результат! Вы выбрали верный ответ."
            : "К сожалению, ваш ответ неверный. Попробуйте в следующий раз!";

    return (
        <>
            <Head title="Результат" />

            <div style={{ maxWidth: 800, margin: "0 auto", padding: "24px 0" }}>
                <Space direction="vertical" size="large" style={{ width: "100%" }}>
                    <div>
                        <Link href={`/contests/${contest.id}`}>
                            <Button type="text" style={{ float: "left", marginRight: 12 }}>
                                ← Назад
                            </Button>
                        </Link>
                        <Title level={2}>{quiz.title}</Title>
                    </div>

                    <Result
                        status={getStatus()}
                        title={getTitle()}
                        subTitle={getSubtitle()}
                    />

                    <Card title="Ваш ответ">
                        <Space direction="vertical" style={{ width: "100%" }}>
                            {quiz.options?.map((option) => {
                                const isSelected = answer.selected_options?.includes(option.id);
                                const isCorrect = option.is_correct;
                                
                                let tagColor: string | undefined;
                                if (isCorrect && isSelected) tagColor = "green";
                                else if (isCorrect && !isSelected) tagColor = "default";
                                else if (!isCorrect && isSelected) tagColor = "red";
                                
                                return (
                                    <Tag
                                        key={option.id}
                                        color={tagColor}
                                        icon={
                                            isCorrect && isSelected ? <CheckCircleOutlined /> :
                                            !isCorrect && isSelected ? <CloseCircleOutlined /> :
                                            undefined
                                        }
                                        style={{ 
                                            width: "100%", 
                                            textAlign: "left",
                                            fontSize: 14,
                                            padding: "8px 12px",
                                        }}
                                    >
                                        {option.text}
                                    </Tag>
                                );
                            })}
                        </Space>
                    </Card>

                    {answer.answered_at && (
                        <Text type="secondary">
                            Ответ дан: {answer.answered_at}
                        </Text>
                    )}
                </Space>
            </div>
        </>
    );
}

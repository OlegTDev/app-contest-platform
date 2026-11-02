import { useState } from 'react';
import { router } from '@inertiajs/react';
import {
    Button,
    Card,
    Col,
    Drawer,
    Empty,
    Row,
    Space,
    Tag,
    Typography,
    message,
} from 'antd';
import { QuestionCircleOutlined } from '@ant-design/icons';
import {
    result as quizResultRoute,
    submit as submitQuiz,
} from '@/routes/quizzes';
import type { ContestData, MediaItem, QuizActivity } from '@/types/contest';
import MediaGallery from './components/media-gallery';

const { Title, Text, Paragraph } = Typography;

type QuizProps = {
    activities: QuizActivity[];
    contest: ContestData;
    onFileClick: (file: MediaItem) => void;
};

export default function Quiz({
    activities,
    contest,
    onFileClick,
}: QuizProps): React.JSX.Element {
    const [selectedQuiz, setSelectedQuiz] = useState<QuizActivity | null>(null);
    const [quizAnswering, setQuizAnswering] = useState<number | null>(null);
    const [selectedAnswers, setSelectedAnswers] = useState<
        Record<number, string[]>
    >({});
    const [quizResult] = useState<Record<number, { is_correct: boolean }>>({});

    const activeQuiz =
        activities.find((quiz) => quiz.id === selectedQuiz?.id) ?? null;

    const handleQuizAnswerSelect = (
        quizId: number,
        optionId: string,
        quizType: string,
    ): void => {
        setSelectedAnswers((previous) => {
            const current = previous[quizId] ?? [];

            if (quizType === 'single') {
                return { ...previous, [quizId]: [optionId] };
            }

            const nextSelection = current.includes(optionId)
                ? current.filter((id) => id !== optionId)
                : [...current, optionId];

            return { ...previous, [quizId]: nextSelection };
        });
    };

    const handleQuizSubmit = (quiz: QuizActivity): void => {
        const selection = selectedAnswers[quiz.id] ?? [];

        if (selection.length === 0) {
            message.warning('Выберите ответ');
            return;
        }

        setQuizAnswering(quiz.id);

        router.post(
            submitQuiz({ contest: contest.id, entry: quiz.id }).url,
            { selected_options: selection },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    setQuizAnswering(null);
                    router.get(
                        quizResultRoute({
                            contest: contest.id,
                            entry: quiz.id,
                        }).url,
                        {},
                        { preserveScroll: true },
                    );
                },
                onError: () => {
                    setQuizAnswering(null);
                    message.error('Не удалось отправить ответ');
                },
                onFinish: () => setQuizAnswering(null),
            },
        );
    };

    const renderQuestion = (quiz: QuizActivity): React.JSX.Element => {
        const isAnswered = quiz.is_answered || Boolean(quizResult[quiz.id]);
        const isAnswering = quizAnswering === quiz.id;
        const selection = selectedAnswers[quiz.id] ?? [];

        if (!isAnswered) {
            return (
                <>
                    <Space
                        orientation="vertical"
                        size="small"
                        style={{ width: '100%' }}
                    >
                        {quiz.options?.map((option) => {
                            const isSelected = selection.includes(option.id);

                            return (
                                <div
                                    key={option.id}
                                    onClick={() =>
                                        handleQuizAnswerSelect(
                                            quiz.id,
                                            option.id,
                                            quiz.quizType ?? 'single',
                                        )
                                    }
                                    style={{
                                        padding: '8px 12px',
                                        border: isSelected
                                            ? '2px solid #1677ff'
                                            : '1px solid #d9d9d9',
                                        borderRadius: 6,
                                        cursor: 'pointer',
                                        background: isSelected
                                            ? '#e6f0ff'
                                            : '#fff',
                                        transition: 'all 0.2s',
                                    }}
                                >
                                    <Space>
                                        {quiz.quizType === 'single' ? (
                                            <input
                                                type="radio"
                                                checked={isSelected}
                                                readOnly
                                                style={{ margin: 0 }}
                                            />
                                        ) : (
                                            <input
                                                type="checkbox"
                                                checked={isSelected}
                                                readOnly
                                                style={{ margin: 0 }}
                                            />
                                        )}
                                        <Text style={{ fontSize: 13 }}>
                                            {option.text}
                                        </Text>
                                    </Space>
                                </div>
                            );
                        })}
                    </Space>
                    <Button
                        type="primary"
                        block
                        loading={isAnswering}
                        disabled={selection.length === 0}
                        onClick={() => handleQuizSubmit(quiz)}
                    >
                        Ответить
                    </Button>
                </>
            );
        }

        return (
            <Space
                orientation="vertical"
                size="small"
                style={{ width: '100%' }}
            >
                {quiz.options?.map((option) => {
                    const isSelected = selection.includes(option.id);
                    const isCorrect = option.is_correct;
                    let tagColor: string | undefined;

                    if (isCorrect && isSelected) {
                        tagColor = 'green';
                    } else if (isCorrect && !isSelected) {
                        tagColor = 'red';
                    } else if (!isCorrect && isSelected) {
                        tagColor = 'volcano';
                    }

                    return (
                        <Tag
                            key={option.id}
                            color={tagColor}
                            style={{
                                width: '100%',
                                textAlign: 'center',
                                whiteSpace: 'normal',
                            }}
                        >
                            {option.text}
                        </Tag>
                    );
                })}
                <Tag
                    color={
                        quizResult[quiz.id]?.is_correct ? 'success' : 'error'
                    }
                >
                    {quizResult[quiz.id]?.is_correct
                        ? '✓ Правильно'
                        : '✗ Неправильно'}
                </Tag>
            </Space>
        );
    };

    return (
        <>
            <Card
                title={
                    <Space>
                        <QuestionCircleOutlined style={{ color: '#1677ff' }} />
                        <span>Вопросы</span>
                        <Tag>{activities.length}</Tag>
                    </Space>
                }
            >
                {activities.length === 0 ? (
                    <Empty description="Вопросов пока нет" />
                ) : (
                    <Row gutter={[16, 16]}>
                        {activities.map((quiz) => {
                            const isAnswered =
                                quiz.is_answered ||
                                Boolean(quizResult[quiz.id]);
                            const result = quizResult[quiz.id];

                            return (
                                <Col xs={24} sm={12} lg={8} key={quiz.id}>
                                    <Card
                                        hoverable
                                        style={{ height: '100%' }}
                                        onClick={() => setSelectedQuiz(quiz)}
                                        cover={
                                            quiz.media.length > 0 ? (
                                                <div
                                                    style={{
                                                        height: 160,
                                                        overflow: 'hidden',
                                                        background: '#f0f5ff',
                                                    }}
                                                >
                                                    <img
                                                        src={
                                                            quiz.media.find(
                                                                (item) =>
                                                                    item.is_main,
                                                            )?.file_url ??
                                                            quiz.media[0]
                                                                .file_url
                                                        }
                                                        alt={quiz.title}
                                                        style={{
                                                            width: '100%',
                                                            height: 160,
                                                            objectFit: 'cover',
                                                        }}
                                                    />
                                                </div>
                                            ) : (
                                                <div
                                                    style={{
                                                        height: 120,
                                                        background: '#f0f5ff',
                                                        display: 'flex',
                                                        alignItems: 'center',
                                                        justifyContent:
                                                            'center',
                                                        padding: '16px',
                                                    }}
                                                >
                                                    <Text
                                                        type="secondary"
                                                        style={{
                                                            fontSize: 13,
                                                            textAlign: 'center',
                                                        }}
                                                    >
                                                        {quiz.question ??
                                                            quiz.title}
                                                    </Text>
                                                </div>
                                            )
                                        }
                                    >
                                        <Space
                                            orientation="vertical"
                                            style={{ width: '100%' }}
                                        >
                                            <Title
                                                level={5}
                                                style={{ marginBottom: 4 }}
                                            >
                                                {quiz.title}
                                            </Title>

                                            {quiz.question && (
                                                <Paragraph
                                                    type="secondary"
                                                    ellipsis={{ rows: 2 }}
                                                    style={{ marginBottom: 8 }}
                                                >
                                                    {quiz.question}
                                                </Paragraph>
                                            )}

                                            {isAnswered ? (
                                                <Tag
                                                    color={
                                                        result?.is_correct
                                                            ? 'success'
                                                            : 'error'
                                                    }
                                                >
                                                    {result?.is_correct
                                                        ? '✓ Правильно'
                                                        : '✗ Неправильно'}
                                                </Tag>
                                            ) : (
                                                <Space size={4}>
                                                    <QuestionCircleOutlined
                                                        style={{
                                                            color: '#1677ff',
                                                        }}
                                                    />
                                                    <Text
                                                        type="secondary"
                                                        style={{ fontSize: 12 }}
                                                    >
                                                        Нажмите для ответа
                                                    </Text>
                                                </Space>
                                            )}

                                            {!quiz.is_visible && (
                                                <Tag color="orange">Скоро</Tag>
                                            )}
                                        </Space>
                                    </Card>
                                </Col>
                            );
                        })}
                    </Row>
                )}
            </Card>

            <Drawer
                title={
                    <Space>
                        <QuestionCircleOutlined style={{ color: '#1677ff' }} />
                        <span>Вопрос: {activeQuiz?.title ?? ''}</span>
                    </Space>
                }
                placement="right"
                onClose={() => setSelectedQuiz(null)}
                open={activeQuiz !== null}
                width={750}
            >
                <Space
                    orientation="vertical"
                    size="large"
                    style={{ width: '100%' }}
                >
                    <MediaGallery
                        media={activeQuiz?.media ?? []}
                        onFileClick={onFileClick}
                    />

                    {activeQuiz && (
                        <Card size="small" title="Вопрос">
                            <Space
                                orientation="vertical"
                                size="middle"
                                style={{ width: '100%' }}
                            >
                                <Paragraph style={{ marginBottom: 0 }}>
                                    {activeQuiz.question ??
                                        'Выберите правильный ответ'}
                                </Paragraph>
                                {renderQuestion(activeQuiz)}
                            </Space>
                        </Card>
                    )}

                    {activeQuiz?.description && (
                        <Card size="small" title="Описание">
                            <Paragraph>{activeQuiz.description}</Paragraph>
                        </Card>
                    )}
                </Space>
            </Drawer>
        </>
    );
}

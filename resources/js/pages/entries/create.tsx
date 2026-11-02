import { Head, Link, useForm, usePage } from "@inertiajs/react";
import { Button, Card, DatePicker, Form, Input, Radio, Space, Typography, message } from "antd";
import { PlusOutlined, MinusCircleOutlined } from "@ant-design/icons";
import dayjs from "dayjs";
import { useState } from "react";

const { Title, Text, Paragraph } = Typography;

type Option = {
    text: string;
    is_correct: boolean;
};

type CreateEntryForm = {
    title: string;
    description: string;
    author_name: string;
    author_department: string;
    question: string;
    options: Option[];
    type: "single" | "multiple";
    show_from: string | null;
    show_until: string | null;
};

type ContestItem = {
    id: number;
    title: string;
    type: string;
};

type CreateEntryProps = {
    contest: ContestItem;
};

export default function CreateEntry({ contest }: CreateEntryProps): React.JSX.Element {
    const { flash } = usePage<{ flash?: { success?: string; error?: string } }>().props;

    const isQuiz = contest.type === "quiz";

    const [options, setOptions] = useState<Option[]>([
        { text: "", is_correct: false },
        { text: "", is_correct: false },
    ]);

    const [questionType, setQuestionType] = useState<"single" | "multiple">("single");

    const { data, setData, post, processing, errors } = useForm<CreateEntryForm>({
        title: "",
        description: "",
        author_name: "",
        author_department: "",
        question: "",
        options: options,
        type: questionType,
        show_from: null as string | null,
        show_until: null as string | null,
    });

    const addOption = () => {
        const newOptions = [...options, { text: "", is_correct: false }];
        setOptions(newOptions);
        setData("options", newOptions);
    };

    const removeOption = (index: number) => {
        if (options.length <= 2) return;
        const newOptions = options.filter((_, i) => i !== index);
        setOptions(newOptions);
        setData("options", newOptions);
    };

    const updateOption = (index: number, field: keyof Option, value: string | boolean) => {
        const newOptions = [...options];
        newOptions[index] = { ...newOptions[index], [field]: value };
        setOptions(newOptions);
        setData("options", newOptions);
    };

    const setCorrectAnswer = (index: number) => {
        const newOptions = options.map((opt, i) => ({
            ...opt,
            is_correct: i === index,
        }));
        setOptions(newOptions);
        setData("options", newOptions);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        const url = `/admin/contests/${contest.id}/entries`;

        const payload: Record<string, unknown> = {
            title: data.title,
            description: data.description || null,
            show_from: data.show_from || null,
            show_until: data.show_until || null,
        };

        if (isQuiz) {
            payload.question = data.question;
            payload.options = data.options;
            payload.type = data.type;
        } else {
            payload.author_name = data.author_name || null;
            payload.author_department = data.author_department || null;
        }

        post(url, {
            data: payload,
            preserveScroll: true,
            onSuccess: () => {
                message.success("Карточка успешно создана");
            },
        });
    };

    return (
        <>
            <Head title="Добавить карточку" />

            <div style={{ maxWidth: "100%", margin: "0 auto", padding: "24px 0" }}>
                <Space direction="vertical" size="large" style={{ width: "100%" }}>
                    <Space>
                        <Link href={`/admin/contests/${contest.id}`}>
                            <Button type="text">← Назад</Button>
                        </Link>
                        <div>
                            <Title level={2} style={{ margin: 0 }}>
                                Добавить карточку
                            </Title>
                            <Text type="secondary">
                                Конкурс: <Text strong>{contest.title}</Text>
                            </Text>
                        </div>
                    </Space>

                    <form onSubmit={handleSubmit}>
                        <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
                            <Card title="Основная информация">
                                <Space direction="vertical" size="middle" style={{ width: "100%" }}>
                                    <Form.Item
                                        label="Название"
                                        validateStatus={errors.title ? "error" : ""}
                                        help={errors.title}
                                    >
                                        <Input
                                            value={data.title}
                                            onChange={(e) => setData("title", e.target.value)}
                                            placeholder={isQuiz ? "Название вопроса" : "Название работы"}
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
                                            placeholder="Краткое описание"
                                            rows={2}
                                        />
                                    </Form.Item>
                                </Space>
                            </Card>

                            {isQuiz ? (
                                <Card title="Вопрос и варианты ответов">
                                    <Space direction="vertical" size="middle" style={{ width: "100%" }}>
                                        <Form.Item
                                            label="Текст вопроса"
                                            validateStatus={errors.question ? "error" : ""}
                                            help={errors.question}
                                        >
                                            <Input.TextArea
                                                value={data.question}
                                                onChange={(e) => setData("question", e.target.value)}
                                                placeholder="Введите текст вопроса"
                                                rows={3}
                                            />
                                        </Form.Item>

                                        <Form.Item label="Тип вопроса">
                                            <Radio.Group
                                                value={data.type}
                                                onChange={(e) => {
                                                    setQuestionType(e.target.value);
                                                    setData("type", e.target.value);
                                                }}
                                            >
                                                <Radio value="single">Один правильный ответ</Radio>
                                                <Radio value="multiple">Несколько правильных ответов</Radio>
                                            </Radio.Group>
                                        </Form.Item>

                                        <Form.Item label="Варианты ответов">
                                            <Space direction="vertical" style={{ width: "100%" }}>
                                                {options.map((option, index) => (
                                                    <Space key={index} style={{ width: "100%" }}>
                                                        {questionType === "single" ? (
                                                            <Radio
                                                                checked={option.is_correct}
                                                                onClick={() => setCorrectAnswer(index)}
                                                            />
                                                        ) : (
                                                            <input
                                                                type="checkbox"
                                                                checked={option.is_correct}
                                                                onChange={() =>
                                                                    updateOption(index, "is_correct", !option.is_correct)
                                                                }
                                                                style={{ marginTop: 8 }}
                                                            />
                                                        )}
                                                        <Input
                                                            value={option.text}
                                                            onChange={(e) => updateOption(index, "text", e.target.value)}
                                                            placeholder={`Вариант ${index + 1}`}
                                                            style={{ flex: 1 }}
                                                        />
                                                        {options.length > 2 && (
                                                            <MinusCircleOutlined
                                                                style={{ color: "red", marginTop: 8 }}
                                                                onClick={() => removeOption(index)}
                                                            />
                                                        )}
                                                    </Space>
                                                ))}
                                                <Button
                                                    type="dashed"
                                                    onClick={addOption}
                                                    icon={<PlusOutlined />}
                                                    style={{ width: "100%" }}
                                                >
                                                    Добавить вариант
                                                </Button>
                                            </Space>
                                        </Form.Item>
                                    </Space>
                                </Card>
                            ) : (
                                <Card title="Информация об авторе">
                                    <Space direction="vertical" size="middle" style={{ width: "100%" }}>
                                        <Form.Item
                                            label="ФИО автора"
                                            validateStatus={errors.author_name ? "error" : ""}
                                            help={errors.author_name}
                                        >
                                            <Input
                                                value={data.author_name}
                                                onChange={(e) => setData("author_name", e.target.value)}
                                                placeholder="Иванов Иван Иванович"
                                            />
                                        </Form.Item>

                                        <Form.Item
                                            label="Отдел/Организация"
                                            validateStatus={errors.author_department ? "error" : ""}
                                            help={errors.author_department}
                                        >
                                            <Input
                                                value={data.author_department}
                                                onChange={(e) => setData("author_department", e.target.value)}
                                                placeholder="Отдел разработки"
                                            />
                                        </Form.Item>
                                    </Space>
                                </Card>
                            )}

                            <Card title="Таймер показа">
                                <Text type="secondary" style={{ display: "block", marginBottom: 16 }}>
                                    Оставьте пустым, чтобы показать сразу. Заполните, чтобы скрыть до определённого времени.
                                </Text>
                                <Space direction="vertical" size="middle" style={{ width: "100%" }}>
                                    <Form.Item label="Показать с">
                                        <DatePicker
                                            showTime
                                            value={data.show_from ? dayjs(data.show_from) : null}
                                            onChange={(date) => {
                                                setData("show_from", date ? date.format("YYYY-MM-DD HH:mm:ss") : null);
                                            }}
                                            style={{ width: "100%" }}
                                            placeholder="Начало показа"
                                        />
                                    </Form.Item>

                                    <Form.Item label="Показать до">
                                        <DatePicker
                                            showTime
                                            value={data.show_until ? dayjs(data.show_until) : null}
                                            onChange={(date) => {
                                                setData("show_until", date ? date.format("YYYY-MM-DD HH:mm:ss") : null);
                                            }}
                                            style={{ width: "100%" }}
                                            placeholder="Конец показа"
                                        />
                                    </Form.Item>
                                </Space>
                            </Card>

                            <Card>
                                <Space>
                                    <Button
                                        type="primary"
                                        htmlType="submit"
                                        loading={processing}
                                    >
                                        Создать карточку
                                    </Button>
                                    <Link href={`/admin/contests/${contest.id}`}>
                                        <Button>Отмена</Button>
                                    </Link>
                                </Space>
                            </Card>
                        </div>
                    </form>
                </Space>
            </div>
        </>
    );
}

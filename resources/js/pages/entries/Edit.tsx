import { Head, Link, useForm, usePage } from "@inertiajs/react";
import { Button, Card, DatePicker, Form, Input, Radio, Space, Typography, message } from "antd";
import { PlusOutlined, MinusCircleOutlined } from "@ant-design/icons";
import dayjs from "dayjs";
import { update } from "@/routes/admin/contests";
import { useState } from "react";

const { Title, Text } = Typography;

type Option = {
    text: string;
    is_correct: boolean;
};

type EditEntryForm = {
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

type EntryItem = {
    id: number;
    title: string;
    description: string | null;
    author_name: string | null;
    author_department: string | null;
    question: string | null;
    options: Option[] | null;
    type: string | null;
    fields_data: Record<string, unknown> | null;
    sort_order: number;
    show_from: string | null;
    show_until: string | null;
};

type EditEntryProps = {
    contest: ContestItem;
    entry: EntryItem;
};

export default function EditEntry({ contest, entry }: EditEntryProps): React.JSX.Element {
    const { flash } = usePage<{ flash?: { success?: string; error?: string } }>().props;

    const isQuiz = contest.type === "quiz";

    const initialOptions: Option[] =
        entry.options && entry.options.length > 0
            ? entry.options
            : [
                  { text: "", is_correct: false },
                  { text: "", is_correct: false },
              ];

    const initialType = (entry.type as "single" | "multiple") || "single";

    const [options, setOptions] = useState<Option[]>(initialOptions);
    const [questionType, setQuestionType] = useState<"single" | "multiple">(initialType);

    const { data, setData, patch, transform, processing, errors } = useForm<EditEntryForm>({
        title: entry.title,
        description: entry.description || "",
        author_name: (entry.author_name as string) || "",
        author_department: (entry.author_department as string) || "",
        question: (entry.question as string) || "",
        options: initialOptions,
        type: initialType,
        show_from: entry.show_from || "",
        show_until: entry.show_until || "",
    });

    const addOption = () => {
        const newOptions = [...options, { text: "", is_correct: false }];
        setOptions(newOptions);
    };

    const removeOption = (index: number) => {
        if (options.length <= 2) return;
        const newOptions = options.filter((_, i) => i !== index);
        setOptions(newOptions);
    };

    const updateOption = (index: number, field: keyof Option, value: string | boolean) => {
        const newOptions = [...options];
        newOptions[index] = { ...newOptions[index], [field]: value };
        setOptions(newOptions);
    };

    const setCorrectAnswer = (index: number) => {
        const newOptions = options.map((opt, i) => ({
            ...opt,
            is_correct: i === index,
        }));
        setOptions(newOptions);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // Бэкенд отдаёт даты в формате "Y-m-d H:i" без секунд, а валидация
        // требует "Y-m-d H:i:s" — нормализуем перед отправкой.
        const normalizeDate = (value: string | null): string | null =>
            value ? dayjs(value).format("YYYY-MM-DD HH:mm:ss") : null;

        if (isQuiz) {
            transform((formData) => ({
                ...formData,
                description: formData.description || null,
                question: formData.question || null,
                show_from: normalizeDate(formData.show_from),
                show_until: normalizeDate(formData.show_until),
                options,
                type: questionType,
            }));
        } else {
            transform((formData) => ({
                ...formData,
                description: formData.description || null,
                author_name: formData.author_name || null,
                author_department: formData.author_department || null,
            }));
        }

        patch(`/admin/contests/${contest.id}/entries/${entry.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                message.success("Карточка успешно обновлена");
            },
        });
    };

    return (
        <>
            <Head title="Редактировать карточку" />

            <div style={{ maxWidth: "100%", margin: "0 auto", padding: "24px 0" }}>
                <Space direction="vertical" size="large" style={{ width: "100%" }}>
                    <Space>
                        <Link href={`/admin/contests/${contest.id}`}>
                            <Button type="text">← Назад</Button>
                        </Link>
                        <div>
                            <Title level={2} style={{ margin: 0 }}>Редактировать карточку</Title>
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
                                                value={questionType}
                                                onChange={(e) => {
                                                    setQuestionType(e.target.value);
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
                                        Сохранить изменения
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

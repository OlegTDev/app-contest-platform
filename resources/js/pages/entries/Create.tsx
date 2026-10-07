import { Head, Link, useForm, usePage } from "@inertiajs/react";
import { Button, Card, DatePicker, Form, Input, message, Space, Typography } from "antd";
import dayjs from "dayjs";
import { store } from "@/routes/contest";

const { Title, Text } = Typography;

type CreateEntryForm = {
    title: string;
    description: string;
    author_name: string;
    author_department: string;
    show_from: string;
    show_until: string;
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

    const { data, setData, post, processing, errors } = useForm<CreateEntryForm>({
        title: "",
        description: "",
        author_name: "",
        author_department: "",
        show_from: "",
        show_until: "",
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        const url = `/contest/${contest.id}/entries`;

        post(url, {
            data: {
                title: data.title,
                description: data.description || null,
                fields_data: {
                    author_name: data.author_name,
                    author_department: data.author_department,
                },
                show_from: data.show_from ? dayjs(data.show_from).format("YYYY-MM-DD HH:mm:ss") : null,
                show_until: data.show_until ? dayjs(data.show_until).format("YYYY-MM-DD HH:mm:ss") : null,
            },
            preserveScroll: true,
            onSuccess: () => {
                message.success("Карточка успешно создана");
            },
        });
    };

    return (
        <>
            <Head title="Добавить карточку" />

            <div style={{  maxWidth: "100%", margin: "0 auto", padding: "24px 0" }}>
                <Space direction="vertical" size="large" style={{ width: "100%" }}>
                    <div>
                        <Link href={`/contest/${contest.id}`}>
                            <Button type="text" style={{ float: "left", marginRight: 12 }}>
                                ← Назад
                            </Button>
                        </Link>
                        <Title level={2}>Добавить карточку</Title>
                        <Text type="secondary">
                            Конкурс: <Text strong>{contest.title}</Text>
                        </Text>
                    </div>

                    <form onSubmit={handleSubmit}>
                        <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
                            <Card title="Основная информация">
                                <Space direction="vertical" size="middle" style={{ width: "100%" }}>
                                    <Form.Item
                                        label="Название работы"
                                        validateStatus={errors.title ? "error" : ""}
                                        help={errors.title}
                                    >
                                        <Input
                                            value={data.title}
                                            onChange={(e) => setData("title", e.target.value)}
                                            placeholder="Название работы"
                                        />
                                    </Form.Item>

                                    <Form.Item
                                        label="Описание работы"
                                        validateStatus={errors.description ? "error" : ""}
                                        help={errors.description}
                                    >
                                        <Input.TextArea
                                            value={data.description}
                                            onChange={(e) => setData("description", e.target.value)}
                                            placeholder="Краткое описание работы"
                                            rows={3}
                                        />
                                    </Form.Item>
                                </Space>
                            </Card>

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

                            <Card title="Таймер показа">
                                <Text type="secondary" style={{ display: "block", marginBottom: 16 }}>
                                    Оставьте пустым, чтобы показать сразу. Заполните, чтобы скрыть до определённого времени.
                                </Text>
                                <Space direction="vertical" size="middle" style={{ width: "100%" }}>
                                    <Form.Item label="Показать с">
                                        <DatePicker
                                            showTime
                                            value={data.show_from ? dayjs(data.show_from) : null}
                                            onChange={(date) =>
                                                setData(
                                                    "show_from",
                                                    date ? date.format("YYYY-MM-DD HH:mm:ss") : ""
                                                )
                                            }
                                            style={{ width: "100%" }}
                                            placeholder="Начало показа"
                                        />
                                    </Form.Item>

                                    <Form.Item label="Показать до">
                                        <DatePicker
                                            showTime
                                            value={data.show_until ? dayjs(data.show_until) : null}
                                            onChange={(date) =>
                                                setData(
                                                    "show_until",
                                                    date ? date.format("YYYY-MM-DD HH:mm:ss") : ""
                                                )
                                            }
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
                                    <Link href={`/contest/${contest.id}/entries`}>
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

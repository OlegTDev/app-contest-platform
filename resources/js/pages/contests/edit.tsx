import { Head, Link, useForm } from "@inertiajs/react";
import { Alert, Button, Card, DatePicker, Form, Input, Select, Space, Typography } from "antd";
import dayjs from "dayjs";
import { update } from "@/routes/admin/contests";

const { Text } = Typography;

type TypeField = "text" | "select" | "textarea" | "file";

type ProjectField = {
  name: string;
  label: string;
  type: TypeField;
  required: boolean;
  select_values?: string;
};

type FormType = {
  title: string;
  type: string;
  status: string;
  description: string;
  start_at: string;
  end_at: string;
  project_schema: ProjectField[];
};

type ContestEditProps = {
  contest: {
    id: number;
    title: string;
    type: string;
    status: string;
    description: string | null;
    start_at: string | null;
    end_at: string | null;
    project_schema: ProjectField[];
  };
  contestTypes: { label: string; value: string }[];
};

export default function ContestEdit({ contest, contestTypes }: ContestEditProps): React.JSX.Element {
  const { data, setData, patch, processing, errors } = useForm<FormType>({
    title: contest.title,
    type: contest.type,
    status: contest.status,
    description: contest.description || "",
    start_at: contest.start_at || "",
    end_at: contest.end_at || "",
    project_schema: contest.project_schema || [],
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    patch(update({ contest: contest.id }).url, {
      preserveScroll: true,
    });
  };

  const errorMessages = Object.values(errors).filter(
    (value): value is string => typeof value === "string"
  );

  const addField = (type: TypeField) => {
    const timestamp = Date.now();
    const newField: ProjectField = {
      name: `field_${timestamp}`,
      label: "",
      type,
      required: false,
      select_values: undefined,
    };
    setData("project_schema", [...data.project_schema, newField]);
  };

  const removeField = (name: string) => {
    setData(
      "project_schema",
      data.project_schema.filter((field) => field.name !== name)
    );
  };

  const updateFieldProperty = <K extends keyof ProjectField>(
    name: string,
    property: K,
    value: ProjectField[K]
  ) => {
    const updatedSchema = data.project_schema.map((field) => {
      if (field.name === name) {
        field[property] = value;
      }
      return field;
    });
    setData("project_schema", updatedSchema);
  };

  const addButtons = (handleAddField: (type: TypeField) => void) => {
    return (
      <Space.Compact block style={{ marginTop: 15 }}>
        <Button
          htmlType="button"
          color="default"
          variant="dashed"
          onClick={() => handleAddField("text")}
          style={{ cursor: "pointer" }}
        >
          Текстовая строка
        </Button>
        <Button
          htmlType="button"
          color="default"
          variant="dashed"
          onClick={() => handleAddField("select")}
          style={{ cursor: "pointer" }}
        >
          Выпадающий список
        </Button>
        <Button
          htmlType="button"
          color="default"
          variant="dashed"
          onClick={() => handleAddField("textarea")}
          style={{ cursor: "pointer" }}
        >
          Текстовое поле
        </Button>
        <Button
          htmlType="button"
          color="default"
          variant="dashed"
          onClick={() => handleAddField("file")}
          style={{ cursor: "pointer" }}
        >
          Загрузка файла
        </Button>
      </Space.Compact>
    );
  };

  const fieldsItems = (
    fields: ProjectField[],
    update: <K extends keyof ProjectField>(
      name: string,
      property: K,
      value: ProjectField[K]
    ) => void,
    remove: (name: string) => void
  ) => {
    if (fields.length === 0) {
      return <Text type="secondary">Поля не добавлены</Text>;
    }
    return (
      <table style={{ width: "100%", borderCollapse: "collapse" }}>
        <tbody>
          {fields.map((field, index) => (
            <tr key={field.name} style={{ verticalAlign: "top", borderBottom: "1px solid #f0f0f0" }}>
              <td style={{ width: 120, paddingRight: 16 }}>
                <span style={{
                  display: "inline-block",
                  padding: "2px 8px",
                  borderRadius: 4,
                  background: "#e6f7ff",
                  color: "#1890ff",
                  fontSize: 12,
                }}>
                  {field.type}
                </span>
              </td>
              <td style={{ width: 300, paddingRight: 16 }}>
                <Space direction="vertical" size="small" style={{ width: "100%" }}>
                  <Input
                    value={field.label}
                    onChange={(e) => update(field.name, "label", e.target.value)}
                    placeholder="Метка (label) поля"
                    size="small"
                  />
                  <Input
                    value={field.type === "select" ? field.select_values || "" : ""}
                    onChange={(e) =>
                      field.type === "select" &&
                      update(field.name, "select_values", e.target.value)
                    }
                    placeholder={
                      field.type === "select"
                        ? "Значения через перенос строки"
                        : undefined
                    }
                    disabled={field.type !== "select"}
                    size="small"
                  />
                </Space>
              </td>
              <td style={{ width: 80 }}>
                <Input.Checkbox
                  checked={field.required}
                  onChange={(e) => update(field.name, "required", e.target.checked)}
                />
              </td>
              <td style={{ width: 40 }}>
                <Button
                  type="text"
                  danger
                  size="small"
                  onClick={() => remove(field.name)}
                >
                  ✕
                </Button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    );
  };

  return (
    <>
      <Head title={`Редактировать: ${contest.title}`} />

      <div style={{ maxWidth: "100%", margin: "0 auto", padding: "24px 0" }}>
        <Space direction="vertical" size="large" style={{ width: "100%" }}>
          <Space>
            <Link href={`/admin/contests/${contest.id}`}>
              <Button type="text">← Назад</Button>
            </Link>
            <h1>Редактировать конкурс</h1>
          </Space>

          {errorMessages.length > 0 && (
            <Alert
              type="error"
              showIcon
              message="Не удалось сохранить конкурс"
              description={
                <ul style={{ margin: 0, paddingLeft: 18 }}>
                  {errorMessages.map((message, index) => (
                    <li key={index}>{message}</li>
                  ))}
                </ul>
              }
            />
          )}

          <form onSubmit={handleSubmit}>
            <Card title="Основная информация">
              <Space direction="vertical" size="middle" style={{ width: "100%" }}>
                <Form.Item
                  label="Название конкурса"
                  validateStatus={errors.title ? "error" : ""}
                  help={errors.title}
                >
                  <Input
                    value={data.title}
                    onChange={(e) => setData("title", e.target.value)}
                    placeholder="Название"
                  />
                </Form.Item>

                <Form.Item
                  label="Тип конкурса"
                  validateStatus={errors.type ? "error" : ""}
                  help={errors.type}
                >
                  <Select
                    options={contestTypes}
                    value={data.type}
                    onChange={(value) => setData("type", value)}
                    disabled
                  />
                </Form.Item>

                <Form.Item
                  label="Статус"
                  validateStatus={errors.status ? "error" : ""}
                  help={errors.status}
                >
                  <Select
                    value={data.status}
                    onChange={(value) => setData("status", value)}
                    options={[
                      { label: "Черновик", value: "draft" },
                      { label: "Активен", value: "published" },
                      { label: "На паузе", value: "paused" },
                      { label: "Закрыт", value: "closed" },
                    ]}
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
                    placeholder="Описание конкурса"
                    rows={3}
                  />
                </Form.Item>
              </Space>
            </Card>

            <Card title="Период проведения">
              <Space direction="vertical" size="middle" style={{ width: "100%" }}>
                <Form.Item
                  label="Начало"
                  validateStatus={errors.start_at ? "error" : ""}
                  help={errors.start_at}
                >
                  <DatePicker
                    showTime
                    value={data.start_at ? dayjs(data.start_at) : null}
                    onChange={(date) =>
                      setData(
                        "start_at",
                        date ? date.format("YYYY-MM-DD HH:mm:ss") : ""
                      )
                    }
                    style={{ width: "100%" }}
                    placeholder="Начало"
                  />
                </Form.Item>

                <Form.Item
                  label="Окончание"
                  validateStatus={errors.end_at ? "error" : ""}
                  help={errors.end_at}
                >
                  <DatePicker
                    showTime
                    value={data.end_at ? dayjs(data.end_at) : null}
                    onChange={(date) =>
                      setData(
                        "end_at",
                        date ? date.format("YYYY-MM-DD HH:mm:ss") : ""
                      )
                    }
                    style={{ width: "100%" }}
                    placeholder="Окончание"
                  />
                </Form.Item>
              </Space>
            </Card>

            <Card title="Поля анкеты проекта">
              <Text type="secondary">
                Настройте поля, которые участники должны заполнить при подаче
                проекта.
              </Text>
              {fieldsItems(data.project_schema, updateFieldProperty, removeField)}
              <div>{addButtons(addField)}</div>
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
          </form>
        </Space>
      </div>
    </>
  );
}

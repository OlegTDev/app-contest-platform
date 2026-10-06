import { Head, useForm } from "@inertiajs/react";
import { Button, Card, Checkbox, Empty, Form, Input, Select, Space, Tag, Typography } from "antd";
import { BsInputCursor, BsTextareaResize, BsUpload, BsXCircle } from "react-icons/bs";
import { CgSelectR } from "react-icons/cg";
import { MinusCircleOutlined } from '@ant-design/icons';
import { store } from "@/routes/contest";


const { Text } = Typography;

type TypeField = "text" | "select" | "textarea" | "file";

type ProjectField = {
  name: string;
  label: string;
  type: TypeField;
  required: boolean;
  select_values?: string;
}

type FormType = {
  title: string;
  type: string;
  project_schema: ProjectField[];
};

const addButtons = (handleAddField: (type: TypeField) => void) => {
  return (
    <Space.Compact block style={{ marginTop: 15 }}>
      <Button
        htmlType="button"
        color="default"
        variant="dashed"
        icon={<BsInputCursor />}
        onClick={() => handleAddField("text")}
        style={{ cursor: "pointer" }}
      >
        Добавить текстовую строку
      </Button>
      <Button
        htmlType="button"
        color="default"
        variant="dashed"
        icon={<CgSelectR />}
        onClick={() => handleAddField("select")}
        style={{ cursor: "pointer" }}
      >
        Добавить выпадающий список
      </Button>
      <Button
        htmlType="button"
        color="default"
        variant="dashed"
        icon={<BsTextareaResize />}
        onClick={() => handleAddField("textarea")}
        style={{ cursor: "pointer" }}
      >
        Добавить текстовое поле
      </Button>
      <Button
        htmlType="button"
        color="default"
        variant="dashed"
        icon={<BsUpload />}
        onClick={() => handleAddField("file")}
        style={{ cursor: "pointer" }}
      >
        Добавить поле загрузки файла
      </Button>
      </Space.Compact>
  );
};

const fieldsItems = (
  fields: ProjectField[],
  update: <K extends keyof ProjectField>(name: string, property: K, value: ProjectField[K]) => void,
  remove: (name: string) => void,
) => {
  if (fields.length  === 0) {
    return (<Empty />);
  }
  return (
    <Typography>
      <table>
        <tbody>
          {fields.map((field, index) => (
            <tr key={field.name} style={{ verticalAlign: 'top' }}>
              <td style={{ width: 200 }}>
                <Tag color="blue" variant="filled">{field.type}</Tag>
              </td>
              <td>
                <Space orientation="vertical" size="medium" style={{ display: 'flex' }}>
                  <Input
                    value={field.label}
                    onChange={(e) =>
                      update(field.name, "label", e.target.value)
                    }
                    placeholder="Метка (label) поля"
                  />
                  <Checkbox
                    checked={field.required}
                    onChange={(e) => update(field.name, "required", e.target.checked)}
                  >
                    Обязательное поле
                  </Checkbox>
                  {field.type === 'select' && (
                    <Form.Item label="Список значений">
                      <Input.TextArea
                        rows={5}
                        value={field.select_values}
                        onChange={(e) => update(field.name, "select_values", e.target.value)}
                      />
                    </Form.Item>
                  )}
                </Space>
              </td>
              <td style={{ width: 200 }}>
                <MinusCircleOutlined style={{ color: 'red' }} onClick={() => remove(field.name)} />
              </td>
            </tr>
        ))}
        </tbody>
      </table>
    </Typography>
  );
};


type ContestType = {
  label: string;
  value: string;
};

type CreateProps = {
  contestTypes: ContestType[];
};

export default function Create({ contestTypes }: CreateProps): React.JSX.Element {

  const { data, setData, post, processing, errors } = useForm<FormType>({
    title: '',
    type: '',
    project_schema: [],
  });

  const handleSubmit = (e: React.SyntheticEvent) => {
    e.preventDefault();
    post(store().url);
  };

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
      data.project_schema.filter((field) => field.name !== name),
    );
  };

  const updateFieldProperty = <K extends keyof ProjectField>(
    name: string,
    property: K,
    value: ProjectField[K],
  ) => {
    const updatedSchema = data.project_schema.map((field) => {
      if (field.name === name) {
        field[property] = value;
      }
      return field;
    });
    setData("project_schema", updatedSchema);
  };

  return (
    <>
      <div>
        <Head title="Создать новый конкурс" />
        <h1>Конструктор конкурсов</h1>

        <form onSubmit={handleSubmit}>
          <div>
            <Form.Item
              layout="vertical"
              label="Название конкурса"
              validateStatus={errors.title ? 'error' : ''}
              help={errors.title}
            >
              <Input
                type="text"
                value={data.title}
                onChange={(e) => setData("title", e.target.value)}
                placeholder="Название"
              />
            </Form.Item>
          </div>
          <div>
            <Form.Item
              label="Тип конкурса"
              layout="vertical"
              validateStatus={errors.type ? 'error' : ''}
              help={errors.type}
            >
              <Select
                options={contestTypes}
                value={data.type}
                onChange={(value) => setData('type', value)}
              />
            </Form.Item>
          </div>
          <Card title="Поля анкеты проекта" style={{ marginTop: 20 }}>
            <Text type="secondary">
              Настройте поля, которые участники должны будут заполнить при
              подаче проекта на этот конкурс.
            </Text>
            {fieldsItems(data.project_schema, updateFieldProperty, removeField)}
            <div>
              {addButtons(addField)}
              {errors.project_schema && (
                <div className="text-red-500 text-sm mt-2">
                  {errors.project_schema}
                </div>
              )}
            </div>
          </Card>
          <Button htmlType="submit">Save</Button>
        </form>
      </div>
    </>
  );
}

import { Head, useForm } from "@inertiajs/react";
import { Button, Card, Checkbox, Empty, Form, Input, Space, Tag, Typography } from "antd";
import { BsInputCursor, BsTextareaResize, BsUpload, BsXCircle } from "react-icons/bs";
import { CgSelectR } from "react-icons/cg";
import { MinusCircleOutlined } from '@ant-design/icons';


const { Text } = Typography;

type TypeField = "text" | "select" | "textarea" | "file";

interface ProjectField {
  name: string;
  label: string;
  type: TypeField;
  required: boolean;
  selectValues?: string;
}

type FormType = {
  title: string;
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
  update: <K extends keyof ProjectField>(index: number, property: K, value: ProjectField[K]) => void,
  remove: (index: number) => void,
) => {
  if (fields.length  === 0) {
    return (<Empty />);
  }
  return (
    <Typography>
      <table>
        <tbody>
          {fields.map((field, index) => (
            <tr style={{ verticalAlign: 'top' }}>
              <td style={{ width: 200 }}>
                <Tag color="blue" variant="filled">{field.type}</Tag>
              </td>
              <td>
                <Space orientation="vertical" size="medium" style={{ display: 'flex' }}>
                  <Input
                    value={field.label}
                    onChange={(e) =>
                      update(index, "label", e.target.value)
                    }
                    placeholder="Метка (label) поля"
                  />
                  <Checkbox
                    checked={field.required}
                    onChange={(e) => update(index, "required", e.target.checked)}
                  >
                    Обязательное поле
                  </Checkbox>
                  {field.type === 'select' && (
                    <Form.Item label="Список значений">
                      <Input.TextArea
                        rows={5}
                        value={field.selectValues}
                        onChange={(e) => update(index, "selectValues", e.target.value)}
                      />
                    </Form.Item>
                  )}
                </Space>
              </td>
              <td style={{ width: 200 }}>
                <MinusCircleOutlined style={{ color: 'red' }} onClick={() => remove(index)} />
              </td>
            </tr>
        ))}
        </tbody>
      </table>
    </Typography>
  );
};


export default function Create(): React.JSX.Element {
  const { data, setData, post, processing, errors } = useForm<FormType>({
    title: "",
    project_schema: [],
  });

  const handleSubmit = () => {};

  const addField = (type: TypeField) => {
    const timestamp = Date.now();
    const newField: ProjectField = {
      name: `field_${timestamp}`,
      label: "",
      type,
      required: false,
      selectValues: undefined,
    };

    setData("project_schema", [...data.project_schema, newField]);
  };

  const removeField = (indexToRemove: number) => {
    setData(
      "project_schema",
      data.project_schema.filter((_, index) => indexToRemove !== index),
    );
  };

  const updateFieldProperty = <K extends keyof ProjectField>(
    index: number,
    property: K,
    value: ProjectField[K],
  ) => {
    const updatedSchema = [...data.project_schema] as ProjectField[];
    updatedSchema[index][property] = value;
    setData("project_schema", updatedSchema);
  };

  return (
    <>
      <div>
        <Head title="Создать новый конкурс" />
        <h1>Конструктор конкурсов</h1>

        <form onSubmit={handleSubmit} className="space-y-6">
          <Form layout="vertical">
          <div>
            <label>Название конкурса</label>

            <Input
              type="text"
              value={data.title}
              onChange={(e) => setData("title", e.target.value)}
              placeholder="Название"
            />
            {errors.title && (
              <div className="text-red-500 text-sm mt-1">{errors.title}</div>
            )}
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
          </Form>
        </form>
      </div>
    </>
  );
}

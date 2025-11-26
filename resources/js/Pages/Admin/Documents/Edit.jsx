import { Link, useForm, usePage } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";

export default function Edit() {
    const { document } = usePage().props;

    const { data, setData, put, processing } = useForm({
        title: document.title,
        text: document.text ?? "",
    });

    const submit = (e) => {
        e.preventDefault();
        put(`/admin/documents/${document.id}`);
    };

    return (
        <AdminLayout>
            {/* HEADER */}
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-3xl font-bold text-[#21397D]">
                    Редактировать документ
                </h1>

                <Link
                    href="/admin/documents"
                    className="px-4 py-2 rounded shadow text-white"
                    style={{ backgroundColor: "#21397D" }}
                >
                    ← Назад
                </Link>
            </div>

            {/* FORM CARD */}
            <div className="bg-white shadow-md rounded-lg p-6 border border-gray-100">
                <form onSubmit={submit} className="space-y-6">
                    {/* Title */}
                    <div>
                        <label className="block mb-1 text-sm font-semibold text-gray-700">
                            Название документа
                        </label>

                        <input
                            type="text"
                            className="
                                w-full rounded-md border-gray-300
                                focus:ring-[#21397D] focus:border-[#21397D]
                                transition
                            "
                            value={data.title}
                            onChange={(e) => setData("title", e.target.value)}
                            required
                        />
                    </div>

                    {/* Text */}
                    <div>
                        <label className="block mb-1 text-sm font-semibold text-gray-700">
                            Текст
                        </label>

                        <textarea
                            className="
                                w-full min-h-[220px] rounded-md border-gray-300
                                focus:ring-[#21397D] focus:border-[#21397D]
                                transition
                            "
                            value={data.text}
                            onChange={(e) => setData("text", e.target.value)}
                        />
                    </div>

                    {/* Update button */}
                    <button
                        className="
                            px-6 py-2 rounded text-white font-medium shadow
                            hover:opacity-90 transition
                        "
                        style={{ backgroundColor: "#21397D" }}
                        disabled={processing}
                    >
                        Обновить
                    </button>
                </form>
            </div>
        </AdminLayout>
    );
}

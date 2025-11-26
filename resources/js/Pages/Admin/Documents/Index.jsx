import { Link, usePage, router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { useState } from "react";

export default function Index() {
    const { documents } = usePage().props;

    const [confirmModal, setConfirmModal] = useState({
        show: false,
        id: null,
        title: "",
    });

    const openConfirm = (id, title) => {
        setConfirmModal({ show: true, id, title });
    };

    const closeConfirm = () => {
        setConfirmModal({ show: false, id: null, title: "" });
    };

    const deleteDocument = () => {
        router.delete(`/admin/documents/${confirmModal.id}`);
        closeConfirm();
    };

    return (
        <AdminLayout>
            {/* Header */}
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-3xl font-bold text-[#21397D]">Документы</h1>

                <Link
                    href="/admin/documents/create"
                    className="px-4 py-2 bg-[#21397D] text-white rounded shadow hover:bg-[#1e2d63] transition"
                >
                    + Добавить документ
                </Link>
            </div>

            {/* Card container */}
            <div className="bg-white shadow-md rounded-lg overflow-hidden border border-gray-100">
                <table className="min-w-full text-left">
                    <thead className="border-b bg-[#21397D]">
                        <tr>
                            <th className="py-3 px-4 text-gray-200 font-semibold">
                                ID
                            </th>
                            <th className="py-3 px-4 text-gray-200 font-semibold">
                                Название
                            </th>
                            <th className="py-3 px-4 text-gray-200 font-semibold text-right">
                                Действия
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        {documents.data.map((doc) => (
                            <tr
                                key={doc.id}
                                className="border-b hover:bg-gray-50 transition"
                            >
                                <td className="py-3 px-4">{doc.id}</td>

                                <td className="py-3 px-4 font-medium text-gray-800">
                                    {doc.title}
                                </td>

                                <td className="py-3 px-4 text-right space-x-3">
                                    {/* Edit button */}
                                    <Link
                                        href={`/admin/documents/${doc.id}/edit`}
                                        className="text-blue-600 hover:text-blue-800 transition"
                                    >
                                        ✏️ Редактировать
                                    </Link>

                                    {/* Delete button -> open modal */}
                                    <button
                                        onClick={() =>
                                            openConfirm(doc.id, doc.title)
                                        }
                                        className="text-red-600 hover:text-red-800 transition"
                                    >
                                        🗑️ Удалить
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Pagination */}
            <div className="mt-6">
                {documents.links.map((link, index) => {
                    let label = link.label;

                    if (label.includes("Previous")) label = "← Назад";
                    else if (label.includes("Next")) label = "Вперёд →";
                    else
                        label = label
                            .replace("&laquo;", "")
                            .replace("&raquo;", "");

                    return (
                        <Link
                            key={index}
                            href={link.url || "#"}
                            className={`px-3 py-1 mx-1 rounded ${
                                link.active
                                    ? "bg-[#21397D] text-white"
                                    : "bg-gray-200 text-gray-700 hover:bg-gray-300"
                            }`}
                            dangerouslySetInnerHTML={{ __html: label }}
                        />
                    );
                })}
            </div>

            {/* ---------------- CONFIRM DELETE MODAL ---------------- */}
            {confirmModal.show && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg shadow-xl p-6 w-96">
                        <h2 className="text-xl font-semibold text-gray-800 mb-4">
                            Удалить документ?
                        </h2>

                        <p className="text-gray-600 mb-6">
                            Вы уверены, что хотите удалить: <br />
                            <span className="font-semibold">
                                {confirmModal.title}
                            </span>
                            ?
                        </p>

                        <div className="flex justify-end space-x-3">
                            <button
                                onClick={closeConfirm}
                                className="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition"
                            >
                                Отмена
                            </button>

                            <button
                                onClick={deleteDocument}
                                className="px-4 py-2 text-white rounded"
                                style={{ backgroundColor: "#21397D" }}
                            >
                                Удалить
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}

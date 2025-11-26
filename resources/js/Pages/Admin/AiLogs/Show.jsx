import AdminLayout from "@/Layouts/AdminLayout";
import { Link, usePage } from "@inertiajs/react";

export default function Show() {
    const { log } = usePage().props;

    return (
        <AdminLayout>
            {/* Header */}
            <div className="flex items-center justify-between mb-6">
                <h1 className="text-3xl font-bold text-[#21397D]">
                    Детали AI Лога #{log.id}
                </h1>

                <Link
                    href="/admin/ai-logs"
                    className="px-4 py-2 bg-[#21397D] text-white rounded shadow hover:bg-[#1e2d63] transition"
                >
                    ← Назад
                </Link>
            </div>

            {/* Card */}
            <div className="bg-white shadow rounded-lg p-6 border border-gray-100 space-y-6">
                {/* Question */}
                <div>
                    <h2 className="text-xl font-semibold text-[#21397D] mb-2">
                        Вопрос пользователя
                    </h2>
                    <p className="text-gray-800 whitespace-pre-line">
                        {log.question}
                    </p>
                </div>

                {/* Matched Titles */}
                <div>
                    <h2 className="text-xl font-semibold text-[#21397D] mb-2">
                        Найденные разделы
                    </h2>

                    {log.matched_titles?.length ? (
                        <ul className="list-disc ml-6 text-gray-700">
                            {log.matched_titles.map((t, idx) => (
                                <li key={idx}>{t}</li>
                            ))}
                        </ul>
                    ) : (
                        <p className="text-gray-500">Нет совпадений</p>
                    )}
                </div>

                {/* Context */}
                <div>
                    <h2 className="text-xl font-semibold text-[#21397D] mb-2">
                        Использованный контекст
                    </h2>
                    <pre className="bg-gray-100 p-4 rounded text-sm overflow-x-auto whitespace-pre-wrap">
                        {log.context || "—"}
                    </pre>
                </div>

                {/* Final Answer */}
                <div>
                    <h2 className="text-xl font-semibold text-[#21397D] mb-2">
                        Ответ AI
                    </h2>
                    <div className="bg-gray-100 p-4 rounded text-sm whitespace-pre-wrap break-words">
                        {log.final_answer || "—"}
                    </div>
                </div>

                {log.error && (
                    <div>
                        <h2 className="text-xl font-semibold text-red-600 mb-2">
                            Ошибка
                        </h2>
                        <div className="bg-red-100 p-4 rounded text-sm whitespace-pre-wrap break-words border border-red-300">
                            {log.error}
                        </div>
                    </div>
                )}

                {/* Date */}
                <div className="text-gray-600 text-sm">
                    Создано: {new Date(log.created_at).toLocaleString()}
                </div>
            </div>
        </AdminLayout>
    );
}
